<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';

if (auth_is_logged_in()) {
    auth_redirect('account.php');
}

$errors = [];
$success = false;
$emailInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailInput = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!auth_csrf_validate('register', $token)) {
        $errors[] = 'Die Anfrage ist abgelaufen. Bitte Formular neu senden.';
    } else {
        $result = auth_register_user($emailInput, $password, $confirmPassword);
        $errors = $result['errors'] ?? [];
        $success = (bool)($result['ok'] ?? false);

        if ($success) {
            auth_attempt_login($emailInput, $password);
            auth_redirect('account.php');
        }
    }
}

render_page('Registrieren', 'Zugang', static function () use ($errors, $success, $emailInput): void {
    ?>
    <section class="auth-shell">
      <article class="card auth-card">
        <span class="card-label">Registrieren</span>
        <h3>Neues Benutzerkonto anlegen</h3>
        <p>Der Zugang wird mit E-Mail und Passwort erstellt. Mindestlaenge: 8 Zeichen.</p>
        <?php if ($errors !== []): ?>
          <div class="auth-message is-error">
            <?= app_h(implode(' ', $errors)) ?>
          </div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="auth-message is-success">Registrierung erfolgreich. Weiterleitung in den Benutzerbereich.</div>
        <?php endif; ?>
        <form class="auth-form" method="post" action="<?= app_h(app_url('register.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('register')) ?>">

          <label for="register-email">E-Mail</label>
          <input id="register-email" name="email" type="email" required autocomplete="email" value="<?= app_h($emailInput) ?>">

          <label for="register-password">Passwort</label>
          <input id="register-password" name="password" type="password" required minlength="8" autocomplete="new-password">

          <label for="register-password-confirm">Passwort bestaetigen</label>
          <input id="register-password-confirm" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">

          <div class="auth-actions">
            <button class="btn btn-primary" type="submit">Konto erstellen</button>
            <a class="btn btn-ghost" href="<?= app_h(app_url('login.php')) ?>">Zum Login</a>
          </div>
        </form>
      </article>
    </section>
    <?php
});

