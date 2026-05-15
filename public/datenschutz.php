<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

render_page('Datenschutz', 'Rechtliches', static function (): void {
    $addressInline = trim(app_legal_address_inline());
    $privacyEmail = trim(app_legal_privacy_email());
    $generalEmail = trim(app_legal_email());
    $missingFields = app_legal_missing_fields();
    ?>
    <section class="grid split legal-grid">
      <article class="card legal-card">
        <span class="card-label">Datenschutz</span>
        <h3>Datenschutzhinweise fuer <?= app_h(app_site_name()) ?></h3>
        <p>Diese Hinweise informieren ueber Art, Umfang und Zweck der Verarbeitung personenbezogener Daten beim Besuch dieser Website und bei der Nutzung einzelner Funktionen innerhalb von <?= app_h(app_site_title()) ?>.</p>

        <div class="legal-block">
          <h4>1. Verantwortliche Stelle</h4>
          <p><strong><?= app_h(app_legal_name()) ?></strong></p>
          <?php if ($addressInline !== ''): ?>
            <p><?= app_h($addressInline) ?></p>
          <?php endif; ?>
          <?php if ($generalEmail !== ''): ?>
            <p>E-Mail: <a href="mailto:<?= app_h($generalEmail) ?>"><?= app_h($generalEmail) ?></a></p>
          <?php else: ?>
            <p class="empty-state">Eine Kontaktadresse des Verantwortlichen ist noch nicht hinterlegt.</p>
          <?php endif; ?>
        </div>

        <div class="legal-block">
          <h4>2. Zugriffsdaten und Bereitstellung der Website</h4>
          <p>Beim Aufruf der Website koennen serverseitig technisch erforderliche Informationen verarbeitet werden, insbesondere IP-Adresse, Datum und Uhrzeit des Zugriffs, aufgerufene Ressource, Referrer, Browserkennung und Betriebssysteminformationen.</p>
          <p>Die Verarbeitung erfolgt zur sicheren Bereitstellung des Angebots, zur Stabilitaet des Serverbetriebs und zur Fehleranalyse.</p>
          <p><strong>Rechtsgrundlage:</strong> Art. 6 Abs. 1 lit. f DSGVO.</p>
        </div>

        <div class="legal-block">
          <h4>3. Kontaktaufnahme</h4>
          <p>Wenn Anfragen per E-Mail oder ueber sonstige direkte Kontaktwege eingehen, werden die uebermittelten Angaben zur Bearbeitung und Dokumentation der Anfrage verarbeitet.</p>
          <p><strong>Verarbeitete Daten:</strong> Absenderdaten, Kommunikationsinhalt, Zeitpunkte und gegebenenfalls freiwillig uebermittelte Zusatzangaben.</p>
          <p><strong>Rechtsgrundlage:</strong> Art. 6 Abs. 1 lit. b DSGVO bei anbahnungs- oder vertragsbezogenen Anfragen, im Uebrigen Art. 6 Abs. 1 lit. f DSGVO.</p>
        </div>

        <div class="legal-block">
          <h4>4. Kalender- und Terminfunktion</h4>
          <p>Die Kalenderseiten speichern Termine zunaechst lokal im Browser des verwendeten Geraets. Wird die Terminfunktion aktiv genutzt, koennen Terminangaben zusaetzlich an die serverseitige Kalender-Schnittstelle uebertragen werden.</p>
          <p><strong>Betroffene Daten:</strong> Titel, Datum, Uhrzeit, Adresse, Notizen und technisch erzeugte Aktualisierungszeitpunkte.</p>
          <p><strong>Zweck:</strong> Bereitstellung, Synchronisation und Bearbeitung der Terminverwaltung.</p>
          <p><strong>Rechtsgrundlage:</strong> Art. 6 Abs. 1 lit. f DSGVO. Werden ueber die Terminnotizen besondere Kategorien personenbezogener Daten eingegeben, sollte dies nur erfolgen, wenn dies zwingend erforderlich ist.</p>
        </div>

        <div class="legal-block">
          <h4>5. Karten, Spracheingabe und Browserfunktionen</h4>
          <p>Bei Eingabe einer Adresse kann eine Kartenansicht ueber Dienste von Google geladen werden. Die Sprachfunktion auf den Kalenderseiten verwendet die im jeweiligen Browser oder Betriebssystem bereitgestellten Schnittstellen fuer Spracherkennung und Sprachwiedergabe.</p>
          <p>Diese Funktionen werden erst durch aktive Nutzung gestartet. Dabei koennen technische Daten an die jeweiligen Anbieter der Browser- oder Kartendienste uebermittelt werden.</p>
          <p><strong>Rechtsgrundlage:</strong> Art. 6 Abs. 1 lit. a DSGVO beziehungsweise Art. 6 Abs. 1 lit. f DSGVO, soweit die Funktion technisch notwendig und vom Nutzer bewusst ausgeloest wird.</p>
        </div>
      </article>

      <article class="card legal-card">
        <span class="card-label">Rechte</span>
        <h3>Betroffenenrechte und Speicherlogik</h3>

        <div class="legal-block">
          <h4>6. Speicherdauer</h4>
          <p>Personenbezogene Daten werden nur so lange gespeichert, wie dies fuer den jeweiligen Zweck erforderlich ist oder gesetzliche Aufbewahrungspflichten bestehen. Lokal im Browser gespeicherte Termine verbleiben grundsaetzlich dort, bis sie vom Nutzer entfernt oder ueberschrieben werden.</p>
        </div>

        <div class="legal-block">
          <h4>7. Cookies und lokale Speicherung</h4>
          <p>Nach dem aktuellen Funktionsstand verwendet die Website keine erkennbaren Tracking-Cookies. Fuer die Kalenderfunktion wird jedoch lokaler Browserspeicher genutzt, damit Termine auf dem verwendeten Geraet verfuegbar bleiben.</p>
        </div>

        <div class="legal-block">
          <h4>8. Rechte betroffener Personen</h4>
          <ul class="simple-list legal-list">
            <li>Auskunft ueber verarbeitete personenbezogene Daten gemaess Art. 15 DSGVO</li>
            <li>Berichtigung unrichtiger Daten gemaess Art. 16 DSGVO</li>
            <li>Loeschung gemaess Art. 17 DSGVO</li>
            <li>Einschraenkung der Verarbeitung gemaess Art. 18 DSGVO</li>
            <li>Datenuebertragbarkeit gemaess Art. 20 DSGVO</li>
            <li>Widerspruch gegen Verarbeitungen auf Grundlage berechtigter Interessen gemaess Art. 21 DSGVO</li>
            <li>Beschwerde bei einer Datenschutzaufsichtsbehoerde gemaess Art. 77 DSGVO</li>
          </ul>
        </div>

        <div class="legal-block">
          <h4>9. Kontakt fuer Datenschutzanfragen</h4>
          <?php if ($privacyEmail !== ''): ?>
            <p>Datenschutzanfragen koennen an <a href="mailto:<?= app_h($privacyEmail) ?>"><?= app_h($privacyEmail) ?></a> gerichtet werden.</p>
          <?php else: ?>
            <p class="empty-state">Eine eigene Kontaktadresse fuer Datenschutzanfragen ist noch nicht hinterlegt.</p>
          <?php endif; ?>
        </div>

        <?php if ($missingFields !== []): ?>
          <div class="legal-notice">
            <strong>Vor einer endgueltigen juristischen Freigabe fehlen noch:</strong>
            <ul class="simple-list legal-list">
              <?php foreach ($missingFields as $field): ?>
                <li><?= app_h($field) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php else: ?>
          <div class="legal-notice legal-notice-ready">
            <strong>Die Datenschutzerklaerung ist technisch auf den aktuellen Funktionsstand von Website und Kalender abgestimmt.</strong>
          </div>
        <?php endif; ?>
      </article>
    </section>
    <?php
});
