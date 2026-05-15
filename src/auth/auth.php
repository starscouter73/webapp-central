<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/user_store.php';

if (!function_exists('auth_normalize_email')) {
    function auth_normalize_email(string $email): string
    {
        return strtolower(trim($email));
    }
}

if (!function_exists('auth_validate_registration')) {
    function auth_validate_registration(string $email, string $password, string $confirmPassword): array
    {
        $errors = [];

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'Bitte eine gueltige E-Mail-Adresse eingeben.';
        }

        if (strlen($password) < 8) {
            $errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwort und Bestaetigung stimmen nicht ueberein.';
        }

        if (auth_user_find_by_email($email) !== null) {
            $errors[] = 'Diese E-Mail-Adresse ist bereits registriert.';
        }

        return $errors;
    }
}

if (!function_exists('auth_register_user')) {
    function auth_register_user(string $email, string $password, string $confirmPassword): array
    {
        $normalizedEmail = auth_normalize_email($email);
        $errors = auth_validate_registration($normalizedEmail, $password, $confirmPassword);
        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (!is_string($passwordHash) || $passwordHash === '') {
            return ['ok' => false, 'errors' => ['Registrierung konnte nicht abgeschlossen werden.']];
        }

        $created = auth_user_create($normalizedEmail, $passwordHash, 'user');
        if (!$created) {
            return ['ok' => false, 'errors' => ['Registrierung konnte nicht abgeschlossen werden.']];
        }

        return ['ok' => true, 'errors' => []];
    }
}

if (!function_exists('auth_attempt_login')) {
    function auth_attempt_login(string $email, string $password): bool
    {
        $normalizedEmail = auth_normalize_email($email);
        if ($normalizedEmail === '' || $password === '') {
            return false;
        }

        $user = auth_user_find_by_email($normalizedEmail);
        if (!is_array($user)) {
            return false;
        }

        $hash = (string)($user['password_hash'] ?? '');
        if ($hash === '' || !password_verify($password, $hash)) {
            return false;
        }

        auth_session_start();
        session_regenerate_id(true);
        auth_session_set('auth_user', [
            'id' => (int)($user['id'] ?? 0),
            'email' => (string)($user['email'] ?? $normalizedEmail),
            'role' => (string)($user['role'] ?? 'user'),
        ]);
        return true;
    }
}

if (!function_exists('auth_current_user')) {
    function auth_current_user(): ?array
    {
        $user = auth_session_get('auth_user');
        return is_array($user) ? $user : null;
    }
}

if (!function_exists('auth_is_logged_in')) {
    function auth_is_logged_in(): bool
    {
        return auth_current_user() !== null;
    }
}

if (!function_exists('auth_logout')) {
    function auth_logout(): void
    {
        auth_session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool)($params['secure'] ?? false),
                'httponly' => (bool)($params['httponly'] ?? true),
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        session_destroy();
    }
}

if (!function_exists('auth_redirect')) {
    function auth_redirect(string $path): never
    {
        header('Location: ' . app_url(ltrim($path, '/')));
        exit;
    }
}

if (!function_exists('auth_require_login')) {
    function auth_require_login(): void
    {
        if (auth_is_logged_in()) {
            return;
        }

        $current = basename((string)($_SERVER['SCRIPT_NAME'] ?? 'account.php'));
        auth_redirect('login.php?next=' . rawurlencode($current));
    }
}

