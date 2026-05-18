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

function webapp_central_starter_body_classes(array $classes): array
{
    if (is_front_page()) {
        $classes[] = 'is-front-page';
    }

    return $classes;
}

add_filter('body_class', 'webapp_central_starter_body_classes');

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
