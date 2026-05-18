<?php
declare(strict_types=1);

require '/var/www/html/wp-load.php';

$theme = 'webapp-central-starter';
switch_theme($theme);

echo 'active_theme=' . get_option('stylesheet') . PHP_EOL;
echo 'template=' . get_option('template') . PHP_EOL;
