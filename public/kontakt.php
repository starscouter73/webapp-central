<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Kontakt', 'Anschluss', static function (): void {
    ?>
    <section class="card">
      <span class="card-label">Kontakt</span>
      <h3>Anschluss und Rueckmeldung</h3>
      <p>Kontaktstelle von <?= app_h(app_site_title()) ?>: Mark Dorth.</p>
      <p>Weitere Kontaktangaben, Formulare oder feste Kanaele koennen hier spaeter gezielt fuer den Live-Betrieb ergaenzt werden.</p>
    </section>
    <?php
});
