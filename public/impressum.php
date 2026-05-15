<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Impressum', 'Rechtliches', static function (): void {
    $addressLines = app_legal_address_lines();
    $contactChannels = app_legal_contact_channels();
    $missingFields = app_legal_missing_fields();
    $disputeNotice = trim(app_legal_dispute_notice());
    $vatId = trim(app_legal_vat_id());
    ?>
    <section class="grid split legal-grid">
      <article class="card legal-card">
        <span class="card-label">Impressum</span>
        <h3>Pflichtangaben fuer <?= app_h(app_site_name()) ?></h3>
        <p>Diese Seite stellt die Anbieterkennzeichnung fuer das Angebot von <?= app_h(app_site_title()) ?> bereit. Grundlage sind insbesondere die Informationspflichten fuer digitale Dienste und, soweit redaktionelle Inhalte bereitgestellt werden, die Benennung einer inhaltlich verantwortlichen Person.</p>

        <div class="legal-block">
          <h4>Diensteanbieter</h4>
          <p><strong><?= app_h(app_legal_name()) ?></strong></p>
          <?php if ($addressLines !== []): ?>
            <address class="legal-address">
              <?php foreach ($addressLines as $line): ?>
                <span><?= app_h($line) ?></span>
              <?php endforeach; ?>
            </address>
          <?php else: ?>
            <p class="empty-state">Die ladungsfaehige Anschrift ist noch nicht hinterlegt.</p>
          <?php endif; ?>
        </div>

        <div class="legal-block">
          <h4>Schnelle Kontaktaufnahme</h4>
          <?php if ($contactChannels !== []): ?>
            <ul class="simple-list legal-list">
              <?php foreach ($contactChannels as $channel): ?>
                <li><strong><?= app_h($channel['label']) ?>:</strong> <a href="<?= app_h($channel['href']) ?>"><?= app_h($channel['value']) ?></a></li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <p class="empty-state">Es ist noch keine E-Mail-Adresse oder Telefonnummer fuer die unmittelbare Kontaktaufnahme hinterlegt.</p>
          <?php endif; ?>
        </div>

        <div class="legal-block">
          <h4>Inhaltlich verantwortlich</h4>
          <p><strong><?= app_h(app_legal_responsible_person()) ?></strong></p>
          <?php if (trim(app_legal_address_inline()) !== ''): ?>
            <p><?= app_h(app_legal_address_inline()) ?></p>
          <?php else: ?>
            <p class="empty-state">Die Anschrift der verantwortlichen Person ist noch nicht vollstaendig gepflegt.</p>
          <?php endif; ?>
        </div>

        <?php if ($vatId !== ''): ?>
          <div class="legal-block">
            <h4>Umsatzsteuer</h4>
            <p><strong>Umsatzsteuer-Identifikationsnummer:</strong> <?= app_h($vatId) ?></p>
          </div>
        <?php endif; ?>

        <?php if ($disputeNotice !== ''): ?>
          <div class="legal-block">
            <h4>Verbraucherstreitbeilegung</h4>
            <p><?= app_h($disputeNotice) ?></p>
          </div>
        <?php endif; ?>
      </article>

      <article class="card legal-card">
        <span class="card-label">Pruefstatus</span>
        <h3>Vollstaendigkeit vor Live-Nutzung pruefen</h3>
        <p>Die Seite ist als belastbare Struktur vorbereitet. Sie ersetzt aber keine konkrete Einzelfallpruefung der tatsaechlichen Anbieterangaben.</p>

        <?php if ($missingFields !== []): ?>
          <div class="legal-notice">
            <strong>Vor einer produktiven Nutzung fehlen noch Pflichtdaten:</strong>
            <ul class="simple-list legal-list">
              <?php foreach ($missingFields as $field): ?>
                <li><?= app_h($field) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <div class="legal-notice legal-notice-ready">
            <strong>Die wesentlichen Basisfelder fuer Impressum und Kontakt sind in der Konfiguration hinterlegt.</strong>
          </div>
        <?php endif; ?>

        <div class="legal-block">
          <h4>Zur Bewertung fehlen folgende Informationen</h4>
          <ul class="simple-list legal-list">
            <li>Ob das Angebot ausschliesslich privat, freiberuflich oder gewerblich betrieben wird.</li>
            <li>Ob zusaetzliche Registerangaben, berufsrechtliche Angaben oder Aufsichtsbehoerden genannt werden muessen.</li>
            <li>Ob ein Hinweis zur Verbraucherstreitbeilegung im konkreten Geschaeftsmodell erforderlich ist.</li>
          </ul>
        </div>
      </article>
    </section>
    <?php
});
