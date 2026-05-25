<?php

declare(strict_types=1);

final class CloudLogger
{
    public static function log(string $level, string $action, string $message, array $context = []): void
    {
        global $wpdb;

        $table = CloudStorage::table('cloud_logs');
        $wpdb->insert(
            $table,
            [
                'level' => sanitize_key($level),
                'action' => sanitize_key($action),
                'message' => sanitize_textarea_field($message),
                'context' => wp_json_encode($context),
                'created_at' => current_time('mysql'),
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
    }
}
