<?php
/**
 * Smart PDF - API: upload.php
 *
 * Accepts one file via multipart/form-data, validates it, stores it
 * in storage/uploads with a unique name, records it in the
 * uploaded_files table, and returns JSON:
 *   { success: true, storedName, path, originalName, size, type }
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Method not allowed.', 405);
}

csrf_verify();

$toolName = trim($_POST['tool'] ?? '');
if (!$toolName) {
    send_json_error('Missing tool name.');
}

// Determine the allowed MIME types for this tool
$mimeMap = [
    // PDF-only tools
    'pdf_to_word'    => ALLOWED_PDF_MIME,
    'pdf_to_excel'   => ALLOWED_PDF_MIME,
    'pdf_to_ppt'     => ALLOWED_PDF_MIME,
    'pdf_to_jpg'     => ALLOWED_PDF_MIME,
    'merge_pdf'      => ALLOWED_PDF_MIME,
    'split_pdf'      => ALLOWED_PDF_MIME,
    'compress_pdf'   => ALLOWED_PDF_MIME,
    'rotate_pdf'     => ALLOWED_PDF_MIME,
    'organize_pdf'   => ALLOWED_PDF_MIME,
    'protect_pdf'    => ALLOWED_PDF_MIME,
    'unlock_pdf'     => ALLOWED_PDF_MIME,
    'watermark_pdf'  => ALLOWED_PDF_MIME,
    'page_numbers'   => ALLOWED_PDF_MIME,
    'pdf_editor'     => ALLOWED_PDF_MIME,
    // Image-only
    'jpg_to_pdf'     => ALLOWED_IMAGE_MIME,
    // Office -> PDF
    'word_to_pdf'    => [
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.oasis.opendocument.text',
        'text/plain',
    ],
    'excel_to_pdf'   => [
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.oasis.opendocument.spreadsheet',
        'text/csv',
    ],
    'ppt_to_pdf'     => [
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.presentation',
    ],
];

$allowed = $mimeMap[$toolName] ?? ALLOWED_PDF_MIME;

if (empty($_FILES['file'])) {
    send_json_error('No file received.');
}

[$ok, $path, $stored, $original, $error] = save_uploaded_file(
    $_FILES['file'], UPLOAD_DIR, $allowed
);

if (!$ok) {
    send_json_error($error);
}

// Persist in DB
try {
    $stmt = db()->prepare(
        'INSERT INTO uploaded_files
            (user_id, original_filename, stored_filename, file_path, file_type, file_size, expires_at)
         VALUES (?,?,?,?,?,?,?)'
    );
    $userId = is_logged_in() ? (int)$_SESSION['user_id'] : null;
    $expiresAt = date('Y-m-d H:i:s', time() + FILE_EXPIRY_SECONDS);
    $stmt->execute([
        $userId, $original, $stored, $path,
        mime_content_type($path) ?: '',
        filesize($path), $expiresAt
    ]);
} catch (Throwable $e) {
    // DB failure shouldn't block the upload itself.
    error_log('uploaded_files insert failed: ' . $e->getMessage());
}

send_json_success([
    'storedName'    => $stored,
    'path'          => $path,
    'originalName'  => $original,
    'size'          => filesize($path),
    'type'          => mime_content_type($path),
]);
