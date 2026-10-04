<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';

$settings = oneh_get_settings();
$c = oneh_get_content('team');
$team = $settings['team'] ?? oneh_default_settings()['team'];
$founders = is_array($team['founders'] ?? null) ? $team['founders'] : [];
$designTeam = is_array($team['designTeam'] ?? null) ? $team['designTeam'] : [];
$renderMember = function (array $member): void {
    $name = trim((string) ($member['name'] ?? ''));
    $title = trim((string) ($member['title'] ?? ''));
    $label = trim((string) ($member['label'] ?? ''));
    $photo = trim((string) ($member['photo'] ?? '')) ?: 'img/t1.jpg';
    if ($name === '' && $title === '') {
        return;
    }
    echo '<article class="team-member">';
    echo oneh_img($photo, trim($name . ', ' . $title, ', '), ['sizes' => '(min-width: 1024px) 25vw, 50vw']);
    echo '<div class="team-member__body">';
    if ($label !== '') {
        echo '<span>' . h($label) . '</span>';
    }
    echo '<h2>' . h($name) . '</h2>';
    if ($title !== '') {
        echo '<p>' . h($title) . '</p>';
    }
    echo '</div></article>';
};

oneh_render_head([
    'key' => 'team',
    'title' => $c['seo_title'],
    'description' => $c['seo_description'],
    'path' => 'team.php',
]);
oneh_render_header('about');
?>
    <main class="page" id="content">
      <section class="team-hero fade-in">
        <div class="container team-hero__grid">
          <div>
            <span class="section__eyebrow"><?= h($c['eyebrow']) ?></span>
            <h1 class="team-hero__title"><?= h($c['title']) ?></h1>
          </div>
          <p class="team-hero__lead"><?= h($c['lead']) ?></p>
        </div>
      </section>
      <?php if ($founders): ?>
      <section class="section team-section fade-in">
        <div class="container">
          <div class="team-section__head">
            <span class="section__eyebrow"><?= h($c['founders_label']) ?></span>
          </div>
          <div class="team-grid team-grid--founders">
            <?php foreach ($founders as $member): $renderMember($member); endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($designTeam): ?>
      <section class="section section--dark team-section fade-in">
        <div class="container">
          <div class="team-section__head">
            <span class="section__eyebrow"><?= h($c['design_label']) ?></span>
            <?php if ($c['design_note'] !== ''): ?><p><?= h($c['design_note']) ?></p><?php endif; ?>
          </div>
          <div class="team-grid">
            <?php foreach ($designTeam as $member): $renderMember($member); endforeach; ?>
          </div>
        </div>
      </section>
      <?php endif; ?>
    </main>
<?php oneh_render_footer($c['footer']); ?>
