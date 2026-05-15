# Webapp Central

> Zentrale PHP-Webapp fuer den direkten Arbeitsweg mit Codex und Live-Server unter `webapp-central.de`.

## Ueberblick

**Webapp Central** ist die reduzierte Ausgangsbasis fuer einen klaren Webauftritt:

- direkt im Repository pflegbar
- direkt auf dem Server aktualisierbar
- bewusst frei von alter Heimnetz- und Altprojekt-Logik

## Struktur

```text
public/      Webroot mit Seiten und Assets
src/         Layout, Hilfsfunktionen, Seitendaten
docker/      bestehende Server-/Container-Konfiguration
.github/     GitHub Actions
```

## Benutzerbereich (Basis)

Es gibt jetzt einen ersten geschuetzten Bereich mit:

- `public/register.php` fuer Registrierung
- `public/login.php` fuer Login
- `public/logout.php` fuer Logout
- `public/account.php` als geschuetzte Einstiegsseite

Technische Basis:

- PHP Sessions
- CSRF-Token fuer Login/Registrierung
- Passwort-Hashing mit `password_hash()` und `password_verify()`
- Userspeicher via SQLite (falls verfuegbar), sonst JSON-Fallback unter `data/`

Produktive Nutzerdaten sind per `.gitignore` ausgeschlossen (`data/*.sqlite`, `data/*.db`, `data/users.json`).

## Arbeitsquelle

Dieses Repository auf GitHub ist die fuehrende Projektquelle.

- Aenderungen direkt hier mit Codex umsetzen
- sichtbare Bereiche auf `webapp-central.de` pruefen
- GitHub fuer Historie, Review und Synchronisation verwenden
- externe Beispiel-Repositories sind keine Arbeitsgrundlage fuer dieses Projekt

## Direkter Arbeitsweg

Der praktische Standardweg ist:

1. Aenderungen im Repository machen
2. sichtbare Seiten im Browser pruefen
3. den Stand direkt auf den Server uebernehmen
4. das Ergebnis live auf `webapp-central.de` kontrollieren

Der aktuelle Serverpfad ist:

```text
/opt/webapps/webzentrale/
```

Die sichtbare Website laeuft hier:

```text
https://webapp-central.de/
```

## Live-Deploy direkt aus Codex

Dieses Repository kann so genutzt werden, dass Aenderungen hier in Codex gemacht und anschliessend direkt live deployed werden, ohne dass lokal in VS Code gearbeitet werden muss.

Vorgesehener Weg:

1. Aenderungen im Repo machen
2. nach `main` pushen
3. GitHub Actions verbindet sich per SSH mit dem Server
4. der Runner uebertraegt die Projektdateien direkt nach `DEPLOY_PATH`
5. wenn Docker fuer den Deploy-Benutzer erreichbar ist, folgt `docker compose --env-file .env up -d --build`

Der Workflow liegt hier:

```text
.github/workflows/deploy-live.yml
```

Dafuer werden in GitHub folgende Repository-Secrets benoetigt:

```text
DEPLOY_HOST
DEPLOY_USER
DEPLOY_SSH_KEY
DEPLOY_PORT
DEPLOY_PATH
```

Auf dem Server muss zusaetzlich im Deploy-Verzeichnis eine produktive `.env` liegen. Diese Datei wird nicht aus Git deployed und bleibt bewusst nur lokal auf dem Server.

Der Workflow spiegelt absichtlich den praktischen Live-Weg:

1. **kein serverseitiges `git pull` noetig**
2. **Dateien werden direkt per SSH kopiert**
3. **Compose-Rebuild nur dann hart erforderlich, wenn Docker-/Compose-Dateien geaendert wurden**
4. **reine PHP-/Content-Aenderungen koennen bei Bind-Mount-Setup auch ohne Docker-Neustart live wirksam werden**

Empfohlener Weg auf dem Server:

1. `.env.example` nach `.env` kopieren
2. produktive Werte fuer Domain, Container und Rechtsdaten eintragen
3. danach erst den GitHub-Deploy laufen lassen

Server-Voraussetzungen:

1. Unter `DEPLOY_PATH` existiert das Zielverzeichnis.
2. Im Deploy-Verzeichnis liegt eine gueltige `.env`.
3. `docker` und `docker compose` sind auf dem Server verfuegbar.
4. Fuer Infrastruktur-Aenderungen sollte der Deploy-Benutzer `docker compose up -d --build` ausfuehren duerfen.

## Server-Ziel

Die Website ist so vorbereitet, dass sie hinter Nginx Proxy Manager unter `webapp-central.de` laeuft.

Aktueller Rahmen:

1. Reverse Proxy zeigt auf den laufenden App-Dienst
2. DNS zeigt auf den Server
3. die Inhalte werden aus `/opt/webapps/webzentrale/` gepflegt
4. der sichtbare Stand wird direkt unter `https://webapp-central.de/` geprueft

## Wichtige Variablen

```env
APP_IMAGE=local/webapp-central-web:latest
APP_CONTAINER_NAME=webapp-central-web
HOST_HTTP_BIND=127.0.0.1
HOST_HTTP_PORT=8080
APP_SITE_NAME=webapp-central.de
APP_SITE_TITLE=Webapp Central
APP_TAGLINE=Zentrale Arbeitsflaeche fuer Webprojekte, Inhalte und Entwicklung.
APP_CALENDAR_STORAGE_FILE=
APP_LEGAL_NAME=
APP_LEGAL_ADDRESS_STREET=
APP_LEGAL_ADDRESS_POSTAL=
APP_LEGAL_ADDRESS_CITY=
APP_LEGAL_COUNTRY=Deutschland
APP_LEGAL_EMAIL=
APP_LEGAL_PHONE=
APP_LEGAL_VAT_ID=
APP_LEGAL_RESPONSIBLE_PERSON=
APP_LEGAL_PRIVACY_EMAIL=
APP_LEGAL_DISPUTE_NOTICE=
```

## Zielbild

Dieses Repo ist absichtlich reduziert und sauber genug, damit Inhalte, Sprache und Gestaltung direkt weiterentwickelt und kontrolliert live uebernommen werden koennen.

Kurz gesagt:

- **direkt in Codex arbeiten**
- **sichtbaren Live-Stand pflegen**
- **schrittweise sauber weiterentwickeln**
