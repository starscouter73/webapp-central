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
            'network' => 'Netzwerk',
            'logs' => 'Logs',
            'settings' => 'Einstellungen',
        ];

        echo '<div class="wrap">';
        echo '<h1>Cloud Connector</h1>';
        self::renderNotices();
        self::renderUiShellStyles();
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
            case 'network':
                self::renderNetwork();
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
            'connection_test_mode_set' => 'Verbindung auf Safe-Mode gesetzt.',
            'readonly_connection_tested' => 'Readonly-Verbindung erfolgreich getestet. Es wurden nur bis zu 5 Metadaten-Eintraege gelesen.',
            'readonly_connection_failed' => 'Readonly-Verbindungstest fehlgeschlagen.',
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
        $connectionMode = self::getConnectionMode($editConnection, $config);
        $selectedConnectionStatus = ($editConnection['status'] ?? '') === 'aktiv' ? 'aktiv' : 'inaktiv';
        $providerGuides = self::getProviderGuides();
        $selectedProviderSlug = (string) ($editConnection['provider_slug'] ?? ($providers[0]['slug'] ?? 'google_drive'));

        echo '<div class="cc-app-shell">';
        echo '<div class="cc-app-banner"><strong>App Shell aktiv.</strong> Provider-Verbindungen, Readonly-Hinweise und Statusmodule bleiben visuell zusammengefasst. Safe-Mode bleibt global Standard.</div>';
        echo '<div class="cc-app-card">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy"><h2>Verbindungen</h2><p class="cc-app-muted">Nur Verbindungen im Modus <strong>READONLY LIVE</strong> duerfen bei einer expliziten Admin-Aktion einen begrenzten Metadaten-Test gegen den Provider ausfuehren.</p></div>';
        echo '<div class="cc-app-badge-row">' . self::renderStatusBadge('aktiv') . ' ' . self::renderConnectionModeBadge('safe_mode') . '</div>';
        echo '</div>';
        echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Anbieter</th><th>Anzeigename</th><th>Status</th><th>Modus</th><th>Client ID</th><th>Client Secret</th><th>Redirect URI</th><th>Token vorhanden</th><th>Letzte Live-Pruefung</th><th>Erstellt am</th><th>Aktualisiert am</th><th>Aktionen</th></tr></thead><tbody>';

        foreach ($connections as $connection) {
            $rowConfig = CloudCrypto::decryptConfig((string) $connection['config_encrypted']);
            $editUrl = esc_url(add_query_arg(['page' => self::MENU_SLUG, 'tab' => 'connections', 'edit_connection' => (int) $connection['id']], admin_url('admin.php')));
            $hasToken = !empty($rowConfig['access_token']) || !empty($rowConfig['refresh_token']);

            echo '<tr>';
            echo '<td>' . esc_html((string) $connection['id']) . '</td>';
            echo '<td><code>' . esc_html($connection['provider_slug']) . '</code></td>';
            echo '<td>' . esc_html($connection['name']) . '</td>';
            echo '<td>' . self::renderStatusBadge((string) $connection['status']) . '</td>';
            echo '<td>' . self::renderConnectionModeBadge(self::getConnectionMode($connection, $rowConfig)) . '</td>';
            echo '<td><code>' . esc_html(self::maskCredential((string) ($rowConfig['client_id'] ?? ''))) . '</code></td>';
            echo '<td><code>' . esc_html(self::maskCredential((string) ($rowConfig['client_secret'] ?? ''))) . '</code></td>';
            echo '<td>' . self::renderOptionalUrl((string) ($rowConfig['redirect_uri'] ?? '')) . '</td>';
            echo '<td>' . esc_html($hasToken ? 'Ja' : 'Nein') . '</td>';
            echo '<td>' . esc_html((string) ($connection['last_connected_at'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($connection['created_at'] ?: '-')) . '</td>';
            echo '<td>' . esc_html((string) ($connection['updated_at'] ?: '-')) . '</td>';
            echo '<td>';
            echo '<a class="button button-secondary" href="' . $editUrl . '">Bearbeiten</a> ';
            if ((string) $connection['provider_slug'] === 'google_drive' && self::getConnectionMode($connection, $rowConfig) === 'readonly_live') {
                self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'test_readonly_connection'], 'Readonly-Verbindung testen');
                echo ' ';
            }
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'set_test_mode'], 'Safe-Mode');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_connection_action', 'cc_connection_action', ['id' => (int) $connection['id'], 'connection_action' => 'deactivate'], 'Deaktivieren');
            echo ' ';
            self::inlinePostButton('admin-post.php?action=cc_delete_connection', 'cc_delete_connection', ['id' => (int) $connection['id']], 'Loeschen');
            echo '</td>';
            echo '</tr>';
        }

        if (empty($connections)) {
            echo '<tr><td colspan="13">Noch keine vorbereiteten Verbindungen vorhanden.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '<div class="cc-app-card">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy"><h2>' . ($editConnection ? 'Verbindung bearbeiten' : 'Neue Verbindung') . '</h2><p class="cc-app-muted">Maskierte Zugangsdaten, klarer Verbindungsmodus und keine automatischen Live-Aktionen.</p></div>';
        echo '<div class="cc-app-badge-row">' . self::renderInfoBadge('vorbereitet') . '</div>';
        echo '</div>';
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
        echo '<tr><th><label for="connection_mode">Verbindungsmodus</label></th><td><select id="connection_mode" name="connection_mode">';
        echo '<option value="safe_mode"' . selected('safe_mode', $connectionMode, false) . '>safe_mode</option>';
        echo '<option value="readonly_live"' . selected('readonly_live', $connectionMode, false) . '>readonly_live</option>';
        echo '<option value="disabled"' . selected('disabled', $connectionMode, false) . '>disabled</option>';
        echo '</select><p class="description">Default bleibt <code>safe_mode</code>. <code>readonly_live</code> erlaubt nur einen expliziten Metadaten-Test ohne Dateioperationen. <code>disabled</code> blockiert jegliche Provider-Kommunikation.</p></td></tr>';
        echo '<tr><th><label for="status">Status</label></th><td><select id="status" name="status">';
        echo '<option value="aktiv"' . selected('aktiv', $selectedConnectionStatus, false) . '>Aktiv</option>';
        echo '<option value="inaktiv"' . selected('inaktiv', $selectedConnectionStatus, false) . '>Inaktiv</option>';
        echo '</select><p class="description">Safe-Mode bleibt auch bei aktivem Status global eingeschaltet. Live-Kommunikation erfolgt nur im Modus <code>readonly_live</code> und nur bei explizitem Test.</p></td></tr>';
        echo '<tr><th><label for="client_id">Client ID</label></th><td><input class="regular-text" id="client_id" name="client_id" value="' . esc_attr((string) ($config['client_id'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="client_secret">Client Secret</label></th><td><input class="regular-text" id="client_secret" name="client_secret" value=""><p class="description">Leer lassen, um das bestehende Secret beizubehalten.</p></td></tr>';
        echo '<tr><th><label for="access_token">Access-Token</label></th><td><input class="regular-text" id="access_token" name="access_token" value="" autocomplete="off"><p class="description">Optional fuer readonly Live-Tests. Leer lassen, um ein bereits gespeichertes Token beizubehalten. Token werden nie im Klartext angezeigt.</p></td></tr>';
        echo '<tr><th><label for="refresh_token">Refresh-Token</label></th><td><input class="regular-text" id="refresh_token" name="refresh_token" value="" autocomplete="off"><p class="description">Optional fuer die stille Access-Token-Aktualisierung im readonly Live-Test. Es werden keine Tokens im HTML, JS oder Log ausgegeben.</p></td></tr>';
        echo '<tr><th><label for="redirect_uri">Redirect URI</label></th><td><input class="regular-text" id="redirect_uri" name="redirect_uri" value="' . esc_attr((string) ($config['redirect_uri'] ?? '')) . '"></td></tr>';
        echo '<tr><th><label for="notes">Notizen</label></th><td><textarea class="large-text" rows="4" id="notes" name="notes">' . esc_textarea((string) ($config['notes'] ?? '')) . '</textarea><p class="description">Google Drive kann in dieser Stufe optional fuer einen strikt readonly begrenzten Metadaten-Test vorbereitet werden. Keine Uploads, keine Moves, keine Deletes, kein echter Sync.</p></td></tr>';
        echo '</tbody></table>';
        submit_button($editConnection ? 'Verbindung aktualisieren' : 'Verbindung speichern');
        echo '</form>';
        echo '</div>';

        echo '<div class="cc-app-card cc-app-card-soft">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy"><h2>OAuth-/Provider-Informationen</h2><p class="cc-app-muted">Produktive Schreibpfade bleiben deaktiviert. Fuer Google Drive wird nur ein readonly OAuth-Setup mit reinem Metadatenzugriff vorbereitet.</p></div>';
        echo '<div class="cc-app-badge-row">' . self::renderInfoBadge('vorbereitet') . ' ' . self::renderStatusBadge('readonly') . '</div>';
        echo '</div>';
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
            if ($slug === 'google_drive') {
                $authUrl = CloudReadonlyProviderService::buildPreparedGoogleAuthUrl($config);
                echo '<tr><td><strong>Readonly OAuth-URL</strong></td><td>' . self::renderProviderGuideValue($authUrl) . '</td><td>' . self::renderCopyButton($authUrl, 'OAuth-URL kopieren') . '</td></tr>';
            }
            echo '<tr><td><strong>Hinweis</strong></td><td colspan="2">' . esc_html((string) $guide['hint']) . '</td></tr>';
            echo '<tr><td><strong>Dokumentationsstatus</strong></td><td colspan="2">' . esc_html((string) $guide['docs_note']) . '</td></tr>';
            echo '<tr><td><strong>Sicherheitsgrenze</strong></td><td colspan="2">Default bleibt Safe-Mode. Nur eine explizite Aktion <code>Readonly-Verbindung testen</code> darf in <code>readonly_live</code> minimale Provider-Metadaten lesen.</td></tr>';
            echo '</tbody></table>';
            echo '</div>';
        }

        echo '</div>';
        self::renderProviderGuideScript($selectedProviderSlug);
        echo '</div>';
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
        $selectedConnectionConfig = $selectedConnection ? CloudCrypto::decryptConfig((string) $selectedConnection['config_encrypted']) : [];
        $selectedConnectionMode = self::getConnectionMode($selectedConnection, $selectedConnectionConfig);
        $explorerFiles = self::buildExplorerRows($selectedProvider, $selectedConnection, $jobs, $logs, $fileCache);
        $previewRows = self::buildExplorerPreviewRows($explorerFiles, $selectedProvider, $selectedConnection, $jobs, $logs);
        $conflictRows = self::buildExplorerConflictRows($previewRows, $selectedProvider);
        $health = self::buildExplorerHealth($jobs, $logs, $previewRows, $safeModeEnabled);
        $detailPanel = self::buildExplorerDetailPanel($explorerFiles, $previewRows, $selectedProvider, $selectedConnection);
        $activityEntries = self::buildExplorerActivityEntries($previewRows);

        echo '<div class="cc-app-shell">';
        echo '<div class="cc-app-banner">';
        if ($selectedConnectionMode === 'readonly_live') {
            echo '<strong>READONLY LIVE aktiv.</strong> Diese Verbindung darf nur einen streng begrenzten Metadatenzugriff gegen den Provider ausfuehren. Keine Dateioperationen, keine Queue, kein Worker, kein echter Sync.';
        } else {
            echo '<strong>Safe-Mode aktiv.</strong> Explorer-Daten stammen nur aus Cache-, DB- und Mockquellen. Es werden keine Provider-Requests, OAuth-Flows oder Dateioperationen ausgeloest.';
        }
        echo '</div>';

        echo '<div class="cc-app-grid">';
        echo '<div class="cc-app-sidebar">';
        echo '<div class="cc-app-card cc-app-card-sticky">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy"><h2>Provider / Verbindungen</h2><p class="cc-app-muted">Virtuelle Ordnerstruktur ohne Live-Abfrage.</p></div>';
        echo '<div class="cc-app-badge-row">' . self::renderStatusBadge($selectedConnectionMode === 'readonly_live' ? 'readonly' : 'safe-mode') . '</div>';
        echo '</div>';

        foreach ($guides as $providerSlug => $guide) {
            $providerUrl = esc_url(add_query_arg([
                'page' => self::MENU_SLUG,
                'tab' => 'explorer',
                'explorer_provider' => $providerSlug,
            ], admin_url('admin.php')));
            $providerClass = $providerSlug === $selectedProvider ? 'background:#f0f6fc;border-color:#72aee6;' : '';

            echo '<div class="cc-app-card cc-app-card-soft" style="' . esc_attr($providerClass) . '">';
            echo '<div class="cc-app-header">';
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
                    $connectionConfig = CloudCrypto::decryptConfig((string) ($connection['config_encrypted'] ?? ''));

                    echo '<li style="margin-bottom:4px;">';
                    echo '<a href="' . $connectionUrl . '" style="text-decoration:none;' . ($isSelectedConnection ? 'font-weight:600;' : '') . '">' . esc_html((string) $connection['name']) . '</a> ';
                    echo self::renderStatusBadge((string) $connection['status']);
                    echo ' ' . self::renderConnectionModeBadge(self::getConnectionMode($connection, $connectionConfig));
                    echo '</li>';
                }

                echo '</ul>';
            }

            echo '</div>';
            echo '<div style="margin-top:10px;"><strong style="display:block;margin-bottom:6px;">Virtuelle Ordner</strong>';
            echo '<ul style="margin:0;padding-left:18px;list-style:none;">';

            foreach (['/', '/Dokumente', '/Uploads', '/Archiv', '/Sync Queue'] as $folder) {
                echo '<li style="margin-bottom:6px;">';
                echo '<button type="button" class="button-link cc-drop-zone cc-app-drop-zone" data-drop-zone="folder" data-drop-target="' . esc_attr($folder) . '" style="display:block;width:100%;text-align:left;">';
                echo '<strong style="display:block;"><code>' . esc_html($folder) . '</code></strong>';
                echo '<span style="display:block;margin-top:4px;color:#646970;">Safe-Mode Drop-Zone fuer simulierte Move-Previews</span>';
                echo '</button>';
                echo '</li>';
            }

            echo '</ul></div>';
            echo '</div>';
        }

        echo '</div>';
        echo '</div>';

        if ($selectedConnectionMode === 'readonly_live') {
            self::renderReadonlyLiveExplorer($selectedProvider, $selectedConnection, $fileCache);
            echo '</div>';
            echo '</div>';

            return;
        }

        echo '<div class="cc-app-main">';
        echo '<div class="cc-app-metrics">';

        foreach ($health as $card) {
            echo '<div class="cc-app-card cc-app-card-soft" data-health-label="' . esc_attr($card['label']) . '">';
            echo '<div style="font-size:12px;text-transform:uppercase;color:#646970;margin-bottom:8px;">' . esc_html($card['label']) . '</div>';
            echo '<div data-health-value style="font-size:22px;font-weight:600;line-height:1.2;">' . esc_html($card['value']) . '</div>';
            if ($card['note'] !== '') {
                echo '<div data-health-note style="margin-top:6px;color:#50575e;">' . esc_html($card['note']) . '</div>';
            } else {
                echo '<div data-health-note style="margin-top:6px;color:#50575e;"></div>';
            }
            echo '</div>';
        }

        echo '</div>';
        echo '<div class="cc-app-card">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy">';
        echo '<h2>Explorer</h2>';
        echo '<p class="cc-app-muted">';
        echo esc_html((string) $guides[$selectedProvider]['title']);
        if ($selectedConnection) {
            echo ' / ' . esc_html((string) $selectedConnection['name']);
        }
        echo '</p>';
        echo '</div>';
        echo '<div class="cc-app-badge-row">' . self::renderStatusBadge('safe-mode') . '</div>';
        echo '</div>';
        echo '<div id="cc-dnd-feedback" class="cc-app-card cc-app-card-soft" style="display:none;margin:0 0 16px 0;"></div>';
        echo '<div style="overflow:auto;">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Dateiname</th><th>Typ</th><th>Groesse</th><th>Provider</th><th>Sync-Richtung</th><th>Status</th><th>Letzte Aenderung</th><th>Letzter Sync</th><th>Konfliktstatus</th><th>Safe-Mode Aktionen</th></tr></thead><tbody>';

        foreach ($explorerFiles as $index => $row) {
            $fileKey = md5($row['name'] . '|' . $row['provider'] . '|' . $row['last_sync']);
            $previewRow = $previewRows[$index] ?? null;
            $isReadonly = is_array($previewRow) && (string) ($previewRow['status'] ?? '') === 'readonly';
            $isBlocked = (string) ($row['status'] ?? '') === 'offline';
            $rowStyle = '';

            if ($isReadonly) {
                $rowStyle = 'background:#f6f7f7;color:#646970;';
            } elseif ($isBlocked) {
                $rowStyle = 'background:#fcf0f1;';
            }

            echo '<tr class="cc-explorer-row" draggable="' . ($isReadonly || $isBlocked ? 'false' : 'true') . '" data-file-key="' . esc_attr($fileKey) . '" data-readonly="' . ($isReadonly ? 'true' : 'false') . '" data-blocked="' . ($isBlocked ? 'true' : 'false') . '" style="' . esc_attr($rowStyle) . '">';
            echo '<td><button type="button" class="button-link cc-explorer-file-trigger" data-file-key="' . esc_attr($fileKey) . '" draggable="false" style="font-weight:600;text-align:left;">' . esc_html($row['name']) . '</button></td>';
            echo '<td>' . esc_html($row['type']) . '</td>';
            echo '<td>' . esc_html($row['size']) . '</td>';
            echo '<td>' . esc_html($row['provider']) . '</td>';
            echo '<td><code>' . esc_html($row['direction']) . '</code></td>';
            echo '<td>' . self::renderStatusBadge($row['status']) . ($isReadonly ? ' ' . self::renderStatusBadge('readonly') : '') . ($isBlocked ? ' ' . self::renderStatusBadge('blocked') : '') . '</td>';
            echo '<td>' . esc_html($row['modified']) . '</td>';
            echo '<td>' . esc_html($row['last_sync']) . '</td>';
            echo '<td>' . self::renderStatusBadge($row['conflict']) . '</td>';
            echo '<td>' . self::renderExplorerActionButtons($fileKey, $isReadonly || $isBlocked) . '</td>';
            echo '</tr>';
        }

        if (empty($explorerFiles)) {
            echo '<tr><td colspan="10">Keine Explorer-Daten verfuegbar.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '<div id="cc-file-detail-panel" class="cc-app-card cc-app-card-soft">';
        echo '<h3 style="margin-top:0;">Datei-Detailpanel</h3>';
        echo '<p class="cc-app-muted">Datei im Explorer anklicken oder per Drag-and-Drop simuliert verschieben. Safe-Mode bleibt read-only.</p>';
        echo '<div id="cc-detail-actions" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">';
        echo '<button type="button" class="button button-secondary cc-detail-action" data-sim-action="queue">Zur Queue simulieren</button>';
        echo '<button type="button" class="button button-secondary cc-detail-action" data-sim-action="conflict">Konflikt simulieren</button>';
        echo '<button type="button" class="button button-secondary cc-detail-action" data-sim-action="move">Move simulieren</button>';
        echo '</div>';
        echo '<div id="cc-file-detail-content">';
        echo self::renderExplorerDetailHtml($detailPanel['initial']);
        echo '</div>';
        echo '</div>';
        echo '</div>';

        echo '<div id="cc-preview-drop-zone" class="cc-drop-zone cc-app-card" data-drop-zone="preview" data-drop-target="/local/sync-preview" style="margin-top:16px;">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy">';
        echo '<h2>Sync Preview / Dry Run</h2>';
        echo '<p class="cc-app-muted">Geplanter Sync, virtuelle Konflikte und Warteschlange bleiben reine Simulation. Keine Dateioperationen werden ausgefuehrt.</p>';
        echo '</div>';
        echo '<div class="cc-app-badge-row">' . self::renderStatusBadge('preview') . ' ' . self::renderStatusBadge('safe-mode') . '</div>';
        echo '</div>';
        echo '<div style="overflow:auto;">';
        echo '<table class="widefat striped">';
        echo '<thead><tr><th>Datei</th><th>Quelle</th><th>Ziel</th><th>Aktion</th><th>Status</th><th>Groesse</th><th>Zeitstempel</th></tr></thead><tbody id="cc-preview-body">';

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
        echo '<div class="cc-app-grid" style="margin-top:16px;">';
        echo '<div id="cc-conflict-drop-zone" class="cc-drop-zone cc-app-card cc-app-card-soft" data-drop-zone="conflict" data-drop-target="Virtuelle Konflikte">';
        echo '<h3 style="margin-top:0;">Virtuelle Konflikte</h3>';
        echo '<table class="widefat striped"><thead><tr><th>Datei</th><th>Lokaler Zustand</th><th>Cloud-Zustand</th><th>Konfliktstatus</th></tr></thead><tbody id="cc-conflict-body">';

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
        echo '<div id="cc-queue-drop-zone" class="cc-drop-zone cc-app-card cc-app-card-soft" data-drop-zone="queue" data-drop-target="Virtuelle Warteschlange">';
        echo '<h3 style="margin-top:0;">Virtuelle Warteschlange</h3>';
        echo '<ul id="cc-queue-list" style="margin:0;padding-left:18px;">';
        foreach ($previewRows as $previewRow) {
            echo '<li style="margin-bottom:6px;">';
            echo '<strong>' . esc_html($previewRow['file']) . '</strong> - ' . esc_html($previewRow['action']) . ' - ' . self::renderStatusBadge($previewRow['status']);
            echo '</li>';
        }
        if (empty($previewRows)) {
            echo '<li>Keine Eintraege in der virtuellen Queue.</li>';
        }
        echo '</ul>';
        echo '<div style="margin-top:16px;padding-top:16px;border-top:1px solid #dcdcde;">';
        echo '<h4 style="margin:0 0 10px 0;">Aktivitaets-Historie</h4>';
        echo '<ul id="cc-activity-list" style="margin:0;padding-left:18px;">';
        foreach ($activityEntries as $entry) {
            echo '<li style="margin-bottom:6px;">' . esc_html($entry) . '</li>';
        }
        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        self::renderExplorerScript($detailPanel['map'], $previewRows, $conflictRows, $health, $activityEntries);
        echo '</div>';
    }

    private static function renderReadonlyLiveExplorer(string $selectedProvider, ?array $selectedConnection, array $fileCache): void
    {
        $connectionId = (int) ($selectedConnection['id'] ?? 0);
        $connectionFiles = array_values(array_filter(
            $fileCache,
            static fn(array $row): bool => (int) ($row['connection_id'] ?? 0) === $connectionId
        ));

        echo '<div class="cc-app-main">';
        echo '<div class="cc-app-card">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy">';
        echo '<h2>Readonly Live Explorer</h2>';
        echo '<p class="cc-app-muted">';
        echo esc_html((string) (self::getProviderGuides()[$selectedProvider]['title'] ?? $selectedProvider));
        if ($selectedConnection) {
            echo ' / ' . esc_html((string) $selectedConnection['name']);
        }
        echo '</p>';
        echo '</div>';
        echo '<div class="cc-app-badge-row">' . self::renderConnectionModeBadge('readonly_live') . ' ' . self::renderStatusBadge('readonly') . '</div>';
        echo '</div>';
        echo '<div class="cc-app-card cc-app-card-soft" style="margin:0 0 16px 0;">';
        echo '<strong>Nur Metadatenzugriff.</strong> Diese Verbindung erlaubt nur readonly Metadatenzugriff. Es werden keine Dateiaenderungen durchgefuehrt.';
        echo '</div>';
        echo '<table class="widefat striped" style="margin-bottom:16px;"><tbody>';
        echo '<tr><td><strong>Verbindungsmodus</strong></td><td>' . self::renderConnectionModeBadge('readonly_live') . '</td></tr>';
        echo '<tr><td><strong>Provider</strong></td><td>' . esc_html((string) ($selectedConnection['provider_slug'] ?? '-')) . '</td></tr>';
        echo '<tr><td><strong>Letzte Live-Pruefung</strong></td><td>' . esc_html((string) (($selectedConnection['last_connected_at'] ?? '') ?: '-')) . '</td></tr>';
        echo '<tr><td><strong>Hard-Limits</strong></td><td><code>maxResults=5</code>, keine Rekursion, kein Auto-Refresh, kein Queue-/Worker-Link, kein Download/Upload/Move/Delete</td></tr>';
        echo '</tbody></table>';
        echo '<h3 style="margin-top:0;">Kleine Test-Dateiliste</h3>';
        echo '<table class="widefat striped"><thead><tr><th>Name</th><th>Typ</th><th>Groesse</th><th>Geaendert am</th></tr></thead><tbody>';

        foreach (array_slice($connectionFiles, 0, 5) as $file) {
            echo '<tr>';
            echo '<td>' . esc_html((string) ($file['name'] ?? '')) . '</td>';
            echo '<td>' . esc_html((string) ($file['mime_type'] ?? 'application/octet-stream')) . '</td>';
            echo '<td>' . esc_html(size_format((int) ($file['size_bytes'] ?? 0))) . '</td>';
            echo '<td>' . esc_html((string) ($file['last_modified'] ?? '-')) . '</td>';
            echo '</tr>';
        }

        if (empty($connectionFiles)) {
            echo '<tr><td colspan="4">Noch keine readonly Live-Metadaten vorhanden. Fuehre zuerst den Button <strong>Readonly-Verbindung testen</strong> im Tab Verbindungen aus.</td></tr>';
        }

        echo '</tbody></table>';
        echo '</div>';
        echo '</div>';
    }

    private static function renderNetwork(): void
    {
        $nodes = self::getNetworkNodes();
        $initial = $nodes[0] ?? [];

        echo '<div class="cc-app-shell">';
        echo '<div class="cc-app-banner">';
        echo '<strong>Readonly Netzwerkansicht.</strong> Diese Mesh-Ansicht ist reine Statusvisualisierung. Keine Provider-Calls, keine Dateioperationen, keine Worker-Anbindung.';
        echo '</div>';
        echo '<div class="cc-app-grid">';
        echo '<div class="cc-app-main">';
        echo '<div class="cc-app-card cc-app-card-tint">';
        echo '<div class="cc-app-header">';
        echo '<div class="cc-app-header-copy"><h2>Cloud Connector Mesh</h2><p class="cc-app-muted">Safe-Mode als Schutzschicht, Readonly Live als streng begrenzter Providerpfad und alle Systemkomponenten als Topologie.</p></div>';
        echo '<div class="cc-app-badge-row">' . self::renderStatusBadge('safe-mode') . ' ' . self::renderStatusBadge('readonly') . '</div>';
        echo '</div>';
        echo '<div class="cc-network-grid cc-app-network-grid">';

        foreach ($nodes as $node) {
            $allowed = implode(' | ', array_map('strval', $node['allowed']));
            $blocked = implode(' | ', array_map('strval', $node['blocked']));
            echo '<button type="button" class="cc-network-node cc-app-network-node" data-node-title="' . esc_attr((string) $node['title']) . '" data-node-status="' . esc_attr((string) $node['status']) . '" data-node-description="' . esc_attr((string) $node['description']) . '" data-node-allowed="' . esc_attr($allowed) . '" data-node-blocked="' . esc_attr($blocked) . '" style="text-align:left;">';
            echo '<div class="cc-app-header">';
            echo '<strong>' . esc_html((string) $node['title']) . '</strong>';
            echo self::renderStatusBadge((string) $node['status']);
            echo '</div>';
            echo '<p style="margin:10px 0 8px 0;color:#50575e;min-height:42px;">' . esc_html((string) $node['description']) . '</p>';
            echo '<div style="font-size:12px;color:#646970;">Erlaubt: ' . esc_html((string) ($node['allowed'][0] ?? '-')) . '</div>';
            echo '<div style="font-size:12px;color:#646970;">Blockiert: ' . esc_html((string) ($node['blocked'][0] ?? '-')) . '</div>';
            echo '</button>';
        }

        echo '</div>';
        echo '</div>';
        echo '<div class="cc-app-sidebar">';
        echo '<div class="cc-app-card cc-app-card-sticky">';
        echo '<h2 style="margin-top:0;">Node-Details</h2>';
        echo '<div id="cc-network-detail">';
        echo self::renderNetworkDetailHtml($initial);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<script>';
        echo '(function(){const detail=document.getElementById("cc-network-detail");const nodes=document.querySelectorAll(".cc-network-node");if(!detail||!nodes.length){return;}';
        echo 'const esc=function(value){return String(value).replace(/[&<>"\']/g,function(char){if(char==="&"){return "&amp;";}if(char==="<"){return "&lt;";}if(char===">"){return "&gt;";}if(char===\'"\'){return "&quot;";}return "&#039;";});};';
        echo 'const badge=function(label){const palette={aktiv:["#dcfce7","#166534"],"safe-mode":["#e0f2fe","#075985"],readonly:["#f3f4f6","#111827"],blocked:["#fee2e2","#991b1b"],geplant:["#dbeafe","#1d4ed8"],inaktiv:["#e5e7eb","#374151"]};const colors=palette[label]||["#f3f4f6","#111827"];return \'<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:\'+colors[0]+\';color:\'+colors[1]+\';font-weight:700;">\'+esc(label)+\'</span>\';};';
        echo 'const list=function(value){return String(value||"").split(" | ").filter(Boolean).map(function(item){return "<li>"+esc(item)+"</li>";}).join("")||"<li>-</li>";};';
        echo 'const render=function(node){detail.innerHTML="<h3 style=\"margin-top:0;\">"+esc(node.title)+"</h3><p style=\"margin:0 0 12px 0;\">"+badge(node.status)+"</p><p style=\"color:#50575e;\">"+esc(node.description)+"</p><div style=\"display:grid;grid-template-columns:1fr;gap:12px;\"><div><strong>Erlaubt</strong><ul style=\"margin:8px 0 0 18px;\">"+list(node.allowed)+"</ul></div><div><strong>Blockiert</strong><ul style=\"margin:8px 0 0 18px;\">"+list(node.blocked)+"</ul></div></div>";};';
        echo 'nodes.forEach(function(node){node.addEventListener("click",function(){nodes.forEach(function(other){other.style.outline="none";});node.style.outline="2px solid #72aee6";node.style.outlineOffset="2px";render({title:node.getAttribute("data-node-title")||"",status:node.getAttribute("data-node-status")||"",description:node.getAttribute("data-node-description")||"",allowed:node.getAttribute("data-node-allowed")||"",blocked:node.getAttribute("data-node-blocked")||""});});});';
        echo 'nodes[0].style.outline="2px solid #72aee6";nodes[0].style.outlineOffset="2px";';
        echo '})();';
        echo '</script>';
        echo '</div>';
        echo '</div>';
    }

    private static function renderNetworkDetailHtml(array $node): string
    {
        $allowedItems = '';
        foreach ((array) ($node['allowed'] ?? []) as $item) {
            $allowedItems .= '<li>' . esc_html((string) $item) . '</li>';
        }
        $blockedItems = '';
        foreach ((array) ($node['blocked'] ?? []) as $item) {
            $blockedItems .= '<li>' . esc_html((string) $item) . '</li>';
        }

        return '<h3 style="margin-top:0;">' . esc_html((string) ($node['title'] ?? 'Cloud Connector')) . '</h3>'
            . '<p style="margin:0 0 12px 0;">' . self::renderStatusBadge((string) ($node['status'] ?? 'aktiv')) . '</p>'
            . '<p style="color:#50575e;">' . esc_html((string) ($node['description'] ?? '')) . '</p>'
            . '<div style="display:grid;grid-template-columns:1fr;gap:12px;">'
            . '<div><strong>Erlaubt</strong><ul style="margin:8px 0 0 18px;">' . ($allowedItems !== '' ? $allowedItems : '<li>-</li>') . '</ul></div>'
            . '<div><strong>Blockiert</strong><ul style="margin:8px 0 0 18px;">' . ($blockedItems !== '' ? $blockedItems : '<li>-</li>') . '</ul></div>'
            . '</div>';
    }

    private static function getNetworkNodes(): array
    {
        return [
            ['title' => 'Cloud Connector', 'status' => 'aktiv', 'description' => 'Zentrale Verwaltungsinstanz fuer Provider, Explorer und Schutzlogik.', 'allowed' => ['Admin-UI', 'Statusdarstellung', 'Readonly Vorbereitung'], 'blocked' => ['Direkter Sync ohne Freigabe', 'Ungeschuetzte Provideraktionen']],
            ['title' => 'Safe-Mode', 'status' => 'safe-mode', 'description' => 'Globale Schutzschicht fuer Simulation, Preview und geblockte Dateiaktionen.', 'allowed' => ['Simulation', 'Preview', 'UI-Aktionen'], 'blocked' => ['Echte Dateioperationen', 'Destruktive Aenderungen']],
            ['title' => 'Readonly Live', 'status' => 'readonly', 'description' => 'Streng begrenzter Live-Pfad nur fuer Provider-Metadaten.', 'allowed' => ['Token pruefen', 'max. 5 Metadaten lesen'], 'blocked' => ['Upload', 'Download', 'Move', 'Delete', 'Sync']],
            ['title' => 'Google Drive', 'status' => 'readonly', 'description' => 'Vorbereitet fuer readonly Metadatenzugriff mit minimalem Scope.', 'allowed' => ['Token pruefen', 'max. 5 Metadaten lesen'], 'blocked' => ['Upload', 'Download', 'Move', 'Delete', 'Sync']],
            ['title' => 'Dropbox', 'status' => 'geplant', 'description' => 'Nur vorbereitete Folgeausbaustufe ohne aktive Live-Kommunikation.', 'allowed' => ['Statusmodell', 'Dokumentation'], 'blocked' => ['Live-Calls', 'Dateioperationen']],
            ['title' => 'OneDrive', 'status' => 'geplant', 'description' => 'Nur vorbereitete Folgeausbaustufe ohne aktive Live-Kommunikation.', 'allowed' => ['Statusmodell', 'Dokumentation'], 'blocked' => ['Live-Calls', 'Dateioperationen']],
            ['title' => 'WebDAV / Nextcloud', 'status' => 'inaktiv', 'description' => 'Geplanter Providerknoten ohne angebundene Kommunikation.', 'allowed' => ['Topologie', 'Hinweise'], 'blocked' => ['Providerzugriff', 'Dateioperationen']],
            ['title' => 'SFTP', 'status' => 'inaktiv', 'description' => 'Geplanter Infrastrukturknoten ohne aktive Integrationslogik.', 'allowed' => ['Topologie', 'Hinweise'], 'blocked' => ['Providerzugriff', 'Dateioperationen']],
            ['title' => 'Server Storage', 'status' => 'safe-mode', 'description' => 'Lokales Zielsystem bleibt in der Schutzschicht isoliert.', 'allowed' => ['Pfadmodell', 'Statusdarstellung'], 'blocked' => ['Live-Schreiboperationen', 'Downloads']],
            ['title' => 'Explorer', 'status' => 'aktiv', 'description' => 'Admin-Explorer fuer Cache-, Mock- und readonly Statusansichten.', 'allowed' => ['UI-Ansicht', 'Readonly Liste'], 'blocked' => ['Aktive Dateioperationen']],
            ['title' => 'Sync Preview', 'status' => 'safe-mode', 'description' => 'Vorschau fuer simulierte Aenderungen ohne Providerwirkung.', 'allowed' => ['Preview', 'Konfliktanzeige'], 'blocked' => ['Echter Sync']],
            ['title' => 'Drag & Drop', 'status' => 'safe-mode', 'description' => 'Clientseitige Simulationsinteraktionen im Explorer.', 'allowed' => ['UI-Simulation', 'Statuswechsel'], 'blocked' => ['Move', 'Delete', 'Upload']],
            ['title' => 'Queue', 'status' => 'blocked', 'description' => 'Virtuelle Warteschlange fuer Safe-Mode-Simulationen.', 'allowed' => ['Anzeige', 'Simulationsstatus'], 'blocked' => ['Worker-Ausfuehrung im readonly Pfad']],
            ['title' => 'Worker', 'status' => 'blocked', 'description' => 'Bestehende Simulationsworker bleiben vom readonly Live-Pfad getrennt.', 'allowed' => ['Safe-Mode Simulation'], 'blocked' => ['Readonly Live Integration', 'Automatische Provideraktionen']],
            ['title' => 'Logs', 'status' => 'aktiv', 'description' => 'Sanitisierte Status- und Ereignisprotokolle ohne Tokenoffenlegung.', 'allowed' => ['Statuslogs', 'Readonly Ereignisse'], 'blocked' => ['Tokenlogging', 'Secrets im Kontext']],
            ['title' => 'Verbindungen', 'status' => 'aktiv', 'description' => 'Providerkonfiguration mit Moduswahl und readonly Testaktion.', 'allowed' => ['Mode setzen', 'Readonly Test starten'], 'blocked' => ['Ungeschuetzte Schreibscopes']],
        ];
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

    private static function renderUiShellStyles(): void
    {
        echo '<style id="cc-ui-shell-styles">';
        echo '.cc-app-shell{display:grid;gap:16px;margin-top:16px;}';
        echo '.cc-app-banner,.cc-app-card{border:1px solid #dcdcde;border-radius:16px;background:#fff;box-shadow:0 10px 30px rgba(15,23,42,.05);}';
        echo '.cc-app-banner{padding:14px 16px;background:linear-gradient(145deg,#f8fbff 0%,#ffffff 100%);}';
        echo '.cc-app-card{padding:16px;}';
        echo '.cc-app-card-soft{background:#f8fafc;}';
        echo '.cc-app-card-tint{background:linear-gradient(180deg,#f8fbff 0%,#ffffff 100%);}';
        echo '.cc-app-card-sticky{position:sticky;top:16px;}';
        echo '.cc-app-grid{display:grid;grid-template-columns:minmax(280px,340px) minmax(0,1fr);gap:16px;align-items:start;}';
        echo '.cc-app-sidebar,.cc-app-main{min-width:0;}';
        echo '.cc-app-header{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:14px;}';
        echo '.cc-app-header-copy{display:grid;gap:6px;min-width:0;}';
        echo '.cc-app-header-copy h2,.cc-app-header-copy h3{margin:0;}';
        echo '.cc-app-muted{margin:0;color:#50575e;}';
        echo '.cc-app-badge-row{display:flex;flex-wrap:wrap;gap:8px;align-items:center;}';
        echo '.cc-app-drop-zone{padding:10px 12px;border:1px dashed #c3c4c7;border-radius:10px;background:#f6f7f7;}';
        echo '.cc-app-metrics{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:16px;}';
        echo '.cc-app-network-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;position:relative;}';
        echo '.cc-app-network-node{border:1px solid #dcdcde;border-radius:14px;padding:14px;background:#fff;box-shadow:0 8px 24px rgba(15,23,42,.06);cursor:pointer;transition:border-color .18s ease,transform .18s ease,box-shadow .18s ease;}';
        echo '.cc-app-network-node:hover{transform:translateY(-1px);border-color:#72aee6;box-shadow:0 12px 30px rgba(15,23,42,.09);}';
        echo '.cc-app-badge{display:inline-flex;align-items:center;min-height:24px;padding:3px 10px;border-radius:999px;font-weight:700;font-size:11px;letter-spacing:.04em;text-transform:uppercase;}';
        echo '@media (max-width:960px){.cc-app-grid{grid-template-columns:minmax(0,1fr);}.cc-app-card-sticky{position:static;}}';
        echo '@media (max-width:640px){.cc-app-card,.cc-app-banner{padding:14px;}.widefat td,.widefat th{white-space:normal;}}';
        echo '</style>';
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
            'blocked' => ['#fee2e2', '#991b1b'],
        ];

        [$background, $color] = $styles[$status] ?? ['#f3f4f6', '#111827'];

        return self::renderBadgePill($status, $background, $color, false);
    }

    private static function renderExplorerActionButtons(string $fileKey, bool $disabled = false): string
    {
        $buttons = [
            'queue' => 'Zur Queue simulieren',
            'conflict' => 'Konflikt simulieren',
            'move' => 'Move simulieren',
        ];
        $html = '<div style="display:flex;gap:6px;flex-wrap:wrap;">';

        foreach ($buttons as $action => $label) {
            $html .= '<button type="button" class="button button-secondary cc-inline-action" data-file-key="' . esc_attr($fileKey) . '" data-sim-action="' . esc_attr($action) . '"' . ($disabled ? ' disabled aria-disabled="true" style="cursor:not-allowed;opacity:0.6;"' : '') . '>' . esc_html($label) . '</button>';
        }

        $html .= '</div>';

        return $html;
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

    private static function getConnectionMode(?array $connection, ?array $config = null): string
    {
        $resolvedConfig = $config;

        if ($resolvedConfig === null && is_array($connection)) {
            $resolvedConfig = CloudCrypto::decryptConfig((string) ($connection['config_encrypted'] ?? ''));
        }

        return CloudReadonlyProviderService::normalizeMode((string) ($resolvedConfig['connection_mode'] ?? 'safe_mode'));
    }

    private static function renderConnectionModeBadge(string $mode): string
    {
        $labels = [
            'safe_mode' => 'SAFE MODE',
            'readonly_live' => 'READONLY LIVE',
            'disabled' => 'DISABLED',
        ];
        $styles = [
            'safe_mode' => ['#e0f2fe', '#075985'],
            'readonly_live' => ['#dcfce7', '#166534'],
            'disabled' => ['#e5e7eb', '#374151'],
        ];
        [$background, $color] = $styles[$mode] ?? ['#f3f4f6', '#111827'];

        return self::renderBadgePill((string) ($labels[$mode] ?? strtoupper($mode)), $background, $color, true);
    }

    private static function renderInfoBadge(string $label): string
    {
        $styles = [
            'vorbereitet' => ['#dbeafe', '#1d4ed8'],
            'geplant' => ['#fef3c7', '#92400e'],
            'kein OAuth erforderlich' => ['#dcfce7', '#166534'],
        ];

        [$background, $color] = $styles[$label] ?? ['#f3f4f6', '#111827'];

        return self::renderBadgePill($label, $background, $color, true);
    }

    private static function renderBadgePill(string $label, string $background, string $color, bool $uppercase = false): string
    {
        $style = 'background:' . esc_attr($background) . ';color:' . esc_attr($color) . ';';
        $style .= $uppercase ? 'text-transform:uppercase;' : '';

        return '<span class="cc-app-badge" style="' . $style . '">' . esc_html($label) . '</span>';
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
                'scopes' => CloudReadonlyProviderService::getGoogleReadonlyScopes(),
                'hint' => 'Readonly-Live ist auf maximal 5 Metadateneintraege begrenzt. Kein Download, kein Upload, kein Move, kein Delete.',
                'status_label' => 'vorbereitet',
                'docs_status' => 'geplant',
                'docs_note' => 'Es wird nur der Scope fuer readonly Metadatenzugriff vorbereitet.',
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
                'file_key' => $key,
                'file_name' => $file['name'],
                'provider' => $providerLabel,
                'virtual_source' => (string) ($preview['source'] ?? '/cloud/' . $file['name']),
                'virtual_target' => (string) ($preview['target'] ?? '/local/' . $file['name']),
                'size' => $file['size'],
                'last_sync' => $file['last_sync'],
                'direction' => $file['direction'],
                'status' => $file['status'],
                'simulated_action' => (string) ($preview['action'] ?? 'update (simuliert)'),
                'conflict_status' => $file['conflict'],
                'checksum' => 'sim-' . substr(md5($connectionName . '|' . $file['name']), 0, 12),
                'preview_status' => (string) ($preview['status'] ?? 'preview'),
                'readonly' => ((string) ($preview['status'] ?? '')) === 'readonly',
                'blocked' => ((string) ($file['status'] ?? '')) === 'offline',
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
                'preview_status' => 'preview',
                'readonly' => false,
                'blocked' => false,
            ];
        }

        return [
            'initial' => $initial,
            'map' => $map,
        ];
    }

    private static function buildExplorerActivityEntries(array $previewRows): array
    {
        $entries = [];

        foreach (array_slice($previewRows, 0, 4) as $previewRow) {
            if ($previewRow['status'] === 'queued') {
                $entries[] = $previewRow['file'] . ' in Queue gezogen (simuliert)';
                continue;
            }

            if ($previewRow['status'] === 'konflikt' || $previewRow['conflict'] === 'konflikt') {
                $entries[] = 'Konfliktmarkierung fuer ' . $previewRow['file'] . ' simuliert';
                continue;
            }

            $entries[] = 'Simulierter Move fuer ' . $previewRow['file'] . ' vorbereitet';
        }

        if (empty($entries)) {
            $entries[] = 'Noch keine clientseitigen Drag-&-Drop-Simulationen ausgefuehrt.';
        }

        return $entries;
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

    private static function renderExplorerScript(array $detailMap, array $previewRows, array $conflictRows, array $health, array $activityEntries): void
    {
        echo '<script>';
        echo '(function(){';
        echo 'const detailRoot=document.getElementById("cc-file-detail-content");';
        echo 'const feedbackRoot=document.getElementById("cc-dnd-feedback");';
        echo 'const previewBody=document.getElementById("cc-preview-body");';
        echo 'const conflictBody=document.getElementById("cc-conflict-body");';
        echo 'const queueList=document.getElementById("cc-queue-list");';
        echo 'const activityList=document.getElementById("cc-activity-list");';
        echo 'const detailMap=' . wp_json_encode($detailMap) . ';';
        echo 'const initialPreviewRows=' . wp_json_encode(array_values($previewRows)) . ';';
        echo 'const initialConflictRows=' . wp_json_encode(array_values($conflictRows)) . ';';
        echo 'const initialHealth=' . wp_json_encode(array_values($health)) . ';';
        echo 'const initialActivity=' . wp_json_encode(array_values($activityEntries)) . ';';
        echo 'if(!detailRoot||!detailMap||!previewBody||!conflictBody||!queueList||!activityList){return;}';
        echo 'const badgePalette={geplant:["#dbeafe","#1d4ed8"],pausiert:["#e5e7eb","#374151"],erfolgreich:["#dcfce7","#166534"],fehlerhaft:["#fee2e2","#991b1b"],laeuft:["#fef3c7","#92400e"],aktiv:["#dcfce7","#166534"],inaktiv:["#e5e7eb","#374151"],testmodus:["#fef3c7","#92400e"],synchronisiert:["#dcfce7","#166534"],ausstehend:["#fef3c7","#92400e"],konflikt:["#fee2e2","#991b1b"],simuliert:["#ede9fe","#6d28d9"],offline:["#e5e7eb","#374151"],"safe-mode":["#e0f2fe","#075985"],keiner:["#f3f4f6","#111827"],preview:["#f3e8ff","#7e22ce"],queued:["#dbeafe","#1d4ed8"],readonly:["#f3f4f6","#111827"],blocked:["#fee2e2","#991b1b"]};';
        echo 'const renderBadge=function(label){const palette=badgePalette[label]||["#f3f4f6","#111827"];return \'<span style="display:inline-block;padding:2px 8px;border-radius:999px;background:\'+palette[0]+\';color:\'+palette[1]+\';font-weight:600;">\'+label+\'</span>\';};';
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
        echo 'const escapeHtml=function(value){return String(value).replace(/[&<>"\']/g,function(char){if(char==="&"){return "&amp;";} if(char==="<"){return "&lt;";} if(char===">"){return "&gt;";} if(char===\'"\'){return "&quot;";} return "&#039;";});};';
        echo 'const state={activeKey:Object.keys(detailMap)[0]||"",previewRows:initialPreviewRows.slice(),conflictRows:initialConflictRows.slice(),activity:initialActivity.slice(),health:initialHealth.slice()};';
        echo 'const activityMessage=function(file,action,target,blocked){if(blocked){return file+" fuer "+target+" geblockt (Safe-Mode)";} if(action==="queue"){return file+" in Queue gezogen";} if(action==="conflict"){return "Konfliktmarkierung fuer "+file+" simuliert";} if(action==="move"){return "Simulierter Move fuer "+file+" vorbereitet";} return file+" zur Preview hinzugefuegt";};';
        echo 'const folderTargets={"/":"preview","/Dokumente":"move","/Uploads":"move","/Archiv":"move","/Sync Queue":"queue"};';
        echo 'const statusText=function(action){if(action==="queue"){return "queued";} if(action==="conflict"){return "konflikt";} if(action==="move"){return "simuliert";} return "preview";};';
        echo 'const actionText=function(action,target){if(action==="queue"){return "zur Queue hinzugefuegt (simuliert)";} if(action==="conflict"){return "als Konflikt markiert (simuliert)";} if(action==="move"){return "Move nach "+target+" (simuliert)";} return "zur Preview hinzugefuegt (simuliert)";};';
        echo 'const nowString=function(){const now=new Date();const pad=function(value){return String(value).padStart(2,"0");};return now.getFullYear()+"-"+pad(now.getMonth()+1)+"-"+pad(now.getDate())+" "+pad(now.getHours())+":"+pad(now.getMinutes())+":"+pad(now.getSeconds());};';
        echo 'const setActiveRow=function(key){document.querySelectorAll(".cc-explorer-row").forEach(function(row){row.style.outline=row.getAttribute("data-file-key")===key?"2px solid #72aee6":"none";row.style.outlineOffset=row.getAttribute("data-file-key")===key?"-2px":"0";});};';
        echo 'const syncDetailButtons=function(detail){document.querySelectorAll(".cc-detail-action").forEach(function(button){const disable=!!(detail&& (detail.readonly||detail.blocked)); button.disabled=disable; button.setAttribute("aria-disabled",disable?"true":"false"); button.style.cursor=disable?"not-allowed":""; button.style.opacity=disable?"0.6":"";});};';
        echo 'const updateDetail=function(key){if(!detailMap[key]){return;} state.activeKey=key; detailRoot.innerHTML=renderTable(detailMap[key]); setActiveRow(key); syncDetailButtons(detailMap[key]);};';
        echo 'const showFeedback=function(detail,target,action,blocked){if(!feedbackRoot){return;} const statusLabel=blocked?"blocked":"Safe-Mode Preview"; const hint=blocked?"Drop erkannt, aber keine echte Aktion ausgefuehrt. Datei bleibt unveraendert.":"Keine echte Dateioperation ausgefuehrt. Queue und Preview wurden nur clientseitig simuliert."; feedbackRoot.style.display="block"; feedbackRoot.style.borderColor=blocked?"#d63638":"#72aee6"; feedbackRoot.style.background=blocked?"#fcf0f1":"#f0f6fc"; feedbackRoot.innerHTML="<strong>"+escapeHtml(detail.file_name)+"</strong><div style=\"margin-top:6px;display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:8px;\"><div><strong>Quelle</strong><br><code>"+escapeHtml(detail.virtual_source)+"</code></div><div><strong>Ziel</strong><br><code>"+escapeHtml(target)+"</code></div><div><strong>Aktion</strong><br>"+escapeHtml(actionText(action,target))+"</div><div><strong>Status</strong><br>"+escapeHtml(statusLabel)+"</div></div><div style=\"margin-top:8px;color:#50575e;\">"+escapeHtml(hint)+"</div>";};';
        echo 'const renderPreviewRows=function(){if(!state.previewRows.length){previewBody.innerHTML=\'<tr><td colspan="7">Noch keine simulierten Preview-Daten verfuegbar.</td></tr>\';return;} previewBody.innerHTML=state.previewRows.map(function(row){return "<tr><td>"+escapeHtml(row.file)+"</td><td><code>"+escapeHtml(row.source)+"</code></td><td><code>"+escapeHtml(row.target)+"</code></td><td>"+escapeHtml(row.action)+"</td><td>"+renderBadge(row.status)+"</td><td>"+escapeHtml(row.size)+"</td><td>"+escapeHtml(row.timestamp)+"</td></tr>";}).join("");};';
        echo 'const renderConflictRows=function(){if(!state.conflictRows.length){conflictBody.innerHTML=\'<tr><td colspan="4">Keine virtuellen Konflikte im aktuellen Preview-Fenster.</td></tr>\';return;} conflictBody.innerHTML=state.conflictRows.map(function(row){return "<tr><td>"+escapeHtml(row.file)+"</td><td>"+escapeHtml(row.local)+"</td><td>"+escapeHtml(row.cloud)+"</td><td>"+renderBadge(row.status)+"</td></tr>";}).join("");};';
        echo 'const renderQueueList=function(){if(!state.previewRows.length){queueList.innerHTML="<li>Keine Eintraege in der virtuellen Queue.</li>";return;} queueList.innerHTML=state.previewRows.map(function(row){return "<li style=\"margin-bottom:6px;\"><strong>"+escapeHtml(row.file)+"</strong> - "+escapeHtml(row.action)+" - "+renderBadge(row.status)+"</li>";}).join("");};';
        echo 'const renderActivity=function(){activityList.innerHTML=state.activity.slice(0,8).map(function(entry){return "<li style=\"margin-bottom:6px;\">"+escapeHtml(entry)+"</li>";}).join("");};';
        echo 'const updateHealth=function(){const queued=state.previewRows.filter(function(row){return row.status==="queued";}).length; const simulated=state.previewRows.filter(function(row){return row.status==="preview"||row.status==="safe-mode"||row.status==="simuliert";}).length; const conflicts=state.previewRows.filter(function(row){return row.status==="konflikt";}).length; const ignored=state.previewRows.filter(function(row){return row.status==="readonly"||row.action==="ignoriert";}).length; const now=nowString(); document.querySelectorAll("[data-health-label]").forEach(function(card){const label=card.getAttribute("data-health-label"); const valueNode=card.querySelector("[data-health-value]"); const noteNode=card.querySelector("[data-health-note]"); if(!valueNode||!noteNode){return;} if(label==="Verarbeitet"){valueNode.textContent=String(queued); noteNode.textContent="Virtuell als queued markiert";} if(label==="Simuliert"){valueNode.textContent=String(simulated); noteNode.textContent="Preview-, Drag-&-Drop- und Safe-Mode-Eintraege";} if(label==="Konflikt"){valueNode.textContent=String(conflicts); noteNode.textContent="Virtuelle Konfliktfaelle";} if(label==="Ignoriert"){valueNode.textContent=String(ignored); noteNode.textContent="Read-only oder geblockte Simulationen";} if(label==="Letzte Simulation"){valueNode.textContent=now; noteNode.textContent="Clientseitige Drag-&-Drop-Simulation";} if(label==="Letzte Queue-Aktualisierung"){valueNode.textContent=now; noteNode.textContent="Nur virtuelle Queue-Metadaten";} if(label==="Queue-Groesse"){valueNode.textContent=String(state.previewRows.length); noteNode.textContent="Alle sichtbaren Safe-Mode-Eintraege";} });};';
        echo 'const rerender=function(){renderPreviewRows(); renderConflictRows(); renderQueueList(); renderActivity(); updateHealth();};';
        echo 'const applySimulation=function(key,zone){const detail=detailMap[key]; if(!detail){return;} const target=zone.target; const action=zone.action; const blocked=detail.blocked||detail.readonly; showFeedback(detail,target,action,blocked); if(blocked){detail.status=detail.readonly?"readonly":"blocked"; detail.simulated_action="blocked (simuliert)"; updateDetail(key); return;} state.activity.unshift(activityMessage(detail.file_name,action,target,blocked)); detail.virtual_target=target; detail.simulated_action=actionText(action,target); detail.status=statusText(action); detail.preview_status=statusText(action); detail.conflict_status=action==="conflict"?"konflikt":"keiner"; const timestamp=nowString(); const previewRow={file:detail.file_name,source:detail.virtual_source,target:detail.virtual_target,action:detail.simulated_action,status:detail.status,size:detail.size,timestamp:timestamp,provider:detail.provider,last_sync:timestamp,direction:detail.direction||"safe-mode",conflict:detail.conflict_status}; state.previewRows=[previewRow].concat(state.previewRows.filter(function(row){return row.file!==detail.file_name;})); if(action==="conflict"){state.conflictRows=[{file:detail.file_name,local:"Simulierter lokaler Konflikt",cloud:"Simulierter Cloud-Konflikt",status:"konflikt"}].concat(state.conflictRows.filter(function(row){return row.file!==detail.file_name;}));} updateDetail(key); rerender();};';
        echo 'const resolveZone=function(zoneType,target){if(zoneType==="folder"){const action=folderTargets[target]||"move"; return {action:action,target:target==="\/Sync Queue"?"Virtuelle Warteschlange":target};} if(zoneType==="preview"){return {action:"preview",target:target};} if(zoneType==="queue"){return {action:"queue",target:target};} if(zoneType==="conflict"){return {action:"conflict",target:target};} return {action:"preview",target:target};};';
        echo 'document.querySelectorAll(".cc-explorer-file-trigger").forEach(function(button){button.addEventListener("click",function(){const key=button.getAttribute("data-file-key")||""; updateDetail(key);});});';
        echo 'document.querySelectorAll(".cc-inline-action").forEach(function(button){button.addEventListener("click",function(){const key=button.getAttribute("data-file-key")||""; const action=button.getAttribute("data-sim-action")||"preview"; const target=action==="queue"?"Virtuelle Warteschlange":(action==="conflict"?"Virtuelle Konflikte":"/Dokumente"); applySimulation(key,{action:action,target:target});});});';
        echo 'document.querySelectorAll(".cc-detail-action").forEach(function(button){button.addEventListener("click",function(){if(!state.activeKey){return;} const action=button.getAttribute("data-sim-action")||"preview"; const target=action==="queue"?"Virtuelle Warteschlange":(action==="conflict"?"Virtuelle Konflikte":"/Dokumente"); applySimulation(state.activeKey,{action:action,target:target});});});';
        echo 'document.querySelectorAll(".cc-explorer-row").forEach(function(row){row.addEventListener("dragstart",function(event){const key=row.getAttribute("data-file-key")||""; const detail=detailMap[key]; if(!detail){event.preventDefault();return;} if(detail.readonly||detail.blocked){event.preventDefault(); showFeedback(detail,"Safe-Mode Drop-Zone","preview",true); updateDetail(key); return;} state.activeKey=key; event.dataTransfer.setData("text/plain",key); event.dataTransfer.effectAllowed="move"; row.style.opacity="0.55"; row.style.background="#f0f6fc"; showFeedback(detail,"Safe-Mode Drop-Zone","preview",false);}); row.addEventListener("dragend",function(){row.style.opacity="1"; row.style.background=row.getAttribute("data-blocked")==="true"?"#fcf0f1":(row.getAttribute("data-readonly")==="true"?"#f6f7f7":""); setActiveRow(state.activeKey); document.querySelectorAll(".cc-drop-zone").forEach(function(zone){zone.style.outline="none"; zone.style.backgroundColor="";});});});';
        echo 'document.querySelectorAll(".cc-drop-zone").forEach(function(zone){zone.addEventListener("dragover",function(event){event.preventDefault(); zone.style.outline="2px dashed #72aee6"; zone.style.outlineOffset="2px"; zone.style.backgroundColor="#f0f6fc";}); zone.addEventListener("dragleave",function(){zone.style.outline="none"; zone.style.backgroundColor="";}); zone.addEventListener("drop",function(event){event.preventDefault(); zone.style.outline="none"; zone.style.backgroundColor=""; const key=event.dataTransfer.getData("text/plain")||state.activeKey; const zoneType=zone.getAttribute("data-drop-zone")||"preview"; const target=zone.getAttribute("data-drop-target")||"/local/sync-preview"; applySimulation(key,resolveZone(zoneType,target));});});';
        echo 'document.querySelectorAll(".cc-drop-zone button,.cc-explorer-file-trigger,.cc-inline-action,.cc-detail-action").forEach(function(node){node.setAttribute("aria-label",(node.textContent||"Safe-Mode Aktion").trim());});';
        echo 'updateDetail(state.activeKey); rerender();';
        echo '})();';
        echo '</script>';
    }
}
