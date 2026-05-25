<?php
declare(strict_types=1);

get_header();
?>
<div class="content-wrap">
    <main class="content-main">
        <?php while (have_posts()) : the_post(); ?>
            <?php
            ob_start();
            the_content();
            $page_content = (string) ob_get_clean();
            webapp_central_starter_render_structured_page_article(
                (string) get_post_field('post_name', get_the_ID()),
                get_the_title(),
                $page_content
            );
            ?>
        <?php endwhile; ?>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
