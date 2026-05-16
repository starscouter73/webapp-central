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
                isset($_POST['page_published']),
                (string)($_POST['page_category_id'] ?? '')
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
        } elseif ($action === 'reorder_categories') {
            $order = trim((string)($_POST['category_order'] ?? ''));
            $ids = $order === '' ? [] : array_filter(array_map('trim', explode(',', $order)));
            if (account_categories_reorder($userEmail, $ids)) {
                $messages[] = 'Kategorienreihenfolge gespeichert.';
            }
        }
    }
}

$accountData = account_data_read($userEmail);
$documents = is_array($accountData['documents'] ?? null) ? $accountData['documents'] : [];
$projectPages = account_pages_list($userEmail);
$moduleCategories = account_categories_list($userEmail);
$moduleCount = 0;
foreach (['workspace', 'calendar', 'hallenberg'] as $moduleKey) {
    if (!empty($accountData['modules'][$moduleKey])) {
        $moduleCount++;
    }
}

render_page('Mein Bereich', 'Benutzerbereich', static function () use ($user, $accountData, $documents, $projectPages, $moduleCategories, $moduleCount, $messages, $errors): void {
    $bioText = trim((string)($accountData['settings']['bio'] ?? ''));
    $bioPreview = mb_strlen($bioText) > 260 ? mb_substr($bioText, 0, 260) . ' ...' : $bioText;
    ?>
    <section class="account-topbar">
      <div class="account-topbar-meta">
        <span class="account-avatar-frame">
          <img src="<?= app_h(app_url('account-avatar.php')) ?>" alt="Avatar" loading="lazy" onerror="this.style.display='none'; this.parentNode.classList.add('is-empty');">
        </span>
        <span class="card-label">Mein Bereich</span>
        <strong><?= app_h((string)($accountData['settings']['display_name'] ?? '') !== '' ? (string)$accountData['settings']['display_name'] : (string)($user['email'] ?? '')) ?></strong>
        <?php if ($bioText !== ''): ?>
          <p class="account-bio-preview"><?= app_h($bioPreview) ?></p>
          <?php if ($bioPreview !== $bioText): ?>
            <details class="readmore-card account-bio-readmore">
              <summary>Mehr lesen</summary>
              <div class="readmore-body">
                <p class="account-bio-full"><?= nl2br(app_h($bioText)) ?></p>
              </div>
            </details>
          <?php endif; ?>
        <?php endif; ?>
        <span class="auth-hint">Rolle: <?= app_h((string)($user['role'] ?? 'user')) ?></span>
      </div>
      <div class="auth-actions">
        <a class="btn btn-primary" href="<?= app_h(app_url('workspace.php')) ?>">Zur Zentrale</a>
        <a class="btn btn-ghost" href="<?= app_h(app_url('logout.php')) ?>">Logout</a>
      </div>
    </section>

    <?php foreach ($messages as $message): ?><div class="auth-message is-success"><?= app_h($message) ?></div><?php endforeach; ?>
    <?php foreach ($errors as $error): ?><div class="auth-message is-error"><?= app_h($error) ?></div><?php endforeach; ?>

    <section class="card account-matrix-shell">
      <span class="card-label">Mein Bereich Matrix</span>
      <h3>Alle Bereiche kompakt</h3>
      <div class="account-matrix-grid">
        <article class="account-matrix-item">
          <span class="account-matrix-title">Verwalten und sortieren</span>
          <details class="readmore-card account-accordion"><summary>Kategorien bearbeiten</summary><div class="readmore-body">
            <div class="account-page-list account-category-list" id="category-sort-list">
              <?php if ($moduleCategories === []): ?><p class="auth-hint">Noch keine Kategorien vorhanden.</p><?php endif; ?>
              <?php foreach ($moduleCategories as $category): ?>
                <article class="account-doc-item account-category-item" draggable="true" data-category-id="<?= app_h((string)($category['id'] ?? '')) ?>">
                  <strong><?= app_h((string)($category['name'] ?? 'Kategorie')) ?></strong>
                  <div class="auth-actions">
                    <a class="btn btn-ghost" href="<?= app_h(app_url('account-category.php?id=' . rawurlencode((string)($category['id'] ?? '')))) ?>">Oeffnen</a>
                    <form method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?= app_h((string)($category['id'] ?? '')) ?>"><button class="btn btn-ghost" type="submit">Loeschen</button></form>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
            <form method="post" action="<?= app_h(app_url('account.php')) ?>" id="category-order-form"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="reorder_categories"><input type="hidden" name="category_order" id="category_order" value=""><div class="auth-actions"><button class="btn btn-ghost" type="submit">Reihenfolge speichern</button></div></form>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Schnellanlage</span>
          <details class="readmore-card account-accordion"><summary>Neue Kategorie anlegen</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="add_category"><label for="category_name">Kategoriename</label><input id="category_name" name="category_name" type="text" placeholder="z. B. Kundenprojekte"><div class="auth-actions"><button class="btn btn-secondary" type="submit">Kategorie anlegen</button></div></form>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 1</span>
          <details class="readmore-card account-accordion"><summary>Fokus setzen</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="save_dashboard"><label for="favorite_section">Favorisierte Sektion</label><input id="favorite_section" name="favorite_section" type="text" value="<?= app_h((string)($accountData['dashboard']['favorite_section'] ?? '')) ?>"><label for="focus_note">Aktueller Fokus</label><input id="focus_note" name="focus_note" type="text" value="<?= app_h((string)($accountData['dashboard']['focus_note'] ?? '')) ?>"><div class="auth-actions"><button class="btn btn-secondary" type="submit">Speichern</button></div></form>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 2</span>
          <details class="readmore-card account-accordion"><summary>Dateien verwalten</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="upload_document"><label for="document_file">Dokument hochladen</label><input id="document_file" name="document_file" type="file" required><div class="auth-actions"><button class="btn btn-secondary" type="submit">Upload</button></div></form>
            <div class="account-doc-list"><?php foreach ($documents as $document): ?><article class="account-doc-item"><strong><?= app_h((string)($document['original_name'] ?? 'Datei')) ?></strong><span><?= app_h((string)($document['uploaded_at'] ?? '')) ?></span><div class="auth-actions"><a class="btn btn-ghost" href="<?= app_h(app_url('account-file.php?id=' . rawurlencode((string)($document['id'] ?? '')))) ?>">Oeffnen</a><form method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="delete_document"><input type="hidden" name="document_id" value="<?= app_h((string)($document['id'] ?? '')) ?>"><button class="btn btn-ghost" type="submit">Loeschen</button></form></div></article><?php endforeach; ?></div>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 3</span>
          <details class="readmore-card account-accordion"><summary>Projektseiten</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="save_page"><label for="page_title">Seitentitel</label><input id="page_title" name="page_title" type="text"><label for="page_content">Inhalt</label><textarea id="page_content" name="page_content" rows="4"></textarea><label for="page_category_id">Kategorie</label><select id="page_category_id" name="page_category_id"><option value="">Ohne Kategorie</option><?php foreach ($moduleCategories as $category): ?><option value="<?= app_h((string)($category['id'] ?? '')) ?>"><?= app_h((string)($category['name'] ?? 'Kategorie')) ?></option><?php endforeach; ?></select><label><input type="checkbox" name="page_published" checked> Freigeben</label><div class="auth-actions"><button class="btn btn-secondary" type="submit">Seite anlegen</button></div></form>
            <div class="account-page-list"><?php foreach ($projectPages as $page): ?><article class="account-doc-item"><strong><?= app_h((string)($page['title'] ?? 'Seite')) ?></strong><span><?= app_h((string)($page['slug'] ?? '')) ?> · <?= !empty($page['published']) ? 'veroeffentlicht' : 'entwurf' ?></span><div class="auth-actions"><?php if (!empty($page['published'])): ?><a class="btn btn-ghost" href="<?= app_h(app_url('account-page.php?slug=' . rawurlencode((string)($page['slug'] ?? '')))) ?>">Oeffnen</a><?php endif; ?><form method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="delete_page"><input type="hidden" name="page_id" value="<?= app_h((string)($page['id'] ?? '')) ?>"><button class="btn btn-ghost" type="submit">Loeschen</button></form></div></article><?php endforeach; ?></div>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 4</span>
          <details class="readmore-card account-accordion"><summary>Modulfokus</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="save_modules"><label><input type="checkbox" name="module_workspace" <?= !empty($accountData['modules']['workspace']) ? 'checked' : '' ?>> Zentrale</label><label><input type="checkbox" name="module_calendar" <?= !empty($accountData['modules']['calendar']) ? 'checked' : '' ?>> Kalender</label><label><input type="checkbox" name="module_hallenberg" <?= !empty($accountData['modules']['hallenberg']) ? 'checked' : '' ?>> Hallenberg</label><label for="module_notes">Modulnotiz</label><input id="module_notes" name="module_notes" type="text" value="<?= app_h((string)($accountData['modules']['notes'] ?? '')) ?>"><p class="auth-hint">Aktivierte Kernmodule: <?= app_h((string)$moduleCount) ?></p><div class="auth-actions"><button class="btn btn-secondary" type="submit">Speichern</button></div></form>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 5</span>
          <details class="readmore-card account-accordion"><summary>Profilwerte</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>" enctype="multipart/form-data"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="save_settings"><label for="display_name">Anzeigename</label><input id="display_name" name="display_name" type="text" value="<?= app_h((string)($accountData['settings']['display_name'] ?? '')) ?>"><label for="bio">Bio</label><textarea id="bio" name="bio" rows="3"><?= app_h((string)($accountData['settings']['bio'] ?? '')) ?></textarea><label for="avatar_file">Avatar</label><input id="avatar_file" name="avatar_file" type="file" accept=".jpg,.jpeg,.png,.webp"><label for="timezone">Zeitzone</label><input id="timezone" name="timezone" type="text" value="<?= app_h((string)($accountData['settings']['timezone'] ?? 'Europe/Berlin')) ?>"><div class="auth-actions"><button class="btn btn-secondary" type="submit">Speichern</button></div></form>
          </div></details>
        </article>

        <article class="account-matrix-item">
          <span class="account-matrix-title">Bereich 6</span>
          <details class="readmore-card account-accordion"><summary>Sicherheit</summary><div class="readmore-body">
            <form class="auth-form" method="post" action="<?= app_h(app_url('account.php')) ?>"><input type="hidden" name="csrf_token" value="<?= app_h(auth_csrf_token('account_actions')) ?>"><input type="hidden" name="action" value="change_password"><label for="current_password">Aktuelles Passwort</label><input id="current_password" name="current_password" type="password" required><label for="new_password">Neues Passwort</label><input id="new_password" name="new_password" type="password" required minlength="8"><label for="confirm_password">Bestaetigen</label><input id="confirm_password" name="confirm_password" type="password" required minlength="8"><div class="auth-actions"><button class="btn btn-secondary" type="submit">Aendern</button></div></form>
          </div></details>
        </article>
      </div>
    </section>

    <script>
      (function () {
        var list = document.getElementById('category-sort-list');
        var orderInput = document.getElementById('category_order');
        if (!list || !orderInput) {
          return;
        }
        var dragItem = null;
        list.querySelectorAll('.account-category-item').forEach(function (item) {
          item.addEventListener('dragstart', function () { dragItem = item; item.classList.add('is-dragging'); });
          item.addEventListener('dragend', function () { item.classList.remove('is-dragging'); dragItem = null; updateOrder(); });
          item.addEventListener('dragover', function (event) {
            event.preventDefault();
            if (!dragItem || dragItem === item) return;
            var rect = item.getBoundingClientRect();
            var before = event.clientY < rect.top + rect.height / 2;
            if (before) { list.insertBefore(dragItem, item); } else { list.insertBefore(dragItem, item.nextSibling); }
          });
        });
        function updateOrder() {
          var ids = [];
          list.querySelectorAll('.account-category-item').forEach(function (item) { ids.push(item.getAttribute('data-category-id') || ''); });
          orderInput.value = ids.filter(Boolean).join(',');
        }
        updateOrder();
      }());
    </script>
    <?php
}, [
    'show_breadcrumb' => false,
    'content_shell_class' => 'account-content-compact',
]);

