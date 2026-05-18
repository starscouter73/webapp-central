<?php
declare(strict_types=1);
?>
<aside class="content-sidebar">
    <?php if (is_active_sidebar('primary-sidebar')) : ?>
        <?php dynamic_sidebar('primary-sidebar'); ?>
    <?php else : ?>
        <section class="glass-panel">
            <h2 class="section-heading"><?php esc_html_e('Projektstatus', 'webapp-central-starter'); ?></h2>
            <ul class="meta-list">
                <li><?php esc_html_e('Theme ist minimal, responsiv und versionierbar aufgebaut.', 'webapp-central-starter'); ?></li>
                <li><?php esc_html_e('Eigene Erweiterungen liegen im custom/-Bereich ausserhalb des WordPress-Cores.', 'webapp-central-starter'); ?></li>
                <li><?php esc_html_e('Uploads, Core und Datenbank bleiben ausserhalb des Git-Repositories.', 'webapp-central-starter'); ?></li>
            </ul>
        </section>
    <?php endif; ?>
</aside>
