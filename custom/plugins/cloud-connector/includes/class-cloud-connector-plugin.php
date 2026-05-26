<?php

declare(strict_types=1);

final class CloudConnectorPlugin
{
    private const VERSION = '0.1.0';
    private const NOTICE_OPTION = 'cloud_connector_admin_notice';

    public static function bootstrap(string $pluginFile): void
    {
        register_activation_hook($pluginFile, [self::class, 'activate']);
        register_deactivation_hook($pluginFile, [self::class, 'deactivate']);
        add_action('plugins_loaded', [self::class, 'maybeUpgrade']);
        add_action('init', [self::class, 'ensureAutomationHook']);
        add_action('admin_menu', [self::class, 'registerAdmin']);
        add_action('admin_notices', [self::class, 'renderAdminNotices']);
        add_action('admin_post_cc_save_connection', [self::class, 'saveConnection']);
        add_action('admin_post_cc_delete_connection', [self::class, 'deleteConnection']);
        add_action('admin_post_cc_connection_action', [self::class, 'connectionAction']);
        add_action('admin_post_cc_save_job', [self::class, 'saveJob']);
        add_action('admin_post_cc_delete_job', [self::class, 'deleteJob']);
        add_action('admin_post_cc_job_action', [self::class, 'jobAction']);
        add_action('admin_post_cc_refresh_files', [self::class, 'refreshFiles']);
        add_action('admin_post_cc_save_settings', [self::class, 'saveSettings']);
        add_action(CloudJobRunner::HOOK, [CloudJobRunner::class, 'runDueJobs']);
    }

    public static function activate(): void
    {
        CloudStorage::clearTableCache();
        $installed = CloudStorage::install();

        if (!$installed) {
            self::storeAdminNotice('Die Cloud-Connector-Tabellen konnten bei der Aktivierung nicht vollstaendig angelegt werden. Bitte Datenbankrechte und WordPress-Umgebung pruefen.');
            return;
        }

        CloudLogger::log('info', 'activate', 'Cloud Connector wurde initialisiert.');
        CloudJobRunner::ensureScheduled();
    }

    public static function deactivate(): void
    {
        CloudJobRunner::clearScheduled();
    }

    public static function maybeUpgrade(): void
    {
        $installedVersion = get_option('cloud_connector_db_version', '');

        if ($installedVersion === self::VERSION && CloudStorage::schemaReady()) {
            return;
        }

        CloudStorage::clearTableCache();
        $installed = CloudStorage::install();

        if (!$installed) {
            self::storeAdminNotice('Cloud Connector ist aktiv, aber die Datenbankbasis ist unvollstaendig. Das Modul bleibt im Safe-Mode und zeigt nur eingeschraenkte Verwaltungsdaten.');
            return;
        }

        if ($installedVersion !== '' && $installedVersion !== self::VERSION) {
            self::storeAdminNotice('Cloud Connector Datenbankschema wurde auf die aktuelle Plugin-Version abgeglichen.');
        }
    }

    public static function registerAdmin(): void
    {
        CloudAdmin::registerMenu();
    }

    public static function ensureAutomationHook(): void
    {
        CloudJobRunner::ensureScheduled();
    }

    public static function saveConnection(): void
    {
        CloudAdmin::assertAdminAction('cc_save_connection');

        if (!CloudStorage::schemaReady()) {
            self::storeAdminNotice('Speichern nicht moeglich: Die Cloud-Connector-Tabellen sind nicht vollstaendig verfuegbar.');
            CloudAdmin::redirectWithNotice('connections', 'schema_incomplete', true);
        }

        $connectionId = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $existing = $connectionId ? CloudStorage::getConnection($connectionId) : null;
        $existingConfig = $existing ? CloudCrypto::decryptConfig((string) $existing['config_encrypted']) : [];
        $submittedConfig = [
            'client_id' => sanitize_text_field(wp_unslash($_POST['client_id'] ?? '')),
            'client_secret' => sanitize_text_field(wp_unslash($_POST['client_secret'] ?? '')),
            'access_token' => sanitize_text_field(wp_unslash($_POST['access_token'] ?? '')),
            'refresh_token' => sanitize_text_field(wp_unslash($_POST['refresh_token'] ?? '')),
            'root_path' => sanitize_text_field(wp_unslash($_POST['root_path'] ?? '')),
            'local_path' => sanitize_text_field(wp_unslash($_POST['local_path'] ?? '')),
            'endpoint' => esc_url_raw(wp_unslash($_POST['endpoint'] ?? '')),
            'username' => sanitize_text_field(wp_unslash($_POST['username'] ?? '')),
            'password' => sanitize_text_field(wp_unslash($_POST['password'] ?? '')),
            'host' => sanitize_text_field(wp_unslash($_POST['host'] ?? '')),
            'port' => sanitize_text_field(wp_unslash($_POST['port'] ?? '')),
        ];

        foreach (['client_secret', 'access_token', 'refresh_token', 'password'] as $secretKey) {
            if (($submittedConfig[$secretKey] ?? '') === '' && !empty($existingConfig[$secretKey])) {
                $submittedConfig[$secretKey] = $existingConfig[$secretKey];
            }
        }

        $id = CloudStorage::saveConnection([
            'id' => $connectionId,
            'provider_slug' => wp_unslash($_POST['provider_slug'] ?? ''),
            'name' => wp_unslash($_POST['name'] ?? ''),
            'status' => 'disconnected',
            'safe_mode' => 1,
            'config' => $submittedConfig,
        ]);

        CloudLogger::log('info', 'save_connection', 'Verbindung gespeichert.', ['connection_id' => $id]);
        CloudAdmin::redirectWithNotice('connections', 'connection_saved');
    }

    public static function deleteConnection(): void
    {
        CloudAdmin::assertAdminAction('cc_delete_connection');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('connections', 'schema_incomplete', true);
        }

        $id = absint($_POST['id'] ?? 0);
        CloudStorage::deleteConnection($id);
        CloudLogger::log('warning', 'delete_connection', 'Verbindung entfernt.', ['connection_id' => $id]);
        CloudAdmin::redirectWithNotice('connections', 'connection_deleted');
    }

    public static function connectionAction(): void
    {
        CloudAdmin::assertAdminAction('cc_connection_action');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('connections', 'schema_incomplete', true);
        }

        $id = absint($_POST['id'] ?? 0);
        $action = sanitize_key(wp_unslash($_POST['connection_action'] ?? ''));
        $connection = CloudStorage::getConnection($id);

        if (!$connection) {
            CloudAdmin::redirectWithNotice('connections', 'connection_missing', true);
        }

        $provider = CloudProviderRegistry::get($connection['provider_slug']);
        $config = CloudCrypto::decryptConfig((string) $connection['config_encrypted']);

        if ($action === 'connect' && $provider) {
            $result = $provider->connect($config);
            $status = is_wp_error($result) ? 'error' : 'connected';
            CloudStorage::saveConnection([
                'id' => $id,
                'provider_slug' => $connection['provider_slug'],
                'name' => $connection['name'],
                'status' => $status,
                'safe_mode' => (int) $connection['safe_mode'],
                'last_connected_at' => current_time('mysql'),
                'last_error' => is_wp_error($result) ? $result->get_error_message() : '',
                'config' => $config,
            ]);
            CloudLogger::log('info', 'connect', 'Verbindung getestet (Simulation).', ['connection_id' => $id, 'status' => $status]);
        }

        if ($action === 'disconnect') {
            CloudStorage::saveConnection([
                'id' => $id,
                'provider_slug' => $connection['provider_slug'],
                'name' => $connection['name'],
                'status' => 'disconnected',
                'safe_mode' => (int) $connection['safe_mode'],
                'last_connected_at' => $connection['last_connected_at'],
                'last_error' => '',
                'config' => $config,
            ]);
            CloudLogger::log('info', 'disconnect', 'Verbindung getrennt.', ['connection_id' => $id]);
        }

        CloudAdmin::redirectWithNotice('connections', 'connection_updated');
    }

    public static function saveJob(): void
    {
        CloudAdmin::assertAdminAction('cc_save_job');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('sync-jobs', 'schema_incomplete', true);
        }

        $id = CloudStorage::saveJob([
            'id' => absint($_POST['id'] ?? 0),
            'provider_slug' => wp_unslash($_POST['provider_slug'] ?? ''),
            'connection_id' => absint($_POST['connection_id'] ?? 0),
            'direction' => wp_unslash($_POST['direction'] ?? 'cloud_to_local'),
            'source_path' => wp_unslash($_POST['source_path'] ?? ''),
            'target_path' => wp_unslash($_POST['target_path'] ?? ''),
            'status' => wp_unslash($_POST['status'] ?? 'geplant'),
            'next_run' => sanitize_text_field(wp_unslash($_POST['next_run'] ?? '')),
            'error_text' => '',
        ]);

        CloudLogger::log('info', 'save_job', 'Sync-Job gespeichert.', ['job_id' => $id]);
        CloudAdmin::redirectWithNotice('sync-jobs', 'job_saved');
    }

    public static function deleteJob(): void
    {
        CloudAdmin::assertAdminAction('cc_delete_job');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('sync-jobs', 'schema_incomplete', true);
        }

        $id = absint($_POST['id'] ?? 0);
        CloudStorage::deleteJob($id);
        CloudLogger::log('warning', 'delete_job', 'Sync-Job geloescht.', ['job_id' => $id]);
        CloudAdmin::redirectWithNotice('sync-jobs', 'job_deleted');
    }

    public static function jobAction(): void
    {
        CloudAdmin::assertAdminAction('cc_job_action');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('sync-jobs', 'schema_incomplete', true);
        }

        $id = absint($_POST['id'] ?? 0);
        $action = sanitize_key(wp_unslash($_POST['job_action'] ?? ''));
        $job = CloudStorage::getJob($id);

        if (!$job) {
            CloudAdmin::redirectWithNotice('sync-jobs', 'job_missing', true);
        }

        $status = $job['status'];
        $error = '';
        $lastRun = $job['last_run'];
        $fileCount = (int) $job['file_count'];
        $nextRun = $job['next_run'];
        $notice = 'job_updated';

        if ($action === 'pause') {
            $status = 'pausiert';
            $notice = 'job_paused';
        } elseif ($action === 'resume') {
            $status = 'geplant';
            $notice = 'job_resumed';
        } elseif ($action === 'simulate') {
            $status = 'geplant';
            $nextRun = current_time('mysql');
            $notice = 'job_simulation_started';
        }

        CloudStorage::saveJob([
            'id' => $id,
            'provider_slug' => $job['provider_slug'],
            'connection_id' => $job['connection_id'],
            'direction' => $job['direction'],
            'source_path' => $job['source_path'],
            'target_path' => $job['target_path'],
            'status' => $status,
            'last_run' => $lastRun,
            'next_run' => $nextRun,
            'file_count' => $fileCount,
            'error_text' => $error,
        ]);

        if ($action === 'pause') {
            CloudLogger::log('info', 'sync_job_paused', 'Sync-Job pausiert.', ['job_id' => $id]);
        } elseif ($action === 'resume') {
            CloudLogger::log('info', 'sync_job_resumed', 'Sync-Job fortgesetzt.', ['job_id' => $id]);
        } elseif ($action === 'simulate') {
            CloudLogger::log('info', 'sync_job_manual_simulation', 'Manueller Safe-Mode-Simulationslauf angefordert.', ['job_id' => $id]);
            CloudJobRunner::runDueJobs();
        } else {
            CloudLogger::log('info', 'job_action', 'Job-Aktion ausgefuehrt.', ['job_id' => $id, 'job_action' => $action]);
        }

        CloudAdmin::redirectWithNotice('sync-jobs', $notice);
    }

    public static function refreshFiles(): void
    {
        CloudAdmin::assertAdminAction('cc_refresh_files');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('files', 'schema_incomplete', true);
        }

        $connectionId = absint($_POST['connection_id'] ?? 0);
        $connection = CloudStorage::getConnection($connectionId);

        if (!$connection) {
            CloudAdmin::redirectWithNotice('files', 'connection_missing', true);
        }

        $provider = CloudProviderRegistry::get($connection['provider_slug']);

        if (!$provider) {
            CloudAdmin::redirectWithNotice('files', 'provider_missing', true);
        }

        $files = $provider->listFiles('/');
        CloudStorage::replaceFileCache($connectionId, is_array($files) ? $files : []);
        CloudLogger::log('info', 'refresh_files', 'Dateiuebersicht aus Demo-Provider aktualisiert.', ['connection_id' => $connectionId]);
        CloudAdmin::redirectWithNotice('files', 'files_refreshed');
    }

    public static function saveSettings(): void
    {
        CloudAdmin::assertAdminAction('cc_save_settings');

        if (!CloudStorage::schemaReady()) {
            CloudAdmin::redirectWithNotice('settings', 'schema_incomplete', true);
        }

        CloudStorage::setSetting('safe_mode', empty($_POST['safe_mode']) ? '0' : '1');
        CloudStorage::setSetting('http_timeout', (string) absint($_POST['http_timeout'] ?? 5));
        CloudStorage::setSetting('allow_destructive_actions', empty($_POST['allow_destructive_actions']) ? '0' : '1');
        CloudStorage::setSetting('log_retention_days', (string) absint($_POST['log_retention_days'] ?? 30));

        CloudLogger::log('info', 'save_settings', 'Einstellungen aktualisiert.');
        CloudAdmin::redirectWithNotice('settings', 'settings_saved');
    }

    public static function renderAdminNotices(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $notice = get_option(self::NOTICE_OPTION, '');

        if (is_string($notice) && $notice !== '') {
            echo '<div class="notice notice-warning"><p>' . esc_html($notice) . '</p></div>';
            delete_option(self::NOTICE_OPTION);
        }

        if (!CloudStorage::schemaReady()) {
            $missing = CloudStorage::missingTables();
            echo '<div class="notice notice-error"><p>';
            echo esc_html('Cloud Connector laeuft im eingeschraenkten Recovery-Modus. Fehlende Tabellen: ' . implode(', ', $missing));
            echo '</p></div>';
        }

        if (!CloudCrypto::canEncrypt()) {
            echo '<div class="notice notice-warning"><p>Cloud Connector kann Konfigurationen in dieser Umgebung nicht verschluesseln und faellt auf maskierte Fallback-Speicherung zurueck.</p></div>';
        }

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            echo '<div class="notice notice-info"><p>WP-Cron ist deaktiviert. Das betrifft V1 nicht produktiv, spaetere Hintergrundjobs benoetigen jedoch einen separaten Scheduler.</p></div>';
        }
    }

    private static function storeAdminNotice(string $message): void
    {
        update_option(self::NOTICE_OPTION, sanitize_text_field($message), false);
    }
}
