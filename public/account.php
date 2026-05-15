<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/auth/account_store.php';

auth_require_login();
$user = auth_current_user();
$userEmail = (string)($user['email'] ?? '');

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    $token = (string)($_POST['csrf_token'] ?? '');

    if (!auth_csrf_validate('account_actions', $token)) {
        $errors[] = 'Die Anfrage ist abgelaufen. Bitte erneut versuchen.';
    } else {
        $data = account_data_read($userEmail);

        if ($action === 'save_dashboard') {
            $data['dashboard']['favorite_section'] = trim((string)($_POST['favorite_section'] ?? ''));
            $data['dashboard']['focus_note'] = trim((string)($_POST['focus_note'] ?? ''));
            account_data_write($userEmail, $data);
            $messages[] = 'Persoenliches Dashboard gespeichert.';
        } elseif ($action === 'upload_document') {
            $result = account_document_add($userEmail, $_FILES['document_file'] ?? []);
            if (($result['ok'] ?? false) === true) {
                $messages[] = 'Dokument wurde hochgeladen.';
            } else {
                $errors[] = (string)($result['error'] ?? 'Upload fehlgeschlagen.');
            }
        } elseif ($action === 'delete_document') {
            $documentId = trim((string)($_POST['document_id'] ?? ''));
            if ($documentId === '' || !account_document_delete($userEmail, $documentId)) {
                $errors[] = 'Dokument konnte nicht geloescht werden.';
            } else {
                $messages[] = 'Dokument wurde geloescht.';
            }
        } elseif ($action === 'save_modules') {
            $data['modules']['workspace'] = isset($_POST['module_workspace']);
            $data['modules']['calendar'] = isset($_POST['module_calendar']);
            $data['modules']['hallenberg'] = isset($_POST['module_hallenberg']);
            $data['modules']['notes'] = trim((string)($_POST['module_notes'] ?? ''));
            account_data_write($userEmail, $data);
            $messages[] = 'Projektmodule gespeichert.';
        } elseif ($action === 'save_settings') {
            $data['settings']['display_name'] = trim((string)($_POST['display_name'] ?? ''));
            $data['settings']['timezone'] = trim((string)($_POST['timezone'] ?? 'Europe/Berlin'));
            account_data_write($userEmail, $data);
            $messages[] = 'Einstellungen gespeichert.';
        } elseif ($action === 'change_password') {
            $result = auth_change_password(
                $userEmail,
                (string)($_POST['current_password'] ?? ''),
                (string)($_POST['new_password'] ?? ''),
                (string)($_POST['confirm_password'] ?? '')
            );
            if (($result['ok'] ?? false) === true) {
                $messages[] = 'Passwort wurde aktualisiert.';
            } else {
                foreach (($result['errors'] ?? []) as $error) {
                    $errors[] = (string)$error;
                }
            }
        }
    }
}

$accountData = account_data_read($userEmail);
$documents = is_array($accountData['documents'] ?? null) ? $accountData['documents'] : [];
$moduleCount = 0;
foreach (['workspace', 'calendar', 'hallenberg'] as $moduleKey) {
    if (!empty($accountData['modules'][$moduleKey])) {
        $moduleCount++;
    }
}

render_page('Mein Bereich', 'Benutzerbereich', static function () use ($user, $accountData, $documents, $moduleCount, $messages, $errors): void {
    ?>
    <section class="auth-shell">
      <article class="card auth-card">
        <span class="card-label">Mein Bereich</span>
        <h3>Geschuetzter Nutzerzugang</h3>
        <p>Hier steuerst du persoenliche Inhalte, geschuetzte Dokumente, Moduleinstellungen und Sicherheit zentral an einer Stelle.</p>
        <div class="auth-message is-success">
          Eingeloggt als <?= app_h((string)($user['email'] ?? '')) ?> (Rolle: <?= app_h((string)($user['role'] ?? 'user')) ?>)
        </div>
        <?php foreach ($messages as $message): ?>
          <div class="auth-message is-success"><?= app_h($message) ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $error): ?>
          <div class="auth-message is-error"><?= app_h($error) ?></div>
        <?php endforeach; ?>
        <div class="auth-actions">
          <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
          <a class="btn btn-ghost" href="<?= app_h(app_url('logout.php')) ?>">Logout</a>
        </div>
      </article>
    </section>

    <section class="grid account-grid">
      <article class="card account-card">
        <span class="card-label">Bereich 1</span>
        <h3>Persoenliches Dashboard</h3>
        <p>Definiere Fokus und Schnellzugriff fuer deine naechsten Schritte.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="save_dashboard">
          <label for="favorite_section">Favorisierte Sektion</label>
          <input id="favorite_section" name="favorite_section" type="text" value="<?= app_h((string)($accountData['dashboard']['favorite_section'] ?? '')) ?>" placeholder="z. B. Hallenberg oder Kalender">
          <label for="focus_note">Aktueller Fokus</label>
          <input id="focus_note" name="focus_note" type="text" value="<?= app_h((string)($accountData['dashboard']['focus_note'] ?? '')) ?>" placeholder="z. B. Deploy, Medien, Doku">
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Dashboard speichern</button>
          </div>
        </form>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 2</span>
        <h3>Geschuetzte Dokumente</h3>
        <p>Dokumente hochladen, geschuetzt listen und intern wieder abrufen.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="upload_document">
          <label for="document_file">Dokument hochladen (max 25 MB)</label>
          <input id="document_file" name="document_file" type="file" required>
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Dokument hochladen</button>
          </div>
        </form>
        <div class="account-doc-list">
          <?php if ($documents === []): ?>
            <p class="auth-hint">Noch keine Dokumente vorhanden.</p>
          <?php endif; ?>
          <?php foreach ($documents as $document): ?>
            <article class="account-doc-item">
              <strong><?= app_h((string)($document['original_name'] ?? 'Datei')) ?></strong>
              <span><?= app_h((string)($document['uploaded_at'] ?? '')) ?></span>
              <div class="auth-actions">
                <a class="btn btn-ghost" href="<?= app_h(app_url('account-file.php?id=' . rawurlencode((string)($document['id'] ?? '')))) ?>">Oeffnen</a>
                <form method="post" action="<?= app_h(app_url('account.php')) ?>">
                  <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
                  <input type="hidden" name="action" value="delete_document">
                  <input type="hidden" name="document_id" value="<?= app_h((string)($document['id'] ?? '')) ?>">
                  <button class="btn btn-ghost" type="submit">Loeschen</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 3</span>
        <h3>Projektmodule</h3>
        <p>Lege fest, welche Module in deinem Fokus bleiben.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="save_modules">
          <label><input type="checkbox" name="module_workspace" <?= !empty($accountData['modules']['workspace']) ? 'checked' : '' ?>> Zentrale</label>
          <label><input type="checkbox" name="module_calendar" <?= !empty($accountData['modules']['calendar']) ? 'checked' : '' ?>> Kalender</label>
          <label><input type="checkbox" name="module_hallenberg" <?= !empty($accountData['modules']['hallenberg']) ? 'checked' : '' ?>> Hallenberg</label>
          <label for="module_notes">Modulnotiz</label>
          <input id="module_notes" name="module_notes" type="text" value="<?= app_h((string)($accountData['modules']['notes'] ?? '')) ?>" placeholder="z. B. als naechstes Kalender bearbeiten">
          <p class="auth-hint">Aktivierte Kernmodule: <?= app_h((string)$moduleCount) ?></p>
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Module speichern</button>
          </div>
        </form>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 4</span>
        <h3>Einstellungen</h3>
        <p>Pflege Anzeige- und Profilwerte fuer deinen Accountbereich.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="save_settings">
          <label for="display_name">Anzeigename</label>
          <input id="display_name" name="display_name" type="text" value="<?= app_h((string)($accountData['settings']['display_name'] ?? '')) ?>" placeholder="z. B. Mark Dorth">
          <label for="timezone">Zeitzone</label>
          <input id="timezone" name="timezone" type="text" value="<?= app_h((string)($accountData['settings']['timezone'] ?? 'Europe/Berlin')) ?>" placeholder="Europe/Berlin">
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Einstellungen speichern</button>
          </div>
        </form>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 5</span>
        <h3>Zugang und Sicherheit</h3>
        <p>Passwort aktualisieren und Zugang absichern.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="change_password">
          <label for="current_password">Aktuelles Passwort</label>
          <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
          <label for="new_password">Neues Passwort (min. 8 Zeichen)</label>
          <input id="new_password" name="new_password" type="password" required minlength="8" autocomplete="new-password">
          <label for="confirm_password">Neues Passwort bestaetigen</label>
          <input id="confirm_password" name="confirm_password" type="password" required minlength="8" autocomplete="new-password">
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Passwort aendern</button>
          </div>
        </form>
      </article>
    </section>
    <?php
});

