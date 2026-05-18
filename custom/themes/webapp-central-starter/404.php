<?php
declare(strict_types=1);

get_header();
?>
<div class="content-wrap">
    <main class="content-main">
        <section class="glass-panel error-shell">
            <span class="eyebrow"><?php esc_html_e('404', 'webapp-central-starter'); ?></span>
            <h1 class="entry-title"><?php esc_html_e('Seite nicht gefunden', 'webapp-central-starter'); ?></h1>
            <p class="entry-summary"><?php esc_html_e('Die angeforderte Seite existiert nicht oder wurde verschoben.', 'webapp-central-starter'); ?></p>
            <p><a class="button-link" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Zur Startseite', 'webapp-central-starter'); ?></a></p>
        </section>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
