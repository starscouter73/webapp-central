# Webapp Central

> Zentrale PHP-Webapp für den direkten Arbeitsweg mit Codex und Live-Server unter `webapp-central.de`.

## Überblick

**Webapp Central** ist die reduzierte Ausgangsbasis für einen klaren Webauftritt:

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

## Arbeitsquelle

Dieses Repository auf GitHub ist die führende Projektquelle.

- Änderungen direkt hier mit Codex umsetzen
- sichtbare Bereiche auf `webapp-central.de` prüfen
- GitHub für Historie, Review und Synchronisation verwenden
- externe Beispiel-Repositories sind keine Arbeitsgrundlage für dieses Projekt

## Direkter Arbeitsweg

Der praktische Standardweg ist:

1. Änderungen im Repository machen
2. sichtbare Seiten im Browser prüfen
3. den Stand direkt auf den Server übernehmen
4. das Ergebnis live auf `webapp-central.de` kontrollieren

Der aktuelle Serverpfad ist:

```text
/opt/webapps/webzentrale/
```

Die sichtbare Website läuft hier:

```text
https://webapp-central.de/
```

## Live-Deploy direkt aus Codex

Dieses Repository kann so genutzt werden, dass Änderungen hier in Codex gemacht und anschließend direkt live deployed werden, ohne dass lokal in VS Code gearbeitet werden muss.

Vorgesehener Weg:

1. Änderungen im Repo machen
2. nach `main` pushen
3. GitHub Actions verbindet sich per SSH mit dem Server
4. der Server aktualisiert das Repo und führt `docker compose up -d --build` aus

Der Workflow liegt hier:

```text
.github/workflows/deploy-live.yml
```

Dafür werden in GitHub folgende Repository-Secrets benötigt:

```text
DEPLOY_HOST
DEPLOY_USER
DEPLOY_SSH_KEY
DEPLOY_PORT
DEPLOY_PATH
```

Server-Voraussetzungen:

1. Das Repository ist auf dem Server bereits unter `DEPLOY_PATH` geklont.
2. `docker` und `docker compose` sind auf dem Server verfügbar.
3. Der Deploy-Benutzer darf in diesem Ordner `git pull` und `docker compose up -d --build` ausführen.

## Server-Ziel

Die Website ist so vorbereitet, dass sie hinter Nginx Proxy Manager unter `webapp-central.de` läuft.

Aktueller Rahmen:

1. Reverse Proxy zeigt auf den laufenden App-Dienst
2. DNS zeigt auf den Server
3. die Inhalte werden aus `/opt/webapps/webzentrale/` gepflegt
4. der sichtbare Stand wird direkt unter `https://webapp-central.de/` geprüft

## Wichtige Variablen

```env
APP_IMAGE=local/webapp-central-web:latest
APP_CONTAINER_NAME=webapp-central-web
HOST_HTTP_BIND=127.0.0.1
HOST_HTTP_PORT=8080
APP_SITE_NAME=webapp-central.de
APP_SITE_TITLE=Webapp Central
APP_TAGLINE=Zentrale Arbeitsfläche für Webprojekte, Inhalte und Entwicklung.
```

## Zielbild

Dieses Repo ist absichtlich reduziert und sauber genug, damit Inhalte, Sprache und Gestaltung direkt weiterentwickelt und kontrolliert live übernommen werden können.

Kurz gesagt:

- **direkt in Codex arbeiten**
- **sichtbaren Live-Stand pflegen**
- **schrittweise sauber weiterentwickeln**
