#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/opt/webapps/webzentrale"

cd "$PROJECT_DIR"
docker compose up -d
docker compose ps

