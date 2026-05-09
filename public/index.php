<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Webapp Zentrale', 'Startseite', static function (): void {
    ?>
    <section class="grid two-up">
      <article class="card feature-card">
        <span class="card-label">Ausrichtung</span>
        <h3>Nicht mehr Planer, sondern Zentrale</h3>
        <p>Die Seite arbeitet nicht mehr unter der alten Matthias-Planer-Logik, sondern als neue, offen gestaltbare Zentrale mit neutraler Marke.</p>
      </article>
      <article class="card feature-card accent-card">
        <span class="card-label">Lokal bereit</span>
        <h3>Webapp Zentrale laeuft direkt hier</h3>
        <p>Die Seite wird lokal ueber Docker ausgeliefert. Aenderungen an Struktur, Text und Gestaltung lassen sich direkt weiterentwickeln.</p>
      </article>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Naechster Schritt</span>
        <h3>Neue Hauptoberflaeche entwickeln</h3>
        <p>Diese Startseite ist jetzt die Basis fuer die neue Marke. Von hier aus koennen wir Navigation, Module und Sprache sauber neu aufbauen.</p>
      </article>
      <article class="card">
        <span class="card-label">Bereiche</span>
        <ul class="simple-list">
          <li><a href="<?= app_h(app_url('workspace.php')) ?>">Zentrale</a> fuer Struktur, Technik und lokale Routine</li>
          <li><a href="<?= app_h(app_url('styleguide.php')) ?>">Design</a> fuer Farben, Typografie und Komponenten</li>
        </ul>
      </article>
    </section>
    <?php
});
