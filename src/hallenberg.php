<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

if (!function_exists('app_hallenberg_media_base')) {
    function app_hallenberg_media_base(): string
    {
        return app_url('media/hallenberg/auswahl');
    }
}

if (!function_exists('app_hallenberg_media_fallback_base')) {
    function app_hallenberg_media_fallback_base(): string
    {
        return app_url('media/hallenberg');
    }
}

if (!function_exists('app_hallenberg_media_item')) {
    function app_hallenberg_media_item(string $category, string $file, string $alt, bool $fallback = false): array
    {
        $base = $fallback ? app_hallenberg_media_fallback_base() : app_hallenberg_media_base() . '/' . $category;
        $path = str_replace('%2F', '/', rawurlencode(ltrim($file, '/')));

        return [
            'src' => $base . '/' . $path,
            'alt' => $alt,
            'category' => $category,
            'fallback' => $fallback,
        ];
    }
}

if (!function_exists('app_hallenberg_sections')) {
    function app_hallenberg_sections(): array
    {
        return [
            ['id' => 'hero', 'label' => 'Projekt'],
            ['id' => 'overview', 'label' => 'Uebersicht'],
            ['id' => 'situation', 'label' => 'Ausgangssituation'],
            ['id' => 'engineering', 'label' => 'Engineering'],
            ['id' => 'substructure', 'label' => 'Unterkonstruktion'],
            ['id' => 'modules', 'label' => 'Modulmontage'],
            ['id' => 'drone', 'label' => 'Drohnenaufnahmen'],
            ['id' => 'technical', 'label' => 'Technik & Kabelwege'],
            ['id' => 'scaffold', 'label' => 'Geruest'],
            ['id' => 'timeline', 'label' => 'Timeline'],
            ['id' => 'future', 'label' => 'Smart Energy'],
            ['id' => 'finale', 'label' => 'Fazit'],
        ];
    }
}

if (!function_exists('app_hallenberg_overview_cards')) {
    function app_hallenberg_overview_cards(): array
    {
        return [
            ['icon' => 'PV', 'title' => '23,4 kWp PV-Anlage', 'text' => '52 JA Solar Full-Black-Module bilden die Grundlage der Dachintegration und der kuenftigen Eigenstromnutzung.'],
            ['icon' => 'UK', 'title' => 'Unterkonstruktion', 'text' => 'Clenergy-Schienen, definierte Dachhakenabstaende und statisch saubere Lastverteilung fuer Wind- und Schneelastzone.'],
            ['icon' => 'LAN', 'title' => 'Netzwerkinfrastruktur', 'text' => 'LAN-Verlegung, Kellerdurchfuehrung und Technikraum wurden frueh abgestimmt, damit Monitoring und Energiemanagement spaeter sauber anschliessen.'],
            ['icon' => 'WP', 'title' => 'Waermepumpe', 'text' => 'Die Modernisierung ist bereits auf spaetere Heizungsintegration und intelligente Stromnutzung ausgelegt.'],
            ['icon' => 'WB', 'title' => 'Wallbox', 'text' => 'Eine Enpal AC Charger Gen 2 Wallbox ist Teil der Zielarchitektur fuer PV-Ueberschussladen und Mobilitaet.'],
            ['icon' => 'SE', 'title' => 'Smart Energy', 'text' => 'Fox ESS Speicher, Monitoring, Backup-Switch und spaetere Laststeuerung bilden die Basis fuer ein vernetztes Energiesystem.'],
            ['icon' => 'NA', 'title' => 'Netzanschluss', 'text' => 'Zaehlertausch, Netzfreigabe und Inbetriebnahme bleiben kritische Schritte fuer den finalen Live-Betrieb der Anlage.'],
            ['icon' => 'DOC', 'title' => 'Dokumentation', 'text' => 'Drohnenaufnahmen, Baustellenprotokolle und Engineering-Daten sichern Planung, Qualitaet und spaetere Referenzdarstellung ab.'],
        ];
    }
}

if (!function_exists('app_hallenberg_timeline')) {
    function app_hallenberg_timeline(): array
    {
        return [
            ['phase' => 'Bestand', 'text' => 'Fachwerkhaus, Dachgeometrie und Technikraum wurden vor Montage dokumentiert.'],
            ['phase' => 'Planung', 'text' => '23,4 kWp, 52 Module, Stringaufteilung, Dachhakenabstaende und Randzonen wurden technisch bewertet.'],
            ['phase' => 'Material', 'text' => 'Material und Geruest waren bereits frueh vor Ort und wurden vor Montagebeginn fotografisch festgehalten.'],
            ['phase' => 'Geruest', 'text' => 'Treppen, Podeste und Fassadenzugaenge sicherten Materiallogistik und sichere Montagehoehen.'],
            ['phase' => 'Unterkonstruktion', 'text' => 'Dachhaken, Schienen und Befestigungspunkte wurden auf Traglastebene vorbereitet.'],
            ['phase' => 'Module', 'text' => 'Die Full-Black-Module wurden zu einer ruhigeren, homogeneren Dachbelegung umgeplant und montiert.'],
            ['phase' => 'Technik', 'text' => 'Kabelweg entlang des rechten Fallrohres, Kellerdurchfuehrung und Technikbereich wurden abgestimmt.'],
            ['phase' => 'Netzanschluss', 'text' => 'Zaehlertausch, Inbetriebnahme und finale Freigaben bleiben fuer die Einspeisung relevant.'],
            ['phase' => 'Fertigstellung', 'text' => 'Zielbild ist eine architektonisch saubere, technisch belastbare und spaeter erweiterbare Energieplattform.'],
        ];
    }
}

if (!function_exists('app_hallenberg_story')) {
    function app_hallenberg_story(): array
    {
        // Hinweis:
        // Die hier verwendeten Dateinamen bilden die geplante Premium-Struktur unter
        // /public/media/hallenberg/auswahl/<kategorie>/ ab.
        // Falls einzelne reale Dateinamen noch abweichen, muessen nur diese Werte
        // angepasst werden; die Seitenlogik selbst bleibt davon unberuehrt.
        return [
            'hero' => [
                'eyebrow' => 'Premium-Projekt',
                'title' => 'Photovoltaik- & Modernisierungsprojekt Hallenberg',
                'subtitle' => 'Energetische Modernisierung eines Fachwerkhauses',
                'text' => 'Historische Bausubstanz trifft moderne Energietechnik. Die Seite dokumentiert Planung, Montage, Kabelwege und die kuenftige Smart-Energy-Architektur als Referenzprojekt fuer webapp-central.de.',
                'media' => [
                    app_hallenberg_media_item('', 'DJI_0112.webp', 'Gesamtansicht der PV-Anlage auf dem Fachwerkhaus', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.10.232.webp', 'Drohnenaufnahme von Fachwerkhaus und PV-Anlage', true),
                ],
            ],
            'situation' => [
                'text' => 'Die Dachflaechen des Fachwerkhauses stellten durch Geometrie, Schornsteinlage, Leitungsfuehrung und den versetzten Technikraum besondere Anforderungen. Vor Montagebeginn wurde der Dachzustand dokumentiert; sichtbare Schaeden waren laut Protokoll nicht erkennbar.',
                'bullets' => [
                    'historische Fachwerkstruktur mit moderner Dachintegration',
                    'technische Abstimmung fuer Kabelweg, Kellerdurchfuehrung und Technikraum',
                    'Dachzustand und Materiallagerung vor Montage fotografisch gesichert',
                    'Vorher-/Nachher-Perspektive fuer Architektur und Energietechnik',
                ],
                'media' => [
                    app_hallenberg_media_item('', 'DJI_0116.webp', 'Fachwerkhaus mit bereits sichtbarer PV-Anlage', true),
                    app_hallenberg_media_item('', 'DJI_0111.webp', 'Fachwerkhaus und Dachflaeche in der Gesamtansicht', true),
                ],
            ],
            'engineering' => [
                'text' => 'Das vorliegende MT-Briefing definiert eine 23,4 kWp Anlage mit 52 JA Solar Modulen, Fox ESS Wechselrichter, Speicher, Wallbox und Backup-Switch. Dokumentiert sind ausserdem Rand- und Mittelzonen fuer Dachhakenabstaende, Stringoptionen sowie technische Vorgaben fuer den Dachaufbau.',
                'bullets' => [
                    '52 JA Solar JAM54D41-450/LB Module',
                    'Fox ESS I-X15 Wechselrichter, B4300-X5 Batterie und Enpal Wallbox',
                    'Windzone 1, Schneezone 2a, Dachhoehe und Spannweiten aus dem Briefing',
                    'Dachhakenlogik mit differenzierten Rand- und Mittelbereichen',
                    'geaenderte Modulbelegung mit homogenerem Dachbild und geschlossener Kaminflaeche',
                ],
                'media' => [
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_15.19.658.webp', 'Dachgeometrie und Schienenraster als Grundlage der Planung', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.10.232.webp', 'Gesamtbelegung als Referenz fuer String- und Flaechenlogik', true),
                ],
            ],
            'substructure' => [
                'text' => 'Vor der Modulmontage wurde die Unterkonstruktion mit Dachhaken und Aluminiumschienen vorbereitet. Entscheidend sind dabei Dachabdichtung, Windsicherheit, Tragpunkte und die in der Planung definierten maximalen Hakenabstaende.',
                'bullets' => [
                    'Dachhaken und Clenergy-Schienensystem',
                    'Lastverteilung in Mittel- und Randzonen',
                    'Befestigungspunkte, Schienenlagen und Kreuzverbund',
                    'statische Grundlage fuer die spaetere Modulmontage',
                ],
                'media' => [
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_34.02.481.webp', 'Unterkonstruktion und Traegersystem auf dem Dach', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_14.36.179.webp', 'Montierte Schienensysteme als Grundlage der Belegung', true),
                ],
            ],
            'modules' => [
                'text' => 'Die Modulmontage ist bewusst als cinematic Galerie gedacht: Full-Black-Oberflaechen, ruhige Linien, Spiegelungen und die geschlossene Gesamtwirkung auf dem Dach. Aus den Protokollen ist zusaetzlich dokumentiert, dass eine geaenderte Modulbelegung mit homogenerer Dachflaeche angestrebt wurde.',
                'media' => [
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_02.07.484.webp', 'Full-Black-Module in Detailansicht', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_05.58.977.webp', 'Reflexionen auf den Moduloberflaechen', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_03.36.481.webp', 'Ruhige Linienfuehrung der Modulbelegung', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_06.54.302.webp', 'Modulfeld im Bereich des Schornsteins', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_38.37.133.webp', 'Rahmen- und Klemmsystem im Detail', true),
                ],
            ],
            'drone' => [
                'text' => 'Die Luftaufnahmen sind der emotionale Showcase-Bereich: Topdown-Geometrie, Symmetrie der Modulfelder, Fachwerk in der Gesamtwirkung und die Einbettung des Projekts in das Ortsbild.',
                'media' => [
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.27.280.webp', 'Topdown-Aufnahme der Dachanlage', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.10.232.webp', 'Cinematic-Drohnenaufnahme der Gesamtanlage', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_34.55.442.webp', 'Fachwerkhaus mit PV aus der Luft', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_34.57.974.webp', 'Dachgeometrie und Linienfuehrung der Anlage', true),
                ],
            ],
            'technical' => [
                'text' => 'Das Baustellenprotokoll dokumentiert die gemeinsam abgestimmte Leitungsfuehrung: aussen entlang des rechten Fallrohres, Durchfuehrung im Bereich des Kellerfensters und anschliessend durch Keller und Kellerflur zum Technik- und Zaehlerbereich. Wechselrichterposition und Monitoring-Anbindung wurden vor Ort abgestimmt.',
                'bullets' => [
                    'Kabelweg entlang des rechten Fallrohres',
                    'Mauerdurchfuehrung im Bereich Kellerfenster',
                    'Innenfuehrung bis Technik- und Zaehlerbereich',
                    'LAN-Thema, Monitoring und Smart-Meter-Anbindung frueh beruecksichtigt',
                    'Platz fuer Wechselrichter, Speicher und spaetere Steuerung',
                ],
                'media' => [
                    app_hallenberg_media_item('', 'DJI_0110.webp', 'Fassadenseite und Montagebereich fuer den abgestimmten Kabelweg', true),
                    app_hallenberg_media_item('', 'DJI_0113.webp', 'Geruest- und Fassadenansicht im Bereich der technischen Leitungsfuehrung', true),
                ],
            ],
            'scaffold' => [
                'text' => 'Geruest und Zugangstechnik waren nicht nur Hilfsmittel, sondern integraler Teil der Bauabwicklung. Treppen, Podeste und Materialwege zeigen den Umfang des Projekts und die Vorbereitung auf sichere Dach- und Fassadenarbeit.',
                'media' => [
                    app_hallenberg_media_item('', 'DJI_0115.webp', 'Geruestzugang mit Treppen und Podesten', true),
                    app_hallenberg_media_item('', 'DJI_0111.webp', 'Fachwerkhaus mit Geruest und PV-Anlage', true),
                ],
            ],
            'timeline' => [
                'media' => [
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_15.19.658.webp', 'Fruehe Projektphase mit vorbereiteter Unterkonstruktion', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_34.28.591.webp', 'Montagephase der PV-Anlage', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.27.280.webp', 'Nahezu fertig integrierte Gesamtanlage', true),
                ],
            ],
            'future' => [
                'text' => 'Das Projekt endet nicht mit der Montage. Speicher, Wallbox, Smart Meter, Monitoring und die spaetere Waermepumpe formen zusammen ein zukunftsfaehiges Energiesystem fuer Eigenverbrauch, Lastmanagement und nachhaltige Gebaeudetechnik.',
                'bullets' => [
                    'Fox ESS Batterie als Erweiterung der Eigenverbrauchsstrategie',
                    'Wallbox fuer spaeteres PV-Ueberschussladen',
                    'Smart Meter, Monitoring und moegliche MPPT-Anpassungen',
                    'Vorbereitung auf Waermepumpe und intelligente Verbrauchersteuerung',
                ],
            ],
            'finale' => [
                'title' => 'Historische Architektur mit moderner Energieinfrastruktur',
                'text' => 'Das Projekt Hallenberg verbindet Fachwerk, Praezision in der Dachbelegung, saubere Technikwege und eine skalierbare Energiearchitektur. So entsteht nicht nur eine Photovoltaikanlage, sondern ein hochwertiges Referenzprojekt fuer nachhaltige Gebaeudemodernisierung.',
                'media' => [
                    app_hallenberg_media_item('', 'DJI_0112.webp', 'Finale Luftaufnahme des Hallenberg-Projekts', true),
                    app_hallenberg_media_item('', '1Video Project 52026-05-09.mp4_snapshot_37.27.280.webp', 'Topdown-Finale der Hallenberg-Anlage', true),
                ],
            ],
        ];
    }
}
