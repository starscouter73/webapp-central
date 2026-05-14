<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page(app_site_title(), 'Startseite', static function (): void {
    $modules = app_modules();
    $highlights = app_highlights();
    $statusCheckedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i');
    ?>
    <section class="grid dashboard-grid">
      <article class="card feature-card">
        <span class="card-label">Ausrichtung</span>
        <h3>Klare Zentrale fuer den neuen Auftritt</h3>
        <p>Die Seite arbeitet als eigenstaendige Webzentrale und ist auf den naechsten Schritt Richtung Server und Domainbetrieb vorbereitet.</p>
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
        <h3><?= app_h(app_site_title()) ?> laeuft direkt hier</h3>
        <p>Struktur, Text und Gestaltung lassen sich direkt hier mit Codex pflegen und anschliessend sofort auf dem Server sichtbar machen.</p>
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
      <article class="card prompt-card">
        <span class="card-label">Projektstatus</span>
        <h3>Status wie in einer klassischen Serverliste</h3>
        <p>Hier erscheint nur der aktuell aktive Live-Status dieser Webapp. Darunter steht weiter der kopierbare Projekt-Prompt fuer den naechsten Chatstart.</p>
        <div class="status-icon-list">
          <article class="status-icon-item">
            <span class="status-icon is-online" aria-hidden="true"></span>
            <div>
              <strong>Online</strong>
              <p>Live-Stand ist erreichbar.</p>
              <p class="status-runtime">Geprueft am <?= app_h($statusCheckedAt) ?> Uhr auf webapp-central.de</p>
            </div>
          </article>
        </div>
        <div class="project-context-box prompt-box">
          <pre id="homepage-project-prompt"># Projekt: webapp-central.de

- Ziel: Webapp Central als saubere Arbeits- und Projektzentrale weiterentwickeln
- Aktueller Fokus: Kalender unter /calendar.php
- Bereits live: Monatsansicht, Wochenansicht, Terminansicht, Bearbeiten, Mapvorschau, Druck, PDF-Export, Suche
- Repo lokal: C:\Users\dorth\Documents\webapp-zentrale
- Live-Server-Pfad: /opt/webapps/webzentrale/
- Deploy aktuell: lokal aendern, commit/push, danach SSH-Sync auf den Server
- Bitte direkt im bestehenden Projekt weiterarbeiten und den Live-Stand mitpruefen

## Naechster sinnvoller Schritt
- UI weiter vereinfachen und produktionsreif machen
- Kalenderlogik spaeter von localStorage auf echte Server-Speicherung umstellen</pre>
        </div>
        <div class="button-row">
          <button class="btn btn-primary" id="copy-homepage-prompt" type="button">Prompt kopieren</button>
          <a class="btn btn-ghost" href="<?= app_h(app_url('workspace.php')) ?>">Projektkontext ansehen</a>
        </div>
        <p class="calendar-status" id="copy-homepage-prompt-status">Bereit zum Kopieren.</p>
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

    <script>
      (function () {
        var copyButton = document.getElementById('copy-homepage-prompt');
        var copyTarget = document.getElementById('homepage-project-prompt');
        var copyStatus = document.getElementById('copy-homepage-prompt-status');

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
              copyStatus.textContent = 'Prompt wurde kopiert.';
            })
            .catch(function () {
              copyStatus.textContent = 'Kopieren ist fehlgeschlagen.';
            });
        });
      }());
    </script>
    <?php
});
