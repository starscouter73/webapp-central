<?php

declare(strict_types=1);

final class CloudProviderRegistry
{
    public static function all(): array
    {
        return [
            'google_drive' => new GoogleDriveProvider(),
            'dropbox' => new DropboxProvider(),
            'onedrive' => new OneDriveProvider(),
            'local_storage' => new LocalStorageProvider(),
            'webdav' => new WebDavProvider(),
        ];
    }

    public static function get(string $slug): ?CloudProviderInterface
    {
        $providers = self::all();

        return $providers[$slug] ?? null;
    }
}
