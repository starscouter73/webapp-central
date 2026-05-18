<?php
declare(strict_types=1);

get_header();

$modules = webapp_central_starter_module_cards();
$recent_posts = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => 3,
]);
?>
<section class="hero">
    <div class="hero__intro glass-panel">
        <span class="eyebrow"><?php esc_html_e('Projektzentrale', 'webapp-central-starter'); ?></span>
        <h1><?php bloginfo('name'); ?></h1>
        <p class="hero__lead">
            <?php esc_html_e('Dieses Theme liefert eine minimale, stabile Basis fuer webapp-central.de: dunkel, modular, responsiv und bewusst schlank versionierbar.', 'webapp-central-starter'); ?>
        </p>
        <div class="hero__actions">
            <a class="button-link" href="<?php echo esc_url(admin_url()); ?>"><?php esc_html_e('WordPress Dashboard', 'webapp-central-starter'); ?></a>
            <a class="button-link button-link--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>"><?php esc_html_e('Seiten bearbeiten', 'webapp-central-starter'); ?></a>
        </div>
        <div class="hero__stats">
            <div class="hero__stat">
                <strong><?php echo esc_html((string) wp_count_posts('page')->publish); ?></strong>
                <span><?php esc_html_e('Seiten', 'webapp-central-starter'); ?></span>
            </div>
            <div class="hero__stat">
                <strong><?php echo esc_html((string) wp_count_posts('post')->publish); ?></strong>
                <span><?php esc_html_e('Beitraege', 'webapp-central-starter'); ?></span>
            </div>
            <div class="hero__stat">
                <strong><?php echo esc_html((string) count(wp_get_nav_menus())); ?></strong>
                <span><?php esc_html_e('Menues', 'webapp-central-starter'); ?></span>
            </div>
        </div>
    </div>
    <aside class="hero__aside glass-panel">
        <h2><?php esc_html_e('Naechste Schritte', 'webapp-central-starter'); ?></h2>
        <ul>
            <li><?php esc_html_e('Theme pruefen, aber erst nach Abschluss der Installation aktivieren.', 'webapp-central-starter'); ?></li>
            <li><?php esc_html_e('Startseite, Module und Projektbereiche als Seitenstruktur aufbauen.', 'webapp-central-starter'); ?></li>
            <li><?php esc_html_e('Eigene Plugins und Snippets weiter im versionierten custom/-Bereich pflegen.', 'webapp-central-starter'); ?></li>
        </ul>
    </aside>
</section>

<section class="layout-grid">
    <div class="module-grid">
        <?php foreach ($modules as $module) : ?>
            <article class="module-card">
                <h3><?php echo esc_html($module['title']); ?></h3>
                <p><?php echo esc_html($module['description']); ?></p>
                <div class="module-card__footer">
                    <a class="button-link button-link--ghost" href="<?php echo esc_url($module['url']); ?>"><?php echo esc_html($module['label']); ?></a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="post-grid">
        <?php if ($recent_posts !== []) : ?>
            <?php foreach ($recent_posts as $post) : ?>
                <?php setup_postdata($post); ?>
                <?php get_template_part('template-parts/content', 'card'); ?>
            <?php endforeach; ?>
            <?php wp_reset_postdata(); ?>
        <?php else : ?>
            <article class="content-card">
                <h2><?php esc_html_e('Noch keine Inhalte vorhanden', 'webapp-central-starter'); ?></h2>
                <p class="empty-state"><?php esc_html_e('Nach der Installation kannst du hier erste Beitraege oder Projektmodule aufbauen.', 'webapp-central-starter'); ?></p>
            </article>
        <?php endif; ?>
    </div>
</section>
<?php
get_footer();
