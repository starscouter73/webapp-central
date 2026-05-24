# GitHub als Steuerzentrale statt WordPress-Archiv

Kurzbeschreibung: Dieses Dokument erklaert die Versionierungslogik des Projekts und begruendet, warum `webapp-central.de` nicht als vollstaendige WordPress-Instanz in Git abgebildet wird.

## Metadaten

- Dokumenttyp: Canonical Doc
- Funktion: Referenztext
- Themenfeld: Infrastrukturprinzipien
- Reihenkontext: Infrastruktur & Betrieb
- Status: Canonical Basis
- Langfristige Relevanz: hoch
- Featured-Eignung: ja
- Empfohlene Position innerhalb von `/dokumentationen/`: Referenztext im Bereich `Infrastruktur & Betrieb`

## Zweck

Dieses Dokument beschreibt die steuerbare Projektquelle von `webapp-central.de` und erklaert die Abgrenzung zwischen versionierter Projektlogik und nicht versionierten Laufzeit- oder Inhaltsdaten.

## Kontext

Bei CMS-basierten Projekten besteht haeufig die Versuchung, moeglichst viel der laufenden Installation als Archiv in einem Repository abzubilden. Das fuehrt schnell zu unklaren Verantwortlichkeiten, schwer wartbaren Repositories und einer Vermischung von steuerbaren Projektbestandteilen mit Laufzeitdaten.

Fuer `webapp-central.de` wird deshalb bewusst zwischen Projektquelle und Laufzeitumgebung unterschieden.

## Kernprinzipien

- GitHub dient als kontrollierte Steuerzentrale fuer versionierbare Projektbestandteile.
- Nicht alles, was im Betrieb einer Plattform existiert, gehoert in ein Repository.
- Versioniert werden nur Bestandteile, die nachvollziehbar entwickelt, geprueft und gepflegt werden sollen.
- Laufzeitdaten, Inhalte mit Betriebscharakter und sensible Informationen bleiben ausserhalb dieser oeffentlichen Referenzlogik.
- Die Versionskontrolle soll Wartbarkeit und Klarheit erhoehen, nicht technische Vollstaendigkeit simulieren.

## Einordnung in die aktuelle Phase

Das Repository konzentriert sich auf steuerbare Projektbestandteile wie Compose-Konfiguration, Beispielumgebungen, Dokumentation, Custom-Code und Skripte. Laufzeitdaten, WordPress-Core, Uploads, Datenbankinhalte und sensible Konfigurationen gehoeren nicht zu diesem Kern.

## Strategische Ableitung

Die Entscheidung gegen ein WordPress-Archiv und fuer eine kontrollierte Projektquelle schafft Klarheit ueber Verantwortung, Pflege und Entwicklungslogik. GitHub ist damit nicht Speicherort fuer alles, sondern Referenzpunkt fuer das, was bewusst gestaltet und versioniert werden soll.

## Auswirkungen

- Das Repository bleibt fokussierter und auditierbarer.
- Technische Entwicklung kann sauberer von Betriebsdaten getrennt werden.
- Dokumentation und Custom-Code erhalten einen klaren Platz in der Projektlogik.
- Die Plattform bleibt leichter wartbar und weniger fehleranfaellig bei Strukturveraenderungen.

## Anschlussfragen

- Welche weiteren steuerbaren Bestandteile spaeter in die Versionierung aufgenommen werden sollen
- Wie Web-Dokumentation und Repo-Dokumentation kuenftig enger aufeinander verweisen
- Welche abstrahierten Infrastrukturtexte spaeter oeffentlich sinnvoll sind

## Verwandte Dokumente

- [docs/GITHUB_VERSIONIERUNG.md](C:/Users/dorth/Documents/webapp-zentrale/docs/GITHUB_VERSIONIERUNG.md)
- [docs/WORDPRESS_SETUP.md](C:/Users/dorth/Documents/webapp-zentrale/docs/WORDPRESS_SETUP.md)
- [Grundprinzipien der Plattformarchitektur](C:/Users/dorth/Documents/webapp-zentrale/docs/canonical/grundprinzipien-der-plattformarchitektur.md)
