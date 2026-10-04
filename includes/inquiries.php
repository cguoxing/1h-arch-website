<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';
require_once __DIR__ . '/security.php';

/** 表单签名密钥：首次使用时随机生成并存入数据库。 */
function oneh_form_secret(): string
{
    static $secret = null;
    if ($secret !== null) {
        return $secret;
    }
    $saved = oneh_get_settings()['_form_secret'] ?? '';
    if (!is_string($saved) || strlen($saved) < 32) {
        $saved = bin2hex(random_bytes(32));
        oneh_save_setting('_form_secret', $saved);
    }
    return $secret = $saved;
}

/** 无需会话的防伪令牌：时间戳 + 签名，用于识别机器人秒提交和伪造请求。 */
function oneh_inquiry_form_token(): string
{
    $ts = (string) time();
    return $ts . '.' . substr(hash_hmac('sha256', 'inquiry|' . $ts, oneh_form_secret()), 0, 32);
}

function oneh_inquiry_token_check(string $token): string
{
    [$ts, $sig] = array_pad(explode('.', $token, 2), 2, '');
    if (!ctype_digit($ts) || !hash_equals(substr(hash_hmac('sha256', 'inquiry|' . $ts, oneh_form_secret()), 0, 32), $sig)) {
        return 'token';
    }
    $age = time() - (int) $ts;
    if ($age < 3) {
        return 'fast';
    }
    if ($age > 86400 * 2) {
        return 'expired';
    }
    return '';
}

function oneh_inquiry_error_message(string $code): string
{
    $messages = [
        'required' => 'Please complete the required fields before sending the inquiry. 请填写必填项。',
        'email' => 'Please enter a valid email address. 请填写正确的邮箱。',
        'expired' => 'The form has expired, please refresh the page and try again. 页面已过期，请刷新后重试。',
        'token' => 'The form has expired, please refresh the page and try again. 页面已过期，请刷新后重试。',
        'fast' => 'Please take a moment to complete the form. 请稍候再提交。',
        'limit' => 'Too many submissions. Please try again later or email us directly. 提交过于频繁，请稍后再试或直接发邮件。',
        'server' => 'Something went wrong. Please email us directly. 提交失败，请直接发送邮件联系我们。',
    ];
    return $messages[$code] ?? $messages['server'];
}

/**
 * 校验并保存留言。返回 [ok(bool), errorCode(string)]。
 */
function oneh_submit_inquiry(array $input): array
{
    // 蜜罐：机器人会填写隐藏字段，直接假装成功
    if (trim((string) ($input['website'] ?? '')) !== '') {
        return [true, ''];
    }
    $tokenError = oneh_inquiry_token_check((string) ($input['_token'] ?? ''));
    if ($tokenError !== '') {
        return [false, $tokenError];
    }

    $clip = function (string $key, int $len) use ($input): string {
        return oneh_substr(trim((string) ($input[$key] ?? '')), 0, $len);
    };
    $data = [
        'name' => $clip('name', 120),
        'company' => $clip('company', 200),
        'email' => $clip('email', 200),
        'phone' => $clip('phone', 60),
        'project_type' => $clip('projectType', 120),
        'brief' => $clip('brief', 5000),
    ];
    if ($data['name'] === '' || $data['email'] === '' || $data['brief'] === '') {
        return [false, 'required'];
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return [false, 'email'];
    }

    $db = oneh_db();
    if (!$db || !oneh_install_schema()) {
        return [false, 'server'];
    }
    $ip = oneh_client_ip();
    $recent = (int) oneh_db_value("SELECT COUNT(*) FROM inquiries WHERE ip = ? AND created_at > (NOW() - INTERVAL 1 HOUR)", 's', [$ip]);
    if ($recent >= 5) {
        return [false, 'limit'];
    }

    $ua = oneh_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
    $stmt = $db->prepare("INSERT INTO inquiries (name, company, email, phone, project_type, brief, ip, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        return [false, 'server'];
    }
    $stmt->bind_param('ssssssss', $data['name'], $data['company'], $data['email'], $data['phone'], $data['project_type'], $data['brief'], $ip, $ua);
    if (!$stmt->execute()) {
        return [false, 'server'];
    }

    oneh_notify_inquiry($data);
    return [true, ''];
}

/** 邮件提醒（虚拟主机未开通 mail() 时静默失败，留言仍在后台可查）。 */
function oneh_notify_inquiry(array $data): void
{
    $to = trim((string) (oneh_get_content('site')['notify_email'] ?? ''));
    if ($to === '' || !function_exists('mail')) {
        return;
    }
    $subject = '=?UTF-8?B?' . base64_encode('[1+H 官网留言] ' . $data['name'] . ' · ' . $data['project_type']) . '?=';
    $body = "姓名: {$data['name']}\n公司: {$data['company']}\n邮箱: {$data['email']}\n电话/微信: {$data['phone']}\n项目类型: {$data['project_type']}\n\n{$data['brief']}\n";
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
    if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $headers .= 'Reply-To: ' . str_replace(["\r", "\n"], '', $data['email']) . "\r\n";
    }
    @mail($to, $subject, $body, $headers);
}

function oneh_inquiry_unread_count(): int
{
    return (int) oneh_db_value("SELECT COUNT(*) FROM inquiries WHERE status = 'new'");
}
