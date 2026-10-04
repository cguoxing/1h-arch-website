<?php
declare(strict_types=1);

/**
 * 单张图片异步上传接口。
 * 后台选图后立即 POST 到这里，返回 JSON，前端据此显示“上传成功/失败”。
 * 图片只落盘到 project-uploads/，数据库只存路径。
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/images.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function oneh_upload_fail(string $message, int $code = 400): void
{
    http_response_code($code);
    echo oneh_json_encode(['ok' => false, 'error' => $message]);
    exit;
}

function oneh_watermark_uploaded_image(string $source, string $target, string $mime): bool
{
    if (!extension_loaded('gd')) {
        return move_uploaded_file($source, $target);
    }

    switch ($mime) {
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            break;
        case 'image/webp':
            $image = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($source) : false;
            break;
        case 'image/gif':
            $image = @imagecreatefromgif($source);
            break;
        default:
            $image = false;
    }

    if (!$image) {
        return move_uploaded_file($source, $target);
    }

    $width = imagesx($image);
    $height = imagesy($image);
    $text = chr(169) . ' 1+H Integrated Design';
    $font = 5;
    $textWidth = imagefontwidth($font) * strlen($text);
    $textHeight = imagefontheight($font);
    $padding = max(10, (int) round(min($width, $height) * 0.018));
    $x = $padding;
    $y = max($padding, $height - $textHeight - $padding);

    imagealphablending($image, true);
    imagesavealpha($image, true);
    $shadow = imagecolorallocatealpha($image, 0, 0, 0, 48);
    $mark = imagecolorallocatealpha($image, 255, 255, 255, 22);
    $back = imagecolorallocatealpha($image, 0, 0, 0, 86);
    imagefilledrectangle($image, $x - 7, $y - 5, $x + $textWidth + 7, $y + $textHeight + 5, $back);
    imagestring($image, $font, $x + 1, $y + 1, $text, $shadow);
    imagestring($image, $font, $x, $y, $text, $mark);

    switch ($mime) {
        case 'image/jpeg':
            $ok = imagejpeg($image, $target, 88);
            break;
        case 'image/png':
            $ok = imagepng($image, $target, 6);
            break;
        case 'image/gif':
            $ok = imagegif($image, $target);
            break;
        case 'image/webp':
            $ok = function_exists('imagewebp') ? imagewebp($image, $target, 86) : false;
            break;
        default:
            $ok = false;
    }
    imagedestroy($image);
    if (!$ok) {
        return move_uploaded_file($source, $target);
    }
    return true;
}

if (!oneh_is_admin()) {
    oneh_upload_fail('未登录或会话已过期，请重新登录后台。', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    oneh_upload_fail('请求方式错误。', 405);
}

if (!oneh_csrf_valid()) {
    oneh_upload_fail('页面已过期，请刷新后台页面后重试。', 400);
}

// 单张图片字段名 image
if (!isset($_FILES['image'])) {
    // 可能整个 POST 超过 post_max_size，导致 $_FILES 为空
    $postMax = ini_get('post_max_size');
    oneh_upload_fail('未收到文件，可能超过服务器上传上限（post_max_size=' . $postMax . '）。');
}

$file = $_FILES['image'];
$errorMessages = [
    UPLOAD_ERR_INI_SIZE   => '图片超过服务器单文件上限（upload_max_filesize）。',
    UPLOAD_ERR_FORM_SIZE  => '图片超过表单允许的大小。',
    UPLOAD_ERR_PARTIAL    => '图片只上传了一部分，请重试。',
    UPLOAD_ERR_NO_FILE    => '没有选择文件。',
    UPLOAD_ERR_NO_TMP_DIR => '服务器缺少临时目录。',
    UPLOAD_ERR_CANT_WRITE  => '服务器写入磁盘失败。',
    UPLOAD_ERR_EXTENSION  => '上传被服务器扩展拦截。',
];
$errCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
if ($errCode !== UPLOAD_ERR_OK) {
    oneh_upload_fail($errorMessages[$errCode] ?? ('上传出错（错误码 ' . $errCode . '）。'));
}

$tmp = (string) $file['tmp_name'];
if (!is_uploaded_file($tmp)) {
    oneh_upload_fail('非法上传。');
}

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];
$type = function_exists('mime_content_type') ? (string) mime_content_type($tmp) : (string) ($file['type'] ?? '');
if (!isset($allowed[$type])) {
    oneh_upload_fail('仅支持 JPG / PNG / WebP / GIF 图片。');
}
$extension = $allowed[$type];

// 上限：12MB（浏览器已压缩，通常几百 KB；真正硬限制由 php.ini upload_max_filesize 决定）
$maxBytes = 12 * 1024 * 1024;
if ((int) $file['size'] > $maxBytes) {
    oneh_upload_fail('图片超过 12MB 上限。');
}

if (!is_dir(ONEH_UPLOAD_DIR) && !mkdir(ONEH_UPLOAD_DIR, 0755, true) && !is_dir(ONEH_UPLOAD_DIR)) {
    oneh_upload_fail('无法创建上传目录 project-uploads/。', 500);
}
if (!is_writable(ONEH_UPLOAD_DIR)) {
    oneh_upload_fail('上传目录 project-uploads/ 不可写，请检查权限。', 500);
}

$projectId = oneh_slugify((string) ($_POST['project_id'] ?? 'project'));
$name = $projectId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
$target = rtrim(ONEH_UPLOAD_DIR, '/') . '/' . $name;

if (!oneh_watermark_uploaded_image($tmp, $target, $type)) {
    oneh_upload_fail('保存文件失败，请重试。', 500);
}
@chmod($target, 0644);

$src = ONEH_UPLOAD_URL . '/' . $name;
// 同步生成 640 / 1280 压缩版本，前台自动按屏幕加载合适尺寸
oneh_make_thumbs($src);
echo oneh_json_encode(['ok' => true, 'src' => $src, 'name' => $name]);
