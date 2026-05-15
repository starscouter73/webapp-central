# Dokumentation Infrastruktur und Entwicklungsweg

## Kompaktuebersicht

### Entwicklungsbild in Kurzform

Dieses Projekt ist von einer lokal gepraegten Docker-Desktop-Entwicklung zu einer deutlich schlankeren repository- und servernahen Arbeitsweise uebergegangen. Die technische Richtung wurde frueh angepasst, um Ressourcen besser zu nutzen, Deployments direkter zu machen und die Infrastruktur auf spaetere Automatisierung vorzubereiten.

### Kernpunkte

- Ursprung in lokaler Containerentwicklung mit Docker Desktop, VS Code und GitHub
- Fruehe Tests in isolierten Sandbox-Stacks auf dem Entwicklungsrechner
- Zunehmende Ressourcenlast durch mehrere lokale Container- und Servicekombinationen
- Strategischer Wechsel hin zu direkter Repository-Arbeit mit schlankerem Infrastrukturpfad
- GitHub als zentrale Versionsquelle und gemeinsame Referenz fuer Aenderungen
- Deployments aktuell teilweise manuell, aber bereits auf spaetere CI/CD-Schritte ausrichtbar
- Live-nahe Arbeitsweise ueber Repository, SSH und serverseitige Bereitstellung

### Aktueller Projektstatus

**Stand:** fortlaufend zu pflegen. Dieser Abschnitt ersetzt aeltere Momentaufnahmen, die den Kalender noch als aktuellen Schwerpunkt nennen.

#### Projekt

- Domain: `webapp-central.de`
- Repository: `starscouter73/webapp-central`
- Produktivbranch: `main`
- Serverpfad: `/opt/webapps/webzentrale/`
- Lokales Repo: `C:\Users\dorth\Documents\webapp-zentrale`

#### Aktuelle Arbeitsrichtung

Der Kalender ist nicht mehr der aktuelle Hauptfokus. Er bleibt als vorhandenes Modul Bestandteil der Plattform.

Der aktuelle Fokus liegt auf:

- sauberem GitHub-/Server-/Lokal-Sync
- stabiler Repository-Struktur
- `webapp-central.de` als uebergreifende Projektzentrale
- Hallenberg-Showcase als hochwertige Referenzseite
- konsistentem Dark-/Glass-/Hallenberg-Design
- sauberem Medien- und Dokumentationsworkflow
- Vorbereitung weiterer Webapps, Module und Referenzbereiche

#### Bestehende Hauptbereiche

- Startseite
- Zentrale / Moduluebersicht
- Workspace / Arbeitsbereich
- Kalender als vorhandenes Funktionsmodul
- Hallenberg-Projektseite mit Medienlogik
- Hallenberg-Showcase mit Drohnen-, Modul-, Technik- und Dokumentationssektionen

#### Arbeitsregel

Alte Projektstatusbloecke mit konkreter Datumsangabe oder altem Fokus duerfen nicht mehr ungeprueft verwendet werden.

Vor jeder groesseren Aenderung gilt:

1. Repository-Status pruefen.
2. Serverstand pruefen.
3. Lokalen Stand pruefen, falls lokal gearbeitet wird.
4. Aenderungen sauber committen.
5. Nach Bedarf rebasen.
6. Push nach `main`.
7. Live-Seite pruefen.
8. Dokumentation aktualisieren.

#### Kein aktueller Fokus mehr

Nicht mehr als aktueller Schwerpunkt behandeln:

- reine Kalenderentwicklung
- `/calendar.php` als Hauptarbeitsziel
- `/calendar-overview.php` als Hauptarbeitsziel

Diese Bereiche nur bearbeiten, wenn ausdruecklich daran gearbeitet werden soll.

<details>
<summary>🟢 Phase 1 — Lokaler Ausgangspunkt mit Docker Desktop</summary>

Der urspruengliche Entwicklungsansatz war klar lokal orientiert. Die erste Arbeitsumgebung wurde auf dem Entwicklungsrechner aufgebaut, um schnell experimentieren, testen und technische Richtungen ohne externe Abhaengigkeiten pruefen zu koennen.

Docker Desktop spielte dabei eine zentrale Rolle. Containerisierte Dienste ermoeglichten eine saubere Trennung zwischen Projektcode, Laufzeitumgebung und Webserver-Stack. Dieses Setup war besonders in der Fruehphase sinnvoll, weil es reproduzierbare Testbedingungen schuf und Aenderungen ohne Eingriff in eine bereits laufende Live-Umgebung pruefbar machte.

Ergaenzt wurde dieser Ansatz durch VS Code als Editor- und Navigationsumgebung, GitHub als Versionsbasis und lokale Tests direkt auf dem Rechner. Die Kombination aus Codebearbeitung, Containerlaufzeit und Versionskontrolle bildete damit einen klassischen Sandbox-Workflow: lokal, isoliert und flexibel.

</details>

<details>
<summary>🟢 Phase 2 — Ressourcenlast und steigende Architekturkomplexitaet</summary>

Mit zunehmender Projektentwicklung wurde sichtbar, dass lokale Containerisierung zwar technisch sauber, langfristig aber nicht immer wirtschaftlich ist. Docker Desktop und mehrere begleitende Services verursachten einen spueren Anstieg bei RAM-, CPU- und Storage-Bedarf.

Je mehr parallele Projektzustaende, Webserver-Varianten oder Testkonstellationen gepflegt werden mussten, desto hoeher wurde die Last auf dem Entwicklungsrechner. Das betraf nicht nur die Laufzeitkosten der Container selbst, sondern auch Image-Verwaltung, Dateisynchronisation, Neustarts, Build-Zeiten und die Pflege mehrerer lokaler Stacks.

Aus Architektursicht fuehrte das zu einer doppelten Belastung: Einerseits blieb die lokale Umgebung fuer schnelle Experimente nützlich, andererseits wuchs der Aufwand, diese Umgebung dauerhaft performant und uebersichtlich zu halten. Gerade bei mehreren parallelen Projekten wurde der Rechner zunehmend zum Engpass statt nur zum Werkzeug.

</details>

<details>
<summary>🟢 Phase 3 — Strategischer Richtungswechsel</summary>

Relativ frueh im Entwicklungsverlauf wurde daher entschieden, die Strategie anzupassen. Diese Entscheidung entstand nicht aus einer singulaeren Stoerung, sondern aus dem Zusammenspiel von Projektwachstum, Infrastrukturbedarf und der Beobachtung, dass eine rein lokal an Docker Desktop gebundene Arbeitsweise auf Dauer unnoetige Reibung erzeugt.

Der Wechsel bedeutete bewusst eine Abkehr von der ausschliesslichen lokalen Containerzentrierung. Stattdessen rueckte GitHub als zentrale Versionsbasis staerker in den Mittelpunkt. Code, Repository-Struktur und technische Infrastruktur wurden enger zusammen gedacht, sodass der Entwicklungsweg nicht mehr primaer ueber lokale Laufzeitabstraktion, sondern ueber nachvollziehbare Dateien, Commits und direkte Bereitstellungswege organisiert wird.

Diese Umstellung war zugleich eine Vorbereitung auf server- und cloudorientiertes Arbeiten. Ziel war nicht, lokale Entwicklung abzuschaffen, sondern sie gezielter einzusetzen und den Hauptworkflow schlanker, direkter und naeher an der spaeteren Betriebsumgebung auszurichten.

</details>

<details>
<summary>🟢 Phase 4 — Aktuelle repository- und servernahe Arbeitsweise</summary>

Der aktuelle Workflow ist deutlich direkter aufgebaut. Entwicklung findet heute primaer entlang der Repository-Strukturen statt: Code, Styles, Skripte und Hilfslogik werden unmittelbar im Projekt gepflegt und versioniert. GitHub fungiert dabei als zentrale Projektquelle und als verbindliche Referenz fuer den laufenden Stand.

Aenderungen werden zeitnah committed und gepusht. Dadurch bleibt der Entwicklungsstand in Echtzeit nachvollziehbar und fuer kuenftige Automatisierungsschritte anschlussfaehig. Diese Arbeitsweise reduziert lokale Zusatzkomplexitaet und verschiebt den Fokus auf die eigentlichen Projektartefakte statt auf schwere Umgebungssimulation.

Deployments werden aktuell teilweise noch manuell angestossen. Gleichzeitig ist die Struktur bereits so angelegt, dass sie in einen spaeteren CI/CD-Pfad ueberfuehrt werden kann. Die Richtung ist klar: Live-Deployment, serverseitige Projektbereitstellung und eine cloudfaehige Architektur, die ohne grundlegenden Strategiewechsel weiter professionalisiert werden kann.

</details>

<details>
<summary>🟢 Phase 5 — Technische Komponenten im Zusammenspiel</summary>

Die wesentlichen technischen Komponenten lassen sich heute klar benennen:

- Docker Desktop als urspruengliche lokale Laufzeit- und Testbasis
- Docker-Container fuer isolierte Dienste und reproduzierbare Entwicklungsumgebungen
- VS Code als zentrale Bearbeitungsumgebung fuer Code, Struktur und Navigation
- GitHub als primaere Versionsquelle und gemeinsame Projektreferenz
- Codex beziehungsweise KI-gestuetzte Entwicklung als Beschleuniger fuer Umsetzung, Ueberarbeitung und Strukturarbeit
- Repository-Workflow als Rueckgrat fuer Aenderungen, Nachvollziehbarkeit und Uebergaben
- SSH als technischer Pfad fuer direkte serverseitige Bereitstellung
- Linux/vServer als Zielsystem fuer die laufende Anwendung
- Webserverstruktur als vorbereitete Basis fuer den sichtbaren Live-Betrieb
- API-Vorbereitung als spaetere Ausbaurichtung fuer persistente Logik und Integrationen
- Deployment-Idee als Uebergang von manueller Auslieferung zu stabilerer Automatisierung

Entscheidend ist nicht jedes Werkzeug fuer sich, sondern ihr Zusammenspiel. Die Architektur entwickelt sich weg von lokaler Simulationslast hin zu einer schlanken, versionierten und betriebsnahen Lieferkette.

</details>

# 📡 Aktueller Infrastrukturstatus

## Entwicklungsstrategie

✅ Fokus auf direkte Arbeit im Repository statt auf dauerhafte lokale Containerabhaengigkeit  
✅ Server- und cloudnahe Weiterentwicklung mit klarer Versionsfuehrung  
✅ Lokale Werkzeuge nur dort, wo sie echten Mehrwert fuer Umsetzung und Tests liefern

## Aktueller Workflow

✅ Codebearbeitung direkt an Projektdateien  
✅ Zeitnahe Commits und Pushes in den zentralen Versionsstand  
✅ Teilweise manuelle, aber klar strukturierte Live-Bereitstellung  
✅ Vorbereitung auf spaetere Automatisierung durch CI/CD-nahe Ablauflogik

## Vorhandene Infrastruktur

✅ Repository-basierte Projektstruktur  
✅ Serverseitige Bereitstellungsumgebung unter Linux/vServer  
✅ Webserverfaehige Anwendungsstruktur  
✅ SSH-gestuetzter Uebergang zwischen Entwicklung und Live-System

## Entwicklungswerkzeuge

✅ VS Code  
✅ GitHub  
✅ Codex / KI-gestuetzte Entwicklung  
✅ Docker Desktop als weiterhin relevantes, aber nicht mehr dominantes Werkzeug

## Technischer Reifegrad

✅ Solide Grundstruktur fuer laufende Weiterentwicklung  
✅ Live-nahe Arbeitsweise bereits etabliert  
✅ Architektur ist noch nicht vollautomatisiert, aber bewusst darauf vorbereitet  
✅ Gute Basis fuer kuenftige Persistenz-, API- und Deployment-Erweiterungen

## Naechste Schritte

✅ Weitere Reduktion manueller Deployments  
✅ Ausbau in Richtung reproduzierbarer CI/CD-Strecken  
✅ Schritthafte Professionalisierung von Serverlogik, Persistenz und Integrationsschnittstellen  
✅ Weiterentwicklung der Webapp auf einer bereits deutlich reiferen Infrastrukturgrundlage
