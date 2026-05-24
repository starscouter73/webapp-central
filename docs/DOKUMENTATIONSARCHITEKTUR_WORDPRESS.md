# Dokumentationsarchitektur fuer WordPress

Kurzbeschreibung: Dieses Dokument leitet aus der bestehenden Canonical-Docs-Basis eine langfristig tragfaehige Informationsarchitektur fuer `/dokumentationen/` ab. Der Fokus liegt auf CMS-Struktur, Navigationslogik, Dokumenttypen und Governance-Kompatibilitaet, nicht auf Frontend oder produktiver Umsetzung.

## Zweck

Dieses Dokument definiert die empfohlene Grundarchitektur fuer den spaeteren Dokumentationsbereich von `webapp-central.de`. Es uebersetzt die bestehende Canonical-Docs-Struktur in ein WordPress-kompatibles Modell, das ruhig navigierbar, redaktionell konsistent und langfristig erweiterbar bleibt.

## Ausgangslage

Die bisherige Canonical-Basis bildet drei stabile Themenkerne:

- Plattformgrundlagen
- Governance & Arbeitslogik
- Infrastrukturprinzipien

Diese Texte sind keine Blogbeitraege, sondern Referenzdokumente mit langfristigem Geltungsanspruch. Daraus folgt, dass `/dokumentationen/` nicht als klassischer News- oder Chronologie-Bereich modelliert werden sollte.

## Architekturprinzipien fuer den Dokumentationsbereich

- `dokumentationen/` ist ein kuratierter Hub, kein Standard-Blog.
- Wenige starke Einstiegspunkte sind wichtiger als viele gleichrangige Listen.
- Statische Orientierungsebenen und dynamische Wachstumsbereiche muessen klar getrennt bleiben.
- Canonical-Inhalte erhalten eine andere Sichtbarkeit und Gewichtung als Journal- oder Timeline-Eintraege.
- Taxonomien sollen Orientierung schaffen, nicht neue Komplexitaet erzeugen.
- Archivlogik darf nicht auf Monatsarchiven oder rein chronologischer Sortierung beruhen.
- Jede Strukturentscheidung muss spaeter in WordPress ohne Theme-Neubau umsetzbar sein.

## Analyse der bestehenden Canonical-Docs-Struktur

### Plattformgrundlagen

Rolle:
Erklaert, was die Plattform ist, wie sie gedacht werden soll und welche Grundannahmen fuer Struktur und Aussenwirkung gelten.

Charakter:
statisch, featured-faehig, hoher Einstiegswert, geringe Aenderungsfrequenz

### Governance & Arbeitslogik

Rolle:
Erklaert, wie innerhalb der Plattform gedacht, dokumentiert und gearbeitet wird.

Charakter:
statisch bis moderat wachsend, referenzorientiert, ordnungsstiftend

### Infrastrukturprinzipien

Rolle:
Erklaert technische Steuerlogik, Versionierungsgrenzen und operative Abgrenzungen.

Charakter:
referenzorientiert, teils erweiterbar, aber nicht als laufendes Techniklogbuch gedacht

## Empfohlenes Inhaltsmodell fuer WordPress

### Grundentscheidung

Empfohlen wird ein kleines Kernmodell:

- statische Seiten fuer Hubs und Einstiege
- ein zentraler Dokument-CPT fuer die meisten dokumentarischen Inhalte
- wenige kontrollierte Taxonomien
- spaeter optional erweiterbare Spezial-CPTs nur bei echtem Mengendruck

Dieses Modell ist ruhiger und wartbarer als eine fruehe Aufspaltung in viele gleichwertige Post Types.

## Empfohlene Content-Typen

### 1. Seiten

Verwendung:

- `/dokumentationen/` als Haupt-Einstiegsseite
- Hub-Seiten fuer kuratierte Unterbereiche
- statische Ordnungsseiten mit Einleitung, Kontext und kuratierten Verweisen

Empfohlene Seiten:

- `/dokumentationen/`
- `/dokumentationen/canonical/`
- `/dokumentationen/plattformgrundlagen/`
- `/dokumentationen/governance/`
- `/dokumentationen/infrastruktur/`
- spaeter optional `/dokumentationen/journal/`
- spaeter optional `/dokumentationen/entscheidungen/`
- spaeter optional `/dokumentationen/leitfaeden/`

Hinweis:
Diese Seiten sind keine reinen Archive, sondern kuratierte Einstiegspunkte.

### 2. CPT `dokumentation`

Verwendung:

- Canonical Docs
- Journal-Eintraege
- Timeline-nahe Entwicklungsbeitraege
- Leitfaeden
- Referenztexte
- Infrastrukturtexte
- Governance-Texte

Vorteil:
Alle Dokumente bleiben technisch in einem gemeinsamen Wissenssystem, waehrend Typ, Bereich und Status ueber Taxonomien und Metadaten gesteuert werden.

### 3. Optional spaeterer CPT `entscheidung`

Nur einfuehren, wenn spaeter formale ADRs oder strukturierte Entscheidungsdokumente in groesserer Zahl entstehen.

Aktuelle Empfehlung:
Noch nicht einfuehren. Entscheidungen koennen zunaechst als `dokumentation` mit eigenem Dokumenttyp gefuehrt werden.

### 4. Optional spaeterer CPT `referenzsammlung` oder `verzeichnis`

Nur sinnvoll, wenn spaeter umfangreiche Link-, Quellen- oder Systemverzeichnisse entstehen.

Aktuelle Empfehlung:
Nicht in dieser Phase.

## Empfohlene Taxonomien

### 1. `doku_bereich`

Zweck:
Ordnet Dokumente nach ihrem inhaltlichen Kernbereich.

Empfohlene Startwerte:

- Plattformgrundlagen
- Governance
- Infrastruktur
- Dokumentation
- Entwicklung

Hinweis:
`Dokumentation` und `Entwicklung` sind bewusst fuer spaetere Journal-, Leitfaden- und Entscheidungsformate vorbereitet.

### 2. `doku_format`

Zweck:
Kennzeichnet den Dokumentcharakter unabhaengig vom Themenbereich.

Empfohlene Startwerte:

- Canonical Doc
- Journal
- Timeline
- Entscheidung
- Leitfaden
- Referenz

Hinweis:
Diese Taxonomie ist zentral, weil sie die Bloglogik ersetzt.

### 3. `doku_status`

Zweck:
Steuert Reifegrad und Sichtbarkeit innerhalb des Dokumentationssystems.

Empfohlene Startwerte:

- Canonical
- Referenz
- Aktiv
- Archiv
- Entwurf intern

Hinweis:
`Canonical` ist kein blosses Label, sondern ein Sichtbarkeits- und Prioritaetsstatus.

### 4. `doku_serie`

Zweck:
Bindet zusammengehoerende Texte an eine ruhige Reihenlogik statt an lose Schlagwoerter.

Empfohlene Startwerte:

- Aufbau von webapp-central.de
- AGENTS.md und Governance
- Dokumentation als Plattformgedaechtnis
- Infrastruktur nachvollziehbar

Hinweis:
Serien sollten sparsam verwendet werden. Nicht jedes Dokument braucht eine Reihe.

### 5. `doku_beziehung`

Zweck:
Kann spaeter semantische Beziehungen ausdruecken, wenn die reine Verlinkung nicht ausreicht.

Moegliche Werte:

- baut auf
- ergaenzt
- konkretisiert
- ersetzt spaeter

Aktuelle Empfehlung:
Noch nicht als sichtbare Frontend-Taxonomie verwenden. Zunaechst nur als internes Strukturkonzept denken.

## Dokumenttyp-Logik

Der Dokumenttyp soll fachlich staerker wirken als das Publikationsdatum.

Empfohlene Prioritaetsordnung:

1. Canonical Doc
2. Referenz
3. Leitfaden
4. Entscheidung
5. Journal
6. Timeline

Ableitung:

- Canonical und Referenz stehen fuer dauerhafte Orientierung.
- Leitfaeden und Entscheidungen sind arbeitsbezogen, aber weiterhin strukturiert.
- Journal und Timeline bilden Entwicklungsgedaechtnis, sollen aber nicht die Hauptidentitaet des Bereichs dominieren.

## Featured-Logik

Featured darf nicht als generische Hervorhebung fuer viele Inhalte verwendet werden.

Empfehlung:

- Featured hauptsaechlich fuer Canonical Docs und wenige Schluesseltexte
- maximal drei bis sechs hervorgehobene Dokumente pro Hub
- Featured-Inhalte manuell kuratieren, nicht algorithmisch befuellen

Empfohlene Featured-Kategorien:

- Gesamtplattform-Einstieg
- Governance-Schluesseltext
- Architektur-Schluesseltext
- Dokumentations-Schluesseltext
- Infrastruktur-Schluesseltext

Nicht empfohlen:

- automatisch neueste Beitraege als Featured
- rotierende Mengenfeatured-Bloecke
- zeitbasierte Ersetzung von Referenztexten

## Reihen- und Serienlogik

Reihen sollen Orientierung vertiefen, nicht Navigation verdoppeln.

Empfohlene Verwendung:

- fuer zusammenhaengende Canonical-Cluster
- fuer spaetere thematische Entwicklungsreihen
- fuer laenger laufende Dokumentationsboegen mit klarer Abfolge

Nicht empfohlen:

- Reihen als Ersatz fuer Bereiche
- zu viele kleine Einser-Serien
- automatische Serien fuer jeden Themenbezug

## Canonical-Kennzeichnung

Canonical-Inhalte benoetigen eine sichtbare Sonderrolle.

Empfohlene Regeln:

- Dokumenttyp `Canonical Doc`
- Status `Canonical`
- Einbindung auf der Hub-Seite `/dokumentationen/canonical/`
- manuelle Verknuepfung mit verwandten Referenztexten
- bevorzugte Platzierung auf Haupt- und Bereichseinstiegen

Canonical bedeutet:

- langfristige Relevanz
- geringe Austauschhaeufigkeit
- hoher Orientierungswert
- redaktionell kontrollierte Aenderung

## Statisch vs. dynamisch wachsende Bereiche

### Ueberwiegend statisch

- `/dokumentationen/`
- `/dokumentationen/canonical/`
- Plattformgrundlagen
- Governance
- Infrastruktur als Einstiegs- und Referenzebene

Diese Bereiche sollen kuratiert und stabil bleiben.

### Kontrolliert dynamisch

- Journal
- Timeline
- Entscheidungen
- Leitfaeden

Diese Bereiche duerfen wachsen, aber nur innerhalb klarer Dokumenttyp- und Hub-Logik.

### Nicht als Primaerstruktur geeignet

- Monatsarchive
- Tag-Clouds
- unkuratiertes Kategorienlisting
- rein chronologische Startseiten

## Empfohlene Navigationshierarchie

### Hauptnavigation

Empfohlener Eintrag:

- Dokumentationen

Die Hauptnavigation soll nicht alle Untertypen direkt ausklappen. Das verletzt die Ruhe des Systems.

### Ebene 1: `/dokumentationen/`

Rolle:
zentraler Dokumentations-Hub

Inhalt:

- kurze Einordnung des Bereichs
- drei starke Einstiege in die stabilen Kernbereiche
- ein begrenzter Block mit Featured-Canonical-Docs
- ein klar abgegrenzter Einstieg in Entwicklungsformate

### Ebene 2: kuratierte Hub-Seiten

Empfohlene Hubs:

- Canonical Docs
- Plattformgrundlagen
- Governance
- Infrastruktur
- spaeter Journal
- spaeter Entscheidungen
- spaeter Leitfaeden

Jeder Hub soll:

- den Bereich kurz erklaeren
- keine rohe Archivseite sein
- wenige priorisierte Dokumente zeigen
- erst danach weitere passende Inhalte listen

### Ebene 3: Einzeldokumente

Einzeldokumente sollen ueber Kontext verfuegen:

- Dokumenttyp
- Bereich
- Status
- Serie
- verwandte Dokumente

Damit wird die Dokumentseite Teil eines Systems und nicht isolierter Content.

## Seitenbeziehungen und Zusammenhang zwischen Dokumenten

Jedes wichtige Dokument sollte drei Beziehungstypen unterstuetzen:

- uebergeordneter Hub
- verwandte Referenztexte
- naechster sinnvoller Leseschritt

Empfohlene Logik:

- Canonical Docs verweisen gegenseitig auf den stabilen Wissenskern.
- Journal- und Timeline-Inhalte verweisen bevorzugt auf Canonical- oder Referenztexte zur Einordnung.
- Entscheidungen verweisen auf das Problem, die getroffene Ableitung und spaetere Auswirkungen.
- Leitfaeden verweisen auf die Referenzbasis, aus der ihre Handlungsschritte abgeleitet wurden.

## Empfohlene URL- und Hierarchielogik

Die folgende Struktur ist in WordPress spaeter gut abbildbar, ohne Blogoptik zu erzwingen:

- `/dokumentationen/`
- `/dokumentationen/canonical/`
- `/dokumentationen/plattformgrundlagen/`
- `/dokumentationen/governance/`
- `/dokumentationen/infrastruktur/`
- `/dokumentationen/journal/`
- `/dokumentationen/entscheidungen/`
- `/dokumentationen/leitfaeden/`

Fuer Einzeldokumente:

- `/dokumentationen/<slug>/` fuer besonders zentrale Canonical-Dokumente
- alternativ spaeter konsistent nach Format oder Bereich, wenn die Menge steigt

Empfehlung:
Nicht zu frueh eine tiefe URL-Systematik erzwingen. Erst die Hub-Logik stabilisieren, dann Rewrite-Details festlegen.

## Empfohlene WordPress-Umsetzung ohne Ueberbau

Minimalmodell fuer die spaetere technische Umsetzung:

- Seiten fuer Hubs und Einstiege
- ein CPT `dokumentation`
- Taxonomien `doku_bereich`, `doku_format`, `doku_status`, `doku_serie`
- manuelle Featured-Auswahl
- kuratierte Query-Logik pro Hub statt Standardarchiven

Bewusst nicht empfohlen in dieser Phase:

- mehrere parallele Dokument-CPTs
- automatische Blog-Startseite fuer `/dokumentationen/`
- Monats- oder Datumsarchive als primaere Navigation
- ueberladene Filteroberflaechen

## Empfohlenes Zielbild fuer `/dokumentationen/`

`/dokumentationen/` soll langfristig vier Rollen zugleich erfuellen:

- kuratierter Dokumentations-Hub
- Governance- und Architekturzentrum
- Plattformgedaechtnis
- kontrollierter Einstieg in Entwicklungsverlaeufe

Die Identitaet des Bereichs soll dabei immer von Referenz und Orientierung ausgehen. Chronologie darf vorhanden sein, aber sie darf die Struktur nicht dominieren.

## Umsetzungsempfehlung fuer die naechsten Schritte

1. Diese Informationsarchitektur als konzeptionelle Basis festhalten.
2. Danach ein redaktionelles Feldmodell definieren, das die Taxonomien in konkrete CMS-Felder uebersetzt.
3. Anschliessend eine minimale WordPress-Content-Map vorbereiten:
   - welche Hub-Seiten angelegt werden
   - welche Canonical Docs zuerst uebernommen werden
   - welche Taxonomie-Werte initial benoetigt werden
4. Erst danach ueber Templates, Theme-Einbindung oder Admin-UX entscheiden.

## Abgrenzung

Dieses Dokument ist kein Frontend-Konzept, kein Theme-Blueprint und keine produktive Implementierungsanweisung. Es ist eine ruhige Strukturvorlage fuer den spaeteren kontrollierten Ausbau des Dokumentationsbereichs.
