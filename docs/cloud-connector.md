# Cloud Connector

## Zweck

`cloud_connector` ist die sichere V1-Grundlage fuer eine zentrale Cloud-Verwaltung innerhalb von `webapp-central.de`. Das Modul dient als kontrollierter Admin-Einstiegspunkt fuer spaetere Integrationen mit Google Drive, Dropbox, Microsoft OneDrive, lokalem Speicher sowie perspektivisch Nextcloud, WebDAV und SFTP.

V1 fuehrt bewusst keine produktiven Datei-Loeschungen, Verschiebungen oder Vollsynchronisationen aus.

## Architektur

- Eigenstaendiges WordPress-Plugin unter `custom/plugins/cloud-connector`
- Eigene Admin-Oberflaeche mit Tabs fuer Uebersicht, Anbieter, Verbindungen, Dateien, Sync-Jobs, Automatisierung, Logs und Einstellungen
- Provider-Registry mit austauschbaren Provider-Klassen
- Eigene Datenhaltung ueber WordPress-Tabellen
- Logging und Safe-Mode als zentrale Querschnittsfunktionen

## Provider-Struktur

Interface: `CloudProviderInterface`

Methoden:

- `connect()`
- `disconnect()`
- `listFiles($path)`
- `getFile($id)`
- `uploadFile($localPath, $remotePath)`
- `downloadFile($remoteId, $localPath)`
- `moveFile($remoteId, $targetPath)`
- `deleteFile($remoteId)`
- `getQuota()`
- `getLastError()`

V1-Provider:

- Google Drive
- Dropbox
- Microsoft OneDrive
- Local Storage
- Nextcloud / WebDAV / SFTP als vorbereiteter Platzhalter

Die Provider liefern in V1 nur sichere Demo- bzw. Simulationsantworten. Es finden keine automatischen Live-API-Aufrufe beim Rendern des Backends statt.

## Safe-Mode

- Standardmaessig aktiv
- Mutierende Provider-Methoden liefern in V1 nur blockierte Safe-Mode-Antworten
- Sync-Jobs koennen nur simuliert werden
- Dateioperationen werden protokolliert, aber nicht produktiv ausgefuehrt
- Destruktive Aktionen bleiben auch bei gesetztem Einstellungsflag ohne produktive Implementierung deaktiviert

## Datenmodell

Es werden folgende WordPress-Tabellen angelegt:

- `cloud_providers`
- `cloud_connections`
- `cloud_sync_jobs`
- `cloud_file_cache`
- `cloud_logs`
- `cloud_settings`

### `cloud_providers`

- Anbieter-Metadaten
- Slug, Name, Status, Faehigkeiten

### `cloud_connections`

- Verbindungsdefinitionen pro Anbieter
- Konfigurationspayload verschluesselt, sofern OpenSSL und WordPress-Schluessel verfuegbar sind
- Secrets werden im Backend nur maskiert dargestellt

### `cloud_sync_jobs`

- Richtung: `cloud_to_local`, `local_to_cloud`, `bidirectional`
- Quellpfad, Zielpfad, Status, letzter Lauf, naechster Lauf, Dateianzahl, Fehlertext

### `cloud_file_cache`

- Sichere Dateiuebersicht fuer Adminlisten
- Kein Live-Browsing im normalen Rendering

### `cloud_logs`

- Level, Aktion, Meldung, Kontext, Zeitstempel

### `cloud_settings`

- Safe-Mode
- Timeout
- Log-Aufbewahrung
- Freigabeflag fuer spaetere destruktive Aktionen

## Rechtekonzept

- Admin-Zugriff nur mit `manage_options`
- Formularaktionen nur ueber `admin-post.php`
- CSRF-Schutz via WordPress-Nonces
- Keine Secret-Anzeige im Klartext
- Fehlende Zugangsdaten fuehren zu kontrollierten Fehlermeldungen statt Fatal Errors

## Sync-Konzept

V1:

- Jobs anlegen, bearbeiten, pausieren, loeschen
- Simulationslauf ausloesbar
- Dateizaehler und Status werden nur demonstrativ aktualisiert
- Keine produktive Delta-Logik, keine Datei-Loeschung, kein Move, kein Upload/Download

Spaeter:

- Hintergrund-Worker fuer kontrollierte Batch-Synchronisation
- Retry-, Locking- und Konfliktstrategie
- Provider-spezifische Delta-Token / Change-Feeds

## API-Setup Google Drive

- Google Cloud Projekt anlegen
- Drive API aktivieren
- OAuth-Client oder Service-Account gemaess spaeterem Sync-Modell definieren
- Redirect- und Scopes spaeter restriktiv dokumentieren
- Zugangsdaten erst nach expliziter Freigabe fuer Live-Calls verwenden

## API-Setup Dropbox

- App in der Dropbox Developer Console anlegen
- App-Typ und Scopes eng begrenzen
- Access- und Refresh-Token spaeter nur in freigegebenen Worker-Pfaden verwenden

## API-Setup OneDrive

- Azure App Registration erstellen
- Microsoft Graph Berechtigungen minimal halten
- Tenant-, Client- und Secret-Verwaltung erst mit spaeterem Live-Betrieb verbinden

## Cron-/Automation-Konzept

- Empfohlener spaeterer Hook: `cloud_connector_run_jobs`
- Ausfuehrung nicht im Seitenrendering, sondern in separaten Cron-/Worker-Kontexten
- Harte Timeouts und Logging pro Lauf
- Safe-Mode bleibt Default, bis produktive Synchronisation explizit freigegeben ist

## Naechste Ausbaustufen

- Provider-spezifische OAuth- und Token-Refresh-Implementierungen
- Hintergrund-Worker fuer echte, idempotente Sync-Laeufe
- Diff-/Konfliktlogik und Dry-Run-Berichte
- Rollen-/Rechtefeingranularitaet ueber `manage_options` hinaus
- Listenfilter, Pagination und erweitertes Monitoring
