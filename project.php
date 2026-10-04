<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';

$projectId = (string) ($_GET['project'] ?? '');
$project = $projectId !== '' ? oneh_get_project($projectId, false) : null;
if (!$project) {
    http_response_code(404);
    oneh_redirect('projects.php');
}
$c = oneh_get_content('project');
$meta = implode(' · ', array_filter([$project['city'] ?? '', $project['year'] ?? '', $project['area'] ?? '']));
$images = $project['images'] ?: [['src' => $project['image'], 'caption' => $project['title']]];
$facts = [
    ['Location', $project['city'] ?? ''],
    ['Year', $project['year'] ?? ''],
    ['Scale', $project['area'] ?? ''],
    ['Role', $project['role'] ?? ''],
];
// 画廊切换时使用压缩版本，同时提供原图地址
$galleryData = array_map(function (array $image) use ($project): array {
    return [
        'src' => oneh_img_url((string) $image['src']),
        'caption' => ($image['caption'] ?? '') ?: $project['title'],
    ];
}, $images);
$description = $project['summary'] ?: ($project['overview'] ?: oneh_get_content('site')['description']);

oneh_render_head([
    'key' => 'project',
    'title' => $project['title'] . ' | ' . oneh_get_content('site')['name'],
    'description' => oneh_substr($description, 0, 160),
    'path' => 'project.php?project=' . rawurlencode($project['id']),
    'image' => $images[0]['src'] ?? null,
    'type' => 'article',
    'preload' => $galleryData[0]['src'] ?? '',
    'data' => ['projectImages' => $galleryData, 'projectTitle' => $project['title']],
    'scripts' => ['project-detail.js'],
    'jsonld' => [
        '@context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        'name' => $project['title'],
        'description' => $description,
        'image' => array_map(function ($image) { return oneh_absolute_url($image['src']); }, $images),
        'dateCreated' => $project['year'] ?? '',
        'locationCreated' => $project['city'] ?? '',
        'creator' => ['@type' => 'Organization', 'name' => oneh_get_content('site')['name']],
    ],
]);
oneh_render_header('projects');
?>
    <main class="page project-page" id="content">
      <section class="project-showcase">
        <div class="container">
          <div class="project-showcase__head">
            <div>
              <span class="section__eyebrow" data-project-category><?= h($project['category']) ?></span>
              <h1 class="project-showcase__title" data-project-title><?= h($project['title']) ?></h1>
            </div>
            <p class="project-showcase__meta" data-project-meta><?= h($meta) ?></p>
          </div>

          <figure class="project-gallery" aria-live="polite">
            <img class="project-gallery__image" src="<?= h($galleryData[0]['src']) ?>" alt="<?= h($project['title']) ?> project visual" fetchpriority="high" decoding="async" data-project-image>
            <?php if (!empty($project['wechat_link'])): ?>
              <a class="project-gallery__wechat-link" href="<?= h($project['wechat_link']) ?>" target="_blank" rel="noopener noreferrer" data-project-wechat-link>查看微信公众号项目</a>
            <?php endif; ?>
            <?php if (count($galleryData) > 1): ?>
            <button class="project-gallery__control project-gallery__control--previous" type="button" data-project-previous aria-label="Previous project image">Previous</button>
            <button class="project-gallery__control project-gallery__control--next" type="button" data-project-next aria-label="Next project image">Next</button>
            <?php endif; ?>
            <figcaption class="project-gallery__caption"><span data-project-image-count>01 / <?= h(str_pad((string) count($galleryData), 2, '0', STR_PAD_LEFT)) ?></span><span data-project-image-caption><?= h($galleryData[0]['caption']) ?></span></figcaption>
          </figure>
        </div>
      </section>

      <section class="section project-overview">
        <div class="container project-overview__grid">
          <div>
            <span class="section__eyebrow">Project Overview</span>
            <h2 class="section__title" data-project-overview-title><?= h($project['overview_title'] ?: $project['title']) ?></h2>
          </div>
          <div class="project-overview__content">
            <p class="project-overview__lead" data-project-overview-copy><?= h($project['overview'] ?: $project['summary']) ?></p>
            <dl class="project-facts" data-project-facts>
              <?php foreach ($facts as [$key, $value]): ?>
                <div><dt><?= h($key) ?></dt><dd><?= h($value ?: '-') ?></dd></div>
              <?php endforeach; ?>
            </dl>
          </div>
        </div>
      </section>

      <section class="section section--dark project-principles">
        <div class="container">
<?php oneh_section_head($c['principles']); ?>
          <div class="project-principles__grid" data-project-principles>
            <?php foreach ($c['principles']['items'] as $index => $item): ?>
            <article><span><?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span><h3><?= h($item['title']) ?></h3><p><?= h($item['text']) ?></p></article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section project-next">
        <div class="container project-next__inner">
          <div>
            <span class="section__eyebrow">Continue Exploring</span>
            <h2 class="section__title">See more selected work.</h2>
          </div>
          <a class="button button--primary" href="projects.php">All Projects</a>
        </div>
      </section>
    </main>
<?php oneh_render_footer($c['footer']); ?>
