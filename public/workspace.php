<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Zentrale', 'Struktur', static function (): void {
    $steps = app_workspace_steps();
    $resumePrompt = app_project_resume_prompt_live();
    $projects = app_workspace_projects();
    $tools = app_workspace_tools();
    ?>
    <section class="card overview-hero">
      <div class="overview-hero-copy">
        <span class="card-label">Zentrale</span>
        <h3>Die Arbeitswelt zuerst sortiert, dann vertieft</h3>
        <p>Projekte, Werkzeuge und Live-Kontext erscheinen hier als klar getrennte Kategorien. Tiefergehende Informationen oeffnen sich erst im zweiten Schritt.</p>
      </div>
      <div class="overview-chip-grid">
        <div class="overview-chip">
          <strong><?= count($projects) ?></strong>
          <span>Projektkategorie</span>
        </div>
        <div class="overview-chip">
          <strong><?= count($tools) ?></strong>
          <span>Arbeitsbereiche</span>
        </div>
        <div class="overview-chip">
          <strong>Direkt</strong>
          <span>Repo zu Live-Stand</span>
        </div>
      </div>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Projekte</span>
        <h3>Aktive Referenzen im Ueberblick</h3>
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
        <h3>Werkzeuge mit klarer Aufgabenrolle</h3>
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
        <span class="card-label">Grundstruktur</span>
        <h3>Technik nur als geordneter Unterbau zeigen</h3>
        <p>Die Kernlogik bleibt kompakt lesbar. Der technische Unterbau wird nicht breit ausgespielt, sondern nur bei Bedarf aufgeklappt.</p>
        <details class="readmore-card">
          <summary>Ordnerlogik weiterlesen</summary>
          <div class="readmore-body">
            <div class="code-block">
              <code>public/</code>
              <code>src/</code>
              <code>docker/</code>
              <code>.github/</code>
            </div>
            <p>So bleibt die Plattform offen fuer weitere Projekte, ohne dass einzelne Showcases das Gesamtsystem zerfasern.</p>
          </div>
        </details>
      </article>
      <article class="card">
        <span class="card-label">Ablauf</span>
        <h3>Praktischer Arbeitsrhythmus</h3>
        <details class="readmore-card" open>
          <summary>Arbeitsweg weiterlesen</summary>
          <div class="readmore-body">
            <ol class="simple-list ordered-list">
              <?php foreach ($steps as $step): ?>
                <li><?= app_h($step) ?></li>
              <?php endforeach; ?>
            </ol>
          </div>
        </details>
      </article>
    </section>

    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Zugriff</span>
        <h3>Wichtige Live-Ziele</h3>
        <details class="readmore-card">
          <summary>Zielpfade weiterlesen</summary>
          <div class="readmore-body">
            <ul class="simple-list">
              <li><code>https://webapp-central.de/</code> als sichtbarer Live-Stand</li>
              <li><code>http://45.133.9.232/</code> als Server-Ziel hinter dem Reverse Proxy</li>
              <li><code>/opt/webapps/webzentrale/</code> als aktueller App-Ordner auf dem Server</li>
            </ul>
          </div>
        </details>
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
      <details class="readmore-card">
        <summary>Projekttext weiterlesen</summary>
        <div class="readmore-body project-context-box">
          <pre id="project-resume-text"><?= app_h($resumePrompt) ?></pre>
        </div>
      </details>
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
