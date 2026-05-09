<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Neue Startseite', 'Basis', static function (): void {
    ?>
    <section class="grid two-up">
      <article class="card feature-card">
        <span class="card-label">Status</span>
        <h3>Altprojekt entfernt</h3>
        <p>Der bisherige Planer wurde aus dem Workspace entfernt. Du arbeitest jetzt auf einer neuen, reduzierten Basis.</p>
      </article>
      <article class="card feature-card accent-card">
        <span class="card-label">Lokal bereit</span>
        <h3>Docker und Live-Reload aktiv</h3>
        <p>Die Seite laeuft ueber Docker auf Port 8000. Aenderungen an PHP und CSS werden lokal automatisch neu geladen.</p>
      </article>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Naechster Schritt</span>
        <h3>Gestaltungsflaeche</h3>
        <p>Nutze diese Startseite als neue Landingpage oder ersetze sie komplett. Die Struktur ist absichtlich klein gehalten.</p>
      </article>
      <article class="card">
        <span class="card-label">Seiten</span>
        <ul class="simple-list">
          <li><a href="<?= app_h(app_url('workspace.php')) ?>">Workspace</a> fuer Projektstatus und lokale Routine</li>
          <li><a href="<?= app_h(app_url('styleguide.php')) ?>">Styleguide</a> fuer Farben, Typografie und Komponenten</li>
        </ul>
      </article>
    </section>
    <?php
});
