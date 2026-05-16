<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/auth/account_store.php';

auth_require_login();
$user = auth_current_user();
$email = (string)($user['email'] ?? '');
$path = account_avatar_path($email);

if ($path === null) {
    http_response_code(404);
    exit('Avatar nicht gefunden.');
}

$mime = (string)(mime_content_type($path) ?: 'application/octet-stream');
header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($path));
readfile($path);
exit;

