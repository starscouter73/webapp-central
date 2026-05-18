<?php
declare(strict_types=1);

get_header();
?>
<div class="content-wrap">
    <main class="content-main">
        <section class="glass-panel archive-shell">
            <header class="entry-header">
                <span class="eyebrow"><?php esc_html_e('Archiv', 'webapp-central-starter'); ?></span>
                <h1 class="entry-title"><?php the_archive_title(); ?></h1>
                <?php the_archive_description('<div class="entry-summary">', '</div>'); ?>
            </header>
            <?php if (have_posts()) : ?>
                <div class="post-grid">
                    <?php while (have_posts()) : the_post(); ?>
                        <?php get_template_part('template-parts/content', 'card'); ?>
                    <?php endwhile; ?>
                </div>
                <div class="pagination"><?php the_posts_pagination(); ?></div>
            <?php else : ?>
                <p class="empty-state"><?php esc_html_e('Keine Inhalte in diesem Archiv gefunden.', 'webapp-central-starter'); ?></p>
            <?php endif; ?>
        </section>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
