<?php
declare(strict_types=1);

if (!function_exists('auth_session_start')) {
    function auth_session_start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name('webapp_central_session');
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }
}

if (!function_exists('auth_session_get')) {
    function auth_session_get(string $key, mixed $default = null): mixed
    {
        auth_session_start();
        return $_SESSION[$key] ?? $default;
    }
}

if (!function_exists('auth_session_set')) {
    function auth_session_set(string $key, mixed $value): void
    {
        auth_session_start();
        $_SESSION[$key] = $value;
    }
}

if (!function_exists('auth_session_unset')) {
    function auth_session_unset(string $key): void
    {
        auth_session_start();
        unset($_SESSION[$key]);
    }
}

