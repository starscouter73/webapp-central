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
        $project_slug = sanitize_key((string) ($_GET['projekt'] ?? ''));
        $project_detail = $project_slug !== '' ? webapp_central_starter_project_detail_data($slug, $project_slug) : null;
        $cards = webapp_central_starter_project_hub_cards($slug);
        $activities = webapp_central_starter_project_hub_activities($slug);
        ?>
        <?php if (is_array($project_detail)) : ?>
            <section class="portal-hero portal-hero--project-detail">
                <div class="portal-hero__content portal-panel">
                    <span class="portal-badge"><?php echo esc_html((string) $project_detail['eyebrow']); ?></span>
                    <h1 class="portal-title portal-title--detail"><?php echo esc_html((string) $project_detail['title']); ?></h1>
                    <p class="portal-lead"><?php echo esc_html((string) $project_detail['summary']); ?></p>
                    <div class="portal-actions">
                        <a class="button-link" href="<?php echo esc_url((string) $project_detail['back_url']); ?>"><?php esc_html_e('Zur Projektkarte', 'webapp-central-starter'); ?></a>
                    </div>
                    <div class="project-detail-stats">
                        <?php foreach (($project_detail['stats'] ?? []) as $stat) : ?>
                            <article class="project-detail-stat">
                                <strong><?php echo esc_html((string) ($stat['value'] ?? '')); ?></strong>
                                <span><?php echo esc_html((string) ($stat['label'] ?? '')); ?></span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <aside class="portal-hero__aside portal-panel project-detail-summary">
                    <div class="portal-card__meta">
                        <span class="portal-status"><?php echo esc_html((string) $project_detail['status']); ?></span>
                        <span class="portal-timestamp"><?php echo esc_html((string) $project_detail['updated']); ?></span>
                    </div>
                    <h2><?php esc_html_e('Projektliste', 'webapp-central-starter'); ?></h2>
                    <p><?php esc_html_e('Strukturierte Uebersicht der bisherigen Themen, Kleinprojekte und Tasks fuer die operative Begleitung.', 'webapp-central-starter'); ?></p>
                </aside>
            </section>

            <section class="portal-section">
                <div class="portal-section__heading">
                    <span class="portal-badge portal-badge--soft"><?php esc_html_e('Projekt-Dashboard', 'webapp-central-starter'); ?></span>
                    <h2><?php esc_html_e('Themen, Aufgaben und Dokumentationsspuren', 'webapp-central-starter'); ?></h2>
                </div>
                <div class="project-detail-grid">
                    <?php foreach (($project_detail['sections'] ?? []) as $section) : ?>
                        <article class="project-detail-card portal-card">
                            <div class="portal-card__meta">
                                <span class="portal-status"><?php echo esc_html((string) ($section['status'] ?? '')); ?></span>
                            </div>
                            <h3><?php echo esc_html((string) ($section['title'] ?? '')); ?></h3>
                            <ul class="project-detail-list">
                                <?php foreach (($section['items'] ?? []) as $item) : ?>
                                    <li><?php echo esc_html((string) $item); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else : ?>
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
        <?php endif; ?>
    <?php endwhile; ?>
</main>
<?php
get_footer();
