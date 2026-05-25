<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height' => 80,
        'width' => 320,
        'flex-width' => true,
        'flex-height' => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'webapp-central-starter'),
    ]);
});

add_action('wp_enqueue_scripts', static function (): void {
    wp_enqueue_style(
        'webapp-central-starter-style',
        get_stylesheet_uri(),
        [],
        wp_get_theme()->get('Version')
    );
});

add_action('wp_head', static function (): void {
    echo '<meta name="theme-color" content="#050505">' . PHP_EOL;
    echo '<meta name="color-scheme" content="dark">' . PHP_EOL;
    echo '<meta name="description" content="' . esc_attr__('Webapp Central ist eine digitale Steuerzentrale fuer Projekte, Dokumentation und intelligente Workflows.', 'webapp-central-starter') . '">' . PHP_EOL;
    echo '<style id="webapp-central-critical-css">html{background:#050505;color-scheme:dark}body,.site-shell{margin:0;min-height:100vh;background:#050505;color:#fff}.content-wrap,.portal-shell,.platform-home{background:transparent}</style>' . PHP_EOL;
}, 1);

add_action('widgets_init', static function (): void {
    register_sidebar([
        'name' => __('Primary Sidebar', 'webapp-central-starter'),
        'id' => 'primary-sidebar',
        'description' => __('Sidebar for project metadata and supporting widgets.', 'webapp-central-starter'),
        'before_widget' => '<section class="glass-panel">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="section-heading">',
        'after_title' => '</h2>',
    ]);
});

add_action('wp_head', static function (): void {
    if (has_site_icon()) {
        return;
    }

    $favicon = webapp_central_starter_asset_url('brand/icons/webapp-central-icon-fresh-blue-32x32.png');
    $apple = webapp_central_starter_asset_url('brand/icons/webapp-central-icon-fresh-blue-192x192.png');
    $site_icon = webapp_central_starter_asset_url('brand/icons/webapp-central-icon-fresh-blue-512x512.png');

    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($favicon) . '">' . PHP_EOL;
    echo '<link rel="apple-touch-icon" sizes="192x192" href="' . esc_url($apple) . '">' . PHP_EOL;
    echo '<meta name="msapplication-TileImage" content="' . esc_url($site_icon) . '">' . PHP_EOL;
}, 5);

function webapp_central_starter_body_classes(array $classes): array
{
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }

    if (webapp_central_starter_is_virtual_portal_request()) {
        $classes = array_values(array_diff($classes, ['error404']));
        $classes[] = 'is-virtual-portal';
    }

    return $classes;
}

add_filter('body_class', 'webapp_central_starter_body_classes');

add_filter('pre_get_document_title', static function (string $title): string {
    if (webapp_central_starter_is_virtual_portal_request()) {
        return __('Portal', 'webapp-central-starter') . ' - ' . get_bloginfo('name');
    }

    if (!is_page()) {
        return $title;
    }

    $project_slug = sanitize_key((string) ($_GET['projekt'] ?? ''));

    if (!is_page('pfarrer-matthias-genster') || $project_slug !== 'enpal-hallenberg') {
        return $title;
    }

    return __('PV-Energie · Enpal Hallenberg', 'webapp-central-starter') . ' – ' . get_bloginfo('name');
});

function webapp_central_starter_asset_url(string $relative_path): string
{
    return get_template_directory_uri() . '/assets/' . ltrim($relative_path, '/');
}

function webapp_central_starter_asset_path(string $relative_path): string
{
    return get_template_directory() . '/assets/' . ltrim($relative_path, '/');
}

function webapp_central_starter_render_header_branding(): void
{
    $logo_markup = null;

    if (has_custom_logo()) {
        $custom_logo_id = (int) get_theme_mod('custom_logo');
        $logo_alt = trim((string) get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true));

        $logo_markup = wp_get_attachment_image(
            $custom_logo_id,
            'full',
            false,
            [
                'class' => 'custom-logo',
                'alt' => $logo_alt !== '' ? $logo_alt : __('Webapp Central Header-Logo', 'webapp-central-starter'),
            ]
        );
    }

    if ($logo_markup !== null) {
        echo '<a class="site-logo" href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr__('webapp-central.de Startseite', 'webapp-central-starter') . '" title="' . esc_attr__('Zur Startseite von webapp-central.de', 'webapp-central-starter') . '">';
        echo $logo_markup;
        echo '</a>';
    }

    echo '<div class="site-branding__text">';
    echo '<p class="site-title' . ($logo_markup !== null ? ' screen-reader-text' : '') . '"><span>' . esc_html__('Webapp Central', 'webapp-central-starter') . '</span></p>';
    echo '<p class="site-kicker">' . esc_html__('Digitale Steuerzentrale', 'webapp-central-starter') . '</p>';
    echo '<p class="site-caption">' . esc_html__('Projekte, Dokumentation und intelligente Workflows in einer Plattform.', 'webapp-central-starter') . '</p>';
    echo '</div>';
}

function webapp_central_starter_render_main_brand(): void
{
    $main_logo = null;
    $relative_path = 'brand/main/webapp-central-main-fresh-blue-display.png';
    $main_logo_path = webapp_central_starter_asset_path($relative_path);
    if (file_exists($main_logo_path)) {
        $main_logo = [
            'url' => webapp_central_starter_asset_url($relative_path),
            'width' => 780,
            'height' => 234,
            'alt' => __('Webapp Central Wortmarke fuer die Projektzentrale', 'webapp-central-starter'),
        ];
    }

    if ($main_logo === null) {
        return;
    }

    echo '<figure class="hero-brandmark">';
    echo '<p class="hero-brandmark__label">' . esc_html__('Wortmarke', 'webapp-central-starter') . '</p>';
    echo '<img src="' . esc_url($main_logo['url']) . '" alt="' . esc_attr((string) $main_logo['alt']) . '" width="' . esc_attr((string) $main_logo['width']) . '" height="' . esc_attr((string) $main_logo['height']) . '">';
    echo '<figcaption class="hero-brandmark__caption">' . esc_html__('Neues frisches Branding fuer webapp-central.de ohne beschnittene Schrift', 'webapp-central-starter') . '</figcaption>';
    echo '</figure>';
}

function webapp_central_starter_render_menu(): void
{
    if (has_nav_menu('primary')) {
        wp_nav_menu([
            'theme_location' => 'primary',
            'container' => 'nav',
            'container_class' => 'site-nav',
            'menu_class' => 'menu',
            'fallback_cb' => false,
            'depth' => 1,
        ]);

        return;
    }

    echo '<nav class="site-nav" aria-label="' . esc_attr__('Primary Navigation', 'webapp-central-starter') . '"><ul>';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Start', 'webapp-central-starter') . '</a></li>';
    echo '<li><a href="' . esc_url(webapp_central_starter_portal_page_url('projekte', '/projekte/')) . '">' . esc_html__('Module', 'webapp-central-starter') . '</a></li>';
    echo '<li><a href="' . esc_url(webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/')) . '">' . esc_html__('Dokumentation', 'webapp-central-starter') . '</a></li>';
    echo '<li><a href="' . esc_url(webapp_central_starter_portal_page_url('kontakt', '/kontakt/')) . '">' . esc_html__('Kontakt', 'webapp-central-starter') . '</a></li>';
    echo '</ul></nav>';
}

function webapp_central_starter_module_cards(): array
{
    return [
        [
            'title' => __('Projektzentrale', 'webapp-central-starter'),
            'description' => __('Zentrale Einstiegsseite fuer Struktur, Navigation und redaktionelle Pflege des Projekts.', 'webapp-central-starter'),
            'url' => admin_url(),
            'label' => __('Zum Dashboard', 'webapp-central-starter'),
        ],
        [
            'title' => __('Seitenaufbau', 'webapp-central-starter'),
            'description' => __('Erste Inhalte koennen ueber Seiten, Menues und Widgets modular aufgebaut werden.', 'webapp-central-starter'),
            'url' => admin_url('edit.php?post_type=page'),
            'label' => __('Seiten verwalten', 'webapp-central-starter'),
        ],
        [
            'title' => __('Designbasis', 'webapp-central-starter'),
            'description' => __('Frischer Glass-Look mit responsiver Basis, geeignet fuer Hallenberg, Module und zentrale Projektseiten.', 'webapp-central-starter'),
            'url' => admin_url('customize.php'),
            'label' => __('Design pruefen', 'webapp-central-starter'),
        ],
    ];
}

function webapp_central_starter_portal_page_url(string $path, string $fallback = '/'): string
{
    $page = get_page_by_path($path);

    if ($page instanceof WP_Post) {
        return (string) get_permalink($page);
    }

    return home_url($fallback);
}

function webapp_central_starter_project_detail_url(string $hub_slug, string $project_slug): string
{
    $base_url = webapp_central_starter_portal_page_url('projekte/' . $hub_slug);

    return (string) add_query_arg(
        [
            'projekt' => $project_slug,
        ],
        $base_url
    );
}

function webapp_central_starter_portal_sections(): array
{
    return [
        [
            'title' => __('Portal', 'webapp-central-starter'),
            'description' => __('Oeffentliche Einstiegsseite fuer Projektstatus, Schnellzugriffe und den aktuellen Relaunch-Kontext.', 'webapp-central-starter'),
            'url' => home_url('/portal/'),
            'badge' => __('Live', 'webapp-central-starter'),
        ],
        [
            'title' => __('Projekte', 'webapp-central-starter'),
            'description' => __('Projekt-Hubs, Themenkarten und strukturierte Arbeitsbereiche.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('projekte'),
            'badge' => __('Aktiv', 'webapp-central-starter'),
        ],
        [
            'title' => __('Dokumentationen', 'webapp-central-starter'),
            'description' => __('Langfristig fuer Handbuecher, technische Wege und nachvollziehbare Ablagen.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('dokumentationen'),
            'badge' => __('Basis', 'webapp-central-starter'),
        ],
        [
            'title' => __('Medien', 'webapp-central-starter'),
            'description' => __('Platz fuer Bilder, Berichte, spaetere Galerien und visuelle Nachweise.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('medien'),
            'badge' => __('Basis', 'webapp-central-starter'),
        ],
        [
            'title' => __('Analyse', 'webapp-central-starter'),
            'description' => __('Vorbereitung fuer spaetere KI-Auswertungen, Reports und Dashboards.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('analyse'),
            'badge' => __('Roadmap', 'webapp-central-starter'),
        ],
        [
            'title' => __('System', 'webapp-central-starter'),
            'description' => __('Bereich fuer Strukturhinweise, Betriebslogik und technische Leitplanken.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('system'),
            'badge' => __('Intern', 'webapp-central-starter'),
        ],
    ];
}

function webapp_central_starter_featured_project_hubs(): array
{
    return [
        [
            'title' => __('Pfarrer Matthias Genster', 'webapp-central-starter'),
            'description' => __('Zentrale Hub-Seite fuer Betreuung, Aufenthalte, Fahrten, Infrastruktur und begleitende Dokumentation.', 'webapp-central-starter'),
            'url' => webapp_central_starter_portal_page_url('projekte/pfarrer-matthias-genster'),
            'status' => __('Aktiv', 'webapp-central-starter'),
            'activity' => __('Heute aktualisiert', 'webapp-central-starter'),
            'visual' => __('Projekt-Hub', 'webapp-central-starter'),
        ],
    ];
}

function webapp_central_starter_project_hub_cards(string $slug): array
{
    if ($slug !== 'pfarrer-matthias-genster') {
        return [];
    }

    return [
        [
            'title' => __('Enpal Hallenberg', 'webapp-central-starter'),
            'description' => __('Statusbild zur PV- und Energieumsetzung, Termine, Unterlagen und Nachverfolgung.', 'webapp-central-starter'),
            'status' => __('In Arbeit', 'webapp-central-starter'),
            'activity' => __('Letzte Aktivitaet: heute', 'webapp-central-starter'),
            'visual' => __('PV / Energie', 'webapp-central-starter'),
            'variant' => 'enpal-hallenberg',
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => webapp_central_starter_project_detail_url('pfarrer-matthias-genster', 'enpal-hallenberg'),
        ],
        [
            'title' => __('Pflege & Betreuung', 'webapp-central-starter'),
            'description' => __('Organisatorische Themen, Betreuungslinien und dokumentierte Hinweise fuer den Alltag.', 'webapp-central-starter'),
            'status' => __('Laufend', 'webapp-central-starter'),
            'activity' => __('Letzte Aktivitaet: diese Woche', 'webapp-central-starter'),
            'visual' => __('Betreuung', 'webapp-central-starter'),
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => '#pflege-betreuung',
        ],
        [
            'title' => __('Fahrten & Organisation', 'webapp-central-starter'),
            'description' => __('Fahrten, Abstimmungen, Routinen und organisatorische Uebersichten kompakt gebuendelt.', 'webapp-central-starter'),
            'status' => __('Strukturiert', 'webapp-central-starter'),
            'activity' => __('Letzte Aktivitaet: gestern', 'webapp-central-starter'),
            'visual' => __('Mobilitaet', 'webapp-central-starter'),
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => '#fahrten-organisation',
        ],
        [
            'title' => __('Infrastruktur', 'webapp-central-starter'),
            'description' => __('Technische, bauliche und betriebliche Punkte fuer eine spaetere tiefe Dokumentationsspur.', 'webapp-central-starter'),
            'status' => __('Vorbereitet', 'webapp-central-starter'),
            'activity' => __('Letzte Aktivitaet: diese Woche', 'webapp-central-starter'),
            'visual' => __('Technik', 'webapp-central-starter'),
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => '#infrastruktur',
        ],
        [
            'title' => __('PV-Dokumentation', 'webapp-central-starter'),
            'description' => __('Sammlung fuer Nachweise, technische Fakten, Fotos und spaetere Berichte zur PV-Linie.', 'webapp-central-starter'),
            'status' => __('Aufbau', 'webapp-central-starter'),
            'activity' => __('Letzte Aktivitaet: offen', 'webapp-central-starter'),
            'visual' => __('Dokumentation', 'webapp-central-starter'),
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => '#pv-dokumentation',
        ],
    ];
}

function webapp_central_starter_project_hub_activities(string $slug): array
{
    if ($slug !== 'pfarrer-matthias-genster') {
        return [];
    }

    return [
        __('Projekt-Hub angelegt und als zentrale Uebersichtsseite vorbereitet.', 'webapp-central-starter'),
        __('Erste Themenkarten fuer Betreuung, Fahrten, Infrastruktur und PV gesetzt.', 'webapp-central-starter'),
        __('Designbasis fuer spaetere Berichte, Medien und Analysen vorbereitet.', 'webapp-central-starter'),
    ];
}

function webapp_central_starter_project_detail_data(string $hub_slug, string $project_slug): ?array
{
    if ($hub_slug !== 'pfarrer-matthias-genster' || $project_slug !== 'enpal-hallenberg') {
        return null;
    }

    return [
        'title' => __('PV-Energie · Enpal Hallenberg', 'webapp-central-starter'),
        'eyebrow' => __('Projekt-Detailseite', 'webapp-central-starter'),
        'summary' => __('Uebersicht zu Montage, Kommunikation, Dokumentation und offenen Punkten rund um das PV-Projekt Hallenberg.', 'webapp-central-starter'),
        'status' => __('Aktiv', 'webapp-central-starter'),
        'updated' => __('Stand: laufende Projektdokumentation', 'webapp-central-starter'),
        'back_url' => webapp_central_starter_portal_page_url('projekte/pfarrer-matthias-genster'),
        'stats' => [
            [
                'value' => '7',
                'label' => __('Bereiche', 'webapp-central-starter'),
            ],
            [
                'value' => '42',
                'label' => __('Eintraege', 'webapp-central-starter'),
            ],
            [
                'value' => __('live', 'webapp-central-starter'),
                'label' => __('Projektstatus', 'webapp-central-starter'),
            ],
        ],
        'sections' => [
            [
                'title' => __('Projektueberblick', 'webapp-central-starter'),
                'status' => __('Basis', 'webapp-central-starter'),
                'items' => [
                    __('Photovoltaik-Projekt Hallenberg', 'webapp-central-starter'),
                    __('Enpal-Montage', 'webapp-central-starter'),
                    __('Pfarrhaus / Matthias Genster', 'webapp-central-starter'),
                    __('technische und organisatorische Begleitung', 'webapp-central-starter'),
                    __('laufende Projektdokumentation', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Montage & Baustelle', 'webapp-central-starter'),
                'status' => __('In Arbeit', 'webapp-central-starter'),
                'items' => [
                    __('Montagebeginn', 'webapp-central-starter'),
                    __('Geruest / Dachzugang', 'webapp-central-starter'),
                    __('Dachhaken', 'webapp-central-starter'),
                    __('Schienenmontage', 'webapp-central-starter'),
                    __('Modulmontage', 'webapp-central-starter'),
                    __('Wechselrichter / Technikbereich', 'webapp-central-starter'),
                    __('Stromabschaltung', 'webapp-central-starter'),
                    __('Zaehlerwechsel', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Kommunikation mit Enpal', 'webapp-central-starter'),
                'status' => __('Laufend', 'webapp-central-starter'),
                'items' => [
                    __('Vertragsfragen', 'webapp-central-starter'),
                    __('Rueckrufe', 'webapp-central-starter'),
                    __('Portal / Zugang', 'webapp-central-starter'),
                    __('Ident-Verfahren', 'webapp-central-starter'),
                    __('Unterlagen', 'webapp-central-starter'),
                    __('offene Rueckfragen', 'webapp-central-starter'),
                    __('Ansprechpartner', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Monteur-Kommunikation', 'webapp-central-starter'),
                'status' => __('Abstimmung', 'webapp-central-starter'),
                'items' => [
                    __('Deutsch-Spanisch-Uebersetzungen', 'webapp-central-starter'),
                    __('schriftliche Rueckfragen', 'webapp-central-starter'),
                    __('Formular fuer Monteure', 'webapp-central-starter'),
                    __('Fragen zur Fertigstellung', 'webapp-central-starter'),
                    __('Fragen zur Stromabschaltung', 'webapp-central-starter'),
                    __('Abstimmung mit Bewohnern/Mietern', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Dokumentation & Medien', 'webapp-central-starter'),
                'status' => __('Sammlung', 'webapp-central-starter'),
                'items' => [
                    __('Baustellenfotos', 'webapp-central-starter'),
                    __('Dachbilder', 'webapp-central-starter'),
                    __('Modulbilder', 'webapp-central-starter'),
                    __('Geruestbilder', 'webapp-central-starter'),
                    __('Technikdetails', 'webapp-central-starter'),
                    __('Drohnenbilder', 'webapp-central-starter'),
                    __('Bildsortierung', 'webapp-central-starter'),
                    __('spaetere Galerie', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Offene Punkte', 'webapp-central-starter'),
                'status' => __('Offen', 'webapp-central-starter'),
                'items' => [
                    __('Fertigstellung pruefen', 'webapp-central-starter'),
                    __('Zaehlerwechsel dokumentieren', 'webapp-central-starter'),
                    __('Enpal-Unterlagen sichern', 'webapp-central-starter'),
                    __('Fotomaterial sortieren', 'webapp-central-starter'),
                    __('Statusbericht erstellen', 'webapp-central-starter'),
                    __('rechtliche / organisatorische Punkte nachhalten', 'webapp-central-starter'),
                ],
            ],
            [
                'title' => __('Web-/Projektarchiv', 'webapp-central-starter'),
                'status' => __('Archiv', 'webapp-central-starter'),
                'items' => [
                    __('Darstellung auf webapp-central.de', 'webapp-central-starter'),
                    __('Projektkarte', 'webapp-central-starter'),
                    __('Detailseite', 'webapp-central-starter'),
                    __('spaetere Galerie', 'webapp-central-starter'),
                    __('PDF-/Whitepaper-Vorbereitung', 'webapp-central-starter'),
                ],
            ],
        ],
    ];
}

function webapp_central_starter_structured_page_data(string $slug): ?array
{
    $pages = [
        'portal' => [
            'eyebrow' => __('Portal', 'webapp-central-starter'),
            'summary' => __('Oeffentliche Steuerzentrale fuer Projektstatus, Schnellzugriffe und den aktuellen Live-Kontext von Webapp Central.', 'webapp-central-starter'),
            'highlights' => [
                __('Status: Relaunch live', 'webapp-central-starter'),
                __('Arbeitsmodus: Lokal / Codex / SSH-Deploy', 'webapp-central-starter'),
                __('Lokal: kein Docker', 'webapp-central-starter'),
            ],
            'actions' => [
                [
                    'label' => __('Projekte oeffnen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('projekte', '/projekte/'),
                ],
                [
                    'label' => __('Dokumentationen ansehen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/'),
                    'variant' => 'ghost',
                ],
            ],
            'sections' => [
                [
                    'badge' => __('Projektstatus', 'webapp-central-starter'),
                    'title' => __('Was aktuell live ist', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'status' => __('Live', 'webapp-central-starter'),
                            'title' => __('Plattform-Relaunch', 'webapp-central-starter'),
                            'text' => __('Die dunkle Plattformoptik, die Projektstruktur und die navigierbaren Hauptbereiche sind live aktiv.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Arbeitsmodus', 'webapp-central-starter'),
                            'title' => __('Lokale Entwicklung mit Codex', 'webapp-central-starter'),
                            'text' => __('Aenderungen werden lokal im Repository vorbereitet und kontrolliert per SSH auf den Server uebertragen.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Naechste Schritte', 'webapp-central-starter'),
                            'title' => __('Inhalte vor Automatisierung', 'webapp-central-starter'),
                            'text' => __('Im Fokus stehen zuerst klare Inhalte, Zielseiten und stabile Arbeitsbereiche statt weiterer Technikshow.', 'webapp-central-starter'),
                        ],
                    ],
                ],
                [
                    'badge' => __('Schnellzugriffe', 'webapp-central-starter'),
                    'title' => __('Direkte Wege in die Hauptbereiche', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'title' => __('Projekte', 'webapp-central-starter'),
                            'text' => __('Projekt-Hubs, Themenkarten und laufende Uebersichtsbereiche fuer operative Arbeit.', 'webapp-central-starter'),
                            'link' => [
                                'label' => __('Zu Projekte', 'webapp-central-starter'),
                                'url' => webapp_central_starter_portal_page_url('projekte', '/projekte/'),
                            ],
                        ],
                        [
                            'title' => __('Dokumentationen', 'webapp-central-starter'),
                            'text' => __('Canonical Docs, Leitfaeden, Projektgedaechtnis und saubere Ablagestrukturen.', 'webapp-central-starter'),
                            'link' => [
                                'label' => __('Zu Dokumentationen', 'webapp-central-starter'),
                                'url' => webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/'),
                            ],
                        ],
                        [
                            'title' => __('System', 'webapp-central-starter'),
                            'text' => __('Technische Leitplanken, Workspace-Kontext, SSH-Deploy und Rollback-Logik.', 'webapp-central-starter'),
                            'link' => [
                                'label' => __('Zu System', 'webapp-central-starter'),
                                'url' => webapp_central_starter_portal_page_url('system', '/system/'),
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'dokumentationen' => [
            'eyebrow' => __('Dokumentationen', 'webapp-central-starter'),
            'summary' => __('Technische Dokumentationen, Canonical Docs, Projektgedaechtnis und nachvollziehbare Leitfaeden fuer Webapp Central.', 'webapp-central-starter'),
            'highlights' => [
                __('Canonical Docs als Referenzspur', 'webapp-central-starter'),
                __('Projektgedaechtnis statt Einzeldatei-Chaos', 'webapp-central-starter'),
                __('Leitfaeden und Ablagen fuer spaetere Erweiterungen', 'webapp-central-starter'),
            ],
            'actions' => [
                [
                    'label' => __('Projektbereiche ansehen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('projekte', '/projekte/'),
                ],
                [
                    'label' => __('Systemkontext lesen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('system', '/system/'),
                    'variant' => 'ghost',
                ],
            ],
            'sections' => [
                [
                    'badge' => __('Dokumentationsbereiche', 'webapp-central-starter'),
                    'title' => __('Saubere Einordnung statt Ablage ohne Struktur', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'status' => __('Basis', 'webapp-central-starter'),
                            'title' => __('Canonical Docs', 'webapp-central-starter'),
                            'text' => __('Grundlegende Architektur-, Plattform- und Governance-Texte als verbindliche Referenzschicht.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Laufend', 'webapp-central-starter'),
                            'title' => __('Projektgedaechtnis', 'webapp-central-starter'),
                            'text' => __('Entscheidungen, Entwicklungswege und Lessons Learned werden nachvollziehbar statt nur implizit gehalten.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Vorbereitet', 'webapp-central-starter'),
                            'title' => __('Leitfaeden und Ablagen', 'webapp-central-starter'),
                            'text' => __('Technische Wege, PDF-Bereiche und geordnete Ablagen koennen hier modular weiter ausgebaut werden.', 'webapp-central-starter'),
                        ],
                    ],
                ],
            ],
        ],
        'medien' => [
            'eyebrow' => __('Medien', 'webapp-central-starter'),
            'summary' => __('Strukturierte Medienablage fuer Bilder, Berichte, Hallenberg-Nachweise und spaetere visuelle Galerien.', 'webapp-central-starter'),
            'highlights' => [
                __('Bilder und Nachweise geordnet statt verstreut', 'webapp-central-starter'),
                __('Hallenberg-Medien als eigener Bezugspunkt', 'webapp-central-starter'),
                __('Spaetere Galerien klar als Ausbaupfad', 'webapp-central-starter'),
            ],
            'actions' => [
                [
                    'label' => __('Projekt-Hubs ansehen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('projekte', '/projekte/'),
                ],
                [
                    'label' => __('Analyse vorbereiten', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('analyse', '/analyse/'),
                    'variant' => 'ghost',
                ],
            ],
            'sections' => [
                [
                    'badge' => __('Medienstruktur', 'webapp-central-starter'),
                    'title' => __('Visuelle Nachweise mit klarer Ordnung', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'status' => __('Aktiv', 'webapp-central-starter'),
                            'title' => __('Bilder und Berichte', 'webapp-central-starter'),
                            'text' => __('Bildmaterial, Berichte und spaetere Exportformate koennen hier nachvollziehbar zusammengefuehrt werden.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Projektbezug', 'webapp-central-starter'),
                            'title' => __('Hallenberg-Medien', 'webapp-central-starter'),
                            'text' => __('Fotos, visuelle Nachweise und Projektmaterialien koennen fuer Hallenberg und andere Hubs separat gebuendelt werden.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Roadmap', 'webapp-central-starter'),
                            'title' => __('Spaetere Galerien', 'webapp-central-starter'),
                            'text' => __('Noch keine Fake-Galerie: der Bereich ist vorbereitet fuer spaetere strukturierte Medienansichten.', 'webapp-central-starter'),
                        ],
                    ],
                ],
            ],
        ],
        'analyse' => [
            'eyebrow' => __('Analyse', 'webapp-central-starter'),
            'summary' => __('Vorbereiteter Bereich fuer KI-Auswertungen, Reports, Dashboards und projektbezogene Analysen ohne vorgetaeuschte Live-Funktionen.', 'webapp-central-starter'),
            'highlights' => [
                __('Keine Fake-Dashboards', 'webapp-central-starter'),
                __('Reports und Analysen als Roadmap markiert', 'webapp-central-starter'),
                __('Spaetere KI-Auswertungen mit Governance-Rahmen', 'webapp-central-starter'),
            ],
            'actions' => [
                [
                    'label' => __('Systemleitplanken lesen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('system', '/system/'),
                ],
                [
                    'label' => __('Dokumentationen ansehen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/'),
                    'variant' => 'ghost',
                ],
            ],
            'sections' => [
                [
                    'badge' => __('Analyse-Roadmap', 'webapp-central-starter'),
                    'title' => __('Was hier spaeter sinnvoll wachsen kann', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'status' => __('Vorbereitet', 'webapp-central-starter'),
                            'title' => __('Reports', 'webapp-central-starter'),
                            'text' => __('Strukturierte Statusberichte, Auswertungen und Verdichtungen koennen hier spaeter eingebunden werden.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Roadmap', 'webapp-central-starter'),
                            'title' => __('Dashboards', 'webapp-central-starter'),
                            'text' => __('Keine Scheinoberflaechen: Dashboards werden erst gezeigt, wenn echte Datenquellen und klare Pflegewege existieren.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Governance', 'webapp-central-starter'),
                            'title' => __('KI-Auswertungen', 'webapp-central-starter'),
                            'text' => __('Codex- und spaetere Agenten-Workflows koennen Analysen vorbereiten, aber nur kontrolliert und nachvollziehbar.', 'webapp-central-starter'),
                        ],
                    ],
                ],
            ],
        ],
        'system' => [
            'eyebrow' => __('System', 'webapp-central-starter'),
            'summary' => __('Technische Leitplanken fuer Repository, lokalen Workspace, SSH-Deploy und kontrollierte Rollback-Strategien.', 'webapp-central-starter'),
            'highlights' => [
                __('GitHub bleibt die Steuerzentrale', 'webapp-central-starter'),
                __('Lokaler Workspace: D:\\Projekte\\webapp-central\\repo', 'webapp-central-starter'),
                __('Kein lokales Docker', 'webapp-central-starter'),
            ],
            'actions' => [
                [
                    'label' => __('Dokumentationen lesen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('dokumentationen', '/dokumentationen/'),
                ],
                [
                    'label' => __('Projektuebersicht oeffnen', 'webapp-central-starter'),
                    'url' => webapp_central_starter_portal_page_url('projekte', '/projekte/'),
                    'variant' => 'ghost',
                ],
            ],
            'sections' => [
                [
                    'badge' => __('Systemstatus', 'webapp-central-starter'),
                    'title' => __('Technischer Rahmen fuer den laufenden Betrieb', 'webapp-central-starter'),
                    'cards' => [
                        [
                            'status' => __('Repository', 'webapp-central-starter'),
                            'title' => __('GitHub als Steuerzentrale', 'webapp-central-starter'),
                            'text' => __('Das Repository bleibt die nachvollziehbare Quelle fuer Theme-, Dokumentations- und Strukturveraenderungen.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Workspace', 'webapp-central-starter'),
                            'title' => __('Lokale Arbeit in D:\\Projekte\\webapp-central\\repo', 'webapp-central-starter'),
                            'text' => __('VSCode, Codex und lokale Dateiverwaltung greifen auf den D:-Workspace zu; der alte C:-Pfad bleibt nur als Sicherheitskopie.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Deployment', 'webapp-central-starter'),
                            'title' => __('SSH-Deploy ohne lokales Docker', 'webapp-central-starter'),
                            'text' => __('Live-Aenderungen werden kontrolliert per SSH synchronisiert. Lokale Docker-Container gehoeren nicht mehr zum Arbeitsmodus.', 'webapp-central-starter'),
                        ],
                        [
                            'status' => __('Sicherheit', 'webapp-central-starter'),
                            'title' => __('Backup- und Rollback-Konzept', 'webapp-central-starter'),
                            'text' => __('Vor Live-Aenderungen wird ein Theme-Backup mit Timestamp angelegt, damit jede Anpassung kontrolliert ruecksetzbar bleibt.', 'webapp-central-starter'),
                        ],
                    ],
                ],
            ],
        ],
    ];

    return $pages[$slug] ?? null;
}

function webapp_central_starter_render_github_status_card(string $classes = 'glass-panel sidebar-card sidebar-card--github'): void
{
    $items = [
        __('Repository: webapp-central', 'webapp-central-starter'),
        __('Branch: codex/docs-plugin-theme-pass', 'webapp-central-starter'),
        __('Arbeitsmodus: Lokal / Codex / SSH-Deploy', 'webapp-central-starter'),
        __('Status: Relaunch live', 'webapp-central-starter'),
        __('Lokal: kein Docker', 'webapp-central-starter'),
        __('Theme: Webapp Central Starter', 'webapp-central-starter'),
    ];

    echo '<section class="' . esc_attr($classes) . '">';
    echo '<div class="sidebar-card__badge">' . esc_html__('GitHub Status', 'webapp-central-starter') . '</div>';
    echo '<h2 class="section-heading">' . esc_html__('GitHub Status', 'webapp-central-starter') . '</h2>';
    echo '<ul class="meta-list meta-list--status">';

    foreach ($items as $item) {
        echo '<li>' . esc_html($item) . '</li>';
    }

    echo '</ul>';
    echo '</section>';
}

function webapp_central_starter_render_structured_page_article(string $slug, string $title, string $content = ''): void
{
    $page_data = webapp_central_starter_structured_page_data($slug);
    $page_summary = is_array($page_data) ? (string) ($page_data['summary'] ?? '') : '';

    echo '<article class="glass-panel page-shell structured-page structured-page--' . esc_attr(sanitize_html_class($slug)) . '">';
    echo '<header class="entry-header">';
    echo '<span class="eyebrow">' . esc_html(is_array($page_data) ? (string) ($page_data['eyebrow'] ?? __('Seite', 'webapp-central-starter')) : __('Seite', 'webapp-central-starter')) . '</span>';
    echo '<h1 class="entry-title">' . esc_html($title) . '</h1>';

    if ($page_summary !== '') {
        echo '<p class="entry-summary">' . esc_html($page_summary) . '</p>';
    }

    if (is_array($page_data) && !empty($page_data['highlights']) && is_array($page_data['highlights'])) {
        echo '<ul class="page-hero-meta">';
        foreach ($page_data['highlights'] as $highlight) {
            echo '<li>' . esc_html((string) $highlight) . '</li>';
        }
        echo '</ul>';
    }

    if (is_array($page_data) && !empty($page_data['actions']) && is_array($page_data['actions'])) {
        echo '<div class="page-actions">';
        foreach ($page_data['actions'] as $action) {
            $button_classes = 'button-link';
            if (($action['variant'] ?? '') === 'ghost') {
                $button_classes .= ' button-link--ghost';
            }

            echo '<a class="' . esc_attr($button_classes) . '" href="' . esc_url((string) ($action['url'] ?? '#')) . '">';
            echo esc_html((string) ($action['label'] ?? ''));
            echo '</a>';
        }
        echo '</div>';
    }

    echo '</header>';

    if (trim($content) !== '') {
        echo '<div class="entry-content">' . $content . '</div>';
    }

    if (is_array($page_data) && !empty($page_data['sections']) && is_array($page_data['sections'])) {
        echo '<div class="page-sections">';
        foreach ($page_data['sections'] as $section) {
            echo '<section class="page-section">';
            echo '<div class="page-section__heading">';
            if (!empty($section['badge'])) {
                echo '<span class="portal-badge portal-badge--soft">' . esc_html((string) $section['badge']) . '</span>';
            }
            echo '<h2>' . esc_html((string) ($section['title'] ?? '')) . '</h2>';
            echo '</div>';

            if (!empty($section['cards']) && is_array($section['cards'])) {
                echo '<div class="page-grid">';
                foreach ($section['cards'] as $card) {
                    echo '<article class="page-card">';
                    if (!empty($card['status'])) {
                        echo '<div class="page-card__meta"><span class="portal-status">' . esc_html((string) $card['status']) . '</span></div>';
                    }
                    echo '<h3>' . esc_html((string) ($card['title'] ?? '')) . '</h3>';
                    echo '<p>' . esc_html((string) ($card['text'] ?? '')) . '</p>';

                    if (!empty($card['link']) && is_array($card['link'])) {
                        echo '<a class="page-card__link" href="' . esc_url((string) ($card['link']['url'] ?? '#')) . '">';
                        echo esc_html((string) ($card['link']['label'] ?? ''));
                        echo '</a>';
                    }

                    echo '</article>';
                }
                echo '</div>';
            }

            echo '</section>';
        }
        echo '</div>';
    }

    echo '</article>';
}

function webapp_central_starter_is_virtual_portal_request(): bool
{
    if (is_admin()) {
        return false;
    }

    $request_uri = (string) ($_SERVER['REQUEST_URI'] ?? '');
    $request_path = trim((string) parse_url($request_uri, PHP_URL_PATH), '/');

    return $request_path === 'portal';
}

add_filter('template_include', static function (string $template): string {
    if (!webapp_central_starter_is_virtual_portal_request()) {
        return $template;
    }

    global $wp_query;

    if ($wp_query instanceof WP_Query) {
        $wp_query->is_404 = false;
        $wp_query->is_page = true;
        $wp_query->is_singular = true;
    }

    status_header(200);

    return get_template_directory() . '/portal.php';
}, 0);
