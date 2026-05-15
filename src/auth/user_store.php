<?php
declare(strict_types=1);

if (!function_exists('auth_data_dir')) {
    function auth_data_dir(): string
    {
        static $resolved = null;
        if (is_string($resolved)) {
            return $resolved;
        }

        $candidates = [
            dirname(__DIR__, 2) . '/data',
            '/var/www/storage',
            dirname(__DIR__, 2) . '/var/runtime',
            rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'webapp-central',
        ];

        foreach ($candidates as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
            if (is_dir($dir)) {
                @chmod($dir, 0775);
            }
            if (is_dir($dir) && is_writable($dir)) {
                $resolved = $dir;
                return $resolved;
            }
        }

        $resolved = $candidates[array_key_last($candidates)];
        return $resolved;
    }
}

if (!function_exists('auth_users_json_file')) {
    function auth_users_json_file(): string
    {
        return auth_data_dir() . '/users.json';
    }
}

if (!function_exists('auth_can_use_sqlite')) {
    function auth_can_use_sqlite(): bool
    {
        return class_exists('PDO') && in_array('sqlite', \PDO::getAvailableDrivers(), true);
    }
}

if (!function_exists('auth_users_sqlite_file')) {
    function auth_users_sqlite_file(): string
    {
        return auth_data_dir() . '/users.sqlite';
    }
}

if (!function_exists('auth_store_kind')) {
    function auth_store_kind(): string
    {
        static $store = null;
        if (is_string($store)) {
            return $store;
        }

        if (!auth_can_use_sqlite()) {
            $store = 'json';
            return $store;
        }

        try {
            auth_sqlite();
            $store = 'sqlite';
            return $store;
        } catch (Throwable) {
            $store = 'json';
            return $store;
        }
    }
}

if (!function_exists('auth_sqlite')) {
    function auth_sqlite(): \PDO
    {
        $dbFile = auth_users_sqlite_file();
        $dir = dirname($dbFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            throw new RuntimeException('SQLite directory is not writable.');
        }

        $pdo = new PDO('sqlite:' . $dbFile);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                role TEXT NOT NULL DEFAULT "user",
                created_at TEXT NOT NULL
            )'
        );

        return $pdo;
    }
}

if (!function_exists('auth_json_read')) {
    function auth_json_read(): array
    {
        $file = auth_users_json_file();
        if (!is_file($file)) {
            return [];
        }

        $raw = (string)file_get_contents($file);
        if ($raw === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }
}

if (!function_exists('auth_json_write')) {
    function auth_json_write(array $users): void
    {
        $file = auth_users_json_file();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            $fallbackDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'webapp-central';
            if (!is_dir($fallbackDir)) {
                @mkdir($fallbackDir, 0775, true);
            }
            if (!is_dir($fallbackDir) || !is_writable($fallbackDir)) {
                throw new RuntimeException('JSON directory is not writable.');
            }
            $file = $fallbackDir . DIRECTORY_SEPARATOR . 'users.json';
        }

        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('auth_user_find_by_email')) {
    function auth_user_find_by_email(string $email): ?array
    {
        if (auth_store_kind() === 'sqlite') {
            $pdo = auth_sqlite();
            $stmt = $pdo->prepare('SELECT id, email, password_hash, role, created_at FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($row) ? $row : null;
        }

        foreach (auth_json_read() as $user) {
            if (!is_array($user)) {
                continue;
            }
            if (strtolower((string)($user['email'] ?? '')) === strtolower($email)) {
                return $user;
            }
        }

        return null;
    }
}

if (!function_exists('auth_user_create')) {
    function auth_user_create(string $email, string $passwordHash, string $role = 'user'): bool
    {
        if (auth_user_find_by_email($email) !== null) {
            return false;
        }

        $createdAt = (new DateTimeImmutable('now', new DateTimeZone('Europe/Berlin')))->format(DateTimeInterface::ATOM);

        if (auth_store_kind() === 'sqlite') {
            $pdo = auth_sqlite();
            $stmt = $pdo->prepare(
                'INSERT INTO users (email, password_hash, role, created_at) VALUES (:email, :password_hash, :role, :created_at)'
            );
            return $stmt->execute([
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => $role,
                'created_at' => $createdAt,
            ]);
        }

        $users = auth_json_read();
        $maxId = 0;
        foreach ($users as $user) {
            $maxId = max($maxId, (int)($user['id'] ?? 0));
        }

        $users[] = [
            'id' => $maxId + 1,
            'email' => $email,
            'password_hash' => $passwordHash,
            'role' => $role,
            'created_at' => $createdAt,
        ];
        auth_json_write($users);
        return true;
    }
}

if (!function_exists('auth_user_update_password')) {
    function auth_user_update_password(string $email, string $passwordHash): bool
    {
        if (auth_store_kind() === 'sqlite') {
            $pdo = auth_sqlite();
            $stmt = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE email = :email');
            $stmt->execute([
                'password_hash' => $passwordHash,
                'email' => $email,
            ]);
            return $stmt->rowCount() > 0;
        }

        $users = auth_json_read();
        $updated = false;
        foreach ($users as &$user) {
            if (!is_array($user)) {
                continue;
            }
            if (strtolower((string)($user['email'] ?? '')) === strtolower($email)) {
                $user['password_hash'] = $passwordHash;
                $updated = true;
                break;
            }
        }
        unset($user);

        if ($updated) {
            auth_json_write($users);
        }

        return $updated;
    }
}

if (!function_exists('auth_user_set_role')) {
    function auth_user_set_role(string $email, string $role): bool
    {
        if (auth_store_kind() === 'sqlite') {
            $pdo = auth_sqlite();
            $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE email = :email');
            $stmt->execute([
                'role' => $role,
                'email' => $email,
            ]);
            return $stmt->rowCount() > 0;
        }

        $users = auth_json_read();
        $updated = false;
        foreach ($users as &$user) {
            if (!is_array($user)) {
                continue;
            }
            if (strtolower((string)($user['email'] ?? '')) === strtolower($email)) {
                $user['role'] = $role;
                $updated = true;
                break;
            }
        }
        unset($user);

        if ($updated) {
            auth_json_write($users);
        }

        return $updated;
    }
}
