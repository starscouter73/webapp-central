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

if (!function_exists('auth_is_admin_email')) {
    function auth_is_admin_email(string $email): bool
    {
        $normalized = auth_normalize_email($email);
        $admins = ['dorth.mark@gmail.com'];
        $extra = trim((string)app_env('APP_AUTH_ADMIN_EMAILS', ''));
        if ($extra !== '') {
            foreach (explode(',', $extra) as $entry) {
                $candidate = auth_normalize_email($entry);
                if ($candidate !== '') {
                    $admins[] = $candidate;
                }
            }
        }

        return in_array($normalized, $admins, true);
    }
}

if (!function_exists('auth_resolve_role')) {
    function auth_resolve_role(string $email, string $storedRole): string
    {
        if (auth_is_admin_email($email)) {
            return 'admin';
        }

        return $storedRole !== '' ? $storedRole : 'user';
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
            'role' => auth_resolve_role((string)($user['email'] ?? $normalizedEmail), (string)($user['role'] ?? 'user')),
        ]);
        return true;
    }
}

if (!function_exists('auth_current_user')) {
    function auth_current_user(): ?array
    {
        $user = auth_session_get('auth_user');
        if (!is_array($user)) {
            return null;
        }

        $email = auth_normalize_email((string)($user['email'] ?? ''));
        if ($email === '') {
            return null;
        }

        $fresh = auth_user_find_by_email($email);
        if (is_array($fresh)) {
            $resolvedRole = auth_resolve_role($email, (string)($fresh['role'] ?? 'user'));
            $resolved = [
                'id' => (int)($fresh['id'] ?? 0),
                'email' => (string)($fresh['email'] ?? $email),
                'role' => $resolvedRole,
            ];
            auth_session_set('auth_user', $resolved);
            return $resolved;
        }

        $user['role'] = auth_resolve_role($email, (string)($user['role'] ?? 'user'));
        auth_session_set('auth_user', $user);
        return $user;
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

if (!function_exists('auth_change_password')) {
    function auth_change_password(string $email, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $errors = [];
        $normalizedEmail = auth_normalize_email($email);
        $user = auth_user_find_by_email($normalizedEmail);

        if (!is_array($user)) {
            return ['ok' => false, 'errors' => ['Benutzerkonto nicht gefunden.']];
        }

        $hash = (string)($user['password_hash'] ?? '');
        if ($hash === '' || !password_verify($currentPassword, $hash)) {
            $errors[] = 'Aktuelles Passwort ist nicht korrekt.';
        }

        if (strlen($newPassword) < 8) {
            $errors[] = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
        }

        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Neues Passwort und Bestaetigung stimmen nicht ueberein.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!is_string($newHash) || $newHash === '') {
            return ['ok' => false, 'errors' => ['Passwort konnte nicht gespeichert werden.']];
        }

        if (!auth_user_update_password($normalizedEmail, $newHash)) {
            return ['ok' => false, 'errors' => ['Passwort konnte nicht gespeichert werden.']];
        }

        return ['ok' => true, 'errors' => []];
    }
}
