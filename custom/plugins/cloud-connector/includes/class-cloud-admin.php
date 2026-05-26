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
            'connections' => 'Verbindungen',
            'sync-jobs' => 'Sync-Jobs',
            'automation' => 'Automatisierung',
            'explorer' => 'Explorer',
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
            case 'explorer':
                self::renderExplorer();
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
            'connection_created' => 'Verbindung vorbereitet gespeichert.',
            'connection_deleted' => 'Verbindung geloescht.',
            'connection_updated' => 'Verbindung aktualisiert.',
            'connection_disabled' => 'Verbindung deaktiviert.',
            'connection_test_mode_set' => 'Verbindung auf Testmodus gesetzt.',
            'connection_missing' => 'Verbindung nicht gefunden.',
            'provider_missing' => 'Anbieter nicht gefunden.',
            'job_saved' => 'Sync-Job gespeichert.',
            'job_deleted' => 'Sync-Job geloescht.',
            'job_updated' => 'Sync-Job aktualisiert.',
            'job_paused' => 'Sync-Job pausiert.',
            'job_resumed' => 'Sync-Job fortgesetzt.',
            'job_simulation_started' => 'Safe-Mode-Simulationslauf ausgelost.',
            'job_missing' => 'Sync-Job nicht gefunden.',
            'files_refreshed' => 'Dateiliste aus Demo-/Cache-Daten aktualisiert.',
            'settings_saved' => 'Einstellungen gespeichert.',
            'schema_incomplete' => 'Die Datenbankbasis des Cloud Connectors ist unvollstaendig. Aktion wurde nicht ausgefuehrt.',
        ];

        return $messages[$key] ?? 'Aktion ausgefuehrt.';
    }

    private static function renderOverview(): void
    {
        $providers = CloudStorage::getProviders();
        $connections = CloudStorage::getConnections();
        $jobs = CloudStorage::getJobs();
        $safeMode = CloudStorage::getSetting('safe_mode', '1') === '1' ? 'Aktiv' : 'Deaktiviert';
        $schemaStatus = CloudStorage::schemaReady() ? 'Bereit' : 'Recovery-Modus';

        echo '<p>V1 stellt nur die sichere Verwaltungsbasis bereit. Produktive Sync-, Move- und Delete-Aktionen bleiben blockiert.</p>';
        echo '<table class="widefat striped"><tbody>';
        echo '<tr><td><strong>Safe-Mode</strong></td><td>' . esc_html($safeMode) . '</td></tr>';
        echo '<tr><td><strong>Schema-Status</strong></td><td>' . esc_html($schemaStatus) . '</td></tr>';
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
        $selectedConnectionStatus = ($editConnection['status'] ?? '') === 'aktiv' ? 'aktiv' : 'inaktiv';
        $providerGuides = self::getProviderGuides();
        $selectedProviderSlug = (string) ($editConnection['provider_slug'] ?? ($providers[0]['slug'] ?? 'google_drive'));

        echo '<h2>Verbindungen</h2>';
        echo '<p>Safe-Mode bleibt aktiv. Diese UI speichert nur vorbereitete Provider-Verbindungen und loest keine OAuth- oder API-Aufrufe aus.</p>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Anbieter</th><th>Anzeigename</th><th>Status</th><th>Modus</th><th>Client ID</th><th>Client Secret</th><th>Redirect URI</th><th>Token vorhanden</th><th>Erstellt am</th><th>Aktualisiert am</th><th>Aktionen</th></tr></thead><tbody>';

        foreach ($connections as $connection) {
            $rowConfig = CloudCrypto::decryptConfig((string) $connection['config_encrypted']);
            $editUrl = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => 'connections', 'edit_connection' => (int) $connection['id']], admin_url('admin.php')));
            $hasToken = !empty($rowConfig['access_token']) || !empty($rowConfig['refresh_token']);

            echo '<tr>';
            echo '<td>' . esc_html((string) $connection['id']) . '</td>';
            echo '<td><code>' . esc_html($connection['provider_slug']) . '</code></td>';
            echo '<td>' . esc_html($connection['name']) . '</td>';
            echo '<td>' . self::renderStatusBadge((string) $connection['status']) . '</td>';
            echo '<td>' . esc_html(self::describeConnectionMode($connection, $rowConfig)) . '</td>';
            echo '<td><code>' . esc_html(self::maskCredential((string) ($rowConfig['client_id'] ?? ''))) . '</code></td>';
            echo '<td><code>' . esc_html(self::maskCredential((string) ($rowConfig['client_secret'] ?? ''))) . '</code></td>';
            echo '<td>' . self::renderOptionalUrl((string) ($rowConfig['redirect_uri'] ?? '')) . '</td>';
            echo '<td>' . esc_html($hasToken ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html((string) ($connection['created_at'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($connection['updated_at'] ?: '-')) . '</td>';
            echo '<td>';
            echo '<a class="button button-secondary" href="' . $editUrl . '">Bearbeiten</a> ';
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'set_test_mode'], 'Testmodus');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'deactivate'], 'Deaktivieren');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_delete_connection', 'cc_delete_connection', ['id' => (int) $connection['id']], 'Loeschen');
            echo '</td>';
            echo '</tr>';
        }

        if (empty($connections)) {
            echo '<tr><td colspan="12">Noch keine vorbereiteten Verbindungen vorhanden.</td></tr>';
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
        echo '<tr><th><label for="name">Anzeigename</label></th><td><input class="regular-text" id="name" name="name" value="' . esc_attr((string) ($editConnection['name'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="status">Status</label></th><td><select id="status" name="status">';
        echo '<option value="aktiv"' . selected('aktiv', $selectedConnectionStatus, false) . '>Aktiv</option>';
        echo '<option value="inaktiv"' . selected('inaktiv', $selectedConnectionStatus, false) . '>Inaktiv</option>';
        echo '</select><p class="description">Safe-Mode bleibt auch bei aktivem Status eingeschaltet.</p></td></tr>';
        echo '<tr><th><label for="client_id">Client ID</label></th><td><input class="regular-text" id="client_id" name="client_id" value="' . esc_attr((string) ($config['client_id'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="client_secret">Client Secret</label></th><td><input class="regular-text" id="client_secret" name="client_secret" value=""><p class="description">Leer lassen, um das bestehende Secret beizubehalten.</p></td></tr>';
        echo '<tr><th><label for="redirect_uri">Redirect URI</label></th><td><input class="regular-text" id="redirect_uri" name="redirect_uri" value="' . esc_attr((string) ($config['redirect_uri'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="notes">Notizen</label></th><td><textarea class="large-text" rows="4" id="notes" name="notes">' . esc_textarea((string) ($config['notes'] ?? '')) . '</textarea><p class="description">Google Drive, Dropbox, OneDrive, Local Storage sowie vorbereitete Nextcloud-/WebDAV- und SFTP-Konfigurationen bleiben in dieser Stufe ohne echten OAuth- oder API-Handshake.</p></td></tr>';
        echo '</tbody></table>';
        submit_button($editConnection ? 'Verbindung aktualisieren' : 'Verbindung speichern');
        echo '</form>';

        echo '<div style="margin-top:24px;padding:16px;border:1px solid #dcdcde;background:#fff;">';
        echo '<h2 style="margin-top:0;">OAuth-/Provider-Informationen</h2>';
        echo '<p>Noch kein produktiver OAuth-Flow aktiv. Die folgenden Angaben dienen nur der sicheren Vorbereitung im Safe-Mode.</p>';
        echo '<p><label for="cc_provider_guide_select"><strong>Infoprovider</strong></label> ';
        echo '<select id="cc_provider_guide_select" style="min-width:240px;">';

        foreach ($providerGuides as $slug => $guide) {
            echo '<option value="' . esc_attr($slug) . '"' . selected($slug, $selectedProviderSlug, false) . '>' . esc_html((string) $guide['title']) . '</option>';
        }

        echo '</select></p>';

        foreach ($providerGuides as $slug => $guide) {
            $display = $slug === $selectedProviderSlug ? 'block' : 'none';
            $redirectUri = (string) ($guide['redirect_uri'] ?? '');
            $scopes = isset($guide['scopes']) && is_array($guide['scopes']) ? $guide['scopes'] : [];
            $scopeText = implode("\n", array_map('strval', $scopes));

            echo '<div class="cc-provider-guide" data-provider-guide="' . esc_attr($slug) . '" style="display:' . esc_attr($display) . ';margin-top:16px;">';
            echo '<h3 style="margin-bottom:8px;">' . esc_html((string) $guide['title']) . '</h3>';
            echo '<p>' . self::renderInfoBadge((string) $guide['status_label']) . ' ' . self::renderInfoBadge((string) $guide['docs_status']) . '</p>';
            echo '<table class="widefat striped"><tbody>';
            echo '<tr><td style="width:180px;"><strong>Redirect URI</strong></td><td>' . self::renderProviderGuideValue($redirectUri) . '</td><td style="width:140px;">' . self::renderCopyButton($redirectUri, 'Redirect URI kopieren') . '</td></tr>';
            echo '<tr><td><strong>Empfohlene Scopes</strong></td><td>' . self::renderScopeList($scopes) . '</td><td>' . self::renderCopyButton($scopeText, 'Scopes kopieren') . '</td></tr>';
            echo '<tr><td><strong>Hinweis</strong></td><td colspan="2">' . esc_html((string) $guide['hint']) . '</td></tr>';
            echo '<tr><td><strong>Dokumentationsstatus</strong></td><td colspan="2">' . esc_html((string) $guide['docs_note']) . '</td></tr>';
            echo '<tr><td><strong>Safe-Mode</strong></td><td colspan="2">Aktiv - keine Redirect-Ausfuehrung, keine Token-Anforderung, keine externen API-Calls.</td></tr>';
            echo '</tbody></table>';
            echo '</div>';
        }

        echo '</div>';
        self::renderProviderGuideScript($selectedProviderSlug);
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

        echo '<p>Safe-Mode aktiv - keine echten Dateioperationen. Manuelle Simulationen laufen ausschliesslich ueber den bestehenden Worker.</p>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Anbieter</th><th>Richtung</th><th>Status</th><th>Naechster Lauf</th><th>Letzter Lauf</th><th>Dateien</th><th>Letzte Fehlermeldung</th><th>Erstellt am</th><th>Aktualisiert am</th><th>Aktionen</th></tr></thead><tbody>';

        foreach ($jobs as $job) {
            $editUrl = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => 'sync-jobs', 'edit_job' => (int) $job['id']], admin_url('admin.php')));
            $isPaused = ($job['status'] ?? '') === 'pausiert';

            echo '<tr>';
            echo '<td>' . esc_html((string) $job['id']) . '</td>';
            echo '<td><code>' . esc_html($job['provider_slug']) . '</code></td>';
            echo '<td>' . esc_html($job['direction']) . '</td>';
            echo '<td>' . self::renderStatusBadge((string) $job['status']) . '</td>';
            echo '<td>' . esc_html((string) ($job['next_run'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($job['last_run'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) $job['file_count']) . '</td>';
            echo '<td>' . esc_html((string) ($job['error_text'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($job['created_at'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($job['updated_at'] ?: '-')) . '</td>';
            echo '<td>';
            echo '<a class="button button-secondary" href="' . $editUrl . '">Bearbeiten</a> ';
            self::inlinePostButton('admin-post.php?action=cc_job_action', 'cc_job_action', ['id' => (int) $job['id'], 'job_action' => 'simulate'], 'Simulation');
            echo ' ';
            if ($isPaused) {
                self::inlinePostButton('admin-post.php?action=cc_job_action', 'cc_job_action', ['id' => (int) $job['id'], 'job_action' => 'resume'], 'Fortsetzen');
            } else {
                self::inlinePostButton('admin-post.php?action=cc_job_action', 'cc_job_action', ['id' => (int) $job['id'], 'job_action' => 'pause'], 'Pausieren');
            }
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_delete_job', 'cc_delete_job', ['id' => (int) $job['id']], 'Loeschen');
            echo '</td>';
            echo '</tr>';
        }

        if (empty($jobs)) {
            echo '<tr><td colspan="11">Noch keine Jobs vorhanden.</td></tr>';
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
        $schedulerStatus = 'Nicht registriert';
        $nextRun = '-';

        if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) {
            $schedulerStatus = 'WP-Cron deaktiviert';
        } elseif (CloudJobRunner::isScheduled()) {
            $schedulerStatus = 'Registriert';
            $timestamp = CloudJobRunner::nextRunTimestamp();
            $nextRun = $timestamp ? wp_date('Y-m-d H:i:s', $timestamp) : '-';
        }

        echo '<p>Die Automatisierungsbasis ist vorbereitet, fuehrt in V1 aber keine produktiven Syncs aus.</p>';
        echo '<table class="widefat striped" style="max-width:720px; margin-bottom:16px;"><tbody>';
        echo '<tr><td><strong>Hook</strong></td><td><code>' . esc_html(CloudJobRunner::HOOK) . '</code></td></tr>';
        echo '<tr><td><strong>Scheduler-Status</strong></td><td>' . esc_html($schedulerStatus) . '</td></tr>';
        echo '<tr><td><strong>Naechster geplanter Worker-Lauf</strong></td><td>' . esc_html($nextRun) . '</td></tr>';
        echo '</tbody></table>';
        echo '<ul style="list-style:disc; padding-left:20px;">';
        echo '<li>Automatischer Worker nutzt den Hook <code>' . esc_html(CloudJobRunner::HOOK) . '</code></li>';
        echo '<li>Faellige Jobs mit Status <code>geplant</code> und gesetztem <code>next_run</code> werden nur simuliert</li>';
        echo '<li>Keine externen API-Requests im normalen Backend-Rendering</li>';
        echo '<li>Timeout-Vorgabe fuer spaetere Worker: ' . esc_html(CloudStorage::getSetting('http_timeout', '5')) . ' Sekunden</li>';
        echo '</ul>';
    }

    private static function renderExplorer(): void
    {
        $guides = self::getProviderGuides();
        $connections = CloudStorage::getConnections();
        $jobs = CloudStorage::getJobs();
        $logs = CloudStorage::getLogs(20);
        $fileCache = CloudStorage::getFileCache();
        $safeModeEnabled = CloudStorage::getSetting('safe_mode', '1') === '1';
        $selectedProvider = sanitize_key(wp_unslash($_GET['explorer_provider'] ?? ''));
        $providerKeys = array_keys($guides);

        if ($selectedProvider === '' || !isset($guides[$selectedProvider])) {
            $selectedProvider = $providerKeys[0] ?? 'google_drive';
        }

        $selectedConnectionId = absint(wp_unslash($_GET['explorer_connection'] ?? 0));
        $providerConnections = array_values(array_filter(
            $connections,
            static fn(array $connection): bool => (string) ($connection['provider_slug'] ?? '') === $selectedProvider
        ));

        if ($selectedConnectionId > 0) {
            $selectedConnection = null;

            foreach ($providerConnections as $connection) {
                if ((int) $connection['id'] === $selectedConnectionId) {
                    $selectedConnection = $connection;
                    break;
                }
            }
            if ($selectedConnection === null) {
                $selectedConnection = $providerConnections[0] ?? null;
            }
        } else {
            $selectedConnection = $providerConnections[0] ?? null;
        }

        $selectedConnectionId = (int) ($selectedConnection['id'] ?? 0);
        $explorerFiles = self::buildExplorerRows($selectedProvider, $selectedConnection, $jobs, $logs, $fileCache);
        $previewRows = self::buildExplorerPreviewRows($explorerFiles, $selectedProvider, $selectedConnection, $jobs, $logs);
        $conflictRows = self::buildExplorerConflictRows($previewRows, $selectedProvider);
        $health = self::buildExplorerHealth($jobs, $logs, $previewRows, $safeModeEnabled);
        $detailPanel = self::buildExplorerDetailPanel($explorerFiles, $previewRows, $selectedProvider, $selectedConnection);

        echo '<div style="margin-bottom:16px;padding:12px 16px;border:1px solid #dcdcde;background:#fff;">';
        echo '<strong>Safe-Mode aktiv.</strong> Explorer-Daten stammen nur aus Cache-, DB- und Mockquellen. Es werden keine Provider-Requests, OAuth-Flows oder Dateioperationen ausgeloest.';
        echo '</div>';

        echo '<div style="display:flex;gap:16px;align-items:flex-start;flex-wrap:wrap;">';
        echo '<div style="flex:1 1 300px;min-width:300px;max-width:360px;">';
        echo '<div style="border:1px solid #dcdcde;background:#fff;padding:16px;">';
        echo '<h2 style="margin-top:0;">Provider / Verbindungen</h2>';
        echo '<p style="margin-top:0;color:#50575e;">Virtuelle Ordnerstruktur ohne Live-Abfrage.</p>';

        foreach ($guides as $providerSlug => $guide) {
            $providerUrl = esc_url(add_query_arg([
                'page' => self::MENU_SLUG,
                'tab' => 'explorer',
                'explorer_provider' => $providerSlug,
            ], admin_url('admin.php')));
            $providerClass = $providerSlug === $selectedProvider ? 'background:#f0f6fc;border-color:#72aee6;' : '';

            echo '<div style="margin-bottom:12px;padding:12px;border:1px solid #dcdcde;' . esc_attr($providerClass) . '">';
            echo '<div style="display:flex;justify-content:space-between;gap:8px;align-items:center;">';
            echo '<a href="' . $providerUrl . '" style="font-weight:600;text-decoration:none;">' . esc_html((string) $guide['title']) . '</a>';
            echo self::renderStatusBadge(self::providerGuideToExplorerStatus($providerSlug));
            echo '</div>';

            $connectionList = array_values(array_filter(
                $connections,
                static fn(array $connection): bool => (string) ($connection['provider_slug'] ?? '') === $providerSlug
            ));

            echo '<div style="margin-top:10px;"><strong style="display:block;margin-bottom:6px;">Verbindungen</strong>';

            if (empty($connectionList)) {
                echo '<div style="color:#646970;">Keine vorbereiteten Verbindungen.</div>';
            } else {
                echo '<ul style="margin:0;padding-left:18px;">';

                foreach ($connectionList as $connection) {
                    $connectionUrl = esc_url(add_query_arg([
                        'page' => self::MENU_SLUG,
                        'tab' => 'explorer',
                        'explorer_provider' => $providerSlug,
                        'explorer_connection' => (int) $connection['id'],
                    ], admin_url('admin.php')));
                    $isSelectedConnection = $providerSlug === $selectedProvider && (int) $connection['id'] === $selectedConnectionId;

                    echo '<li style="margin-bottom:4px;">';
                    echo '<a href="' . $connectionUrl . '" style="text-decoration:none;' . ($isSelectedConnection ? 'font-weight:600;' : '') . '">' . esc_html((string) $connection['name']) . '</a> ';
                    echo self::renderStatusBadge((string) $connection['status']);
                    echo '</li>';
                }

                echo '</ul>';
            }

            echo '</div>';
            echo '<div style="margin-top:10px;"><strong style="display:block;margin-bottom:6px;">Virtuelle Ordner</strong>';
            echo '<ul style="margin:0;padding-left:18px;">';

            foreach (['/', '/Dokumente', '/Uploads', '/Archiv', '/Sync Queue'] as $folder) {
                echo '<li><code>' . esc_html($folder) . '</code></li>';
            }

            echo '</ul></div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';

        echo '<div style="flex:2 1 640px;min-width:320px;">';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;">';

        foreach ($health as $card) {
            echo '<div style="border:1px solid #dcdcde;background:#fff;padding:14px;">';
            echo '<div style="font-size:12px;text-transform:uppercase;color:#646970;margin-bottom:8px;">' . esc_html($card['label']) . '</div>';
            echo '<div style="font-size:22px;font-weight:600;line-height:1.2;">' . esc_html($card['value']) . '</div>';
            if ($card['note'] !== '') {
                echo '<div style="margin-top:6px;color:#50575e;">' . esc_html($card['note']) . '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '<div style="border:1px solid #dcdcde;background:#fff;padding:16px;">';
        echo '<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">';
        echo '<div>';
        echo '<h2 style="margin-top:0;">Explorer</h2>';
        echo '<p style="margin-top:0;color:#50575e;">';
        echo esc_html((string) $guides[$selectedProvider]['title']);
        if ($selectedConnection) {
            echo ' / ' . esc_html((string) $selectedConnection['name']);
        }
        echo '</p>';
        echo '</div>';
        echo '<div>' . self::renderStatusBadge('safe-mode') . '</div>';
        echo '</div>';
        echo '<div style="overflow:auto;">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Dateiname</th><th>Typ</th><th>Groesse</th><th>Provider</th><th>Sync-Richtung</th><th>Status</th><th>Letzte Aenderung</th><th>Letzter Sync</th><th>Konfliktstatus</th></tr></thead><tbody>';

        foreach ($explorerFiles as $row) {
            $fileKey = md5($row['name'] . '|' . $row['provider'] . '|' . $row['last_sync']);
            echo '<tr>';
            echo '<td><button type="button" class="button-link cc-explorer-file-trigger" data-file-key="' . esc_attr($fileKey) . '" style="font-weight:600;text-align:left;">' . esc_html($row['name']) . '</button></td>';
            echo '<td>' . esc_html($row['type']) . '</td>';
            echo '<td>' . esc_html($row['size']) . '</td>';
            echo '<td>' . esc_html($row['provider']) . '</td>';
            echo '<td><code>' . esc_html($row['direction']) . '</code></td>';
            echo '<td>' . self::renderStatusBadge($row['status']) . '</td>';
            echo '<td>' . esc_html($row['modified']) . '</td>';
            echo '<td>' . esc_html($row['last_sync']) . '</td>';
            echo '<td>' . self::renderStatusBadge($row['conflict']) . '</td>';
            echo '</tr>';
        }

        if (empty($explorerFiles)) {
            echo '<tr><td colspan="9">Keine Explorer-Daten verfuegbar.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '<div id="cc-file-detail-panel" style="margin-top:16px;border:1px solid #dcdcde;background:#f6f7f7;padding:16px;">';
        echo '<h3 style="margin-top:0;">Datei-Detailpanel</h3>';
        echo '<p style="margin-top:0;color:#50575e;">Datei im Explorer anklicken, um virtuelle Sync- und Konfliktdetails anzuzeigen.</p>';
        echo '<div id="cc-file-detail-content">';
        echo self::renderExplorerDetailHtml($detailPanel['initial']);
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div style="border:1px solid #dcdcde;background:#fff;padding:16px;margin-top:16px;">';
        echo '<div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">';
        echo '<div>';
        echo '<h2 style="margin-top:0;">Sync Preview / Dry Run</h2>';
        echo '<p style="margin-top:0;color:#50575e;">Geplanter Sync, virtuelle Konflikte und Warteschlange bleiben reine Simulation. Keine Dateioperationen werden ausgefuehrt.</p>';
        echo '</div>';
        echo '<div>' . self::renderStatusBadge('preview') . ' ' . self::renderStatusBadge('safe-mode') . '</div>';
        echo '</div>';
        echo '<div style="overflow:auto;">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Datei</th><th>Quelle</th><th>Ziel</th><th>Aktion</th><th>Status</th><th>Groesse</th><th>Zeitstempel</th></tr></thead><tbody>';

        foreach ($previewRows as $previewRow) {
            echo '<tr>';
            echo '<td>' . esc_html($previewRow['file']) . '</td>';
            echo '<td><code>' . esc_html($previewRow['source']) . '</code></td>';
            echo '<td><code>' . esc_html($previewRow['target']) . '</code></td>';
            echo '<td>' . esc_html($previewRow['action']) . '</td>';
            echo '<td>' . self::renderStatusBadge($previewRow['status']) . '</td>';
            echo '<td>' . esc_html($previewRow['size']) . '</td>';
            echo '<td>' . esc_html($previewRow['timestamp']) . '</td>';
            echo '</tr>';
        }

        if (empty($previewRows)) {
            echo '<tr><td colspan="7">Noch keine simulierten Preview-Daten verfuegbar.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:16px;">';
        echo '<div style="border:1px solid #dcdcde;background:#f6f7f7;padding:16px;">';
        echo '<h3 style="margin-top:0;">Virtuelle Konflikte</h3>';
        echo '<table class="widefat striped"><thead><tr><th>Datei</th><th>Lokaler Zustand</th><th>Cloud-Zustand</th><th>Konfliktstatus</th></tr></thead><tbody>';

        foreach ($conflictRows as $conflictRow) {
            echo '<tr>';
            echo '<td>' . esc_html($conflictRow['file']) . '</td>';
            echo '<td>' . esc_html($conflictRow['local']) . '</td>';
            echo '<td>' . esc_html($conflictRow['cloud']) . '</td>';
            echo '<td>' . self::renderStatusBadge($conflictRow['status']) . '</td>';
            echo '</tr>';
        }

        if (empty($conflictRows)) {
            echo '<tr><td colspan="4">Keine virtuellen Konflikte im aktuellen Preview-Fenster.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '<div style="border:1px solid #dcdcde;background:#f6f7f7;padding:16px;">';
        echo '<h3 style="margin-top:0;">Virtuelle Warteschlange</h3>';
        echo '<ul style="margin:0;padding-left:18px;">';
        foreach ($previewRows as $previewRow) {
            echo '<li style="margin-bottom:6px;">';
            echo '<strong>' . esc_html($previewRow['file']) . '</strong> - ' . esc_html($previewRow['action']) . ' - ' . self::renderStatusBadge($previewRow['status']);
            echo '</li>';
        }
        if (empty($previewRows)) {
            echo '<li>Keine Eintraege in der virtuellen Queue.</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        self::renderExplorerScript($detailPanel['map']);
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

    private static function renderStatusBadge(string $status): string
    {
        $styles = [
            'geplant' => ['#dbeafe', '#1d4ed8'],
            'pausiert' => ['#e5e7eb', '#374151'],
            'erfolgreich' => ['#dcfce7', '#166534'],
            'fehlerhaft' => ['#fee2e2', '#991b1b'],
            'laeuft' => ['#fef3c7', '#92400e'],
            'aktiv' => ['#dcfce7', '#166534'],
            'inaktiv' => ['#e5e7eb', '#374151'],
            'testmodus' => ['#fef3c7', '#92400e'],
            'synchronisiert' => ['#dcfce7', '#166534'],
            'ausstehend' => ['#fef3c7', '#92400e'],
            'konflikt' => ['#fee2e2', '#991b1b'],
            'simuliert' => ['#ede9fe', '#6d28d9'],
            'offline' => ['#e5e7eb', '#374151'],
            'safe-mode' => ['#e0f2fe', '#075985'],
            'keiner' => ['#f3f4f6', '#111827'],
            'preview' => ['#f3e8ff', '#7e22ce'],
            'queued' => ['#dbeafe', '#1d4ed8'],
            'readonly' => ['#f3f4f6', '#111827'],
        ];

        [$background, $color] = $styles[$status] ?? ['#f3f4f6', '#111827'];

        return sprintf(
            '<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:%s;color:%s;font-weight:600;">%s</span>',
            esc_attr($background),
            esc_attr($color),
            esc_html($status)
        );
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

    private static function maskCredential(string $value): string
    {
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '-';
        }

        $length = strlen($trimmed);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($trimmed, 0, 3) . str_repeat('*', max(4, $length - 5)) . substr($trimmed, -2);
    }

    private static function renderOptionalUrl(string $url): string
    {
        if ($url === '') {
            return '-';
        }

        return '<code>' . esc_html($url) . '</code>';
    }

    private static function describeConnectionMode(array $connection, array $config): string
    {
        $status = (string) ($connection['status'] ?? '');

        if ($status === 'testmodus') {
            return 'Test';
        }

        if ($status === 'aktiv' && (!empty($config['client_id']) || !empty($config['client_secret']))) {
            return 'Live vorbereitet';
        }

        return 'Safe';
    }

    private static function renderInfoBadge(string $label): string
    {
        $styles = [
            'vorbereitet' => ['#dbeafe', '#1d4ed8'],
            'geplant' => ['#fef3c7', '#92400e'],
            'kein OAuth erforderlich' => ['#dcfce7', '#166534'],
        ];

        [$background, $color] = $styles[$label] ?? ['#f3f4f6', '#111827'];

        return sprintf(
            '<span style="display:inline-block;margin-right:8px;padding:2px 8px;border-radius:999px;background:%s;color:%s;font-weight:600;">%s</span>',
            esc_attr($background),
            esc_attr($color),
            esc_html($label)
        );
    }

    private static function renderProviderGuideValue(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        return '<code>' . esc_html($value) . '</code>';
    }

    private static function renderScopeList(array $scopes): string
    {
        if (empty($scopes)) {
            return 'Keine OAuth-Scopes erforderlich.';
        }

        $items = array_map(
            static fn($scope): string => '<li><code>' . esc_html((string) $scope) . '</code></li>',
            $scopes
        );

        return '<ul style="margin:0;padding-left:18px;">' . implode('', $items) . '</ul>';
    }

    private static function renderCopyButton(string $value, string $label): string
    {
        if ($value === '') {
            return '<span style="color:#646970;">Nicht erforderlich</span>';
        }

        return '<button type="button" class="button button-secondary cc-copy-button" data-copy-label="' . esc_attr($label) . '" data-copy-value="' . esc_attr($value) . '">' . esc_html($label) . '</button>';
    }

    private static function renderProviderGuideScript(string $selectedProviderSlug): void
    {
        echo '<script>';
        echo '(function(){';
        echo 'const providerSelect=document.getElementById("provider_slug");';
        echo 'const guideSelect=document.getElementById("cc_provider_guide_select");';
        echo 'const guides=Array.from(document.querySelectorAll("[data-provider-guide]"));';
        echo 'const toggleGuide=function(slug){guides.forEach(function(node){node.style.display=node.getAttribute("data-provider-guide")===slug?"block":"none";}); if(guideSelect){guideSelect.value=slug;}};';
        echo 'const initialSlug=(providerSelect&&providerSelect.value)?providerSelect.value:(guideSelect&&guideSelect.value?guideSelect.value:"' . esc_js($selectedProviderSlug) . '");';
        echo 'toggleGuide(initialSlug);';
        echo 'if(providerSelect){providerSelect.addEventListener("change",function(){toggleGuide(providerSelect.value);});}';
        echo 'if(guideSelect){guideSelect.addEventListener("change",function(){toggleGuide(guideSelect.value);});}';
        echo 'document.querySelectorAll(".cc-copy-button").forEach(function(button){button.addEventListener("click",function(){const value=button.getAttribute("data-copy-value")||\"\"; const originalLabel=button.getAttribute(\"data-copy-label\")||button.textContent; if(!value){return;} if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(value);} button.textContent=\"Kopiert\"; window.setTimeout(function(){button.textContent=originalLabel;},1200);});});';
        echo '})();';
        echo '</script>';
    }

    private static function getProviderGuides(): array
    {
        $redirectUri = 'https://www.webapp-central.de/wp-admin/admin.php?page=cloud_connector';

        return [
            'google_drive' => [
                'title' => 'Google Drive',
                'redirect_uri' => $redirectUri,
                'scopes' => [
                    'https://www.googleapis.com/auth/drive.metadata.readonly',
                    'https://www.googleapis.com/auth/drive.file',
                ],
                'hint' => 'Nur vorbereitende Angaben. Es wird kein OAuth-Redirect erzeugt und kein Token angefordert.',
                'status_label' => 'vorbereitet',
                'docs_status' => 'geplant',
                'docs_note' => 'Scopes und Redirect sind fuer eine spaetere OAuth-Stufe vorgemerkt.',
            ],
            'dropbox' => [
                'title' => 'Dropbox',
                'redirect_uri' => $redirectUri,
                'scopes' => [
                    'files.metadata.read',
                    'files.content.write',
                ],
                'hint' => 'V1 zeigt nur die spaeteren Scope-Anforderungen. Noch kein produktiver OAuth-Flow aktiv.',
                'status_label' => 'vorbereitet',
                'docs_status' => 'geplant',
                'docs_note' => 'Nur Informationsdarstellung fuer die spaetere App-Konfiguration.',
            ],
            'onedrive' => [
                'title' => 'OneDrive',
                'redirect_uri' => $redirectUri,
                'scopes' => [
                    'Files.ReadWrite',
                    'offline_access',
                ],
                'hint' => 'Die Microsoft-Scopes werden nur angezeigt. Es werden keine Redirects, Tokens oder API-Requests ausgeloest.',
                'status_label' => 'vorbereitet',
                'docs_status' => 'geplant',
                'docs_note' => 'Azure-App-Registrierung bleibt fuer spaetere Ausbauphasen dokumentiert.',
            ],
            'webdav' => [
                'title' => 'WebDAV / Nextcloud',
                'redirect_uri' => '',
                'scopes' => [],
                'hint' => 'Verwendet typischerweise URL + Benutzername + Passwort oder App-Token.',
                'status_label' => 'kein OAuth erforderlich',
                'docs_status' => 'vorbereitet',
                'docs_note' => 'Credential-basierter Provider ohne OAuth, weiterhin nur als Safe-Mode-Vorbereitung.',
            ],
            'sftp' => [
                'title' => 'SFTP',
                'redirect_uri' => '',
                'scopes' => [],
                'hint' => 'Verwendet Host, Benutzername, Passwort oder SSH-Key.',
                'status_label' => 'kein OAuth erforderlich',
                'docs_status' => 'vorbereitet',
                'docs_note' => 'Nur Informationskarte; noch kein eigener Verbindungsprovider im Formular.',
            ],
            'local_storage' => [
                'title' => 'Local Storage',
                'redirect_uri' => '',
                'scopes' => [],
                'hint' => 'Lokale Pfade benoetigen keinen OAuth-Flow. Auch hier bleibt alles im Safe-Mode.',
                'status_label' => 'kein OAuth erforderlich',
                'docs_status' => 'vorbereitet',
                'docs_note' => 'Kein externer Provider-Handshake erforderlich.',
            ],
        ];
    }

    private static function providerGuideToExplorerStatus(string $providerSlug): string
    {
        if (in_array($providerSlug, ['webdav', 'sftp', 'local_storage'], true)) {
            return 'offline';
        }

        return 'safe-mode';
    }

    private static function buildExplorerRows(string $selectedProvider, ?array $selectedConnection, array $jobs, array $logs, array $fileCache): array
    {
        $providerLabel = self::getProviderGuides()[$selectedProvider]['title'] ?? $selectedProvider;
        $selectedConnectionId = (int) ($selectedConnection['id'] ?? 0);
        $connectionFileCache = array_values(array_filter(
            $fileCache,
            static fn(array $row): bool => $selectedConnectionId > 0 ? (int) ($row['connection_id'] ?? 0) === $selectedConnectionId : true
        ));
        $jobForProvider = null;

        foreach ($jobs as $job) {
            if ((string) ($job['provider_slug'] ?? '') === $selectedProvider) {
                $jobForProvider = $job;
                break;
            }
        }

        $logMap = self::buildExplorerLogMap($logs);
        $defaults = [
            ['name' => 'angebot-final.pdf', 'type' => 'PDF', 'size_bytes' => 348160, 'status' => 'synchronisiert', 'conflict' => 'keiner'],
            ['name' => 'pv-anlage-plan.xlsx', 'type' => 'XLSX', 'size_bytes' => 892928, 'status' => 'ausstehend', 'conflict' => 'keiner'],
            ['name' => 'kundenmappe.zip', 'type' => 'ZIP', 'size_bytes' => 5242880, 'status' => 'simuliert', 'conflict' => 'keiner'],
            ['name' => 'bild-hallenberg.webp', 'type' => 'WEBP', 'size_bytes' => 215040, 'status' => 'offline', 'conflict' => 'keiner'],
            ['name' => 'dokumentation.docx', 'type' => 'DOCX', 'size_bytes' => 471040, 'status' => 'safe-mode', 'conflict' => 'konflikt'],
        ];
        $rows = [];

        foreach ($defaults as $index => $default) {
            $cacheRow = $connectionFileCache[$index] ?? null;
            $name = (string) ($cacheRow['name'] ?? $default['name']);
            $mimeType = (string) ($cacheRow['mime_type'] ?? '');
            $lastModified = (string) ($cacheRow['last_modified'] ?? current_time('mysql'));
            $sizeBytes = isset($cacheRow['size_bytes']) ? (int) $cacheRow['size_bytes'] : (int) $default['size_bytes'];
            $rows[] = [
                'name' => $name,
                'type' => $cacheRow ? strtoupper((string) pathinfo($name, PATHINFO_EXTENSION)) : $default['type'],
                'size' => size_format($sizeBytes),
                'provider' => $providerLabel,
                'direction' => (string) ($jobForProvider['direction'] ?? ($index % 2 === 0 ? 'cloud_to_local' : 'local_to_cloud')),
                'status' => self::deriveExplorerFileStatus($default['status'], $jobForProvider, $mimeType, $logMap),
                'modified' => $lastModified,
                'last_sync' => (string) ($jobForProvider['last_run'] ?? ($logMap['last_simulation'] ?: '-')),
                'conflict' => self::deriveExplorerConflictStatus($default['conflict'], $jobForProvider),
            ];
        }

        return $rows;
    }

    private static function buildExplorerPreviewRows(array $explorerFiles, string $selectedProvider, ?array $selectedConnection, array $jobs, array $logs): array
    {
        $providerLabel = self::getProviderGuides()[$selectedProvider]['title'] ?? $selectedProvider;
        $sourceRoot = '/cloud/' . strtolower(str_replace(' ', '-', $providerLabel));
        $targetRoot = '/local/sync-preview';
        $connectionName = (string) ($selectedConnection['name'] ?? 'Standardverbindung');
        $logMap = self::buildExplorerLogMap($logs);
        $rows = [];
        $actions = ['download (simuliert)', 'upload (simuliert)', 'update (simuliert)', 'konflikt', 'ignoriert'];
        $statuses = ['preview', 'queued', 'safe-mode', 'konflikt', 'readonly'];

        foreach ($explorerFiles as $index => $file) {
            $rows[] = [
                'file' => $file['name'],
                'source' => $sourceRoot . '/' . rawurlencode($connectionName) . '/' . $file['name'],
                'target' => $targetRoot . '/' . $file['name'],
                'action' => $actions[$index % count($actions)],
                'status' => $statuses[$index % count($statuses)],
                'size' => $file['size'],
                'timestamp' => $file['last_sync'] !== '-' ? $file['last_sync'] : ($logMap['last_worker'] ?: current_time('mysql')),
                'provider' => $providerLabel,
                'last_sync' => $file['last_sync'],
                'direction' => $file['direction'],
                'conflict' => $file['conflict'],
            ];
        }

        return $rows;
    }

    private static function buildExplorerConflictRows(array $previewRows, string $selectedProvider): array
    {
        $conflicts = [];

        foreach ($previewRows as $previewRow) {
            if (!in_array($previewRow['status'], ['konflikt', 'readonly'], true) && $previewRow['conflict'] !== 'konflikt') {
                continue;
            }

            $conflicts[] = [
                'file' => $previewRow['file'],
                'local' => $previewRow['status'] === 'readonly' ? 'Neuere lokale Version' : 'Doppelte Datei erkannt',
                'cloud' => $previewRow['status'] === 'konflikt' ? 'Neuere Cloud-Version' : 'Cloud-Version unveraendert',
                'status' => 'konflikt',
            ];
        }

        if (empty($conflicts) && $selectedProvider === 'google_drive') {
            $conflicts[] = [
                'file' => 'dokumentation.docx',
                'local' => 'Neuere lokale Version',
                'cloud' => 'Neuere Cloud-Version',
                'status' => 'konflikt',
            ];
        }

        return $conflicts;
    }

    private static function buildExplorerLogMap(array $logs): array
    {
        $lastSimulation = '';
        $lastWorker = '';
        $lastError = '';

        foreach ($logs as $log) {
            $action = (string) ($log['action'] ?? '');

            if ($lastSimulation === '' && in_array($action, ['cron_simulation', 'sync_job_manual_simulation'], true)) {
                $lastSimulation = (string) ($log['created_at'] ?? '');
            }

            if ($lastWorker === '' && in_array($action, ['cron_simulation', 'cron_idle'], true)) {
                $lastWorker = (string) ($log['created_at'] ?? '');
            }

            if ($lastError === '' && (string) ($log['level'] ?? '') === 'error') {
                $lastError = (string) ($log['message'] ?? '');
            }
        }

        return [
            'last_simulation' => $lastSimulation,
            'last_worker' => $lastWorker,
            'last_error' => $lastError,
        ];
    }

    private static function deriveExplorerFileStatus(string $fallbackStatus, ?array $job, string $mimeType, array $logMap): string
    {
        if ($job) {
            $jobStatus = (string) ($job['status'] ?? '');

            if ($jobStatus === 'laeuft') {
                return 'simuliert';
            }

            if ($jobStatus === 'fehlerhaft') {
                return 'offline';
            }

            if ($jobStatus === 'geplant') {
                return 'ausstehend';
            }

            if ($jobStatus === 'erfolgreich') {
                return 'synchronisiert';
            }
        }

        if ($mimeType === 'application/zip') {
            return 'simuliert';
        }

        if ($logMap['last_simulation'] === '' && $fallbackStatus === 'safe-mode') {
            return 'safe-mode';
        }

        return $fallbackStatus;
    }

    private static function deriveExplorerConflictStatus(string $fallbackStatus, ?array $job): string
    {
        if ($job && (string) ($job['status'] ?? '') === 'fehlerhaft') {
            return 'konflikt';
        }

        return $fallbackStatus;
    }

    private static function buildExplorerHealth(array $jobs, array $logs, array $previewRows, bool $safeModeEnabled): array
    {
        $pendingJobs = 0;
        $queueSize = count($jobs);
        $processed = 0;
        $simulated = 0;
        $conflicts = 0;
        $ignored = 0;

        foreach ($jobs as $job) {
            if ((string) ($job['status'] ?? '') === 'geplant') {
                $pendingJobs++;
            }
        }

        foreach ($previewRows as $previewRow) {
            if ($previewRow['status'] === 'queued') {
                $processed++;
            }

            if ($previewRow['status'] === 'preview' || $previewRow['status'] === 'safe-mode') {
                $simulated++;
            }

            if ($previewRow['status'] === 'konflikt') {
                $conflicts++;
            }

            if ($previewRow['action'] === 'ignoriert' || $previewRow['status'] === 'readonly') {
                $ignored++;
            }
        }

        $logMap = self::buildExplorerLogMap($logs);

        return [
            [
                'label' => 'Pending Jobs',
                'value' => (string) $pendingJobs,
                'note' => 'Status geplant in der Queue',
            ],
            [
                'label' => 'Verarbeitet',
                'value' => (string) $processed,
                'note' => 'Virtuell als queued markiert',
            ],
            [
                'label' => 'Simuliert',
                'value' => (string) $simulated,
                'note' => 'Preview- und Safe-Mode-Eintraege',
            ],
            [
                'label' => 'Konflikt',
                'value' => (string) $conflicts,
                'note' => 'Virtuelle Konfliktfaelle',
            ],
            [
                'label' => 'Ignoriert',
                'value' => (string) $ignored,
                'note' => 'Read-only oder ignorierte Aenderungen',
            ],
            [
                'label' => 'Letzte Simulation',
                'value' => $logMap['last_simulation'] !== '' ? $logMap['last_simulation'] : '-',
                'note' => 'Aus Worker- oder Manuellogik',
            ],
            [
                'label' => 'Letzte Fehler',
                'value' => $logMap['last_error'] !== '' ? '1' : '0',
                'note' => $logMap['last_error'] !== '' ? $logMap['last_error'] : 'Keine Fehler im letzten Logfenster',
            ],
            [
                'label' => 'Queue-Groesse',
                'value' => (string) $queueSize,
                'note' => 'Alle bekannten Sync-Jobs',
            ],
            [
                'label' => 'Safe-Mode',
                'value' => $safeModeEnabled ? 'Aktiv' : 'Deaktiviert',
                'note' => 'Explorerdaten bleiben read-only',
            ],
            [
                'label' => 'Letzter Worker-Lauf',
                'value' => $logMap['last_worker'] !== '' ? $logMap['last_worker'] : '-',
                'note' => 'cron_idle oder cron_simulation',
            ],
            [
                'label' => 'Letzte Queue-Aktualisierung',
                'value' => $logMap['last_worker'] !== '' ? $logMap['last_worker'] : current_time('mysql'),
                'note' => 'Nur virtuelle Queue-Metadaten',
            ],
        ];
    }

    private static function buildExplorerDetailPanel(array $explorerFiles, array $previewRows, string $selectedProvider, ?array $selectedConnection): array
    {
        $providerLabel = self::getProviderGuides()[$selectedProvider]['title'] ?? $selectedProvider;
        $connectionName = (string) ($selectedConnection['name'] ?? 'Standardverbindung');
        $map = [];

        foreach ($explorerFiles as $index => $file) {
            $preview = $previewRows[$index] ?? null;
            $key = md5($file['name'] . '|' . $file['provider'] . '|' . $file['last_sync']);
            $detail = [
                'file_name' => $file['name'],
                'provider' => $providerLabel,
                'virtual_source' => (string) ($preview['source'] ?? '/cloud/' . $file['name']),
                'virtual_target' => (string) ($preview['target'] ?? '/local/' . $file['name']),
                'size' => $file['size'],
                'last_sync' => $file['last_sync'],
                'status' => $file['status'],
                'simulated_action' => (string) ($preview['action'] ?? 'update (simuliert)'),
                'conflict_status' => $file['conflict'],
                'checksum' => 'sim-' . substr(md5($connectionName . '|' . $file['name']), 0, 12),
            ];
            $map[$key] = $detail;
        }

        $initial = reset($map);

        if (!is_array($initial)) {
            $initial = [
                'file_name' => 'Keine Datei gewaehlt',
                'provider' => $providerLabel,
                'virtual_source' => '-',
                'virtual_target' => '-',
                'size' => '-',
                'last_sync' => '-',
                'status' => 'safe-mode',
                'simulated_action' => 'preview',
                'conflict_status' => 'keiner',
                'checksum' => 'sim-000000000000',
            ];
        }

        return [
            'initial' => $initial,
            'map' => $map,
        ];
    }

    private static function renderExplorerDetailHtml(array $detail): string
    {
        $html = '<table class="widefat striped"><tbody>';
        $html .= '<tr><td style="width:180px;"><strong>Dateiname</strong></td><td>' . esc_html((string) $detail['file_name']) . '</td></tr>';
        $html .= '<tr><td><strong>Provider</strong></td><td>' . esc_html((string) $detail['provider']) . '</td></tr>';
        $html .= '<tr><td><strong>Virtueller Quellpfad</strong></td><td><code>' . esc_html((string) $detail['virtual_source']) . '</code></td></tr>';
        $html .= '<tr><td><strong>Virtueller Zielpfad</strong></td><td><code>' . esc_html((string) $detail['virtual_target']) . '</code></td></tr>';
        $html .= '<tr><td><strong>Groesse</strong></td><td>' . esc_html((string) $detail['size']) . '</td></tr>';
        $html .= '<tr><td><strong>Letzter Sync</strong></td><td>' . esc_html((string) $detail['last_sync']) . '</td></tr>';
        $html .= '<tr><td><strong>Status</strong></td><td>' . self::renderStatusBadge((string) $detail['status']) . '</td></tr>';
        $html .= '<tr><td><strong>Simulierte Aktion</strong></td><td>' . esc_html((string) $detail['simulated_action']) . '</td></tr>';
        $html .= '<tr><td><strong>Konfliktstatus</strong></td><td>' . self::renderStatusBadge((string) $detail['conflict_status']) . '</td></tr>';
        $html .= '<tr><td><strong>Hash / Checksum</strong></td><td><code>' . esc_html((string) $detail['checksum']) . '</code></td></tr>';
        $html .= '</tbody></table>';

        return $html;
    }

    private static function renderExplorerScript(array $detailMap): void
    {
        echo '<script>';
        echo '(function(){';
        echo 'const detailRoot=document.getElementById("cc-file-detail-content");';
        echo 'const detailMap=' . wp_json_encode($detailMap) . ';';
        echo 'if(!detailRoot||!detailMap){return;}';
        echo 'const renderBadge=function(label){return \'<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:#f3f4f6;color:#111827;font-weight:600;">\'+label+\'</span>\';};';
        echo 'const renderTable=function(detail){return \'<table class="widefat striped"><tbody>\''
            . '+\'<tr><td style="width:180px;"><strong>Dateiname</strong></td><td>\'+detail.file_name+\'</td></tr>\''
            . '+\'<tr><td><strong>Provider</strong></td><td>\'+detail.provider+\'</td></tr>\''
            . '+\'<tr><td><strong>Virtueller Quellpfad</strong></td><td><code>\'+detail.virtual_source+\'</code></td></tr>\''
            . '+\'<tr><td><strong>Virtueller Zielpfad</strong></td><td><code>\'+detail.virtual_target+\'</code></td></tr>\''
            . '+\'<tr><td><strong>Groesse</strong></td><td>\'+detail.size+\'</td></tr>\''
            . '+\'<tr><td><strong>Letzter Sync</strong></td><td>\'+detail.last_sync+\'</td></tr>\''
            . '+\'<tr><td><strong>Status</strong></td><td>\'+renderBadge(detail.status)+\'</td></tr>\''
            . '+\'<tr><td><strong>Simulierte Aktion</strong></td><td>\'+detail.simulated_action+\'</td></tr>\''
            . '+\'<tr><td><strong>Konfliktstatus</strong></td><td>\'+renderBadge(detail.conflict_status)+\'</td></tr>\''
            . '+\'<tr><td><strong>Hash / Checksum</strong></td><td><code>\'+detail.checksum+\'</code></td></tr>\''
            . '+\'</tbody></table>\';};';
        echo 'document.querySelectorAll(".cc-explorer-file-trigger").forEach(function(button){button.addEventListener("click",function(){const key=button.getAttribute("data-file-key")||""; if(!detailMap[key]){return;} detailRoot.innerHTML=renderTable(detailMap[key]);});});';
        echo '})();';
        echo '</script>';
    }
}
