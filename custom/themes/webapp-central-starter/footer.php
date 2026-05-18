<?php
declare(strict_types=1);

$footer_pages = [
    'impressum' => get_page_by_path('impressum'),
    'datenschutz' => get_page_by_path('datenschutz'),
    'kontakt' => get_page_by_path('kontakt'),
];
?>
    <footer class="site-footer">
        <div class="site-footer__inner glass-panel">
            <p class="footer-note">
                Copyright <?php echo esc_html((string) gmdate('Y')); ?> Mark Dorth |
                <a href="mailto:dorth.mark@gmail.com">dorth.mark@gmail.com</a>
            </p>
            <nav class="site-footer__links" aria-label="<?php echo esc_attr__('Footer Navigation', 'webapp-central-starter'); ?>">
                <ul>
                    <?php foreach ($footer_pages as $slug => $page) : ?>
                        <?php if ($page instanceof WP_Post) : ?>
                            <li><a href="<?php echo esc_url(get_permalink($page)); ?>"><?php echo esc_html(get_the_title($page)); ?></a></li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
