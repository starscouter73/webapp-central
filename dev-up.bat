@echo off
setlocal
docker compose up -d --build
if errorlevel 1 exit /b 1
start http://127.0.0.1:8080/
