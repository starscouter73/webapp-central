<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (!function_exists('render_page')) {
    function render_page(string $title, string $eyebrow, callable $content): void
    {
        $currentPage = app_current_page();
        $currentMeta = app_current_page_meta();
        $currentNavParent = (string)($currentMeta['nav_parent'] ?? $currentPage);

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
</head>
<body>
  <div class="page-shell">
    <header class="site-header">
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

    <main class="content-shell">
      <section class="page-intro" aria-label="Seitenkontext">
        <nav class="page-breadcrumb" aria-label="Breadcrumb">
          <a href="<?= app_h(app_url('index.php')) ?>"><?= app_h(app_site_title()) ?></a>
          <span>/</span>
          <span><?= app_h($title) ?></span>
        </nav>
      </section>

      <?php $content(); ?>
    </main>

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
  </div>
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
