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
<main style="max-width: 60rem; margin: 4rem auto; padding: 0 1.5rem;">
    <h1><?php bloginfo('name'); ?></h1>
    <p>WordPress-Reload fuer webapp-central.de ist aktiv.</p>
</main>
<?php wp_footer(); ?>
</body>
</html>

