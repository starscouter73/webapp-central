<?php
declare(strict_types=1);

require_once '/var/www/html/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if (!function_exists('wp_generate_attachment_metadata')) {
    fwrite(STDERR, "WordPress media functions unavailable.\n");
    exit(1);
}

$theme_assets = '/var/www/html/wp-content/themes/webapp-central-starter/assets/brand';
$uploads = wp_upload_dir();

if (!is_dir($theme_assets) || !is_array($uploads) || !isset($uploads['path'], $uploads['url'])) {
    fwrite(STDERR, "Theme assets or uploads path unavailable.\n");
    exit(1);
}

$items = [
    'header_logo' => [
        'source' => $theme_assets . '/header/webapp-central-header-fresh-blue-display.png',
        'title' => 'Webapp Central Header Logo',
        'alt' => 'Webapp Central Header-Logo',
        'mime' => 'image/png',
    ],
    'hero_logo' => [
        'source' => $theme_assets . '/main/webapp-central-main-fresh-blue-display.png',
        'title' => 'Webapp Central Hero Logo',
        'alt' => 'Webapp Central Hero-Logo',
        'mime' => 'image/png',
    ],
    'site_icon' => [
        'source' => $theme_assets . '/icons/webapp-central-icon-fresh-blue-512x512.png',
        'title' => 'Webapp Central Site Icon',
        'alt' => 'Webapp Central Site Icon',
        'mime' => 'image/png',
    ],
];

function webapp_central_find_attachment(string $brand_key): int
{
    $query = new WP_Query([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'meta_key' => '_webapp_central_brand_key',
        'meta_value' => $brand_key,
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    return isset($query->posts[0]) ? (int) $query->posts[0] : 0;
}

function webapp_central_import_attachment(string $brand_key, array $item, array $uploads): int
{
    $existing_id = webapp_central_find_attachment($brand_key);
    $basename = wp_unique_filename($uploads['path'], basename($item['source']));
    $target = trailingslashit($uploads['path']) . $basename;

    if (!copy($item['source'], $target)) {
        throw new RuntimeException('Failed to copy asset: ' . $item['source']);
    }

    $filetype = wp_check_filetype($basename, null);
    $attachment = [
        'post_mime_type' => $filetype['type'] ?: $item['mime'],
        'post_title' => $item['title'],
        'post_content' => '',
        'post_status' => 'inherit',
    ];

    if ($existing_id > 0) {
        wp_update_post([
            'ID' => $existing_id,
            'post_title' => $item['title'],
        ]);
        update_attached_file($existing_id, $target);
        $attachment_id = $existing_id;
    } else {
        $attachment_id = wp_insert_attachment($attachment, $target);
    }

    if (!is_int($attachment_id) || $attachment_id <= 0) {
        throw new RuntimeException('Failed to create attachment for: ' . $brand_key);
    }

    update_post_meta($attachment_id, '_webapp_central_brand_key', $brand_key);
    update_post_meta($attachment_id, '_wp_attachment_image_alt', $item['alt']);

    $metadata = wp_generate_attachment_metadata($attachment_id, $target);
    wp_update_attachment_metadata($attachment_id, $metadata);

    return $attachment_id;
}

try {
    $ids = [];
    foreach ($items as $brand_key => $item) {
        if (!file_exists($item['source'])) {
            throw new RuntimeException('Missing source file: ' . $item['source']);
        }
        $ids[$brand_key] = webapp_central_import_attachment($brand_key, $item, $uploads);
    }

    set_theme_mod('custom_logo', $ids['header_logo']);
    set_theme_mod('webapp_central_hero_logo_id', $ids['hero_logo']);
    update_option('site_icon', $ids['site_icon']);

    echo wp_json_encode($ids, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
