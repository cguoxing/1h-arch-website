<?php
declare(strict_types=1);

require_once __DIR__ . '/content.php';
require_once __DIR__ . '/images.php';

/** 静态资源 URL，自动带文件修改时间作版本号：改了文件浏览器就会拿新版，不用手动改 ?v=。 */
function oneh_asset(string $path): string
{
    $file = oneh_site_root() . '/' . ltrim($path, '/');
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return $path . '?v=' . $version;
}

function oneh_absolute_url(string $path = ''): string
{
    $base = rtrim((string) (oneh_get_content('site')['url'] ?? ''), '/');
    if ($path === '' || preg_match('#^https?://#i', $path)) {
        return $path === '' ? $base . '/' : $path;
    }
    return $base . '/' . ltrim($path, '/');
}

/**
 * 页面 <head> + 打开 body。
 * $page: [key, title, description, path, image, type, data(array 传给 JS), scripts(array), jsonld(array)]
 */
function oneh_render_head(array $page): void
{
    $site = oneh_get_content('site');
    $title = $page['title'] ?? $site['name'];
    $description = $page['description'] ?? $site['description'];
    $canonical = oneh_absolute_url($page['path'] ?? '');
    $image = oneh_absolute_url($page['image'] ?? $site['og_image']);
    $key = $page['key'] ?? 'page';
    $scripts = !empty($page['admin']) ? ($page['scripts'] ?? []) : array_merge(['site-main.js', 'motion-reference.js'], $page['scripts'] ?? []);
    $jsonld = $page['jsonld'] ?? null;
    $noindex = !empty($page['noindex']);
    ?>
<!doctype html>
<html lang="<?= h($page['lang'] ?? 'en') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?></title>
  <meta name="description" content="<?= h($description) ?>">
<?php if ($noindex): ?>
  <meta name="robots" content="noindex, nofollow">
<?php else: ?>
  <link rel="canonical" href="<?= h($canonical) ?>">
  <meta property="og:site_name" content="<?= h($site['name']) ?>">
  <meta property="og:type" content="<?= h($page['type'] ?? 'website') ?>">
  <meta property="og:title" content="<?= h($title) ?>">
  <meta property="og:description" content="<?= h($description) ?>">
  <meta property="og:url" content="<?= h($canonical) ?>">
  <meta property="og:image" content="<?= h($image) ?>">
  <meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
  <meta name="theme-color" content="#101010">
  <link rel="icon" type="image/svg+xml" href="<?= h(oneh_asset('img/favicon-h.svg')) ?>">
<?php if (!empty($page['preload'])): ?>
  <link rel="preload" as="image" href="<?= h($page['preload']) ?>" fetchpriority="high">
<?php endif; ?>
  <link rel="stylesheet" href="<?= h(oneh_asset('styles.css')) ?>">
<?php if (empty($page['admin'])): ?>
  <link rel="stylesheet" href="<?= h(oneh_asset('motion-reference.css')) ?>">
<?php endif; ?>
<?php if (!empty($page['data'])): ?>
  <script>window.oneHConfig = <?= oneh_json_encode($page['data']) ?>;</script>
<?php endif; ?>
<?php foreach ($scripts as $script): ?>
  <script defer src="<?= h(oneh_asset($script)) ?>"></script>
<?php endforeach; ?>
<?php if (empty($page['admin'])): ?>
  <script type="speculationrules">{"prefetch":[{"where":{"and":[{"href_matches":"/*"},{"not":{"href_matches":["/admin*","/api/*","/install/*"]}}]},"eagerness":"moderate"}]}</script>
<?php endif; ?>
<?php if ($jsonld): ?>
  <script type="application/ld+json"><?= oneh_json_encode($jsonld) ?></script>
<?php endif; ?>
<?= $page['head_extra'] ?? '' ?>
</head>
<body data-page="<?= h($key) ?>">
  <a class="skip-link" href="#content">Skip to content</a>
  <div class="site-shell">
<?php
}

function oneh_nav_items(): array
{
    return [
        'home' => ['index.php', 'Home'],
        'about' => ['about.php', 'About'],
        'projects' => ['projects.php', 'Projects'],
        'contact' => ['contact.php', 'Contact'],
    ];
}

function oneh_render_header(string $active): void
{
    $navId = 'site-nav';
    ?>
    <header class="topbar">
      <div class="container topbar__inner">
        <a class="brand" href="index.php">
          <img class="brand__logo" src="<?= h(oneh_asset('img/new-logo.svg')) ?>" alt="1+H Architecture">
          <span class="sr-only">1+H Architecture</span>
        </a>
        <nav class="nav" aria-label="Primary navigation">
          <button class="nav__toggle" type="button" data-nav-toggle aria-label="Open menu" aria-expanded="false" aria-controls="<?= $navId ?>">
            <span class="nav__toggle-lines"><span></span></span>
          </button>
          <div class="nav__links" id="<?= $navId ?>" data-nav-links>
<?php foreach (oneh_nav_items() as $key => [$href, $label]): ?>
            <a class="nav__link" href="<?= h($href) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= h($label) ?></a>
<?php endforeach; ?>
          </div>
        </nav>
      </div>
    </header>
<?php
}

function oneh_render_footer(string $tagline = ''): void
{
    $site = oneh_get_content('site');
    ?>
    <footer class="footer">
      <div class="container footer__inner">
        <p><?= h($site['name']) ?> &copy; <?= date('Y') ?><?php if ($site['icp'] !== ''): ?> | <a class="footer__icp" href="<?= h($site['icp_url'] ?: 'https://beian.miit.gov.cn/') ?>" target="_blank" rel="noopener noreferrer"><?= h($site['icp']) ?></a><?php endif; ?></p>
<?php if ($tagline !== ''): ?>
        <p><?= h($tagline) ?></p>
<?php endif; ?>
      </div>
    </footer>
  </div>
</body>
</html>
<?php
}

/** 节标题（eyebrow + 标题 + 中文 + 导语），与原有 section__head 结构一致。 */
function oneh_section_head(array $section, bool $withLead = true): void
{
    ?>
          <div class="section__head">
            <div>
<?php if (($section['eyebrow'] ?? '') !== ''): ?>
              <span class="section__eyebrow"><?= h($section['eyebrow']) ?></span>
<?php endif; ?>
              <h2 class="section__title"><?= h($section['title'] ?? '') ?><?php if (($section['title_cn'] ?? '') !== ''): ?> <span class="cn"><?= h($section['title_cn']) ?></span><?php endif; ?></h2>
            </div>
<?php if ($withLead && ($section['lead'] ?? '') !== ''): ?>
            <p class="section__lead"><?= h($section['lead']) ?></p>
<?php endif; ?>
          </div>
<?php
}

/** 随机项目配图池（About / Contact / Projects 顶部大图用），只传图片与标题，不再把整库数据塞进页面。 */
function oneh_visual_pool(array $projects, int $limit = 8): array
{
    $pool = [];
    foreach ($projects as $project) {
        $sources = array_column($project['images'] ?? [], 'src') ?: [$project['image'] ?? ''];
        foreach (array_filter($sources) as $src) {
            $pool[] = ['src' => oneh_img_url($src), 'label' => trim(($project['category'] ?? '') . ' / ' . ($project['title'] ?? ''), ' /')];
        }
    }
    shuffle($pool);
    return array_slice($pool, 0, $limit);
}

/** 原页面中的“品牌大图”占位块：服务端直接选一张图，避免页面加载后再闪一下。 */
function oneh_random_visual(array $projects, string $fallbackLabel, string $modifier = 'ph-img--hero'): string
{
    $pool = oneh_visual_pool($projects, 1);
    $item = $pool[0] ?? null;
    $style = $item ? ' style="background-image:url(\'' . h($item['src']) . '\')"' : '';
    $label = $item ? $item['label'] : $fallbackLabel;
    return '<div class="ph-img ' . h($modifier) . '"' . $style . ' role="img" aria-label="' . h($label) . '"><span class="ph-img__label">' . h($label) . '</span></div>';
}
