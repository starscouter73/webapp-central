# WordPress Setup

## Zielbild

WordPress wird ueber Docker betrieben. Core und Uploads liegen in Volumes, versioniert werden nur steuerbare Projektdateien und eigener Custom-Code.

## Setup-Ablauf

1. `.env.example` nach `.env` kopieren
2. Datenbank- und WordPress-Werte setzen
3. `docker compose up -d`
4. WordPress im Browser installieren
5. danach eigenes Theme oder Child-Theme aktivieren

## Custom-Code

- `custom/themes/` fuer eigene Themes
- `custom/plugins/` fuer eigene Plugins
- `custom/snippets/` fuer MU-Plugins / Bootstrap-Snippets

## PHP-Upload-Limits

Die PHP-Upload-Konfiguration wird projektseitig ueber `docker/php/uploads.ini` eingebunden.

- `upload_max_filesize = 1024M`
- `post_max_size = 1024M`
- `memory_limit = 512M`

## Produktionshinweis

Fuer Produktionsbetrieb muss `WORDPRESS_DEBUG=0` gesetzt sein. Der String `false` ist in der offiziellen Docker-WordPress-Logik nicht ausreichend, weil er als nicht-leerer Wert dennoch als wahr ausgewertet werden kann.
