# Linux Mint – Terminal- und Bash-Referenz

Diese Referenz sammelt praktische Terminal-Befehle und Bash-Grundlagen für Linux Mint und andere Ubuntu-/Debian-basierte Systeme. Sie dient im Repository **Webapp Central** als kompakte Arbeits- und Betriebsreferenz für Entwicklung, Administration, Fehlersuche und Wartung.

> Hinweis: Nicht jeder hier gezeigte Befehl ist ein eingebauter Bash-Befehl. Viele Einträge sind eigenständige Linux-Kommandozeilenprogramme, die aus einer Bash-Sitzung heraus aufgerufen werden.

## Grundregeln

- Linux unterscheidet zwischen Groß- und Kleinschreibung: `Datei.txt` und `datei.txt` sind verschiedene Dateien.
- Pfade mit Leerzeichen sollten in Anführungszeichen stehen: `cd "Mein Ordner"`.
- `.` steht für das aktuelle Verzeichnis, `..` für das übergeordnete Verzeichnis und `~` für das eigene Home-Verzeichnis.
- Befehle mit `sudo` werden mit Administratorrechten ausgeführt. Verwende `sudo` nur, wenn Zweck und Wirkung des Befehls klar sind.
- Mit `man BEFEHL` oder `BEFEHL --help` lässt sich die lokale Hilfe aufrufen.

## Hilfe und Systeminformationen

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `man BEFEHL` | Handbuchseite anzeigen. | `man cp` |
| `BEFEHL --help` | Kurzhilfe anzeigen. | `mkdir --help` |
| `info BEFEHL` | Ausführlichere GNU-Dokumentation anzeigen, falls vorhanden. | `info coreutils` |
| `history` | Verlauf der eingegebenen Befehle anzeigen. | `history` |
| `clear` | Terminalfenster leeren. | `clear` |
| `date` | Datum und Uhrzeit anzeigen. | `date` |
| `whoami` | Aktuellen Benutzernamen anzeigen. | `whoami` |
| `hostname` | Computernamen anzeigen. | `hostname` |
| `uname -a` | Kernel- und Systeminformationen anzeigen. | `uname -a` |
| `lsb_release -a` | Informationen zur Distribution anzeigen. | `lsb_release -a` |

## Navigation im Dateisystem

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `pwd` | Aktuelles Verzeichnis anzeigen. | `pwd` |
| `ls` | Dateien und Ordner anzeigen. | `ls` |
| `ls -l` | Ausführliche Dateiliste anzeigen. | `ls -l` |
| `ls -la` | Auch versteckte Dateien anzeigen. | `ls -la` |
| `cd PFAD` | In ein Verzeichnis wechseln. | `cd Dokumente` |
| `cd ..` | Ein Verzeichnis nach oben wechseln. | `cd ..` |
| `cd ~` | In das Home-Verzeichnis wechseln. | `cd ~` |
| `cd -` | Zum vorherigen Verzeichnis zurückwechseln. | `cd -` |
| `tree` | Verzeichnisbaum anzeigen, falls installiert. | `tree -L 2` |

## Dateien und Ordner verwalten

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `touch DATEI` | Leere Datei erstellen oder Zeitstempel aktualisieren. | `touch notizen.txt` |
| `mkdir ORDNER` | Ordner erstellen. | `mkdir Projekt` |
| `mkdir -p PFAD` | Mehrere Verzeichnisebenen erstellen. | `mkdir -p Projekt/src/css` |
| `cp QUELLE ZIEL` | Datei kopieren. | `cp a.txt b.txt` |
| `cp -r ORDNER ZIEL` | Ordner rekursiv kopieren. | `cp -r Bilder Backup/` |
| `mv QUELLE ZIEL` | Datei oder Ordner verschieben oder umbenennen. | `mv alt.txt neu.txt` |
| `rm DATEI` | Datei löschen. | `rm test.txt` |
| `rm -r ORDNER` | Ordner inklusive Inhalt löschen. | `rm -r alter_ordner` |
| `rm -i DATEI` | Vor dem Löschen nachfragen. | `rm -i wichtig.txt` |
| `rmdir ORDNER` | Leeren Ordner löschen. | `rmdir leerer_ordner` |
| `ln -s ZIEL LINK` | Symbolischen Link erstellen. | `ln -s /var/www/html web` |

> Vorsicht: `rm` verschiebt Dateien nicht in den Papierkorb. Besonders `rm -r` und Befehle mit `sudo rm` vor dem Ausführen genau prüfen.

## Dateien anzeigen und bearbeiten

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `cat DATEI` | Datei komplett ausgeben. | `cat README.md` |
| `less DATEI` | Datei seitenweise anzeigen; beenden mit `q`. | `less README.md` |
| `head DATEI` | Erste Zeilen anzeigen. | `head -n 20 datei.txt` |
| `tail DATEI` | Letzte Zeilen anzeigen. | `tail -n 20 datei.txt` |
| `tail -f DATEI` | Datei live verfolgen, z. B. ein Log. | `tail -f /var/log/syslog` |
| `nano DATEI` | Einsteigerfreundlichen Editor öffnen. | `nano notizen.txt` |
| `vim DATEI` | Terminal-Editor öffnen. | `vim config.txt` |
| `wc DATEI` | Zeilen, Wörter oder Zeichen zählen. | `wc -l datei.txt` |
| `file DATEI` | Dateityp erkennen. | `file bild.png` |

## Suchen und Filtern

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `find PFAD -name MUSTER` | Dateien nach Namen suchen. | `find . -name "*.txt"` |
| `find PFAD -type f` | Nur Dateien suchen. | `find . -type f` |
| `find PFAD -type d` | Nur Ordner suchen. | `find . -type d` |
| `grep MUSTER DATEI` | Text in einer Datei suchen. | `grep "Fehler" log.txt` |
| `grep -r MUSTER PFAD` | Text rekursiv suchen. | `grep -r "TODO" .` |
| `grep -i MUSTER DATEI` | Ohne Beachtung von Groß-/Kleinschreibung suchen. | `grep -i "linux" text.txt` |
| `sort DATEI` | Zeilen sortieren. | `sort namen.txt` |
| `uniq DATEI` | Benachbarte doppelte Zeilen zusammenfassen. | `sort namen.txt | uniq` |
| `cut -d':' -f1 DATEI` | Spalten ausschneiden. | `cut -d':' -f1 /etc/passwd` |
| `awk '{print $1}' DATEI` | Text spaltenweise verarbeiten. | `awk '{print $1}' daten.txt` |
| `sed 's/alt/neu/g' DATEI` | Text ersetzen oder umformen. | `sed 's/Linux/Mint/g' text.txt` |

## Weiterleitungen und Pipes

| Zeichen/Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `>` | Ausgabe in Datei schreiben und vorhandenen Inhalt überschreiben. | `echo Hallo > test.txt` |
| `>>` | Ausgabe an Datei anhängen. | `echo Welt >> test.txt` |
| `<` | Datei als Eingabe verwenden. | `sort < namen.txt` |
| `\|` | Ausgabe an den nächsten Befehl weitergeben. | `ls -la \| less` |
| `2>` | Fehlermeldungen in Datei schreiben. | `find /root 2> fehler.txt` |
| `&>` | Standard- und Fehlerausgabe gemeinsam umleiten. | `befehl &> ausgabe.txt` |
| `tee DATEI` | Ausgabe anzeigen und gleichzeitig speichern. | `ls | tee liste.txt` |

## Rechte, Besitzer und Ausführung

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `chmod +x DATEI` | Datei ausführbar machen. | `chmod +x script.sh` |
| `chmod 644 DATEI` | Typische Rechte für normale Dateien setzen. | `chmod 644 index.html` |
| `chmod 755 DATEI_ODER_ORDNER` | Typische Rechte für ausführbare Dateien oder Verzeichnisse setzen. | `chmod 755 script.sh` |
| `chown BENUTZER:GRUPPE DATEI` | Besitzer und Gruppe ändern. | `sudo chown max:max datei.txt` |
| `chgrp GRUPPE DATEI` | Gruppe ändern. | `sudo chgrp www-data datei.txt` |
| `sudo BEFEHL` | Befehl mit Administratorrechten ausführen. | `sudo apt update` |
| `su - BENUTZER` | Benutzer wechseln. | `su - max` |
| `id` | Benutzer-ID und Gruppenzugehörigkeiten anzeigen. | `id` |
| `groups` | Gruppen des aktuellen Benutzers anzeigen. | `groups` |

## Pakete unter Linux Mint verwalten

Linux Mint basiert auf Ubuntu/Debian und verwendet `apt` zur Paketverwaltung.

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `sudo apt update` | Paketlisten aktualisieren. | `sudo apt update` |
| `sudo apt upgrade` | Installierte Pakete aktualisieren. | `sudo apt upgrade` |
| `sudo apt install PAKET` | Paket installieren. | `sudo apt install git` |
| `sudo apt install ./DATEI.deb` | Lokales `.deb` inklusive Abhängigkeiten installieren. | `sudo apt install ./paket.deb` |
| `sudo apt remove PAKET` | Paket entfernen; Konfigurationsdateien bleiben meist erhalten. | `sudo apt remove vlc` |
| `sudo apt purge PAKET` | Paket inklusive Konfigurationsdateien entfernen. | `sudo apt purge vlc` |
| `sudo apt autoremove` | Nicht mehr benötigte Pakete entfernen. | `sudo apt autoremove` |
| `apt search SUCHBEGRIFF` | Paket suchen. | `apt search chromium` |
| `apt show PAKET` | Paketinformationen anzeigen. | `apt show firefox` |
| `dpkg -l` | Installierte Pakete anzeigen. | `dpkg -l | less` |

`dpkg -i DATEI.deb` funktioniert ebenfalls, löst fehlende Abhängigkeiten aber nicht so komfortabel wie `apt install ./DATEI.deb` auf.

## Prozesse und Systemauslastung

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `ps aux` | Laufende Prozesse anzeigen. | `ps aux | less` |
| `top` | Prozesse und Systemlast live anzeigen. | `top` |
| `htop` | Komfortablere Prozessanzeige, falls installiert. | `htop` |
| `pgrep NAME` | Prozess-ID nach Namen suchen. | `pgrep firefox` |
| `kill PID` | Prozess regulär beenden. | `kill 1234` |
| `kill -9 PID` | Prozess sofort beenden; nur einsetzen, wenn reguläres Beenden nicht funktioniert. | `kill -9 1234` |
| `pkill NAME` | Prozesse nach Namen beenden. | `pkill firefox` |
| `jobs` | Hintergrundjobs der aktuellen Shell anzeigen. | `jobs` |
| `bg` | Angehaltenen Job im Hintergrund fortsetzen. | `bg %1` |
| `fg` | Hintergrundjob in den Vordergrund holen. | `fg %1` |
| `BEFEHL &` | Befehl im Hintergrund starten. | `sleep 60 &` |
| `nohup BEFEHL &` | Befehl nach Schließen des Terminals weiterlaufen lassen. | `nohup ./backup.sh &` |

## Speicherplatz, Datenträger und Hardware

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `df -h` | Freien und belegten Speicherplatz anzeigen. | `df -h` |
| `du -sh PFAD` | Größe eines Ordners oder einer Datei anzeigen. | `du -sh Downloads` |
| `du -h --max-depth=1` | Größen direkter Unterordner anzeigen. | `du -h --max-depth=1 ~` |
| `lsblk` | Datenträger und Partitionen anzeigen. | `lsblk` |
| `blkid` | UUIDs und Dateisysteme anzeigen. | `sudo blkid` |
| `mount` | Eingehängte Dateisysteme anzeigen. | `mount` |
| `mount GERÄT ZIEL` | Datenträger einhängen. | `sudo mount /dev/sdb1 /mnt` |
| `umount ZIEL` | Datenträger aushängen. | `sudo umount /mnt` |
| `free -h` | Arbeitsspeicher anzeigen. | `free -h` |
| `lscpu` | CPU-Informationen anzeigen. | `lscpu` |
| `lsusb` | USB-Geräte anzeigen. | `lsusb` |
| `lspci` | PCI-Geräte anzeigen. | `lspci` |

## Netzwerk

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `ip addr` | IP-Adressen anzeigen. | `ip addr` |
| `ip route` | Routing-Tabelle anzeigen. | `ip route` |
| `ping HOST` | Erreichbarkeit eines Hosts testen. | `ping linuxmint.com` |
| `traceroute HOST` | Netzwerkweg anzeigen, falls installiert. | `traceroute linuxmint.com` |
| `ss -tulpen` | Offene TCP-/UDP-Ports und Prozesse anzeigen. | `sudo ss -tulpen` |
| `curl URL` | Daten oder HTTP-Antworten abrufen. | `curl https://example.com` |
| `curl -I URL` | Nur HTTP-Header abrufen. | `curl -I https://webapp-central.de` |
| `wget URL` | Datei herunterladen. | `wget https://example.com/datei.zip` |
| `nmcli device status` | Netzwerkgeräte mit NetworkManager anzeigen. | `nmcli device status` |
| `nmcli connection show` | Gespeicherte Netzwerkverbindungen anzeigen. | `nmcli connection show` |
| `hostname -I` | Lokale IP-Adressen kurz anzeigen. | `hostname -I` |

## Logs und Dienste mit systemd

Viele aktuelle Linux-Mint-/Ubuntu-Systeme verwenden `systemd` und das Journal für Dienste und Logs.

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `systemctl status DIENST` | Status eines Dienstes anzeigen. | `systemctl status ssh` |
| `sudo systemctl restart DIENST` | Dienst neu starten. | `sudo systemctl restart ssh` |
| `journalctl -b` | Logs seit dem letzten Systemstart anzeigen. | `journalctl -b` |
| `journalctl -u DIENST` | Logs eines Dienstes anzeigen. | `journalctl -u ssh` |
| `journalctl -u DIENST -f` | Dienst-Log live verfolgen. | `journalctl -u ssh -f` |
| `journalctl -p err -b` | Fehlermeldungen des aktuellen Boots anzeigen. | `journalctl -p err -b` |

## Archive und Komprimierung

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `tar -cf ARCHIV.tar ORDNER` | Tar-Archiv erstellen. | `tar -cf backup.tar Dokumente` |
| `tar -xf ARCHIV.tar` | Tar-Archiv entpacken. | `tar -xf backup.tar` |
| `tar -czf ARCHIV.tar.gz ORDNER` | Gzip-komprimiertes Tar-Archiv erstellen. | `tar -czf backup.tar.gz Dokumente` |
| `tar -xzf ARCHIV.tar.gz` | Gzip-komprimiertes Tar-Archiv entpacken. | `tar -xzf backup.tar.gz` |
| `zip -r ARCHIV.zip ORDNER` | ZIP-Archiv erstellen. | `zip -r backup.zip Dokumente` |
| `unzip ARCHIV.zip` | ZIP-Archiv entpacken. | `unzip backup.zip` |
| `gzip DATEI` | Datei mit gzip komprimieren. | `gzip log.txt` |
| `gunzip DATEI.gz` | gzip-Datei entpacken. | `gunzip log.txt.gz` |

## Git-Grundbefehle

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `git status` | Arbeitsbaum und Branch-Status anzeigen. | `git status` |
| `git diff` | Noch nicht committete Änderungen anzeigen. | `git diff` |
| `git log --oneline --decorate -n 10` | Letzte Commits kompakt anzeigen. | `git log --oneline --decorate -n 10` |
| `git branch --show-current` | Aktuellen Branch anzeigen. | `git branch --show-current` |
| `git fetch --prune` | Remote-Informationen aktualisieren und entfernte Remote-Branches bereinigen. | `git fetch --prune` |
| `git pull --ff-only` | Nur per Fast-Forward aktualisieren; verhindert unbeabsichtigte Merge-Commits. | `git pull --ff-only` |

## Bash-Skripte

| Befehl/Syntax | Erklärung | Beispiel |
| --- | --- | --- |
| `#!/usr/bin/env bash` | Shebang für Bash-Skripte. | Erste Zeile in `script.sh` |
| `bash SCRIPT` | Skript mit Bash ausführen. | `bash backup.sh` |
| `./SCRIPT` | Ausführbares Skript starten. | `./backup.sh` |
| `echo TEXT` | Text ausgeben. | `echo "Hallo"` |
| `read VARIABLE` | Eingabe einlesen. | `read name` |
| `$VARIABLE` | Variable verwenden. | `echo "$name"` |
| `export NAME=WERT` | Umgebungsvariable setzen. | `export EDITOR=nano` |
| `if ... then ... fi` | Bedingung ausführen. | `if [ -f datei.txt ]; then echo OK; fi` |
| `for ... do ... done` | Schleife über Werte ausführen. | `for f in *.txt; do echo "$f"; done` |
| `while ... do ... done` | Schleife ausführen, solange eine Bedingung wahr ist. | `while true; do date; sleep 1; done` |
| `$?` | Rückgabecode des letzten Befehls anzeigen. | `echo $?` |
| `set -e` | Skript beim ersten nicht behandelten Fehler abbrechen. | `set -e` |

Für robustere Skripte wird häufig `set -euo pipefail` verwendet. Vor dem Einsatz sollte klar sein, wie sich diese Optionen auf erwartete Fehlerfälle und nicht gesetzte Variablen auswirken.

## Nützliche Tastenkombinationen in der Bash

| Tastenkombination | Erklärung |
| --- | --- |
| `Strg + C` | Laufenden Befehl abbrechen. |
| `Strg + D` | Eingabe beenden oder Shell-Sitzung schließen. |
| `Strg + L` | Bildschirm leeren. |
| `Strg + A` | Zum Anfang der Zeile springen. |
| `Strg + E` | Zum Ende der Zeile springen. |
| `Strg + U` | Text links vom Cursor löschen. |
| `Strg + K` | Text rechts vom Cursor löschen. |
| `Strg + R` | Rückwärtssuche im Befehlsverlauf starten. |
| `Tab` | Befehle, Dateien und Ordner vervollständigen. |
| `Pfeil hoch/runter` | Durch die Befehlshistorie blättern. |

## Webapp Central – praktische Befehle

Das Repository ist die führende Projektquelle. Änderungen sollen gemäß `README.md` über GitHub/Codex gepflegt und anschließend über den vorgesehenen Deploy-Weg nach `main` live übernommen werden. Ein serverseitiges `git pull` ist für den dokumentierten Live-Deploy nicht erforderlich.

### Projekt und Website prüfen

```bash
# Repository lokal prüfen
git status
git branch --show-current
git log --oneline --decorate -n 10

# Website-Erreichbarkeit und HTTP-Header prüfen
curl -I https://webapp-central.de
```

### Server-Verzeichnis und laufende Container prüfen

Der im Projekt dokumentierte Serverpfad ist `/opt/webapps/webzentrale/`.

```bash
cd /opt/webapps/webzentrale/
pwd
ls -la

docker ps
docker compose ps
docker compose logs --tail=100
```

Falls eine Fehlersuche ausdrücklich einen Live-Log benötigt:

```bash
docker compose logs -f
```

Mit `Strg + C` wird die Live-Anzeige beendet.

### Compose-Konfiguration vor einem manuellen Eingriff prüfen

```bash
cd /opt/webapps/webzentrale/
docker compose --env-file .env config
```

Ein manueller `docker compose up -d --build` sollte nur ausgeführt werden, wenn der aktuelle Deploy-Ablauf und die Änderung das tatsächlich erfordern. Der normale Projektweg bleibt der in `README.md` beschriebene GitHub-/Deploy-Workflow.

## Häufige praktische Beispiele

```bash
# Einen neuen Projektordner erstellen und hineingehen
mkdir -p ~/Projekte/mein-projekt
cd ~/Projekte/mein-projekt

# Eine Datei erstellen und Inhalt prüfen
echo "Hallo Linux Mint" > hallo.txt
cat hallo.txt

# Alle Markdown-Dateien im aktuellen Projekt finden
find . -name "*.md"

# Nach Fehlerhinweisen in Textdateien suchen
grep -ri "error" .

# Speicherfresser im Home-Verzeichnis finden
du -h --max-depth=1 ~ | sort -h

# System aktualisieren
sudo apt update
sudo apt upgrade

# Fehler des aktuellen Systemstarts anzeigen
journalctl -p err -b
```

## Sicherheits-Tipps

- Keine unbekannten Befehle aus Chats, Foren oder Webseiten ungeprüft ausführen, besonders nicht mit `sudo`.
- Konstrukte wie `curl URL | bash`, `wget -O- URL | sh` oder Varianten mit `sudo` führen heruntergeladenen Code direkt aus. Skript zuerst herunterladen, lesen und nur aus vertrauenswürdiger Quelle ausführen.
- Vor `rm`, `rm -r`, `chown -R`, `chmod -R`, `dd`, Partitions- und Dateisystembefehlen Zielpfade und Geräte besonders sorgfältig prüfen.
- Variablen in Shell-Skripten in der Regel quoten, zum Beispiel `"$datei"`.
- Regelmäßig Backups wichtiger Daten anlegen und Wiederherstellung testen.
- Bei produktiven Diensten zuerst Status, Logs und Konfiguration prüfen, bevor ein Neustart oder Rebuild erfolgt.
