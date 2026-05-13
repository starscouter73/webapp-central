<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page(app_site_title(), 'Startseite', static function (): void {
    $modules = app_modules();
    $highlights = app_highlights();
    ?>
    <section class="grid dashboard-grid">
      <article class="card feature-card">
        <span class="card-label">Ausrichtung</span>
        <h3>Klare Zentrale für den neuen Auftritt</h3>
        <p>Die Seite arbeitet als eigenständige Webzentrale und ist auf den nächsten Schritt Richtung Server und Domainbetrieb vorbereitet.</p>
        <div class="metric-row">
          <div class="metric-tile">
            <strong><?= count($modules) ?></strong>
            <span>Bereiche angelegt</span>
          </div>
          <div class="metric-tile">
            <strong>Live</strong>
            <span>direkt pflegbar</span>
          </div>
        </div>
      </article>
      <article class="card feature-card accent-card">
        <span class="card-label">Direkt live</span>
        <h3><?= app_h(app_site_title()) ?> läuft direkt hier</h3>
        <p>Struktur, Text und Gestaltung lassen sich direkt hier mit Codex pflegen und anschließend sofort auf dem Server sichtbar machen.</p>
        <div class="button-row">
          <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
          <a class="btn btn-ghost" href="<?= app_h(app_url('modules.php')) ?>">Module ansehen</a>
        </div>
      </article>
    </section>

    <section class="grid three-up">
      <?php foreach ($highlights as $highlight): ?>
        <article class="card">
          <span class="card-label"><?= app_h($highlight['label']) ?></span>
          <h3><?= app_h($highlight['title']) ?></h3>
          <p><?= app_h($highlight['text']) ?></p>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Nächster Schritt</span>
        <h3>Hauptoberfläche für webapp-central.de entwickeln</h3>
        <p>Diese Startseite ist die Basis für die Domain und den künftigen Live-Auftritt. Von hier aus können Navigation, Module und Sprache sauber ausgebaut werden.</p>
      </article>
      <article class="card">
        <span class="card-label">Direkte Wege</span>
        <div class="module-list">
          <?php foreach ($modules as $module): ?>
            <a class="module-link" href="<?= app_h($module['href']) ?>">
              <strong><?= app_h($module['name']) ?></strong>
              <span><?= app_h($module['summary']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </article>
    </section>
    <?php
});
