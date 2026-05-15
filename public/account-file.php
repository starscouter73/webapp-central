<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/src/layout.php';
require_once dirname(__DIR__) . '/src/auth/account_store.php';

auth_require_login();
$user = auth_current_user();
$email = (string)($user['email'] ?? '');
$id = trim((string)($_GET['id'] ?? ''));

if ($id === '') {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$document = account_document_find($email, $id);
if (!is_array($document)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$storageName = (string)($document['storage_name'] ?? '');
$path = account_user_documents_dir($email) . '/' . $storageName;
if (!is_file($path)) {
    http_response_code(404);
    exit('Datei nicht gefunden.');
}

$downloadName = (string)($document['original_name'] ?? 'dokument');
$mime = (string)(mime_content_type($path) ?: 'application/octet-stream');

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($path));
header('Content-Disposition: inline; filename="' . str_replace('"', '', $downloadName) . '"');
readfile($path);
exit;

