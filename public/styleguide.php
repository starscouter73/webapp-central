<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Design', 'Gestaltung', static function (): void {
    ?>
    <section class="card">
      <span class="card-label">Farben</span>
      <div class="swatch-row">
        <div class="swatch"><span class="swatch-chip swatch-1"></span><strong>Ink</strong><small>#172126</small></div>
        <div class="swatch"><span class="swatch-chip swatch-2"></span><strong>Clay</strong><small>#b85c38</small></div>
        <div class="swatch"><span class="swatch-chip swatch-3"></span><strong>Mist</strong><small>#dce6e0</small></div>
        <div class="swatch"><span class="swatch-chip swatch-4"></span><strong>Paper</strong><small>#f6f1e8</small></div>
      </div>
    </section>

    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Buttons</span>
        <div class="button-row">
          <a class="btn btn-primary" href="<?= app_h(app_url('index.php')) ?>">Startseite</a>
          <a class="btn btn-secondary" href="<?= app_h(app_url('workspace.php')) ?>">Zentrale</a>
          <a class="btn btn-ghost" href="<?= app_h(app_url('modules.php')) ?>">Module</a>
        </div>
      </article>
      <article class="card">
        <span class="card-label">Typografie</span>
        <h3>Webapp Central als ruhige Webmarke</h3>
        <p>Diese Seite dient als visuelle Werkbank fuer die kuenftige Hauptflaeche unter webapp-central.de, bevor Inhalte und Module weiter ausgebaut werden.</p>
      </article>
    </section>

    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Flaechen</span>
        <div class="module-list">
          <div class="module-link">
            <strong>Standardkarte</strong>
            <span>helle Flaeche fuer Inhalte, Listen und strukturierte Texte</span>
          </div>
          <div class="module-link">
            <strong>Akzentflaeche</strong>
            <span>dunklere Buehne fuer Hinweise, CTA-Bereiche oder markante Einstiege</span>
          </div>
        </div>
      </article>
      <article class="card">
        <span class="card-label">Ton</span>
        <h3>Ruhig, klar und belastbar</h3>
        <p>Die Gestaltung setzt auf warme Papierflaechen, dunkle Schrift und erdige Akzente statt auf generische Standard-UI.</p>
      </article>
    </section>
    <?php
});
