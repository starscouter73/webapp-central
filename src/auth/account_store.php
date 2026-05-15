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
            ],
            'settings' => [
                'display_name' => '',
                'timezone' => 'Europe/Berlin',
            ],
            'documents' => [],
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

