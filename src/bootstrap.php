<?php
declare(strict_types=1);

if (!function_exists('app_root')) {
    function app_root(): string
    {
        return dirname(__DIR__);
    }
}

if (!function_exists('app_is_local')) {
    function app_is_local(): bool
    {
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?? '';
        $remoteAddr = (string)($_SERVER['REMOTE_ADDR'] ?? '');

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
    }
}

if (!function_exists('app_h')) {
    function app_h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app_env')) {
    function app_env(string $name, string $default = ''): string
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }
}

if (!function_exists('app_normalize_umlauts')) {
    function app_normalize_umlauts(string $value): string
    {
        return strtr($value, [
            'Ae' => 'Ä',
            'Oe' => 'Ö',
            'Ue' => 'Ü',
            'ae' => 'ä',
            'oe' => 'ö',
            'ue' => 'ü',
            'fuer' => 'für',
        ]);
    }
}

if (!function_exists('app_site_title')) {
    function app_site_title(): string
    {
        return app_env('APP_SITE_TITLE', 'Webapp Central');
    }
}

if (!function_exists('app_site_name')) {
    function app_site_name(): string
    {
        return app_env('APP_SITE_NAME', 'webapp-central.de');
    }
}

if (!function_exists('app_legal_name')) {
    function app_legal_name(): string
    {
        return app_env('APP_LEGAL_NAME', 'Mark Dorth');
    }
}

if (!function_exists('app_legal_address_street')) {
    function app_legal_address_street(): string
    {
        return app_env('APP_LEGAL_ADDRESS_STREET', '');
    }
}

if (!function_exists('app_legal_address_postal')) {
    function app_legal_address_postal(): string
    {
        return app_env('APP_LEGAL_ADDRESS_POSTAL', '');
    }
}

if (!function_exists('app_legal_address_city')) {
    function app_legal_address_city(): string
    {
        return app_env('APP_LEGAL_ADDRESS_CITY', '');
    }
}

if (!function_exists('app_legal_country')) {
    function app_legal_country(): string
    {
        return app_env('APP_LEGAL_COUNTRY', 'Deutschland');
    }
}

if (!function_exists('app_legal_email')) {
    function app_legal_email(): string
    {
        return app_env('APP_LEGAL_EMAIL', '');
    }
}

if (!function_exists('app_legal_phone')) {
    function app_legal_phone(): string
    {
        return app_env('APP_LEGAL_PHONE', '');
    }
}

if (!function_exists('app_legal_vat_id')) {
    function app_legal_vat_id(): string
    {
        return app_env('APP_LEGAL_VAT_ID', '');
    }
}

if (!function_exists('app_legal_responsible_person')) {
    function app_legal_responsible_person(): string
    {
        return app_env('APP_LEGAL_RESPONSIBLE_PERSON', app_legal_name());
    }
}

if (!function_exists('app_legal_dispute_notice')) {
    function app_legal_dispute_notice(): string
    {
        return app_env('APP_LEGAL_DISPUTE_NOTICE', '');
    }
}

if (!function_exists('app_legal_privacy_email')) {
    function app_legal_privacy_email(): string
    {
        return app_env('APP_LEGAL_PRIVACY_EMAIL', app_legal_email());
    }
}

if (!function_exists('app_tagline')) {
    function app_tagline(): string
    {
        return app_normalize_umlauts(
            app_env('APP_TAGLINE', 'Zentrale Arbeitsfläche für Webprojekte, Inhalte und Entwicklung.')
        );
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_legal_address_lines')) {
    function app_legal_address_lines(): array
    {
        $lines = [];
        $street = trim(app_legal_address_street());
        $postal = trim(app_legal_address_postal());
        $city = trim(app_legal_address_city());
        $country = trim(app_legal_country());

        if ($street !== '') {
            $lines[] = $street;
        }

        $postalCity = trim($postal . ' ' . $city);
        if ($postalCity !== '') {
            $lines[] = $postalCity;
        }

        if ($country !== '') {
            $lines[] = $country;
        }

        return $lines;
    }
}

if (!function_exists('app_legal_address_inline')) {
    function app_legal_address_inline(): string
    {
        return implode(', ', app_legal_address_lines());
    }
}

if (!function_exists('app_legal_contact_channels')) {
    function app_legal_contact_channels(): array
    {
        $channels = [];
        $email = trim(app_legal_email());
        $phone = trim(app_legal_phone());

        if ($email !== '') {
            $channels[] = [
                'label' => 'E-Mail',
                'value' => $email,
                'href' => 'mailto:' . $email,
            ];
        }

        if ($phone !== '') {
            $channels[] = [
                'label' => 'Telefon',
                'value' => $phone,
                'href' => 'tel:' . preg_replace('/[^0-9+]/', '', $phone),
            ];
        }

        return $channels;
    }
}

if (!function_exists('app_legal_missing_fields')) {
    function app_legal_missing_fields(): array
    {
        $missing = [];

        if (trim(app_legal_name()) === '') {
            $missing[] = 'Name des Diensteanbieters';
        }

        if (trim(app_legal_address_street()) === '' || trim(app_legal_address_postal()) === '' || trim(app_legal_address_city()) === '') {
            $missing[] = 'vollstaendige ladungsfaehige Anschrift';
        }

        if (trim(app_legal_email()) === '') {
            $missing[] = 'E-Mail-Adresse fuer die schnelle Kontaktaufnahme';
        }

        return $missing;
    }
}

if (!function_exists('app_asset_url')) {
    function app_asset_url(string $path): string
    {
        $normalized = ltrim($path, '/');
        $fullPath = app_root() . '/public/' . $normalized;
        $url = app_url($normalized);

        if (!is_file($fullPath)) {
            return $url;
        }

        return $url . '?v=' . (string)(filemtime($fullPath) ?: time());
    }
}

if (!function_exists('app_calendar_storage_file')) {
    function app_calendar_storage_file(): string
    {
        return app_env('APP_CALENDAR_STORAGE_FILE', app_root() . '/var/runtime/calendar-events.json');
    }
}

if (!function_exists('app_pages')) {
    function app_pages(): array
    {
        return [
            [
                'file' => 'index.php',
                'label' => 'Startseite',
                'title' => app_site_title(),
                'eyebrow' => 'Startseite',
                'description' => 'Übersicht, Prioritäten und direkte Einstiege in die zentrale Webfläche.',
            ],
            [
                'file' => 'workspace.php',
                'label' => 'Zentrale',
                'title' => 'Zentrale',
                'eyebrow' => 'Struktur',
                'description' => 'Technische Basis, Arbeitslogik und der direkte Weg vom Repository auf den Server.',
            ],
            [
                'file' => 'modules.php',
                'label' => 'Module',
                'title' => 'Module',
                'eyebrow' => 'Bausteine',
                'description' => 'Funktionsbereiche, die als nächste Ausbaustufen bereitstehen.',
                'nav' => false,
                'nav_parent' => 'workspace.php',
            ],
            [
                'file' => 'calendar.php',
                'label' => 'Kalender',
                'title' => 'Kalender',
                'eyebrow' => 'Termine',
                'description' => 'Kalenderansicht mit Spracheingabe für neue Termine direkt im Browser.',
            ],
            [
                'file' => 'calendar-overview.php',
                'label' => 'Uebersicht',
                'title' => 'Terminuebersicht',
                'eyebrow' => 'Termine',
                'description' => 'Eigene Uebersichtsseite fuer anstehende Termine, Suche, Druck und PDF-Export.',
                'nav' => false,
                'nav_parent' => 'calendar.php',
            ],
            [
                'file' => 'hallenberg.php',
                'label' => 'Hallenberg',
                'title' => 'Hallenberg',
                'eyebrow' => 'Referenzprojekt',
                'description' => 'Premium-Projektseite fuer das Photovoltaik- und Modernisierungsprojekt Hallenberg.',
                'nav' => false,
                'nav_parent' => 'workspace.php',
            ],
        ];
    }
}

if (!function_exists('app_nav_pages')) {
    function app_nav_pages(): array
    {
        return array_values(array_filter(app_pages(), static function (array $page): bool {
            return (bool)($page['nav'] ?? true);
        }));
    }
}

if (!function_exists('app_page_map')) {
    function app_page_map(): array
    {
        $map = [];

        foreach (app_pages() as $page) {
            $map[$page['file']] = $page;
        }

        return $map;
    }
}

if (!function_exists('app_current_page')) {
    function app_current_page(): string
    {
        return basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    }
}

if (!function_exists('app_current_page_meta')) {
    function app_current_page_meta(): array
    {
        $pages = app_page_map();
        $currentPage = app_current_page();

        return $pages[$currentPage] ?? $pages['index.php'];
    }
}

if (!function_exists('app_highlights')) {
    function app_highlights(): array
    {
        return [
            [
                'label' => 'Basis',
                'title' => 'Sauberer Ausgangspunkt',
                'text' => 'PHP-Struktur und Repo-Basis sind so reduziert, dass die App direkt und kontrolliert auf dem Server weiterentwickelt werden kann.',
            ],
            [
                'label' => 'Aufgabe',
                'title' => 'Zentrale statt Altprojekt',
                'text' => 'Die Oberfläche ist nicht mehr an den alten Planer gebunden und kann als eigenständige Marke für den künftigen Live-Auftritt weitergebaut werden.',
            ],
            [
                'label' => 'Fokus',
                'title' => 'Module schrittweise erweitern',
                'text' => 'Navigation, Inhaltsbereiche und visuelle Sprache werden als wiederverwendbare Bausteine organisiert.',
            ],
        ];
    }
}

if (!function_exists('app_modules')) {
    function app_modules(): array
    {
        return [
            [
                'name' => 'Startseite',
                'status' => 'Aktiv',
                'summary' => 'Einstieg mit Prioritäten, Schnellzugriffen und Status der Arbeitsumgebung.',
                'href' => app_url('index.php'),
            ],
            [
                'name' => 'Zentrale',
                'status' => 'Aktiv',
                'summary' => 'Arbeitslogik für Repo-Struktur, Live-Stand und den direkten Weg in den Serverbetrieb.',
                'href' => app_url('workspace.php'),
            ],
            [
                'name' => 'Module',
                'status' => 'Neu',
                'summary' => 'Übersicht der Funktionsbausteine, die als nächstes ausgebaut werden können.',
                'href' => app_url('modules.php'),
            ],
            [
                'name' => 'Kalender',
                'status' => 'Neu',
                'summary' => 'Monatskalender mit lokaler Terminliste und Voice-to-Text für neue Einträge.',
                'href' => app_url('calendar.php'),
            ],
            [
                'name' => 'Hallenberg',
                'status' => 'Neu',
                'summary' => 'Premium-Projektseite fuer Photovoltaik, Montage, Medien und Smart-Energy-Architektur in Hallenberg.',
                'href' => app_url('hallenberg.php'),
            ],
        ];
    }
}

if (!function_exists('app_workspace_projects')) {
    function app_workspace_projects(): array
    {
        return [
            [
                'name' => 'Hallenberg',
                'status' => 'Live',
                'summary' => 'Premium-Projektseite fuer Photovoltaik, Montage, Medienlogik und Smart-Energy-Architektur.',
                'href' => app_url('hallenberg.php'),
                'meta' => 'Referenzprojekt',
            ],
        ];
    }
}

if (!function_exists('app_workspace_tools')) {
    function app_workspace_tools(): array
    {
        return [
            [
                'name' => 'Module',
                'status' => 'Bereich',
                'summary' => 'Funktionsbausteine, Seitenideen und nächste Ausbaustufen der Plattform.',
                'href' => app_url('modules.php'),
                'meta' => 'Werkzeuge',
            ],
            [
                'name' => 'Kalender',
                'status' => 'Live',
                'summary' => 'Terminseite mit Monats- und Wochenansicht, Suche, Druck, PDF und Bearbeiten.',
                'href' => app_url('calendar.php'),
                'meta' => 'Arbeitsbereich',
            ],
        ];
    }
}

if (!function_exists('app_workspace_steps')) {
    function app_workspace_steps(): array
    {
        return [
            'Inhalte und Oberflächen direkt im Repository bearbeiten',
            'Gemeinsame Hilfslogik in src/ ausbauen',
            'Änderungen prüfen und direkt live auf den Server übernehmen',
            'Den sichtbaren Stand auf webapp-central.de kontrollieren',
        ];
    }
}

if (!function_exists('app_project_resume_prompt')) {
    function app_project_resume_prompt(): string
    {
        return implode("\n", [
            'Wir arbeiten weiter an webapp-central.de.',
            'Aktueller Fokus ist die Kalenderseite unter /calendar.php.',
            'Der Kalender hat bereits Monats- und Wochenansicht, Terminansicht, Mapvorschau, Bearbeiten, Druck, PDF-Export und Suche.',
            'Deploy passiert aktuell direkt aus dem lokalen Repo plus SSH-Sync auf den Server.',
            'Lokales Repo: C:\\Users\\dorth\\Documents\\webapp-zentrale',
            'Live-Ziel auf dem Server: /opt/webapps/webzentrale/',
            'Bitte direkt im bestehenden Projekt weiterarbeiten und den Live-Stand nach Änderungen mitprüfen.',
        ]);
    }
}

if (!function_exists('app_project_resume_prompt_live')) {
    function app_project_resume_prompt_live(): string
    {
        $visibleModules = array_map(static function (array $page): string {
            return (string)$page['label'];
        }, app_nav_pages());

        $focusTargets = ['/calendar.php'];
        if (is_file(app_root() . '/public/calendar-overview.php')) {
            $focusTargets[] = '/calendar-overview.php';
        }

        $featureGroups = [
            'Kalenderbasis' => ['Monatsansicht', 'Wochenansicht', 'Bearbeiten', 'Mapvorschau', 'Voice-to-Text'],
            'Terminlogik' => ['Suche', 'Druck', 'PDF-Export', 'Detailansicht'],
            'Projektseiten' => ['Hallenberg-Showcase', 'Medienlogik', 'Drohnen-, Modul- und Techniksektionen'],
        ];

        $featureLines = [];
        foreach ($featureGroups as $label => $features) {
            $featureLines[] = '- ' . $label . ': ' . implode(', ', $features);
        }

        $extraLines = [];
        if (is_file(app_infrastructure_doc_path())) {
            $extraLines[] = '- Doku vorhanden: /DOKUMENTATION_INFRASTRUKTUR_ENTWICKLUNGSWEG.md';
        }

        return implode("\n", array_merge([
            'Wir arbeiten weiter an webapp-central.de.',
            'Stand dieses Projekttexts: ' . date('d.m.Y H:i') . ' Uhr.',
            'Sichtbare Hauptbereiche: ' . implode(', ', $visibleModules) . '.',
            'Aktueller Fokus: ' . implode(' und ', $focusTargets) . '.',
            'Aktueller Funktionsstand:',
        ], $featureLines, $extraLines, [
            'Deploy passiert aktuell ueber Commit/Push plus direkten SSH-Sync auf den Server.',
            'Lokales Repo: C:\\Users\\dorth\\Documents\\webapp-zentrale',
            'Live-Ziel auf dem Server: /opt/webapps/webzentrale/',
            'Bitte direkt im bestehenden Projekt weiterarbeiten und den Live-Stand nach Aenderungen mitpruefen.',
        ]));
    }
}

if (!function_exists('app_infrastructure_doc_path')) {
    function app_infrastructure_doc_path(): string
    {
        return app_root() . '/DOKUMENTATION_INFRASTRUKTUR_ENTWICKLUNGSWEG.md';
    }
}

if (!function_exists('app_infrastructure_overview_markdown')) {
    function app_infrastructure_overview_markdown(): string
    {
        $path = app_infrastructure_doc_path();
        if (!is_file($path)) {
            return "## Infrastrukturuebersicht\n\n- Dokumentation wird vorbereitet.";
        }

        $content = (string)file_get_contents($path);
        if ($content === '') {
            return "## Infrastrukturuebersicht\n\n- Dokumentation ist aktuell leer.";
        }

        $parts = preg_split('/^\s*<details>/m', $content, 2);
        $excerpt = trim((string)($parts[0] ?? ''));

        return $excerpt !== '' ? $excerpt : "## Infrastrukturuebersicht\n\n- Keine Zusammenfassung verfuegbar.";
    }
}

if (!function_exists('app_render_markdown')) {
    function app_render_markdown(string $markdown): string
    {
        $lines = preg_split("/\r\n|\n|\r/", $markdown) ?: [];
        $html = [];
        $inList = false;
        $inParagraph = false;

        $closeParagraph = static function () use (&$html, &$inParagraph): void {
            if ($inParagraph) {
                $html[] = '</p>';
                $inParagraph = false;
            }
        };

        $closeList = static function () use (&$html, &$inList): void {
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                $closeParagraph();
                $closeList();
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.*)$/', $trimmed, $matches) === 1) {
                $closeParagraph();
                $closeList();
                $level = strlen($matches[1]) + 1;
                $level = min($level, 4);
                $html[] = '<h' . $level . '>' . app_h($matches[2]) . '</h' . $level . '>';
                continue;
            }

            if (preg_match('/^-+\s+(.*)$/', $trimmed, $matches) === 1) {
                $closeParagraph();
                if (!$inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $html[] = '<li>' . app_h($matches[1]) . '</li>';
                continue;
            }

            $closeList();
            if (!$inParagraph) {
                $html[] = '<p>';
                $inParagraph = true;
            }
            $html[] = app_h($trimmed) . ' ';
        }

        $closeParagraph();
        $closeList();

        return implode('', $html);
    }
}

if (!function_exists('app_watch_signature')) {
    function app_watch_signature(): string
    {
        $entries = [];
        $targets = [app_root() . '/public', app_root() . '/src', app_root() . '/README.md'];

        foreach ($targets as $target) {
            if (is_file($target)) {
                $entries[] = basename($target) . ':' . (string)(filemtime($target) ?: 0);
                continue;
            }

            if (!is_dir($target)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                $extension = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($extension, ['php', 'css', 'md', 'js'], true)) {
                    continue;
                }

                $entries[] = str_replace('\\', '/', substr($path, strlen(app_root()) + 1))
                    . ':'
                    . (string)$file->getMTime();
            }
        }

        sort($entries);
        return sha1(implode('|', $entries));
    }
}

if (!function_exists('app_footer_links')) {
    function app_footer_links(): array
    {
        return [
            ['file' => 'impressum.php', 'label' => 'Impressum'],
            ['file' => 'datenschutz.php', 'label' => 'Datenschutz'],
            ['file' => 'kontakt.php', 'label' => 'Kontakt'],
        ];
    }
}

if (!function_exists('app_copyright_line')) {
    function app_copyright_line(): string
    {
        return sprintf('Copyright %s Mark Dorth.', date('Y'));
    }
}
