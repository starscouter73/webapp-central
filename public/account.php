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
            $data['settings']['bio'] = trim((string)($_POST['bio'] ?? ''));
            account_data_write($userEmail, $data);
            $avatarResult = account_avatar_upload($userEmail, $_FILES['avatar_file'] ?? []);
            if (($avatarResult['ok'] ?? false) !== true) {
                $errors[] = (string)($avatarResult['error'] ?? 'Avatar konnte nicht gespeichert werden.');
            }
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
        } elseif ($action === 'save_page') {
            $result = account_page_save(
                $userEmail,
                (string)($_POST['page_title'] ?? ''),
                (string)($_POST['page_content'] ?? ''),
                isset($_POST['page_published'])
            );
            if (($result['ok'] ?? false) === true) {
                $messages[] = 'Projektseite wurde angelegt.';
            } else {
                $errors[] = (string)($result['error'] ?? 'Projektseite konnte nicht angelegt werden.');
            }
        } elseif ($action === 'delete_page') {
            $pageId = trim((string)($_POST['page_id'] ?? ''));
            if ($pageId === '' || !account_page_delete($userEmail, $pageId)) {
                $errors[] = 'Projektseite konnte nicht geloescht werden.';
            } else {
                $messages[] = 'Projektseite wurde geloescht.';
            }
        } elseif ($action === 'add_category') {
            $result = account_category_add($userEmail, (string)($_POST['category_name'] ?? ''));
            if (($result['ok'] ?? false) === true) {
                $messages[] = 'Kategorie wurde angelegt.';
            } else {
                $errors[] = (string)($result['error'] ?? 'Kategorie konnte nicht angelegt werden.');
            }
        } elseif ($action === 'delete_category') {
            $categoryId = trim((string)($_POST['category_id'] ?? ''));
            if ($categoryId === '' || !account_category_delete($userEmail, $categoryId)) {
                $errors[] = 'Kategorie konnte nicht geloescht werden.';
            } else {
                $messages[] = 'Kategorie wurde geloescht.';
            }
        }
    }
}

$accountData = account_data_read($userEmail);
$documents = is_array($accountData['documents'] ?? null) ? $accountData['documents'] : [];
$projectPages = account_pages_list($userEmail);
$moduleCategories = is_array($accountData['modules']['categories'] ?? null) ? $accountData['modules']['categories'] : [];
$moduleCount = 0;
foreach (['workspace', 'calendar', 'hallenberg'] as $moduleKey) {
    if (!empty($accountData['modules'][$moduleKey])) {
        $moduleCount++;
    }
}

render_page('Mein Bereich', 'Benutzerbereich', static function () use ($user, $accountData, $documents, $projectPages, $moduleCategories, $moduleCount, $messages, $errors): void {
    ?>
    <section class="account-topbar">
      <div class="account-topbar-meta">
        <span class="account-avatar-frame">
          <img src="<?= app_h(app_url('account-avatar.php')) ?>" alt="Avatar" loading="lazy" onerror="this.style.display='none'; this.parentNode.classList.add('is-empty');">
        </span>
        <span class="card-label">Mein Bereich</span>
        <strong><?= app_h((string)($accountData['settings']['display_name'] ?? '') !== '' ? (string)$accountData['settings']['display_name'] : (string)($user['email'] ?? '')) ?></strong>
        <span class="auth-hint"><?= app_h((string)($accountData['settings']['bio'] ?? '')) ?></span>
        <span class="auth-hint">Rolle: <?= app_h((string)($user['role'] ?? 'user')) ?></span>
      </div>
      <div class="auth-actions">
        <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
        <a class="btn btn-ghost" href="<?= app_h(app_url('logout.php')) ?>">Logout</a>
      </div>
    </section>
    <?php foreach ($messages as $message): ?>
      <div class="auth-message is-success"><?= app_h($message) ?></div>
    <?php endforeach; ?>
    <?php foreach ($errors as $error): ?>
      <div class="auth-message is-error"><?= app_h($error) ?></div>
    <?php endforeach; ?>

    <section class="grid two-up">
      <article class="card">
        <span class="card-label">Kategorien</span>
        <h3>Deine Bereiche im Fokus</h3>
        <div class="account-page-list">
          <?php if ($moduleCategories === []): ?>
            <p class="auth-hint">Noch keine Kategorien vorhanden.</p>
          <?php endif; ?>
          <?php foreach ($moduleCategories as $category): ?>
            <article class="account-doc-item">
              <strong><?= app_h((string)($category['name'] ?? 'Kategorie')) ?></strong>
              <div class="auth-actions">
                <form method="post" action="<?= app_h(app_url('account.php')) ?>">
                  <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
                  <input type="hidden" name="action" value="delete_category">
                  <input type="hidden" name="category_id" value="<?= app_h((string)($category['id'] ?? '')) ?>">
                  <button class="btn btn-ghost" type="submit">Loeschen</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </article>
      <article class="card">
        <span class="card-label">Schnellanlage</span>
        <h3>Neue Kategorie anlegen</h3>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="add_category">
          <label for="category_name">Kategoriename</label>
          <input id="category_name" name="category_name" type="text" placeholder="z. B. Kundenprojekte, Planung, Reports">
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Kategorie anlegen</button>
          </div>
        </form>
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
        <h3>Projektseiten</h3>
        <p>Lege eigene Projektseiten an, wie im kleinen CMS: Titel, Inhalt, Freigabe.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="save_page">
          <label for="page_title">Seitentitel</label>
          <input id="page_title" name="page_title" type="text" placeholder="z. B. Projekt Hallenberg Q2">
          <label for="page_content">Inhalt</label>
          <textarea id="page_content" name="page_content" rows="4" placeholder="Kurze Projektseite mit den wichtigsten Infos."></textarea>
          <label><input type="checkbox" name="page_published" checked> Seite direkt freigeben</label>
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Seite anlegen</button>
          </div>
        </form>
        <div class="account-page-list">
          <?php if ($projectPages === []): ?>
            <p class="auth-hint">Noch keine Projektseiten vorhanden.</p>
          <?php endif; ?>
          <?php foreach ($projectPages as $page): ?>
            <article class="account-doc-item">
              <strong><?= app_h((string)($page['title'] ?? 'Seite')) ?></strong>
              <span>Slug: <?= app_h((string)($page['slug'] ?? '')) ?> · <?= !empty($page['published']) ? 'veroeffentlicht' : 'entwurf' ?></span>
              <div class="auth-actions">
                <?php if (!empty($page['published'])): ?>
                  <a class="btn btn-ghost" href="<?= app_h(app_url('account-page.php?slug=' . rawurlencode((string)($page['slug'] ?? '')))) ?>">Oeffnen</a>
                <?php endif; ?>
                <form method="post" action="<?= app_h(app_url('account.php')) ?>">
                  <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
                  <input type="hidden" name="action" value="delete_page">
                  <input type="hidden" name="page_id" value="<?= app_h((string)($page['id'] ?? '')) ?>">
                  <button class="btn btn-ghost" type="submit">Loeschen</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 4</span>
        <h3>Projektmodule</h3>
        <p>Lege fest, welche Module und Kategorien in deinem Fokus bleiben.</p>
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
        <span class="card-label">Bereich 5</span>
        <h3>Einstellungen</h3>
        <p>Pflege Anzeige- und Profilwerte fuer deinen Accountbereich.</p>
        <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>">
          <input type="hidden" name="action" value="save_settings">
          <label for="display_name">Anzeigename</label>
          <input id="display_name" name="display_name" type="text" value="<?= app_h((string)($accountData['settings']['display_name'] ?? '')) ?>" placeholder="z. B. Mark Dorth">
          <label for="bio">Bio</label>
          <textarea id="bio" name="bio" rows="3" placeholder="Kurzer Profiltext fuer deinen Bereich"><?= app_h((string)($accountData['settings']['bio'] ?? '')) ?></textarea>
          <label for="avatar_file">Avatar / Profilbild</label>
          <input id="avatar_file" name="avatar_file" type="file" accept=".jpg,.jpeg,.png,.webp">
          <label for="timezone">Zeitzone</label>
          <input id="timezone" name="timezone" type="text" value="<?= app_h((string)($accountData['settings']['timezone'] ?? 'Europe/Berlin')) ?>" placeholder="Europe/Berlin">
          <div class="auth-actions">
            <button class="btn btn-secondary" type="submit">Einstellungen speichern</button>
          </div>
        </form>
      </article>

      <article class="card account-card">
        <span class="card-label">Bereich 6</span>
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
}, [
    'show_breadcrumb' => false,
    'content_shell_class' => 'account-content-compact',
]);
