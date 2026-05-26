<?php

declare(strict_types=1);

final class CloudJobRunner
{
    public const HOOK = 'cloud_connector_run_jobs';

    public static function ensureScheduled(): void
    {
        if (
            (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON)
            || !CloudStorage::schemaReady()
            || wp_next_scheduled(self::HOOK)
        ) {
            return;
        }

        wp_schedule_event(time() + MINUTE_IN_SECONDS, 'hourly', self::HOOK);
    }

    public static function clearScheduled(): void
    {
        wp_clear_scheduled_hook(self::HOOK);
    }

    public static function isScheduled(): bool
    {
        return (bool) wp_next_scheduled(self::HOOK);
    }

    public static function nextRunTimestamp(): ?int
    {
        $timestamp = wp_next_scheduled(self::HOOK);

        return $timestamp ? (int) $timestamp : null;
    }

    public static function runDueJobs(): void
    {
        if (!CloudStorage::schemaReady()) {
            return;
        }

        $jobs = CloudStorage::getDueJobs();

        if (empty($jobs)) {
            CloudLogger::log('info', 'cron_idle', 'Keine faelligen Sync-Jobs gefunden.');

            return;
        }

        foreach ($jobs as $job) {
            try {
                $now = current_time('mysql');
                $nextRun = wp_date('Y-m-d H:i:s', current_time('timestamp') + HOUR_IN_SECONDS);

                CloudStorage::saveJob([
                    'id' => (int) $job['id'],
                    'provider_slug' => $job['provider_slug'],
                    'connection_id' => $job['connection_id'],
                    'direction' => $job['direction'],
                    'source_path' => $job['source_path'],
                    'target_path' => $job['target_path'],
                    'status' => 'geplant',
                    'last_run' => $now,
                    'next_run' => $nextRun,
                    'file_count' => max(1, (int) $job['file_count']),
                    'error_text' => 'Automatischer Safe-Mode-Simulationslauf. Keine Dateioperationen ausgefuehrt.',
                ]);

                CloudLogger::log(
                    'info',
                    'cron_simulation',
                    'Faelliger Sync-Job im Safe-Mode simuliert.',
                    ['job_id' => (int) $job['id'], 'next_run' => $nextRun]
                );
            } catch (Throwable $exception) {
                CloudLogger::log(
                    'error',
                    'cron_simulation_failed',
                    'Safe-Mode-Simulationslauf konnte nicht abgeschlossen werden.',
                    ['job_id' => (int) $job['id'], 'error' => $exception->getMessage()]
                );
            }
        }
    }
}
