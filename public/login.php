<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

if (auth_is_logged_in()) {
    auth_redirect('account.php');
}

$next = basename((string)($_GET['next'] ?? 'account.php'));
$allowedNext = ['account.php', 'workspace.php', 'index.php'];
if (!in_array($next, $allowedNext, true)) {
    $next = 'account.php';
}

$errors = [];
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');
    $next = basename((string)($_POST['next'] ?? $next));
    if (!in_array($next, $allowedNext, true)) {
        $next = 'account.php';
    }

    if (!auth_csrf_validate('login', $token)) {
        $errors[] = 'Die Anfrage ist abgelaufen. Bitte Formular neu senden.';
    } elseif (!auth_attempt_login($emailInput, $password)) {
        $errors[] = 'Login fehlgeschlagen. Bitte Zugangsdaten pruefen.';
    } else {
        auth_redirect($next);
    }
}

render_page('Login', 'Zugang', static function () use ($errors, $emailInput, $next): void {
    ?>
    <section class="auth-shell">
      <article class="card auth-card">
        <span class="card-label">Login</span>
        <h3>Zugang zum geschuetzten Bereich</h3>
        <p>Bitte mit E-Mail-Adresse und Passwort anmelden.</p>
        <?php if ($errors !== []): ?>
          <div class="auth-message is-error">
            <?= app_h(implode(' ', $errors)) ?>
          </div>
        <?php endif; ?>
        <form class="auth-form" method="post" action="<?= app_h(app_url('login.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('login')) ?>">
          <input type="hidden" name="next" value="<?= app_h($next) ?>">
          <label for="login-email">E-Mail</label>
          <input id="login-email" name="email" type="email" required autocomplete="email" value="<?= app_h($emailInput) ?>">

          <label for="login-password">Passwort</label>
          <input id="login-password" name="password" type="password" required autocomplete="current-password">

          <div class="auth-actions">
            <button class="btn btn-primary" type="submit">Einloggen</button>
            <a class="btn btn-ghost" href="<?= app_h(app_url('register.php')) ?>">Registrieren</a>
          </div>
        </form>
      </article>
    </section>
    <?php
});

