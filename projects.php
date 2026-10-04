<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';

$settings = oneh_get_settings();
$c = oneh_get_content('projects');
$projects = oneh_get_projects(false);
usort($projects, function (array $a, array $b): int {
    $yearCompare = ((int) ($b['year'] ?? 0)) <=> ((int) ($a['year'] ?? 0));
    if ($yearCompare !== 0) {
        return $yearCompare;
    }
    return strcmp((string) ($a['title'] ?? ''), (string) ($b['title'] ?? ''));
});
$projectsPage = $settings['projectsPage'] ?? [];
$typeLabels = oneh_type_labels();
$periodLabels = ['all-timeframes' => 'All timeframes'] + oneh_period_labels();
$visibleTypes = $projectsPage['typeFilters'] ?? array_keys($typeLabels);
$visiblePeriods = array_values(array_unique(array_merge(['all-timeframes'], $projectsPage['yearFilters'] ?? array_keys(oneh_period_labels()))));
$defaultPeriod = 'all-timeframes';
$firstProject = $projects[0] ?? null;

oneh_render_head([
    'key' => 'projects',
    'title' => $c['seo_title'],
    'description' => $c['seo_description'],
    'path' => 'projects.php',
    'image' => $firstProject['image'] ?? null,
    'data' => ['projectsPage' => ['defaultYearFilter' => $defaultPeriod]],
]);
oneh_render_header('projects');
?>
    <main class="page" id="content">
      <section class="hero hero--dark" data-od-id="projects-hero">
        <div class="container hero__grid">
          <div class="hero__copy">
            <span class="hero__eyebrow"><?= h($c['hero']['eyebrow']) ?></span>
            <h1 class="hero__title"><?= h($c['hero']['title']) ?></h1>
            <p class="hero__lead"><?= h($c['hero']['lead']) ?><?php if ($c['hero']['lead_cn'] !== ''): ?> <span class="cn"><?= h($c['hero']['lead_cn']) ?></span><?php endif; ?></p>
            <div class="hero__actions">
              <a class="button button--primary" href="contact.php">Request Material</a>
              <a class="button button--secondary" href="index.php">Back to Home</a>
            </div>
          </div>
          <aside class="hero__panel">
            <?= oneh_random_visual($projects, 'Project visual') ?>
          </aside>
        </div>
      </section>

      <section class="section fade-in" id="projects-filter" data-od-id="projects-filter">
        <div class="container">
<?php oneh_section_head($c['filter'], false); ?>

          <div class="filters" role="tablist" aria-label="Project filters">
            <button class="filter-btn" type="button" data-filter-button="all" aria-pressed="true">All Projects</button>
            <?php foreach ($visibleTypes as $type): ?>
              <button class="filter-btn" type="button" data-filter-button="<?= h($type) ?>" aria-pressed="false"><?= h($typeLabels[$type] ?? $type) ?></button>
            <?php endforeach; ?>
          </div>
          <div class="project-search" role="search">
            <label class="sr-only" for="project-search">搜索项目名称 / 年份 / 类型</label>
            <div class="search-control">
              <input id="project-search" type="search" data-project-search placeholder="搜索项目 / 年份 / 类型">
              <button class="search-button" type="button" data-project-search-button aria-label="搜索项目"></button>
            </div>
          </div>
          <div class="project-periods" aria-label="Representative project period">
            <span class="project-periods__label">Archive period</span>
            <div class="project-periods__list" role="tablist" aria-label="Project archive periods">
              <?php foreach ($visiblePeriods as $period): ?>
                <button class="project-period<?= $period === $defaultPeriod ? ' is-active' : '' ?>" type="button" data-period-button="<?= h($period) ?>" aria-pressed="<?= $period === $defaultPeriod ? 'true' : 'false' ?>"><?= h($periodLabels[$period] ?? $period) ?></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>

      <section class="section fade-in" data-od-id="projects-layout">
        <div class="container project-layout">
          <div>
            <div class="project-archive-status" aria-live="polite">
              <p class="project-archive-status__selection" data-project-filter-summary>All Projects · <?= h($periodLabels[$defaultPeriod] ?? $defaultPeriod) ?></p>
              <p class="project-archive-status__count" data-project-filter-count><?= count($projects) ?> projects</p>
            </div>
            <div class="project-grid">
              <?php foreach ($projects as $index => $project): ?>
                <?php
                  $projectSearchText = implode(' ', array_filter([
                      $project['title'] ?? '',
                      $project['year'] ?? '',
                      $project['category'] ?? '',
                      $project['type_attribute'] ?? '',
                      $project['city'] ?? '',
                  ]));
                ?>
                <article class="project-card fade-in<?= $index === 0 ? ' is-active' : '' ?>" data-project-card data-project-url="<?= h($project['link']) ?>" data-category="<?= h($project['type_attribute']) ?>" data-category-label="<?= h($project['category']) ?>" data-title="<?= h($project['title']) ?>" data-summary="<?= h($project['summary']) ?>" data-city="<?= h($project['city']) ?>" data-year="<?= h($project['year']) ?>" data-area="<?= h($project['area']) ?>" data-role="<?= h($project['role']) ?>" data-image-label="<?= h($project['category']) ?>" data-image="<?= h(oneh_img_url($project['image'])) ?>" data-search-text="<?= h($projectSearchText) ?>" role="link" tabindex="0">
                  <?= oneh_img($project['image'], $project['title'] . ' project visual', ['class' => 'project-card__image', 'sizes' => '(min-width: 1024px) 30vw, (min-width: 640px) 50vw, 100vw', 'lazy' => $index > 3]) ?>
                  <div class="project-card__body">
                    <div class="badge"><?= h($project['category']) ?></div>
                    <h3 class="project-card__title"><a href="<?= h($project['link']) ?>" tabindex="-1"><?= h($project['title']) ?></a></h3>
                    <p class="project-card__meta"><?= h($project['year'] ?? '') ?></p>
                    <p class="project-card__lead"><?= h($project['lead_text'] ?: $project['summary']) ?></p>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            <div class="project-more" data-project-more hidden>
              <button class="button button--secondary" type="button">Load more 加载更多</button>
            </div>
          </div>

          <aside class="card project-detail fade-in">
            <span class="badge badge--accent" data-project-detail-badge><?= h($firstProject['category'] ?? '') ?></span>
            <figure class="project-detail__image">
              <img src="<?= h(oneh_img_url($firstProject['image'] ?? 'img/001.jpg')) ?>" alt="<?= h($firstProject['title'] ?? 'Project') ?> project visual" decoding="async" data-project-detail-image>
            </figure>
            <h3 class="project-detail__title" data-project-detail-title><?= h($firstProject['title'] ?? '') ?></h3>
            <p class="project-detail__text" data-project-detail-text><?= h($firstProject['summary'] ?? '') ?></p>
            <div class="project-detail__facts" data-project-detail-facts></div>
            <div class="detail__actions">
              <a class="button button--primary" href="contact.php">Request Project Material</a>
              <a class="button button--secondary" href="about.php">View Team Background</a>
            </div>
          </aside>
        </div>
      </section>

      <section class="section section--dark fade-in" data-od-id="projects-downloads">
        <div class="container">
<?php oneh_section_head($c['downloads']); ?>

          <div class="download-list">
            <?php foreach ($c['downloads']['items'] as $index => $item): ?>
            <article class="download fade-in">
              <div class="download__text">
                <h3 class="download__title"><?= h($item['title']) ?></h3>
                <p class="download__lead"><?= h($item['text']) ?></p>
              </div>
              <a class="button <?= $index === 0 ? 'button--primary' : 'button--secondary' ?>" href="<?= h($item['link'] ?: 'contact.php') ?>">Request Download</a>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </main>
<?php oneh_render_footer($c['footer']); ?>
