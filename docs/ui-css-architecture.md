# UI-/CSS-Architektur

## Überblick

Die aktuelle CSS-Lage bleibt bewusst pragmatisch, aber die Zuständigkeiten sind nicht identisch:

- öffentliche WordPress-Seiten laufen im aktiven Theme `custom/themes/webapp-central-starter`
- die WordPress-Startseite wird über `custom/themes/webapp-central-starter/front-page.php` gerendert
- das zugehörige Live-Theme-CSS liegt in `custom/themes/webapp-central-starter/style.css`
- `public/assets/app.css` bleibt für App-/Portal-artige Bereiche relevant, ist aber nicht der Live-Pfad der aktuellen WordPress-Startseite

Diese Änderung führt noch keine technische Trennung ein. Sie bereitet die spätere Aufteilung nur strukturell vor, damit Folgearbeiten gezielter und risikoärmer erfolgen können.

## Aktuelle Rollen der Styles

### Eher Theme-/Website-CSS

Primär im aktiven WordPress-Theme:

- `custom/themes/webapp-central-starter/front-page.php`
- `custom/themes/webapp-central-starter/style.css`

- Basis-Tokens, Typografie und globale Flächen (`:root`, `body`, Buttons, Karten, Shell-Grids)
- öffentliche Seitenrahmen wie `page-shell`, `site-header`, Branding- und Navigationsbausteine
- Content- und Showcase-Komponenten für öffentliche oder redaktionelle Seiten
- projektbezogene Präsentationssektionen wie Hallenberg-/Portfolio-Layouts

### Eher App-/Portal-/Cloud-UI-CSS

Primär in eigenständigen App-/Portal-Pfaden wie `public/assets/app.css`:

- Account-, Workspace- und Portal-Flächen für eingeloggte Bereiche
- Dashboard-nahe Status- und Arbeitsoberflächen
- Explorer-/Dokumentenflächen und zugehörige Drag-and-Drop-Zustände
- karten- und transfernahe Vorschaukomponenten innerhalb arbeitsorientierter Oberflächen

Cloud-Connector-Admin-Oberflächen gehören zusätzlich in eine eigene Plugin-Ebene und nicht in das öffentliche Theme-CSS.

## Zielbild der späteren Trennung

Später soll die CSS-Verantwortung klarer getrennt werden:

- Theme-CSS: Branding, globale Website-Struktur, öffentliche Content-Komponenten
- App-CSS: Portal, Workspace, Explorer, Cloud Connector, Transfer- und Status-UI
- Plugin-CSS: Cloud-Connector-Admin, pluginlokale Status- und Verwaltungskomponenten

Der Grund dafür ist fachlich klar: Cloud-, Explorer- und Transfer-Oberflächen verhalten sich eher wie Produkt- oder Arbeitsoberflächen als wie öffentliches Website-Design. Sie haben eigene Zustände, Interaktionen und Layout-Anforderungen, die langfristig getrennt gepflegt werden sollten.

## Warum jetzt noch kein harter Umbau erfolgt

In diesem Schritt wird bewusst keine Migration vorgenommen:

- keine Includes werden umgebaut
- keine Styles werden verschoben oder gelöscht
- keine Selektoren werden funktional verändert
- keine UI-Logik wird berührt

Der Branch dient nur als kleine Vorarbeit. Abschnittskommentare in `public/assets/app.css` markieren weiterhin die vorhandenen App-/Portal-Verantwortungsbereiche, ohne die aktive WordPress-Theme-Startseite umzubauen.

## Aktueller Live-Pfad der Startseite

Fuer die aktuelle Live-Site ist wichtig:

- die WordPress-Startseite kommt nicht aus `public/index.php`
- `public/assets/app.css` stylt nicht die aktive WordPress-Startseite
- die oeffentliche Startseite wird aus `custom/themes/webapp-central-starter/front-page.php` gerendert
- das aktive Website-CSS liegt in `custom/themes/webapp-central-starter/style.css`

Damit bleibt die kuenftige Trennung nachvollziehbar:

- Theme-CSS fuer oeffentliche WordPress-Seiten
- App-/Portal-CSS fuer eigenstaendige App-/Portalbereiche
- Plugin-CSS fuer Cloud-Connector-Adminoberflaechen
