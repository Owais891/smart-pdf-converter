<?php
/**
 * Smart PDF - API: download.php
 *
 * Streams a file from storage/processed or storage/uploads back to the
 * client. Only files inside the storage directories are served, and
 * PHP files inside storage are blocked.
 *
 * Usage: download.php?file=<stored-name>[&dir=processed|uploads]
 */
require_once __DIR__ . '/../includes/functions.php';

$raw = $_GET['file'] ?? '';
$dir = ($_GET['dir'] ?? 'processed') === 'uploads' ? UPLOAD_DIR : PROCESSED_DIR;

// Validate input - never trust user-supplied filenames
if ($raw === '' || preg_match('/[^A-Za-z0-9._-]/', $raw)) {
    http_response_code(400);
    echo 'Invalid file name.';
    exit;
}

$path = realpath($dir . '/' . $raw);
$baseReal = realpath($dir);

// Block path traversal
if ($path === false || $baseReal === false || strpos($path, $baseReal) !== 0) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}
// Block PHP files
if (preg_match('/\.(php|phtml|phps)$/i', $path)) {
    http_response_code(403);
    echo 'Forbidden.';
    exit;
}
if (!is_file($path)) {
    http_response_code(404);
    echo 'File not found.';
    exit;
}

// Friendly name for the download
$displayName = $_GET['name'] ?? $raw;
download_file($path, $displayName);
