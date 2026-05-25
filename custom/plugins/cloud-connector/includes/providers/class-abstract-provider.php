<?php

declare(strict_types=1);

abstract class AbstractProvider implements CloudProviderInterface
{
    protected ?string $lastError = null;

    public function connect(array $config = [])
    {
        $this->lastError = null;

        if (empty($config)) {
            return new WP_Error('cloud_missing_config', 'Keine Zugangsdaten hinterlegt.');
        }

        return [
            'connected' => true,
            'safe_mode' => CloudStorage::getSetting('safe_mode', '1') === '1',
            'message' => 'Verbindung nur simuliert. Keine externe API-Anfrage ausgefuehrt.',
        ];
    }

    public function disconnect()
    {
        return true;
    }

    public function listFiles(string $path)
    {
        return $this->demoFiles($path);
    }

    public function getFile(string $id)
    {
        foreach ($this->demoFiles('/') as $file) {
            if ($file['remote_id'] === $id) {
                return $file;
            }
        }

        return new WP_Error('cloud_file_missing', 'Datei nicht gefunden.');
    }

    public function uploadFile(string $localPath, string $remotePath)
    {
        return $this->blockedMutation('upload', $localPath . ' -> ' . $remotePath);
    }

    public function downloadFile(string $remoteId, string $localPath)
    {
        return $this->blockedMutation('download', $remoteId . ' -> ' . $localPath);
    }

    public function moveFile(string $remoteId, string $targetPath)
    {
        return $this->blockedMutation('move', $remoteId . ' -> ' . $targetPath);
    }

    public function deleteFile(string $remoteId)
    {
        return $this->blockedMutation('delete', $remoteId);
    }

    public function getQuota()
    {
        return [
            'used_bytes' => 10485760,
            'total_bytes' => 1073741824,
            'safe_mode' => true,
        ];
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    protected function blockedMutation(string $action, string $target)
    {
        $this->lastError = sprintf('Safe-Mode blockiert %s fuer %s.', $action, $target);

        return new WP_Error('cloud_safe_mode_blocked', $this->lastError);
    }

    protected function demoFiles(string $path): array
    {
        $base = trim($path, '/') === '' ? '/' : '/' . trim($path, '/');

        return [
            [
                'remote_id' => static::class . '-001',
                'path' => $base,
                'name' => 'readme.txt',
                'mime_type' => 'text/plain',
                'size_bytes' => 2048,
                'checksum' => md5(static::class . '-001'),
                'last_modified' => current_time('mysql'),
            ],
            [
                'remote_id' => static::class . '-002',
                'path' => $base,
                'name' => 'media',
                'mime_type' => 'inode/directory',
                'size_bytes' => 0,
                'checksum' => md5(static::class . '-002'),
                'last_modified' => current_time('mysql'),
            ],
        ];
    }
}
