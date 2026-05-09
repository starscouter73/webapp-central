<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Styleguide', 'Designsystem', static function (): void {
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
          <a class="btn btn-primary" href="<?= app_h(app_url('index.php')) ?>">Primary</a>
          <a class="btn btn-secondary" href="<?= app_h(app_url('workspace.php')) ?>">Secondary</a>
        </div>
      </article>
      <article class="card">
        <span class="card-label">Typografie</span>
        <h3>Ueberschrift fuer neue Module</h3>
        <p>Nutze diese Seite als visuelle Werkbank, bevor du konkrete Produktseiten aufsetzt.</p>
      </article>
    </section>
    <?php
});
