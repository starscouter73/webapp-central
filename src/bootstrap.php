<?php
declare(strict_types=1);

if (!function_exists('app_root')) {
    function app_root(): string
    {
        return dirname(__DIR__);
    }
}

if (!function_exists('app_is_local')) {
    function app_is_local(): bool
    {
        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? ''));
        $host = preg_replace('/:\d+$/', '', $host) ?? '';
        $remoteAddr = (string)($_SERVER['REMOTE_ADDR'] ?? '');

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true)
            || in_array($remoteAddr, ['127.0.0.1', '::1'], true);
    }
}

if (!function_exists('app_h')) {
    function app_h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('app_url')) {
    function app_url(string $path = ''): string
    {
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_pages')) {
    function app_pages(): array
    {
        return [
            ['file' => 'index.php', 'label' => 'Start'],
            ['file' => 'workspace.php', 'label' => 'Workspace'],
            ['file' => 'styleguide.php', 'label' => 'Styleguide'],
        ];
    }
}

if (!function_exists('app_current_page')) {
    function app_current_page(): string
    {
        return basename((string)($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
    }
}

if (!function_exists('app_watch_signature')) {
    function app_watch_signature(): string
    {
        $entries = [];
        $targets = [app_root() . '/public', app_root() . '/src', app_root() . '/README.md'];

        foreach ($targets as $target) {
            if (is_file($target)) {
                $entries[] = basename($target) . ':' . (string)(filemtime($target) ?: 0);
                continue;
            }

            if (!is_dir($target)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }

                $path = $file->getPathname();
                $extension = strtolower((string)pathinfo($path, PATHINFO_EXTENSION));
                if (!in_array($extension, ['php', 'css', 'md', 'js'], true)) {
                    continue;
                }

                $entries[] = str_replace('\\', '/', substr($path, strlen(app_root()) + 1))
                    . ':'
                    . (string)$file->getMTime();
            }
        }

        sort($entries);
        return sha1(implode('|', $entries));
    }
}
