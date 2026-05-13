<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Zentrale', 'Struktur', static function (): void {
    $steps = app_workspace_steps();
    ?>
    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Arbeitsweg</span>
        <h3>Direkt mit Codex und Live-Server</h3>
        <p>Die App wird hier vor allem direkt im Repository gepflegt und anschließend unmittelbar auf dem Server aktualisiert. <code>public/</code> bleibt dabei der klare Webroot.</p>
      </article>
      <article class="card">
        <span class="card-label">Bereitstellung</span>
        <h3>Pfad Richtung Live-Server</h3>
        <p>Die App ist so vorbereitet, dass sie hinter Nginx Proxy Manager unter <code>webapp-central.de</code> laufen kann, sobald DNS und Domain aktiv sind.</p>
      </article>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Ordnerlogik</span>
        <h3>Webapp Central als Grundstruktur</h3>
        <div class="code-block">
          <code>public/</code>
          <code>src/</code>
          <code>docker/</code>
          <code>.github/</code>
        </div>
        <p>Damit lässt sich die neue Marke ohne Altlasten weiterentwickeln, egal ob daraus eine Startseite, ein Portal oder eine kleine Inhaltszentrale wird.</p>
      </article>
      <article class="card">
        <span class="card-label">Ablauf</span>
        <h3>Praktischer Arbeitsrhythmus</h3>
        <ol class="simple-list ordered-list">
          <?php foreach ($steps as $step): ?>
            <li><?= app_h($step) ?></li>
          <?php endforeach; ?>
        </ol>
      </article>
    </section>

    <section class="card">
      <span class="card-label">Schnellzugriffe</span>
      <h3>Wichtige Einstiege</h3>
      <div class="button-row">
        <a class="btn btn-primary" href="<?= app_h(app_url('index.php')) ?>">Startseite</a>
        <a class="btn btn-secondary" href="<?= app_h(app_url('modules.php')) ?>">Module</a>
      </div>
    </section>

    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Zugriff</span>
        <h3>Wichtige Live-Ziele</h3>
        <ul class="simple-list">
          <li><code>https://webapp-central.de/</code> als sichtbarer Live-Stand</li>
          <li><code>http://45.133.9.232/</code> als Server-Ziel hinter dem Reverse Proxy</li>
          <li><code>/opt/webapps/webzentrale/</code> als aktueller App-Ordner auf dem Server</li>
        </ul>
      </article>
      <article class="card">
        <span class="card-label">Hinweis</span>
        <h3>Domain und DNS liegen außerhalb des Repos</h3>
        <p>Die App selbst bleibt host-neutral. Ob <code>webapp-central.de</code> erreichbar ist, entscheidet die DNS-Konfiguration beim Anbieter und der Reverse Proxy auf dem Server.</p>
      </article>
    </section>
    <?php
});
