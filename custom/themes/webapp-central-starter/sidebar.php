<?php
declare(strict_types=1);
?>
<aside class="content-sidebar">
    <?php webapp_central_starter_render_github_status_card(); ?>
    <section class="glass-panel">
        <h2 class="section-heading"><?php esc_html_e('Projektstatus', 'webapp-central-starter'); ?></h2>
        <ul class="meta-list">
            <li><?php esc_html_e('Theme ist minimal, responsiv und versionierbar aufgebaut.', 'webapp-central-starter'); ?></li>
            <li><?php esc_html_e('Eigene Erweiterungen liegen im custom/-Bereich ausserhalb des WordPress-Cores.', 'webapp-central-starter'); ?></li>
            <li><?php esc_html_e('Uploads, Core und Datenbank bleiben ausserhalb des Git-Repositories.', 'webapp-central-starter'); ?></li>
        </ul>
    </section>
    <section class="glass-panel">
        <h2 class="section-heading"><?php esc_html_e('Verantwortlich', 'webapp-central-starter'); ?></h2>
        <ul class="meta-list">
            <li>Mark Dorth</li>
            <li>Louis-Mannstaedt-Str. 64, 53840 Troisdorf</li>
            <li><a href="tel:+4915751444355">015751444355</a></li>
            <li><a href="mailto:dorth.mark@gmail.com">dorth.mark@gmail.com</a></li>
        </ul>
    </section>
</aside>
