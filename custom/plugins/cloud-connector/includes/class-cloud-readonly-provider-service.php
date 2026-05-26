<?php

declare(strict_types=1);

final class CloudReadonlyProviderService
{
    private const GOOGLE_DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.metadata.readonly';
    private const GOOGLE_OAUTH_AUTHORIZE = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_OAUTH_TOKEN = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_DRIVE_FILES = 'https://www.googleapis.com/drive/v3/files';
    private const MAX_RESULTS = 5;
    private const MAX_TIMEOUT = 8;

    public static function normalizeMode(string $mode): string
    {
        $allowed = ['safe_mode', 'readonly_live', 'disabled'];

        return in_array($mode, $allowed, true) ? $mode : 'safe_mode';
    }

    public static function getGoogleReadonlyScopes(): array
    {
        return [self::GOOGLE_DRIVE_SCOPE];
    }

    public static function buildPreparedGoogleAuthUrl(array $config): string
    {
        $clientId = trim((string) ($config['client_id'] ?? ''));
        $redirectUri = trim((string) ($config['redirect_uri'] ?? ''));

        if ($clientId === '' || $redirectUri === '') {
            return '';
        }

        return add_query_arg(
            [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => self::GOOGLE_DRIVE_SCOPE,
                'access_type' => 'offline',
                'include_granted_scopes' => 'false',
                'prompt' => 'consent',
            ],
            self::GOOGLE_OAUTH_AUTHORIZE
        );
    }

    public static function testConnection(array $connection)
    {
        $providerSlug = sanitize_key((string) ($connection['provider_slug'] ?? ''));
        $config = CloudCrypto::decryptConfig((string) ($connection['config_encrypted'] ?? ''));
        $mode = self::normalizeMode((string) ($config['connection_mode'] ?? 'safe_mode'));
        $connectionId = (int) ($connection['id'] ?? 0);

        if ($mode !== 'readonly_live') {
            return new WP_Error('cloud_readonly_mode_required', 'Die Verbindung steht nicht im Modus READONLY LIVE.');
        }

        if ($providerSlug !== 'google_drive') {
            return new WP_Error('cloud_readonly_provider_unsupported', 'Readonly-Live-Tests sind aktuell nur fuer Google Drive vorbereitet.');
        }

        CloudLogger::log(
            'info',
            'readonly_connection_tested',
            'Readonly-Verbindungstest gestartet.',
            ['connection_id' => $connectionId, 'provider_slug' => $providerSlug, 'mode' => $mode]
        );

        $accessToken = self::resolveGoogleAccessToken($config);

        if (is_wp_error($accessToken)) {
            CloudLogger::log(
                'error',
                'readonly_provider_failed',
                'Readonly-Verbindung fehlgeschlagen.',
                ['connection_id' => $connectionId, 'provider_slug' => $providerSlug, 'error_code' => $accessToken->get_error_code()]
            );

            return $accessToken;
        }

        $response = wp_remote_get(
            add_query_arg(
                [
                    'pageSize' => self::MAX_RESULTS,
                    'fields' => 'files(id,name,mimeType,size,modifiedTime)',
                    'orderBy' => 'modifiedTime desc',
                    'q' => 'trashed = false',
                ],
                self::GOOGLE_DRIVE_FILES
            ),
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken['token'],
                    'Accept' => 'application/json',
                ],
                'timeout' => self::requestTimeout(),
                'redirection' => 0,
                'user-agent' => 'webapp-central-cloud-connector/readonly-live',
            ]
        );

        if (is_wp_error($response)) {
            CloudLogger::log(
                'error',
                'readonly_provider_failed',
                'Readonly-Metadatenabfrage fehlgeschlagen.',
                ['connection_id' => $connectionId, 'provider_slug' => $providerSlug, 'error_code' => $response->get_error_code()]
            );

            return new WP_Error('cloud_readonly_request_failed', 'Die readonly Metadatenabfrage an Google Drive ist fehlgeschlagen.');
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $payload = json_decode((string) $body, true);

        if ($statusCode < 200 || $statusCode >= 300 || !is_array($payload)) {
            CloudLogger::log(
                'error',
                'readonly_provider_failed',
                'Google Drive antwortete nicht mit einer gueltigen readonly Metadatenliste.',
                ['connection_id' => $connectionId, 'provider_slug' => $providerSlug, 'status_code' => $statusCode]
            );

            return new WP_Error('cloud_readonly_invalid_response', 'Google Drive lieferte keine gueltige readonly Antwort.');
        }

        $files = [];

        foreach (array_slice((array) ($payload['files'] ?? []), 0, self::MAX_RESULTS) as $file) {
            if (!is_array($file)) {
                continue;
            }

            $files[] = [
                'remote_id' => sanitize_text_field((string) ($file['id'] ?? '')),
                'path' => '/',
                'name' => sanitize_text_field((string) ($file['name'] ?? '')),
                'mime_type' => sanitize_text_field((string) ($file['mimeType'] ?? 'application/octet-stream')),
                'size_bytes' => isset($file['size']) ? (int) $file['size'] : 0,
                'checksum' => '',
                'last_modified' => self::normalizeGoogleDate((string) ($file['modifiedTime'] ?? '')),
            ];
        }

        $updatedConfig = $config;
        $updatedConfig['connection_mode'] = $mode;

        if ($accessToken['updated']) {
            $updatedConfig['access_token'] = $accessToken['token'];
        }

        CloudLogger::log(
            'info',
            'readonly_provider_connected',
            'Readonly-Metadatenzugriff erfolgreich.',
            ['connection_id' => $connectionId, 'provider_slug' => $providerSlug, 'file_count' => count($files)]
        );

        return [
            'config' => $updatedConfig,
            'files' => $files,
            'last_connected_at' => current_time('mysql'),
            'last_error' => '',
            'status' => 'aktiv',
        ];
    }

    private static function resolveGoogleAccessToken(array $config)
    {
        $token = trim((string) ($config['access_token'] ?? ''));

        if ($token !== '') {
            return ['token' => $token, 'updated' => false];
        }

        $refreshToken = trim((string) ($config['refresh_token'] ?? ''));
        $clientId = trim((string) ($config['client_id'] ?? ''));
        $clientSecret = trim((string) ($config['client_secret'] ?? ''));

        if ($refreshToken === '' || $clientId === '' || $clientSecret === '') {
            return new WP_Error(
                'cloud_readonly_missing_token',
                'Fuer den readonly Live-Test wird ein Access-Token oder ein Refresh-Token mit Google Client-Daten benoetigt.'
            );
        }

        $response = wp_remote_post(
            self::GOOGLE_OAUTH_TOKEN,
            [
                'timeout' => self::requestTimeout(),
                'redirection' => 0,
                'headers' => ['Accept' => 'application/json'],
                'body' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ],
                'user-agent' => 'webapp-central-cloud-connector/readonly-live',
            ]
        );

        if (is_wp_error($response)) {
            return new WP_Error('cloud_readonly_token_refresh_failed', 'Das Google Refresh-Token konnte nicht aktualisiert werden.');
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $payload = json_decode((string) wp_remote_retrieve_body($response), true);
        $newAccessToken = is_array($payload) ? trim((string) ($payload['access_token'] ?? '')) : '';

        if ($statusCode < 200 || $statusCode >= 300 || $newAccessToken === '') {
            return new WP_Error('cloud_readonly_token_refresh_invalid', 'Google lieferte kein gueltiges Access-Token fuer den readonly Test.');
        }

        return ['token' => $newAccessToken, 'updated' => true];
    }

    private static function normalizeGoogleDate(string $value): string
    {
        $timestamp = strtotime($value);

        if ($timestamp === false) {
            return current_time('mysql');
        }

        return gmdate('Y-m-d H:i:s', $timestamp);
    }

    private static function requestTimeout(): int
    {
        $configured = (int) CloudStorage::getSetting('http_timeout', '5');

        return max(3, min(self::MAX_TIMEOUT, $configured));
    }
}
