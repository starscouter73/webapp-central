<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Kontakt', 'Anschluss', static function (): void {
    $addressLines = app_legal_address_lines();
    $contactChannels = app_legal_contact_channels();
    $privacyEmail = trim(app_legal_privacy_email());
    $missingFields = app_legal_missing_fields();
    ?>
    <section class="grid split legal-grid">
      <article class="card legal-card">
        <span class="card-label">Kontakt</span>
        <h3>Offizielle Kontaktwege fuer <?= app_h(app_site_title()) ?></h3>
        <p>Diese Seite buendelt die vorgesehenen Kontaktkanale fuer allgemeine Anfragen, Datenschutzanliegen und organisatorische Rueckmeldungen zum Angebot unter <?= app_h(app_site_name()) ?>.</p>

        <div class="legal-block">
          <h4>Kontaktstelle</h4>
          <p><strong><?= app_h(app_legal_name()) ?></strong></p>
          <?php if ($addressLines !== []): ?>
            <address class="legal-address">
              <?php foreach ($addressLines as $line): ?>
                <span><?= app_h($line) ?></span>
              <?php endforeach; ?>
            </address>
          <?php else: ?>
            <p class="empty-state">Eine postalische Kontaktanschrift ist noch nicht vollstaendig hinterlegt.</p>
          <?php endif; ?>
        </div>

        <div class="legal-block">
          <h4>Direkte Erreichbarkeit</h4>
          <?php if ($contactChannels !== []): ?>
            <ul class="simple-list legal-list">
              <?php foreach ($contactChannels as $channel): ?>
                <li><strong><?= app_h($channel['label']) ?>:</strong> <a href="<?= app_h($channel['href']) ?>"><?= app_h($channel['value']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Es ist noch kein direkter Kontaktkanal fuer eingehende Anfragen konfiguriert.</p>
          <?php endif; ?>
        </div>

        <div class="legal-block">
          <h4>Datenschutzanfragen</h4>
          <?php if ($privacyEmail !== ''): ?>
            <p>Fuer Anliegen zu Auskunft, Berichtigung, Loeschung oder Widerspruch kann folgende Adresse genutzt werden: <a href="mailto:<?= app_h($privacyEmail) ?>"><?= app_h($privacyEmail) ?></a>.</p>
          <?php else: ?>
            <p class="empty-state">Es ist noch keine eigene Kontaktadresse fuer Datenschutzanfragen hinterlegt. Aktuell wird die allgemeine Kontaktadresse verwendet, sobald diese gepflegt ist.</p>
          <?php endif; ?>
        </div>
      </article>

      <article class="card legal-card">
        <span class="card-label">Hinweise</span>
        <h3>Hinweise zur Kontaktaufnahme</h3>
        <div class="legal-block">
          <h4>Zweck der Kontaktaufnahme</h4>
          <ul class="simple-list legal-list">
            <li>Allgemeine Rueckfragen zum Angebot und zu veroeffentlichten Inhalten.</li>
            <li>Technische Hinweise, Fehlermeldungen und Meldungen zu Funktionsproblemen.</li>
            <li>Datenschutzbezogene Anliegen und Rechteausuebung.</li>
          </ul>
        </div>

        <div class="legal-block">
          <h4>Bearbeitungshinweis</h4>
          <p>Uebermittelte Angaben werden ausschliesslich zur Bearbeitung der jeweiligen Anfrage verwendet. Weitere Hinweise zur Datenverarbeitung stehen in der Datenschutzerklaerung.</p>
        </div>

        <?php if ($missingFields !== []): ?>
          <div class="legal-notice">
            <strong>Vor der Live-Nutzung sollten diese Angaben noch ergaenzt werden:</strong>
            <ul class="simple-list legal-list">
              <?php foreach ($missingFields as $field): ?>
                <li><?= app_h($field) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <div class="legal-notice legal-notice-ready">
            <strong>Die Kontaktseite ist mit den aktuell hinterlegten Stammangaben befuellt.</strong>
          </div>
        <?php endif; ?>
      </article>
    </section>
    <?php
});
