<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Impressum', 'Rechtliches', static function (): void {
    ?>
    <section class="card">
      <span class="card-label">Impressum</span>
      <h3>Angaben zum Angebot</h3>
      <p>Verantwortlich fuer <?= app_h(app_site_title()) ?>: Mark Dorth.</p>
      <p>Diese Seite wird als Arbeits-, Medien- und Weboberflaeche fuer den kuenftigen Auftritt unter <?= app_h(app_site_name()) ?> vorbereitet.</p>
    </section>
    <?php
});
