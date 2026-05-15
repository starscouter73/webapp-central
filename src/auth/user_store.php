<?php
declare(strict_types=1);

if (!function_exists('auth_data_dir')) {
    function auth_data_dir(): string
    {
        $dir = dirname(__DIR__, 2) . '/data';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        return $dir;
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
        return auth_can_use_sqlite() ? 'sqlite' : 'json';
    }
}

if (!function_exists('auth_sqlite')) {
    function auth_sqlite(): \PDO
    {
        $pdo = new PDO('sqlite:' . auth_users_sqlite_file());
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
        file_put_contents(auth_users_json_file(), json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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

