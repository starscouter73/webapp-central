<?php

declare(strict_types=1);

final class CloudCrypto
{
    private const CIPHER = 'aes-256-cbc';

    public static function encryptConfig(array $config): string
    {
        $json = wp_json_encode($config);

        if (!is_string($json) || $json === '') {
            return '';
        }

        if (!self::canEncrypt()) {
            return 'plain:' . base64_encode($json);
        }

        try {
            $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);
            $iv = random_bytes(openssl_cipher_iv_length(self::CIPHER));
            $ciphertext = openssl_encrypt($json, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
        } catch (Throwable $exception) {
            unset($exception);

            return 'plain:' . base64_encode($json);
        }

        if ($ciphertext === false) {
            return 'plain:' . base64_encode($json);
        }

        return 'enc:' . base64_encode($iv . $ciphertext);
    }

    public static function decryptConfig(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        if (self::startsWith($payload, 'plain:')) {
            $decoded = base64_decode(substr($payload, 6), true);

            return is_string($decoded) ? (json_decode($decoded, true) ?: []) : [];
        }

        if (!self::startsWith($payload, 'enc:') || !self::canEncrypt()) {
            return [];
        }

        $raw = base64_decode(substr($payload, 4), true);

        if (!is_string($raw) || $raw === '') {
            return [];
        }

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($raw, 0, $ivLength);
        $ciphertext = substr($raw, $ivLength);
        $key = hash('sha256', AUTH_KEY . SECURE_AUTH_KEY, true);
        $json = openssl_decrypt($ciphertext, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);

        return is_string($json) ? (json_decode($json, true) ?: []) : [];
    }

    public static function canEncrypt(): bool
    {
        return function_exists('openssl_encrypt')
            && function_exists('openssl_decrypt')
            && function_exists('random_bytes')
            && defined('AUTH_KEY')
            && defined('SECURE_AUTH_KEY');
    }

    private static function startsWith(string $haystack, string $needle): bool
    {
        return strpos($haystack, $needle) === 0;
    }
}
