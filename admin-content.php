<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/admin-ui.php';
require_once __DIR__ . '/includes/layout.php';

if (!oneh_is_admin()) {
    oneh_redirect('admin.php?login=1');
}
if (!oneh_install_schema()) {
    exit('数据库未就绪，请先到 admin.php 检查配置。');
}

$tabs = [
    'home' => '首页',
    'about' => 'About',
    'team' => '团队页',
    'projects' => '项目列表页',
    'project' => '项目详情页',
    'contact' => 'Contact',
    'site' => '站点与 SEO',
    'inquiries' => '留言',
    'tools' => '工具',
];
$tab = (string) ($_GET['tab'] ?? 'home');
if (!isset($tabs[$tab])) {
    $tab = 'home';
}
$message = '';
$error = '';

// ---------- 留言导出（CSV，Excel 可直接打开） ----------
if ($tab === 'inquiries' && ($_GET['export'] ?? '') === 'csv') {
    $rows = oneh_db_rows("SELECT id, created_at, status, name, company, email, phone, project_type, brief, ip FROM inquiries ORDER BY id DESC");
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="1h-inquiries-' . date('Ymd') . '.csv"');
    echo "\xEF\xBB\xBF";
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', '时间', '状态', '姓名', '公司', '邮箱', '电话/微信', '项目类型', '项目说明', 'IP']);
    foreach ($rows as $row) {
        fputcsv($out, array_values($row));
    }
    exit;
}

// ---------- POST 处理 ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!oneh_csrf_valid()) {
        $error = '页面已过期，请刷新后重试。';
    } elseif (isset($_POST['save_content'])) {
        $section = (string) ($_POST['section'] ?? '');
        $defaults = oneh_default_content();
        if (isset($defaults[$section])) {
            $all = oneh_get_content();
            $all[$section] = (array) ($_POST['content'][$section] ?? []);
            oneh_save_setting('content', oneh_sanitize_content($all));
            oneh_redirect('admin-content.php?tab=' . rawurlencode($section) . '&saved=1');
        }
    } elseif (isset($_POST['reset_content'])) {
        $section = (string) ($_POST['section'] ?? '');
        $all = oneh_get_content();
        if (isset($all[$section])) {
            $all[$section] = oneh_default_content()[$section];
            oneh_save_setting('content', oneh_sanitize_content($all));
            oneh_redirect('admin-content.php?tab=' . rawurlencode($section) . '&reset=1');
        }
    } elseif (isset($_POST['change_password'])) {
        $current = (string) ($_POST['current_password'] ?? '');
        $new = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');
        $user = (string) oneh_current_admin();
        $hash = (string) oneh_db_value("SELECT password_hash FROM admins WHERE username = ?", 's', [$user]);
        if ($hash === '' || !password_verify($current, $hash)) {
            $error = '当前密码不正确。';
        } elseif (oneh_strlen($new) < 10) {
            $error = '新密码至少 10 位。';
        } elseif ($new !== $confirm) {
            $error = '两次输入的新密码不一致。';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = oneh_db()->prepare("UPDATE admins SET password_hash = ? WHERE username = ?");
            $stmt->bind_param('ss', $newHash, $user);
            $stmt->execute();
            oneh_redirect('admin-content.php?tab=tools&password=1');
        }
    } elseif (isset($_POST['inquiry_action'])) {
        $ids = array_values(array_filter(array_map('intval', (array) ($_POST['ids'] ?? []))));
        $action = (string) $_POST['inquiry_action'];
        $db = oneh_db();
        if ($db && $ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            if ($action === 'delete') {
                $stmt = $db->prepare("DELETE FROM inquiries WHERE id IN ($placeholders)");
                oneh_bind_params($stmt, $types, $ids);
            } else {
                $status = in_array($action, ['new', 'read', 'archived'], true) ? $action : 'read';
                $stmt = $db->prepare("UPDATE inquiries SET status = ? WHERE id IN ($placeholders)");
                oneh_bind_params($stmt, 's' . $types, array_merge([$status], $ids));
            }
            $stmt->execute();
        }
        oneh_redirect('admin-content.php?tab=inquiries&status=' . rawurlencode((string) ($_POST['return_status'] ?? '')) . '&done=1');
    }
}
if (isset($_GET['saved'])) {
    $message = '已保存，前台页面立即生效。';
} elseif (isset($_GET['reset'])) {
    $message = '已恢复为默认文案。';
} elseif (isset($_GET['password'])) {
    $message = '密码已修改，下次登录请使用新密码。';
} elseif (isset($_GET['done'])) {
    $message = '操作已完成。';
}

// ---------- 通用字段渲染 ----------
$labels = [
    'seo_title' => 'SEO 标题（浏览器标签 / 搜索结果标题）', 'seo_description' => 'SEO 描述（搜索结果摘要，建议 80–160 字符）',
    'eyebrow' => '小标题', 'title' => '标题', 'title_cn' => '标题中文补充', 'lead' => '导语', 'lead_cn' => '导语中文补充',
    'text' => '正文', 'card' => '右侧卡片文字', 'image' => '图片', 'icon' => '图标', 'items' => '条目', 'footer' => '页脚右侧文字',
    'hero' => '顶部首屏', 'hero_title' => '首屏大标题', 'hero_lead' => '首屏导语', 'hero_chips' => '首屏关键词标签',
    'board' => 'Project Index 区', 'who' => 'Who We Are 区', 'works' => 'Selected Works 区', 'capabilities' => '能力区',
    'why' => 'Why 1+H 区', 'cta' => '底部行动区', 'story' => '公司介绍 / 发展历程', 'timeline' => '时间线', 'year' => '年份',
    'values' => '设计理念', 'team' => '团队介绍', 'label' => '图片标签', 'credentials' => '荣誉 / 媒体 / 合作',
    'founders_label' => '合伙人栏目名', 'design_label' => '设计团队栏目名', 'design_note' => '设计团队说明',
    'filter' => '筛选区', 'downloads' => '资料下载区', 'link' => '链接', 'principles' => '设计原则区',
    'social' => '社交平台', 'name' => '名称', 'url' => '链接地址（留空则不可点击）', 'channels' => '联系方式',
    'emails' => '邮箱（每行一个）', 'phone' => '电话', 'address' => '地址', 'response' => '回复时效',
    'form' => '咨询表单', 'project_types' => '项目类型选项', 'success' => '提交成功提示',
    'location' => '办公地点区', 'note_title' => '提示标题', 'note_text' => '提示正文', 'notice' => '提示条',
    'og_image' => '默认分享图', 'icp' => 'ICP 备案号', 'icp_url' => '备案链接', 'notify_email' => '新留言提醒邮箱（留空则不发邮件）',
    'description' => '默认 SEO 描述',
];
$imageKeys = ['image', 'icon', 'og_image'];
$longKeys = ['lead', 'text', 'card', 'hero_lead', 'seo_description', 'description', 'note_text', 'success', 'lead_cn', 'design_note'];

$isList = function ($value): bool {
    return is_array($value) && ($value === [] || array_keys($value) === range(0, count($value) - 1));
};

$renderField = function (string $key, $value, $default, string $name) use (&$renderField, $labels, $imageKeys, $longKeys, $isList): void {
    $label = $labels[$key] ?? $key;
    if ($isList($default)) {
        $template = $default[0] ?? '';
        $items = is_array($value) ? $value : [];
        echo '<fieldset class="ce-group ce-list" data-list><legend>' . h($label) . '</legend>';
        echo '<div class="ce-list__items" data-list-items>';
        // 空白占位，保证列表被全部删除时也能提交为空
        if (is_array($template)) {
            echo '<input type="hidden" name="' . h($name) . '[_][' . h((string) key($template)) . ']" value="">';
        } else {
            echo '<input type="hidden" name="' . h($name) . '[]" value="">';
        }
        foreach (array_values($items) as $index => $item) {
            echo '<div class="ce-list__item" data-list-item>';
            if (is_array($template)) {
                foreach ($template as $subKey => $subDefault) {
                    $renderField((string) $subKey, $item[$subKey] ?? $subDefault, $subDefault, $name . '[' . $index . '][' . $subKey . ']');
                }
            } else {
                echo '<input class="ce-input" name="' . h($name) . '[]" value="' . h((string) $item) . '">';
            }
            echo '<div class="ce-list__tools"><button type="button" class="ce-btn" data-move="-1">上移</button><button type="button" class="ce-btn" data-move="1">下移</button><button type="button" class="ce-btn ce-btn--danger" data-remove>删除</button></div>';
            echo '</div>';
        }
        echo '</div>';
        // 新增条目模板
        echo '<template data-list-template>';
        echo '<div class="ce-list__item" data-list-item>';
        if (is_array($template)) {
            foreach ($template as $subKey => $subDefault) {
                $renderField((string) $subKey, '', $subDefault, $name . '[__INDEX__][' . $subKey . ']');
            }
        } else {
            echo '<input class="ce-input" name="' . h($name) . '[]" value="">';
        }
        echo '<div class="ce-list__tools"><button type="button" class="ce-btn" data-move="-1">上移</button><button type="button" class="ce-btn" data-move="1">下移</button><button type="button" class="ce-btn ce-btn--danger" data-remove>删除</button></div>';
        echo '</div></template>';
        echo '<button type="button" class="button button--secondary ce-add" data-add>＋ 增加一条</button>';
        echo '</fieldset>';
        return;
    }
    if (is_array($default)) {
        echo '<fieldset class="ce-group"><legend>' . h($label) . '</legend>';
        foreach ($default as $subKey => $subDefault) {
            $renderField((string) $subKey, is_array($value) ? ($value[$subKey] ?? $subDefault) : $subDefault, $subDefault, $name . '[' . $subKey . ']');
        }
        echo '</fieldset>';
        return;
    }
    $id = 'f' . substr(md5($name), 0, 10);
    echo '<div class="ce-field">';
    echo '<label for="' . $id . '">' . h($label) . '</label>';
    if (in_array($key, $imageKeys, true)) {
        echo '<div class="ce-image" data-image-field>';
        echo '<img src="' . h((string) $value) . '" alt="" data-image-preview' . ($value === '' ? ' hidden' : '') . '>';
        echo '<input class="ce-input" id="' . $id . '" name="' . h($name) . '" value="' . h((string) $value) . '" placeholder="img/xxx.jpg 或上传" data-image-input>';
        echo '<label class="ce-btn ce-upload">上传图片<input type="file" accept="image/*" hidden data-image-upload></label>';
        echo '<span class="ce-hint" data-image-status></span>';
        echo '</div>';
    } elseif (in_array($key, $longKeys, true) || oneh_strlen((string) $default) > 90) {
        echo '<textarea class="ce-input" id="' . $id . '" name="' . h($name) . '" rows="3">' . h((string) $value) . '</textarea>';
    } else {
        echo '<input class="ce-input" id="' . $id . '" name="' . h($name) . '" value="' . h((string) $value) . '">';
    }
    echo '</div>';
};

$content = oneh_get_content();
$defaults = oneh_default_content();

// 留言数据
$inquiryStatus = (string) ($_GET['status'] ?? '');
$inquiries = [];
$inquiryCounts = ['new' => 0, 'read' => 0, 'archived' => 0];
if ($tab === 'inquiries') {
    foreach (oneh_db_rows("SELECT status, COUNT(*) AS n FROM inquiries GROUP BY status") as $row) {
        $inquiryCounts[$row['status']] = (int) $row['n'];
    }
    if (in_array($inquiryStatus, ['new', 'read', 'archived'], true)) {
        $inquiries = oneh_db_rows("SELECT * FROM inquiries WHERE status = ? ORDER BY id DESC LIMIT 300", 's', [$inquiryStatus]);
    } else {
        $inquiries = oneh_db_rows("SELECT * FROM inquiries WHERE status <> 'archived' ORDER BY id DESC LIMIT 300");
    }
}
$imageSources = $tab === 'tools' ? oneh_all_image_sources() : [];
$missingThumbs = $tab === 'tools' ? count(array_filter($imageSources, function ($src) { return count(oneh_thumb_variants($src)) < count(ONEH_THUMB_WIDTHS); })) : 0;

oneh_render_head([
    'key' => 'admin',
    'lang' => 'zh-CN',
    'title' => '内容管理 | ' . $content['site']['name'],
    'noindex' => true,
    'admin' => true,
    'scripts' => [],
    'head_extra' => '<meta name="csrf-token" content="' . h(oneh_csrf_token()) . '">',
]);
?>
<style>
  body[data-page="admin"] { background: var(--bg); }
  .ce-shell { padding: 96px 0 80px; }
  .ce-tabs { display: flex; flex-wrap: wrap; gap: 6px; margin: 18px 0 22px; }
  .ce-tabs a { padding: 8px 14px; border: 1px solid var(--border); background: var(--surface); color: var(--fg); text-decoration: none; font-size: var(--text-sm); }
  .ce-tabs a[aria-current="page"] { background: var(--fg); color: var(--bg); border-color: var(--fg); }
  .ce-panel { padding: clamp(16px, 2vw, 28px); border: 1px solid var(--border); background: var(--surface); }
  .ce-group { margin: 0 0 16px; padding: 14px 16px 6px; border: 1px solid var(--border-soft, var(--border)); background: var(--bg); }
  .ce-group > legend { padding: 0 6px; font-weight: 600; font-size: .95rem; }
  .ce-group .ce-group { background: var(--surface); }
  .ce-field { display: grid; gap: 5px; margin-bottom: 12px; }
  .ce-field > label { color: var(--meta); font-size: var(--text-xs); letter-spacing: .06em; }
  .ce-input { width: 100%; min-height: 40px; padding: 8px 10px; border: 1px solid var(--border-soft, var(--border)); background: var(--surface); color: var(--fg); font: inherit; font-size: 15px; }
  textarea.ce-input { min-height: 84px; resize: vertical; line-height: 1.55; }
  .ce-list__items { display: grid; gap: 10px; margin-bottom: 10px; }
  .ce-list__item { padding: 12px 12px 4px; border: 1px dashed var(--border); background: var(--surface); }
  .ce-list__item > .ce-input { margin-bottom: 8px; }
  .ce-list__tools { display: flex; gap: 6px; justify-content: flex-end; margin-bottom: 8px; }
  .ce-btn { display: inline-flex; align-items: center; min-height: 30px; padding: 4px 10px; border: 1px solid var(--border); background: var(--bg); color: var(--fg); font-size: var(--text-xs); cursor: pointer; }
  .ce-btn--danger { color: #b42318; }
  .ce-add { margin-bottom: 10px; }
  .ce-image { display: grid; grid-template-columns: 120px minmax(0, 1fr) auto; gap: 8px; align-items: center; }
  .ce-image img { width: 120px; aspect-ratio: 4 / 3; object-fit: cover; background: var(--surface); border: 1px solid var(--border); }
  .ce-image .ce-hint { grid-column: 2 / -1; color: var(--meta); font-size: var(--text-xs); }
  .ce-actions { position: sticky; bottom: 0; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; padding: 14px 0; background: linear-gradient(transparent, var(--surface) 30%); }
  .ce-message { margin: 0 0 14px; padding: 10px 14px; border-left: 3px solid var(--accent); background: var(--surface); }
  .ce-error { margin: 0 0 14px; padding: 10px 14px; border-left: 3px solid #b42318; background: var(--surface); color: #b42318; }
  .ce-table { width: 100%; border-collapse: collapse; font-size: var(--text-sm); }
  .ce-table th, .ce-table td { padding: 10px 8px; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
  .ce-table tr.is-new td { background: color-mix(in oklab, var(--accent), transparent 93%); }
  .ce-table .brief { white-space: pre-wrap; max-width: 520px; }
  .ce-pill { display: inline-block; padding: 2px 8px; border: 1px solid var(--border); font-size: var(--text-xs); }
  .ce-filter { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 14px; }
  .ce-progress { height: 6px; background: var(--bg); border: 1px solid var(--border); margin: 10px 0; }
  .ce-progress span { display: block; height: 100%; width: 0; background: var(--accent); transition: width .2s; }
  @media (max-width: 760px) { .ce-image { grid-template-columns: 80px minmax(0, 1fr); } .ce-image img { width: 80px; } .ce-upload { grid-column: 1 / -1; } .ce-table .brief { max-width: none; } }
</style>
<?php oneh_admin_nav($tab === 'inquiries' ? 'inquiries' : ($tab === 'tools' ? 'tools' : 'content')); ?>
    <main class="page ce-shell" id="content">
      <div class="container">
        <span class="section__eyebrow">Website Admin</span>
        <h1 class="section__title" style="margin-top:6px">内容管理</h1>

        <nav class="ce-tabs" aria-label="内容分区">
          <?php foreach ($tabs as $key => $label): ?>
            <a href="admin-content.php?tab=<?= h($key) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= h($label) ?><?= $key === 'inquiries' && oneh_inquiry_unread_count() > 0 ? '（' . oneh_inquiry_unread_count() . '）' : '' ?></a>
          <?php endforeach; ?>
        </nav>

        <?php if ($message): ?><p class="ce-message"><?= h($message) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="ce-error"><?= h($error) ?></p><?php endif; ?>

<?php if (isset($defaults[$tab])): ?>
        <form class="ce-panel" method="post" data-content-form>
          <?= oneh_csrf_field() ?>
          <input type="hidden" name="section" value="<?= h($tab) ?>">
          <?php foreach ($defaults[$tab] as $key => $default): ?>
            <?php $renderField((string) $key, $content[$tab][$key] ?? $default, $default, 'content[' . $tab . '][' . $key . ']'); ?>
          <?php endforeach; ?>
          <div class="ce-actions">
            <button class="button button--primary" type="submit" name="save_content" value="1">保存「<?= h($tabs[$tab]) ?>」</button>
            <a class="button button--secondary" href="<?= h(['home' => 'index.php', 'about' => 'about.php', 'team' => 'team.php', 'projects' => 'projects.php', 'project' => 'projects.php', 'contact' => 'contact.php', 'site' => 'index.php'][$tab]) ?>" target="_blank" rel="noopener">预览页面</a>
            <button class="ce-btn ce-btn--danger" type="submit" name="reset_content" value="1" formnovalidate data-confirm="确定把「<?= h($tabs[$tab]) ?>」恢复为默认文案吗？">恢复默认</button>
          </div>
        </form>

<?php elseif ($tab === 'inquiries'): ?>
        <div class="ce-panel">
          <div class="ce-filter">
            <a class="ce-btn" href="admin-content.php?tab=inquiries">未归档（<?= $inquiryCounts['new'] + $inquiryCounts['read'] ?>）</a>
            <a class="ce-btn" href="admin-content.php?tab=inquiries&status=new">未读（<?= $inquiryCounts['new'] ?>）</a>
            <a class="ce-btn" href="admin-content.php?tab=inquiries&status=read">已读（<?= $inquiryCounts['read'] ?>）</a>
            <a class="ce-btn" href="admin-content.php?tab=inquiries&status=archived">已归档（<?= $inquiryCounts['archived'] ?>）</a>
            <a class="ce-btn" href="admin-content.php?tab=inquiries&export=csv">导出 CSV</a>
          </div>
          <?php if (!$inquiries): ?>
            <p>暂无留言。官网 Contact 页面的表单提交后会出现在这里。</p>
          <?php else: ?>
          <form method="post">
            <?= oneh_csrf_field() ?>
            <input type="hidden" name="return_status" value="<?= h($inquiryStatus) ?>">
            <div class="ce-filter">
              <span>选中项：</span>
              <button class="ce-btn" name="inquiry_action" value="read">标记已读</button>
              <button class="ce-btn" name="inquiry_action" value="new">标记未读</button>
              <button class="ce-btn" name="inquiry_action" value="archived">归档</button>
              <button class="ce-btn ce-btn--danger" name="inquiry_action" value="delete" data-confirm="确定永久删除选中的留言吗？">删除</button>
            </div>
            <div style="overflow-x:auto">
            <table class="ce-table">
              <thead><tr><th><input type="checkbox" data-check-all aria-label="全选"></th><th>时间</th><th>联系人</th><th>项目类型</th><th>项目说明</th><th>状态</th></tr></thead>
              <tbody>
              <?php foreach ($inquiries as $row): ?>
                <tr class="<?= $row['status'] === 'new' ? 'is-new' : '' ?>">
                  <td><input type="checkbox" name="ids[]" value="<?= (int) $row['id'] ?>" aria-label="选择"></td>
                  <td><?= h(substr((string) $row['created_at'], 0, 16)) ?></td>
                  <td><strong><?= h($row['name']) ?></strong><br><?= h($row['company']) ?><br><a href="mailto:<?= h($row['email']) ?>?subject=<?= rawurlencode('Re: 1+H Project Inquiry') ?>"><?= h($row['email']) ?></a><?php if ($row['phone'] !== ''): ?><br><?= h($row['phone']) ?><?php endif; ?></td>
                  <td><?= h($row['project_type']) ?></td>
                  <td class="brief"><?= h($row['brief']) ?></td>
                  <td><span class="ce-pill"><?= h(['new' => '未读', 'read' => '已读', 'archived' => '已归档'][$row['status']] ?? $row['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            </div>
          </form>
          <?php endif; ?>
        </div>

<?php elseif ($tab === 'tools'): ?>
        <div class="ce-panel">
          <h2 style="margin-top:0">图片压缩版本</h2>
          <p>为网站图片生成 640px / 1280px 的压缩版本（<?= h(strtoupper(oneh_thumb_ext())) ?>），手机和普通屏幕只下载小图，页面打开更快。新上传的图片会自动生成；旧图片可点下方按钮批量补齐。</p>
          <p>共 <strong><?= count($imageSources) ?></strong> 张图片，待处理 <strong data-thumb-missing><?= $missingThumbs ?></strong> 张。</p>
          <div class="ce-progress"><span data-thumb-bar></span></div>
          <button class="button button--primary" type="button" data-thumb-run>生成压缩版本</button>
          <span class="ce-hint" data-thumb-status></span>
          <hr style="margin:28px 0;border:0;border-top:1px solid var(--border)">
          <h2>修改后台密码</h2>
          <form method="post" style="max-width:420px">
            <?= oneh_csrf_field() ?>
            <div class="ce-field"><label for="cp1">当前密码</label><input class="ce-input" id="cp1" type="password" name="current_password" autocomplete="current-password" required></div>
            <div class="ce-field"><label for="cp2">新密码（至少 10 位）</label><input class="ce-input" id="cp2" type="password" name="new_password" autocomplete="new-password" minlength="10" required></div>
            <div class="ce-field"><label for="cp3">再次输入新密码</label><input class="ce-input" id="cp3" type="password" name="confirm_password" autocomplete="new-password" minlength="10" required></div>
            <button class="button button--primary" type="submit" name="change_password" value="1">修改密码</button>
          </form>
          <hr style="margin:28px 0;border:0;border-top:1px solid var(--border)">
          <h2>搜索引擎</h2>
          <p>站点地图：<a href="sitemap.xml" target="_blank" rel="noopener">sitemap.xml</a>（自动包含所有已发布项目，可提交到百度 / 必应 / Google 站长平台）。</p>
          <p>当前站点网址：<code><?= h($content['site']['url']) ?></code>（在「站点与 SEO」中修改，影响分享链接与站点地图）。</p>
        </div>
<?php endif; ?>
      </div>
    </main>
<script>
(() => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

  document.addEventListener('click', (event) => {
    const confirmButton = event.target.closest('[data-confirm]');
    if (confirmButton && !window.confirm(confirmButton.dataset.confirm)) {
      event.preventDefault();
      return;
    }
    const add = event.target.closest('[data-add]');
    if (add) {
      const list = add.closest('[data-list]');
      const template = list.querySelector(':scope > template[data-list-template]');
      const items = list.querySelector(':scope > [data-list-items]');
      const html = template.innerHTML.replaceAll('__INDEX__', 'n' + Date.now());
      items.insertAdjacentHTML('beforeend', html);
      items.lastElementChild.querySelector('input, textarea')?.focus();
      return;
    }
    const remove = event.target.closest('[data-remove]');
    if (remove) {
      remove.closest('[data-list-item]').remove();
      return;
    }
    const move = event.target.closest('[data-move]');
    if (move) {
      const item = move.closest('[data-list-item]');
      if (move.dataset.move === '-1' && item.previousElementSibling?.matches('[data-list-item]')) {
        item.parentNode.insertBefore(item, item.previousElementSibling);
      } else if (move.dataset.move === '1' && item.nextElementSibling) {
        item.parentNode.insertBefore(item.nextElementSibling, item);
      }
    }
  });

  // 说明：列表顺序以页面上的排列为准（表单按 DOM 顺序提交，服务端按提交顺序保存）。

  document.querySelectorAll('[data-check-all]').forEach((box) => {
    box.addEventListener('change', () => {
      box.closest('table').querySelectorAll('tbody input[type=checkbox]').forEach((item) => { item.checked = box.checked; });
    });
  });

  // 图片字段：上传 + 预览
  document.addEventListener('change', async (event) => {
    const upload = event.target.closest('[data-image-upload]');
    if (!upload || !upload.files?.length) return;
    const field = upload.closest('[data-image-field]');
    const input = field.querySelector('[data-image-input]');
    const preview = field.querySelector('[data-image-preview]');
    const status = field.querySelector('[data-image-status]');
    const form = new FormData();
    form.append('image', upload.files[0]);
    form.append('project_id', 'content');
    status.textContent = '上传中…';
    try {
      const response = await fetch('api/upload.php', { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf } });
      const result = await response.json();
      if (!result.ok) throw new Error(result.error || '上传失败');
      input.value = result.src;
      preview.src = result.src;
      preview.hidden = false;
      status.textContent = '✓ 上传成功，记得点击保存';
    } catch (error) {
      status.textContent = '✗ ' + error.message;
    }
    upload.value = '';
  });
  document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-image-input]');
    if (!input) return;
    const preview = input.closest('[data-image-field]').querySelector('[data-image-preview]');
    preview.src = input.value;
    preview.hidden = !input.value;
  });

  // 批量生成压缩图
  const runButton = document.querySelector('[data-thumb-run]');
  runButton?.addEventListener('click', async () => {
    const bar = document.querySelector('[data-thumb-bar]');
    const status = document.querySelector('[data-thumb-status]');
    const missing = document.querySelector('[data-thumb-missing]');
    runButton.disabled = true;
    let offset = 0;
    try {
      for (;;) {
        const body = new FormData();
        body.append('offset', String(offset));
        const response = await fetch('api/thumbs.php', { method: 'POST', body, credentials: 'same-origin', headers: { 'X-CSRF-Token': csrf } });
        const result = await response.json();
        if (!result.ok) throw new Error(result.error || '处理失败');
        offset = result.next;
        bar.style.width = `${Math.round((offset / Math.max(1, result.total)) * 100)}%`;
        status.textContent = `已处理 ${offset} / ${result.total}`;
        if (result.done) {
          missing.textContent = String(result.missing);
          status.textContent = `完成：共 ${result.total} 张，剩余未处理 ${result.missing} 张（无法处理的通常是超大或损坏的图片）。`;
          break;
        }
      }
    } catch (error) {
      status.textContent = '✗ ' + error.message;
    }
    runButton.disabled = false;
  });
})();
</script>
<?php oneh_render_footer(''); ?>
