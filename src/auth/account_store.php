<?php
declare(strict_types=1);

require_once __DIR__ . '/user_store.php';

if (!function_exists('account_user_key')) {
    function account_user_key(string $email): string
    {
        return sha1(strtolower(trim($email)));
    }
}

if (!function_exists('account_user_dir')) {
    function account_user_dir(string $email): string
    {
        $dir = auth_data_dir() . '/account/' . account_user_key($email);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }
}

if (!function_exists('account_user_documents_dir')) {
    function account_user_documents_dir(string $email): string
    {
        $dir = account_user_dir($email) . '/documents';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir;
    }
}

if (!function_exists('account_data_file')) {
    function account_data_file(string $email): string
    {
        return account_user_dir($email) . '/profile.json';
    }
}

if (!function_exists('account_default_data')) {
    function account_default_data(): array
    {
        return [
            'dashboard' => [
                'favorite_section' => '',
                'focus_note' => '',
            ],
            'modules' => [
                'workspace' => true,
                'calendar' => true,
                'hallenberg' => true,
                'notes' => '',
                'categories' => [],
            ],
            'settings' => [
                'display_name' => '',
                'timezone' => 'Europe/Berlin',
                'bio' => '',
                'avatar_file' => '',
            ],
            'documents' => [],
            'pages' => [],
        ];
    }
}

if (!function_exists('account_data_read')) {
    function account_data_read(string $email): array
    {
        $file = account_data_file($email);
        $default = account_default_data();
        if (!is_file($file)) {
            return $default;
        }

        $raw = (string)file_get_contents($file);
        if ($raw === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $default;
        }

        return array_replace_recursive($default, $decoded);
    }
}

if (!function_exists('account_data_write')) {
    function account_data_write(string $email, array $data): void
    {
        $file = account_data_file($email);
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException('Account directory is not writable.');
        }

        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('account_document_add')) {
    function account_document_add(string $email, array $uploaded): array
    {
        if (($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Datei konnte nicht hochgeladen werden.'];
        }

        $tmp = (string)($uploaded['tmp_name'] ?? '');
        $name = (string)($uploaded['name'] ?? '');
        $size = (int)($uploaded['size'] ?? 0);
        if ($tmp === '' || $name === '' || $size <= 0) {
            return ['ok' => false, 'error' => 'Datei ist ungueltig.'];
        }
        if ($size > 25 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'Datei ist zu gross (maximal 25 MB).'];
        }

        $extension = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'md', 'jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            return ['ok' => false, 'error' => 'Dateityp ist nicht erlaubt.'];
        }

        $storageName = date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
        $destination = account_user_documents_dir($email) . '/' . $storageName;
        if (!@move_uploaded_file($tmp, $destination)) {
            return ['ok' => false, 'error' => 'Datei konnte nicht gespeichert werden.'];
        }

        $data = account_data_read($email);
        $documents = is_array($data['documents'] ?? null) ? $data['documents'] : [];
        $documents[] = [
            'id' => bin2hex(random_bytes(8)),
            'original_name' => $name,
            'storage_name' => $storageName,
            'size' => filesize($destination) ?: $size,
            'uploaded_at' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format(DateTimeInterface::ATOM),
        ];
        $data['documents'] = $documents;
        account_data_write($email, $data);

        return ['ok' => true, 'error' => ''];
    }
}

if (!function_exists('account_document_delete')) {
    function account_document_delete(string $email, string $documentId): bool
    {
        $data = account_data_read($email);
        $documents = is_array($data['documents'] ?? null) ? $data['documents'] : [];
        $newDocuments = [];
        $deleted = false;

        foreach ($documents as $document) {
            if (!is_array($document)) {
                continue;
            }
            if ((string)($document['id'] ?? '') === $documentId) {
                $path = account_user_documents_dir($email) . '/' . (string)($document['storage_name'] ?? '');
                if (is_file($path)) {
                    @unlink($path);
                }
                $deleted = true;
                continue;
            }
            $newDocuments[] = $document;
        }

        if ($deleted) {
            $data['documents'] = $newDocuments;
            account_data_write($email, $data);
        }

        return $deleted;
    }
}

if (!function_exists('account_document_find')) {
    function account_document_find(string $email, string $documentId): ?array
    {
        $data = account_data_read($email);
        $documents = is_array($data['documents'] ?? null) ? $data['documents'] : [];
        foreach ($documents as $document) {
            if (!is_array($document)) {
                continue;
            }
            if ((string)($document['id'] ?? '') === $documentId) {
                return $document;
            }
        }
        return null;
    }
}

if (!function_exists('account_avatar_upload')) {
    function account_avatar_upload(string $email, array $uploaded): array
    {
        if (($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'error' => ''];
        }
        if (($uploaded['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'Avatar konnte nicht hochgeladen werden.'];
        }

        $tmp = (string)($uploaded['tmp_name'] ?? '');
        $name = (string)($uploaded['name'] ?? '');
        $size = (int)($uploaded['size'] ?? 0);
        if ($tmp === '' || $name === '' || $size <= 0) {
            return ['ok' => false, 'error' => 'Avatar ist ungueltig.'];
        }
        if ($size > 8 * 1024 * 1024) {
            return ['ok' => false, 'error' => 'Avatar ist zu gross (maximal 8 MB).'];
        }

        $extension = strtolower((string)pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowed, true)) {
            return ['ok' => false, 'error' => 'Avatar-Dateityp ist nicht erlaubt.'];
        }

        $fileName = 'avatar.' . $extension;
        $path = account_user_dir($email) . '/' . $fileName;
        if (!@move_uploaded_file($tmp, $path)) {
            return ['ok' => false, 'error' => 'Avatar konnte nicht gespeichert werden.'];
        }

        $data = account_data_read($email);
        $data['settings']['avatar_file'] = $fileName;
        account_data_write($email, $data);

        return ['ok' => true, 'error' => ''];
    }
}

if (!function_exists('account_avatar_path')) {
    function account_avatar_path(string $email): ?string
    {
        $data = account_data_read($email);
        $file = (string)($data['settings']['avatar_file'] ?? '');
        if ($file === '') {
            return null;
        }
        $path = account_user_dir($email) . '/' . $file;
        return is_file($path) ? $path : null;
    }
}

if (!function_exists('account_pages_list')) {
    function account_pages_list(string $email): array
    {
        $data = account_data_read($email);
        $pages = $data['pages'] ?? [];
        return is_array($pages) ? $pages : [];
    }
}

if (!function_exists('account_page_slugify')) {
    function account_page_slugify(string $value): string
    {
        $value = strtolower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        $value = trim($value, '-');
        return $value !== '' ? $value : 'seite-' . date('YmdHis');
    }
}

if (!function_exists('account_page_save')) {
    function account_page_save(string $email, string $title, string $content, bool $published, string $categoryId = ''): array
    {
        $title = trim($title);
        $content = trim($content);
        if ($title === '') {
            return ['ok' => false, 'error' => 'Seitentitel fehlt.'];
        }
        if (mb_strlen($content) < 20) {
            return ['ok' => false, 'error' => 'Seiteninhalt ist zu kurz (mind. 20 Zeichen).'];
        }

        $data = account_data_read($email);
        $pages = is_array($data['pages'] ?? null) ? $data['pages'] : [];
        $slug = account_page_slugify($title);
        $baseSlug = $slug;
        $idx = 1;
        $existingSlugs = array_map(static fn(array $p): string => (string)($p['slug'] ?? ''), $pages);
        while (in_array($slug, $existingSlugs, true)) {
            $slug = $baseSlug . '-' . $idx;
            $idx++;
        }

        $pages[] = [
            'id' => bin2hex(random_bytes(8)),
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'published' => $published,
            'category_id' => trim($categoryId),
            'updated_at' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format(DateTimeInterface::ATOM),
        ];
        $data['pages'] = $pages;
        account_data_write($email, $data);

        return ['ok' => true, 'error' => '', 'slug' => $slug];
    }
}

if (!function_exists('account_page_delete')) {
    function account_page_delete(string $email, string $pageId): bool
    {
        $data = account_data_read($email);
        $pages = is_array($data['pages'] ?? null) ? $data['pages'] : [];
        $newPages = [];
        $deleted = false;
        foreach ($pages as $page) {
            if (!is_array($page)) {
                continue;
            }
            if ((string)($page['id'] ?? '') === $pageId) {
                $deleted = true;
                continue;
            }
            $newPages[] = $page;
        }
        if ($deleted) {
            $data['pages'] = $newPages;
            account_data_write($email, $data);
        }
        return $deleted;
    }
}

if (!function_exists('account_page_find_by_slug')) {
    function account_page_find_by_slug(string $email, string $slug): ?array
    {
        foreach (account_pages_list($email) as $page) {
            if (!is_array($page)) {
                continue;
            }
            if ((string)($page['slug'] ?? '') === $slug) {
                return $page;
            }
        }
        return null;
    }
}

if (!function_exists('account_category_add')) {
    function account_category_add(string $email, string $name): array
    {
        $name = trim($name);
        if ($name === '') {
            return ['ok' => false, 'error' => 'Kategoriename fehlt.'];
        }

        $data = account_data_read($email);
        $categories = is_array($data['modules']['categories'] ?? null) ? $data['modules']['categories'] : [];
        foreach ($categories as $category) {
            if (strtolower((string)($category['name'] ?? '')) === strtolower($name)) {
                return ['ok' => false, 'error' => 'Kategorie existiert bereits.'];
            }
        }

        $categories[] = [
            'id' => bin2hex(random_bytes(6)),
            'name' => $name,
            'sort_order' => count($categories) + 1,
            'created_at' => (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format(DateTimeInterface::ATOM),
        ];
        $data['modules']['categories'] = $categories;
        account_data_write($email, $data);

        return ['ok' => true, 'error' => ''];
    }
}

if (!function_exists('account_category_delete')) {
    function account_category_delete(string $email, string $categoryId): bool
    {
        $data = account_data_read($email);
        $categories = is_array($data['modules']['categories'] ?? null) ? $data['modules']['categories'] : [];
        $newCategories = [];
        $deleted = false;

        foreach ($categories as $category) {
            if (!is_array($category)) {
                continue;
            }
            if ((string)($category['id'] ?? '') === $categoryId) {
                $deleted = true;
                continue;
            }
            $newCategories[] = $category;
        }

        if ($deleted) {
            $data['modules']['categories'] = $newCategories;
            account_data_write($email, $data);
        }

        return $deleted;
    }
}

if (!function_exists('account_categories_list')) {
    function account_categories_list(string $email): array
    {
        $data = account_data_read($email);
        $categories = is_array($data['modules']['categories'] ?? null) ? $data['modules']['categories'] : [];
        usort($categories, static function (array $a, array $b): int {
            return (int)($a['sort_order'] ?? 9999) <=> (int)($b['sort_order'] ?? 9999);
        });
        return $categories;
    }
}

if (!function_exists('account_categories_reorder')) {
    function account_categories_reorder(string $email, array $orderedIds): bool
    {
        $data = account_data_read($email);
        $categories = is_array($data['modules']['categories'] ?? null) ? $data['modules']['categories'] : [];
        if ($categories === []) {
            return false;
        }

        $orderMap = [];
        $pos = 1;
        foreach ($orderedIds as $id) {
            $id = trim((string)$id);
            if ($id === '') {
                continue;
            }
            $orderMap[$id] = $pos;
            $pos++;
        }

        foreach ($categories as &$category) {
            $id = (string)($category['id'] ?? '');
            if ($id !== '' && isset($orderMap[$id])) {
                $category['sort_order'] = $orderMap[$id];
            } else {
                $category['sort_order'] = $pos++;
            }
        }
        unset($category);

        usort($categories, static function (array $a, array $b): int {
            return (int)($a['sort_order'] ?? 9999) <=> (int)($b['sort_order'] ?? 9999);
        });

        $data['modules']['categories'] = $categories;
        account_data_write($email, $data);
        return true;
    }
}

if (!function_exists('account_category_find')) {
    function account_category_find(string $email, string $categoryId): ?array
    {
        foreach (account_categories_list($email) as $category) {
            if ((string)($category['id'] ?? '') === $categoryId) {
                return $category;
            }
        }
        return null;
    }
}
