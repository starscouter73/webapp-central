<?php
/**
 * Template Name: Project Hub
 * Template Post Type: page
 */

declare(strict_types=1);

get_header();
?>
<main class="portal-shell portal-shell--project">
    <?php while (have_posts()) : the_post(); ?>
        <?php
        $slug = (string) get_post_field('post_name', get_the_ID());
        $cards = webapp_central_starter_project_hub_cards($slug);
        $activities = webapp_central_starter_project_hub_activities($slug);
        ?>
        <section class="portal-hero portal-hero--project">
            <div class="portal-hero__content portal-panel">
                <span class="portal-badge"><?php esc_html_e('Projekt-Hub', 'webapp-central-starter'); ?></span>
                <h1 class="portal-title"><?php the_title(); ?></h1>
                <p class="portal-lead">
                    <?php
                    $summary = trim((string) get_the_excerpt());
                    if ($summary === '') {
                        $summary = __('Zentrale Uebersichtsseite fuer Themen, Dokumentationen, Medien, Organisation und spaetere Analysen.', 'webapp-central-starter');
                    }
                    echo esc_html($summary);
                    ?>
                </p>
                <div class="portal-actions">
                    <a class="button-link" href="<?php echo esc_url(webapp_central_starter_portal_page_url('projekte')); ?>"><?php esc_html_e('Zu Projekte', 'webapp-central-starter'); ?></a>
                    <a class="button-link button-link--ghost" href="<?php echo esc_url(admin_url('post.php?post=' . get_the_ID() . '&action=edit')); ?>"><?php esc_html_e('Hub bearbeiten', 'webapp-central-starter'); ?></a>
                </div>
            </div>
            <aside class="portal-hero__aside portal-panel">
                <h2><?php esc_html_e('Letzte Aktivitaeten', 'webapp-central-starter'); ?></h2>
                <ul class="portal-activity-list">
                    <?php foreach ($activities as $activity) : ?>
                        <li><?php echo esc_html($activity); ?></li>
                    <?php endforeach; ?>
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
                <span class="portal-badge portal-badge--soft"><?php esc_html_e('Projektkarten', 'webapp-central-starter'); ?></span>
                <h2><?php esc_html_e('Arbeitsfelder und Themenbereiche', 'webapp-central-starter'); ?></h2>
            </div>
            <div class="portal-grid portal-grid--projects">
                <?php foreach ($cards as $card) : ?>
                    <?php get_template_part('template-parts/portal/project', 'card', ['card' => $card]); ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>
<?php
get_footer();
