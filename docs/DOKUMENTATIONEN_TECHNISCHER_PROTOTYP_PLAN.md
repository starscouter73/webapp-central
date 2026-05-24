# Technischer Prototypplan fuer Dokumentationen

Kurzbeschreibung: Dieses Dokument beschreibt die kontrollierte Minimalstrategie fuer einen technischen WordPress-Prototyp des Dokumentationsbereichs. Ziel ist eine kleine, saubere Registrierungsbasis fuer CPT, Taxonomien und Metafelder, ohne Frontend-Ausbau oder produktive Inhaltsveraenderung.

## Zweck

Dieses Dokument legt fest, wie die Architekturartefakte fuer `/dokumentationen/` in eine kleine technische Grundlage uebersetzt werden koennen. Es ist bewusst kein Implementierungsumfang fuer Templates, Importlogik oder Redaktionsoberflaechen, sondern ein risikoarmer Startpunkt.

## Repository-Zustand

Zum Zeitpunkt dieser Planung:

- `git status -sb` zeigt einen sauberen Branch `main` mit mehreren unversionierten Dokumentationsartefakten.
- Unter `custom/plugins/` existiert bereits ein generischer Platzhalter `webapp-central-helper`.
- Unter `custom/snippets/` existiert ein MU-Bootstrap-Platzhalter.
- Unter `custom/themes/` existiert das Theme `webapp-central-starter`.

## Relevante Strukturbeobachtungen

### Bestehendes Plugin

`custom/plugins/webapp-central-helper/webapp-central-helper.php` ist derzeit nur ein leerer Platzhalter. Technisch koennte der Prototyp dort eingebaut werden, fachlich wuerde die Dokumentationslogik aber mit sonstigen Projekterweiterungen vermischt.

### MU-Snippet

`custom/snippets/webapp-central-bootstrap.php` eignet sich fuer globale Bootstraps oder sehr kleine Querschnittslogik. Fuer den Dokumentationsbereich ist es als primaerer Ort weniger geeignet, weil die Fachlogik dadurch zu unspezifisch verortet waere.

### Theme

Das Theme `custom/themes/webapp-central-starter/` enthaelt bereits Layout-, Branding- und Portalstruktur. Der Dokumentationsprototyp sollte nicht dort eingebaut werden, weil sonst Systemlogik mit Darstellung gekoppelt wird.

## Empfohlene Umsetzungsform

Empfehlung:
ein eigenes kleines Plugin unter `custom/plugins/webapp-central-dokumentationen/`

Begruendung:

- klare fachliche Trennung von Theme und Layout
- bessere Wartbarkeit als ein MU-Snippet fuer dieses Teilmodul
- sauberere Ownership als im generischen Helper-Plugin
- spaeter leichter erweiterbar fuer Adminlogik, Import und Hub-Abfragen

Nicht empfohlen:

- Theme-Funktionen fuer CPT- und Taxonomieregistrierung
- Vermischung mit dem generischen Helper-Plugin ohne klare Modulgrenze
- MU-Bootstrap als Hauptort fuer eine wachsende Dokumentationslogik

## Minimaler technischer Zielumfang

Der erste Prototyp soll nur Folgendes leisten:

- Registrierung des CPT `dokumentation`
- Registrierung der Taxonomien:
  - `doku_bereich`
  - `doku_format`
  - `doku_status`
  - `doku_serie`
- Registrierung grundlegender Post-Meta-Felder gemaess Feldmodell
- einfache Admin-Labels
- keine Templates
- keine Frontend-Ausgabeanpassung
- keine automatische Inhaltserstellung
- keine Hub-Seiten-Anlage

## Datei- und Funktionsplan

## Empfohlene Dateien

### 1. Plugin-Hauptdatei

Pfad:
`custom/plugins/webapp-central-dokumentationen/webapp-central-dokumentationen.php`

Aufgabe:

- Plugin-Header
- Sicherheitscheck mit `ABSPATH`
- Laden oder Definieren der Registrierungsfunktionen
- Hook auf `init`

### 2. Optional spaeter: Moduldatei

Pfad:
`custom/plugins/webapp-central-dokumentationen/includes/register-documentation.php`

Aufgabe:

- CPT-Registrierung
- Taxonomie-Registrierung
- Metaregistrierung

Aktuelle Empfehlung:
fuer den ersten Prototyp nicht zwingend noetig, aber zulaessig, wenn die Hauptdatei sonst unruhig wird.

## Empfohlene Funktionen

### `webapp_central_docs_register_post_type()`

Registriert den CPT `dokumentation`.

Empfohlene Eigenschaften:

- `public` vorerst `true`, damit die WordPress-Struktur sauber ist
- `show_ui` `true`
- `show_in_menu` `true`
- `show_in_rest` `true`
- `has_archive` `false`, um keine implizite Blog-Archivlogik zu erzwingen
- `rewrite` mit Slug `dokumentationen`
- `supports` minimal:
  - `title`
  - `editor`
  - `excerpt`
  - `revisions`
  - `page-attributes`

Hinweis:
`page-attributes` ist sinnvoll fuer spaetere manuelle Reihenfolgen.

### `webapp_central_docs_register_taxonomies()`

Registriert:

- `doku_bereich`
- `doku_format`
- `doku_status`
- `doku_serie`

Empfohlene Eigenschaften:

- `show_ui` `true`
- `show_admin_column` `true`
- `show_in_rest` `true`
- hierarchisch:
  - `doku_bereich` ja
  - `doku_format` nein
  - `doku_status` nein
  - `doku_serie` nein

### `webapp_central_docs_register_meta()`

Registriert die Basis-Metafelder aus dem Feldmodell, zunaechst ohne ausgebaute Eingabemaske.

Empfohlene erste Meta-Keys:

- `doku_kurzbeschreibung`
- `doku_featured`
- `doku_featured_rank`
- `doku_is_canonical`
- `doku_primary_hub`
- `doku_position_label`
- `doku_next_step`
- `doku_visibility`
- `doku_updated_at`
- `doku_editorial_note`
- `doku_source_reference`

Hinweis:
`doku_related_documents` ist technisch moeglich, aber fuer den Minimalprototyp optional. Ohne saubere Relationship-UI bringt die reine Registrierung zunaechst wenig.

## Taxonomie-Initialisierung

Fuer den Prototyp sollen Taxonomien nur registriert, nicht automatisch befuellt werden.

Begruendung:

- keine unkontrollierte Inhaltserzeugung
- keine impliziten redaktionellen Entscheidungen
- Initialwerte koennen spaeter kontrolliert manuell oder ueber eine gezielte Setup-Routine angelegt werden

## Admin-Basis

Im Prototyp sinnvoll:

- klare deutschsprachige Labels
- eigener Menueeintrag fuer `Dokumentationen`
- Taxonomie-Spalten in der Adminliste

Im Prototyp bewusst nicht notwendig:

- eigene Meta-Boxen
- Feldgruppen
- Listenfilter-Speziallogik
- Hub-Manager
- Importdialoge

## Risiken

### Geringe Risiken

- CPT-Registrierung in eigenem Plugin ist klar abgrenzbar
- Taxonomie-Registrierung ist Standard-WordPress-Logik
- Metaregistrierung ohne UI ist technisch risikoarm

### Mittlere Risiken

- der Slug `dokumentationen` kann spaeter mit einer echten Seite gleichen Slugs kollidieren
- ohne Feld-UI sind registrierte Metafelder noch nicht redaktionell nutzbar
- `public = true` kann spaeter Frontend-Routen sichtbar machen, falls das System aktiv genutzt wird

### Gegenmassnahmen

- keine Templates bereitstellen
- kein Archiv aktivieren
- keine Inhalte automatisch anlegen
- klar dokumentieren, dass dies nur die Registrierungsbasis ist

## Tests

## Sinnvolle Minimaltests

1. PHP-Syntaxpruefung der Plugin-Datei
2. Sichtpruefung, dass der CPT registriert wird
3. Sichtpruefung, dass Taxonomien im Admin erscheinen
4. Sichtpruefung, dass keine Templates oder Inhalte automatisch angelegt werden

## In dieser Phase realistisch pruefbar

Ohne laufende WordPress-Instanz im Turn sicher pruefbar:

- PHP-Syntax mit `php -l`
- Dateistruktur
- statische Kontrolle der Registrierungsfunktionen

Wenn PHP lokal nicht verfuegbar ist, soll spaeter auf dem Server exakt folgender Syntaxcheck ausgefuehrt werden:

```powershell
php -l custom/plugins/webapp-central-dokumentationen/webapp-central-dokumentationen.php
```

Falls der Check im WordPress-Pluginverzeichnis der Laufzeitumgebung erfolgt, entsprechend mit absolutem oder deploymentnahem Pfad:

```powershell
php -l wp-content/plugins/webapp-central-dokumentationen/webapp-central-dokumentationen.php
```

Erwartetes Ergebnis:

- `No syntax errors detected in ...`

Optionaler Folgecheck, falls spaeter weitere PHP-Dateien im Plugin entstehen:

```powershell
Get-ChildItem custom/plugins/webapp-central-dokumentationen -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
```

Nicht automatisch pruefbar ohne laufende Instanz:

- Admin-Menues im Browser
- REST-Sichtbarkeit
- Verhalten im Redaktionsalltag

## Empfohlene Umsetzungsreihenfolge

1. Technisches Planungsdokument anlegen.
2. Eigenes Pluginverzeichnis fuer den Dokumentationsprototyp anlegen.
3. CPT registrieren.
4. Taxonomien registrieren.
5. Basis-Metafelder registrieren.
6. PHP-Syntax pruefen.
7. Keine weitere UI oder Frontendlogik in diesem Schritt.

## Entscheidungsfazit

Der risikoaermste erste Prototyp ist ein kleines eigenes Plugin fuer die Dokumentationslogik. Es schafft eine kontrollierte technische Grundlage, ohne Theme, produktive Inhalte oder Frontendstruktur anzufassen.

## Abgrenzung

Dieses Dokument ist ein technischer Prototypplan. Es ist kein Deploymentplan, kein Plugin-Release und keine Freigabe fuer produktive Veroeffentlichung.
