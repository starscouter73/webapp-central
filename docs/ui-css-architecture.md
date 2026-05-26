# UI-/CSS-Architektur

## Überblick

Die aktuelle CSS-Lage ist bewusst noch pragmatisch gehalten. `public/assets/app.css` enthält derzeit sowohl Website-nahe Präsentationsbausteine als auch App-/Portal-Oberflächen für angemeldete Bereiche und Cloud-nahe Status-UI.

Diese Änderung führt noch keine technische Trennung ein. Sie bereitet die spätere Aufteilung nur strukturell vor, damit Folgearbeiten gezielter und risikoärmer erfolgen können.

## Aktuelle Rollen der Styles

### Eher Theme-/Website-CSS

- Basis-Tokens, Typografie und globale Flächen (`:root`, `body`, Buttons, Karten, Shell-Grids)
- öffentliche Seitenrahmen wie `page-shell`, `site-header`, Branding- und Navigationsbausteine
- Content- und Showcase-Komponenten für öffentliche oder redaktionelle Seiten
- projektbezogene Präsentationssektionen wie Hallenberg-/Portfolio-Layouts

### Eher App-/Portal-/Cloud-UI-CSS

- Account-, Workspace- und Portal-Flächen für eingeloggte Bereiche
- Dashboard-nahe Status- und Arbeitsoberflächen
- Cloud-Connector-Statuskarten, Badges und Sticky-Zustandsmodule
- Explorer-/Dokumentenflächen und zugehörige Drag-and-Drop-Zustände
- karten- und transfernahe Vorschaukomponenten innerhalb arbeitsorientierter Oberflächen

## Zielbild der späteren Trennung

Später soll die CSS-Verantwortung klarer getrennt werden:

- Theme-CSS: Branding, globale Website-Struktur, öffentliche Content-Komponenten
- App-CSS: Portal, Workspace, Explorer, Cloud Connector, Transfer- und Status-UI

Der Grund dafür ist fachlich klar: Cloud-, Explorer- und Transfer-Oberflächen verhalten sich eher wie Produkt- oder Arbeitsoberflächen als wie öffentliches Website-Design. Sie haben eigene Zustände, Interaktionen und Layout-Anforderungen, die langfristig getrennt gepflegt werden sollten.

## Warum jetzt noch kein harter Umbau erfolgt

In diesem Schritt wird bewusst keine Migration vorgenommen:

- keine Includes werden umgebaut
- keine Styles werden verschoben oder gelöscht
- keine Selektoren werden funktional verändert
- keine UI-Logik wird berührt

Der Branch dient nur als kleine Vorarbeit. Abschnittskommentare in `public/assets/app.css` markieren die vorhandenen Verantwortungsbereiche, damit ein späterer Umbau mit kleinerem Risiko geplant werden kann.

## Hinweis zum Theme-Pfad in diesem Branch

Der im Arbeitsauftrag genannte Pfad `custom/themes/webapp-central-starter/style.css` ist in diesem Branch aktuell nicht als bearbeitbare Stylesheet-Datei vorhanden. Deshalb wurde dort bewusst keine Kommentarergänzung erzwungen. Die Theme-/Website-Rolle ist stattdessen hier dokumentiert und kann bei Verfügbarkeit der Datei später direkt im Theme-CSS gespiegelt werden.
