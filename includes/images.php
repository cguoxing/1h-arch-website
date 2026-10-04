<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * 响应式图片：为本地图片生成 640 / 1280 宽的压缩版本（WebP，不支持时用 JPG），
 * 存放在 project-uploads/_thumbs/。页面输出 <img> 时如果缩略图已存在就自动加 srcset，
 * 浏览器按屏幕尺寸只下载需要的那一张，大幅减少首页和项目页的流量。
 */
const ONEH_THUMB_WIDTHS = [640, 1280];

function oneh_site_root(): string
{
    return dirname(__DIR__);
}

function oneh_thumb_dir(): string
{
    return rtrim(ONEH_UPLOAD_DIR, '/') . '/_thumbs';
}

/** 只接受站内 img/ 或 project-uploads/ 下的相对路径，返回磁盘绝对路径。 */
function oneh_local_image_path(string $src): ?string
{
    $src = strtok($src, '?#') ?: '';
    if ($src === '' || strpos($src, '..') !== false || !preg_match('#^(img|project-uploads)/[^\s]+\.(jpe?g|png|webp|gif)$#i', $src)) {
        return null;
    }
    if (strpos($src, 'project-uploads/_thumbs/') === 0) {
        return null;
    }
    $path = oneh_site_root() . '/' . $src;
    return is_file($path) ? $path : null;
}

function oneh_thumb_ext(): string
{
    static $ext = null;
    if ($ext === null) {
        $ext = function_exists('imagewebp') ? 'webp' : 'jpg';
    }
    return $ext;
}

function oneh_thumb_name(string $src, int $width): string
{
    $base = preg_replace('/[^a-z0-9_-]+/i', '-', pathinfo($src, PATHINFO_FILENAME)) ?: 'img';
    return substr($base, 0, 48) . '-' . substr(md5($src), 0, 8) . '-' . $width . '.' . oneh_thumb_ext();
}

/** @return array<int,string> 宽度 => URL（仅包含已经生成的版本） */
function oneh_thumb_variants(string $src): array
{
    static $cache = [];
    if (isset($cache[$src])) {
        return $cache[$src];
    }
    $variants = [];
    if (oneh_local_image_path($src) !== null) {
        foreach (ONEH_THUMB_WIDTHS as $width) {
            $name = oneh_thumb_name($src, $width);
            if (is_file(oneh_thumb_dir() . '/' . $name)) {
                $variants[$width] = rtrim(ONEH_UPLOAD_URL, '/') . '/_thumbs/' . $name;
            }
        }
    }
    return $cache[$src] = $variants;
}

function oneh_gd_memory_ok(int $width, int $height): bool
{
    $limit = trim((string) ini_get('memory_limit'));
    if ($limit === '' || $limit === '-1') {
        return true;
    }
    $unit = strtolower(substr($limit, -1));
    $bytes = (float) $limit;
    if ($unit === 'g') {
        $bytes *= 1024 ** 3;
    } elseif ($unit === 'm') {
        $bytes *= 1024 ** 2;
    } elseif ($unit === 'k') {
        $bytes *= 1024;
    }
    // 原图解码约 5 字节/像素，再加 1280 宽画布与余量
    $needed = $width * $height * 5 + 1280 * 1280 * 5 + 8 * 1024 * 1024;
    return memory_get_usage(true) + $needed < $bytes;
}

/** 生成缩略图；返回新生成的文件数。已存在的会跳过。 */
function oneh_make_thumbs(string $src): int
{
    if (!extension_loaded('gd')) {
        return 0;
    }
    $path = oneh_local_image_path($src);
    if ($path === null) {
        return 0;
    }
    $info = @getimagesize($path);
    if (!$info) {
        return 0;
    }
    [$srcW, $srcH] = $info;
    // 内存不够时跳过（虚拟主机 memory_limit 通常 128M），保留原图使用
    if (!oneh_gd_memory_ok((int) $srcW, (int) $srcH)) {
        return 0;
    }
    $dir = oneh_thumb_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
        return 0;
    }

    $created = 0;
    $image = null;
    foreach (ONEH_THUMB_WIDTHS as $width) {
        $target = $dir . '/' . oneh_thumb_name($src, $width);
        if (is_file($target)) {
            continue;
        }
        if ($image === null) {
            switch ($info[2]) {
                case IMAGETYPE_JPEG: $image = @imagecreatefromjpeg($path); break;
                case IMAGETYPE_PNG: $image = @imagecreatefrompng($path); break;
                case IMAGETYPE_WEBP: $image = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false; break;
                case IMAGETYPE_GIF: $image = @imagecreatefromgif($path); break;
                default: $image = false;
            }
            if (!$image) {
                return $created;
            }
        }
        $newW = min($width, $srcW);
        $newH = (int) max(1, round($srcH * $newW / $srcW));
        $canvas = imagecreatetruecolor($newW, $newH);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $newW, $newH, $srcW, $srcH);
        $ok = oneh_thumb_ext() === 'webp' ? imagewebp($canvas, $target, 80) : imagejpeg($canvas, $target, 82);
        imagedestroy($canvas);
        if ($ok) {
            @chmod($target, 0644);
            $created++;
        }
    }
    if ($image) {
        imagedestroy($image);
    }
    return $created;
}

/**
 * 输出 <img>。$opts: class, sizes, lazy(bool, 默认 true), priority(bool), attrs(string 额外属性)
 */
function oneh_img(string $src, string $alt, array $opts = []): string
{
    $variants = oneh_thumb_variants($src);
    $lazy = $opts['lazy'] ?? true;
    $attrs = ['src="' . h($src) . '"', 'alt="' . h($alt) . '"'];
    if ($variants) {
        $set = [];
        foreach ($variants as $width => $url) {
            $set[] = h($url) . ' ' . $width . 'w';
        }
        $path = oneh_local_image_path($src);
        $size = $path ? @getimagesize($path) : false;
        if ($size && $size[0] > max(array_keys($variants))) {
            $set[] = h($src) . ' ' . (int) $size[0] . 'w';
        }
        $attrs[0] = 'src="' . h(end($variants)) . '"';
        $attrs[] = 'srcset="' . implode(', ', $set) . '"';
        $attrs[] = 'sizes="' . h($opts['sizes'] ?? '100vw') . '"';
    }
    if (!empty($opts['class'])) {
        $attrs[] = 'class="' . h($opts['class']) . '"';
    }
    if (!empty($opts['priority'])) {
        $attrs[] = 'fetchpriority="high"';
    } elseif ($lazy) {
        $attrs[] = 'loading="lazy"';
    }
    $attrs[] = 'decoding="async"';
    if (!empty($opts['attrs'])) {
        $attrs[] = $opts['attrs'];
    }
    return '<img ' . implode(' ', $attrs) . '>';
}

/** 给 JS 切换图片时用：返回中等尺寸版本（没有就原图）。 */
function oneh_img_url(string $src, int $preferWidth = 1280): string
{
    $variants = oneh_thumb_variants($src);
    if (!$variants) {
        return $src;
    }
    foreach ($variants as $width => $url) {
        if ($width >= $preferWidth) {
            return $url;
        }
    }
    return end($variants);
}

/** 站内所有需要缩略图的图片路径（静态 img/ + 项目图 + 团队照片 + 文案配图）。 */
function oneh_all_image_sources(): array
{
    $sources = [];
    foreach (glob(oneh_site_root() . '/img/*.{jpg,jpeg,png,webp}', GLOB_BRACE) ?: [] as $file) {
        $sources[] = 'img/' . basename($file);
    }
    foreach (glob(rtrim(ONEH_UPLOAD_DIR, '/') . '/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG}', GLOB_BRACE) ?: [] as $file) {
        $sources[] = rtrim(ONEH_UPLOAD_URL, '/') . '/' . basename($file);
    }
    return array_values(array_unique($sources));
}
