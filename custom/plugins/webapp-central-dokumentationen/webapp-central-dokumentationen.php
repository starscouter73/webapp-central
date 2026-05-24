<?php
/**
 * Plugin Name: Webapp Central Dokumentationen
 * Description: Minimaler technischer Prototyp fuer den Dokumentationsbereich mit CPT-, Taxonomie- und Basis-Meta-Registrierung.
 * Version: 0.1.0
 * Author: Mark Dorth
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

register_activation_hook(__FILE__, static function (): void {
    webapp_central_docs_register_content_model();
    flush_rewrite_rules();
});

register_deactivation_hook(__FILE__, static function (): void {
    flush_rewrite_rules();
});

add_action('init', 'webapp_central_docs_register_content_model');

function webapp_central_docs_register_content_model(): void
{
    webapp_central_docs_register_post_type();
    webapp_central_docs_register_taxonomies();
    webapp_central_docs_register_meta();
}

function webapp_central_docs_register_post_type(): void
{
    $labels = [
        'name' => __('Dokumentationen', 'webapp-central-dokumentationen'),
        'singular_name' => __('Dokumentation', 'webapp-central-dokumentationen'),
        'menu_name' => __('Dokumentationen', 'webapp-central-dokumentationen'),
        'name_admin_bar' => __('Dokumentation', 'webapp-central-dokumentationen'),
        'add_new' => __('Neu hinzufuegen', 'webapp-central-dokumentationen'),
        'add_new_item' => __('Neue Dokumentation anlegen', 'webapp-central-dokumentationen'),
        'edit_item' => __('Dokumentation bearbeiten', 'webapp-central-dokumentationen'),
        'new_item' => __('Neue Dokumentation', 'webapp-central-dokumentationen'),
        'view_item' => __('Dokumentation ansehen', 'webapp-central-dokumentationen'),
        'view_items' => __('Dokumentationen ansehen', 'webapp-central-dokumentationen'),
        'search_items' => __('Dokumentationen durchsuchen', 'webapp-central-dokumentationen'),
        'not_found' => __('Keine Dokumentationen gefunden', 'webapp-central-dokumentationen'),
        'not_found_in_trash' => __('Keine Dokumentationen im Papierkorb gefunden', 'webapp-central-dokumentationen'),
        'all_items' => __('Alle Dokumentationen', 'webapp-central-dokumentationen'),
        'archives' => __('Dokumentationsarchiv', 'webapp-central-dokumentationen'),
        'attributes' => __('Dokumentationsattribute', 'webapp-central-dokumentationen'),
        'insert_into_item' => __('In Dokumentation einfuegen', 'webapp-central-dokumentationen'),
        'uploaded_to_this_item' => __('Zu dieser Dokumentation hochgeladen', 'webapp-central-dokumentationen'),
        'filter_items_list' => __('Dokumentationsliste filtern', 'webapp-central-dokumentationen'),
        'items_list_navigation' => __('Navigation der Dokumentationsliste', 'webapp-central-dokumentationen'),
        'items_list' => __('Dokumentationsliste', 'webapp-central-dokumentationen'),
        'item_published' => __('Dokumentation veroeffentlicht.', 'webapp-central-dokumentationen'),
        'item_updated' => __('Dokumentation aktualisiert.', 'webapp-central-dokumentationen'),
    ];

    register_post_type('dokumentation', [
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'show_in_nav_menus' => false,
        'show_in_rest' => true,
        'has_archive' => false,
        'rewrite' => [
            'slug' => 'dokumentationen',
            'with_front' => false,
        ],
        'menu_position' => 21,
        'menu_icon' => 'dashicons-media-document',
        'supports' => ['title', 'editor', 'excerpt', 'revisions', 'page-attributes'],
        'map_meta_cap' => true,
    ]);
}

function webapp_central_docs_register_taxonomies(): void
{
    register_taxonomy('doku_bereich', ['dokumentation'], [
        'labels' => webapp_central_docs_taxonomy_labels(
            __('Bereiche', 'webapp-central-dokumentationen'),
            __('Bereich', 'webapp-central-dokumentationen')
        ),
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'hierarchical' => true,
        'rewrite' => false,
    ]);

    register_taxonomy('doku_format', ['dokumentation'], [
        'labels' => webapp_central_docs_taxonomy_labels(
            __('Dokumenttypen', 'webapp-central-dokumentationen'),
            __('Dokumenttyp', 'webapp-central-dokumentationen')
        ),
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite' => false,
    ]);

    register_taxonomy('doku_status', ['dokumentation'], [
        'labels' => webapp_central_docs_taxonomy_labels(
            __('Status', 'webapp-central-dokumentationen'),
            __('Status', 'webapp-central-dokumentationen')
        ),
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite' => false,
    ]);

    register_taxonomy('doku_serie', ['dokumentation'], [
        'labels' => webapp_central_docs_taxonomy_labels(
            __('Serien', 'webapp-central-dokumentationen'),
            __('Serie', 'webapp-central-dokumentationen')
        ),
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'hierarchical' => false,
        'rewrite' => false,
    ]);
}

function webapp_central_docs_register_meta(): void
{
    $text_fields = [
        'doku_kurzbeschreibung',
        'doku_primary_hub',
        'doku_position_label',
        'doku_visibility',
        'doku_updated_at',
        'doku_source_reference',
    ];

    foreach ($text_fields as $meta_key) {
        register_post_meta('dokumentation', $meta_key, [
            'type' => 'string',
            'single' => true,
            'default' => '',
            'show_in_rest' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => 'webapp_central_docs_can_edit_meta',
        ]);
    }

    register_post_meta('dokumentation', 'doku_editorial_note', [
        'type' => 'string',
        'single' => true,
        'default' => '',
        'show_in_rest' => true,
        'sanitize_callback' => 'sanitize_textarea_field',
        'auth_callback' => 'webapp_central_docs_can_edit_meta',
    ]);

    register_post_meta('dokumentation', 'doku_featured', [
        'type' => 'boolean',
        'single' => true,
        'default' => false,
        'show_in_rest' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'auth_callback' => 'webapp_central_docs_can_edit_meta',
    ]);

    register_post_meta('dokumentation', 'doku_is_canonical', [
        'type' => 'boolean',
        'single' => true,
        'default' => false,
        'show_in_rest' => true,
        'sanitize_callback' => 'rest_sanitize_boolean',
        'auth_callback' => 'webapp_central_docs_can_edit_meta',
    ]);

    register_post_meta('dokumentation', 'doku_featured_rank', [
        'type' => 'integer',
        'single' => true,
        'default' => 0,
        'show_in_rest' => true,
        'sanitize_callback' => 'absint',
        'auth_callback' => 'webapp_central_docs_can_edit_meta',
    ]);

    register_post_meta('dokumentation', 'doku_next_step', [
        'type' => 'integer',
        'single' => true,
        'default' => 0,
        'show_in_rest' => true,
        'sanitize_callback' => 'absint',
        'auth_callback' => 'webapp_central_docs_can_edit_meta',
    ]);
}

function webapp_central_docs_taxonomy_labels(string $plural, string $singular): array
{
    return [
        'name' => $plural,
        'singular_name' => $singular,
        'search_items' => sprintf(__('%s durchsuchen', 'webapp-central-dokumentationen'), $plural),
        'all_items' => sprintf(__('Alle %s', 'webapp-central-dokumentationen'), $plural),
        'edit_item' => sprintf(__('%s bearbeiten', 'webapp-central-dokumentationen'), $singular),
        'view_item' => sprintf(__('%s ansehen', 'webapp-central-dokumentationen'), $singular),
        'update_item' => sprintf(__('%s aktualisieren', 'webapp-central-dokumentationen'), $singular),
        'add_new_item' => sprintf(__('Neue %s hinzufuegen', 'webapp-central-dokumentationen'), $singular),
        'new_item_name' => sprintf(__('Neuer Name fuer %s', 'webapp-central-dokumentationen'), $singular),
        'menu_name' => $plural,
    ];
}

function webapp_central_docs_can_edit_meta(
    bool $allowed,
    string $meta_key,
    int $post_id,
    int $user_id,
    string $cap,
    array $caps
): bool
{
    unset($allowed, $meta_key, $cap, $caps);

    return user_can($user_id, 'edit_post', $post_id);
}
