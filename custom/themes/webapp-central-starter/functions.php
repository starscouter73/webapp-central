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

    return $classes;
}

add_filter('body_class', 'webapp_central_starter_body_classes');

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
    echo '<p class="site-title' . ($logo_markup !== null ? ' screen-reader-text' : '') . '"><span>' . esc_html(get_bloginfo('name')) . '</span></p>';
    echo '<p class="site-kicker">' . esc_html__('Offizielle Wortmarke', 'webapp-central-starter') . '</p>';
    echo '<p class="site-caption">' . esc_html__('Projektzentrale fuer Inhalte, Module und Tutorials', 'webapp-central-starter') . '</p>';
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
    echo '<li><a href="' . esc_url(admin_url()) . '">' . esc_html__('Dashboard', 'webapp-central-starter') . '</a></li>';
    echo '<li><a href="' . esc_url(home_url('/wp-admin/post-new.php?post_type=page')) . '">' . esc_html__('Neue Seite', 'webapp-central-starter') . '</a></li>';
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

function webapp_central_starter_portal_sections(): array
{
    return [
        [
            'title' => __('Dashboard', 'webapp-central-starter'),
            'description' => __('Zentrale Einstiegsseite fuer den aktuellen Projekt- und Systemueberblick.', 'webapp-central-starter'),
            'url' => home_url('/'),
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
            'button' => __('Oeffnen', 'webapp-central-starter'),
            'url' => '#enpal-hallenberg',
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
