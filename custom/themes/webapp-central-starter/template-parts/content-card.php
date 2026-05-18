<?php
declare(strict_types=1);
?>
<article <?php post_class('content-card'); ?>>
    <header>
        <div class="content-card__meta"><?php echo esc_html(get_the_date()); ?></div>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </header>
    <div class="entry-summary">
        <?php the_excerpt(); ?>
    </div>
    <div class="content-card__footer">
        <a class="button-link button-link--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e('Mehr lesen', 'webapp-central-starter'); ?></a>
    </div>
</article>
