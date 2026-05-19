<?php
declare(strict_types=1);

$card = $args['card'] ?? null;

if (!is_array($card)) {
    return;
}
?>
<article class="portal-card portal-card--project">
    <div class="portal-card__visual" aria-hidden="true">
        <span><?php echo esc_html((string) ($card['visual'] ?? 'Projekt')); ?></span>
    </div>
    <div class="portal-card__meta">
        <span class="portal-status"><?php echo esc_html((string) ($card['status'] ?? 'Offen')); ?></span>
        <span class="portal-timestamp"><?php echo esc_html((string) ($card['activity'] ?? '')); ?></span>
    </div>
    <h3><?php echo esc_html((string) ($card['title'] ?? '')); ?></h3>
    <p><?php echo esc_html((string) ($card['description'] ?? '')); ?></p>
    <a class="portal-card__link" href="<?php echo esc_url((string) ($card['url'] ?? '#')); ?>">
        <?php echo esc_html((string) ($card['button'] ?? __('Oeffnen', 'webapp-central-starter'))); ?>
    </a>
</article>
