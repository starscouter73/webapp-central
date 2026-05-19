<?php
declare(strict_types=1);

get_header();
?>
<main class="front-page-editor">
    <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('front-page-editor__content'); ?>>
            <?php the_content(); ?>
        </article>
    <?php endwhile; ?>
</main>
<?php
get_footer();
