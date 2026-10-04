<?php
/**
 * 配置模板：复制为 includes/config.php 后填写（config.php 不进 git）。
 *   cp includes/config.example.php includes/config.php
 * Config template: copy to includes/config.php and fill in (config.php is git-ignored).
 *
 * 1H-Website 统一配置
 * 开发（1H-NAS）与上线（西部数码虚拟主机）共用此文件，靠环境变量切换。
 * 上线时只需把 DB_HOST 改为服务商提供的数据库地址，代码零改。
 */
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'oneh_arch');
define('DB_USER', getenv('DB_USER') ?: 'oneh');
define('DB_PASS', getenv('DB_PASS') ?: 'change-me');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// 上传目录（相对站点根）。后台上传图片只存路径，不进数据库。
define('UPLOAD_DIR',  __DIR__ . '/../project-uploads/');
define('UPLOAD_URL',  'project-uploads/');

// New dynamic-site aliases. Keep the older DB_* names above for existing test tools.
define('ONEH_DB_HOST', DB_HOST);
define('ONEH_DB_NAME', DB_NAME);
define('ONEH_DB_USER', DB_USER);
define('ONEH_DB_PASS', DB_PASS);
define('ONEH_DB_CHARSET', 'utf8mb4');
define('ONEH_BOOTSTRAP_ADMIN_USER', 'admin');
define('ONEH_BOOTSTRAP_ADMIN_PASS', 'change-this-password');
define('ONEH_UPLOAD_DIR', rtrim(UPLOAD_DIR, '/'));
define('ONEH_UPLOAD_URL', rtrim(UPLOAD_URL, '/'));
define('ONEH_SITE_NAME', '1+H Integrated Design');

/**
 * 返回 mysqli 连接（单例）
 */
function db_connect() {
    static $conn = null;
    if ($conn === null) {
        $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, (int) DB_PORT);
        if (!$conn) {
            http_response_code(500);
            die('Database connection failed: ' . mysqli_connect_error());
        }
        mysqli_set_charset($conn, 'utf8mb4');
    }
    return $conn;
}
