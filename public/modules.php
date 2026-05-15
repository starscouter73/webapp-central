<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/hallenberg.php';

$_headerLinks = [
    ['href' => '#auftakt', 'label' => 'Auftakt'],
    ['href' => '#fokus', 'label' => 'Fokus'],
    ['href' => '#katalog', 'label' => 'Katalog'],
    ['href' => '#ausbau', 'label' => 'Ausbau'],
];

render_page('Module', 'Bausteine', static function (): void {
    $modules = app_modules();
    $hallenbergStory = app_hallenberg_story();
    ?>
    <section class="platform-shell-header">
      <a class="platform-shell-anchor" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
      <div class="platform-shell-copy">
        <span class="card-label">Module Catalog</span>
        <strong>Jede Kategorie wirkt wie ein eigener Produktbaustein statt wie ein bloßer Menüpunkt</strong>
      </div>
    </section>

    <section class="card overview-hero" id="auftakt">
      <div class="overview-hero-copy">
        <span class="card-label">Kategorien</span>
        <p class="hero-intro-line">Die Module sollen nicht wie Menuepunkte wirken, sondern wie klar lesbare Bausteine mit eigener Funktion.</p>
        <h3>Jeder Bereich bekommt eine erkennbare Rolle und einen ruhigen Auftritt</h3>
        <p>So entsteht keine beliebige Linksammlung, sondern ein geordneter Katalog aus Bereichen, die man intuitiv unterscheiden und gezielt weiterlesen kann.</p>
      </div>
      <div class="overview-hero-aside">
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
        <div class="hero-note-card is-contrast">
          <span class="card-label">Modulbild</span>
          <strong>Module muessen auf den ersten Blick unterscheidbar und merkfaehig sein.</strong>
          <p>Darum liegt der Fokus auf Rolle, Kurzprofil und einem kontrollierten Tiefeneinstieg statt auf gleichfoermigen Karten.</p>
        </div>
      </div>
    </section>

    <section class="grid split showcase-band" id="fokus">
      <article class="card showcase-panel">
        <span class="card-label">Modulcharakter</span>
        <h3>Jeder Bereich soll schon vor dem Klick eine eigene Anmutung mitbringen</h3>
        <p>Deshalb erhalten die Modulbereiche Preview-Flaechen und eine klarere visuelle Unterscheidung. Das macht die Auswahl merkfaehiger und wirkt weniger wie eine uniforme Kartenwand.</p>
      </article>
      <article class="card showcase-visual-panel">
        <figure class="showcase-inline-photo">
          <img src="<?= app_h($hallenbergStory['modules']['media'][0]['src']) ?>" alt="<?= app_h($hallenbergStory['modules']['media'][0]['alt']) ?>" loading="lazy">
        </figure>
      </article>
    </section>

    <section class="grid two-up" id="katalog">
      <?php foreach ($modules as $index => $module): ?>
        <article class="card category-card">
          <div class="category-preview <?= $index === 4 ? 'is-photo' : ($index % 2 === 0 ? 'is-grid' : 'is-signal') ?>">
            <?php if ($index === 4): ?>
              <img src="<?= app_h($hallenbergStory['hero']['media'][1]['src']) ?>" alt="<?= app_h($hallenbergStory['hero']['media'][1]['alt']) ?>" loading="lazy">
            <?php else: ?>
              <span class="preview-bar is-1"></span>
              <span class="preview-bar is-2"></span>
              <span class="preview-bar is-3"></span>
            <?php endif; ?>
          </div>
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

    <section class="card" id="ausbau">
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
}, [
    'header_links' => $_headerLinks,
]);
