<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/admin-ui.php';
require_once __DIR__ . '/includes/layout.php';

$message = '';
$error = '';

// 所有后台表单都校验 CSRF 令牌，防止被第三方页面冒用登录状态提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !oneh_csrf_valid()) {
    $error = '页面已过期，请刷新后重试。';
    $_POST = [];
}

function oneh_project_id_exists(string $id): bool
{
    return (int) oneh_db_value("SELECT COUNT(*) FROM projects WHERE id = ?", 's', [$id]) > 0;
}

function oneh_unique_project_id(string $baseId, string $originalId = ''): string
{
    $baseId = oneh_slugify($baseId ?: 'project');
    if ($originalId !== '' && $baseId === $originalId) {
        return $baseId;
    }
    if (!oneh_project_id_exists($baseId)) {
        return $baseId;
    }

    $index = 2;
    do {
        $candidate = $baseId . '-' . $index;
        $index++;
    } while (oneh_project_id_exists($candidate));

    return $candidate;
}

// 新增项目：prj- + 7 位随机数字，且保证数据库内唯一（生成即查重）
function oneh_generate_project_id(): string
{
    do {
        $num = random_int(0, 9999999);
        $id = 'prj-' . str_pad((string) $num, 7, '0', STR_PAD_LEFT);
    } while (oneh_project_id_exists($id));
    return $id;
}

if (isset($_GET['logout'])) {
    oneh_logout();
    oneh_redirect('admin.php?login=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if (oneh_login((string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''))) {
        oneh_redirect('admin.php');
    }
    $error = '登录失败，请检查用户名和密码。';
}

$dbReady = oneh_db_configured() && oneh_install_schema();

if ($dbReady && oneh_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_project'])) {
    $formMode = (string) ($_POST['form_mode'] ?? 'create');
    $originalId = $formMode === 'edit' ? oneh_slugify((string) ($_POST['original_id'] ?? '')) : '';
    if ($formMode === 'edit' && $originalId !== '') {
        // 编辑已有项目：保留原 ID（id 字段与 original_id 相同，原地更新）
        $id = oneh_unique_project_id(oneh_slugify((string) ($_POST['id'] ?? '')), $originalId);
    } else {
        // 新增项目：忽略任何残留 original_id；仅当表单 ID 已存在时重新生成，避免覆盖旧项目。
        $requestedId = oneh_slugify((string) ($_POST['id'] ?? ''));
        if ($requestedId === '' || oneh_project_id_exists($requestedId)) {
            $id = oneh_generate_project_id();
        } else {
            $id = $requestedId;
        }
    }
    // 图片已由 api/upload.php 异步上传完成，images_json 里全是真实路径（不含 blob:/pending）
    $postedImages = json_decode((string) ($_POST['images_json'] ?? '[]'), true);
    $images = [];
    if (is_array($postedImages)) {
        foreach ($postedImages as $image) {
            if (!is_array($image)) {
                continue;
            }
            $src = (string) ($image['src'] ?? '');
            if ($src === '' || strpos($src, 'blob:') === 0) {
                continue;
            }
            $images[] = ['src' => $src, 'caption' => (string) ($image['caption'] ?? '')];
        }
    }
    // 服务端兜底：每个项目最多 10 张
    if (count($images) > 10) {
        $images = array_slice($images, 0, 10);
    }
    $primaryImage = $images[0]['src'] ?? (string) ($_POST['image'] ?? '');
    $types = array_values(array_filter((array) ($_POST['types'] ?? [])));
    $project = [
        'id' => $id,
        'title' => trim((string) ($_POST['title'] ?? '')),
        'category' => trim((string) ($_POST['category'] ?? '')),
        'type' => $types[0] ?? 'renovation',
        'types' => $types ?: ['renovation'],
        'city' => trim((string) ($_POST['city'] ?? '')),
        'year' => trim((string) ($_POST['year'] ?? '')),
        'area' => trim((string) ($_POST['area'] ?? '')),
        'role' => trim((string) ($_POST['role'] ?? '')),
        'image' => $primaryImage,
        'link' => 'project.php?project=' . $id,
        'wechat_link' => trim((string) ($_POST['wechat_link'] ?? '')),
        'summary' => trim((string) ($_POST['summary'] ?? '')),
        'lead_text' => trim((string) ($_POST['lead_text'] ?? '')),
        'overview_title' => trim((string) ($_POST['overview_title'] ?? '')),
        'overview' => trim((string) ($_POST['overview'] ?? '')),
        'status' => (string) ($_POST['status'] ?? 'draft'),
        'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
        'sort_order' => (int) ($_POST['sort_order'] ?? 100),
        'images' => $images,
    ];
    if ($project['category'] === '') {
        $labels = oneh_type_labels();
        $project['category'] = implode(' / ', array_map(function ($type) use ($labels) {
            return $labels[$type] ?? $type;
        }, $project['types']));
    }
    $saved = oneh_save_project($project, $formMode === 'edit' && $originalId !== '');
    if ($saved && $originalId !== '' && $originalId !== $id) {
        $db = oneh_db();
        if ($db) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->bind_param('s', $originalId);
            $stmt->execute();
            $stmt = $db->prepare("DELETE FROM project_images WHERE project_id = ?");
            $stmt->bind_param('s', $originalId);
            $stmt->execute();
        }
    }
    $message = $saved ? '项目已保存。项目库不限制数量，新增项目不会自动顶替旧项目。' : '项目保存失败。';
}

if ($dbReady && oneh_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_project'])) {
    $id = (string) ($_POST['id'] ?? '');
    $db = oneh_db();
    if ($db && $id !== '') {
        $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $stmt = $db->prepare("DELETE FROM project_images WHERE project_id = ?");
        $stmt->bind_param('s', $id);
        $stmt->execute();
        $message = '项目已删除。';
    }
}

if ($dbReady && oneh_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $representativeByType = [];
    foreach ((array) ($_POST['representativeByType'] ?? []) as $type => $projectId) {
        if ($projectId !== '') {
            $representativeByType[$type] = $projectId;
        }
    }
    $representativeByYear = [];
    foreach ((array) ($_POST['representativeByYear'] ?? []) as $period => $projectId) {
        if ($projectId !== '') {
            $representativeByYear[$period] = $projectId;
        }
    }
    oneh_save_setting('homeHeroIds', array_slice(array_values(array_filter((array) ($_POST['homeHeroIds'] ?? []))), 0, 5));
    oneh_save_setting('projectIndexIds', array_values(array_filter((array) ($_POST['projectIndexIds'] ?? []))));
    oneh_save_setting('selectedWorks', [
        'featuredId' => (string) ($_POST['featuredId'] ?? ''),
        'smallIds' => array_values(array_filter((array) ($_POST['selectedSmallIds'] ?? []))),
    ]);
    oneh_save_setting('projectsPage', [
        'typeFilters' => array_values(array_filter((array) ($_POST['typeFilters'] ?? []))),
        'yearFilters' => array_values(array_filter((array) ($_POST['yearFilters'] ?? []))),
        'defaultYearFilter' => (string) ($_POST['defaultYearFilter'] ?? '2016-2020'),
        'representativeByType' => $representativeByType,
        'representativeByYear' => $representativeByYear,
    ]);
    $message = '页面配置已保存。';
}

if ($dbReady && oneh_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rotation_settings'])) {
    oneh_save_setting('randomHeroEnabled', isset($_POST['randomHeroEnabled']) ? 1 : 0);
    $heroFixedId = trim((string)($_POST['heroFixedId'] ?? '259m'));
    oneh_save_setting('heroFixedId', $heroFixedId === '' ? '259m' : $heroFixedId);
    oneh_save_setting('heroFixedPos', (int)($_POST['heroFixedPos'] ?? 0));
    oneh_save_setting('heroInterval', (int)($_POST['heroInterval'] ?? 3));
    oneh_save_setting('randomIndexEnabled', isset($_POST['randomIndexEnabled']) ? 1 : 0);
    oneh_save_setting('indexInterval', (int)($_POST['indexInterval'] ?? 5));
    oneh_save_setting('randomSelectedEnabled', isset($_POST['randomSelectedEnabled']) ? 1 : 0);
    oneh_save_setting('selectedFeaturedInterval', (int)($_POST['selectedFeaturedInterval'] ?? 7));
    oneh_save_setting('selectedSmallInterval', (int)($_POST['selectedSmallInterval'] ?? 4));
    if (isset($_POST['force_home_refresh'])) {
        oneh_save_setting('last_update_homeHero', 0);
        oneh_save_setting('last_update_projectIndex', 0);
        oneh_save_setting('last_update_selectedFeatured', 0);
        oneh_save_setting('last_update_selectedSmall', 0);
        oneh_save_setting('homeHero_ids', []);
        oneh_save_setting('projectIndex_ids', []);
        $message = '轮播配置已保存，并已触发首页立即刷新。';
    } else {
        $message = '轮播配置已保存。';
    }
}

if ($dbReady && oneh_is_admin() && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_team'])) {
    $collectTeam = function (string $key): array {
        $rows = (array) ($_POST[$key] ?? []);
        $members = [];
        $count = max(
            count((array) ($rows['name'] ?? [])),
            count((array) ($rows['label'] ?? [])),
            count((array) ($rows['title'] ?? [])),
            count((array) ($rows['photo'] ?? []))
        );
        for ($index = 0; $index < $count; $index++) {
            $member = [
                'name' => trim((string) ($rows['name'][$index] ?? '')),
                'label' => trim((string) ($rows['label'][$index] ?? '')),
                'title' => trim((string) ($rows['title'][$index] ?? '')),
                'photo' => trim((string) ($rows['photo'][$index] ?? '')),
            ];
            if ($member['name'] === '' && $member['label'] === '' && $member['title'] === '' && $member['photo'] === '') {
                continue;
            }
            $members[] = $member;
        }
        return $members;
    };

    oneh_save_setting('team', [
        'founders' => $collectTeam('team_founders'),
        'designTeam' => $collectTeam('team_design'),
    ]);
    $message = '合伙人与设计团队信息已保存。';
}

$isEditing = isset($_GET['edit']);
$editingProject = $isEditing ? oneh_get_project((string) $_GET['edit'], true) : null;
$projects = $dbReady && oneh_is_admin() ? oneh_get_projects(true) : [];
$settings = $dbReady ? oneh_get_settings() : oneh_default_settings();
$teamSettings = $settings['team'] ?? oneh_default_settings()['team'];
$typeLabels = oneh_type_labels();
$periodLabels = oneh_period_labels();
$loginMode = !oneh_is_admin();
?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="1+H website content management page.">
  <title>Website Admin | 1+H Integrated Design</title>
  <meta name="robots" content="noindex, nofollow">
  <meta name="csrf-token" content="<?= h(oneh_csrf_token()) ?>">
  <link rel="icon" type="image/svg+xml" href="<?= h(oneh_asset('img/favicon-h.svg')) ?>">
  <link rel="stylesheet" href="<?= h(oneh_asset('styles.css')) ?>">
  <style>
    body[data-page="admin"] { background: var(--bg); }
    .admin-shell { padding: 78px 0 var(--section-y-desktop); }
    .admin-panel { padding: clamp(16px, 2vw, 24px); border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--surface); margin-bottom: var(--space-4); }
    .admin-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(360px, 0.42fr); gap: var(--space-4); align-items: start; }
    .admin-main { display: grid; gap: var(--space-4); }
    .admin-side { position: sticky; top: 92px; display: grid; gap: var(--space-3); max-height: calc(100vh - 116px); overflow: auto; }
    .admin-form { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: var(--space-3); }
    .field { display: grid; gap: 6px; }
    .field--2 { grid-column: span 2; } .field--3 { grid-column: span 3; } .field--4 { grid-column: span 4; } .field--5 { grid-column: span 5; } .field--6 { grid-column: span 6; } .field--7 { grid-column: span 7; } .field--8 { grid-column: span 8; } .field--9 { grid-column: span 9; } .field--12 { grid-column: 1 / -1; }
    .field label, .check-group legend { color: var(--meta); font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: .12em; text-transform: uppercase; }
    .field input, .field select, .field textarea { width: 100%; min-height: 42px; padding: 9px 11px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); color: var(--fg); font-size: 15px; }
    .field--title input { min-height: 52px; font-size: 1.15rem; font-weight: 600; }
    .field textarea { min-height: 92px; resize: vertical; }
    .field-note { margin: 0; color: var(--meta); font-size: var(--text-sm); line-height: 1.5; }
    .check-group { grid-column: 1 / -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px; padding: 0; border: 0; }
    .check { display: flex; gap: 8px; align-items: center; padding: 8px 10px; border: 1px solid var(--border); border-radius: var(--radius-sm); background: var(--bg); }
    .admin-project-list { display: grid; gap: 4px; }
    .admin-project-row { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; gap: 6px; align-items: center; min-height: 42px; padding: 7px 8px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); cursor: grab; }
    .admin-project-row:active { cursor: grabbing; }
    .admin-project-row strong { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: var(--text-sm); }
    .admin-project-row small { color: var(--meta); }
    .admin-project-row .button { min-height: 30px; padding: 5px 8px; font-size: var(--text-xs); }
    .admin-search { display: grid; grid-template-columns: minmax(0, 1fr) 42px; align-items: stretch; }
    .admin-search input { border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 0; }
    .admin-search .search-button { min-width: 42px; min-height: 42px; }
    .admin-message { margin: 0 0 var(--space-3); color: var(--accent); }
    .admin-error { margin: 0 0 var(--space-3); color: #b42318; }
    .image-manager { display: grid; gap: var(--space-2); }
    .image-dropzone { display: grid; place-items: center; min-height: 124px; padding: var(--space-3); border: 1px dashed var(--border); border-radius: var(--radius-sm); background: color-mix(in oklab, var(--surface), white 3%); text-align: center; }
    .image-dropzone.is-dragover { border-color: var(--accent); background: color-mix(in oklab, var(--accent), transparent 92%); }
    .image-actions { display: flex; flex-wrap: wrap; gap: var(--space-2); align-items: center; }
    .image-list { display: grid; gap: 8px; }
    .image-row { display: grid; grid-template-columns: 112px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 9px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); }
    .image-row.is-error { border-color: #b42318; background: color-mix(in oklab, #b42318, transparent 92%); }
    .image-status { display: inline-flex; align-items: center; gap: 5px; margin-left: 8px; font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: .04em; }
    .image-status--uploading { color: var(--meta); }
    .image-status--ok { color: #15803d; }
    .image-status--error { color: #b42318; }
    .image-status__retry { min-height: 26px; padding: 3px 7px; font-size: var(--text-xs); border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--surface); color: var(--fg); cursor: pointer; }
    .image-count { color: var(--meta); font-family: var(--font-mono); font-size: var(--text-xs); letter-spacing: .12em; text-transform: uppercase; }
    .image-row.is-dragging { opacity: .45; }
    .image-row img { width: 112px; aspect-ratio: 4 / 3; object-fit: cover; border-radius: var(--radius-sm); background: var(--surface); }
    .image-row input { width: 100%; min-height: 36px; padding: 7px 9px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--surface); color: var(--fg); }
    .image-row__actions { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
    .drop-target { border: 1px dashed var(--border); border-radius: var(--radius-sm); padding: var(--space-2); min-height: 58px; background: var(--bg); }
    .drop-target.is-dragover { border-color: var(--accent); background: color-mix(in oklab, var(--accent), transparent 92%); }
    .drop-target.is-active-target { border-color: var(--accent); box-shadow: inset 0 0 0 1px color-mix(in oklab, var(--accent), transparent 35%); }
    .selection-list { display: grid; gap: 6px; min-height: 38px; }
    .selection-item { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; gap: 8px; align-items: center; padding: 7px 8px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--surface); }
    .selection-item strong { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: var(--text-sm); }
    .selection-item__index { color: var(--meta); font-family: var(--font-mono); font-size: var(--text-xs); }
    .selection-remove { min-height: 28px; padding: 4px 8px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); color: var(--fg); }
    .selection-empty { margin: 0; color: var(--meta); font-size: var(--text-sm); }
    .settings-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: var(--space-3); grid-column: 1 / -1; }
    .settings-block { display: grid; gap: var(--space-2); align-content: start; padding: var(--space-3); border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); }
    .settings-block h3 { margin: 0 0 var(--space-1); font-size: 1rem; }
.settings-block label { display: block; color: var(--fg); font-family: inherit; font-size: var(--text-sm); letter-spacing: normal; text-transform: none; }
    .settings-block select { min-height: 40px; }
    .team-admin { display: grid; gap: var(--space-3); }
    .team-admin__section { display: grid; gap: var(--space-2); padding: var(--space-3); border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); }
    .team-admin__section h3 { margin: 0; }
    .team-admin__list { display: grid; gap: 10px; }
    .team-admin__row { display: grid; grid-template-columns: 96px repeat(3, minmax(0, 1fr)) auto; gap: 8px; align-items: center; padding: 9px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--surface); }
    .team-admin__photo { display: grid; gap: 6px; }
    .team-admin__photo img { width: 96px; aspect-ratio: 4 / 3; object-fit: cover; border-radius: var(--radius-sm); background: var(--bg); }
    .team-admin__photo input[type="file"] { width: 96px; font-size: var(--text-xs); }
    .team-admin__row input { width: 100%; min-height: 38px; padding: 7px 9px; border: 1px solid var(--border-soft); border-radius: var(--radius-sm); background: var(--bg); color: var(--fg); }
    .team-admin__actions { display: flex; flex-wrap: wrap; gap: var(--space-2); }
    @media (max-width: 1100px) { .admin-grid { grid-template-columns: 1fr; } .admin-side { position: static; max-height: none; } }
    @media (max-width: 900px) { .admin-form, .settings-grid { grid-template-columns: 1fr; } .field, .field--2, .field--3, .field--4, .field--5, .field--6, .field--7, .field--8, .field--9, .field--12 { grid-column: 1 / -1; } .image-row { grid-template-columns: 86px minmax(0, 1fr); } .image-row__actions { grid-column: 1 / -1; justify-content: flex-start; } .team-admin__row { grid-template-columns: 86px minmax(0, 1fr); } .team-admin__row > button { grid-column: 1 / -1; } }
  </style>
</head>
<body data-page="admin">
  <a class="skip-link" href="#content">Skip to content</a>
  <div class="site-shell">
<?php oneh_admin_nav('projects'); ?>

    <main class="page admin-shell" id="content">
      <div class="container">
        <span class="section__eyebrow">Website Admin</span>
        <h1 class="section__title">内容管理后台</h1>
        <p class="section__lead">项目数据保存到 MySQL，前台只显示状态为 Published 的项目；示例项目可保留为 Sample/Hidden。</p>

        <?php if ($message): ?><p class="admin-message"><?= h($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="admin-error"><?= h($error) ?></p><?php endif; ?>

        <?php if (!$dbReady): ?>
          <section class="admin-panel">
            <h2>数据库未配置</h2>
            <p>请先在 <code>includes/config.php</code> 填写西部数码 MySQL 信息，然后访问 <a href="install/">install/</a> 初始化数据表。</p>
          </section>
        <?php elseif ($loginMode): ?>
          <section class="admin-panel">
            <h2>登录</h2>
            <form class="admin-form" method="post">
                <?= oneh_csrf_field() ?>
              <input type="hidden" name="login" value="1">
              <div class="field field--6"><label>用户名</label><input name="username" autocomplete="username" required></div>
              <div class="field field--6"><label>密码</label><input name="password" type="password" autocomplete="current-password" required></div>
              <div class="field field--12"><button class="button button--primary" type="submit">登录后台</button></div>
            </form>
          </section>
        <?php else: ?>
          <div class="admin-grid">
            <div class="admin-main">
            <section class="admin-panel">
              <h2><?= $editingProject ? '编辑项目' : '新增项目' ?></h2>
              <?php $p = $editingProject ?: ['id' => '', 'title' => '', 'category' => '', 'types' => ['renovation'], 'city' => '', 'year' => date('Y'), 'area' => '', 'role' => '', 'image' => '', 'wechat_link' => '', 'summary' => '', 'lead_text' => '', 'overview_title' => '', 'overview' => '', 'status' => 'draft', 'is_featured' => 0, 'sort_order' => 100, 'images' => []];
              $displayId = $editingProject ? $p['id'] : oneh_generate_project_id(); ?>
              <form class="admin-form" method="post" enctype="multipart/form-data">
                <?= oneh_csrf_field() ?>
                <input type="hidden" name="save_project" value="1">
                <input type="hidden" name="form_mode" value="<?= $editingProject ? 'edit' : 'create' ?>">
                <input type="hidden" name="original_id" value="<?= h($p['id']) ?>">
                <input type="hidden" name="images_json" data-images-json value="<?= h(oneh_json_encode($p['images'])) ?>">
                <div class="field field--3"><label>项目 ID</label><input name="id" value="<?= h($displayId) ?>" readonly><p class="field-note">系统自动生成（prj- + 7 位数字），每次新建都唯一，不可修改。</p></div>
                <div class="field field--9 field--title"><label>项目名称</label><input name="title" value="<?= h($p['title']) ?>" required></div>
                <fieldset class="check-group">
                  <legend>项目类型</legend>
                  <?php foreach ($typeLabels as $value => $label): ?>
                    <label class="check"><input type="checkbox" name="types[]" value="<?= h($value) ?>" <?= in_array($value, $p['types'] ?? [], true) ? 'checked' : '' ?>><?= h($label) ?></label>
                  <?php endforeach; ?>
                </fieldset>
                <div class="field field--4"><label>类型显示名</label><input name="category" value="<?= h($p['category']) ?>"></div>
                <div class="field field--3"><label>城市</label><input name="city" value="<?= h($p['city']) ?>"></div>
                <div class="field field--3"><label>年份</label><input name="year" value="<?= h($p['year']) ?>"></div>
                <div class="field field--6"><label>尺度</label><input name="area" value="<?= h($p['area']) ?>"></div>
                <div class="field field--6"><label>角色</label><input name="role" value="<?= h($p['role']) ?>"></div>
                <div class="field field--4"><label>状态</label><select name="status">
                  <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'hidden' => 'Hidden', 'sample' => 'Sample'] as $value => $label): ?>
                    <option value="<?= h($value) ?>" <?= ($p['status'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                  <?php endforeach; ?>
                </select></div>
                <div class="field field--4"><label>排序</label><input name="sort_order" type="number" value="<?= h($p['sort_order']) ?>"><p class="field-note">数字越小越靠前；建议 10、20、30 这样留间隔，方便以后插入项目。</p></div>
                <label class="check field--4"><input type="checkbox" name="is_featured" <?= !empty($p['is_featured']) ? 'checked' : '' ?>>首页重点项目</label>
                <div class="field field--12"><label>主图路径</label><input name="image" value="<?= h($p['image']) ?>" placeholder="上传图片后自动生成，也可填写 img/xxx.jpg"></div>
                <div class="field field--12">
                  <label>项目图片</label>
                  <div class="image-manager" data-image-manager data-max-images="10">
                    <div class="image-dropzone" data-image-dropzone>
                      <div>
                        <strong>拖入或选择图片</strong>
                        <p class="field-note">选中后立即上传，每张会即时显示“上传成功/失败”。浏览器先压缩为 WebP/JPEG（最长边约 1800px）。第一张图片是主图，<strong>每个项目最多 10 张</strong>。</p>
                      </div>
                    </div>
                    <div class="image-actions">
                      <input type="file" accept="image/*" multiple data-image-input>
                      <button class="button button--secondary" type="button" data-clear-images>清空图片</button>
                      <span class="image-count" data-image-count>0 / 10</span>
                    </div>
                    <div class="image-list" data-image-list></div>
                  </div>
                </div>
                <div class="field field--12"><label>微信公众号项目链接</label><input name="wechat_link" value="<?= h($p['wechat_link']) ?>"></div>
                <div class="field field--12"><label>卡片短文案</label><input name="lead_text" value="<?= h($p['lead_text']) ?>"></div>
                <div class="field field--12"><label>Projects 卡片摘要</label><textarea name="summary"><?= h($p['summary']) ?></textarea></div>
                <div class="field field--12"><label>详情页标题</label><textarea name="overview_title"><?= h($p['overview_title']) ?></textarea></div>
                <div class="field field--12"><label>详情页正文</label><textarea name="overview"><?= h($p['overview']) ?></textarea></div>
                <div class="field field--12"><button class="button button--primary" type="submit">保存项目</button> <a class="button button--secondary" href="admin.php">新建项目</a></div>
              </form>
              <?php if ($editingProject): ?>
                <form method="post" style="margin-top: var(--space-2);">
                <?= oneh_csrf_field() ?>
                  <input type="hidden" name="delete_project" value="1">
                  <input type="hidden" name="id" value="<?= h($editingProject['id']) ?>">
                  <button class="button button--secondary" type="submit">删除当前项目</button>
                </form>
              <?php endif; ?>
            </section>

              <section class="admin-panel">
                <h2>首页动态轮播配置</h2>
                <form class="admin-form" method="post">
                <?= oneh_csrf_field() ?>
                  <input type="hidden" name="save_rotation_settings" value="1">
                  <div class="settings-grid">
                    <div class="settings-block">
                      <h3>Hero (轮播)</h3>
                      <label class="check"><input type="checkbox" name="randomHeroEnabled" <?= !empty($settings['randomHeroEnabled']) ? 'checked' : '' ?>> 开启随机</label>
                      <label>固定项目 ID: <input name="heroFixedId" value="<?= h($settings['heroFixedId'] ?? '259m') ?>"></label>
                      <label>固定位置 (0-4): <input name="heroFixedPos" type="number" min="0" max="4" value="<?= h($settings['heroFixedPos'] ?? 0) ?>"></label>
                      <label>更换间隔 (天): <input name="heroInterval" type="number" min="1" value="<?= h($settings['heroInterval'] ?? 3) ?>"></label>
                    </div>
                    <div class="settings-block">
                      <h3>Project Index</h3>
                      <label class="check"><input type="checkbox" name="randomIndexEnabled" <?= !empty($settings['randomIndexEnabled']) ? 'checked' : '' ?>> 开启随机</label>
                      <label>更换间隔 (天): <input name="indexInterval" type="number" min="1" value="<?= h($settings['indexInterval'] ?? 5) ?>"></label>
                    </div>
                    <div class="settings-block">
                      <h3>Selected Works</h3>
                      <label class="check"><input type="checkbox" name="randomSelectedEnabled" <?= !empty($settings['randomSelectedEnabled']) ? 'checked' : '' ?>> 开启随机</label>
                      <label>大图更换间隔 (天): <input name="selectedFeaturedInterval" type="number" min="1" value="<?= h($settings['selectedFeaturedInterval'] ?? 7) ?>"></label>
                      <label>小图更换间隔 (天): <input name="selectedSmallInterval" type="number" min="1" value="<?= h($settings['selectedSmallInterval'] ?? 4) ?>"></label>
                    </div>
                  </div>
                  <div class="field field--12">
                    <button class="button button--primary" type="submit">保存轮播配置</button>
                    <button class="button button--secondary" type="submit" name="force_home_refresh" value="1">强制立即刷新</button>
                  </div>
                </form>
              </section>
              <section class="admin-panel">
                <h2>页面配置</h2>
                <form class="admin-form" method="post">
                <?= oneh_csrf_field() ?>
                  <input type="hidden" name="save_settings" value="1">
                  <?php
                    $renderSelection = function (string $name, array $selected, bool $single = false) use ($projects): void {
                        $byId = [];
                        foreach ($projects as $project) {
                            $byId[$project['id']] = $project;
                        }
                        echo '<div class="selection-list" data-selection-list data-selection-name="' . h($name) . '">';
                        $index = 1;
                        foreach ($selected as $projectId) {
                            if (!isset($byId[$projectId])) {
                                continue;
                            }
                            $project = $byId[$projectId];
                            echo '<div class="selection-item" data-selection-id="' . h($project['id']) . '"><span class="selection-item__index">' . h(str_pad((string) $index, 2, '0', STR_PAD_LEFT)) . '</span><strong>' . h($project['title']) . '</strong><button class="selection-remove" type="button" data-selection-remove>删除</button><input type="hidden" name="' . h($name) . ($single ? '' : '[]') . '" value="' . h($project['id']) . '"></div>';
                            $index++;
                        }
                        echo '</div><p class="selection-empty" data-selection-empty>拖动右侧项目到这里</p>';
                    };
                  ?>
                  <fieldset class="drop-target field--12" data-drop-target="homeHeroIds" data-selection-name="homeHeroIds"><legend>首页轮播</legend><?php $renderSelection('homeHeroIds', $settings['homeHeroIds'] ?? []); ?></fieldset>
                  <fieldset class="drop-target field--12" data-drop-target="projectIndexIds" data-selection-name="projectIndexIds"><legend>Project Index</legend><?php $renderSelection('projectIndexIds', $settings['projectIndexIds'] ?? []); ?></fieldset>
                  <fieldset class="drop-target field--12" data-drop-target="featuredId" data-selection-name="featuredId" data-single-selection="true"><legend>Selected Works 大图</legend><?php $renderSelection('featuredId', array_filter([$settings['selectedWorks']['featuredId'] ?? '']), true); ?></fieldset>
                  <fieldset class="drop-target field--12" data-drop-target="selectedSmallIds" data-selection-name="selectedSmallIds"><legend>Selected Works 小图</legend><?php $renderSelection('selectedSmallIds', $settings['selectedWorks']['smallIds'] ?? []); ?></fieldset>
                  <fieldset class="check-group"><legend>Projects 类型筛选</legend><?php foreach ($typeLabels as $value => $label): ?><label class="check"><input type="checkbox" name="typeFilters[]" value="<?= h($value) ?>" <?= in_array($value, $settings['projectsPage']['typeFilters'] ?? [], true) ? 'checked' : '' ?>><?= h($label) ?></label><?php endforeach; ?></fieldset>
                  <fieldset class="check-group"><legend>Projects 年份筛选</legend><?php foreach ($periodLabels as $value => $label): ?><label class="check"><input type="checkbox" name="yearFilters[]" value="<?= h($value) ?>" <?= in_array($value, $settings['projectsPage']['yearFilters'] ?? [], true) ? 'checked' : '' ?>><?= h($label) ?></label><?php endforeach; ?></fieldset>
                  <div class="field field--12"><label>默认年份</label><select name="defaultYearFilter"><?php foreach ($periodLabels as $value => $label): ?><option value="<?= h($value) ?>" <?= ($settings['projectsPage']['defaultYearFilter'] ?? '') === $value ? 'selected' : '' ?>><?= h($label) ?></option><?php endforeach; ?></select></div>
                  <div class="field field--12">
                    <label>Projects 类型 / 年份典型案例</label>
                    <p class="field-note">这些项目会显示在 Projects 页面右侧最大详情卡片中。选择“自动选择”时，会取当前筛选后的第一个项目。</p>
                    <div class="settings-grid">
                      <?php foreach ($typeLabels as $value => $label): ?>
                        <div class="settings-block drop-target" data-drop-target="representativeByType[<?= h($value) ?>]" data-selection-name="representativeByType[<?= h($value) ?>]" data-single-selection="true">
                          <h3><?= h($label) ?> 类型卡片</h3>
                          <?php $renderSelection('representativeByType[' . $value . ']', array_filter([$settings['projectsPage']['representativeByType'][$value] ?? '']), true); ?>
                        </div>
                      <?php endforeach; ?>
                      <?php foreach ($periodLabels as $value => $label): ?>
                        <div class="settings-block drop-target" data-drop-target="representativeByYear[<?= h($value) ?>]" data-selection-name="representativeByYear[<?= h($value) ?>]" data-single-selection="true">
                          <h3><?= h($label) ?> 年份卡片</h3>
                          <?php $renderSelection('representativeByYear[' . $value . ']', array_filter([$settings['projectsPage']['representativeByYear'][$value] ?? '']), true); ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                  <div class="field field--12"><button class="button button--primary" type="submit">保存页面配置</button></div>
                </form>
              </section>

              <section class="admin-panel">
                <h2>合伙人及设计团队</h2>
                <form class="team-admin" method="post">
                <?= oneh_csrf_field() ?>
                  <input type="hidden" name="save_team" value="1">
                  <?php
                    $renderTeamRows = function (string $groupKey, array $members): void {
                        foreach ($members as $member) {
                            $photo = (string) ($member['photo'] ?? '');
                            echo '<div class="team-admin__row" data-team-row>';
                            echo '<div class="team-admin__photo"><img src="' . h($photo ?: 'img/t1.jpg') . '" alt="" data-team-preview><input type="file" accept="image/*" data-team-photo-input><input type="hidden" name="' . h($groupKey) . '[photo][]" value="' . h($photo) . '" data-team-photo></div>';
                            echo '<input name="' . h($groupKey) . '[name][]" value="' . h($member['name'] ?? '') . '" placeholder="姓名">';
                            echo '<input name="' . h($groupKey) . '[label][]" value="' . h($member['label'] ?? '') . '" placeholder="分类 / 标签">';
                            echo '<input name="' . h($groupKey) . '[title][]" value="' . h($member['title'] ?? '') . '" placeholder="职位">';
                            echo '<button class="button button--secondary" type="button" data-team-remove>删除</button>';
                            echo '</div>';
                        }
                    };
                  ?>
                  <div class="team-admin__section">
                    <h3>合伙人</h3>
                    <p class="field-note">可修改照片、姓名、标签与职位；照片上传后会自动写入左下角 © 1+H Integrated Design。</p>
                    <div class="team-admin__list" data-team-list="team_founders">
                      <?php $renderTeamRows('team_founders', is_array($teamSettings['founders'] ?? null) ? $teamSettings['founders'] : []); ?>
                    </div>
                    <div class="team-admin__actions">
                      <button class="button button--secondary" type="button" data-team-add="team_founders">增加合伙人</button>
                    </div>
                  </div>
                  <div class="team-admin__section">
                    <h3>设计团队</h3>
                    <p class="field-note">设计团队可增加、减少，并可分别维护照片和职位。</p>
                    <div class="team-admin__list" data-team-list="team_design">
                      <?php $renderTeamRows('team_design', is_array($teamSettings['designTeam'] ?? null) ? $teamSettings['designTeam'] : []); ?>
                    </div>
                    <div class="team-admin__actions">
                      <button class="button button--secondary" type="button" data-team-add="team_design">增加设计团队成员</button>
                    </div>
                  </div>
                  <div class="team-admin__actions">
                    <button class="button button--primary" type="submit">保存团队信息</button>
                  </div>
                </form>
              </section>
            </div>

            <aside class="admin-side">
              <section class="admin-panel">
                <h2>项目库</h2>
                <div class="field">
                  <div class="admin-search">
                    <input type="search" data-project-search placeholder="搜索项目名称 / 年份 / 类型">
                    <button class="search-button" type="button" data-project-search-button aria-label="搜索项目库"></button>
                  </div>
                </div>
                <p class="field-note">拖动项目到左侧首页轮播、Project Index 或 Selected Works 小图区域，可快速勾选；项目越来越多时，这里作为素材库使用。</p>
                <div class="admin-project-list">
                  <?php foreach ($projects as $project): ?>
                    <?php
                      $projectSearchText = implode(' ', array_filter([
                          $project['title'] ?? '',
                          $project['year'] ?? '',
                          $project['category'] ?? '',
                          $project['type_attribute'] ?? '',
                          $project['city'] ?? '',
                          $project['status'] ?? '',
                      ]));
                    ?>
                    <div class="admin-project-row" draggable="true" data-project-id="<?= h($project['id']) ?>" data-project-title="<?= h($project['title']) ?>" data-project-search-text="<?= h($projectSearchText) ?>">
                      <div><strong><?= h($project['title']) ?></strong><br><small><?= h($project['status']) ?> · <?= h($project['year']) ?> · <?= h($project['category']) ?></small></div>
                      <button class="button button--secondary" type="button" data-add-project>加入</button>
                      <a class="button button--secondary" href="admin.php?edit=<?= h($project['id']) ?>">编辑</a>
                    </div>
                  <?php endforeach; ?>
                </div>
              </section>
            </aside>
          </div>
        <?php endif; ?>
      </div>
    </main>

    <footer class="footer">
      <div class="container footer__inner">
        <p>1+H Integrated Design &copy; <?= date('Y') ?></p>
      </div>
    </footer>
  </div>
  <script>
    (() => {
      const imageManager = document.querySelector('[data-image-manager]');
      const imagesInput = document.querySelector('[data-images-json]');
      const imageList = document.querySelector('[data-image-list]');
      const fileInput = document.querySelector('[data-image-input]');
      const dropzone = document.querySelector('[data-image-dropzone]');
      const primaryInput = document.querySelector('input[name="image"]');
      const countNode = document.querySelector('[data-image-count]');
      const projectIdInput = document.querySelector('input[name="id"]');
      const originalIdInput = document.querySelector('input[name="original_id"]');
      const MAX_IMAGES = Number(imageManager?.dataset.maxImages || 10);
      // 每张图片: { src, caption, status:'uploading'|'done'|'error', meta, error, localId, file }
      let images = [];
      let localSeq = 0;

      const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
      })[char]);

      // 只把“上传成功”的图片写进表单，uploading/error 不入库
      const syncImages = () => {
        const done = images.filter((image) => image.status === 'done' && image.src);
        if (imagesInput) imagesInput.value = JSON.stringify(done.map((image) => ({ src: image.src, caption: image.caption || '' })));
        if (primaryInput && done[0]?.src) primaryInput.value = done[0].src;
        if (countNode) {
          const active = images.filter((image) => image.status !== 'error').length;
          countNode.textContent = `${active} / ${MAX_IMAGES}`;
        }
      };

      const statusMarkup = (image) => {
        if (image.status === 'uploading') return '<span class="image-status image-status--uploading">上传中…</span>';
        if (image.status === 'done') return '<span class="image-status image-status--ok">✓ 上传成功</span>';
        if (image.status === 'error') return `<span class="image-status image-status--error">✗ 上传失败：${escapeHTML(image.error || '未知错误')}</span> <button type="button" class="image-status__retry" data-image-retry>重试</button>`;
        return '';
      };

      const renderImages = () => {
        if (!imageList) return;
        imageList.innerHTML = images.map((image, index) => `
          <div class="image-row${image.status === 'error' ? ' is-error' : ''}" draggable="${image.status === 'done'}" data-image-index="${index}">
            <img src="${escapeHTML(image.src)}" alt="">
            <div>
              <input data-image-caption value="${escapeHTML(image.caption || '')}" placeholder="图片说明"${image.status === 'done' ? '' : ' disabled'}>
              <p class="field-note">${index === 0 && image.status === 'done' ? '主图 / ' : ''}${escapeHTML(image.meta || image.src || '')}${statusMarkup(image)}</p>
            </div>
            <div class="image-row__actions">
              <button class="button button--secondary" type="button" data-image-move="up">上移</button>
              <button class="button button--secondary" type="button" data-image-move="down">下移</button>
              <button class="button button--secondary" type="button" data-image-delete>删除</button>
            </div>
          </div>
        `).join('') || '<p class="field-note">尚未添加图片。选中图片后立即上传，第一张会作为首页和项目卡片主图。</p>';
        syncImages();
      };

      const loadInitialImages = () => {
        try {
          const parsed = JSON.parse(imagesInput?.value || '[]');
          images = Array.isArray(parsed)
            ? parsed.filter((image) => image && image.src).map((image) => ({ src: image.src, caption: image.caption || '', status: 'done', meta: image.src }))
            : [];
        } catch {
          images = [];
        }
        renderImages();
      };

      const loadImage = (file) => new Promise((resolve, reject) => {
        const image = new Image();
        const url = URL.createObjectURL(file);
        image.onload = () => { URL.revokeObjectURL(url); resolve(image); };
        image.onerror = () => { URL.revokeObjectURL(url); reject(new Error('图片无法读取')); };
        image.src = url;
      });

      const canvasToBlob = (canvas, type, quality) => new Promise((resolve) => {
        canvas.toBlob((blob) => resolve(blob), type, quality);
      });

      const compressImage = async (file) => {
        const source = await loadImage(file);
        const maxEdge = 1800;
        const scale = Math.min(1, maxEdge / Math.max(source.naturalWidth || source.width, source.naturalHeight || source.height));
        const width = Math.max(1, Math.round((source.naturalWidth || source.width) * scale));
        const height = Math.max(1, Math.round((source.naturalHeight || source.height) * scale));
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;
        const context = canvas.getContext('2d', { alpha: false });
        context.fillStyle = '#f5f4ed';
        context.fillRect(0, 0, width, height);
        context.drawImage(source, 0, 0, width, height);
        let blob = await canvasToBlob(canvas, 'image/webp', 0.78);
        let extension = 'webp';
        if (!blob) {
          blob = await canvasToBlob(canvas, 'image/jpeg', 0.82);
          extension = 'jpg';
        }
        const safeName = file.name.replace(/[^a-zA-Z0-9_.-]+/g, '-').replace(/\.[^.]+$/, `.${extension}`);
        const compressedFile = new File([blob], safeName, { type: blob.type || `image/${extension}` });
        return {
          file: compressedFile,
          preview: URL.createObjectURL(blob),
          meta: `${file.name} · ${width}x${height} · ${Math.round(blob.size / 1024)} KB`
        };
      };

      // 真正上传单张图片到后端接口
      const uploadEntry = async (entry) => {
        entry.status = 'uploading';
        entry.error = '';
        renderImages();
        try {
          const form = new FormData();
          form.append('image', entry.file, entry.file.name);
          form.append('project_id', (projectIdInput?.value || originalIdInput?.value || 'project'));
          const res = await fetch('api/upload.php', { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
          let data = {};
          try { data = await res.json(); } catch { data = {}; }
          if (!res.ok || !data.ok) {
            throw new Error(data.error || `服务器返回 ${res.status}`);
          }
          entry.src = data.src;
          entry.status = 'done';
          entry.file = null;
        } catch (err) {
          entry.status = 'error';
          entry.error = err.message || '上传失败';
        }
        renderImages();
      };

      const activeCount = () => images.filter((image) => image.status !== 'error').length;

      const addFiles = async (fileList) => {
        const files = [...fileList].filter((file) => file.type.startsWith('image/'));
        if (!files.length) return;
        let slots = MAX_IMAGES - activeCount();
        if (slots <= 0) {
          alert(`每个项目最多 ${MAX_IMAGES} 张图片，请先删除部分图片再添加。`);
          return;
        }
        const accepted = files.slice(0, slots);
        if (files.length > accepted.length) {
          alert(`每个项目最多 ${MAX_IMAGES} 张图片，本次只添加前 ${accepted.length} 张。`);
        }
        for (const file of accepted) {
          let item;
          try {
            item = await compressImage(file);
          } catch (err) {
            const failed = { src: '', caption: '', status: 'error', meta: file.name, error: (err && err.message) || '图片处理失败', localId: ++localSeq, file: null };
            images.push(failed);
            renderImages();
            continue;
          }
          const entry = { src: item.preview, caption: '', status: 'uploading', meta: item.meta, error: '', localId: ++localSeq, file: item.file };
          images.push(entry);
          renderImages();
          uploadEntry(entry); // 异步上传，不阻塞后续图片
        }
      };

      fileInput?.addEventListener('change', () => { addFiles(fileInput.files); fileInput.value = ''; });
      dropzone?.addEventListener('dragover', (event) => { event.preventDefault(); dropzone.classList.add('is-dragover'); });
      dropzone?.addEventListener('dragleave', () => dropzone.classList.remove('is-dragover'));
      dropzone?.addEventListener('drop', (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragover');
        addFiles(event.dataTransfer.files);
      });

      imageList?.addEventListener('input', (event) => {
        const row = event.target.closest('[data-image-index]');
        if (!row || !event.target.matches('[data-image-caption]')) return;
        images[Number(row.dataset.imageIndex)].caption = event.target.value;
        syncImages();
      });
      imageList?.addEventListener('click', (event) => {
        const row = event.target.closest('[data-image-index]');
        if (!row) return;
        const index = Number(row.dataset.imageIndex);
        if (event.target.matches('[data-image-retry]')) {
          const entry = images[index];
          if (entry && entry.status === 'error' && entry.file) { uploadEntry(entry); }
          else if (entry) { images.splice(index, 1); renderImages(); }
          return;
        }
        if (event.target.matches('[data-image-delete]')) images.splice(index, 1);
        if (event.target.matches('[data-image-move="up"]') && index > 0) [images[index - 1], images[index]] = [images[index], images[index - 1]];
        if (event.target.matches('[data-image-move="down"]') && index < images.length - 1) [images[index + 1], images[index]] = [images[index], images[index + 1]];
        renderImages();
      });
      imageList?.addEventListener('dragstart', (event) => {
        const row = event.target.closest('[data-image-index]');
        if (!row || images[Number(row.dataset.imageIndex)]?.status !== 'done') return;
        row.classList.add('is-dragging');
        event.dataTransfer.setData('text/image-index', row.dataset.imageIndex);
      });
      imageList?.addEventListener('dragend', (event) => {
        event.target.closest('.image-row')?.classList.remove('is-dragging');
      });
      imageList?.addEventListener('dragover', (event) => event.preventDefault());
      imageList?.addEventListener('drop', (event) => {
        event.preventDefault();
        const from = Number(event.dataTransfer.getData('text/image-index'));
        const target = event.target.closest('[data-image-index]');
        if (!target || Number.isNaN(from)) return;
        const to = Number(target.dataset.imageIndex);
        const [moved] = images.splice(from, 1);
        images.splice(to, 0, moved);
        renderImages();
      });
      document.querySelector('[data-clear-images]')?.addEventListener('click', () => {
        images = [];
        if (fileInput) fileInput.value = '';
        renderImages();
      });

      // 有图片正在上传时，阻止提交，避免保存到一半
      document.querySelector('form[enctype]')?.addEventListener('submit', (event) => {
        if (images.some((image) => image.status === 'uploading')) {
          event.preventDefault();
          alert('还有图片正在上传，请等待全部显示“上传成功”后再保存。');
        }
      });

      document.querySelectorAll('[data-project-id]').forEach((row) => {
        row.addEventListener('dragstart', (event) => {
          const payload = JSON.stringify({ id: row.dataset.projectId || '', title: row.dataset.projectTitle || '' });
          event.dataTransfer.effectAllowed = 'copy';
          event.dataTransfer.setData('text/plain', payload);
          event.dataTransfer.setData('text/project-id', row.dataset.projectId || '');
          event.dataTransfer.setData('text/project-title', row.dataset.projectTitle || '');
        });
      });
      const adminProjectSearchInput = document.querySelector('[data-project-search]');
      const adminProjectSearchButton = document.querySelector('[data-project-search-button]');
      const adminProjectList = document.querySelector('.admin-project-list');
      const sortAdminProjectRows = () => {
        if (!adminProjectList) return;
        [...adminProjectList.querySelectorAll('[data-project-id]')]
          .sort((a, b) => {
            const ay = Number((a.dataset.projectSearchText || '').match(/\b(19|20)\d{2}\b/)?.[0] || 0);
            const by = Number((b.dataset.projectSearchText || '').match(/\b(19|20)\d{2}\b/)?.[0] || 0);
            if (by !== ay) return by - ay;
            return (a.dataset.projectTitle || '').localeCompare(b.dataset.projectTitle || '', 'zh-Hans-CN');
          })
          .forEach((row) => adminProjectList.appendChild(row));
      };
      const applyAdminProjectSearch = () => {
        sortAdminProjectRows();
        const query = (adminProjectSearchInput?.value || '').trim().toLowerCase();
        document.querySelectorAll('[data-project-id]').forEach((row) => {
          const haystack = (row.dataset.projectSearchText || row.textContent || '').toLowerCase();
          row.hidden = query !== '' && !haystack.includes(query);
        });
      };
      adminProjectSearchInput?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        applyAdminProjectSearch();
      });
      adminProjectSearchInput?.addEventListener('search', applyAdminProjectSearch);
      adminProjectSearchInput?.addEventListener('input', applyAdminProjectSearch);
      adminProjectSearchButton?.addEventListener('click', applyAdminProjectSearch);
      sortAdminProjectRows();

      const updateSelectionIndexes = (target) => {
        const items = [...target.querySelectorAll('.selection-item')];
        items.forEach((item, index) => {
          const indexNode = item.querySelector('.selection-item__index');
          if (indexNode) indexNode.textContent = String(index + 1).padStart(2, '0');
        });
        const emptyNode = target.querySelector('[data-selection-empty]');
        if (emptyNode) emptyNode.hidden = items.length > 0;
      };

      const addSelectionItem = (target, projectId, projectTitle) => {
        const list = target.querySelector('[data-selection-list]');
        const name = target.dataset.selectionName;
        if (!list || !name) return;
        const single = target.dataset.singleSelection === 'true';
        if (single) {
          list.innerHTML = '';
        }
        if (!single && list.querySelector(`[data-selection-id="${projectId}"]`)) {
          return;
        }
        const item = document.createElement('div');
        item.className = 'selection-item';
        item.dataset.selectionId = projectId;
        item.innerHTML = `
          <span class="selection-item__index"></span>
          <strong></strong>
          <button class="selection-remove" type="button" data-selection-remove>删除</button>
          <input type="hidden" name="${name}${single ? '' : '[]'}" value="${projectId}">
        `;
        item.querySelector('strong').textContent = projectTitle || projectId;
        list.appendChild(item);
        updateSelectionIndexes(target);
      };

      let activeDropTarget = null;
      const setActiveDropTarget = (target) => {
        document.querySelectorAll('[data-drop-target]').forEach((node) => node.classList.remove('is-active-target'));
        activeDropTarget = target;
        target?.classList.add('is-active-target');
      };

      const readDraggedProject = (event) => {
        let projectId = event.dataTransfer.getData('text/project-id');
        let projectTitle = event.dataTransfer.getData('text/project-title');
        if (!projectId) {
          try {
            const payload = JSON.parse(event.dataTransfer.getData('text/plain') || '{}');
            projectId = payload.id || '';
            projectTitle = payload.title || '';
          } catch {
            projectId = '';
          }
        }
        return { projectId, projectTitle };
      };

      document.querySelectorAll('[data-drop-target]').forEach((target) => {
        updateSelectionIndexes(target);
        target.addEventListener('click', () => setActiveDropTarget(target));
        target.addEventListener('dragover', (event) => {
          event.preventDefault();
          event.dataTransfer.dropEffect = 'copy';
          target.classList.add('is-dragover');
        });
        target.addEventListener('dragleave', () => target.classList.remove('is-dragover'));
        target.addEventListener('drop', (event) => {
          event.preventDefault();
          target.classList.remove('is-dragover');
          setActiveDropTarget(target);
          const { projectId, projectTitle } = readDraggedProject(event);
          if (!projectId) return;
          addSelectionItem(target, projectId, projectTitle);
        });
        target.addEventListener('click', (event) => {
          const removeButton = event.target.closest('[data-selection-remove]');
          if (!removeButton) return;
          removeButton.closest('.selection-item')?.remove();
          updateSelectionIndexes(target);
        });
      });
      setActiveDropTarget(document.querySelector('[data-drop-target]'));

      document.querySelectorAll('[data-add-project]').forEach((button) => {
        button.addEventListener('click', () => {
          const row = button.closest('[data-project-id]');
          const target = activeDropTarget || document.querySelector('[data-drop-target]');
          if (!row || !target) return;
          addSelectionItem(target, row.dataset.projectId || '', row.dataset.projectTitle || '');
        });
      });

      const teamRowTemplate = (groupName) => `
        <div class="team-admin__row" data-team-row>
          <div class="team-admin__photo">
            <img src="img/t1.jpg" alt="" data-team-preview>
            <input type="file" accept="image/*" data-team-photo-input>
            <input type="hidden" name="${groupName}[photo][]" value="" data-team-photo>
          </div>
          <input name="${groupName}[name][]" value="" placeholder="姓名">
          <input name="${groupName}[label][]" value="" placeholder="分类 / 标签">
          <input name="${groupName}[title][]" value="" placeholder="职位">
          <button class="button button--secondary" type="button" data-team-remove>删除</button>
        </div>
      `;

      document.querySelectorAll('[data-team-add]').forEach((button) => {
        button.addEventListener('click', () => {
          const groupName = button.dataset.teamAdd || '';
          const list = document.querySelector(`[data-team-list="${groupName}"]`);
          if (!groupName || !list) return;
          list.insertAdjacentHTML('beforeend', teamRowTemplate(groupName));
        });
      });

      document.addEventListener('click', (event) => {
        const removeButton = event.target.closest('[data-team-remove]');
        if (!removeButton) return;
        removeButton.closest('[data-team-row]')?.remove();
      });

      document.addEventListener('change', async (event) => {
        const input = event.target.closest('[data-team-photo-input]');
        if (!input || !input.files?.[0]) return;
        const row = input.closest('[data-team-row]');
        const photoInput = row?.querySelector('[data-team-photo]');
        const preview = row?.querySelector('[data-team-preview]');
        const file = input.files[0];
        const previewUrl = URL.createObjectURL(file);
        if (preview) preview.src = previewUrl;
        try {
          const form = new FormData();
          form.append('image', file, file.name);
          form.append('project_id', 'team');
          const res = await fetch('api/upload.php', { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || '' } });
          let data = {};
          try { data = await res.json(); } catch { data = {}; }
          if (!res.ok || !data.ok) {
            throw new Error(data.error || `服务器返回 ${res.status}`);
          }
          if (photoInput) photoInput.value = data.src;
          if (preview) preview.src = data.src;
        } catch (err) {
          alert(`团队照片上传失败：${err.message || '未知错误'}`);
          if (preview && photoInput?.value) preview.src = photoInput.value;
        } finally {
          URL.revokeObjectURL(previewUrl);
          input.value = '';
        }
      });

      if (imageManager) loadInitialImages();
    })();
  </script>
</body>
</html>
