# Linux Mint: Wichtige Bash-Befehle

Diese Übersicht sammelt wichtige Bash-Befehle für Linux Mint. Viele Befehle funktionieren auch auf anderen Linux-Distributionen.

## Grundregeln

- Die Bash unterscheidet zwischen Groß- und Kleinschreibung: `Datei.txt` und `datei.txt` sind verschiedene Dateien.
- Pfade mit Leerzeichen sollten in Anführungszeichen geschrieben werden: `cd "Mein Ordner"`.
- `.` steht für das aktuelle Verzeichnis, `..` für das übergeordnete Verzeichnis.
- Befehle mit `sudo` werden mit Administratorrechten ausgeführt. Verwende `sudo` nur, wenn du weißt, was der Befehl macht.
- Mit `man BEFEHL` öffnest du die ausführliche Hilfeseite zu einem Befehl, z. B. `man ls`.

## Hilfe und Informationen

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `man BEFEHL` | Handbuchseite eines Befehls anzeigen. | `man cp` |
| `BEFEHL --help` | Kurzhilfe zu einem Befehl anzeigen. | `mkdir --help` |
| `info BEFEHL` | Ausführlichere Dokumentation anzeigen, falls vorhanden. | `info coreutils` |
| `history` | Verlauf der eingegebenen Befehle anzeigen. | `history` |
| `clear` | Terminalfenster leeren. | `clear` |
| `date` | Aktuelles Datum und Uhrzeit anzeigen. | `date` |
| `whoami` | Aktuellen Benutzernamen anzeigen. | `whoami` |
| `hostname` | Computernamen anzeigen. | `hostname` |
| `uname -a` | Kernel- und Systeminformationen anzeigen. | `uname -a` |
| `lsb_release -a` | Informationen zur Linux-Mint-/Ubuntu-Basis anzeigen. | `lsb_release -a` |

## Navigation im Dateisystem

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `pwd` | Aktuelles Verzeichnis anzeigen. | `pwd` |
| `ls` | Dateien und Ordner anzeigen. | `ls` |
| `ls -l` | Dateien ausführlich mit Rechten, Besitzer, Größe und Datum anzeigen. | `ls -l` |
| `ls -la` | Auch versteckte Dateien anzeigen. | `ls -la` |
| `cd PFAD` | In ein Verzeichnis wechseln. | `cd Dokumente` |
| `cd ..` | Ein Verzeichnis nach oben wechseln. | `cd ..` |
| `cd ~` | In das eigene Home-Verzeichnis wechseln. | `cd ~` |
| `cd -` | Zum vorherigen Verzeichnis zurückwechseln. | `cd -` |
| `tree` | Verzeichnisbaum anzeigen, falls installiert. | `tree -L 2` |

## Dateien und Ordner erstellen, kopieren, verschieben und löschen

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `touch DATEI` | Leere Datei erstellen oder Zeitstempel aktualisieren. | `touch notizen.txt` |
| `mkdir ORDNER` | Neuen Ordner erstellen. | `mkdir Projekt` |
| `mkdir -p PFAD` | Mehrere Ordnerstufen auf einmal erstellen. | `mkdir -p Projekt/src/css` |
| `cp QUELLE ZIEL` | Datei kopieren. | `cp a.txt b.txt` |
| `cp -r ORDNER ZIEL` | Ordner rekursiv kopieren. | `cp -r Bilder Backup/` |
| `mv QUELLE ZIEL` | Datei oder Ordner verschieben oder umbenennen. | `mv alt.txt neu.txt` |
| `rm DATEI` | Datei löschen. | `rm test.txt` |
| `rm -r ORDNER` | Ordner inklusive Inhalt löschen. | `rm -r alter_ordner` |
| `rm -i DATEI` | Vor dem Löschen nachfragen. | `rm -i wichtig.txt` |
| `rmdir ORDNER` | Leeren Ordner löschen. | `rmdir leerer_ordner` |
| `ln -s ZIEL LINK` | Symbolischen Link erstellen. | `ln -s /var/www/html web` |

> Vorsicht: `rm -r` löscht dauerhaft. Prüfe Pfade vor dem Ausführen genau.

## Dateien anzeigen und bearbeiten

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `cat DATEI` | Datei komplett ausgeben. | `cat README.md` |
| `less DATEI` | Datei seitenweise anzeigen. Beenden mit `q`. | `less syslog` |
| `head DATEI` | Erste Zeilen einer Datei anzeigen. | `head -n 20 datei.txt` |
| `tail DATEI` | Letzte Zeilen einer Datei anzeigen. | `tail -n 20 datei.txt` |
| `tail -f DATEI` | Datei live verfolgen, z. B. Logdateien. | `tail -f /var/log/syslog` |
| `nano DATEI` | Einsteigerfreundlichen Editor öffnen. | `nano notizen.txt` |
| `vim DATEI` | Mächtigen Terminal-Editor öffnen. | `vim config.txt` |
| `wc DATEI` | Zeilen, Wörter und Zeichen zählen. | `wc -l datei.txt` |
| `file DATEI` | Dateityp erkennen. | `file bild.png` |

## Suchen und Filtern

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `find PFAD -name MUSTER` | Dateien nach Namen suchen. | `find . -name "*.txt"` |
| `find PFAD -type f` | Nur Dateien suchen. | `find . -type f` |
| `find PFAD -type d` | Nur Ordner suchen. | `find . -type d` |
| `grep MUSTER DATEI` | Text in einer Datei suchen. | `grep "Fehler" log.txt` |
| `grep -r MUSTER PFAD` | Text rekursiv in Ordnern suchen. | `grep -r "TODO" .` |
| `grep -i MUSTER DATEI` | Suche ohne Beachtung von Groß-/Kleinschreibung. | `grep -i "linux" text.txt` |
| `sort DATEI` | Zeilen sortieren. | `sort namen.txt` |
| `uniq DATEI` | Doppelte benachbarte Zeilen entfernen. | `sort namen.txt | uniq` |
| `cut -d':' -f1 DATEI` | Spalten ausschneiden. | `cut -d':' -f1 /etc/passwd` |
| `awk '{print $1}' DATEI` | Text spaltenweise verarbeiten. | `awk '{print $1}' daten.txt` |
| `sed 's/alt/neu/g' DATEI` | Text ersetzen oder umformen. | `sed 's/Linux/Mint/g' text.txt` |

## Weiterleitungen und Pipes

| Zeichen/Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `>` | Ausgabe in Datei schreiben und vorhandenen Inhalt überschreiben. | `echo Hallo > test.txt` |
| `>>` | Ausgabe an Datei anhängen. | `echo Welt >> test.txt` |
| `<` | Datei als Eingabe verwenden. | `sort < namen.txt` |
| `|` | Ausgabe eines Befehls an den nächsten Befehl weitergeben. | `ls -la | less` |
| `2>` | Fehlermeldungen in Datei schreiben. | `find /root 2> fehler.txt` |
| `&>` | Standardausgabe und Fehlerausgabe in Datei schreiben. | `befehl &> ausgabe.txt` |
| `tee DATEI` | Ausgabe anzeigen und gleichzeitig in Datei speichern. | `ls | tee liste.txt` |

## Rechte, Besitzer und Ausführung

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `chmod +x DATEI` | Datei ausführbar machen. | `chmod +x script.sh` |
| `chmod 644 DATEI` | Typische Rechte für normale Dateien setzen. | `chmod 644 index.html` |
| `chmod 755 DATEI_ODER_ORDNER` | Typische Rechte für ausführbare Dateien oder Ordner setzen. | `chmod 755 script.sh` |
| `chown BENUTZER:GRUPPE DATEI` | Besitzer und Gruppe ändern. | `sudo chown max:max datei.txt` |
| `chgrp GRUPPE DATEI` | Gruppe ändern. | `sudo chgrp www-data datei.txt` |
| `sudo BEFEHL` | Befehl als Administrator ausführen. | `sudo apt update` |
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
| `sudo apt remove PAKET` | Paket entfernen, Konfigurationsdateien bleiben meist erhalten. | `sudo apt remove vlc` |
| `sudo apt purge PAKET` | Paket inklusive Konfigurationsdateien entfernen. | `sudo apt purge vlc` |
| `sudo apt autoremove` | Nicht mehr benötigte Pakete entfernen. | `sudo apt autoremove` |
| `apt search SUCHBEGRIFF` | Paket suchen. | `apt search chromium` |
| `apt show PAKET` | Paketinformationen anzeigen. | `apt show firefox` |
| `dpkg -l` | Installierte Pakete anzeigen. | `dpkg -l | less` |
| `dpkg -i DATEI.deb` | Lokales `.deb`-Paket installieren. | `sudo dpkg -i paket.deb` |

## Prozesse und Systemauslastung

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `ps aux` | Laufende Prozesse anzeigen. | `ps aux | less` |
| `top` | Prozesse und Systemlast live anzeigen. | `top` |
| `htop` | Komfortablere Prozessanzeige, falls installiert. | `htop` |
| `pgrep NAME` | Prozess-ID nach Namen suchen. | `pgrep firefox` |
| `kill PID` | Prozess beenden. | `kill 1234` |
| `kill -9 PID` | Prozess hart beenden, wenn normales Beenden nicht funktioniert. | `kill -9 1234` |
| `pkill NAME` | Prozesse anhand des Namens beenden. | `pkill firefox` |
| `jobs` | Hintergrundjobs der aktuellen Shell anzeigen. | `jobs` |
| `bg` | Angehaltenen Job im Hintergrund fortsetzen. | `bg %1` |
| `fg` | Hintergrundjob in den Vordergrund holen. | `fg %1` |
| `BEFEHL &` | Befehl im Hintergrund starten. | `sleep 60 &` |
| `nohup BEFEHL &` | Befehl weiterlaufen lassen, auch wenn das Terminal geschlossen wird. | `nohup backup.sh &` |

## Speicherplatz, Datenträger und Hardware

| Befehl | Erklärung | Beispiel |
| --- | --- | --- |
| `df -h` | Freien und belegten Speicherplatz anzeigen. | `df -h` |
| `du -sh PFAD` | Größe eines Ordners oder einer Datei anzeigen. | `du -sh Downloads` |
| `du -h --max-depth=1` | Größen der Unterordner anzeigen. | `du -h --max-depth=1 ~` |
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
| `ping HOST` | Verbindung zu einem Host testen. | `ping linuxmint.com` |
| `traceroute HOST` | Netzwerkweg zu einem Host anzeigen, falls installiert. | `traceroute linuxmint.com` |
| `ss -tulpen` | Offene Ports und Dienste anzeigen. | `sudo ss -tulpen` |
| `curl URL` | Daten von einer URL abrufen. | `curl https://example.com` |
| `wget URL` | Datei aus dem Internet herunterladen. | `wget https://example.com/datei.zip` |
| `nmcli device status` | Netzwerkgeräte mit NetworkManager anzeigen. | `nmcli device status` |
| `nmcli connection show` | Gespeicherte Netzwerkverbindungen anzeigen. | `nmcli connection show` |
| `hostname -I` | Lokale IP-Adressen kurz anzeigen. | `hostname -I` |

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
| `while ... do ... done` | Schleife solange Bedingung wahr ist. | `while true; do date; sleep 1; done` |
| `$?` | Rückgabecode des letzten Befehls anzeigen. | `echo $?` |
| `set -e` | Skript bei Fehler abbrechen. | `set -e` |

## Nützliche Tastenkombinationen in der Bash

| Tastenkombination | Erklärung |
| --- | --- |
| `Strg + C` | Laufenden Befehl abbrechen. |
| `Strg + D` | Eingabe beenden oder Terminal-Sitzung schließen. |
| `Strg + L` | Bildschirm leeren, ähnlich wie `clear`. |
| `Strg + A` | Zum Anfang der Zeile springen. |
| `Strg + E` | Zum Ende der Zeile springen. |
| `Strg + U` | Text links vom Cursor löschen. |
| `Strg + K` | Text rechts vom Cursor löschen. |
| `Strg + R` | Rückwärtssuche im Befehlsverlauf starten. |
| `Tab` | Befehle, Dateien und Ordner automatisch vervollständigen. |
| `Pfeil hoch/runter` | Durch die Befehlshistorie blättern. |

## Häufige praktische Beispiele

```bash
# Einen neuen Projektordner erstellen und hineingehen
mkdir -p ~/Projekte/mein-projekt
cd ~/Projekte/mein-projekt

# Eine Datei erstellen und Inhalt einfügen
echo "Hallo Linux Mint" > hallo.txt
cat hallo.txt

# Alle Textdateien im aktuellen Ordner finden
find . -name "*.txt"

# In Logdateien nach Fehlern suchen
grep -i "error" /var/log/syslog

# Speicherfresser im Home-Verzeichnis finden
du -h --max-depth=1 ~ | sort -h

# System aktualisieren
sudo apt update
sudo apt upgrade
```

## Sicherheits-Tipps

- Führe keine unbekannten Befehle aus dem Internet aus, besonders nicht mit `sudo`.
- Prüfe vor dem Löschen mit `rm -r`, ob der Pfad wirklich stimmt.
- Erstelle regelmäßig Backups wichtiger Dateien.
- Verwende Anführungszeichen um Variablen in Skripten: `"$datei"`.
- Teste gefährliche Befehle zuerst mit `echo`, z. B. `echo rm *.tmp`.
