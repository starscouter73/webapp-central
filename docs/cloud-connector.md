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
- Eigene Admin-Oberflaeche mit Tabs fuer Uebersicht, Verbindungen, Sync-Jobs, Automatisierung, Explorer, Logs und Einstellungen
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

## Verbindungsmodi

- `safe_mode`
  - Default
  - keine externen Provider-Requests
  - nur vorbereitete Konfiguration, Cache, DB und Mockdaten
- `readonly_live`
  - nur fuer explizit freigegebene readonly Metadaten-Tests
  - aktuell nur fuer Google Drive vorbereitet
  - nur kleine Metadatenliste, keine Dateioperationen
- `disabled`
  - blockiert Provider-Kommunikation fuer die Verbindung

Safe-Mode bleibt der globale Standard. `readonly_live` ist eine eng begrenzte Ausnahme fuer einen expliziten Admin-Test.

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
- Google Drive kann zusaetzlich fuer einen expliziten readonly Live-Test vorbereitet werden
- Der Informationsblock `OAuth-/Provider-Informationen` zeigt nur statische Redirect-/Scope-Hinweise und fuehrt keine Redirects aus
- Fuer Google Drive wird eine vorbereitete readonly OAuth-URL angezeigt, aber nicht automatisch aufgerufen
- Copy-Buttons kopieren nur vorbereitete Redirect-URIs oder Scope-Listen in die Zwischenablage
- Aktionen im Backend:
  - `Bearbeiten`
  - `Readonly-Verbindung testen`
  - `Deaktivieren`
  - `Testmodus`
  - `Loeschen`
- Alle Aktionen bleiben durch `manage_options` und Nonces abgesichert
- Secrets werden nur maskiert angezeigt; ein leeres Secret-Feld beim Bearbeiten behaelt den vorhandenen Wert bei
- Access- und Refresh-Token koennen gespeichert werden, werden aber nie im Klartext angezeigt
- Es werden keine Tokens, Secrets oder Passwoerter in Logs geschrieben
- In dieser Stufe werden keine automatischen OAuth-Redirects aktiviert
- Externe Provider-Verbindungen werden nur ueber die explizite Aktion `Readonly-Verbindung testen` aufgebaut
- Dokumentationsstatus und Safe-Mode-Hinweise dienen nur der Vorbereitung einer spaeteren OAuth-Ausbaustufe

## Readonly Live Mode

`readonly_live` ist der erste kontrollierte Live-Pfad fuer echte Provider-Kommunikation.

Erlaubte Operationen:

- Token pruefen
- falls noetig Access-Token ueber Google Refresh-Token erneuern
- maximal 5 Dateimetadaten lesen
- Health-/Statuspruefung der Verbindung

Verbotene Operationen:

- Upload
- Download auf das Dateisystem
- Delete
- Move
- echter Sync
- rekursive Traversals
- Hintergrundsynchronisation
- Worker-/Queue-Anbindung
- Auto-Refresh

Google-Drive-Scope:

- `https://www.googleapis.com/auth/drive.metadata.readonly`

Es werden bewusst keine Schreibscopes vorbereitet.

## Explorer-Tab

- Tab `Explorer` bildet einen ersten Cloud- bzw. Sync-Explorer im WordPress-Backend ab
- Das Layout ist zweispaltig:
  - links Provider-, Verbindungs- und virtuelle Ordnerstruktur
  - rechts Datei-/Sync-Ansicht mit Queue- und Health-Kennzahlen
- Angezeigt werden nur Demo-, Cache- und DB-Daten:
  - vorbereitete Provider-Verbindungen
  - bestehende Sync-Jobs
  - letzte Worker-/Simulationslogs
  - vorhandene Cache-Eintraege aus `cloud_file_cache`
  - feste Mockdateien fuer eine produktnahe Erstansicht
- Virtuelle Ordner:
  - `/`
  - `/Dokumente`
  - `/Uploads`
  - `/Archiv`
  - `/Sync Queue`
- Dateitabelle zeigt:
  - Dateiname
  - Typ
  - Groesse
  - Provider
  - Sync-Richtung
  - Status
  - Letzte Aenderung
  - Letzter Sync
  - Konfliktstatus
- Queue-/Health-Widget zeigt:
  - Pending Jobs
  - letzte Simulation
  - letzte Fehler
  - Queue-Groesse
  - Safe-Mode-Status
  - letzter Worker-Lauf
- Es werden keine echten Cloud-Dateien geladen, keine Dateisystemscans ausgefuehrt und keine Provider angefragt
- Safe-Mode bleibt auch im Explorer zwingend aktiv; die Ansicht bleibt read-only und loest keine Dateioperationen aus
- Fuer Verbindungen im Modus `readonly_live` wird stattdessen ein separater readonly Live-Explorer gerendert:
  - klar als `READONLY LIVE` markiert
  - Warnhinweis `Nur Metadatenzugriff`
  - nur kleine Test-Dateiliste mit:
    - Name
    - Typ
    - Groesse
    - geaendert am
  - keine Queue-, Preview-, Drag-&-Drop- oder Worker-Anbindung

## Netzwerk-/Mesh-Ansicht

- Neuer Tab `Netzwerk`
- Rein visuelle Topologie fuer den Cloud Connector
- Keine aktiven Operationen aus der Map
- Keine Provider-Calls
- Kein OAuth-Flow
- Kein Sync
- Keine Dateioperationen

## UI-Shell-Konzept

- Verbindungen, Explorer, Readonly-Live-Bereich und Netzwerkansicht werden als zusammenhaengende App-Shell behandelt
- Ziel ist eine klarere UI-Hierarchie statt vieler isolierter WordPress-Boxen
- wiederkehrende Shell-Bausteine:
  - Bereichsheader
  - Statuszeilen
  - Karten und Sidepanels
  - Badge-Zonen fuer Modus, Status und Sicherheitsgrenzen
  - Detailpanels fuer readonly und Netzwerkstatus
- Theme-/Website-UI und Cloud-/Portal-UI bleiben weiterhin fachlich getrennt gedacht
- diese Konsolidierung ist rein visuell:
  - keine neuen Features
  - keine neuen Tabs
  - keine Provider-, OAuth-, Worker- oder Queue-Logik
  - keine Dateioperationen
  - keine Aenderung der Safe-Mode-Schutzgrenzen

Gezeigte Nodes:

- Cloud Connector
- Safe-Mode
- Readonly Live
- Google Drive
- Dropbox
- OneDrive
- WebDAV / Nextcloud
- SFTP
- Server Storage
- Explorer
- Sync Preview
- Drag & Drop
- Queue
- Worker
- Logs
- Verbindungen

Statusmodell je Node:

- Titel
- Status-Badge
- Kurzbeschreibung
- erlaubte Operationen
- blockierte Operationen

Interaktion:

- Klick auf eine Node aktualisiert nur clientseitig die Detailkarte
- kein fetch
- kein AJAX
- kein DB-Schreiben

Die Mesh-Ansicht dient nur der Verstaendlichmachung von Schutzschichten, readonly Live-Abgrenzung und blockierten Systempfaden.

## Sync Preview / Dry Run

- Der Explorer enthaelt zusaetzlich einen Bereich `Sync Preview / Dry Run`
- Dieser Bereich simuliert:
  - geplante Aenderungen
  - virtuelle Konflikte
  - virtuelle Warteschlangen-Eintraege
  - Datei-Details mit simulierten Pfaden und Checksummen
- Die Preview-Tabelle zeigt:
  - Datei
  - Quelle
  - Ziel
  - Aktion
  - Status
  - Groesse
  - Zeitstempel
- Simulierte Aktionen:
  - `upload (simuliert)`
  - `download (simuliert)`
  - `update (simuliert)`
  - `konflikt`
  - `ignoriert`
- Verwendete Preview-Badges:
  - `preview`
  - `safe-mode`
  - `konflikt`
  - `queued`
  - `readonly`
- Das Datei-Detailpanel arbeitet rein clientseitig und nutzt nur bereits gerenderte Mock-/Cache-Daten
- Der Explorer enthaelt zusaetzlich reine Safe-Mode-Drag-&-Drop-Interaktionen:
  - Drag von Explorer-Dateizeilen
  - visuelle Drop-Zonen fuer virtuelle Ordner, Preview, Queue und Konfliktbereich
  - clientseitige Inline-Feedbacks statt echter Dateisystemaktionen
  - Aktivitaets-Historie fuer simulierte Queue-, Move- und Konfliktvorgaenge
- Virtuelle Drop-Zonen:
  - `/`
  - `/Dokumente`
  - `/Uploads`
  - `/Archiv`
  - `/Sync Queue`
  - `Sync Preview / Dry Run`
  - `Virtuelle Warteschlange`
  - `Virtuelle Konflikte`
- Nach einem Drop wird nur eine Safe-Mode-Vorschau erzeugt:
  - Datei
  - Quelle
  - Ziel
  - simulierte Aktion
  - Status `Safe-Mode Preview`
  - Hinweis, dass keine echte Dateioperation ausgefuehrt wurde
- Readonly-/Blocked-Faelle bleiben folgenlos:
  - Drag darf sichtbar bleiben
  - der Drop erzeugt nur einen Blockierhinweis
  - keine Queue-, Datei- oder Provider-Aktion wird wirklich ausgefuehrt
- Fallback-Buttons fuer Tastatur und Barrierefreiheit:
  - `Zur Queue simulieren`
  - `Konflikt simulieren`
  - `Move simulieren`
- Es gibt:
  - keinen echten Dry-Run gegen Provider
  - keine Requests an externe APIs
  - keine Dateisystemveraenderung
  - keine Upload-, Download-, Move- oder Delete-Operation
- Virtuelle Konflikte sind nur fachliche Vorschau fuer spaetere Produktpfade und bleiben folgenlos

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
- OAuth-Client fuer den readonly Metadatenzugriff definieren
- Redirect-URI im Cloud-Connector hinterlegen
- nur Scope `drive.metadata.readonly` verwenden
- Zugangsdaten erst nach expliziter Freigabe fuer Live-Calls verwenden

## Readonly Live Sicherheitsgrenzen

- `maxResults=5`
- nur ein flacher Metadaten-List-Request
- keine rekursiven Vollscans
- kein Background-Worker
- kein Auto-Refresh
- defensiver Timeout ueber die Plugin-Einstellung, hart begrenzt
- kein Token im HTML
- kein Token im JavaScript
- kein Token im Log
- keine Secrets im Frontend
- keine Queue-/Worker-Verknuepfung

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
- `readonly_connection_tested` bei explizitem Start des readonly Live-Tests
- `readonly_provider_connected` bei erfolgreichem readonly Metadatenzugriff
- `readonly_provider_failed` bei fehlgeschlagenem readonly Live-Test
- `sync_job_paused` bei manueller Pausierung
- `sync_job_resumed` bei manueller Fortsetzung
- `sync_job_manual_simulation` bei manuell ausgeloster Safe-Mode-Simulation
- `cron_simulation` bei erfolgreicher Worker-Simulation eines faelligen Jobs
- Fuer den Explorer sind keine zusaetzlichen Runtime-Logs erforderlich; die Ansicht konsumiert nur bestehende, nicht-sensitive Daten
- Fuer Preview und Dry Run sind optionale spaetere UI-Logs denkbar, in dieser Stufe aber nicht erforderlich

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
- Explorer-Daten sind nur eine Mischung aus Cache-, Log- und Mockdaten, keine echte Cloud-Dateiliste
- Sync Preview und Konfliktansicht sind reine UI-Simulation ohne Provider- oder Dateizugriff
- Log-Rotation ist nur als einfache Aufbewahrungsbereinigung vorbereitet, nicht als vollwertiges Monitoring

## Naechste Ausbaustufen

- Sichere OAuth-Verbindungs-UI mit restriktiven Redirect- und Scope-Vorgaben
- Provider-spezifische OAuth- und Token-Refresh-Implementierungen
- Hintergrund-Worker fuer echte, idempotente Sync-Laeufe
- Diff-/Konfliktlogik und Dry-Run-Berichte
- Rollen-/Rechtefeingranularitaet ueber `manage_options` hinaus
- Listenfilter, Pagination und erweitertes Monitoring
