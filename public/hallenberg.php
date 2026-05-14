<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/hallenberg.php';

$story = app_hallenberg_story();
$sections = app_hallenberg_sections();
$overviewCards = app_hallenberg_overview_cards();
$timeline = app_hallenberg_timeline();

render_page('Hallenberg', 'Referenzprojekt', static function () use ($story, $sections, $overviewCards, $timeline): void {
    ?>
    <section class="hallenberg-page">
      <article class="hallenberg-hero-card">
        <div class="hallenberg-hero-media">
          <?php foreach ($story['hero']['media'] as $index => $media): ?>
            <div class="hero-media-frame <?= $index === 0 ? 'is-primary' : 'is-secondary' ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="hallenberg-hero-copy" id="hero">
          <span class="card-label"><?= app_h($story['hero']['eyebrow']) ?></span>
          <h2><?= app_h($story['hero']['title']) ?></h2>
          <p class="hallenberg-hero-subtitle"><?= app_h($story['hero']['subtitle']) ?></p>
          <p class="hallenberg-lead"><?= app_h($story['hero']['text']) ?></p>
          <div class="button-row hallenberg-hero-actions">
            <a class="btn btn-primary" href="#overview">Projektdokumentation ansehen</a>
            <a class="btn btn-secondary" href="#engineering">Technische Details</a>
            <a class="btn btn-ghost" href="#timeline">Baufortschritt</a>
          </div>
        </div>
      </article>

      <section class="hallenberg-sticky-nav card" aria-label="Kapitelnavigation">
        <?php foreach ($sections as $section): ?>
          <a href="#<?= app_h($section['id']) ?>"><?= app_h($section['label']) ?></a>
        <?php endforeach; ?>
      </section>

      <section class="hallenberg-section stack" id="overview">
        <div class="section-heading">
          <span class="card-label">Projektuebersicht</span>
          <h3>Vom Fachwerkhaus zur Premium-Energieplattform</h3>
          <p>Die Seite verbindet Architektur, Baustellendokumentation, Medienkuratierung und technische Planung zu einem hochwertigen Referenzprojekt innerhalb von webapp-central.de.</p>
        </div>
        <div class="hallenberg-overview-grid">
          <?php foreach ($overviewCards as $card): ?>
            <article class="overview-chip-card">
              <span class="overview-icon"><?= app_h($card['icon']) ?></span>
              <div>
                <strong><?= app_h($card['title']) ?></strong>
                <p><?= app_h($card['text']) ?></p>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section split-media" id="situation">
        <div class="section-copy">
          <span class="card-label">Ausgangssituation</span>
          <h3>Historische Struktur, anspruchsvolle Dachgeometrie</h3>
          <p><?= app_h($story['situation']['text']) ?></p>
          <ul class="simple-list hallenberg-list">
            <?php foreach ($story['situation']['bullets'] as $bullet): ?>
              <li><?= app_h($bullet) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="dual-visual-stage">
          <?php foreach ($story['situation']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section engineering-stage" id="engineering">
        <div class="section-heading">
          <span class="card-label">Planning & Engineering</span>
          <h3>23,4 kWp Planung, Stringlogik und bauliche Freigaben</h3>
          <p><?= app_h($story['engineering']['text']) ?></p>
        </div>
        <div class="grid two-up">
          <article class="dark-info-card">
            <h4>Technische Kernpunkte</h4>
            <ul class="simple-list hallenberg-list">
              <?php foreach ($story['engineering']['bullets'] as $bullet): ?>
                <li><?= app_h($bullet) ?></li>
              <?php endforeach; ?>
            </ul>
          </article>
          <div class="dual-visual-stage">
            <?php foreach ($story['engineering']['media'] as $media): ?>
              <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
                <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
                <figcaption><?= app_h($media['alt']) ?></figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <section class="hallenberg-section split-media" id="substructure">
        <div class="section-copy">
          <span class="card-label">Unterkonstruktion</span>
          <h3>Dachhaken, Schienen und Lastverteilung als statische Basis</h3>
          <p><?= app_h($story['substructure']['text']) ?></p>
          <ul class="simple-list hallenberg-list">
            <?php foreach ($story['substructure']['bullets'] as $bullet): ?>
              <li><?= app_h($bullet) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="dual-visual-stage">
          <?php foreach ($story['substructure']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section" id="modules">
        <div class="section-heading">
          <span class="card-label">Modulmontage</span>
          <h3>Full-Black-Optik, Spiegelungen und eine ruhige Dachzeichnung</h3>
          <p><?= app_h($story['modules']['text']) ?></p>
        </div>
        <div class="hallenberg-masonry">
          <?php foreach ($story['modules']['media'] as $media): ?>
            <figure class="premium-figure is-masonry" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section hallenberg-drone-section" id="drone">
        <div class="section-heading">
          <span class="card-label">Drohnenaufnahmen</span>
          <h3>Topdown-Perspektiven und die Gesamtwirkung im Ortsbild</h3>
          <p><?= app_h($story['drone']['text']) ?></p>
        </div>
        <div class="drone-showcase-grid">
          <?php foreach ($story['drone']['media'] as $index => $media): ?>
            <figure class="premium-figure <?= $index === 0 ? 'is-wide' : '' ?>" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section split-media" id="technical">
        <div class="section-copy">
          <span class="card-label">Technik & Kabelwege</span>
          <h3>Vom Fallrohr durch den Keller in den Technikbereich</h3>
          <p><?= app_h($story['technical']['text']) ?></p>
          <ul class="simple-list hallenberg-list">
            <?php foreach ($story['technical']['bullets'] as $bullet): ?>
              <li><?= app_h($bullet) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="dual-visual-stage">
          <?php foreach ($story['technical']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section split-media" id="scaffold">
        <div class="section-copy">
          <span class="card-label">Geruest & Montagelogistik</span>
          <h3>Zugangssysteme, Podeste und Materialwege</h3>
          <p><?= app_h($story['scaffold']['text']) ?></p>
        </div>
        <div class="dual-visual-stage">
          <?php foreach ($story['scaffold']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section hallenberg-timeline-section" id="timeline">
        <div class="section-heading">
          <span class="card-label">Baufortschritt</span>
          <h3>Timeline von Bestand bis Fertigstellung</h3>
          <p>Die horizontale Timeline verbindet die mediale Projekterzaehlung mit den dokumentierten Bauphasen und den technischen Entscheidungen auf der Baustelle.</p>
        </div>
        <div class="timeline-strip" role="list">
          <?php foreach ($timeline as $step): ?>
            <article class="timeline-step" role="listitem">
              <span class="timeline-dot"></span>
              <strong><?= app_h($step['phase']) ?></strong>
              <p><?= app_h($step['text']) ?></p>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="drone-showcase-grid timeline-gallery">
          <?php foreach ($story['timeline']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
              <figcaption><?= app_h($media['alt']) ?></figcaption>
            </figure>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section future-grid" id="future">
        <div class="section-copy">
          <span class="card-label">Smart Energy & Zukunft</span>
          <h3>Speicher, Wallbox, Monitoring und spaetere Waermepumpe</h3>
          <p><?= app_h($story['future']['text']) ?></p>
        </div>
        <div class="grid two-up">
          <?php foreach ($story['future']['bullets'] as $bullet): ?>
            <article class="future-card">
              <strong><?= app_h($bullet) ?></strong>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-finale card" id="finale">
        <div class="hallenberg-finale-copy">
          <span class="card-label">Gesamtwirkung</span>
          <h3><?= app_h($story['finale']['title']) ?></h3>
          <p><?= app_h($story['finale']['text']) ?></p>
          <a class="btn btn-primary" href="#hero">Zurueck zur Titelbuehne</a>
        </div>
        <div class="hallenberg-finale-media">
          <?php foreach ($story['finale']['media'] as $media): ?>
            <figure class="premium-figure" data-lightbox-src="<?= app_h($media['src']) ?>" data-lightbox-alt="<?= app_h($media['alt']) ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="lazy">
            </figure>
          <?php endforeach; ?>
        </div>
      </section>
    </section>

    <div class="lightbox-shell" hidden aria-hidden="true">
      <button class="lightbox-close" type="button" aria-label="Lightbox schliessen">Schliessen</button>
      <img class="lightbox-image" src="" alt="">
      <p class="lightbox-caption"></p>
    </div>

    <script>
      (function () {
        var figures = document.querySelectorAll('[data-lightbox-src]');
        var shell = document.querySelector('.lightbox-shell');
        var image = document.querySelector('.lightbox-image');
        var caption = document.querySelector('.lightbox-caption');
        var close = document.querySelector('.lightbox-close');

        if (!figures.length || !shell || !image || !caption || !close) {
          return;
        }

        function closeLightbox() {
          shell.hidden = true;
          shell.setAttribute('aria-hidden', 'true');
          image.removeAttribute('src');
          image.alt = '';
          caption.textContent = '';
          document.body.classList.remove('lightbox-open');
        }

        function openLightbox(src, alt) {
          image.src = src;
          image.alt = alt;
          caption.textContent = alt;
          shell.hidden = false;
          shell.setAttribute('aria-hidden', 'false');
          document.body.classList.add('lightbox-open');
        }

        figures.forEach(function (figure) {
          figure.addEventListener('click', function () {
            openLightbox(figure.getAttribute('data-lightbox-src') || '', figure.getAttribute('data-lightbox-alt') || '');
          });
        });

        close.addEventListener('click', closeLightbox);
        shell.addEventListener('click', function (event) {
          if (event.target === shell) {
            closeLightbox();
          }
        });

        document.addEventListener('keydown', function (event) {
          if (event.key === 'Escape' && !shell.hidden) {
            closeLightbox();
          }
        });
      }());
    </script>
    <?php
});
