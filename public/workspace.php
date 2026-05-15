<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/hallenberg.php';

$_headerLinks = [
    ['href' => '#auftakt', 'label' => 'Auftakt'],
    ['href' => '#fokus', 'label' => 'Fokus'],
    ['href' => '#projekte', 'label' => 'Projekte'],
    ['href' => '#struktur', 'label' => 'Struktur'],
    ['href' => '#zugriff', 'label' => 'Zugriff'],
    ['href' => '#kontext', 'label' => 'Kontext'],
];

render_page('Zentrale', 'Struktur', static function (): void {
    $steps = app_workspace_steps();
    $resumePrompt = app_project_resume_prompt_live();
    $projects = app_workspace_projects();
    $tools = app_workspace_tools();
    $hallenbergStory = app_hallenberg_story();
    ?>
    <section class="platform-shell-header">
      <a class="platform-shell-anchor" href="<?= app_h(app_url('index.php')) ?>">Zur Startseite</a>
      <div class="platform-shell-copy">
        <span class="card-label">Operations Layer</span>
        <strong>Projektfokus, Infrastruktur und Live-Kontext in einer dunklen Arbeitsansicht</strong>
      </div>
    </section>

    <section class="card overview-hero" id="auftakt">
      <div class="overview-hero-copy">
        <span class="card-label">Zentrale</span>
        <p class="hero-intro-line">Die Zentrale verbindet Projektkontext, Werkzeuge und Live-Betrieb in einer lesbaren, gefuehrten Arbeitsansicht.</p>
        <h3>Ein Arbeitsraum mit Schwerpunkt statt einer bloßen Sammlung</h3>
        <p>Hier soll man sofort verstehen, welche Referenzen aktiv sind, welche Bereiche handlungsrelevant bleiben und wo die technische Tiefe beginnt.</p>
      </div>
      <div class="overview-hero-aside">
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
        <div class="hero-note-card is-contrast">
          <span class="card-label">Leselogik</span>
          <strong>Erst Prioritaet und Rolle, dann Infrastruktur und Detailkontext.</strong>
          <p>Die Zentrale soll den Blick fuehren und nicht den Nutzer mit Technikbausteinen empfangen.</p>
        </div>
      </div>
    </section>

    <section class="grid split showcase-band" id="fokus">
      <article class="card showcase-panel">
        <span class="card-label">Projektfokus</span>
        <h3>Die Zentrale braucht ein sichtbares Leitprojekt, nicht nur eine Liste von Bereichen</h3>
        <p>Hallenberg uebernimmt diese Rolle als Premium-Referenz. Damit bekommt die Seite eine erkennbare Hauptachse und wirkt weniger wie ein neutraler Verwaltungsraum.</p>
      </article>
      <article class="card showcase-visual-panel">
        <figure class="showcase-inline-photo">
          <img src="<?= app_h($hallenbergStory['hero']['media'][0]['src']) ?>" alt="<?= app_h($hallenbergStory['hero']['media'][0]['alt']) ?>" loading="lazy">
        </figure>
      </article>
    </section>

    <section class="grid split" id="projekte">
      <article class="card">
        <span class="card-label">Projekte</span>
        <h3>Aktive Referenzen im Ueberblick</h3>
        <div class="workspace-card-list">
          <?php foreach ($projects as $project): ?>
            <a class="workspace-link-card" href="<?= app_h($project['href']) ?>">
              <div class="workspace-card-preview is-photo">
                <img src="<?= app_h($hallenbergStory['drone']['media'][1]['src']) ?>" alt="<?= app_h($hallenbergStory['drone']['media'][1]['alt']) ?>" loading="lazy">
              </div>
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
          <?php foreach ($tools as $index => $tool): ?>
            <a class="workspace-link-card" href="<?= app_h($tool['href']) ?>">
              <div class="workspace-card-preview <?= $index === 0 ? 'is-grid' : 'is-signal' ?>">
                <span class="preview-bar is-1"></span>
                <span class="preview-bar is-2"></span>
                <span class="preview-bar is-3"></span>
              </div>
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

    <section class="grid split" id="struktur">
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

    <section class="grid two-up" id="zugriff">
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

    <section class="card project-context-card" id="kontext">
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
}, [
    'header_links' => $_headerLinks,
]);
