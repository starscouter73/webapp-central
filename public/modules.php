<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Module', 'Bausteine', static function (): void {
    $modules = app_modules();
    ?>
    <section class="card overview-hero">
      <div class="overview-hero-copy">
        <span class="card-label">Kategorien</span>
        <h3>Module erst als Rollenbild, dann als Detailansicht</h3>
        <p>Jeder Bereich wird als uebersichtliche Kategorie praesentiert. Weiterfuehrende Hinweise bleiben in aufklappbaren Bereichen gebuendelt.</p>
      </div>
      <div class="overview-chip-grid">
        <div class="overview-chip">
          <strong><?= count($modules) ?></strong>
          <span>Module geordnet</span>
        </div>
        <div class="overview-chip">
          <strong>Klar</strong>
          <span>Kategorien zuerst</span>
        </div>
        <div class="overview-chip">
          <strong>Optional</strong>
          <span>Details per Weiterlesen</span>
        </div>
      </div>
    </section>

    <section class="grid two-up">
      <?php foreach ($modules as $module): ?>
        <article class="card category-card">
          <div class="stack-row">
            <span class="card-label"><?= app_h($module['status']) ?></span>
            <span class="status-pill"><?= app_h($module['name']) ?></span>
          </div>
          <h3><?= app_h($module['name']) ?></h3>
          <p><?= app_h($module['summary']) ?></p>
          <div class="button-row">
            <a class="btn btn-secondary" href="<?= app_h($module['href']) ?>">Modul oeffnen</a>
          </div>
          <details class="readmore-card">
            <summary>Weiterlesen</summary>
            <div class="readmore-body">
              <p>Dieser Bereich ist als eigene Kategorie gedacht und soll Inhalte, Aktionen und Statusinformationen gebuendelt lesbar machen.</p>
            </div>
          </details>
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
