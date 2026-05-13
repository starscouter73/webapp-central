# Webapp Central

> Zentrale PHP-Webapp fuer lokalen Docker-Start und spaeteren Rollout unter `webapp-central.de`.

## ✦ Überblick

**Webapp Central** ist die reduzierte Ausgangsbasis fuer einen sauberen Webauftritt:

- lokal entwickelbar mit Docker
- vorbereitet fuer Reverse Proxy und Domainbetrieb
- bewusst frei von alter Heimnetz- und Altprojekt-Logik

## 🧱 Struktur

```text
public/      Webroot mit Seiten und Assets
src/         Layout, Hilfsfunktionen, Seitendaten
docker/      Apache- und PHP-Konfiguration
.github/     GitHub Actions
```

## 🔀 Arbeitsquelle

Dieses Repository auf GitHub ist die fuehrende Projektquelle.

- lokal entwickeln und testen
- Aenderungen per Git committen
- GitHub fuer Historie, Review und Synchronisation verwenden
- externe Beispiel-Repositories sind keine Arbeitsgrundlage fuer dieses Projekt

## 🚀 Lokaler Start

Optional zuerst `.env.example` nach `.env` kopieren und bei Bedarf Port oder Branding anpassen.

Start:

```powershell
.\dev-up.bat
```

Danach erreichbar:

- `http://127.0.0.1:8080/`
- `http://127.0.0.1:8080/styleguide.php`
- `http://127.0.0.1:8080/workspace.php`

Stoppen:

```powershell
.\dev-down.bat
```

## 🌐 Server-Ziel

Die Compose-Konfiguration ist so vorbereitet, dass die App lokal auf `127.0.0.1:8080` laeuft und spaeter hinter Nginx Proxy Manager unter `webapp-central.de` veroeffentlicht werden kann.

Vorgesehener Ablauf:

1. App lokal oder auf dem Server per Docker Compose starten
2. Nginx Proxy Manager auf `http://127.0.0.1:8080` weiterleiten
3. DNS fuer `webapp-central.de` auf den Server zeigen lassen
4. TLS spaeter ueber Let's Encrypt im Proxy aktivieren

## ⚙️ Wichtige Variablen

```env
APP_IMAGE=local/webapp-central-web:latest
APP_CONTAINER_NAME=webapp-central-web
HOST_HTTP_BIND=127.0.0.1
HOST_HTTP_PORT=8080
APP_SITE_NAME=webapp-central.de
APP_SITE_TITLE=Webapp Central
APP_TAGLINE=Zentrale Arbeitsflaeche fuer Webprojekte, Inhalte und Entwicklung.
```

## 🧪 Optionaler LAN-Helfer

Falls du auf deinem Windows-Rechner einen lokalen Alias fuer den Browser willst:

```powershell
powershell -ExecutionPolicy Bypass -File .\setup-lan.ps1
```

Das Skript:

- ergaenzt einen Hosts-Eintrag fuer `webapp-central.test`
- oeffnet die Windows-Firewall fuer eingehendes TCP auf Port `8080`

## 🎯 Zielbild

Dieses Repo ist absichtlich reduziert und sauber genug, damit die Gestaltung direkt weiterentwickelt und spaeter kontrolliert auf den Server uebernommen werden kann.

Kurz gesagt:

- **lokal ruhig entwickeln**
- **servernah vorbereiten**
- **spaeter sauber unter `webapp-central.de` veroeffentlichen**

## 🖥️ Server-Hinweis

Fuer den Server kann der Containername per `.env` bewusst auf den bereits genutzten Reverse-Proxy-Namen gesetzt werden:

```env
APP_CONTAINER_NAME=webzentrale
HOST_HTTP_BIND=127.0.0.1
HOST_HTTP_PORT=8080
APP_SITE_NAME=webapp-central.de
APP_SITE_TITLE=Webapp Central
APP_TAGLINE=Zentrale Arbeitsflaeche fuer Webprojekte, Inhalte und Entwicklung.
```

Damit kann Nginx Proxy Manager weiter auf denselben Upstream zeigen, auch wenn die App intern aus dem echten Repo deployed wird.
