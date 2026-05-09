<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (!function_exists('render_page')) {
    function render_page(string $title, string $eyebrow, callable $content): void
    {
        $currentPage = app_current_page();
        ?>
<!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= app_h($title) ?> | Webapp Zentrale</title>
  <link rel="stylesheet" href="<?= app_h(app_url('assets/app.css')) ?>">
</head>
<body>
  <div class="page-shell">
    <header class="site-header">
      <div class="brand-block">
        <span class="brand-kicker">Zentrale Arbeitsflaeche</span>
        <h1>Webapp Zentrale</h1>
        <p>Lokale Webbasis fuer neue Seiten, klare Strukturen und einen ruhigen redaktionellen Auftritt.</p>
      </div>
      <nav class="site-nav" aria-label="Hauptnavigation">
        <?php foreach (app_pages() as $page): ?>
          <a class="<?= $currentPage === $page['file'] ? 'is-active' : '' ?>" href="<?= app_h(app_url($page['file'])) ?>">
            <?= app_h($page['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </header>

    <main class="content-shell">
      <section class="hero-panel">
        <span class="eyebrow"><?= app_h($eyebrow) ?></span>
        <h2><?= app_h($title) ?></h2>
      </section>

      <?php $content(); ?>
    </main>
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
