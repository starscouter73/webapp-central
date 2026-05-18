<?php
declare(strict_types=1);

get_header();
?>
<div class="content-wrap">
    <main class="content-main">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('glass-panel page-shell'); ?>>
                <header class="entry-header">
                    <span class="eyebrow"><?php esc_html_e('Seite', 'webapp-central-starter'); ?></span>
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>
                <div class="entry-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
