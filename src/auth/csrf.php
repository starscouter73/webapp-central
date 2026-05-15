<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

if (!function_exists('auth_csrf_token')) {
    function auth_csrf_token(string $form): string
    {
        auth_session_start();
        $tokens = auth_session_get('csrf_tokens', []);
        if (!is_array($tokens)) {
            $tokens = [];
        }

        if (!isset($tokens[$form]) || !is_string($tokens[$form]) || $tokens[$form] === '') {
            $tokens[$form] = bin2hex(random_bytes(32));
            auth_session_set('csrf_tokens', $tokens);
        }

        return $tokens[$form];
    }
}

if (!function_exists('auth_csrf_validate')) {
    function auth_csrf_validate(string $form, ?string $token): bool
    {
        auth_session_start();
        $tokens = auth_session_get('csrf_tokens', []);
        if (!is_array($tokens)) {
            return false;
        }

        $expected = $tokens[$form] ?? null;
        if (!is_string($expected) || $expected === '' || !is_string($token) || $token === '') {
            return false;
        }

        return hash_equals($expected, $token);
    }
}

