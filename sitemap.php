<?php
declare(strict_types=1);

/** 站点地图：自动列出所有页面与已发布项目。通过 .htaccess 映射为 /sitemap.xml。 */
require_once __DIR__ . '/includes/layout.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$urls = [
    ['', '1.0', 'weekly'],
    ['about.php', '0.8', 'monthly'],
    ['projects.php', '0.9', 'weekly'],
    ['team.php', '0.6', 'monthly'],
    ['contact.php', '0.7', 'yearly'],
];
$projects = oneh_get_projects(false);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
foreach ($urls as [$path, $priority, $freq]) {
    echo '  <url><loc>' . h(oneh_absolute_url($path)) . '</loc><changefreq>' . $freq . '</changefreq><priority>' . $priority . "</priority></url>\n";
}
foreach ($projects as $project) {
    if (($project['status'] ?? '') !== 'published') {
        continue;
    }
    $lastmod = !empty($project['updated_at']) ? substr((string) $project['updated_at'], 0, 10) : '';
    echo '  <url><loc>' . h(oneh_absolute_url('project.php?project=' . rawurlencode($project['id']))) . '</loc>';
    if ($lastmod !== '') {
        echo '<lastmod>' . h($lastmod) . '</lastmod>';
    }
    echo '<priority>0.7</priority>';
    foreach (array_slice($project['images'] ?? [], 0, 5) as $image) {
        echo '<image:image><image:loc>' . h(oneh_absolute_url((string) $image['src'])) . '</image:loc></image:image>';
    }
    echo "</url>\n";
}
echo "</urlset>\n";
