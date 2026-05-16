<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/auth/account_store.php';

auth_require_login();
$user = auth_current_user();
$email = (string)($user['email'] ?? '');
$slug = trim((string)($_GET['slug'] ?? ''));

if ($slug === '') {
    http_response_code(404);
    exit('Seite nicht gefunden.');
}

$page = account_page_find_by_slug($email, $slug);
if (!is_array($page)) {
    http_response_code(404);
    exit('Seite nicht gefunden.');
}

if (empty($page['published'])) {
    http_response_code(403);
    exit('Seite ist nicht freigegeben.');
}

render_page((string)($page['title'] ?? 'Projektseite'), 'Projektseite', static function () use ($page): void {
    ?>
    <section class="card account-page-view">
      <span class="card-label">Projektseite</span>
      <h3><?= app_h((string)($page['title'] ?? 'Seite')) ?></h3>
      <div class="account-page-content">
        <?= nl2br(app_h((string)($page['content'] ?? ''))) ?>
      </div>
      <div class="auth-actions">
        <a class="btn btn-primary" href="<?= app_h(app_url('account.php')) ?>">Zurueck zu Mein Bereich</a>
      </div>
    </section>
    <?php
}, [
    'content_shell_class' => 'account-content-compact',
]);

