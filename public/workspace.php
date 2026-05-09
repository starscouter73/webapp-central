<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Zentrale', 'Struktur', static function (): void {
    ?>
    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Docker</span>
        <h3>Lokaler Container</h3>
        <p>Start mit <code>dev-up.bat</code>, Stop mit <code>dev-down.bat</code>. Die neue Struktur arbeitet klar mit <code>public/</code> als Webroot.</p>
      </article>
      <article class="card">
        <span class="card-label">GitHub</span>
        <h3>Saubere Repo-Basis</h3>
        <p>Das Repo ist mit GitHub verbunden und enthaelt bereits Ignore-Regeln, Editor-Defaults und eine erste Action fuer PHP-Linting.</p>
      </article>
    </section>

    <section class="card">
      <span class="card-label">Ordnerlogik</span>
      <h3>Webapp Zentrale als Grundstruktur</h3>
      <div class="code-block">
        <code>public/</code>
        <code>src/</code>
        <code>docker/</code>
        <code>.github/</code>
      </div>
      <p>Damit laesst sich die neue Marke jetzt ohne Altlasten weiterentwickeln, egal ob daraus eine Startseite, ein Portal oder eine kleine Inhaltszentrale wird.</p>
    </section>
    <?php
});
