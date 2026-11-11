<?php
/**
 * Smart PDF - API: delete_file.php
 *
 * Deletes a stored upload/processed file. Used by the UI when a user
 * removes a file from a tool's queue before processing, or by the
 * dashboard to clear history.
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Method not allowed.', 405);
}
csrf_verify();

$stored = trim($_POST['stored'] ?? '');
if ($stored === '' || preg_match('/[^A-Za-z0-9._-]/', $stored)) {
    send_json_error('Invalid file name.');
}

$dir = ($_POST['dir'] ?? 'uploads') === 'processed' ? PROCESSED_DIR : UPLOAD_DIR;
$path = $dir . '/' . $stored;

safe_unlink($path);

// Best-effort: drop from uploaded_files table
try {
    db()->prepare('DELETE FROM uploaded_files WHERE stored_filename = ?')->execute([$stored]);
} catch (Throwable $e) {}

send_json_success(['deleted' => true]);
