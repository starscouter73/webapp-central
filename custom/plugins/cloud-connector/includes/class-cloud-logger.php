<?php

declare(strict_types=1);

final class CloudLogger
{
    public static function log(string $level, string $action, string $message, array $context = []): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !CloudStorage::tableExists('cloud_logs')) {
            return;
        }

        $table = CloudStorage::table('cloud_logs');
        $result = $wpdb->insert(
            $table,
            [
                'level' => sanitize_key($level),
                'action' => sanitize_key($action),
                'message' => sanitize_textarea_field($message),
                'context' => wp_json_encode(self::sanitizeContext($context)),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );

        if ($result !== false) {
            self::maybePrune();
        }
    }

    private static function sanitizeContext(array $context): array
    {
        $maskedKeys = ['access_token', 'refresh_token', 'client_secret', 'password', 'token', 'secret'];

        foreach ($context as $key => $value) {
            if (is_array($value)) {
                $context[$key] = self::sanitizeContext($value);
                continue;
            }

            if (in_array((string) $key, $maskedKeys, true)) {
                $context[$key] = '[masked]';
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $context[$key] = $value;
                continue;
            }

            $context[$key] = '[complex]';
        }

        return $context;
    }

    private static function maybePrune(): void
    {
        if (wp_rand(1, 100) > 2) {
            return;
        }

        $retentionDays = max(1, (int) CloudStorage::getSetting('log_retention_days', '30'));
        CloudStorage::pruneLogs($retentionDays);
    }
}
