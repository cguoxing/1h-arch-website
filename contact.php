<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/inquiries.php';

$c = oneh_get_content('contact');
$projects = oneh_get_projects(false);
$sent = isset($_GET['sent']);
$formError = (string) ($_GET['error'] ?? '');
$channels = $c['channels'];

oneh_render_head([
    'key' => 'contact',
    'title' => $c['seo_title'],
    'description' => $c['seo_description'],
    'path' => 'contact.php',
]);
oneh_render_header('contact');
?>
    <main class="page" id="content">
      <section class="hero fade-in" data-od-id="contact-hero">
        <div class="container hero__grid">
          <div class="hero__copy">
            <span class="hero__eyebrow"><?= h($c['hero']['eyebrow']) ?></span>
            <h1 class="hero__title"><?= h($c['hero']['title']) ?></h1>
            <p class="hero__lead"><?= h($c['hero']['lead']) ?><?php if ($c['hero']['lead_cn'] !== ''): ?> <span class="cn"><?= h($c['hero']['lead_cn']) ?></span><?php endif; ?></p>
            <div class="hero__actions">
              <a class="button button--primary" href="#inquiry">Fill Out Form</a>
              <a class="button button--secondary" href="projects.php">View Projects</a>
            </div>
            <?php if ($c['social']): ?>
            <ul class="contact-social" aria-label="Social platforms">
              <?php foreach ($c['social'] as $social): ?>
              <li>
                <?php if ($social['url'] !== ''): ?>
                <a class="contact-social__item" href="<?= h($social['url']) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= h($social['name']) ?>">
                  <img class="contact-social__icon" src="<?= h($social['icon']) ?>" alt="">
                </a>
                <?php else: ?>
                <span class="contact-social__item" aria-label="<?= h($social['name']) ?>">
                  <img class="contact-social__icon" src="<?= h($social['icon']) ?>" alt="">
                </span>
                <?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
          </div>
          <aside class="hero__panel">
            <?= oneh_random_visual($projects, 'Contact visual') ?>
          </aside>
        </div>
      </section>

      <section class="section fade-in" data-od-id="contact-channels">
        <div class="container">
<?php oneh_section_head($channels); ?>

          <div class="contact-grid">
            <article class="card contact-card fade-in">
              <h3 class="contact-card__title">Email</h3>
              <?php foreach ($channels['emails'] as $email): ?>
              <p class="contact-card__text"><a href="mailto:<?= h($email) ?>"><?= h($email) ?></a></p>
              <?php endforeach; ?>
              <?php if ($channels['phone'] !== ''): ?>
              <p class="contact-card__text"><a href="tel:<?= h(preg_replace('/[^0-9+]/', '', $channels['phone'])) ?>"><?= h($channels['phone']) ?></a></p>
              <?php endif; ?>
            </article>
            <article class="card contact-card fade-in">
              <h3 class="contact-card__title">Address</h3>
              <p class="contact-card__text"><?= h($channels['address']) ?></p>
            </article>
            <article class="card contact-card fade-in">
              <h3 class="contact-card__title">Response time</h3>
              <p class="contact-card__text"><?= h($channels['response']) ?></p><span class="badge badge--accent">Response</span>
            </article>
          </div>
        </div>
      </section>

      <section class="section section--dark fade-in" id="inquiry" data-od-id="contact-form">
        <div class="container">
<?php oneh_section_head($c['form']); ?>

          <form class="form" data-inquiry-form action="api/inquiry.php" method="post" novalidate>
            <input type="hidden" name="_token" value="<?= h(oneh_inquiry_form_token()) ?>">
            <div class="form-hp" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden">
              <label for="website">Website</label>
              <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>
            <div class="form-grid">
              <div class="field">
                <label for="name">Your Name</label>
                <input id="name" name="name" type="text" required maxlength="120" autocomplete="name" placeholder="How should we address you?">
              </div>
              <div class="field">
                <label for="company">Company / Organization</label>
                <input id="company" name="company" type="text" maxlength="200" autocomplete="organization" placeholder="For example: developer, company, school, or hotel brand">
              </div>
              <div class="field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required maxlength="200" autocomplete="email" placeholder="name@example.com">
              </div>
              <div class="field">
                <label for="phone">Phone / WeChat (optional)</label>
                <input id="phone" name="phone" type="text" maxlength="60" autocomplete="tel" placeholder="手机或微信，选填">
              </div>
              <div class="field">
                <label for="projectType">Project Type</label>
                <select id="projectType" name="projectType" required>
                  <option value="">Select a type</option>
                  <?php foreach ($c['form']['project_types'] as $type): ?>
                  <option><?= h($type) ?></option>
                  <?php endforeach; ?>
                  <option>Other</option>
                </select>
              </div>
              <div class="field">
                <label for="brief">Project Brief</label>
                <textarea id="brief" name="brief" required maxlength="5000" placeholder="Share the project background, location, budget range, or the main question you want us to solve."></textarea>
              </div>
            </div>

            <div class="form__actions">
              <button class="button button--primary" type="submit" data-inquiry-submit>Send Inquiry</button>
              <button class="button button--secondary" type="reset">Reset</button>
            </div>

            <div class="notice<?= $sent ? ' notice--success' : ($formError !== '' ? ' notice--error' : '') ?>" data-form-status role="status" aria-live="polite"><?php
              if ($sent) {
                  echo h($c['form']['success']);
              } elseif ($formError !== '') {
                  echo h(oneh_inquiry_error_message($formError));
              } else {
                  echo 'Complete the required fields and submit the inquiry.';
              }
            ?></div>
          </form>
        </div>
      </section>

      <section class="section fade-in" data-od-id="contact-location">
        <div class="container">
<?php oneh_section_head($c['location']); ?>

          <div class="card-grid card-grid--2">
            <article class="card fade-in">
              <?php if ($c['location']['image'] !== ''): ?>
                <div class="ph-img ph-img--hero" style="background-image:url('<?= h(oneh_img_url($c['location']['image'])) ?>')"><span class="ph-img__label">Office location</span></div>
              <?php else: ?>
                <div class="ph-img ph-img--hero"><span class="ph-img__label">Office location</span></div>
              <?php endif; ?>
            </article>
            <article class="card fade-in">
              <h3 class="card__title"><?= h($c['location']['note_title']) ?></h3>
              <p class="card__lead"><?= h($c['location']['note_text']) ?></p>
              <?php if ($c['location']['notice'] !== ''): ?><div class="notice"><?= h($c['location']['notice']) ?></div><?php endif; ?>
            </article>
          </div>
        </div>
      </section>
    </main>
<?php oneh_render_footer($c['footer']); ?>
