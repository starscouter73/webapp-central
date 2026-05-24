# Redaktionelles Feldmodell fuer `dokumentation`

Kurzbeschreibung: Dieses Dokument definiert das redaktionelle Feldmodell fuer den spaeteren WordPress-CPT `dokumentation` sowie die minimale Content-Map fuer den ersten Ausbau von `/dokumentationen/`.

## Zweck

Dieses Dokument uebersetzt die bestehende Dokumentationsarchitektur in ein kontrolliertes Datenmodell. Es dient als Grundlage fuer spaetere ACF-Felder, CPT-Implementierung, Hub-Seiten, Featured-Logik, Dokumentbeziehungen und die WordPress-Adminstruktur.

## Ausgangsbasis

Die Ableitung erfolgt aus:

- `AGENTS.md`
- `docs/canonical/`
- `docs/DOKUMENTATIONSARCHITEKTUR_WORDPRESS.md`

Leitgedanke:
`/dokumentationen/` ist kein klassischer Blogbereich, sondern ein kuratierter Dokumentations-Hub mit ruhiger Referenzlogik, klaren Einstiegen und kontrolliertem Wachstum.

## Modellprinzipien

- Das Feldmodell soll redaktionelle Klarheit erzeugen, nicht technische Komplexitaet verlagern.
- Taxonomien steuern Ordnung auf Systemebene.
- Felder steuern Darstellung, Prioritaet, Beziehungen und kuratierte Navigation.
- Canonical- und Featured-Logik werden bewusst manuell kuratiert.
- Nicht jedes moegliche Meta-Feld wird in dieser Phase eingefuehrt.

## Datenmodell: Grundentscheidung

Empfohlen wird fuer die erste Ausbaustufe:

- ein CPT `dokumentation`
- vier primaere Taxonomien:
  - `doku_bereich`
  - `doku_format`
  - `doku_status`
  - `doku_serie`
- eine kleine Menge redaktioneller Meta-Felder fuer Kuratierung und Beziehungen

Nicht Teil dieser Phase:

- technische Registrierung in WordPress
- ACF-Konfiguration
- Plugin-Code
- Template- oder Theme-Umsetzung

## Feldmodell fuer den CPT `dokumentation`

| Feldname | Technischer Name | Feldtyp | Pflichtfeld | Zweck | Beispielwert | Frontend sichtbar |
|---|---|---|---|---|---|---|
| Titel | `post_title` | Standard-Posttitel | ja | Primärer Dokumenttitel | `Warum webapp-central.de keine klassische Website ist` | ja |
| Hauptinhalt | `post_content` | Standard-Inhaltsfeld | ja | Vollständiger Dokumentinhalt | Canonical-Text des Dokuments | ja |
| Kurzbeschreibung | `doku_kurzbeschreibung` | Textarea oder kurzer WYSIWYG-Text | ja | Kompakte Einordnung für Hubs, Karten, Admin und interne Orientierung | `Dieses Dokument erklaert die Plattformidentitaet...` | ja |
| Dokumenttyp | `doku_format` | Taxonomie, Single Select | ja | Ordnet den Charakter des Dokuments ein | `Canonical Doc` | ja |
| Themenbereich | `doku_bereich` | Taxonomie, Single Select | ja | Ordnet das Dokument einem Kernbereich zu | `Plattformgrundlagen` | ja |
| Status | `doku_status` | Taxonomie, Single Select | ja | Steuert Reifegrad und Sichtbarkeitslogik | `Canonical` | ja |
| Serie / Reihe | `doku_serie` | Taxonomie, optional | nein | Verbindet zusammengehörige Dokumente in ruhigen Reihen | `Aufbau von webapp-central.de` | ja |
| Featured | `doku_featured` | Boolean / True-False | nein | Kennzeichnet redaktionell hervorgehobene Dokumente | `true` | nein |
| Featured-Gewicht | `doku_featured_rank` | Zahl | nein | Steuert die Reihenfolge innerhalb featured-kuratierter Listen | `10` | nein |
| Canonical-Status | `doku_is_canonical` | Boolean / True-False | nein | Explizite Kennzeichnung fuer den stabilen Wissenskern | `true` | nein |
| Hub-Zuordnung | `doku_primary_hub` | Select oder Relationship zu Hub-Seiten | ja | Legt die primäre kuratierte Einstiegsseite fest | `/dokumentationen/plattformgrundlagen/` | nein |
| Empfohlene Position | `doku_position_label` | Select | nein | Beschreibt die redaktionelle Rolle innerhalb eines Hubs | `Einstieg`, `Schluesseltext`, `Vertiefung` | ja |
| Manuelle Reihenfolge | `doku_menu_order` | Zahl | nein | Steuert kuratierte Reihenfolge in Hubs oder Serien | `1` | nein |
| Verwandte Dokumente | `doku_related_documents` | Relationship, mehrfach | nein | Zeigt inhaltlich nahe Referenztexte | andere Canonical Docs | ja |
| Naechster Leseschritt | `doku_next_step` | Relationship, Single Select | nein | Legt den empfohlenen naechsten Leseschritt fest | `Einfuehrung der AGENTS.md` | ja |
| Sichtbarkeit | `doku_visibility` | Select | ja | Steuert redaktionelle Freigabestufe | `oeffentlich`, `intern`, `vorbereitet` | nein |
| Aktualisiert am | `doku_updated_at` | Datum/Zeit | nein | Redaktionell gepflegtes Aktualisierungsdatum fuer sichtbare Hinweise | `2026-05-20` | ja |
| Redaktionelle Notiz | `doku_editorial_note` | Textarea | nein | Interne Hinweise fuer Redaktion, Struktur oder spaetere Ueberarbeitung | `Beim spaeteren Web-Rollout mit Governance-Hub verknuepfen.` | nein |
| Quellen- oder Bezugsdokument | `doku_source_reference` | Text oder URL | nein | Verweist auf Ursprungsdokument im Repo oder auf interne Referenzbasis | `docs/canonical/warum-webapp-central-keine-klassische-website-ist.md` | nein |

## Feldlogik und Empfehlungen

### Pflichtfelder in der ersten Ausbaustufe

Pflicht sein sollten:

- `post_title`
- `post_content`
- `doku_kurzbeschreibung`
- `doku_format`
- `doku_bereich`
- `doku_status`
- `doku_primary_hub`
- `doku_visibility`

Diese Felder reichen aus, um den Dokumentationsbereich kontrolliert aufzubauen, ohne ihn in der Adminpflege zu ueberladen.

### Felder mit bewusster Optionalitaet

Optional bleiben zunaechst:

- `doku_serie`
- `doku_featured`
- `doku_featured_rank`
- `doku_related_documents`
- `doku_next_step`
- `doku_editorial_note`

Begruendung:
Diese Felder sind fuer kuratierte Qualitaet wichtig, aber nicht fuer jede einzelne Eingabe zwingend erforderlich.

### Frontend-Sichtbarkeit

Im Frontend sichtbar sein sollten vor allem:

- Titel
- Kurzbeschreibung
- Dokumenttyp
- Themenbereich
- Status, sofern sinnvoll als lesbare Kennzeichnung
- Serie, falls vorhanden
- empfohlene Position nur kontextuell
- verwandte Dokumente
- naechster Leseschritt
- Aktualisiert am

Nicht sichtbar sein sollten:

- Featured-Flag
- Featured-Gewicht
- interne Sichtbarkeitseinstellung
- redaktionelle Notiz
- manuelle Reihenfolge
- interne Quellenreferenz

## Initiale Taxonomie-Werte

### `doku_bereich`

| Wert | Slug | Zweck |
|---|---|---|
| Plattformgrundlagen | `plattformgrundlagen` | Grundlegende Texte zur Plattformidentitaet und Architekturabsicht |
| Governance | `governance` | Regeln, Arbeitslogik, Agentenverhalten, Dokumentationsprinzipien |
| Infrastruktur | `infrastruktur` | Steuerlogik, Versionierung, technische Betriebsabgrenzung |
| Dokumentation | `dokumentation` | Spaetere Texte zur Dokumentationsmethodik, Pflege oder Wissensorganisation |
| Entwicklung | `entwicklung` | Spaetere Journal-, Timeline- oder Entwicklungsbeitraege |

### `doku_format`

| Wert | Slug | Zweck |
|---|---|---|
| Canonical Doc | `canonical-doc` | Stabile Referenztexte mit hohem Orientierungswert |
| Referenz | `referenz` | Fachliche oder strukturelle Bezugstexte ausserhalb des Canonical-Kerns |
| Leitfaden | `leitfaden` | Handlungs- und Umsetzungstexte |
| Entscheidung | `entscheidung` | Strukturierte Entscheidungsdokumente, spaeter auch ADR-nah |
| Journal | `journal` | Reflexive oder fortlaufende Entwicklungsbeitraege |
| Timeline | `timeline` | Chronologisch lesbare Entwicklungsschritte oder Projektstationen |

### `doku_status`

| Wert | Slug | Zweck |
|---|---|---|
| Canonical | `canonical` | Kernbestand mit langfristigem Referenzanspruch |
| Referenz | `referenz` | Stabile, aber nicht zentrale Dokumente |
| Aktiv | `aktiv` | Laufend relevante, aktuell genutzte Texte |
| Archiv | `archiv` | Dokumente mit Rueckblick- oder Verlaufskarakter |
| Entwurf intern | `entwurf-intern` | Noch nicht zur oeffentlichen Darstellung vorgesehene Inhalte |

### `doku_serie`

| Wert | Slug | Zweck |
|---|---|---|
| Aufbau von webapp-central.de | `aufbau-von-webapp-central-de` | Plattformgrundlagen und fruehe Strukturtexte |
| AGENTS.md und Governance | `agents-md-und-governance` | Governance- und Leitplankentexte |
| Dokumentation als Plattformgedaechtnis | `dokumentation-als-plattformgedaechtnis` | Dokumentationsphilosophie und Wissenssystem |
| Infrastruktur nachvollziehbar | `infrastruktur-nachvollziehbar` | Versionierung, Infrastruktur und technische Ordnung |

### Sichtbarkeitswerte fuer `doku_visibility`

| Wert | Technischer Wert | Zweck |
|---|---|---|
| Oeffentlich | `public` | Fuer spaetere oeffentliche Darstellung geeignet |
| Intern | `internal` | Nur fuer interne oder vorbereitende Nutzung |
| Vorbereitet | `prepared` | Redaktionell angelegt, aber noch nicht fuer breite Darstellung freigegeben |

## Minimale Content-Map fuer die erste Umsetzung

### Hub-Seiten

| Hub-Seite | Rolle | Typ | Charakter |
|---|---|---|---|
| `/dokumentationen/` | zentraler Dokumentations-Hub | Seite | statisch, kuratiert |
| `/dokumentationen/canonical/` | Einstieg in den stabilen Wissenskern | Seite | statisch, kuratiert |
| `/dokumentationen/plattformgrundlagen/` | Bereichshub fuer Identitaet und Architekturgrundlagen | Seite | statisch, kuratiert |
| `/dokumentationen/governance/` | Bereichshub fuer Governance und Arbeitslogik | Seite | statisch, kuratiert |
| `/dokumentationen/infrastruktur/` | Bereichshub fuer Infrastrukturprinzipien | Seite | statisch, kuratiert |

### Erste Dokumente

| Dokument | Technischer Vorschlag fuer Slug | Ausgangsquelle |
|---|---|---|
| Warum webapp-central.de keine klassische Website ist | `warum-webapp-central-keine-klassische-website-ist` | `docs/canonical/warum-webapp-central-keine-klassische-website-ist.md` |
| Einfuehrung der AGENTS.md | `einfuehrung-der-agents-md` | `docs/canonical/einfuehrung-der-agents-md.md` |
| Dokumentation als Plattformgedaechtnis | `dokumentation-als-plattformgedaechtnis` | `docs/canonical/dokumentation-als-plattformgedaechtnis.md` |
| GitHub als Steuerzentrale statt WordPress-Archiv | `github-als-steuerzentrale-statt-wordpress-archiv` | `docs/canonical/github-als-steuerzentrale-statt-wordpress-archiv.md` |
| Grundprinzipien der Plattformarchitektur | `grundprinzipien-der-plattformarchitektur` | `docs/canonical/grundprinzipien-der-plattformarchitektur.md` |

### Konkrete Zuordnung der ersten Canonical Docs

| Dokument | Primaere Hub-Seite | Bereich | Format | Status | Serie | Featured | Empfohlene Position | Verwandte Dokumente | Naechster Leseschritt |
|---|---|---|---|---|---|---|---|---|---|
| Warum webapp-central.de keine klassische Website ist | `/dokumentationen/plattformgrundlagen/` | Plattformgrundlagen | Canonical Doc | Canonical | Aufbau von webapp-central.de | ja | Einstieg | Grundprinzipien der Plattformarchitektur; Dokumentation als Plattformgedaechtnis; Einfuehrung der AGENTS.md | Einfuehrung der AGENTS.md |
| Einfuehrung der AGENTS.md | `/dokumentationen/governance/` | Governance | Canonical Doc | Canonical | AGENTS.md und Governance | ja | Schluesseltext | Warum webapp-central.de keine klassische Website ist; Dokumentation als Plattformgedaechtnis; Grundprinzipien der Plattformarchitektur | Grundprinzipien der Plattformarchitektur |
| Dokumentation als Plattformgedaechtnis | `/dokumentationen/governance/` | Governance | Canonical Doc | Canonical | Dokumentation als Plattformgedaechtnis | ja | Featured-Grundlagentext | Warum webapp-central.de keine klassische Website ist; Einfuehrung der AGENTS.md; Grundprinzipien der Plattformarchitektur | GitHub als Steuerzentrale statt WordPress-Archiv |
| GitHub als Steuerzentrale statt WordPress-Archiv | `/dokumentationen/infrastruktur/` | Infrastruktur | Canonical Doc | Canonical | Infrastruktur nachvollziehbar | ja | Referenztext | Grundprinzipien der Plattformarchitektur; Dokumentation als Plattformgedaechtnis | Grundprinzipien der Plattformarchitektur |
| Grundprinzipien der Plattformarchitektur | `/dokumentationen/plattformgrundlagen/` | Plattformgrundlagen | Canonical Doc | Canonical | Aufbau von webapp-central.de | ja | Zentrales Referenzdokument | Warum webapp-central.de keine klassische Website ist; Einfuehrung der AGENTS.md; Dokumentation als Plattformgedaechtnis; GitHub als Steuerzentrale statt WordPress-Archiv | Dokumentation als Plattformgedaechtnis |

### Hub-Logik fuer die erste Kuratierung

### `/dokumentationen/`

Empfohlener Aufbau:

- kurze Einfuehrung in die Rolle des Dokumentationsbereichs
- Einstiege in:
  - Plattformgrundlagen
  - Governance
  - Infrastruktur
- Featured-Block mit drei bis fuenf Canonical Docs
- klar abgegrenzter Hinweis, dass spaeter weitere Formate wie Journal, Entscheidungen und Leitfaeden folgen koennen

### `/dokumentationen/canonical/`

Empfohlener Aufbau:

- Einordnung des Canonical-Kerns
- empfohlener Einstiegspfad
- alle Canonical Docs in kuratierter Reihenfolge

Empfohlene Reihenfolge:

1. Warum webapp-central.de keine klassische Website ist
2. Einfuehrung der AGENTS.md
3. Grundprinzipien der Plattformarchitektur
4. Dokumentation als Plattformgedaechtnis
5. GitHub als Steuerzentrale statt WordPress-Archiv

### `/dokumentationen/plattformgrundlagen/`

Empfohlene Dokumente:

- Warum webapp-central.de keine klassische Website ist
- Grundprinzipien der Plattformarchitektur

### `/dokumentationen/governance/`

Empfohlene Dokumente:

- Einfuehrung der AGENTS.md
- Dokumentation als Plattformgedaechtnis

### `/dokumentationen/infrastruktur/`

Empfohlene Dokumente:

- GitHub als Steuerzentrale statt WordPress-Archiv

## Empfohlene Priorisierung fuer spaetere Adminpflege

### Unbedingt im ersten Schritt pflegen

- Dokumenttyp
- Bereich
- Status
- Kurzbeschreibung
- Primaerer Hub
- Featured ja/nein
- Verwandte Dokumente
- Naechster Leseschritt

### Spaeter ausbaubar

- Featured-Gewicht
- Redaktionelle Notiz
- manuelle Reihenfolge je Hub
- differenziertere Sichtbarkeitslogik
- weitergehende Beziehungsmodelle

## Risiko- und Qualitaetshinweise

- Nicht jedes Dokument sollte mehrere gleich starke Hubs erhalten. Eine primaere Hub-Zuordnung haelt das System ruhig.
- `Canonical` und `Featured` sollten nicht verwechselt werden. Nicht jedes Featured-Dokument ist zwingend canonical, auch wenn das initial haeufig zusammenfaellt.
- `doku_status` und `doku_visibility` sollten getrennt bleiben. Ein Dokument kann inhaltlich canonical sein, aber redaktionell noch nicht oeffentlich freigegeben.
- `doku_serie` ist nur sinnvoll, wenn sie echte Orientierung stiftet. Serieninflation untergraebt die ruhige Referenzlogik.

## Naechste kontrollierte Ausbaustufe

Nach diesem Feldmodell waeren spaeter sinnvoll:

1. Uebersetzung in konkrete ACF-Gruppen oder eine vergleichbare Meta-Feldstruktur.
2. Definition der minimalen Admin-Eingabemaske fuer Redakteure.
3. Mapping der Hub-Seiten auf konkrete Query-Logik.
4. Erst danach technische Registrierung von CPT und Taxonomien.

## Abgrenzung

Dieses Dokument ist ein Architekturartefakt fuer Datenmodell und redaktionelle Ordnung. Es ist keine produktive Implementierung, kein Plugin-Konzept und kein Frontend-Blueprint.
