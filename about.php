<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';

$c = oneh_get_content('about');
$projects = oneh_get_projects(false);

oneh_render_head([
    'key' => 'about',
    'title' => $c['seo_title'],
    'description' => $c['seo_description'],
    'path' => 'about.php',
]);
oneh_render_header('about');
?>
    <main class="page" id="content">
      <section class="hero fade-in" data-od-id="about-hero">
        <div class="container hero__grid">
          <div class="hero__copy">
            <span class="hero__eyebrow"><?= h($c['hero']['eyebrow']) ?></span>
            <h1 class="hero__title"><?= h($c['hero']['title']) ?></h1>
            <p class="hero__lead"><?= h($c['hero']['lead']) ?><?php if ($c['hero']['lead_cn'] !== ''): ?> <span class="cn"><?= h($c['hero']['lead_cn']) ?></span><?php endif; ?></p>
            <div class="hero__actions">
              <a class="button button--primary" href="projects.php">See Project Proof</a>
              <a class="button button--secondary" href="contact.php">Start a Conversation</a>
            </div>
          </div>
          <aside class="hero__panel">
            <?= oneh_random_visual($projects, 'Brand visual') ?>
          </aside>
        </div>
      </section>

      <section class="section fade-in" data-od-id="about-story">
        <div class="container">
<?php oneh_section_head($c['story']); ?>

          <div class="timeline">
            <?php foreach ($c['story']['timeline'] as $item): ?>
            <article class="timeline__item fade-in">
              <div class="timeline__year"><?= h($item['year']) ?></div>
              <div>
                <h3 class="timeline__title"><?= h($item['title']) ?></h3>
                <p class="timeline__text"><?= h($item['text']) ?></p>
              </div>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section--dark fade-in" data-od-id="about-values">
        <div class="container">
<?php oneh_section_head($c['values']); ?>

          <div class="card-grid card-grid--2">
            <?php foreach ($c['values']['items'] as $index => $item): ?>
            <article class="card card--dark fade-in">
              <span class="badge badge--dark"><?= h(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
              <h3 class="card__title"><?= h($item['title']) ?></h3>
              <p class="card__lead"><?= h($item['text']) ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section fade-in" data-od-id="about-team">
        <div class="container">
          <div class="section__head">
            <div>
              <span class="section__eyebrow"><?= h($c['team']['eyebrow']) ?></span>
              <h2 class="section__title"><?= h($c['team']['title']) ?></h2>
            </div>
            <div class="about-team__intro">
              <p class="section__lead"><?= h($c['team']['lead']) ?></p>
              <a class="button button--primary" href="team.php">Founders &amp; Design Team</a>
            </div>
          </div>

          <div class="card-grid card-grid--3">
            <?php foreach ($c['team']['items'] as $item): ?>
            <article class="card fade-in">
              <?php if ($item['image'] !== ''): ?>
                <div class="ph-img ph-img--tall" style="background-image:url('<?= h(oneh_img_url($item['image'], 640)) ?>')"><span class="ph-img__label"><?= h($item['label']) ?></span></div>
              <?php else: ?>
                <div class="ph-img ph-img--tall"><span class="ph-img__label"><?= h($item['label']) ?></span></div>
              <?php endif; ?>
              <h3 class="card__title"><?= h($item['title']) ?></h3>
              <p class="card__lead"><?= h($item['text']) ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section section--dark fade-in" data-od-id="about-credentials">
        <div class="container">
<?php oneh_section_head($c['credentials']); ?>

          <div class="list">
            <?php foreach ($c['credentials']['items'] as $item): ?>
            <article class="list__item fade-in">
              <h3 class="list__title"><?= h($item['title']) ?></h3>
              <p class="list__text"><?= h($item['text']) ?></p>
            </article>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="section fade-in" data-od-id="about-cta">
        <div class="container cta-split">
          <div class="cta-split__copy card">
            <span class="section__eyebrow"><?= h($c['cta']['eyebrow']) ?></span>
            <h2 class="section__title"><?= h($c['cta']['title']) ?></h2>
            <p class="section__lead"><?= h($c['cta']['lead']) ?></p>
            <div class="section__actions">
              <a class="button button--primary" href="projects.php">Go to Projects</a>
              <a class="button button--secondary" href="contact.php">Contact 1+H</a>
            </div>
          </div>
          <?php if ($c['cta']['image'] !== ''): ?>
          <figure class="cta-split__media">
            <?= oneh_img($c['cta']['image'], 'About 1+H studio image', ['sizes' => '(min-width: 1024px) 50vw, 100vw']) ?>
          </figure>
          <?php endif; ?>
        </div>
      </section>
    </main>
<?php oneh_render_footer($c['footer']); ?>
