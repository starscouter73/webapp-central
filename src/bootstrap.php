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

if (!function_exists('app_tagline')) {
    function app_tagline(): string
    {
        return app_env('APP_TAGLINE', 'Zentrale Arbeitsflaeche fuer Webprojekte, Inhalte und Entwicklung.');
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
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
                'description' => 'Uebersicht, Prioritaeten und direkte Einstiege in die zentrale Webflaeche.',
            ],
            [
                'file' => 'workspace.php',
                'label' => 'Zentrale',
                'title' => 'Zentrale',
                'eyebrow' => 'Struktur',
                'description' => 'Technische Basis, Arbeitslogik und der Weg von lokalem Stand zu Serverbetrieb.',
            ],
            [
                'file' => 'modules.php',
                'label' => 'Module',
                'title' => 'Module',
                'eyebrow' => 'Bausteine',
                'description' => 'Funktionsbereiche, die als naechste Ausbaustufen bereitstehen.',
            ],
            [
                'file' => 'styleguide.php',
                'label' => 'Design',
                'title' => 'Design',
                'eyebrow' => 'Gestaltung',
                'description' => 'Farben, Typografie und Komponenten fuer den neuen Auftritt.',
            ],
        ];
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
                'text' => 'Docker, PHP-Struktur und Repo-Basis sind so reduziert, dass die App lokal und auf dem Server kontrolliert wachsen kann.',
            ],
            [
                'label' => 'Aufgabe',
                'title' => 'Zentrale statt Altprojekt',
                'text' => 'Die Oberflaeche ist nicht mehr an den alten Planer gebunden und kann als eigenstaendige Marke fuer den kuenftigen Live-Auftritt weitergebaut werden.',
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
                'summary' => 'Einstieg mit Prioritaeten, Schnellzugriffen und Status der Arbeitsumgebung.',
                'href' => app_url('index.php'),
            ],
            [
                'name' => 'Zentrale',
                'status' => 'Aktiv',
                'summary' => 'Arbeitslogik fuer Container, Repo-Struktur und den Weg in den Serverbetrieb.',
                'href' => app_url('workspace.php'),
            ],
            [
                'name' => 'Module',
                'status' => 'Neu',
                'summary' => 'Uebersicht der Funktionsbausteine, die als naechstes ausgebaut werden koennen.',
                'href' => app_url('modules.php'),
            ],
            [
                'name' => 'Design',
                'status' => 'Aktiv',
                'summary' => 'Visuelle Regeln fuer Farben, Buttons, Flaechen und Ton der neuen Marke.',
                'href' => app_url('styleguide.php'),
            ],
        ];
    }
}

if (!function_exists('app_workspace_steps')) {
    function app_workspace_steps(): array
    {
        return [
            'Container lokal mit dev-up.bat oder servernah mit docker compose starten',
            'Oberflaechen in public/ weiterentwickeln',
            'Gemeinsame Hilfslogik in src/ ausbauen',
            'Aenderungen testen, per Git sichern und fuer den Server bereitstellen',
        ];
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
