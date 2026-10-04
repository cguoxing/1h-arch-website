<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';

$settings = oneh_get_settings();
$c = oneh_get_content('home');
$allProjects = oneh_get_projects(false);
$allIds = array_column($allProjects, 'id');
$fallbackProjects = array_map('oneh_normalize_project', oneh_sample_projects());

// 首页三个展示区按顺序排他生成：Hero -> Project Index -> Selected Works。
$homeSectionIds = oneh_get_home_section_ids($allIds, $settings);
$heroProjects = oneh_projects_by_ids($homeSectionIds['heroIds'], $fallbackProjects, 5, false);
$boardProjects = oneh_projects_by_ids($homeSectionIds['projectIndexIds'], $fallbackProjects, 4, false);
$selectedProjects = oneh_projects_by_ids($homeSectionIds['selectedIds'], $fallbackProjects, 5, false);

$meta = function (array $project): string {
    return implode(' · ', array_filter([$project['city'] ?? '', $project['category'] ?? '', $project['year'] ?? '']));
};
$detail = function (array $project): string {
    return implode(' / ', array_filter([$project['category'] ?? '', $project['city'] ?? '']));
};
$site = oneh_get_content('site');
$contactInfo = oneh_get_content('contact')['channels'];
$firstHero = $heroProjects[0]['image'] ?? '';

oneh_render_head([
    'key' => 'home',
    'title' => $c['seo_title'],
    'description' => $c['seo_description'],
    'path' => '',
    'image' => $firstHero ?: null,
    'preload' => $firstHero ? oneh_img_url($firstHero) : '',
    'jsonld' => [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $site['name'],
        'url' => oneh_absolute_url(),
        'logo' => oneh_absolute_url('img/new-logo.svg'),
        'email' => $contactInfo['emails'][0] ?? '',
        'address' => $contactInfo['address'] ?? '',
    ],
]);
oneh_render_header('home');
?>
    <main class="page" id="content">
      <section class="hero hero--cinematic fade-in" data-od-id="home-hero">
        <div class="hero-cinema" data-hero-carousel>
          <div class="hero-cinema__stage" aria-label="Hero carousel">
            <?php foreach ($heroProjects as $index => $project): ?>
              <figure class="hero-cinema__slide<?= $index === 0 ? ' is-active' : '' ?>" data-hero-slide data-title="<?= h($project['title']) ?>" data-meta="<?= h($meta($project)) ?>" data-label="<?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) . ' / ' . str_pad((string) count($heroProjects), 2, '0', STR_PAD_LEFT)) ?>">
                <?= oneh_img($project['image'], $project['title'] . ' project visual', ['priority' => $index === 0, 'lazy' => $index !== 0]) ?>
              </figure>
            <?php endforeach; ?>
          </div>
          <div class="hero-cinema__veil" aria-hidden="true"></div>

          <div class="container hero-cinema__content">
            <div class="hero-cinema__copy">
              <h1 class="home-hero__title"><?= h($c['hero_title']) ?></h1>
              <p class="home-hero__lead">
                <span class="cn"><?= h($c['hero_lead']) ?></span>
              </p>
              <div class="hero__actions">
                <a class="button button--primary" href="projects.php">View Projects</a>
                <a class="button button--secondary" href="contact.php">Contact Us</a>
                <a class="button button--ghost" href="about.php">About 1+H</a>
              </div>
              <?php if ($c['hero_chips']): ?>
              <div class="hero-cinema__chips" aria-label="Homepage keywords">
                <?php foreach ($c['hero_chips'] as $chip): ?><span><?= h($chip) ?></span><?php endforeach; ?>
              </div>
              <?php endif; ?>
            </div>

            <aside class="hero-cinema__panel" aria-label="Current project information">
              <div class="hero-cinema__panel-head">
                <div class="hero-cinema__panel-title-wrap" aria-live="polite" aria-atomic="true">
                  <h2 class="hero-cinema__panel-title" data-hero-title><?= h($heroProjects[0]['title'] ?? '') ?></h2>
                </div>
                <p class="hero-cinema__panel-meta" data-hero-meta><?= h(isset($heroProjects[0]) ? $meta($heroProjects[0]) : '') ?></p>
              </div>

              <div class="hero-cinema__thumbs" aria-label="Hero selection">
                <?php foreach ($heroProjects as $index => $project): ?>
                  <button class="hero-cinema__thumb<?= $index === 0 ? ' is-active' : '' ?>" type="button" data-hero-thumb="<?= $index ?>" aria-label="Show <?= h($project['title']) ?>" aria-pressed="<?= $index === 0 ? 'true' : 'false' ?>"></button>
                <?php endforeach; ?>
              </div>
            </aside>
          </div>

          <div class="hero-cinema__ticker" aria-hidden="true">
            <span>ARCHITECTURE</span>
            <span>INTERIORS</span>
            <span>PLANNING</span>
            <span>LANDSCAPE</span>
            <span>RENEWAL</span>
          </div>
        </div>
      </section>

      <?php if ($boardProjects): ?>
      <section class="section fade-in" data-od-id="home-board">
        <div class="container project-board">
<?php oneh_section_head($c['board']); ?>

          <div class="project-board__layout">
            <aside class="project-board__viewer card" aria-live="polite">
              <div class="project-board__stage">
                <?= oneh_img($boardProjects[0]['image'] ?? 'img/001.jpg', ($boardProjects[0]['title'] ?? 'Project') . ' project visual', ['class' => 'project-board__image', 'sizes' => '(min-width: 1024px) 60vw, 100vw', 'attrs' => 'data-board-image']) ?>
                <div class="project-board__orbit" aria-hidden="true"></div>
                <button class="project-board__point" type="button" data-board-point aria-label="Project point"></button>
                <span class="project-board__orbit-caption">Moving project index</span>
                <div class="project-board__headline">
                  <span class="badge badge--accent" data-board-tag><?= h($boardProjects[0]['category'] ?? '') ?></span>
                  <h3 data-board-title><?= h($boardProjects[0]['title'] ?? '') ?></h3>
                  <p data-board-copy><?= h(isset($boardProjects[0]) ? $meta($boardProjects[0]) : '') ?></p>
                </div>
                <dl class="project-board__facts">
                  <div class="project-board__fact">
                    <dt>Scale</dt>
                    <dd data-board-fact-scale><?= h(($boardProjects[0]['area'] ?? '') ?: '—') ?></dd>
                  </div>
                  <div class="project-board__fact">
                    <dt>Role</dt>
                    <dd data-board-fact-role><?= h(($boardProjects[0]['role'] ?? '') ?: '—') ?></dd>
                  </div>
                </dl>
              </div>
            </aside>

            <div class="project-board__list" role="list" aria-label="Featured projects">
              <?php foreach ($boardProjects as $index => $project): ?>
                <a class="project-board__item<?= $index === 0 ? ' is-active' : '' ?>" href="<?= h($project['link']) ?>" data-board-item data-board-phase="<?= $index ?>" data-board-tag="<?= h($project['category']) ?>" data-board-title="<?= h($project['title']) ?>" data-board-copy="<?= h($meta($project)) ?>" data-board-scale="<?= h($project['area']) ?>" data-board-role="<?= h($project['role']) ?>" data-board-image="<?= h(oneh_img_url($project['image'])) ?>" data-board-alt="<?= h($project['title']) ?> project visual">
                  <span class="project-board__index"><?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                  <span class="project-board__name"><?= h($project['title']) ?></span>
                  <span class="project-board__detail"><?= h($detail($project)) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <section class="section fade-in" data-od-id="home-who">
        <div class="container home-intro">
          <div class="home-intro__copy">
            <span class="section__eyebrow"><?= h($c['who']['eyebrow']) ?></span>
            <h2 class="section__title"><?= h($c['who']['title']) ?><?php if ($c['who']['title_cn'] !== ''): ?> <span class="cn"><?= h($c['who']['title_cn']) ?></span><?php endif; ?></h2>
            <p class="section__lead"><?= h($c['who']['lead']) ?></p>
          </div>
          <?php if ($c['who']['card'] !== ''): ?>
          <div class="home-intro__card card">
            <p><?= h($c['who']['card']) ?></p>
          </div>
          <?php endif; ?>
        </div>
      </section>

      <section class="section section--dark fade-in" data-od-id="home-works">
        <div class="container">
<?php oneh_section_head($c['works']); ?>

          <?php if ($selectedProjects): ?>
          <div class="work-grid work-grid--home">
            <?php foreach ($selectedProjects as $index => $project): ?>
              <article class="work-card<?= $index === 0 ? ' work-card--featured' : '' ?> fade-in">
                <?= oneh_img($project['image'], $project['title'] . ' project visual', ['sizes' => $index === 0 ? '(min-width: 1024px) 66vw, 100vw' : '(min-width: 1024px) 33vw, 100vw']) ?>
                <div class="work-card__body">
                  <?php if ($index === 0): ?><span class="badge">Featured</span><?php endif; ?>
                  <h3><a href="<?= h($project['link']) ?>"><?= h($project['title']) ?></a></h3>
                  <p><?= h($meta($project)) ?></p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <div class="section__actions">
            <a class="button button--primary" href="projects.php">View All Projects</a>
          </div>
        </div>
      </section>

      <section class="section fade-in" data-od-id="home-types">
        <div class="container">
<?php oneh_section_head($c['capabilities']); ?>

          <div class="category-grid category-grid--home">
            <?php foreach ($c['capabilities']['items'] as $item): ?>
            <article class="category-card fade-in">
              <?php if ($item['image'] !== ''): ?><?= oneh_img($item['image'], $item['title'] . ' visual', ['sizes' => '(min-width: 1024px) 25vw, 50vw']) ?><?php endif; ?>
              <h3><?= h($item['title']) ?></h3>
              <p><?= h($item['text']) ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section--dark fade-in" data-od-id="home-why">
        <div class="container">
<?php oneh_section_head($c['why']); ?>
          <div class="why-grid">
            <?php foreach ($c['why']['items'] as $index => $item): ?>
            <article class="why-card fade-in">
              <span class="badge badge--accent"><?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
              <h3><?= h($item['title']) ?></h3>
              <p><?= h($item['text']) ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section fade-in" data-od-id="home-cta">
        <div class="container cta-split">
          <div class="cta-split__copy card">
            <span class="section__eyebrow"><?= h($c['cta']['eyebrow']) ?></span>
            <h2 class="section__title"><?= h($c['cta']['title']) ?></h2>
            <p class="section__lead"><?= h($c['cta']['lead']) ?></p>
            <div class="section__actions">
              <a class="button button--primary" href="contact.php">Book a Consultation</a>
              <a class="button button--secondary" href="about.php">Meet the Team</a>
            </div>
          </div>
          <?php if ($c['cta']['image'] !== ''): ?>
          <figure class="cta-split__media">
            <?= oneh_img($c['cta']['image'], 'Office and reception contact visual', ['sizes' => '(min-width: 1024px) 50vw, 100vw']) ?>
          </figure>
          <?php endif; ?>
        </div>
      </section>
    </main>
<?php oneh_render_footer($c['footer']); ?>
