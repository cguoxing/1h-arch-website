<?php
declare(strict_types=1);

function oneh_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        session_set_cookie_params(0, '/; samesite=Lax', '', $secure, true);
    }
    session_start();
}

/** CSRF 令牌：每个会话一个，所有后台表单与上传接口都需携带。 */
function oneh_csrf_token(): string
{
    oneh_session_start();
    if (empty($_SESSION['oneh_csrf'])) {
        $_SESSION['oneh_csrf'] = bin2hex(random_bytes(32));
    }
    return (string) $_SESSION['oneh_csrf'];
}

function oneh_csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(oneh_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function oneh_csrf_valid(): bool
{
    $sent = (string) ($_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    return $sent !== '' && hash_equals(oneh_csrf_token(), $sent);
}

function oneh_client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64);
}
