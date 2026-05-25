<?php
declare(strict_types=1);
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="site-shell">
    <header class="site-header">
        <div class="site-header__inner">
            <div class="site-branding">
                <?php webapp_central_starter_render_header_branding(); ?>
            </div>
            <div class="site-header__nav-group">
                <?php webapp_central_starter_render_menu(); ?>
                <a class="button-link site-header__cta" href="<?php echo esc_url(webapp_central_starter_portal_page_url('projekte', '/projekte/')); ?>">
                    <?php esc_html_e('Projekt starten', 'webapp-central-starter'); ?>
                </a>
            </div>
        </div>
    </header>
