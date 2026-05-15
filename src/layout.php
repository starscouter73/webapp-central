<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/auth/auth.php';

if (!function_exists('render_page')) {
    function render_page(string $title, string $eyebrow, callable $content, array $options = []): void
    {
        $currentPage = app_current_page();
        $currentMeta = app_current_page_meta();
        $currentNavParent = (string)($currentMeta['nav_parent'] ?? $currentPage);
        $description = trim((string)($options['description'] ?? $currentMeta['description'] ?? app_tagline()));
        $showHeader = (bool)($options['show_header'] ?? true);
        $showBreadcrumb = (bool)($options['show_breadcrumb'] ?? true);
        $showFooter = (bool)($options['show_footer'] ?? true);
        $bodyClass = trim((string)($options['body_class'] ?? ''));
        $bodyClasses = trim('app-body ' . $bodyClass);
        $shellClass = trim((string)($options['shell_class'] ?? ''));
        $contentShellClass = trim((string)($options['content_shell_class'] ?? ''));
        $headerLinks = $options['header_links'] ?? [];
        $headerClass = '';
        auth_session_start();
        $authUser = auth_current_user();
        $authLoggedIn = $authUser !== null;
        $liveStatusInitial = app_live_status_line();

        if (!headers_sent()) {
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Cross-Origin-Opener-Policy: same-origin-allow-popups');
            header('Permissions-Policy: geolocation=(), camera=(), payment=(), usb=()');
            header("Content-Security-Policy: default-src 'self'; base-uri 'self'; object-src 'none'; frame-ancestors 'self'; img-src 'self' data: https:; frame-src 'self' https://www.google.com https://maps.google.com; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; connect-src 'self'; form-action 'self'");

            if (!app_is_local() && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
                header('Strict-Transport-Security: max-age=86400; includeSubDomains');
            }

            if (app_is_local()) {
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
                header('Expires: 0');
            }
        }
        ?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="<?= app_h($description) ?>">
  <title><?= app_h($title) ?> | <?= app_h(app_site_title()) ?></title>
  <link rel="stylesheet" href="<?= app_h(app_asset_url('assets/app.css')) ?>">
</head>
<body class="<?= app_h($bodyClasses) ?>">
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
          <?php if ($authLoggedIn): ?>
            <a class="<?= $currentPage === 'account.php' ? 'is-active' : '' ?>" href="<?= app_h(app_url('account.php')) ?>">Mein Bereich</a>
            <a href="<?= app_h(app_url('logout.php')) ?>">Logout</a>
          <?php else: ?>
            <a class="<?= $currentPage === 'login.php' ? 'is-active' : '' ?>" href="<?= app_h(app_url('login.php')) ?>">Login</a>
            <a class="<?= $currentPage === 'register.php' ? 'is-active' : '' ?>" href="<?= app_h(app_url('register.php')) ?>">Registrieren</a>
          <?php endif; ?>
        </nav>
        <?php if (is_array($headerLinks) && $headerLinks !== []): ?>
          <div class="site-header-links" aria-label="Seitenabschnitte">
            <?php foreach ($headerLinks as $link): ?>
              <?php if (!is_array($link)) { continue; } ?>
              <a href="<?= app_h((string)($link['href'] ?? '#')) ?>"><?= app_h((string)($link['label'] ?? 'Link')) ?></a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </header>

      <div class="global-live-ticker" aria-label="Projektstatus Live">
        <div class="global-live-ticker-track" id="global-live-ticker-track">
          <span class="global-live-dot"></span>
          <?= app_h($liveStatusInitial) ?>
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
      var isLoggedIn = <?= $authLoggedIn ? 'true' : 'false' ?>;

      if (!ticker) {
        return;
      }

      function updateTicker() {
        var now = new Date();
        var time = now.toLocaleTimeString('de-DE', {
          hour: '2-digit',
          minute: '2-digit'
        });

        var authLine = isLoggedIn ? 'Nutzerbereich: Session aktiv' : 'Nutzerbereich: Login bereit';
        var segments = [
          'LIVE STATUS ' + time,
          'webapp-central.de aktiv',
          'Repository- und Server-Sync aktiv',
          'Chronologische Listenansicht priorisiert',
          authLine,
          'Hallenberg Medienmodule online',
          'Serverstatus OK'
        ];

        ticker.innerHTML = '<span class="global-live-dot"></span>' + segments.join(' • ');
      }

      updateTicker();
      window.setInterval(updateTicker, 180000);
    }());
  </script>

  <script src="<?= app_h(app_asset_url('assets/calendar-priority.js')) ?>"></script>

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
