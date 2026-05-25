<?php

declare(strict_types=1);

final class CloudAdmin
{
    private const MENU_SLUG = 'cloud_connector';

    public static function registerMenu(): void
    {
        add_menu_page(
            'Cloud Connector',
            'Cloud-Verwaltung',
            'manage_options',
            self::MENU_SLUG,
            [self::class, 'renderPage'],
            'dashicons-cloud',
            59
        );
    }

    public static function assertAdminAction(string $nonceAction): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Keine Berechtigung.');
        }

        check_admin_referer($nonceAction);
    }

    public static function redirectWithNotice(string $tab, string $notice, bool $error = false): void
    {
        $args = [
            'page' => self::MENU_SLUG,
            'tab' => $tab,
            $error ? 'cc_error' : 'cc_notice' => $notice,
        ];

        wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
        exit;
    }

    public static function renderPage(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('Keine Berechtigung.');
        }

        $currentTab = sanitize_key($_GET['tab'] ?? 'overview');
        $tabs = [
            'overview' => 'Uebersicht',
            'providers' => 'Anbieter',
            'connections' => 'Verbindungen',
            'files' => 'Dateien',
            'sync-jobs' => 'Sync-Jobs',
            'automation' => 'Automatisierung',
            'logs' => 'Logs',
            'settings' => 'Einstellungen',
        ];

        echo '<div class="wrap">';
        echo '<h1>Cloud Connector</h1>';
        self::renderNotices();
        echo '<h2 class="nav-tab-wrapper">';

        foreach ($tabs as $tab => $label) {
            $class = $tab === $currentTab ? ' nav-tab-active' : '';
            $url = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => $tab], admin_url('admin.php')));
            echo '<a class="nav-tab' . esc_attr($class) . '" href="' . $url . '">' . esc_html($label) . '</a>';
        }

        echo '</h2>';
        echo '<div style="margin-top:16px;">';

        switch ($currentTab) {
            case 'providers':
                self::renderProviders();
                break;
            case 'connections':
                self::renderConnections();
                break;
            case 'files':
                self::renderFiles();
                break;
            case 'sync-jobs':
                self::renderJobs();
                break;
            case 'automation':
                self::renderAutomation();
                break;
            case 'logs':
                self::renderLogs();
                break;
            case 'settings':
                self::renderSettings();
                break;
            case 'overview':
            default:
                self::renderOverview();
        }

        echo '</div></div>';
    }

    private static function renderNotices(): void
    {
        $notice = sanitize_key($_GET['cc_notice'] ?? '');
        $error = sanitize_key($_GET['cc_error'] ?? '');

        if ($notice !== '') {
            echo '<div class="notice notice-success"><p>' . esc_html(self::noticeText($notice)) . '</p></div>';
        }

        if ($error !== '') {
            echo '<div class="notice notice-error"><p>' . esc_html(self::noticeText($error)) . '</p></div>';
        }
    }

    private static function noticeText(string $key): string
    {
        $messages = [
            'connection_saved' => 'Verbindung gespeichert.',
            'connection_deleted' => 'Verbindung geloescht.',
            'connection_updated' => 'Verbindungsstatus aktualisiert.',
            'connection_missing' => 'Verbindung nicht gefunden.',
            'provider_missing' => 'Anbieter nicht gefunden.',
            'job_saved' => 'Sync-Job gespeichert.',
            'job_deleted' => 'Sync-Job geloescht.',
            'job_updated' => 'Sync-Job aktualisiert.',
            'job_missing' => 'Sync-Job nicht gefunden.',
            'files_refreshed' => 'Dateiliste aus Demo-/Cache-Daten aktualisiert.',
            'settings_saved' => 'Einstellungen gespeichert.',
        ];

        return $messages[$key] ?? 'Aktion ausgefuehrt.';
    }

    private static function renderOverview(): void
    {
        $providers = CloudStorage::getProviders();
        $connections = CloudStorage::getConnections();
        $jobs = CloudStorage::getJobs();
        $safeMode = CloudStorage::getSetting('safe_mode', '1') === '1' ? 'Aktiv' : 'Deaktiviert';

        echo '<p>V1 stellt nur die sichere Verwaltungsbasis bereit. Produktive Sync-, Move- und Delete-Aktionen bleiben blockiert.</p>';
        echo '<table class="widefat striped"><tbody>';
        echo '<tr><td><strong>Safe-Mode</strong></td><td>' . esc_html($safeMode) . '</td></tr>';
        echo '<tr><td><strong>Anbieter</strong></td><td>' . esc_html((string) count($providers)) . '</td></tr>';
        echo '<tr><td><strong>Verbindungen</strong></td><td>' . esc_html((string) count($connections)) . '</td></tr>';
        echo '<tr><td><strong>Sync-Jobs</strong></td><td>' . esc_html((string) count($jobs)) . '</td></tr>';
        echo '<tr><td><strong>Timeout fuer spaetere API-Calls</strong></td><td>' . esc_html(CloudStorage::getSetting('http_timeout', '5')) . ' Sekunden</td></tr>';
        echo '</tbody></table>';
    }

    private static function renderProviders(): void
    {
        $providers = CloudStorage::getProviders();

        echo '<table class="widefat striped"><thead><tr><th>Anbieter</th><th>Slug</th><th>Status</th><th>Faehigkeiten</th></tr></thead><tbody>';

        foreach ($providers as $provider) {
            $capabilities = json_decode((string) $provider['capabilities'], true) ?: [];
            echo '<tr>';
            echo '<td>' . esc_html($provider['name']) . '</td>';
            echo '<td><code>' . esc_html($provider['slug']) . '</code></td>';
            echo '<td>' . esc_html($provider['status']) . '</td>';
            echo '<td>' . esc_html(implode(', ', $capabilities)) . '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
    }

    private static function renderConnections(): void
    {
        $connections = CloudStorage::getConnections();
        $providers = CloudStorage::getProviders();
        $editId = absint($_GET['edit_connection'] ?? 0);
        $editConnection = $editId ? CloudStorage::getConnection($editId) : null;
        $config = $editConnection ? CloudCrypto::decryptConfig((string) $editConnection['config_encrypted']) : [];

        echo '<h2>Verbindungen</h2>';
        echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Anbieter</th><th>Status</th><th>Safe-Mode</th><th>Secrets</th><th>Aktionen</th></tr></thead><tbody>';

        foreach ($connections as $connection) {
            $summary = self::maskConnectionSummary(CloudCrypto::decryptConfig((string) $connection['config_encrypted']));
            $editUrl = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => 'connections', 'edit_connection' => (int) $connection['id']], admin_url('admin.php')));

            echo '<tr>';
            echo '<td>' . esc_html($connection['name']) . '</td>';
            echo '<td><code>' . esc_html($connection['provider_slug']) . '</code></td>';
            echo '<td>' . esc_html($connection['status']) . '</td>';
            echo '<td>' . (!empty($connection['safe_mode']) ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html($summary) . '</td>';
            echo '<td>';
            echo '<a class="button button-secondary" href="' . $editUrl . '">Bearbeiten</a> ';
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'connect'], 'Test-Connect');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'disconnect'], 'Disconnect');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_delete_connection', 'cc_delete_connection', ['id' => (int) $connection['id']], 'Loeschen');
            echo '</td>';
            echo '</tr>';
        }

        echo '</tbody></table>';
        echo '<h2 style="margin-top:24px;">' . ($editConnection ? 'Verbindung bearbeiten' : 'Neue Verbindung') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('cc_save_connection');
        echo '<input type="hidden" name="action" value="cc_save_connection">';
        echo '<input type="hidden" name="id" value="' . esc_attr((string) ($editConnection['id'] ?? 0)) . '">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label for="provider_slug">Anbieter</label></th><td><select id="provider_slug" name="provider_slug">';

        foreach ($providers as $provider) {
            $selected = selected($provider['slug'], $editConnection['provider_slug'] ?? '', false);
            echo '<option value="' . esc_attr($provider['slug']) . '"' . $selected . '>' . esc_html($provider['name']) . '</option>';
        }

        echo '</select></td></tr>';
        echo '<tr><th><label for="name">Name</label></th><td><input class="regular-text" id="name" name="name" value="' . esc_attr((string) ($editConnection['name'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="client_id">Client ID</label></th><td><input class="regular-text" id="client_id" name="client_id" value="' . esc_attr((string) ($config['client_id'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="client_secret">Client Secret</label></th><td><input class="regular-text" id="client_secret" name="client_secret" value=""></td></tr>';
        echo '<tr><th><label for="access_token">Access Token</label></th><td><input class="regular-text" id="access_token" name="access_token" value=""></td></tr>';
        echo '<tr><th><label for="refresh_token">Refresh Token</label></th><td><input class="regular-text" id="refresh_token" name="refresh_token" value=""></td></tr>';
        echo '<tr><th><label for="root_path">Root Path</label></th><td><input class="regular-text" id="root_path" name="root_path" value="' . esc_attr((string) ($config['root_path'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="local_path">Local Path</label></th><td><input class="regular-text" id="local_path" name="local_path" value="' . esc_attr((string) ($config['local_path'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="endpoint">Endpoint</label></th><td><input class="regular-text" id="endpoint" name="endpoint" value="' . esc_attr((string) ($config['endpoint'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="username">Username</label></th><td><input class="regular-text" id="username" name="username" value="' . esc_attr((string) ($config['username'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="password">Password</label></th><td><input class="regular-text" id="password" name="password" value=""></td></tr>';
        echo '<tr><th><label for="host">Host</label></th><td><input class="regular-text" id="host" name="host" value="' . esc_attr((string) ($config['host'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="port">Port</label></th><td><input class="small-text" id="port" name="port" value="' . esc_attr((string) ($config['port'] ?? '')) . '"></td></tr>';
        echo '</tbody></table>';
        submit_button($editConnection ? 'Verbindung aktualisieren' : 'Verbindung speichern');
        echo '</form>';
    }

    private static function renderFiles(): void
    {
        $connections = CloudStorage::getConnections();
        $selectedConnectionId = absint($_GET['connection_id'] ?? ($connections[0]['id'] ?? 0));
        $files = CloudStorage::getFileCache($selectedConnectionId ?: null);

        echo '<p>Dateiansichten basieren in V1 ausschliesslich auf Cache-/Demo-Daten. Es werden keine Live-Abfragen beim Rendern ausgefuehrt.</p>';

        if (!empty($connections)) {
            echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" style="margin-bottom:16px;">';
            wp_nonce_field('cc_refresh_files');
            echo '<input type="hidden" name="action" value="cc_refresh_files">';
            echo '<select name="connection_id">';

            foreach ($connections as $connection) {
                $selected = selected((int) $connection['id'], $selectedConnectionId, false);
                echo '<option value="' . esc_attr((string) $connection['id']) . '"' . $selected . '>' . esc_html($connection['name']) . '</option>';
            }

            echo '</select> ';
            submit_button('Dateiliste simuliert aktualisieren', 'secondary', '', false);
            echo '</form>';
        }

        echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Pfad</th><th>Typ</th><th>Groesse</th><th>Geaendert</th></tr></thead><tbody>';

        foreach ($files as $file) {
            echo '<tr>';
            echo '<td>' . esc_html($file['name']) . '</td>';
            echo '<td><code>' . esc_html($file['path']) . '</code></td>';
            echo '<td>' . esc_html($file['mime_type']) . '</td>';
            echo '<td>' . esc_html(size_format((int) $file['size_bytes'])) . '</td>';
            echo '<td>' . esc_html((string) $file['last_modified']) . '</td>';
            echo '</tr>';
        }

        if (empty($files)) {
            echo '<tr><td colspan="5">Noch keine Cache-Daten vorhanden.</td></tr>';
        }

        echo '</tbody></table>';
    }

    private static function renderJobs(): void
    {
        $jobs = CloudStorage::getJobs();
        $providers = CloudStorage::getProviders();
        $connections = CloudStorage::getConnections();
        $editId = absint($_GET['edit_job'] ?? 0);
        $editJob = $editId ? CloudStorage::getJob($editId) : null;

        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Anbieter</th><th>Richtung</th><th>Status</th><th>Letzter Lauf</th><th>Naechster Lauf</th><th>Dateien</th><th>Aktionen</th></tr></thead><tbody>';

        foreach ($jobs as $job) {
            $editUrl = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => 'sync-jobs', 'edit_job' => (int) $job['id']], admin_url('admin.php')));
            echo '<tr>';
            echo '<td>' . esc_html((string) $job['id']) . '</td>';
            echo '<td><code>' . esc_html($job['provider_slug']) . '</code></td>';
            echo '<td>' . esc_html($job['direction']) . '</td>';
            echo '<td>' . esc_html($job['status']) . '</td>';
            echo '<td>' . esc_html((string) ($job['last_run'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($job['next_run'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) $job['file_count']) . '</td>';
            echo '<td>';
            echo '<a class="button button-secondary" href="' . $editUrl . '">Bearbeiten</a> ';
            self::inlinePostButton('admin-post.php?action=cc_job_action', 'cc_job_action', ['id' => (int) $job['id'], 'job_action' => 'simulate'], 'Simulation');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_job_action', 'cc_job_action', ['id' => (int) $job['id'], 'job_action' => 'pause'], 'Pausieren');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_delete_job', 'cc_delete_job', ['id' => (int) $job['id']], 'Loeschen');
            echo '</td>';
            echo '</tr>';
        }

        if (empty($jobs)) {
            echo '<tr><td colspan="8">Noch keine Jobs vorhanden.</td></tr>';
        }

        echo '</tbody></table>';

        echo '<h2 style="margin-top:24px;">' . ($editJob ? 'Sync-Job bearbeiten' : 'Neuer Sync-Job') . '</h2>';
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('cc_save_job');
        echo '<input type="hidden" name="action" value="cc_save_job">';
        echo '<input type="hidden" name="id" value="' . esc_attr((string) ($editJob['id'] ?? 0)) . '">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th><label for="provider_slug_job">Anbieter</label></th><td><select id="provider_slug_job" name="provider_slug">';

        foreach ($providers as $provider) {
            $selected = selected($provider['slug'], $editJob['provider_slug'] ?? '', false);
            echo '<option value="' . esc_attr($provider['slug']) . '"' . $selected . '>' . esc_html($provider['name']) . '</option>';
        }

        echo '</select></td></tr>';
        echo '<tr><th><label for="connection_id">Verbindung</label></th><td><select id="connection_id" name="connection_id"><option value="0">Keine</option>';

        foreach ($connections as $connection) {
            $selected = selected((int) $connection['id'], (int) ($editJob['connection_id'] ?? 0), false);
            echo '<option value="' . esc_attr((string) $connection['id']) . '"' . $selected . '>' . esc_html($connection['name']) . '</option>';
        }

        echo '</select></td></tr>';
        echo '<tr><th><label for="direction">Richtung</label></th><td><select id="direction" name="direction">';
        foreach (['cloud_to_local', 'local_to_cloud', 'bidirectional'] as $direction) {
            $selected = selected($direction, $editJob['direction'] ?? 'cloud_to_local', false);
            echo '<option value="' . esc_attr($direction) . '"' . $selected . '>' . esc_html($direction) . '</option>';
        }
        echo '</select></td></tr>';
        echo '<tr><th><label for="source_path">Quellpfad</label></th><td><input class="regular-text" id="source_path" name="source_path" value="' . esc_attr((string) ($editJob['source_path'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="target_path">Zielpfad</label></th><td><input class="regular-text" id="target_path" name="target_path" value="' . esc_attr((string) ($editJob['target_path'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="status">Status</label></th><td><select id="status" name="status">';
        foreach (['geplant', 'laeuft', 'erfolgreich', 'fehlerhaft', 'pausiert'] as $status) {
            $selected = selected($status, $editJob['status'] ?? 'geplant', false);
            echo '<option value="' . esc_attr($status) . '"' . $selected . '>' . esc_html($status) . '</option>';
        }
        echo '</select></td></tr>';
        echo '<tr><th><label for="next_run">Naechster Lauf</label></th><td><input class="regular-text" id="next_run" name="next_run" placeholder="YYYY-MM-DD HH:MM:SS" value="' . esc_attr((string) ($editJob['next_run'] ?? '')) . '"></td></tr>';
        echo '</tbody></table>';
        submit_button($editJob ? 'Job aktualisieren' : 'Job speichern');
        echo '</form>';
    }

    private static function renderAutomation(): void
    {
        echo '<p>Die Automatisierungsbasis ist vorbereitet, fuehrt in V1 aber keine produktiven Syncs aus.</p>';
        echo '<ul style="list-style:disc; padding-left:20px;">';
        echo '<li>Empfohlener spaeterer Hook: <code>cloud_connector_run_jobs</code></li>';
        echo '<li>Nur Simulationslaeufe, solange Safe-Mode aktiv bleibt</li>';
        echo '<li>Keine externen API-Requests im normalen Backend-Rendering</li>';
        echo '<li>Timeout-Vorgabe fuer spaetere Worker: ' . esc_html(CloudStorage::getSetting('http_timeout', '5')) . ' Sekunden</li>';
        echo '</ul>';
    }

    private static function renderLogs(): void
    {
        $logs = CloudStorage::getLogs();

        echo '<table class="widefat striped"><thead><tr><th>Zeit</th><th>Level</th><th>Aktion</th><th>Meldung</th><th>Kontext</th></tr></thead><tbody>';

        foreach ($logs as $log) {
            echo '<tr>';
            echo '<td>' . esc_html($log['created_at']) . '</td>';
            echo '<td>' . esc_html($log['level']) . '</td>';
            echo '<td><code>' . esc_html($log['action']) . '</code></td>';
            echo '<td>' . esc_html($log['message']) . '</td>';
            echo '<td><code>' . esc_html((string) $log['context']) . '</code></td>';
            echo '</tr>';
        }

        if (empty($logs)) {
            echo '<tr><td colspan="5">Noch keine Logs vorhanden.</td></tr>';
        }

        echo '</tbody></table>';
    }

    private static function renderSettings(): void
    {
        echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '">';
        wp_nonce_field('cc_save_settings');
        echo '<input type="hidden" name="action" value="cc_save_settings">';
        echo '<table class="form-table"><tbody>';
        echo '<tr><th>Safe-Mode</th><td><label><input type="checkbox" name="safe_mode" value="1" ' . checked('1', CloudStorage::getSetting('safe_mode', '1'), false) . '> Standardmaessig aktiv</label></td></tr>';
        echo '<tr><th>Destruktive Aktionen</th><td><label><input type="checkbox" name="allow_destructive_actions" value="1" ' . checked('1', CloudStorage::getSetting('allow_destructive_actions', '0'), false) . '> Nur fuer spaetere Freigabe vormerken</label></td></tr>';
        echo '<tr><th><label for="http_timeout">HTTP-Timeout</label></th><td><input class="small-text" id="http_timeout" name="http_timeout" value="' . esc_attr(CloudStorage::getSetting('http_timeout', '5')) . '"> Sekunden</td></tr>';
        echo '<tr><th><label for="log_retention_days">Log-Aufbewahrung</label></th><td><input class="small-text" id="log_retention_days" name="log_retention_days" value="' . esc_attr(CloudStorage::getSetting('log_retention_days', '30')) . '"> Tage</td></tr>';
        echo '</tbody></table>';
        submit_button('Einstellungen speichern');
        echo '</form>';
    }

    private static function inlinePostButton(string $path, string $nonceAction, array $fields, string $label): void
    {
        echo '<form method="post" action="' . esc_url(admin_url($path)) . '" style="display:inline-block;">';
        wp_nonce_field($nonceAction);

        foreach ($fields as $name => $value) {
            echo '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '">';
        }

        echo '<button type="submit" class="button button-secondary">' . esc_html($label) . '</button>';
        echo '</form>';
    }

    private static function maskConnectionSummary(array $config): string
    {
        $keys = ['client_secret', 'access_token', 'refresh_token', 'password'];
        $parts = [];

        foreach ($keys as $key) {
            if (empty($config[$key])) {
                continue;
            }

            $parts[] = $key . ':' . self::maskValue((string) $config[$key]);
        }

        return empty($parts) ? 'Keine sensiblen Werte hinterlegt oder ausgeblendet.' : implode(' | ', $parts);
    }

    private static function maskValue(string $value): string
    {
        $length = strlen($value);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($value, 0, 2) . str_repeat('*', max(2, $length - 4)) . substr($value, -2);
    }
}
