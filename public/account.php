<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

auth_require_login();
$user = auth_current_user();

render_page('Mein Bereich', 'Benutzerbereich', static function () use ($user): void {
    ?>
    <section class="auth-shell">
      <article class="card auth-card">
        <span class="card-label">Mein Bereich</span>
        <h3>Geschuetzter Nutzerzugang</h3>
        <p>Hier entsteht der persoenliche Arbeitsbereich fuer geschuetzte Projektfunktionen, eigene Ablagen, interne Notizen und spaetere Webapp-Module.</p>
        <div class="auth-message is-success">
          Eingeloggt als <?= app_h((string)($user['email'] ?? '')) ?> (Rolle: <?= app_h((string)($user['role'] ?? 'user')) ?>)
        </div>
        <div class="auth-actions">
          <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
          <a class="btn btn-ghost" href="<?= app_h(app_url('logout.php')) ?>">Logout</a>
        </div>
      </article>
    </section>

    <section class="grid account-grid">
      <article class="card account-card">
        <span class="card-label">Bereich 1</span>
        <h3>Persoenliches Dashboard</h3>
        <p>Platzhalter fuer persoenliche Startansichten, Favoriten und Arbeitskennzahlen.</p>
      </article>
      <article class="card account-card">
        <span class="card-label">Bereich 2</span>
        <h3>Geschuetzte Dokumente</h3>
        <p>Platzhalter fuer private Unterlagen, Projektdateien und interne Dokumentationen.</p>
      </article>
      <article class="card account-card">
        <span class="card-label">Bereich 3</span>
        <h3>Projektmodule</h3>
        <p>Platzhalter fuer modulbezogene Ansichten und nutzerspezifische Konfigurationen.</p>
      </article>
      <article class="card account-card">
        <span class="card-label">Bereich 4</span>
        <h3>Einstellungen</h3>
        <p>Platzhalter fuer Profiloptionen, Ansichtslogik und persoenliche Voreinstellungen.</p>
      </article>
      <article class="card account-card">
        <span class="card-label">Bereich 5</span>
        <h3>Zugang und Sicherheit</h3>
        <p>Platzhalter fuer Passwortwechsel, Session-Verwaltung und spaetere Sicherheitsfunktionen.</p>
      </article>
    </section>
    <?php
});

