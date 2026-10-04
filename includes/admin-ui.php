<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/inquiries.php';

/** 后台顶部导航（admin.php 与 admin-content.php 共用）。 */
function oneh_admin_nav(string $active): void
{
    $unread = oneh_is_admin() ? oneh_inquiry_unread_count() : 0;
    $items = [
        'projects' => ['admin.php', '项目与首页'],
        'content' => ['admin-content.php', '页面文案'],
        'inquiries' => ['admin-content.php?tab=inquiries', '留言' . ($unread > 0 ? '（' . $unread . '）' : '')],
        'tools' => ['admin-content.php?tab=tools', '工具'],
    ];
    ?>
    <header class="topbar">
      <div class="container topbar__inner">
        <a class="brand" href="index.php">
          <img class="brand__logo" src="img/new-logo.svg" alt="1+H Architecture">
          <span class="sr-only">1+H Architecture</span>
        </a>
        <nav class="nav" aria-label="Admin navigation">
          <div class="nav__links" data-nav-links>
<?php if (oneh_is_admin()): ?>
<?php foreach ($items as $key => [$href, $label]): ?>
            <a class="nav__link" href="<?= h($href) ?>"<?= $key === $active ? ' aria-current="page"' : '' ?>><?= h($label) ?></a>
<?php endforeach; ?>
            <a class="nav__link" href="index.php" target="_blank" rel="noopener">查看网站</a>
            <a class="nav__link" href="admin.php?logout=1">退出</a>
<?php else: ?>
            <a class="nav__link" href="index.php">Home</a>
<?php endif; ?>
          </div>
        </nav>
      </div>
    </header>
<?php
}
