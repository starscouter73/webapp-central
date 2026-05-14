<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Zentrale', 'Struktur', static function (): void {
    $steps = app_workspace_steps();
    $resumePrompt = app_project_resume_prompt_live();
    $projects = app_workspace_projects();
    $tools = app_workspace_tools();
    ?>
    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Zentrale</span>
        <h3>Projektbereich statt Modulsammlung</h3>
        <p>Hier laufen die aktiven Webprojekte zusammen. Einzelne Showcases wie Hallenberg sitzen unter der Zentrale und nicht mehr als gleichrangige Hauptnavigation.</p>
      </article>
      <article class="card">
        <span class="card-label">Arbeitslogik</span>
        <h3>Repo, Live-Stand und Serverpfad bleiben direkt greifbar</h3>
        <p>Die App wird weiter direkt im Repository gepflegt und anschliessend unmittelbar auf den Server synchronisiert. <code>public/</code> bleibt dabei der klare Webroot.</p>
      </article>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Projekte</span>
        <h3>Aktive Referenzen und Showcases</h3>
        <div class="workspace-card-list">
          <?php foreach ($projects as $project): ?>
            <a class="workspace-link-card" href="<?= app_h($project['href']) ?>">
              <div class="workspace-link-meta">
                <span class="status-pill"><?= app_h($project['status']) ?></span>
                <span><?= app_h($project['meta']) ?></span>
              </div>
              <strong><?= app_h($project['name']) ?></strong>
              <p><?= app_h($project['summary']) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="card">
        <span class="card-label">Arbeitsbereiche</span>
        <h3>Werkzeuge und Seiten unterhalb der Zentrale</h3>
        <div class="workspace-card-list">
          <?php foreach ($tools as $tool): ?>
            <a class="workspace-link-card" href="<?= app_h($tool['href']) ?>">
              <div class="workspace-link-meta">
                <span class="status-pill"><?= app_h($tool['status']) ?></span>
                <span><?= app_h($tool['meta']) ?></span>
              </div>
              <strong><?= app_h($tool['name']) ?></strong>
              <p><?= app_h($tool['summary']) ?></p>
            </a>
          <?php endforeach; ?>
        </div>
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
        <p>So bleibt die Plattform offen fuer weitere Projekte, ohne dass einzelne Showcases das Gesamtsystem zerfasern.</p>
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
        <h3>Domain und DNS liegen ausserhalb des Repos</h3>
        <p>Die App selbst bleibt host-neutral. Ob <code>webapp-central.de</code> erreichbar ist, entscheidet die DNS-Konfiguration beim Anbieter und der Reverse Proxy auf dem Server.</p>
      </article>
    </section>

    <section class="card project-context-card">
      <span class="card-label">Projektkontext</span>
      <h3>Kurztext fuer Neustart und Tester</h3>
      <p>Wenn Codex oder der Browser neu startet, kannst du diesen Block direkt kopieren und wieder einfuegen. Der Text wird aus dem aktuellen Projektstand erzeugt und bleibt damit naeher am Live-Zustand.</p>
      <div class="project-context-box">
        <pre id="project-resume-text"><?= app_h($resumePrompt) ?></pre>
      </div>
      <div class="button-row">
        <button class="btn btn-primary" id="copy-project-context" type="button">Projekttext kopieren</button>
      </div>
      <p class="calendar-status" id="copy-project-context-status">Bereit zum Kopieren.</p>
    </section>

    <script>
      (function () {
        var copyButton = document.getElementById('copy-project-context');
        var copyTarget = document.getElementById('project-resume-text');
        var copyStatus = document.getElementById('copy-project-context-status');

        if (!copyButton || !copyTarget || !copyStatus) {
          return;
        }

        copyButton.addEventListener('click', function () {
          var text = copyTarget.textContent || '';

          if (!navigator.clipboard || typeof navigator.clipboard.writeText !== 'function') {
            copyStatus.textContent = 'Clipboard-API im aktuellen Browser nicht verfuegbar.';
            return;
          }

          navigator.clipboard.writeText(text)
            .then(function () {
              copyStatus.textContent = 'Projekttext wurde kopiert.';
            })
            .catch(function () {
              copyStatus.textContent = 'Kopieren ist fehlgeschlagen.';
            });
        });
      }());
    </script>
    <?php
});
