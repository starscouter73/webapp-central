<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/auth/account_store.php';

auth_require_login();
$user = auth_current_user();
$email = (string)($user['email'] ?? '');
$categoryId = trim((string)($_GET['id'] ?? ''));

if ($categoryId === '') {
    http_response_code(404);
    exit('Kategorie nicht gefunden.');
}

$category = account_category_find($email, $categoryId);
if (!is_array($category)) {
    http_response_code(404);
    exit('Kategorie nicht gefunden.');
}

$pages = array_values(array_filter(account_pages_list($email), static function (array $page) use ($categoryId): bool {
    return (string)($page['category_id'] ?? '') === $categoryId;
}));

render_page((string)($category['name'] ?? 'Kategorie'), 'Kategorie', static function () use ($category, $pages): void {
    ?>
    <section class="card account-page-view">
      <span class="card-label">Projektkategorie</span>
      <h3><?= app_h((string)($category['name'] ?? 'Kategorie')) ?></h3>
      <p>Alle zugeordneten Projektseiten in dieser Kategorie.</p>
      <div class="account-page-list">
        <?php if ($pages === []): ?>
          <p class="auth-hint">Noch keine Seiten in dieser Kategorie.</p>
        <?php endif; ?>
        <?php foreach ($pages as $page): ?>
          <article class="account-doc-item">
            <strong><?= app_h((string)($page['title'] ?? 'Seite')) ?></strong>
            <span><?= !empty($page['published']) ? 'veroeffentlicht' : 'entwurf' ?></span>
            <div class="auth-actions">
              <?php if (!empty($page['published'])): ?>
                <a class="btn btn-ghost" href="<?= app_h(app_url('account-page.php?slug=' . rawurlencode((string)($page['slug'] ?? '')))) ?>">Oeffnen</a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="auth-actions">
        <a class="btn btn-primary" href="<?= app_h(app_url('account.php')) ?>">Zurueck zu Mein Bereich</a>
      </div>
    </section>
    <?php
}, [
    'content_shell_class' => 'account-content-compact',
]);

