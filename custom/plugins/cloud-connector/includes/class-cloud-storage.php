<?php

declare(strict_types=1);

final class CloudStorage
{
    private static array $tableExistsCache = [];

    public static function table(string $table): string
    {
        global $wpdb;

        return $wpdb->prefix . $table;
    }

    public static function install(): bool
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb)) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset = $wpdb->get_charset_collate();
        $tables = [];

        $tables[] = 'CREATE TABLE ' . self::table('cloud_providers') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(100) NOT NULL,
            name VARCHAR(190) NOT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'available',
            capabilities LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) $charset;";

        $tables[] = 'CREATE TABLE ' . self::table('cloud_connections') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider_slug VARCHAR(100) NOT NULL,
            name VARCHAR(190) NOT NULL,
            config_encrypted LONGTEXT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'disconnected',
            safe_mode TINYINT(1) NOT NULL DEFAULT 1,
            last_connected_at DATETIME NULL,
            last_error TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY provider_slug (provider_slug)
        ) $charset;";

        $tables[] = 'CREATE TABLE ' . self::table('cloud_sync_jobs') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            provider_slug VARCHAR(100) NOT NULL,
            connection_id BIGINT UNSIGNED NULL,
            direction VARCHAR(40) NOT NULL,
            source_path TEXT NOT NULL,
            target_path TEXT NOT NULL,
            status VARCHAR(40) NOT NULL DEFAULT 'geplant',
            last_run DATETIME NULL,
            next_run DATETIME NULL,
            file_count INT UNSIGNED NOT NULL DEFAULT 0,
            error_text TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY provider_slug (provider_slug),
            KEY connection_id (connection_id)
        ) $charset;";

        $tables[] = 'CREATE TABLE ' . self::table('cloud_file_cache') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            connection_id BIGINT UNSIGNED NULL,
            remote_id VARCHAR(190) NOT NULL,
            path TEXT NOT NULL,
            name VARCHAR(190) NOT NULL,
            mime_type VARCHAR(190) NULL,
            size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
            checksum VARCHAR(190) NULL,
            last_modified DATETIME NULL,
            cached_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY connection_id (connection_id),
            KEY remote_id (remote_id)
        ) $charset;";

        $tables[] = 'CREATE TABLE ' . self::table('cloud_logs') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            level VARCHAR(40) NOT NULL,
            action VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            context LONGTEXT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY level (level),
            KEY action (action)
        ) $charset;";

        $tables[] = 'CREATE TABLE ' . self::table('cloud_settings') . " (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(190) NOT NULL,
            setting_value LONGTEXT NULL,
            autoload_setting TINYINT(1) NOT NULL DEFAULT 0,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset;";

        foreach ($tables as $sql) {
            dbDelta($sql);
        }

        self::seedProviders();
        self::seedSettings();

        if (!self::schemaReady()) {
            return false;
        }

        update_option('cloud_connector_db_version', '0.1.0', false);

        return true;
    }

    public static function seedProviders(): void
    {
        global $wpdb;

        $table = self::table('cloud_providers');
        $now = current_time('mysql');
        $providers = [
            'google_drive' => ['Google Drive', ['quota', 'download', 'upload', 'safe_mode', 'readonly_live']],
            'dropbox' => ['Dropbox', ['quota', 'download', 'upload', 'safe_mode', 'readonly_live_prepared']],
            'onedrive' => ['Microsoft OneDrive', ['quota', 'download', 'upload', 'safe_mode', 'readonly_live_prepared']],
            'local_storage' => ['Local Storage', ['browse', 'download', 'safe_mode']],
            'webdav' => ['Nextcloud / WebDAV / SFTP (spaeter)', ['planned', 'safe_mode']],
        ];

        foreach ($providers as $slug => [$name, $capabilities]) {
            $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE slug = %s", $slug));

            if ($existing) {
                continue;
            }

            $wpdb->insert(
                $table,
                [
                    'slug' => $slug,
                    'name' => $name,
                    'status' => 'available',
                    'capabilities' => wp_json_encode($capabilities),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                ['%s', '%s', '%s', '%s', '%s', '%s']
            );
        }
    }

    public static function seedSettings(): void
    {
        self::setDefaultSetting('safe_mode', '1');
        self::setDefaultSetting('http_timeout', '5');
        self::setDefaultSetting('allow_destructive_actions', '0');
        self::setDefaultSetting('log_retention_days', '30');
    }

    public static function getSetting(string $key, string $default = ''): string
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_settings')) {
            return $default;
        }

        $table = self::table('cloud_settings');
        $value = $wpdb->get_var($wpdb->prepare("SELECT setting_value FROM {$table} WHERE setting_key = %s", $key));

        return is_string($value) ? $value : $default;
    }

    public static function setSetting(string $key, string $value, int $autoload = 0): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_settings')) {
            return;
        }

        $table = self::table('cloud_settings');
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE setting_key = %s", $key));
        $data = [
            'setting_key' => $key,
            'setting_value' => $value,
            'autoload_setting' => $autoload,
            'updated_at' => current_time('mysql'),
        ];

        if ($existing) {
            $wpdb->update($table, $data, ['id' => (int) $existing], ['%s', '%s', '%d', '%s'], ['%d']);
            return;
        }

        $wpdb->insert($table, $data, ['%s', '%s', '%d', '%s']);
    }

    public static function setDefaultSetting(string $key, string $value, int $autoload = 0): void
    {
        if (self::getSetting($key, '') !== '') {
            return;
        }

        self::setSetting($key, $value, $autoload);
    }

    public static function getProviders(): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_providers')) {
            return [];
        }

        return $wpdb->get_results('SELECT * FROM ' . self::table('cloud_providers') . ' ORDER BY name ASC', ARRAY_A) ?: [];
    }

    public static function getConnections(): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_connections')) {
            return [];
        }

        return $wpdb->get_results('SELECT * FROM ' . self::table('cloud_connections') . ' ORDER BY updated_at DESC', ARRAY_A) ?: [];
    }

    public static function getConnection(int $id): ?array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_connections')) {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . self::table('cloud_connections') . ' WHERE id = %d', $id),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    public static function saveConnection(array $data): int
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_connections')) {
            return 0;
        }

        $table = self::table('cloud_connections');
        $now = current_time('mysql');
        $record = [
            'provider_slug' => sanitize_key($data['provider_slug'] ?? ''),
            'name' => sanitize_text_field($data['name'] ?? ''),
            'config_encrypted' => CloudCrypto::encryptConfig($data['config'] ?? []),
            'status' => sanitize_key($data['status'] ?? 'disconnected'),
            'safe_mode' => empty($data['safe_mode']) ? 0 : 1,
            'last_connected_at' => $data['last_connected_at'] ?? null,
            'last_error' => sanitize_textarea_field($data['last_error'] ?? ''),
            'updated_at' => $now,
        ];

        if (!empty($data['id'])) {
            $wpdb->update(
                $table,
                $record,
                ['id' => (int) $data['id']],
                ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'],
                ['%d']
            );

            return (int) $data['id'];
        }

        $record['created_at'] = $now;
        $wpdb->insert(
            $table,
            $record,
            ['%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    public static function deleteConnection(int $id): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_connections')) {
            return;
        }

        $wpdb->delete(self::table('cloud_connections'), ['id' => $id], ['%d']);
    }

    public static function getJobs(): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_sync_jobs')) {
            return [];
        }

        return $wpdb->get_results('SELECT * FROM ' . self::table('cloud_sync_jobs') . ' ORDER BY updated_at DESC', ARRAY_A) ?: [];
    }

    public static function getDueJobs(): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_sync_jobs')) {
            return [];
        }

        $table = self::table('cloud_sync_jobs');
        $now = current_time('mysql');

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE status = %s AND next_run IS NOT NULL AND next_run <> %s AND next_run <> %s AND next_run <= %s ORDER BY next_run ASC",
                'geplant',
                '',
                '0000-00-00 00:00:00',
                $now
            ),
            ARRAY_A
        ) ?: [];
    }

    public static function getJob(int $id): ?array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_sync_jobs')) {
            return null;
        }

        $row = $wpdb->get_row(
            $wpdb->prepare('SELECT * FROM ' . self::table('cloud_sync_jobs') . ' WHERE id = %d', $id),
            ARRAY_A
        );

        return is_array($row) ? $row : null;
    }

    public static function saveJob(array $data): int
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_sync_jobs')) {
            return 0;
        }

        $table = self::table('cloud_sync_jobs');
        $now = current_time('mysql');
        $lastRun = self::normalizeDateTimeValue($data['last_run'] ?? null);
        $nextRun = self::normalizeDateTimeValue($data['next_run'] ?? null);
        $record = [
            'provider_slug' => sanitize_key($data['provider_slug'] ?? ''),
            'connection_id' => empty($data['connection_id']) ? null : (int) $data['connection_id'],
            'direction' => sanitize_key($data['direction'] ?? 'cloud_to_local'),
            'source_path' => sanitize_text_field($data['source_path'] ?? ''),
            'target_path' => sanitize_text_field($data['target_path'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'geplant'),
            'last_run' => $lastRun,
            'next_run' => $nextRun,
            'file_count' => isset($data['file_count']) ? (int) $data['file_count'] : 0,
            'error_text' => sanitize_textarea_field($data['error_text'] ?? ''),
            'updated_at' => $now,
        ];

        if (!empty($data['id'])) {
            $wpdb->update(
                $table,
                $record,
                ['id' => (int) $data['id']],
                ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s'],
                ['%d']
            );

            return (int) $data['id'];
        }

        $record['created_at'] = $now;
        $wpdb->insert(
            $table,
            $record,
            ['%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
        );

        return (int) $wpdb->insert_id;
    }

    public static function deleteJob(int $id): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_sync_jobs')) {
            return;
        }

        $wpdb->delete(self::table('cloud_sync_jobs'), ['id' => $id], ['%d']);
    }

    public static function getFileCache(?int $connectionId = null): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_file_cache')) {
            return [];
        }

        $table = self::table('cloud_file_cache');

        if ($connectionId) {
            return $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM {$table} WHERE connection_id = %d ORDER BY cached_at DESC", $connectionId),
                ARRAY_A
            ) ?: [];
        }

        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY cached_at DESC", ARRAY_A) ?: [];
    }

    public static function replaceFileCache(int $connectionId, array $files): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_file_cache')) {
            return;
        }

        $table = self::table('cloud_file_cache');
        $wpdb->delete($table, ['connection_id' => $connectionId], ['%d']);

        foreach ($files as $file) {
            $wpdb->insert(
                $table,
                [
                    'connection_id' => $connectionId,
                    'remote_id' => sanitize_text_field((string) ($file['remote_id'] ?? '')),
                    'path' => sanitize_text_field((string) ($file['path'] ?? '/')),
                    'name' => sanitize_text_field((string) ($file['name'] ?? '')),
                    'mime_type' => sanitize_text_field((string) ($file['mime_type'] ?? 'application/octet-stream')),
                    'size_bytes' => (int) ($file['size_bytes'] ?? 0),
                    'checksum' => sanitize_text_field((string) ($file['checksum'] ?? '')),
                    'last_modified' => sanitize_text_field((string) ($file['last_modified'] ?? current_time('mysql'))),
                    'cached_at' => current_time('mysql'),
                ],
                ['%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s']
            );
        }
    }

    public static function getLogs(int $limit = 100): array
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_logs')) {
            return [];
        }

        $limit = max(1, $limit);

        return $wpdb->get_results(
            $wpdb->prepare('SELECT * FROM ' . self::table('cloud_logs') . ' ORDER BY created_at DESC LIMIT %d', $limit),
            ARRAY_A
        ) ?: [];
    }

    public static function pruneLogs(int $retentionDays): void
    {
        global $wpdb;

        if (!($wpdb instanceof wpdb) || !self::tableExists('cloud_logs')) {
            return;
        }

        $retentionDays = max(1, $retentionDays);
        $cutoff = gmdate('Y-m-d H:i:s', time() - ($retentionDays * DAY_IN_SECONDS));
        $wpdb->query(
            $wpdb->prepare('DELETE FROM ' . self::table('cloud_logs') . ' WHERE created_at < %s', $cutoff)
        );
    }

    public static function schemaReady(): bool
    {
        foreach (self::requiredTables() as $table) {
            if (!self::tableExists($table)) {
                return false;
            }
        }

        return true;
    }

    public static function missingTables(): array
    {
        $missing = [];

        foreach (self::requiredTables() as $table) {
            if (!self::tableExists($table)) {
                $missing[] = self::table($table);
            }
        }

        return $missing;
    }

    public static function tableExists(string $table): bool
    {
        global $wpdb;

        if (isset(self::$tableExistsCache[$table])) {
            return self::$tableExistsCache[$table];
        }

        if (!($wpdb instanceof wpdb)) {
            self::$tableExistsCache[$table] = false;

            return false;
        }

        $prefixedTable = self::table($table);
        $result = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $prefixedTable));
        self::$tableExistsCache[$table] = $result === $prefixedTable;

        return self::$tableExistsCache[$table];
    }

    public static function clearTableCache(): void
    {
        self::$tableExistsCache = [];
    }

    private static function normalizeDateTimeValue($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        return sanitize_text_field($value);
    }

    private static function requiredTables(): array
    {
        return [
            'cloud_providers',
            'cloud_connections',
            'cloud_sync_jobs',
            'cloud_file_cache',
            'cloud_logs',
            'cloud_settings',
        ];
    }
}
