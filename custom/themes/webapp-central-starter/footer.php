<?php
declare(strict_types=1);
?>
    <footer class="site-footer">
        <div class="site-footer__inner glass-panel">
            <p class="footer-note">
                <?php echo esc_html(get_bloginfo('name')); ?> |
                <?php esc_html_e('WordPress Projektzentrale mit Docker- und GitHub-freundlicher Struktur.', 'webapp-central-starter'); ?>
            </p>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
