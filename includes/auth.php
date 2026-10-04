<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/security.php';

oneh_session_start();

function oneh_current_admin(): ?string
{
    return $_SESSION['oneh_admin'] ?? null;
}

function oneh_is_admin(): bool
{
    return oneh_current_admin() !== null;
}

function oneh_login(string $username, string $password): bool
{
    oneh_install_schema();
    $rows = oneh_db_rows("SELECT username, password_hash FROM admins WHERE username = ? LIMIT 1", 's', [$username]);
    $admin = $rows[0] ?? null;
    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        usleep(600000); // 减缓暴力尝试
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['oneh_admin'] = $admin['username'];
    return true;
}

function oneh_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
    }
    session_destroy();
}

function oneh_require_admin(): void
{
    if (!oneh_is_admin()) {
        oneh_redirect('admin.php?login=1');
    }
}
