<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Module', 'Bausteine', static function (): void {
    $modules = app_modules();
    ?>
    <section class="grid two-up">
      <?php foreach ($modules as $module): ?>
        <article class="card">
          <div class="stack-row">
            <span class="card-label"><?= app_h($module['status']) ?></span>
            <span class="status-pill"><?= app_h($module['name']) ?></span>
          </div>
          <h3><?= app_h($module['name']) ?></h3>
          <p><?= app_h($module['summary']) ?></p>
          <a class="btn btn-secondary" href="<?= app_h($module['href']) ?>">Modul oeffnen</a>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="card">
      <span class="card-label">Ausbauidee</span>
      <h3>Naechste sinnvolle Erweiterungen</h3>
      <ul class="simple-list">
        <li>weitere Inhaltsseiten fuer konkrete Arbeitsablaeufe anlegen</li>
        <li>weitere zentrale Inhaltsbausteine fuer Dokumentation, Kontakt und Statusbereiche ausbauen</li>
        <li>wiederverwendbare Komponenten im Layout zentralisieren</li>
        <li>spaeter Datenquellen oder Formulare gezielt an einzelne Module anbinden</li>
      </ul>
    </section>
    <?php
});
