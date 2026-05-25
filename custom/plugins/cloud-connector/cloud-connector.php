<?php
/**
 * Plugin Name: Cloud Connector
 * Description: Sichere Modulbasis fuer die zentrale Verwaltung von Cloud-Anbietern in webapp-central.de.
 * Version: 0.1.0
 * Author: Mark Dorth
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/interfaces/class-cloud-provider-interface.php';
require_once __DIR__ . '/includes/class-cloud-crypto.php';
require_once __DIR__ . '/includes/class-cloud-logger.php';
require_once __DIR__ . '/includes/class-cloud-storage.php';
require_once __DIR__ . '/includes/class-cloud-provider-registry.php';
require_once __DIR__ . '/includes/providers/class-abstract-provider.php';
require_once __DIR__ . '/includes/providers/class-google-drive-provider.php';
require_once __DIR__ . '/includes/providers/class-dropbox-provider.php';
require_once __DIR__ . '/includes/providers/class-onedrive-provider.php';
require_once __DIR__ . '/includes/providers/class-local-storage-provider.php';
require_once __DIR__ . '/includes/providers/class-webdav-provider.php';
require_once __DIR__ . '/includes/class-cloud-admin.php';
require_once __DIR__ . '/includes/class-cloud-connector-plugin.php';

CloudConnectorPlugin::bootstrap(__FILE__);
