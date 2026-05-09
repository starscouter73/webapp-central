<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Workspace', 'Entwicklung', static function (): void {
    ?>
    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Docker</span>
        <h3>Lokaler Container</h3>
        <p>Start mit <code>dev-up.bat</code>, Stop mit <code>dev-down.bat</code>. DocumentRoot ist bewusst auf <code>public/</code> gesetzt.</p>
      </article>
      <article class="card">
        <span class="card-label">GitHub</span>
        <h3>Repo-Basis</h3>
        <p>Das Repo enthaelt Ignore-Regeln, Editor-Defaults und eine erste Action fuer PHP-Linting.</p>
      </article>
    </section>

    <section class="card">
      <span class="card-label">Ordnerlogik</span>
      <h3>Saubere Projektstruktur</h3>
      <div class="code-block">
        <code>public/</code>
        <code>src/</code>
        <code>docker/</code>
        <code>.github/</code>
      </div>
      <p>Damit kannst du neue Seiten, Komponenten oder eine komplett andere visuelle Richtung direkt auf sauberer Basis weiterentwickeln.</p>
    </section>
    <?php
});
