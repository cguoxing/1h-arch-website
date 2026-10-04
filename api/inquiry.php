<?php
declare(strict_types=1);

/**
 * 联系表单提交接口。
 * - JS 提交（Accept: application/json）返回 JSON；
 * - 普通表单提交（无 JS）处理完重定向回 contact.php。
 */
require_once __DIR__ . '/../includes/inquiries.php';

$wantsJson = stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    oneh_redirect('../contact.php');
}

[$ok, $error] = oneh_submit_inquiry($_POST);

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    if (!$ok) {
        http_response_code($error === 'limit' ? 429 : 422);
    }
    echo oneh_json_encode([
        'ok' => $ok,
        'error' => $error,
        'message' => $ok ? oneh_get_content('contact')['form']['success'] : oneh_inquiry_error_message($error),
        'token' => oneh_inquiry_form_token(),
    ]);
    exit;
}

oneh_redirect('../contact.php?' . ($ok ? 'sent=1' : 'error=' . rawurlencode($error)) . '#inquiry');
