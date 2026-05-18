<?php
declare(strict_types=1);

require '/var/www/html/wp-load.php';

$pages = [
    [
        'title' => 'Start',
        'slug' => 'start',
        'content' => '<h2>Willkommen bei webapp-central.de</h2><p>Diese Seite bildet die neue WordPress-Projektzentrale fuer Inhalte, Module und den weiteren strukturierten Ausbau.</p>',
    ],
    [
        'title' => 'Impressum',
        'slug' => 'impressum',
        'content' => '<h2>Impressum</h2><p><strong>Verantwortlich fuer dieses Angebot:</strong><br>Mark Dorth<br>Louis-Mannstaedt-Str. 64<br>53840 Troisdorf<br>Telefon: <a href="tel:+4915751444355">015751444355</a><br>E-Mail: <a href="mailto:dorth.mark@gmail.com">dorth.mark@gmail.com</a></p><p>Diese Website wird als Projektzentrale und Weboberflaeche fuer webapp-central.de betrieben.</p>',
    ],
    [
        'title' => 'Datenschutz',
        'slug' => 'datenschutz',
        'content' => '<h2>Datenschutz</h2><p>Diese Website wird technisch ueber WordPress und eine Docker-basierte Serverumgebung betrieben.</p><p>Beim rein informatorischen Aufruf der Website koennen serverseitig technisch erforderliche Verbindungsdaten verarbeitet werden. Dazu koennen insbesondere IP-Adresse, Datum, Uhrzeit, aufgerufene URL sowie technische Request-Informationen gehoeren, soweit dies fuer die Auslieferung und Systemsicherheit erforderlich ist.</p><p>Personenbezogene Kontaktangaben, die ueber diese Website verarbeitet werden, erfolgen ausschliesslich zweckgebunden. Eine weitergehende Datenschutzerklaerung mit konkreten Angaben zu Speicherfristen, Logfiles und kuenftigen Zusatzdiensten wird mit dem weiteren Ausbau dieser Website fortgeschrieben.</p><p>Verantwortlich: Mark Dorth, Louis-Mannstaedt-Str. 64, 53840 Troisdorf, <a href="mailto:dorth.mark@gmail.com">dorth.mark@gmail.com</a>, Telefon <a href="tel:+4915751444355">015751444355</a>.</p>',
    ],
    [
        'title' => 'Kontakt',
        'slug' => 'kontakt',
        'content' => '<h2>Kontakt</h2><p>Kontaktstelle fuer webapp-central.de:<br>Mark Dorth<br>Louis-Mannstaedt-Str. 64<br>53840 Troisdorf<br>Telefon: <a href="tel:+4915751444355">015751444355</a><br>E-Mail: <a href="mailto:dorth.mark@gmail.com">dorth.mark@gmail.com</a></p><p>Weitere Kontaktkanaele und feste Formulare koennen spaeter gezielt fuer den Live-Betrieb ergaenzt werden.</p>',
    ],
];

$created = [];

foreach ($pages as $page) {
    $existing = get_page_by_path($page['slug']);
    $payload = [
        'post_title' => $page['title'],
        'post_name' => $page['slug'],
        'post_content' => $page['content'],
        'post_type' => 'page',
        'post_status' => 'publish',
    ];

    if ($existing instanceof WP_Post) {
        $payload['ID'] = $existing->ID;
        wp_update_post($payload, true);
        $created[] = 'updated:' . $page['slug'];
    } else {
        wp_insert_post($payload, true);
        $created[] = 'created:' . $page['slug'];
    }
}

$default_posts = get_posts([
    'post_type' => 'post',
    'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
    'numberposts' => -1,
]);

foreach ($default_posts as $post) {
    if (in_array($post->post_name, ['hello-world'], true) || in_array($post->post_title, ['Hallo Welt!', 'Hello world!'], true)) {
        wp_delete_post($post->ID, true);
    }
}

$default_pages = get_posts([
    'post_type' => 'page',
    'post_status' => ['publish', 'draft', 'pending', 'future', 'private'],
    'numberposts' => -1,
]);

foreach ($default_pages as $page) {
    if (in_array($page->post_name, ['sample-page', 'beispiel-seite'], true) || in_array($page->post_title, ['Sample Page', 'Beispiel-Seite'], true)) {
        wp_delete_post($page->ID, true);
    }
}

$front_page = get_page_by_path('start');
if ($front_page instanceof WP_Post) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $front_page->ID);
}

flush_rewrite_rules(false);

echo implode(PHP_EOL, $created) . PHP_EOL;
echo 'front_page=' . ($front_page instanceof WP_Post ? (string) $front_page->ID : 'none') . PHP_EOL;
