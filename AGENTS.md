# AGENTS.md - Governance-Manifest fuer webapp-central.de

Status: **Initiale Governance-Basis (Architektur- und Workflowphase)**  
Zuletzt aktualisiert: **2026-05-19**

---

## 1) Zweck dieses Dokuments

Diese `AGENTS.md` ist die zentrale Verhaltens- und Workflowdefinition fuer KI-Agenten, Codex-Sitzungen und Maintainer, die in diesem Repository arbeiten.

In der aktuellen Phase ist dieses Dokument ein:

- Manifest
- Governance-Dokument
- Workflowdefinition
- Systemphilosophie
- Agentenrichtlinie

Es ist **keine** finale technische Spezifikation.

---

## 2) Plattformidentitaet

`webapp-central.de` ist **keine klassische Website**. Es ist eine **modulare digitale Projekt-, Arbeits- und Entwicklungsumgebung**.

Die Plattform ist darauf ausgelegt, Folgendes zu beherbergen und zu organisieren:

- Projekte
- Ideen
- Kreative Entwicklungsphasen
- Werkzeuge
- Dokumentationen
- Referenzsysteme
- Workflows
- Technische Verwaltungsprozesse

Zentrale Identitaetsregel:

> Die Plattform darf intern komplex sein, muss nach aussen jedoch kontrolliert, ruhig und verstaendlich wirken.

---

## 3) Verbindliche Rahmenbedingungen der aktuellen Phase

Wir befinden uns in einem **Analyse- und Strukturierungsmodus**.

### 3.1 In dieser Phase untersagt

- Keine automatischen Live-Deployments
- Keine destruktiven Aenderungen
- Keine unkontrollierten Architektur-Umbauten
- Kein Ueberschreiben produktiver Systeme
- Keine Aenderungen an sensiblen Dateien ohne ausdrueckliche Anweisung

### 3.2 In dieser Phase erforderlich

- Strukturelle Klarheit vor Geschwindigkeit priorisieren
- Entscheidungen vor breiter Implementierung dokumentieren
- Aenderungen inkrementell und reversibel halten
- Operative Stabilitaet bewahren

---

## 4) Governance-Ziele

Jede Arbeit in diesem Repository sollte Folgendes staerken:

1. Systemidentitaet
2. Workflow-Stabilitaet
3. Projektphilosophie
4. Governance-Struktur
5. Konsistenz des KI-/Agentenverhaltens
6. Langfristige Plattformarchitektur

Entscheidungsheuristik:

- **Kurzfristige Bequemlichkeit darf niemals die langfristige Wartbarkeit beschaedigen.**

---

## 5) Workflowregeln

### 5.1 Arbeitsmodus

- Standardmodus: **analysieren -> strukturieren -> dokumentieren -> minimal implementieren**
- Ad-hoc-Feature-Erweiterungen ohne Governance-Kontext vermeiden
- Kleine, pruefbare Commits mit klarem Zweck bevorzugen

### 5.2 Aenderungskontrolle

Vor groesseren Aenderungen:

1. Repository-Zustand pruefen
2. Relevante Dokumentation pruefen
3. Ziel und Umfang definieren
4. Minimalen strukturellen Umsetzungsschritt implementieren
5. Konsistenz mit dieser `AGENTS.md` verifizieren

### 5.3 Documentation-first-Prinzip

Wenn sich Verhalten, Architektur oder Prozesse aendern:

- Dokumentation nach Moeglichkeit im selben Change-Set aktualisieren
- Praezise Formulierungen breiten Aussagen vorziehen
- Governance-Sprache, wo sinnvoll, implementationsagnostisch halten

### 5.4 Entwicklungsdokumentations-Philosophie

Entwicklungsprozesse, Architekturentscheidungen, Workflowveraenderungen und bedeutende technische Entwicklungsschritte sollen, wenn sinnvoll, dokumentativ begleitet werden.

`webapp-central.de` soll nicht nur fertige Ergebnisse zeigen, sondern auch die Entstehung, Entwicklung und Evolution der Plattform nachvollziehbar machen.

Dokumentation ist Teil der Plattformidentitaet und kein nachgelagerter Zusatzprozess.

Der Bereich `/dokumentationen/` dient als zentrale Stelle fuer:

- Entwicklungsberichte
- Architekturentscheidungen
- Workflow-Notizen
- KI-/Codex-Integration
- Infrastruktur- und Deploy-Themen
- Projektfortschritte
- Lessons Learned

Dabei gilt:

- Keine vertraulichen Daten veroeffentlichen
- Keine Zugangsdaten, Serverdetails oder Secrets nennen
- Technische Inhalte verstaendlich erklaeren
- Nicht ueberladen
- Lieber klare Reihen als chaotische Einzeleintraege
- Dokumentationen sollen Fachcontent und Projektgedaechtnis zugleich sein

---

## 6) Entwicklungsphilosophie

### 6.1 Strukturelle Prinzipien

- Modularitaet vor monolithischem Wachstum
- Trennung von Verantwortlichkeiten vor bequemer Kopplung
- Explizite Grenzen vor implizitem Verhalten
- Reversible Weiterentwicklung vor irreversiblen Umbauten

### 6.2 Prinzipien der Produktgestaltung

- Progressive Offenlegung vor Funktionsueberladung
- Ruhige und kohaerente Interaktion vor visueller Unruhe
- Stabile Standards vor risikoreichen Experimenten
- Konsistente Muster vor Einmalloesungen

### 6.3 Komplexitaetsrichtlinie

- Interne Komplexitaet ist nur dann akzeptabel, wenn sie nach aussen vereinfacht wird
- Jedes neue Subsystem benoetigt eine klare Ownership- und Lifecycle-Definition

---

## 7) UX-Prinzipien auf Plattformebene

Die Plattform soll sich anfuehlen wie:

- Klar
- Intentional
- Vorhersehbar
- Nicht chaotisch

### 7.1 UX-Leitplanken

- Nicht jede Faehigkeit auf einmal sichtbar machen
- Navigation ueber Module hinweg verstaendlich halten
- Unfertige oder interne Bereiche klar von stabilen Bereichen abgrenzen
- Progressive Offenlegung und kontextuelle Vertiefung bevorzugen

### 7.2 Informationsarchitektur

- Nach Workflow und Zweck gruppieren, nicht nach Implementierungsdetail
- Modulgrenzen explizit halten
- Zukuenftige Skalierung unterstuetzen, ohne die UI sofort aufzublaehen

---

## 8) Repository-Regeln

### 8.1 Source of truth

- Dieses Repository ist die autoritative Projektquelle
- Aenderungen muessen ueber die Commit-Historie nachvollziehbar sein

### 8.2 Sichere Aenderungspraxis

- Secrets oder umgebungssensible Dateien nicht ohne ausdrueckliche Anweisung bearbeiten
- Breiten, nicht zielfuehrenden Dateiumbruch vermeiden
- Diffs fokussiert und auditierbar halten

### 8.3 Commit-Qualitaet

Commits sollen:

- Einen klaren, eng begrenzten Zweck haben
- Lesbarkeit und Wartbarkeit erhalten
- Governance-Absicht widerspiegeln, wenn Architektur oder Workflow betroffen sind

---

## 9) Richtlinie fuer KI-/Codex-Verhalten

### 9.1 Rollenverstaendnis

Agenten sind nicht nur Codegeneratoren. Sie sind governance-bewusste Implementierungsassistenten.

### 9.2 Verbindliches Verhalten

- Phasenregeln vor technischer Ausfuehrung beachten
- Stabilitaetsorientierte Vorschlaege bevorzugen
- Riskante oder irreversible Operationen explizit kennzeichnen
- Keine Annahmen ueber Deploy-Berechtigungen treffen

### 9.3 Eskalationsverhalten

Wenn eine Anfrage mit den Governance-Rahmenbedingungen kollidiert:

1. Destruktive Aktion pausieren
2. Sichere Alternative vorschlagen
3. Ausdrueckliche Bestaetigung fuer Grenzfaelle anfordern

### 9.4 Qualitaet der Ausgaben

- Strukturierte, modulare Ausgaben liefern
- Langfristige Konsistenz ueber Entscheidungen hinweg bewahren
- Architektur- und Workflow-Dokumente als Artefakte erster Ordnung behandeln

---

## 10) Deploy- und Serverregeln (aktueller Governance-Stand)

### 10.1 Standardhaltung zu Deployments

- Deployment ist **in dieser Phase standardmaessig nicht automatisch**
- Keine impliziten Release-Aktionen aus rein architektonischen Aufgaben ableiten

### 10.2 Server-Sicherheit

- Niemals annehmen, dass produktive Veraenderungen erlaubt sind
- Design- und Strukturarbeit von Runtime-Rollout-Aktionen trennen
- Fuer deploybezogene Ausfuehrung eine ausdrueckliche Release-Absicht verlangen

### 10.3 Zukuenftiger Uebergang

Vor dem Uebergang zu aktivem CI/CD muessen zuerst definiert werden:

- Release-Gates
- Rollback-Strategie
- Umgebungs-Validierungspruefungen
- Ownership- und Freigabepfad

---

## 11) Langfristige Architektur-Ausrichtung

`webapp-central.de` soll sich zu einer modularen Umgebung mit klaren Schichten entwickeln:

- Praesentationsschicht (oeffentliche Klarheit)
- Workspace-Schicht (Projektoperationen)
- Modulschicht (domainspezifische Faehigkeiten)
- Governance-/Dokumentationsschicht (Regeln, Entscheidungen, Referenzen)
- Infrastruktur-/Deployment-Schicht (kontrollierte Auslieferung)

Architekturabsicht:

- Wachstum ohne strukturelles Chaos ermoeglichen
- Parallele Modulentwicklung ohne Integritaetsverlust erlauben
- Governance und Implementierung verbunden, aber in der Verantwortung entkoppelt halten

---

## 12) Langfristige Vision

Die Plattform soll zu einer stabilen, erweiterbaren zentralen Umgebung fuer projektgetriebene digitale Arbeit werden.

Zielmerkmale:

- Modular
- Wartbar
- Governance-getrieben
- Menschlich lesbar
- KI-kooperativ
- Operativ kontrolliert

Erfolgskriterium:

> Wachsende Faehigkeit darf Klarheit nicht verringern.

---

## 13) Wie diese AGENTS.md erweitert werden soll

Beim Erweitern dieses Dokuments:

1. Abschnitte modular halten
2. Fuer neue Regeln eine konkrete Begruendung ergaenzen
3. Bestehende Rahmenbedingungen nicht ohne expliziten Migrationsabschnitt widersprechen
4. Groessere Governance-Aenderungen mit Datum und Grund kennzeichnen

Empfohlene zukuenftige Ergaenzungen:

- Decision Records im ADR-Stil
- Reifegradmodell fuer Module
- Release-Governance-Checkliste
- Matrix zur Klassifikation von Agentenaufgaben
- Rubrik zur Risikokategorisierung

---

## 14) Prioritaetsreihenfolge bei Konflikten

Wenn Anweisungen miteinander kollidieren, gilt folgende Reihenfolge:

1. Explizite Benutzeranweisung zur aktuellen Aufgabe
2. System-/Developer-Guardrails
3. Diese `AGENTS.md`
4. Lokale Implementierungsbequemlichkeit

Interpretationsregel:

- Bei Unklarheit immer den sichereren und leichter reversiblen Weg waehlen.
