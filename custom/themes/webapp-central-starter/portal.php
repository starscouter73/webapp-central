<?php
declare(strict_types=1);

get_header();
?>
<div class="content-wrap">
    <main class="content-main">
        <?php webapp_central_starter_render_structured_page_article('portal', __('Portal', 'webapp-central-starter')); ?>
    </main>
    <?php get_sidebar(); ?>
</div>
<?php
get_footer();
