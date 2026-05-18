# WordPress Setup

## Zielbild

WordPress wird über Docker betrieben. Core und Uploads liegen in Volumes, versioniert werden nur steuerbare Projektdateien und eigener Custom-Code.

## Setup-Ablauf

1. `.env.example` nach `.env` kopieren
2. Datenbank- und WordPress-Werte setzen
3. `docker compose up -d`
4. WordPress im Browser installieren
5. danach eigenes Theme oder Child-Theme aktivieren

## Custom-Code

- `custom/themes/` für eigene Themes
- `custom/plugins/` für eigene Plugins
- `custom/snippets/` für MU-Plugins / Bootstrap-Snippets

