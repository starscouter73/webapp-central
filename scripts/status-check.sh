#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/opt/webapps/webzentrale"

cd "$PROJECT_DIR"
docker compose ps
curl -I --max-time 10 http://127.0.0.1:8080 || true

