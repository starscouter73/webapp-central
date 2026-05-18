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
    $favicon = webapp_central_starter_asset_url('brand/icons/webapp-central-favicon-light-32x32.png');
    $apple = webapp_central_starter_asset_url('brand/icons/webapp-central-apple-touch-icon-light-180x180.png');
    $site_icon = webapp_central_starter_asset_url('brand/icons/webapp-central-site-icon-light-512x512.png');

    echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url($favicon) . '">' . PHP_EOL;
    echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url($apple) . '">' . PHP_EOL;
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
    $relative_path = 'brand/header/webapp-central-header-logo-light-600x160.png';
    $logo_path = webapp_central_starter_asset_path($relative_path);
    $logo_url = webapp_central_starter_asset_url($relative_path);

    if (file_exists($logo_path)) {
        echo '<a class="site-logo" href="' . esc_url(home_url('/')) . '" aria-label="' . esc_attr__('webapp-central.de Startseite', 'webapp-central-starter') . '" title="' . esc_attr__('Zur Startseite von webapp-central.de', 'webapp-central-starter') . '">';
        echo '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr__('Webapp Central Header-Logo fuer Projekte, Module und Tutorials', 'webapp-central-starter') . '" width="300" height="80">';
        echo '</a>';
    }

    echo '<div class="site-branding__text">';
    echo '<p class="site-title' . (file_exists($logo_path) ? ' screen-reader-text' : '') . '"><span>' . esc_html(get_bloginfo('name')) . '</span></p>';
    echo '<p class="site-kicker">' . esc_html__('Offizielles Header-Logo', 'webapp-central-starter') . '</p>';
    echo '<p class="site-caption">' . esc_html__('Projektzentrale fuer Inhalte, Module und Tutorials', 'webapp-central-starter') . '</p>';
    echo '</div>';
}

function webapp_central_starter_render_main_brand(): void
{
    $relative_path = 'brand/main/webapp-central-main-logo-light-1200x400.png';
    $main_logo_path = webapp_central_starter_asset_path($relative_path);
    $main_logo = webapp_central_starter_asset_url($relative_path);

    if (!file_exists($main_logo_path)) {
        return;
    }

    echo '<figure class="hero-brandmark">';
    echo '<p class="hero-brandmark__label">' . esc_html__('Hauptlogo', 'webapp-central-starter') . '</p>';
    echo '<img src="' . esc_url($main_logo) . '" alt="' . esc_attr__('Webapp Central Hauptlogo fuer die Projektzentrale', 'webapp-central-starter') . '" width="600" height="200">';
    echo '<figcaption class="hero-brandmark__caption">' . esc_html__('Offizielles WordPress-Branding fuer webapp-central.de', 'webapp-central-starter') . '</figcaption>';
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
