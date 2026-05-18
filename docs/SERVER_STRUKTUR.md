# Server Struktur

## Zielpfad

```text
/opt/webapps/webzentrale/
├── docker-compose.yml
├── .env
├── .env.example
├── README.md
├── docs/
├── custom/
│   ├── themes/
│   ├── plugins/
│   └── snippets/
└── scripts/
```

## Laufzeitdaten

- WordPress-Core: Docker-Volume
- Uploads: Docker-Volume
- MariaDB-Daten: Docker-Volume

## Nicht anfassen

- `/opt/docker/nginx-proxy-manager/`
- `/opt/docker/portainer/`
- globale NPM-/Portainer-Ressourcen

