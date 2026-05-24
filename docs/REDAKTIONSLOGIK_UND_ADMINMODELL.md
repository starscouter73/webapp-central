# Redaktionslogik und Adminmodell fuer `dokumentation`

Kurzbeschreibung: Dieses Dokument definiert die empfohlene Redaktions- und Eingabelogik fuer den spaeteren WordPress-CPT `dokumentation`. Es beschreibt keine technische Umsetzung, sondern eine ruhige, kuratierte und governance-kompatible Pflegearchitektur.

## Zweck

Dieses Dokument uebersetzt die bestehende Dokumentationsarchitektur und das redaktionelle Feldmodell in eine konkrete Admin- und Eingabelogik. Ziel ist eine Redaktionsoberflaeche, die klare Entscheidungen ermoeglicht, Informationschaos begrenzt und langfristig ohne CMS-Ueberfrachtung pflegbar bleibt.

## Ausgangsbasis

Die Ableitung erfolgt aus:

- `AGENTS.md`
- `docs/canonical/`
- `docs/DOKUMENTATIONSARCHITEKTUR_WORDPRESS.md`
- `docs/REDAKTIONELLES_FELDMODELL_DOKUMENTATION.md`

Leitregel:
Die Redaktion soll kuratieren koennen, ohne in einer komplexen CMS-Maschine zu versinken.

## Redaktionsprinzipien

- Wenige Pflichtentscheidungen vor vielen Optionalfeldern
- Sichtbare Felder nur dort, wo sie echte Redaktionsentscheidungen erfordern
- Unterschiedliche Dokumenttypen erhalten unterschiedliche Eingabetiefen
- Canonical Docs werden strenger gefuehrt als dynamische Formate
- Featured, Serie und Beziehungen bleiben kuratierte Signale und keine Massenlabels
- Die Adminlogik folgt dem Zweck des Dokuments, nicht der Vollstaendigkeit des Systems

## Grundmodell der Eingabemaske

Empfohlen wird eine Eingabemaske mit drei Ebenen:

1. Kernangaben
2. Kurationsangaben
3. Erweiterte interne Angaben

Diese Ebenen muessen sich klar unterscheiden. Nicht jede Redaktion sieht von Beginn an alle Felder.

## Ebene 1: Kernangaben

Diese Felder sollten fuer nahezu alle Dokumenttypen immer sichtbar sein:

- Titel
- Kurzbeschreibung
- Dokumenttyp
- Themenbereich
- Status
- Hauptinhalt
- Primaerer Hub
- Sichtbarkeit

Begruendung:
Mit diesen Angaben kann jedes Dokument sinnvoll eingeordnet, gespeichert und spaeter dargestellt werden.

## Ebene 2: Kurationsangaben

Diese Felder sollten sichtbar sein, aber nicht zwingend permanent im Fokus stehen. Sie koennen als eigener Bereich unterhalb der Kernangaben erscheinen:

- Serie / Reihe
- Featured
- Empfohlene Position
- Verwandte Dokumente
- Naechster Leseschritt
- Aktualisiert am

Begruendung:
Diese Felder strukturieren die Leserfuehrung und die ruhige Ordnung der Plattform, sind aber nicht fuer jedes Dokument gleich relevant.

## Ebene 3: Erweiterte interne Angaben

Diese Felder sollten bewusst zurueckgenommen oder standardmaessig eingeklappt werden:

- Featured-Gewicht
- Manuelle Reihenfolge
- Canonical-Status
- Redaktionelle Notiz
- Quellen- oder Bezugsdokument

Begruendung:
Diese Felder sind fuer Governance, Strukturpflege und spaetere Kontrolle wichtig, sollen aber die primaere Eingabemaske nicht ueberladen.

## Sichtbarkeitslogik der Felder

### Immer sichtbar

- `post_title`
- `doku_kurzbeschreibung`
- `doku_format`
- `doku_bereich`
- `doku_status`
- `post_content`
- `doku_primary_hub`
- `doku_visibility`

Diese Felder bilden das minimale Redaktionsgeruest.

### Optional sichtbar

- `doku_serie`
- `doku_featured`
- `doku_position_label`
- `doku_related_documents`
- `doku_next_step`
- `doku_updated_at`

Diese Felder sollten sichtbar, aber nicht zwingend bei jeder Pflegeaktion aktiv bearbeitet werden.

### Nur bei bestimmten Dokumenttypen sichtbar

#### Journal

- `doku_serie` nur wenn der Eintrag Teil einer laenger laufenden Entwicklungsreihe ist
- `doku_related_documents`
- `doku_next_step`
- `doku_updated_at`

Nicht noetig im Regelfall:

- `doku_featured_rank`
- `doku_menu_order`

#### Leitfaden

- `doku_related_documents`
- `doku_next_step`
- `doku_updated_at`
- optional `doku_serie`, wenn mehrere Leitfaeden zusammenhaengen

#### Entscheidung

- `doku_related_documents`
- `doku_next_step`
- `doku_source_reference`
- `doku_updated_at`

#### Timeline

- `doku_related_documents`
- `doku_source_reference`

Reduzierter Bedarf bei:

- `doku_featured`
- `doku_position_label`
- `doku_serie`

### Nur fuer Canonical Docs sichtbar

- `doku_is_canonical`
- `doku_featured`
- `doku_featured_rank`
- `doku_menu_order`
- `doku_position_label`

Zusatzregel:
Bei Canonical Docs sollten `doku_related_documents` und `doku_next_step` praktisch immer gepflegt werden, auch wenn sie technisch optional bleiben.

### Bewusst verborgen oder selten sichtbar

- `doku_featured_rank` nur wenn `doku_featured = true`
- `doku_menu_order` nur wenn eine kuratierte Hub-Reihenfolge wirklich gepflegt wird
- `doku_editorial_note` nur in einer internen Bearbeitungssicht
- `doku_source_reference` nur fuer Dokumenttypen mit starker Quellen- oder Bezugslogik

## Empfohlene Eingabereihenfolge

Die Eingabefolge sollte sich an der redaktionellen Denklogik orientieren, nicht an technischer Meta-Feld-Reihenfolge.

### Schritt 1: Identitaet des Dokuments

- Titel
- Kurzbeschreibung
- Dokumenttyp
- Themenbereich
- Status

Ziel:
Das Dokument soll zunaechst fachlich eingeordnet werden, bevor Kurationsentscheidungen getroffen werden.

### Schritt 2: Inhalt und Aussage

- Hauptinhalt
- optional Aktualisiert am

Ziel:
Erst wenn die Grundrolle geklaert ist, wird der eigentliche Inhalt ausformuliert oder uebernommen.

### Schritt 3: Kuratierte Einordnung

- Primaerer Hub
- Serie / Reihe
- Empfohlene Position
- Verwandte Dokumente
- Naechster Leseschritt

Ziel:
Das Dokument wird in die Informationsarchitektur eingebettet.

### Schritt 4: Sichtbarkeit und Priorisierung

- Sichtbarkeit
- Featured
- bei Bedarf Featured-Gewicht
- bei Bedarf Canonical-Status

Ziel:
Erst am Ende wird entschieden, wie stark das Dokument hervorgehoben oder freigegeben wird.

### Schritt 5: Interne Pflege

- Redaktionelle Notiz
- Quellen- oder Bezugsdokument
- Manuelle Reihenfolge

Ziel:
Diese Angaben dienen der spaeteren Pflege und Kontrolle, nicht der inhaltlichen Erstfassung.

## Pflichtfelder, Vorschlagsfelder und Kurationsfelder

### Pflichtfelder

Pflicht bleiben sollten:

- Titel
- Kurzbeschreibung
- Dokumenttyp
- Themenbereich
- Status
- Hauptinhalt
- Primaerer Hub
- Sichtbarkeit

Bei Canonical Docs zusaetzlich redaktionell verpflichtend:

- Verwandte Dokumente
- Naechster Leseschritt
- Featured ja/nein als bewusste Entscheidung

### Automatisch vorschlagbare Felder

Spaeter automatisch vorschlagbar waeren:

- `doku_primary_hub` aus `doku_bereich`
- `doku_status` aus `doku_format`
- `doku_is_canonical` aus `doku_format = Canonical Doc`
- `doku_updated_at` aus Bearbeitungsdatum
- `doku_source_reference` aus Importquelle

Wichtig:
Automatische Vorschlaege sollen bestaetigt, nicht blind uebernommen werden.

### Spaeter automatisch generierbare Felder

Mit spaeterer technischer Reife waeren generierbar:

- Standard-Lesepfade fuer wiederkehrende Canonical-Abfolgen
- Vorschlaege fuer verwandte Dokumente auf Basis gleicher Taxonomien
- Hub-Vorauswahl aus Bereich und Format
- Sichtbarkeitshinweise aus Status und Redaktionszustand

Diese Automatisierung darf nur assistierend arbeiten.

### Manuell kuratiert bleibende Felder

Immer manuell kuratiert bleiben sollten:

- Kurzbeschreibung
- Featured
- Featured-Gewicht
- Empfohlene Position
- Verwandte Dokumente
- Naechster Leseschritt
- Redaktionelle Notiz

Begruendung:
Diese Felder enthalten redaktionelle Absicht, nicht nur Strukturinformation.

## Dokumenttypspezifische Adminlogik

### Canonical Docs

Charakter:
hohe Relevanz, geringe Frequenz, starke Kuratierung

Empfohlene Adminlogik:

- vollstaendige Kernangaben immer sichtbar
- Kurationsbereich immer sichtbar
- erweiterte interne Angaben sichtbar, aber eingeklappt
- `Featured` muss bewusst entschieden werden
- `Verwandte Dokumente` und `Naechster Leseschritt` sollen aktiv gepflegt werden
- `Serie` nur wenn sie Orientierung schafft

Canonical Docs benoetigen die engste redaktionelle Fuehrung.

### Journal

Charakter:
dynamischer, aber kontrolliert

Empfohlene Adminlogik:

- reduzierte Eingabemaske
- kein Zwang zu Featured
- `Serie` nur bei echten Reihen
- `Naechster Leseschritt` optional
- `Verwandte Dokumente` sinnvoll, aber nicht immer zwingend

Journal-Eintraege sollen nicht dieselbe Kurationslast tragen wie Canonical Docs.

### Leitfaeden

Charakter:
arbeitsorientiert, anwendungsnah, anschlussfaehig an Referenztexte

Empfohlene Adminlogik:

- Kernangaben plus Kurationsangaben
- `Verwandte Dokumente` wichtig
- `Naechster Leseschritt` wichtig
- `Serie` optional
- `Featured` nur in Ausnahmefaellen

### Entscheidungen

Charakter:
strukturierte Ableitung, mittlere Kuratierungstiefe

Empfohlene Adminlogik:

- Kernangaben immer sichtbar
- `Verwandte Dokumente` und `Quellen- oder Bezugsdokument` wichtig
- `Featured` selten
- `Serie` nur wenn Entscheidungen Teil eines zusammenhaengenden Strangs sind

### Timeline-Eintraege

Charakter:
chronologischer, aber nicht blogdominant

Empfohlene Adminlogik:

- deutlich reduzierte Maske
- keine starke Featured-Logik
- `Naechster Leseschritt` optional
- `Serie` selten
- `Verwandte Dokumente` nur falls echte Einordnung noetig ist

Timeline soll Verlauf dokumentieren, nicht den kuratierten Wissenskern simulieren.

## Reduzierte und erweiterte Eingabemasken

### Reduzierte Eingabemasken

Diese Dokumenttypen sollten reduzierte Masken erhalten:

- Journal
- Timeline

Sichtbar bleiben:

- Titel
- Kurzbeschreibung
- Dokumenttyp
- Bereich
- Status
- Hauptinhalt
- Primaerer Hub
- Sichtbarkeit
- optional verwandte Dokumente

### Erweiterte Kurationsmasken

Diese Dokumenttypen benoetigen erweiterte Kurationsfelder:

- Canonical Doc
- Leitfaden
- Entscheidung

Zusaetzlich sichtbar:

- Serie
- Featured
- Empfohlene Position
- Verwandte Dokumente
- Naechster Leseschritt
- Quellen- oder Bezugsdokument bei Bedarf

## Empfohlene Adminstruktur

Die spaetere Adminstruktur sollte nicht nach technischer Vollstaendigkeit, sondern nach Pflegezweck geordnet sein.

### Bereich `Dokumente`

Enthaelt:

- alle Eintraege des CPT `dokumentation`
- Filter nach Dokumenttyp
- Filter nach Bereich
- Filter nach Status
- Filter nach Sichtbarkeit

Empfohlene Listenansicht:

- Titel
- Dokumenttyp
- Bereich
- Status
- Primaerer Hub
- Featured
- Sichtbarkeit
- Aktualisiert am

### Bereich `Taxonomien`

Relevant:

- Bereiche
- Formate
- Status
- Serien

Pflegeprinzip:
Taxonomien sollen selten erweitert und nicht im Alltagsfluss beliebig vermehrt werden.

### Bereich `Hub-Zuordnungen`

Keine eigene ueberladene Struktur noetig.

Empfehlung:
Ein primaerer Hub als klares Feld, keine offene Mehrfach-Hub-Logik als Standard.

### Bereich `Featured-Inhalte`

Featured benoetigt eine enge Kurationslogik:

- kein Massenflagging
- nur gezielte Dokumente
- bevorzugt Canonical oder Schluesseltexte
- regelmaessige manuelle Pruefung

### Bereich `Canonical Docs`

Canonical sollte spaeter einfach filterbar sein ueber:

- Dokumenttyp `Canonical Doc`
- Status `Canonical`
- optional Boolean `doku_is_canonical`

Canonical ist damit auffindbar, ohne einen komplett separaten Redaktionsraum zu erzwingen.

### Bereich `Sichtbarkeit`

Sichtbarkeit sollte als redaktioneller Freigabestatus verstanden werden:

- `public`
- `prepared`
- `internal`

Diese Logik muss klar vom inhaltlichen Status getrennt bleiben.

## Governance-kompatible Pflegephilosophie

### Wie die Redaktion ruhig bleibt

- Pflichtentscheidungen klein halten
- seltene Felder aus der Primaermaske herausnehmen
- keine gleichzeitige Kurationspflicht fuer alle Dokumenttypen erzeugen
- Hubs kuratieren statt Archive sich selbst zu ueberlassen

### Wie Informationschaos vermieden wird

- ein primaerer Hub pro Dokument
- ein klarer Bereich pro Dokument
- Serien nur bei echter Reihenlogik
- Featured nur bei begruendeter Hervorhebung
- keine Navigation aus reiner Chronologie ableiten

### Wie Serieninflation verhindert wird

- neue Serien nur bei mindestens zwei bis drei wirklich zusammenhaengenden Dokumenten
- Serien duerfen keine Ersatzkategorien sein
- lose thematische Naehe wird ueber verwandte Dokumente geloest, nicht ueber neue Serien

### Wie Featured-Inflation verhindert wird

- Featured als knappe Ressource behandeln
- pro Hub nur wenige Featured-Dokumente
- Featured regelmaessig pruefen
- Journal- und Timeline-Inhalte nur ausnahmsweise featuren

### Wie die Plattform kuratiert statt chronologisch-chaotisch bleibt

- Start- und Hub-Seiten bleiben manuell gefuehrt
- Chronologie wird nur in dafuer vorgesehenen Formaten sichtbar
- Canonical und Referenz behalten Prioritaet vor Neuigkeit
- Lesepfade werden bewusst gesetzt statt automatisch aus Datum erzeugt

## Selten zu verwendende Felder

Diese Felder sollten bewusst sparsam eingesetzt werden:

- `doku_featured_rank`
- `doku_menu_order`
- `doku_serie`
- `doku_editorial_note`

Sie haben Nutzen, verursachen aber schnell Pflegekomplexitaet, wenn sie inflationaer verwendet werden.

## Naechste kontrollierte Ausbaustufe

Nach dieser Redaktionslogik waeren spaeter sinnvoll:

1. Ableitung einer konkreten Feldgruppierung fuer die Adminmaske
2. Definition einer Listenansicht mit den wichtigsten Redaktionsspalten
3. Festlegung einfacher Redaktionsregeln fuer neue Dokumenttypen
4. Erst danach technische Umsetzung in WordPress oder ACF

## Abgrenzung

Dieses Dokument ist ein Architekturartefakt fuer Redaktionsworkflow, Eingabelogik und Adminfuehrung. Es ist keine technische WordPress-Spezifikation und keine Implementierungsanweisung.
