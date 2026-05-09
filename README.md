# Clean Workspace

Sauberer Neustart fuer eine lokal gestaltbare PHP-Seite mit Docker-, Git- und GitHub-Basis.

## Struktur

```text
public/      Webroot mit Seiten und Assets
src/         Layout, Hilfsfunktionen, Seitendaten
docker/      Apache- und PHP-Konfiguration
.github/     GitHub Actions
```

## Lokaler Start

```powershell
.\dev-up.bat
```

Danach:

- `http://127.0.0.1:8000/`
- `http://127.0.0.1:8000/styleguide.php`
- `http://127.0.0.1:8000/workspace.php`

Stoppen:

```powershell
.\dev-down.bat
```

## Ziel

Dieses Repo ist absichtlich leer und sauber genug, damit die Gestaltung jetzt direkt lokal weiterentwickelt werden kann, ohne Altcode des alten Planers.
