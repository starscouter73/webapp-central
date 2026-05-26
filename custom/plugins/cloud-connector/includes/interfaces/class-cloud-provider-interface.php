<?php

declare(strict_types=1);

interface CloudProviderInterface
{
    public function connect(array $config = []);

    public function disconnect();

    public function listFiles(string $path);

    public function getFile(string $id);

    public function uploadFile(string $localPath, string $remotePath);

    public function downloadFile(string $remoteId, string $localPath);

    public function moveFile(string $remoteId, string $targetPath);

    public function deleteFile(string $remoteId);

    public function getQuota();

    public function getLastError(): ?string;
}
