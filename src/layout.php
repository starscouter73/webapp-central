<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (!function_exists('render_page')) {
    function render_page(string $title, string $eyebrow, callable $content, array $options = []): void
    {
        $currentPage = app_current_page();
        $currentMeta = app_current_page_meta();
        $currentNavParent = (string)($currentMeta['nav_parent'] ?? $currentPage);
        $showHeader = (bool)($options['show_header'] ?? true);
        $showBreadcrumb = (bool)($options['show_breadcrumb'] ?? true);
        $showFooter = (bool)($options['show_footer'] ?? true);
        $bodyClass = trim((string)($options['body_class'] ?? ''));
        $shellClass = trim((string)($options['shell_class'] ?? ''));
        $contentShellClass = trim((string)($options['content_shell_class'] ?? ''));
        $headerClass = '';

        if (!headers_sent()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
        }
        ?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= app_h($title) ?> | <?= app_h(app_site_title()) ?></title>
  <link rel="stylesheet" href="<?= app_h(app_asset_url('assets/app.css')) ?>">
  <style>
    .global-live-ticker {
      position: relative;
      overflow: hidden;
      border-top: 1px solid rgba(255,255,255,.08);
      border-bottom: 1px solid rgba(255,255,255,.08);
      background: linear-gradient(90deg,#111827 0%,#172033 50%,#111827 100%);
      padding: 10px 0;
      white-space: nowrap;
    }

    .global-live-ticker-track {
      display: inline-block;
      min-width: 100%;
      padding-left: 100%;
      animation: globalTickerMove 42s linear infinite;
      color: #eef4ff;
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .02em;
    }

    .global-live-ticker:hover .global-live-ticker-track {
      animation-play-state: paused;
    }

    .global-live-dot {
      display: inline-block;
      width: 9px;
      height: 9px;
      border-radius: 999px;
      background: #39d98a;
      margin: 0 10px 0 18px;
      box-shadow: 0 0 0 5px rgba(57,217,138,.16);
      vertical-align: middle;
    }

    @keyframes globalTickerMove {
      0% { transform: translateX(0); }
      100% { transform: translateX(-100%); }
    }
  </style>
</head>
<body<?= $bodyClass !== '' ? ' class="' . app_h($bodyClass) . '"' : '' ?>>
  <div class="page-shell<?= $shellClass !== '' ? ' ' . app_h($shellClass) : '' ?>">
    <?php if ($showHeader): ?>
      <header class="site-header<?= $headerClass ?>">
        <div class="brand-block">
          <h1><a class="brand-link" href="<?= app_h(app_url('index.php')) ?>"><?= app_h(app_site_title()) ?></a></h1>
          <p><?= app_h(app_tagline()) ?></p>
        </div>
        <nav class="site-nav" aria-label="Hauptnavigation">
          <?php foreach (app_nav_pages() as $page): ?>
            <a class="<?= $currentNavParent === $page['file'] ? 'is-active' : '' ?>" href="<?= app_h(app_url($page['file'])) ?>">
              <?= app_h($page['label']) ?>
            </a>
          <?php endforeach; ?>
        </nav>
      </header>

      <div class="global-live-ticker" aria-label="Projektstatus Live">
        <div class="global-live-ticker-track" id="global-live-ticker-track">
          <span class="global-live-dot"></span>
          LIVE STATUS • webapp-central.de aktiv • Kalendermodul in Entwicklung • Listenansicht priorisiert • Voice-to-Text aktiv • Hallenberg Showcase online • Live-/Lokal-Sync aktiv • Letzte Projektphase 15.05.2026 • Serverstatus OK
        </div>
      </div>
    <?php endif; ?>

    <main class="content-shell<?= $contentShellClass !== '' ? ' ' . app_h($contentShellClass) : '' ?>">
      <?php if ($showBreadcrumb): ?>
        <section class="page-intro" aria-label="Seitenkontext">
          <nav class="page-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= app_h(app_url('index.php')) ?>"><?= app_h(app_site_title()) ?></a>
            <span>/</span>
            <span><?= app_h($title) ?></span>
          </nav>
        </section>
      <?php endif; ?>

      <?php $content(); ?>
    </main>

    <?php if ($showFooter): ?>
      <footer class="site-footer">
        <div class="footer-brand">
          <span class="footer-mark">C</span>
          <div class="footer-copy">
            <strong><?= app_h(app_copyright_line()) ?></strong>
            <p><?= app_h(app_site_title()) ?> für Medien, Struktur und zentrale Arbeitsabläufe.</p>
          </div>
        </div>
        <nav class="footer-nav" aria-label="Footer">
          <?php foreach (app_footer_links() as $link): ?>
            <a href="<?= app_h(app_url($link['file'])) ?>"><?= app_h($link['label']) ?></a>
          <?php endforeach; ?>
        </nav>
      </footer>
    <?php endif; ?>
  </div>

  <script>
    (function () {
      var ticker = document.getElementById('global-live-ticker-track');

      if (!ticker) {
        return;
      }

      function updateTicker() {
        var now = new Date();
        var time = now.toLocaleTimeString('de-DE', {
          hour: '2-digit',
          minute: '2-digit'
        });

        ticker.innerHTML = '<span class="global-live-dot"></span>'
          + 'LIVE STATUS ' + time
          + ' • webapp-central.de aktiv'
          + ' • Kalendermodul wird erweitert'
          + ' • Chronologische Listenansicht priorisiert'
          + ' • Formularlogik wird umgebaut'
          + ' • Voice-to-Text aktiv'
          + ' • Hallenberg Medienmodule online'
          + ' • Lokaler und Live-Sync aktiv'
          + ' • Serverstatus OK';
      }

      updateTicker();
      window.setInterval(updateTicker, 300000);
    }());
  </script>

  <?php if (app_is_local()): ?>
    <script>
      (function () {
        var currentSignature = null;
        var watchUrl = <?= json_encode(app_url('dev-watch.php')) ?>;

        function scheduleNext() {
          window.setTimeout(check, 1200);
        }

        function check() {
          if (document.visibilityState === 'hidden') {
            scheduleNext();
            return;
          }

          fetch(watchUrl, { cache: 'no-store' })
            .then(function (response) { return response.json(); })
            .then(function (data) {
              if (!data || typeof data.signature !== 'string') {
                scheduleNext();
                return;
              }

              if (currentSignature === null) {
                currentSignature = data.signature;
                scheduleNext();
                return;
              }

              if (currentSignature !== data.signature) {
                window.location.reload();
                return;
              }

              scheduleNext();
            })
            .catch(function () {
              scheduleNext();
            });
        }

        check();
      }());
    </script>
  <?php endif; ?>
</body>
</html>
<?php
    }
}
