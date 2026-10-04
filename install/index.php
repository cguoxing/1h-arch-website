<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/projects.php';

// 已安装（存在管理员账号）的站点，必须先登录后台才能使用本页，防止外人重复初始化。
$installed = oneh_db() !== null && (int) oneh_db_value("SELECT COUNT(*) FROM admins") > 0;
if ($installed && !oneh_is_admin()) {
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><meta charset="utf-8"><meta name="robots" content="noindex"><p style="font-family:sans-serif;padding:2rem">站点已初始化。如需升级数据表，请先 <a href="../admin.php">登录后台</a> 后再访问本页。</p>';
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($installed && !oneh_csrf_valid()) {
        $message = '页面已过期，请刷新后重试。';
    } elseif ($installed) {
        $message = oneh_install_schema(true) ? '数据表已升级到最新结构（不会改动已有内容）。' : '升级失败：请检查数据库配置。';
    } else {
        $message = oneh_seed_samples()
            ? '初始化完成：已创建数据表、后台账号和示例项目。请立即登录后台修改默认密码。'
            : '初始化失败：请先在 includes/config.php 填写正确的 MySQL 信息。';
    }
}
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Install | 1+H</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <main class="page">
    <section class="section">
      <div class="container">
        <span class="section__eyebrow">Install</span>
        <h1 class="section__title"><?= $installed ? '升级数据表' : '初始化动态站点' ?></h1>
        <p class="section__lead"><?= $installed ? '站点已安装。点击下方按钮可补齐新版本需要的数据表（例如联系表单留言表），不会删除或覆盖已有项目与文案。' : '先在 includes/config.php 填写西部数码 MySQL 数据库信息，然后点击初始化。' ?></p>
        <?php if ($message): ?><p class="notice"><?= h($message) ?></p><?php endif; ?>
        <form method="post">
          <?php if ($installed): ?><?= oneh_csrf_field() ?><?php endif; ?>
          <button class="button button--primary" type="submit"><?= $installed ? '升级数据表' : '创建数据表并导入示例数据' ?></button>
          <a class="button button--secondary" href="../admin.php">进入后台</a>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
