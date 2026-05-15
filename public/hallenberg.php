<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/hallenberg.php';

$story = app_hallenberg_story();
$sections = app_hallenberg_sections();
$overviewCards = app_hallenberg_overview_cards();
$timeline = app_hallenberg_timeline();
$futureProducts = app_hallenberg_future_products();
$initialFutureProduct = $futureProducts[0];
$projectDocuments = [
    [
        'label' => 'PDF-Dokumentation',
        'file' => 'hallenberg-projektdokumentation-modern.pdf',
        'summary' => 'Druck- und Freigabefassung fuer Weitergabe, Review und Ablage.',
    ],
    [
        'label' => 'DOCX-Arbeitsfassung',
        'file' => 'hallenberg-projektdokumentation.docx',
        'summary' => 'Bearbeitbare Version fuer redaktionelle Anpassungen und Fortschreibung.',
    ],
    [
        'label' => 'Markdown-Quelle',
        'file' => 'hallenberg-projektdokumentation.md',
        'summary' => 'Textuelle Projektbasis fuer Repo-Pflege, Versionierung und inhaltliche Ableitung.',
    ],
];

render_page('Hallenberg', 'Referenzprojekt', static function () use ($story, $sections, $overviewCards, $timeline, $futureProducts, $initialFutureProduct, $projectDocuments): void {
    ?>
    <section class="hallenberg-page">
      <section class="project-shell-header">
        <a class="project-shell-back" href="<?= app_h(app_url('workspace.php')) ?>">Zurueck zur Zentrale</a>
        <div class="project-shell-copy">
          <span class="card-label">Projektshowcase</span>
          <strong>Hallenberg</strong>
        </div>
      </section>

      <section class="chapter-pager card" aria-label="Kapitelsteuerung">
        <div class="chapter-pager-meta">
          <span class="card-label">Kapitelmodus</span>
          <strong id="chapter-current-title">Projekt</strong>
          <p id="chapter-current-position">Kapitel 1 von <?= count($sections) ?></p>
        </div>
        <div class="chapter-pager-actions">
          <button class="btn btn-ghost" id="chapter-prev" type="button">Zurueck</button>
          <button class="btn btn-primary" id="chapter-next" type="button">Weiter</button>
        </div>
      </section>

      <article class="hallenberg-hero-card" id="hero" data-chapter="hero">
        <div class="hallenberg-hero-media">
          <?php foreach ($story['hero']['media'] as $index => $media): ?>
            <div class="hero-media-frame <?= $index === 0 ? 'is-primary' : 'is-secondary' ?>">
              <img src="<?= app_h($media['src']) ?>" alt="<?= app_h($media['alt']) ?>" loading="<?= $index === 0 ? 'eager' : 'lazy' ?>">
            </div>
          <?php endforeach; ?>
        </div>
        <div class="hallenberg-hero-copy">
          <span class="card-label"><?= app_h($story['hero']['eyebrow']) ?></span>
          <h2><?= app_h($story['hero']['title']) ?></h2>
          <p class="hallenberg-hero-subtitle"><?= app_h($story['hero']['subtitle']) ?></p>
          <p class="hallenberg-lead"><?= app_h($story['hero']['text']) ?></p>
          <div class="button-row hallenberg-hero-actions">
            <a class="btn btn-primary" href="#overview">Projektdokumentation ansehen</a>
            <a class="btn btn-secondary" href="#documents">Unterlagen</a>
            <a class="btn btn-secondary" href="#engineering">Technische Details</a>
            <a class="btn btn-ghost" href="#timeline">Baufortschritt</a>
          </div>
        </div>
      </article>

      <section class="hallenberg-sticky-nav card" aria-label="Kapitelnavigation">
        <?php foreach ($sections as $section): ?>
          <a href="#<?= app_h($section['id']) ?>" data-chapter-link="<?= app_h($section['id']) ?>"><?= app_h($section['label']) ?></a>
        <?php endforeach; ?>
      </section>

      <section class="hallenberg-section stack" id="overview" data-chapter="overview">
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

      <section class="hallenberg-section" id="documents" data-chapter="documents">
        <div class="section-heading">
          <span class="card-label">Projektunterlagen</span>
          <h3>Direkte Downloads fuer Review, Ablage und Weiterbearbeitung</h3>
          <p>Die Hallenberg-Dokumentation liegt zusaetzlich als PDF, DOCX und Markdown direkt im Projekt. So bleiben Freigabefassung, bearbeitbare Version und Repo-Quelle synchron verfuegbar.</p>
        </div>
        <div class="grid three-up">
          <?php foreach ($projectDocuments as $document): ?>
            <article class="card">
              <span class="card-label">Download</span>
              <h3><?= app_h($document['label']) ?></h3>
              <p><?= app_h($document['summary']) ?></p>
              <div class="button-row">
                <a class="btn btn-primary" href="<?= app_h(app_url($document['file'])) ?>" target="_blank" rel="noreferrer">Oeffnen</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </section>

      <section class="hallenberg-section split-media" id="situation" data-chapter="situation">
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

      <section class="hallenberg-section engineering-stage" id="engineering" data-chapter="engineering">
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

      <section class="hallenberg-section split-media" id="substructure" data-chapter="substructure">
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

      <section class="hallenberg-section" id="modules" data-chapter="modules">
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

      <section class="hallenberg-section hallenberg-drone-section" id="drone" data-chapter="drone">
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

      <section class="hallenberg-section split-media" id="technical" data-chapter="technical">
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

      <section class="hallenberg-section split-media" id="scaffold" data-chapter="scaffold">
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

      <section class="hallenberg-section hallenberg-timeline-section" id="timeline" data-chapter="timeline">
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

      <section class="hallenberg-section future-grid" id="future" data-chapter="future">
        <div class="section-copy">
          <span class="card-label">Smart Energy & Zukunft</span>
          <h3>Speicher, Wallbox, Monitoring und spaetere Waermepumpe</h3>
          <p><?= app_h($story['future']['text']) ?></p>
        </div>
        <div class="future-product-grid" role="tablist" aria-label="Smart-Energy-Produkte">
          <?php foreach ($futureProducts as $index => $product): ?>
            <button
              class="future-product-card<?= $index === 0 ? ' is-active' : '' ?>"
              type="button"
              role="tab"
              aria-selected="<?= $index === 0 ? 'true' : 'false' ?>"
              data-future-target="<?= app_h($product['id']) ?>"
            >
              <span class="future-product-icon"><?= app_h($product['icon']) ?></span>
              <span class="future-product-copy">
                <strong><?= app_h($product['title']) ?></strong>
                <span><?= app_h($product['subtitle']) ?></span>
              </span>
            </button>
          <?php endforeach; ?>
        </div>
        <div class="future-product-stage" aria-live="polite">
          <article class="future-product-detail is-active" id="future-product-detail">
            <div class="future-product-detail-copy">
              <span class="card-label" id="future-detail-label"><?= app_h($initialFutureProduct['subtitle']) ?></span>
              <h4 id="future-detail-title"><?= app_h($initialFutureProduct['title']) ?></h4>
              <p id="future-detail-text"><?= app_h($initialFutureProduct['text']) ?></p>
              <ul class="simple-list hallenberg-list" id="future-detail-facts">
                <?php foreach ($initialFutureProduct['facts'] as $fact): ?>
                  <li><?= app_h($fact) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
            <div class="future-product-media-stack" id="future-detail-media">
              <?php foreach (($initialFutureProduct['media_gallery'] ?? [$initialFutureProduct['media']]) as $mediaItem): ?>
                <figure class="premium-figure future-product-figure" data-lightbox-src="<?= app_h($mediaItem['src']) ?>" data-lightbox-alt="<?= app_h($mediaItem['alt']) ?>">
                  <img src="<?= app_h($mediaItem['src']) ?>" alt="<?= app_h($mediaItem['alt']) ?>" loading="lazy">
                  <figcaption><?= app_h($mediaItem['alt']) ?></figcaption>
                </figure>
              <?php endforeach; ?>
            </div>
          </article>
        </div>
      </section>

      <section class="hallenberg-finale card" id="finale" data-chapter="finale">
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

        var futureCards = document.querySelectorAll('[data-future-target]');
        var futureDetailLabel = document.getElementById('future-detail-label');
        var futureDetailTitle = document.getElementById('future-detail-title');
        var futureDetailText = document.getElementById('future-detail-text');
        var futureDetailFacts = document.getElementById('future-detail-facts');
        var futureDetailMedia = document.getElementById('future-detail-media');
        var futureData = <?= json_encode($futureProducts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        var chapters = Array.prototype.slice.call(document.querySelectorAll('[data-chapter]'));
        var chapterLinks = Array.prototype.slice.call(document.querySelectorAll('[data-chapter-link]'));
        var chapterPrev = document.getElementById('chapter-prev');
        var chapterNext = document.getElementById('chapter-next');
        var chapterCurrentTitle = document.getElementById('chapter-current-title');
        var chapterCurrentPosition = document.getElementById('chapter-current-position');
        var chapterData = <?= json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        var chapterAnchors = Array.prototype.slice.call(document.querySelectorAll('a[href^="#"]'));
        var chapterLookup = {};
        var activeChapterId = '';

        chapterData.forEach(function (item) {
          chapterLookup[item.id] = item.label;
        });

        function getChapterIndex(chapterId) {
          var index = -1;

          chapters.some(function (chapterItem, chapterIndex) {
            if (chapterItem.getAttribute('data-chapter') === chapterId) {
              index = chapterIndex;
              return true;
            }

            return false;
          });

          return index;
        }

        function renderChapterState(chapterId, syncHash) {
          var index = getChapterIndex(chapterId);

          if (index < 0) {
            index = 0;
            chapterId = chapters.length ? chapters[0].getAttribute('data-chapter') || '' : '';
          }

          activeChapterId = chapterId;
          document.body.classList.add('chapter-mode-active');

          chapters.forEach(function (chapterItem) {
            var isActive = chapterItem.getAttribute('data-chapter') === chapterId;
            chapterItem.classList.toggle('is-active', isActive);
            chapterItem.toggleAttribute('hidden', !isActive);
          });

          chapterLinks.forEach(function (link) {
            var isActive = link.getAttribute('data-chapter-link') === chapterId;
            link.classList.toggle('is-active', isActive);
          });

          if (chapterCurrentTitle) {
            chapterCurrentTitle.textContent = chapterLookup[chapterId] || 'Projekt';
          }

          if (chapterCurrentPosition) {
            chapterCurrentPosition.textContent = 'Kapitel ' + String(index + 1) + ' von ' + String(chapters.length);
          }

          if (chapterPrev) {
            chapterPrev.disabled = index <= 0;
          }

          if (chapterNext) {
            chapterNext.disabled = index >= chapters.length - 1;
            chapterNext.textContent = index >= chapters.length - 1 ? 'Fertig' : 'Weiter';
          }

          if (syncHash && chapterId) {
            window.history.replaceState(null, '', '#' + chapterId);
          }

          window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function moveChapter(direction) {
          var currentIndex = getChapterIndex(activeChapterId);
          var nextIndex = currentIndex + direction;

          if (nextIndex < 0 || nextIndex >= chapters.length) {
            return;
          }

          renderChapterState(chapters[nextIndex].getAttribute('data-chapter') || '', true);
        }

        if (chapterPrev) {
          chapterPrev.addEventListener('click', function () {
            moveChapter(-1);
          });
        }

        if (chapterNext) {
          chapterNext.addEventListener('click', function () {
            moveChapter(1);
          });
        }

        chapterLinks.forEach(function (link) {
          link.addEventListener('click', function (event) {
            var chapterId = link.getAttribute('data-chapter-link') || '';

            if (!chapterId) {
              return;
            }

            event.preventDefault();
            renderChapterState(chapterId, true);
          });
        });

        chapterAnchors.forEach(function (anchor) {
          anchor.addEventListener('click', function (event) {
            var targetId = (anchor.getAttribute('href') || '').replace(/^#/, '');

            if (!targetId || getChapterIndex(targetId) < 0) {
              return;
            }

            event.preventDefault();
            renderChapterState(targetId, true);
          });
        });

        window.addEventListener('hashchange', function () {
          if (!window.location.hash) {
            return;
          }

          renderChapterState(window.location.hash.replace(/^#/, ''), false);
        });

        if (window.location.hash) {
          renderChapterState(window.location.hash.replace(/^#/, ''), false);
        } else if (chapters.length) {
          renderChapterState(chapters[0].getAttribute('data-chapter') || '', false);
        }

        function bindFigure(figure) {
          figure.addEventListener('click', function () {
            openLightbox(figure.getAttribute('data-lightbox-src') || '', figure.getAttribute('data-lightbox-alt') || '');
          });
        }

        function renderFutureProduct(product) {
          if (!product || !futureDetailLabel || !futureDetailTitle || !futureDetailText || !futureDetailFacts || !futureDetailMedia) {
            return;
          }

          futureDetailLabel.textContent = product.subtitle || '';
          futureDetailTitle.textContent = product.title || '';
          futureDetailText.textContent = product.text || '';
          futureDetailFacts.innerHTML = '';

          (product.facts || []).forEach(function (fact) {
            var item = document.createElement('li');
            item.textContent = fact;
            futureDetailFacts.appendChild(item);
          });

          futureDetailMedia.innerHTML = '';

          (product.media_gallery || [product.media]).forEach(function (mediaItem) {
            if (!mediaItem || !mediaItem.src) {
              return;
            }

            var figure = document.createElement('figure');
            figure.className = 'premium-figure future-product-figure';
            figure.setAttribute('data-lightbox-src', mediaItem.src);
            figure.setAttribute('data-lightbox-alt', mediaItem.alt || '');

            var imageElement = document.createElement('img');
            imageElement.src = mediaItem.src;
            imageElement.alt = mediaItem.alt || '';
            imageElement.loading = 'lazy';

            var captionElement = document.createElement('figcaption');
            captionElement.textContent = mediaItem.alt || '';

            figure.appendChild(imageElement);
            figure.appendChild(captionElement);
            bindFigure(figure);
            futureDetailMedia.appendChild(figure);
          });
        }

        if (futureCards.length && futureData.length) {
          futureCards.forEach(function (card) {
            card.addEventListener('click', function () {
              var target = card.getAttribute('data-future-target');

              futureCards.forEach(function (item) {
                var active = item === card;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
              });

              renderFutureProduct(futureData.find(function (item) {
                return item.id === target;
              }) || null);
            });
          });
        }
      }());
    </script>
    <?php
}, [
    'show_header' => false,
    'show_breadcrumb' => false,
    'show_footer' => false,
    'body_class' => 'project-body',
    'shell_class' => 'page-shell-standalone',
    'content_shell_class' => 'content-shell-standalone',
]);
