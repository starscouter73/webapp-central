# Deployment

## Serverziel

- Host: `webapp-central.de`
- Projektpfad: `/opt/webapps/webzentrale/`
- Reverse Proxy: Nginx Proxy Manager

## Grundlogik

1. Projektdateien nach `/opt/webapps/webzentrale/` übertragen
2. produktive `.env` lokal auf dem Server pflegen
3. `docker compose up -d` im Projektpfad ausführen
4. Live-URL prüfen

## Wichtig

- NPM bleibt die Zertifikats- und HTTPS-Schicht
- WordPress-Core wird nicht aus Git bereitgestellt
- Uploads und Datenbankdaten bleiben außerhalb des Repos

