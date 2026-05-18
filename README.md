# webapp-central-wp

`webapp-central.de` wird ab diesem Stand als WordPress-basiertes CMS-Projekt betrieben.

## Ziel

Dieses Repository versioniert nicht den WordPress-Core, keine Uploads und keine Datenbankdaten. Versioniert werden nur die steuerbaren Projektbestandteile:

- `docker-compose.yml`
- `.env.example`
- `docs/`
- `custom/themes/`
- `custom/plugins/`
- `custom/snippets/`
- `scripts/`
- `.gitignore`

## Architektur

- WordPress läuft in Docker mit Apache
- MariaDB läuft in einem separaten Container
- WordPress-Core liegt in einem Docker-Volume
- Uploads liegen in einem separaten Docker-Volume
- eigene Themes, Plugins und MU-Snippets kommen aus dem Repository
- HTTPS und Zertifikate bleiben bei Nginx Proxy Manager

## Schnellstart

1. `.env.example` nach `.env` kopieren
2. sichere Passwörter und Werte in `.env` eintragen
3. `docker compose up -d`
4. `https://webapp-central.de` aufrufen
5. WordPress-Installation im Browser abschließen

## Versionierungsregel

Nicht in Git:

- WordPress-Core
- `wp-admin/`
- `wp-includes/`
- `wp-content/uploads/`
- Datenbankdaten
- `.env`
- Backups, Logs, Cache, temporäre Dateien
- Zugangsdaten, Keys und Secrets

Weiterführende Hinweise liegen unter [docs/WORDPRESS_SETUP.md](docs/WORDPRESS_SETUP.md).

