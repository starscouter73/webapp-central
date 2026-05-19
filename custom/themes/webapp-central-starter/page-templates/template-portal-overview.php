<?php
/**
 * Template Name: Portal Overview
 * Template Post Type: page
 */

declare(strict_types=1);

get_header();

$sections = webapp_central_starter_portal_sections();
$featured_hubs = webapp_central_starter_featured_project_hubs();
?>
<main class="portal-shell portal-shell--overview">
    <?php while (have_posts()) : the_post(); ?>
        <section class="portal-hero portal-hero--overview">
            <div class="portal-hero__content portal-panel">
                <span class="portal-badge"><?php esc_html_e('Portalstruktur', 'webapp-central-starter'); ?></span>
                <h1 class="portal-title"><?php the_title(); ?></h1>
                <p class="portal-lead">
                    <?php
                    $summary = trim((string) get_the_excerpt());
                    if ($summary === '') {
                        $summary = __('Moderne Geschaefts- und Projektzentrale fuer webapp-central.de mit klarem Fokus auf Projekte, Dokumentation, Medien und spaetere Analysen.', 'webapp-central-starter');
                    }
                    echo esc_html($summary);
                    ?>
                </p>
                <div class="portal-actions">
                    <a class="button-link" href="<?php echo esc_url(webapp_central_starter_portal_page_url('projekte/pfarrer-matthias-genster')); ?>"><?php esc_html_e('Projekt-Hub oeffnen', 'webapp-central-starter'); ?></a>
                    <a class="button-link button-link--ghost" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>"><?php esc_html_e('Portalinhalte pflegen', 'webapp-central-starter'); ?></a>
                </div>
            </div>
            <aside class="portal-hero__aside portal-panel">
                <h2><?php esc_html_e('Erste Ausbaustufe', 'webapp-central-starter'); ?></h2>
                <ul class="portal-activity-list">
                    <li><?php esc_html_e('Einfaches, stabiles Portal statt komplexem Projektmanagement.', 'webapp-central-starter'); ?></li>
                    <li><?php esc_html_e('Jede Hauptsektion ist bereits als spaeter erweiterbarer Bereich angelegt.', 'webapp-central-starter'); ?></li>
                    <li><?php esc_html_e('Projekt-Hubs koennen nach demselben Muster schrittweise weiter wachsen.', 'webapp-central-starter'); ?></li>
                </ul>
            </aside>
        </section>

        <?php if (trim((string) get_the_content()) !== '') : ?>
            <section class="portal-intro portal-panel">
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="portal-section">
            <div class="portal-section__heading">
                <span class="portal-badge portal-badge--soft"><?php esc_html_e('Navigation', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Portalbereiche', 'webapp-central-starter'); ?></h2>
            </div>
            <div class="portal-grid portal-grid--areas">
                <?php foreach ($sections as $section) : ?>
                    <article class="portal-card portal-card--area">
                        <div class="portal-card__meta">
                            <span class="portal-status"><?php echo esc_html($section['badge']); ?></span>
                        </div>
                        <h3><?php echo esc_html($section['title']); ?></h3>
                        <p><?php echo esc_html($section['description']); ?></p>
                        <a class="portal-card__link" href="<?php echo esc_url($section['url']); ?>"><?php esc_html_e('Bereich oeffnen', 'webapp-central-starter'); ?></a>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="portal-section">
            <div class="portal-section__heading">
                <span class="portal-badge portal-badge--soft"><?php esc_html_e('Projekt-Hubs', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Aktive Uebersichtsseiten', 'webapp-central-starter'); ?></h2>
            </div>
            <div class="portal-grid portal-grid--featured">
                <?php foreach ($featured_hubs as $card) : ?>
                    <?php get_template_part('template-parts/portal/project', 'card', ['card' => $card]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php
get_footer();
