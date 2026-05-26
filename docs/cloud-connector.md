# Cloud Connector

## Zweck

`cloud_connector` ist die sichere V1-Grundlage fuer eine zentrale Cloud-Verwaltung innerhalb von `webapp-central.de`. Das Modul dient als kontrollierter Admin-Einstiegspunkt fuer spaetere Integrationen mit Google Drive, Dropbox, Microsoft OneDrive, lokalem Speicher sowie perspektivisch Nextcloud, WebDAV und SFTP.

V1 fuehrt bewusst keine produktiven Datei-Loeschungen, Verschiebungen oder Vollsynchronisationen aus.

## Mindestanforderungen

- WordPress ab 6.0
- PHP ab 7.4
- MySQL bzw. MariaDB mit Berechtigung fuer `CREATE TABLE`, `ALTER TABLE`, `INSERT`, `UPDATE`, `DELETE`
- WordPress-Schluessel (`AUTH_KEY`, `SECURE_AUTH_KEY`) fuer die bevorzugte Konfigurationsverschluesselung

Hinweis:
Wenn `OpenSSL` oder `random_bytes()` nicht verfuegbar sind, bleibt das Plugin aktivierbar. Sensible Konfigurationswerte fallen dann auf eine maskierte Fallback-Speicherung zurueck und werden weiterhin nicht im Klartext angezeigt.

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

## Aktivierungsverhalten

- Bei Aktivierung versucht das Plugin ausschliesslich, seine eigenen Tabellen anzulegen oder zu aktualisieren.
- Schlaegt das Schema unvollstaendig fehl, bleibt das Plugin im eingeschraenkten Recovery-Modus nutzbar.
- Im Recovery-Modus werden Admin-Warnungen angezeigt, riskante Schreibaktionen blockiert und leere Listen statt PHP-Warnings ausgegeben.
- Das Plugin fuehrt bei Aktivierung keine Cloud-API-Calls, keine OAuth-Flows und keine Dateioperationen aus.

## Fehlerbehandlung

- Fehlende Tabellen werden erkannt und im Admin als Warnung angezeigt.
- Datenbankzugriffe liefern bei fehlendem Schema sichere Defaults wie leere Arrays oder Standardwerte.
- Logging ist fehlertolerant und schreibt nur, wenn die Log-Tabelle verfuegbar ist.
- Bei fehlender Verschluesselungsumgebung wird eine nicht-fatale Fallback-Speicherung genutzt.
- Deaktiviertes `WP-Cron` verursacht keinen Aktivierungsfehler; fuer spaetere Worker wird dann ein externer Scheduler benoetigt.

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
- V1 speichert vorbereitete Provider-Verbindungen ohne echten OAuth- oder API-Handshake

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

## Provider-Verbindungs-UI

- Tab `Verbindungen` dient in dieser Stufe nur der sicheren Vorbereitung von Provider-Konfigurationen
- Unterstuetzt werden Google Drive, Dropbox, OneDrive, Local Storage sowie vorbereitete Nextcloud-/WebDAV- und SFTP-Konfigurationen
- Die Uebersicht zeigt ID, Provider, Anzeigename, Status, Modus, maskierte Client-Daten, Redirect URI, Token-Hinweis sowie Zeitstempel
- Aktionen im Backend:
  - `Bearbeiten`
  - `Deaktivieren`
  - `Testmodus`
  - `Loeschen`
- Alle Aktionen bleiben durch `manage_options` und Nonces abgesichert
- Secrets werden nur maskiert angezeigt; ein leeres Secret-Feld beim Bearbeiten behaelt den vorhandenen Wert bei
- Es werden keine Tokens, Secrets oder Passwoerter in Logs geschrieben
- In dieser Stufe werden keine OAuth-Redirects aktiviert und keine externen Provider-Verbindungen aufgebaut

## Recovery-/Safe-Mode

- Safe-Mode bleibt der funktionale Standard fuer alle Provider.
- Recovery-Modus bedeutet: Plugin geladen, aber Datenbankschema unvollstaendig oder Umgebung eingeschraenkt.
- In diesem Zustand werden Verwaltungsaktionen defensiv blockiert, waehrend die Admin-Oberflaeche weiter erreichbar bleibt.
- Ziel ist, White Screens und WP-Admin-Blockaden zu vermeiden und stattdessen konkrete Warnungen anzuzeigen.

## Sync-Konzept

V1:

- Jobs anlegen, bearbeiten, pausieren, loeschen
- Simulationslauf ausloesbar
- Sync-Job-UI zeigt Status, Zeitpunkte, Dateizaehler und letzte Fehlermeldung im WordPress-Backend
- Manueller Button `Simulation` setzt nur einen Safe-Mode-Lauf ueber den bestehenden Worker an
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
- V1 registriert bereits einen stuendlichen WP-Cron-Worker fuer faellige Simulationsjobs, sofern `WP-Cron` aktiv ist
- Ausfuehrung nicht im Seitenrendering, sondern in separaten Cron-/Worker-Kontexten
- Harte Timeouts und Logging pro Lauf
- Safe-Mode bleibt Default, bis produktive Synchronisation explizit freigegeben ist
- Es werden nur Jobs mit Status `geplant` und gueltigem `next_run` verarbeitet
- Jeder Lauf bleibt eine reine Simulation; Upload, Download, Move, Delete und externe Cloud-API-Calls bleiben deaktiviert
- Fehler pro Job werden geloggt und blockieren die weitere Abarbeitung nicht
- Admin-Aktionen `Pausieren`, `Fortsetzen` und `Simulation` arbeiten nur mit der vorhandenen Safe-Mode-Logik

### Manuelle Pruefung per WP-CLI

- `wp cron event list | grep cloud_connector`
- `wp cron event run cloud_connector_run_jobs --allow-root`
- `wp plugin status cloud-connector --allow-root`

## Logging

- `connection_created` bei neuer vorbereiteter Verbindung
- `connection_updated` bei bearbeiteter Verbindung
- `connection_disabled` bei manueller Deaktivierung
- `connection_deleted` bei Loeschung
- `connection_test_mode_set` bei Umschalten auf vorbereiteten Testmodus
- `sync_job_paused` bei manueller Pausierung
- `sync_job_resumed` bei manueller Fortsetzung
- `sync_job_manual_simulation` bei manuell ausgeloster Safe-Mode-Simulation
- `cron_simulation` bei erfolgreicher Worker-Simulation eines faelligen Jobs

## Geplante Migrationen

- Versionierte Schema-Weiterentwicklung ueber `cloud_connector_db_version`
- spaetere Datenmigrationen bei neuen Providern, Worker-Metadaten oder erweiterten Job-Statuswerten
- Upgrades sollen wiederholt ausfuehrbar und idempotent bleiben
- neue Provider sollen ueber Registry- und Tabellenkompatibilitaet nachruestbar sein, ohne bestehende Verbindungen zu brechen

## Bekannte Einschraenkungen V1

- Keine lokale Laufzeitpruefung per `php -l` in dieser Codex-Umgebung moeglich, da keine PHP-Runtime verfuegbar war
- Keine Plugin-Aktivierung getestet, da keine lauffaehige WordPress-Instanz im aktuellen Kontext verfuegbar war
- Keine produktiven Upload-, Download-, Move-, Delete- oder Sync-Prozesse
- Kein echter Hintergrund-Sync trotz registriertem Worker; V1 fuehrt ausschliesslich Safe-Mode-Simulationen aus
- Keine OAuth-Implementierung, kein Token-Refresh, keine externen API-Requests
- Log-Rotation ist nur als einfache Aufbewahrungsbereinigung vorbereitet, nicht als vollwertiges Monitoring

## Naechste Ausbaustufen

- Sichere OAuth-Verbindungs-UI mit restriktiven Redirect- und Scope-Vorgaben
- Provider-spezifische OAuth- und Token-Refresh-Implementierungen
- Hintergrund-Worker fuer echte, idempotente Sync-Laeufe
- Diff-/Konfliktlogik und Dry-Run-Berichte
- Rollen-/Rechtefeingranularitaet ueber `manage_options` hinaus
- Listenfilter, Pagination und erweitertes Monitoring
