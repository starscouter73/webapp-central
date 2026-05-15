<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page(app_site_title(), 'Startseite', static function (): void {
    $modules = app_modules();
    $highlights = app_highlights();
    $statusCheckedAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format('d.m.Y H:i');
    $infrastructureOverviewHtml = app_render_markdown(app_infrastructure_overview_markdown());
    $overviewCategories = [
        [
            'label' => 'Projekte',
            'title' => 'Referenzen zuerst sichtbar machen',
            'text' => 'Showcases und Projektseiten werden als eigene Kategorie gebuendelt und erst danach vertieft.',
            'href' => app_url('workspace.php'),
            'link' => 'Projektbereiche ansehen',
            'details' => [
                'Hallenberg steht als Referenzprojekt bereit.',
                'Weitere Showcases koennen spaeter unter derselben Logik einsortiert werden.',
            ],
        ],
        [
            'label' => 'Arbeitsbereiche',
            'title' => 'Module als klare Kategorien lesen',
            'text' => 'Kalender, Zentrale und weitere Bereiche werden als Uebersichtskarten mit klarer Rolle praesentiert.',
            'href' => app_url('modules.php'),
            'link' => 'Module im Ueberblick',
            'details' => [
                'Jede Kategorie erhaelt erst einen Kurzueberblick und dann einen Weiterlesen-Bereich.',
                'So bleibt die Seite visuell ruhig und trotzdem informationsreich.',
            ],
        ],
        [
            'label' => 'Live-Betrieb',
            'title' => 'Status und Ablauf getrennt erfassen',
            'text' => 'Deployment, Live-Stand und Serverpfad bleiben direkt sichtbar, ohne die Startseite zu ueberladen.',
            'href' => app_url('workspace.php'),
            'link' => 'Zum Live-Kontext',
            'details' => [
                'Der Live-Stand wird zuerst knapp visualisiert.',
                'Technische Details liegen in aufklappbaren Kontextbloecken.',
            ],
        ],
    ];
    ?>
    <section class="card overview-hero">
      <div class="overview-hero-copy">
        <span class="card-label">Ueberblick</span>
        <h3>Elegant starten, dann gezielt weiterlesen</h3>
        <p>Die Startseite ordnet zuerst Projekte, Bereiche und Live-Kontext. Details liegen nachgelagert in ruhigen, aufklappbaren Bloecken.</p>
      </div>
      <div class="overview-chip-grid">
        <div class="overview-chip">
          <strong><?= count($modules) ?></strong>
          <span>Kategorien sichtbar</span>
        </div>
        <div class="overview-chip">
          <strong>Live</strong>
          <span>Stand direkt erreichbar</span>
        </div>
        <div class="overview-chip">
          <strong>Mehr lesen</strong>
          <span>Details nur bei Bedarf</span>
        </div>
      </div>
      <div class="button-row">
        <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
        <a class="btn btn-ghost" href="<?= app_h(app_url('modules.php')) ?>">Kategorien ansehen</a>
      </div>
    </section>

    <section class="grid three-up">
      <?php foreach ($overviewCategories as $category): ?>
        <article class="card category-card">
          <span class="card-label"><?= app_h($category['label']) ?></span>
          <h3><?= app_h($category['title']) ?></h3>
          <p><?= app_h($category['text']) ?></p>
          <a class="btn btn-secondary" href="<?= app_h($category['href']) ?>"><?= app_h($category['link']) ?></a>
          <details class="readmore-card">
            <summary>Weiterlesen</summary>
            <div class="readmore-body">
              <ul class="simple-list">
                <?php foreach ($category['details'] as $detail): ?>
                  <li><?= app_h($detail) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          </details>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="grid split">
      <article class="card">
        <span class="card-label">Kernpunkte</span>
        <h3>Die wichtigsten Leitlinien auf einen Blick</h3>
        <div class="module-list">
          <?php foreach ($highlights as $highlight): ?>
            <article class="module-link">
              <strong><?= app_h($highlight['title']) ?></strong>
              <span><?= app_h($highlight['text']) ?></span>
            </article>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="card prompt-card">
        <span class="card-label">Projektstatus</span>
        <h3>Live-Informationen erst kompakt, dann im Detail</h3>
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
        <details class="readmore-card is-wide-open">
          <summary>Projektkontext weiterlesen</summary>
          <div class="readmore-body project-context-box markdown-overview">
            <?= $infrastructureOverviewHtml ?>
          </div>
        </details>
      </article>
    </section>

    <section class="card">
      <span class="card-label">Kategorien</span>
      <h3>Bereiche zuerst als Uebersichtskarten lesen</h3>
      <div class="module-list">
        <?php foreach ($modules as $module): ?>
          <article class="module-link">
            <strong><?= app_h($module['name']) ?></strong>
            <span><?= app_h($module['summary']) ?></span>
            <div class="button-row">
              <a class="btn btn-secondary" href="<?= app_h($module['href']) ?>">Oeffnen</a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
    <?php
});
