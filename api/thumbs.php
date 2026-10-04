<?php
declare(strict_types=1);

/** 后台「工具」页批量生成图片压缩版本：每次处理一小批，避免虚拟主机超时。 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/images.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (!oneh_is_admin()) {
    http_response_code(403);
    echo oneh_json_encode(['ok' => false, 'error' => '未登录或会话已过期。']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !oneh_csrf_valid()) {
    http_response_code(400);
    echo oneh_json_encode(['ok' => false, 'error' => '请求无效，请刷新页面后重试。']);
    exit;
}

@set_time_limit(60);
$sources = oneh_all_image_sources();
$total = count($sources);
$offset = max(0, (int) ($_POST['offset'] ?? 0));
$started = microtime(true);
$created = 0;

while ($offset < $total && microtime(true) - $started < 12) {
    $created += oneh_make_thumbs($sources[$offset]);
    $offset++;
}

$done = $offset >= $total;
$missing = 0;
if ($done) {
    foreach ($sources as $src) {
        if (count(oneh_thumb_variants($src)) < count(ONEH_THUMB_WIDTHS)) {
            $missing++;
        }
    }
}

echo oneh_json_encode(['ok' => true, 'next' => $offset, 'total' => $total, 'done' => $done, 'created' => $created, 'missing' => $missing]);
