<?php
/**
 * Smart PDF - Core Helper Functions
 * ------------------------------------------------------------------
 * Central place for: bootstrap, security, sessions, uploads,
 * PDF processing wrappers, file cleanup, logging, etc.
 *
 * Every public script in the project calls smartpdf_bootstrap() first.
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

/* ===================================================================
 * 1. BOOTSTRAP / SESSION
 * =================================================================== */

/**
 * Initialise session, regenerate id periodically, expose CSRF token.
 * Loads user settings (theme) into session if logged in.
 */
function smartpdf_bootstrap(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();

        // Regenerate id every 30 minutes to harden against fixation
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
        } elseif (time() - $_SESSION['_created'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['_created'] = time();
        }
    }

    // Make sure storage folders exist
    foreach ([UPLOAD_DIR, PROCESSED_DIR, TEMP_DIR] as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }
}

/* ===================================================================
 * 2. SECURITY HELPERS
 * =================================================================== */

/** Escape output for HTML context. */
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/** Generate / fetch CSRF token. */
function csrf_token(): string
{
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/** Render a hidden input with CSRF token for use inside <form>. */
function csrf_field(): string
{
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME .
           '" value="' . e(csrf_token()) . '">';
}

/** Verify CSRF token from POST. Calls send_json_error on AJAX, redirect otherwise. */
function csrf_verify(): void
{
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!hash_equals(csrf_token(), (string)$token)) {
        if (is_ajax()) {
            send_json_error('Invalid CSRF token. Please refresh the page.', 419);
        }
        flash('error', 'Security token expired. Please try again.');
        redirect_back();
    }
}

/** Detect AJAX request. */
function is_ajax(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || (($_SERVER['HTTP_ACCEPT'] ?? '') && stripos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

/** Validate email strictly. */
function is_valid_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Sanitise user-supplied filename (NEVER trust). */
function sanitize_filename(string $name): string
{
    $name = str_replace(['/', '\\', "\0"], '_', $name);
    $name = preg_replace('/[^\w\.\-\s]/u', '_', $name) ?? $name;
    $name = trim($name, " \t\n\r\0\x0b.");
    // Avoid leading dot
    if ($name === '' || $name[0] === '.') {
        $name = 'file_' . $name;
    }
    return substr($name, 0, 200);
}

/** Generate a unique stored filename preserving extension. */
function unique_filename(string $dir, string $originalName): string
{
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $ext = $ext ? '.' . strtolower($ext) : '';
    do {
        $name = bin2hex(random_bytes(8)) . $ext;
    } while (file_exists($dir . '/' . $name));
    return $name;
}

/* ===================================================================
 * 3. FLASH MESSAGES
 * =================================================================== */

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'msg' => $message];
}

function get_flashes(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

/* ===================================================================
 * 4. REDIRECTS & JSON
 * =================================================================== */

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function redirect_back(): void
{
    $back = $_SERVER['HTTP_REFERER'] ?? APP_URL;
    redirect($back);
}

function send_json($data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function send_json_error(string $msg, int $code = 400, array $extra = []): void
{
    send_json(array_merge(['success' => false, 'error' => $msg], $extra), $code);
}

function send_json_success(array $data = []): void
{
    send_json(array_merge(['success' => true], $data), 200);
}

/* ===================================================================
 * 5. AUTHENTICATION
 * =================================================================== */

function current_user(): ?array
{
    if (!empty($_SESSION['user_id'])) {
        try {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch() ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
    return null;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function is_admin(): bool
{
    return (current_user()['role'] ?? '') === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        flash('error', 'Please log in to continue.');
        redirect(APP_URL . '/auth/login.php');
    }
}

function require_admin(): void
{
    if (!is_admin()) {
        http_response_code(403);
        echo 'Forbidden - administrators only.';
        exit;
    }
}

function login_user(int $userId, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['_created'] = time();

    try {
        db()->prepare('UPDATE users SET last_login = NOW() WHERE id = ?')
            ->execute([$userId]);

        // Ensure a settings row exists
        $s = db()->prepare('SELECT id FROM user_settings WHERE user_id = ?');
        $s->execute([$userId]);
        if (!$s->fetch()) {
            db()->prepare('INSERT INTO user_settings (user_id, theme, language) VALUES (?, "light","en")')
                ->execute([$userId]);
        }
    } catch (Throwable $e) {
        // non-fatal
    }

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        try {
            db()->prepare('UPDATE users SET remember_token = ? WHERE id = ?')
                ->execute([hash('sha256', $token), $userId]);
        } catch (Throwable $e) {}
        setcookie('remember_me', $userId . ':' . $token, [
            'expires' => time() + 86400 * REMEMBER_ME_DAYS,
            'path' => '/', 'httponly' => true, 'samesite' => 'Lax',
        ]);
    }
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $p['path'], 'domain' => $p['domain'],
            'secure' => $p['secure'], 'httponly' => $p['httponly'],
        ]);
    }
    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
    session_destroy();
}

function try_remember_me(): void
{
    if (is_logged_in() || empty($_COOKIE['remember_me'])) {
        return;
    }
    [$uid, $token] = array_pad(explode(':', $_COOKIE['remember_me'], 2), 2, '');
    $uid = (int)$uid;
    if (!$uid || !$token) {
        return;
    }
    try {
        $stmt = db()->prepare('SELECT id FROM users WHERE id = ? AND remember_token = ? LIMIT 1');
        $stmt->execute([$uid, hash('sha256', $token)]);
        if ($row = $stmt->fetch()) {
            login_user((int)$row['id'], false);
        } else {
            setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    } catch (Throwable $e) {}
}

/* ===================================================================
 * 6. THEME
 * =================================================================== */

function current_theme(): string
{
    // 1) Logged-in user preference
    if (is_logged_in()) {
        try {
            $stmt = db()->prepare('SELECT theme FROM user_settings WHERE user_id = ? LIMIT 1');
            $stmt->execute([$_SESSION['user_id']]);
            if ($row = $stmt->fetch()) {
                return $row['theme'] === 'dark' ? 'dark' : 'light';
            }
        } catch (Throwable $e) {}
    }
    // 2) Session override
    if (!empty($_SESSION['theme'])) {
        return $_SESSION['theme'] === 'dark' ? 'dark' : 'light';
    }
    return 'light';
}

function set_theme(string $theme): void
{
    $_SESSION['theme'] = ($theme === 'dark') ? 'dark' : 'light';
    if (is_logged_in()) {
        try {
            db()->prepare(
                'INSERT INTO user_settings (user_id, theme, language)
                 VALUES (?, ?, "en")
                 ON DUPLICATE KEY UPDATE theme = VALUES(theme)'
            )->execute([$_SESSION['user_id'], $_SESSION['theme']]);
        } catch (Throwable $e) {}
    }
}

/* ===================================================================
 * 7. FILE & UPLOAD HELPERS
 * =================================================================== */

/**
 * Validate an uploaded file with finfo, with a content-checked fallback for
 * Office formats that the server reports as a generic MIME type.
 * Returns [ok, storedPath, storedName, originalName, error]
 */
function save_uploaded_file(
    array $file,
    string $targetDir,
    array $allowedMimes,
    int $maxSize = MAX_UPLOAD_SIZE
): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, null, null, null, 'Upload failed: ' . upload_error_string($file['error'] ?? 0)];
    }
    if ($file['size'] > $maxSize) {
        return [false, null, null, null, 'File exceeds the maximum allowed size (' . format_bytes($maxSize) . ').'];
    }

    // Use finfo to detect the real MIME - never trust the browser-supplied type.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, $allowedMimes, true)) {
        $officeMimeByExtension = [
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'odt'  => 'application/vnd.oasis.opendocument.text',
            'ppt'  => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odp'  => 'application/vnd.oasis.opendocument.presentation',
            'txt'  => 'text/plain',
        ];
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $expectedMime = $officeMimeByExtension[$extension] ?? null;
        $genericOfficeMimes = [
            'application/octet-stream',
            'application/zip',
            'application/x-zip-compressed',
            'application/x-ole-storage',
            'application/vnd.ms-office',
        ];

        if (
            !$expectedMime ||
            !in_array($expectedMime, $allowedMimes, true) ||
            !in_array($mime, $genericOfficeMimes, true) ||
            !is_valid_office_upload($file['tmp_name'], $extension)
        ) {
            return [false, null, null, null, 'File type "' . e($mime) . '" is not allowed.'];
        }
    }

    $original = sanitize_filename($file['name']);
    $stored   = unique_filename($targetDir, $original);
    $path     = rtrim($targetDir, '/') . '/' . $stored;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return [false, null, null, null, 'Could not store uploaded file on the server.'];
    }

    return [true, $path, $stored, $original, null];
}

function is_valid_office_upload(string $path, string $extension): bool
{
    if (in_array($extension, ['ppt', 'pptx', 'odp'], true)) {
        return is_valid_powerpoint_upload($path, $extension);
    }

    return is_valid_word_upload($path, $extension);
}

function is_valid_word_upload(string $path, string $extension): bool
{
    if ($extension === 'txt') {
        return !file_contains_bytes($path, "\0");
    }

    if ($extension === 'doc') {
        $signature = file_get_contents($path, false, null, 0, 8);
        return $signature === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"
            && file_contains_bytes($path, 'WordDocument');
    }

    if (!in_array($extension, ['docx', 'odt'], true)) {
        return false;
    }

    if ($extension === 'docx') {
        $contentTypes = read_office_zip_entry($path, '[Content_Types].xml');
        return office_zip_entry_exists($path, 'word/document.xml')
            && $contentTypes !== null
            && strpos($contentTypes, 'wordprocessingml.document.main+xml') !== false;
    }

    return read_office_zip_entry($path, 'mimetype') === 'application/vnd.oasis.opendocument.text';
}

function office_zip_entry_exists(string $path, string $entry): bool
{
    if (class_exists('ZipArchive')) {
        $archive = new ZipArchive();
        if ($archive->open($path) !== true) {
            return false;
        }

        try {
            return $archive->locateName($entry) !== false;
        } finally {
            $archive->close();
        }
    }

    if (!class_exists('PharData')) {
        return false;
    }

    try {
        $archive = new PharData($path);
        return isset($archive[$entry]);
    } catch (UnexpectedValueException $error) {
        return false;
    }
}

function read_office_zip_entry(string $path, string $entry): ?string
{
    if (class_exists('ZipArchive')) {
        $archive = new ZipArchive();
        if ($archive->open($path) !== true) {
            return null;
        }

        try {
            $content = $archive->getFromName($entry);
            return is_string($content) ? $content : null;
        } finally {
            $archive->close();
        }
    }

    if (!class_exists('PharData')) {
        return null;
    }

    try {
        $archive = new PharData($path);
        return isset($archive[$entry]) ? $archive[$entry]->getContent() : null;
    } catch (UnexpectedValueException $error) {
        return null;
    }
}

function file_contains_bytes(string $path, string $needle): bool
{
    $handle = fopen($path, 'rb');
    if ($handle === false) {
        return false;
    }

    $overlap = '';
    $overlapLength = max(0, strlen($needle) - 1);
    try {
        while (!feof($handle)) {
            $chunk = fread($handle, 8192);
            if ($chunk === false) {
                return false;
            }

            $buffer = $overlap . $chunk;
            if (strpos($buffer, $needle) !== false) {
                return true;
            }
            $overlap = $overlapLength > 0 ? substr($buffer, -$overlapLength) : '';
        }
    } finally {
        fclose($handle);
    }

    return false;
}

function is_valid_powerpoint_upload(string $path, string $extension): bool
{
    if ($extension === 'ppt') {
        $signature = file_get_contents($path, false, null, 0, 8);
        return $signature === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
    }

    if (!in_array($extension, ['pptx', 'odp'], true)) {
        return false;
    }

    if ($extension === 'pptx') {
        return office_zip_entry_exists($path, '[Content_Types].xml')
            && office_zip_entry_exists($path, 'ppt/presentation.xml');
    }

    return read_office_zip_entry($path, 'mimetype') === 'application/vnd.oasis.opendocument.presentation';
}

function upload_error_string(int $code): string
{
    return [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds the server size limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form size limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was selected.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
    ][$code] ?? 'Unknown upload error.';
}

function format_bytes(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes / 1048576, 1) . ' MB';
    return round($bytes / 1073741824, 2) . ' GB';
}

/**
 * Force-download a file with proper headers. Aborts script afterwards.
 */
function download_file(string $path, string $name, ?string $mime = null): void
{
    if (!is_file($path)) {
        http_response_code(404);
        echo 'File not found.';
        exit;
    }
    $mime = $mime ?: (mime_content_type($path) ?: 'application/octet-stream');
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $mime);
    header('Content-Disposition: attachment; filename="' . str_replace('"', '', $name) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($path));
    while (ob_get_level()) { ob_end_flush(); }
    readfile($path);
    exit;
}

/**
 * Log a conversion history record.
 */
function log_conversion(
    ?int $userId,
    string $toolName,
    string $original,
    ?string $converted = null,
    ?int $fileSize = null,
    string $status = 'success',
    ?string $errorMsg = null,
    ?string $action = null
): void {
    try {
        db()->prepare(
            'INSERT INTO conversion_history
             (user_id, tool_name, tool_action, original_filename, converted_filename, file_size, status, error_message)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([$userId, $toolName, $action, $original, $converted, $fileSize, $status, $errorMsg]);
    } catch (Throwable $e) {
        error_log('log_conversion failed: ' . $e->getMessage());
    }
}

/**
 * Delete a file (best-effort) ignoring missing files.
 */
function safe_unlink(?string $path): void
{
    if ($path && is_file($path)) {
        @unlink($path);
    }
}

/**
 * Clean up old temp/upload/processed files older than FILE_EXPIRY_SECONDS.
 */
function cleanup_temp_files(): void
{
    $cutoff = time() - FILE_EXPIRY_SECONDS;
    foreach ([UPLOAD_DIR, PROCESSED_DIR, TEMP_DIR] as $dir) {
        if (!is_dir($dir)) continue;
        foreach ((array)glob($dir . '*') as $f) {
            if (is_file($f) && filemtime($f) < $cutoff) {
                @unlink($f);
            }
        }
    }
    // Also expire rows in uploaded_files table
    try {
        db()->exec('DELETE FROM uploaded_files WHERE expires_at IS NOT NULL AND expires_at < NOW()');
        db()->exec("UPDATE conversion_history SET status='expired' WHERE status='success' AND created_at < NOW() - INTERVAL 1 DAY");
    } catch (Throwable $e) {}
}

/* ===================================================================
 * 8. COMPOSER AUTOLOAD
 * =================================================================== */

/**
 * Load Composer's autoloader if present. PDF libraries depend on it.
 */
function load_composer(): void
{
    $paths = [
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/../../vendor/autoload.php',
    ];
    foreach ($paths as $p) {
        if (file_exists($p)) {
            require_once $p;
            return;
        }
    }
}

/* ===================================================================
 * 9. PDF PROCESSING WRAPPERS
 * -------------------------------------------------------------------
 * Real, working implementations using FPDI + FPDF + TCPDF + LibreOffice
 * (and ImageMagick where helpful). Each function returns [ok, path|error].
 * =================================================================== */

/** Merge an array of PDF files into one. */
function pdf_merge(array $files, string $outputPath): array
{
    if (empty($files)) {
        return [false, 'No PDF files provided.'];
    }

    foreach ($files as $file) {
        if (!is_file($file)) {
            return [false, 'One of the selected PDF files could not be found.'];
        }
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'merge_pdfs.py';
    if (!is_file($script)) {
        return [false, 'The PDF merge conversion script is missing.'];
    }

    $command = 'python ' . escapeshellarg($script) . ' ' . escapeshellarg($outputPath);
    foreach ($files as $file) {
        $command .= ' ' . escapeshellarg($file);
    }

    exec($command . ' 2>&1', $lines, $status);
    if ($status === 0 && is_file($outputPath)) {
        return [true, $outputPath];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'Python or PyMuPDF is not available for PDF merging.';
    }
    error_log('PDF merge failed: ' . $message);
    return [false, 'Could not merge the selected PDFs. Check that Python and PyMuPDF are available and that the PDFs are not password-protected.'];
}

/** Split a PDF at a page boundary. Returns list of generated files. */
function pdf_split(string $inputPath, string $outputDir, int $fromPage = 1, ?int $toPage = null, string $mode = 'each'): array
{
    if (!is_file($inputPath)) {
        return [false, 'The uploaded PDF could not be found.'];
    }

    if (!in_array($mode, ['each', 'range'], true)) {
        return [false, 'Invalid split mode.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'split_pdf.py';
    if (!is_file($script)) {
        return [false, 'The PDF split conversion script is missing.'];
    }

    $fromPage = max(1, $fromPage);
    $toPage = $toPage ?? 0;
    $prefix = 'split_' . bin2hex(random_bytes(8));
    $command = 'python ' . escapeshellarg($script) . ' ' .
        escapeshellarg($inputPath) . ' ' .
        escapeshellarg($outputDir) . ' ' .
        escapeshellarg($prefix) . ' ' .
        escapeshellarg($mode) . ' ' .
        escapeshellarg((string)$fromPage) . ' ' .
        escapeshellarg((string)$toPage) . ' 2>&1';

    exec($command, $lines, $status);
    if ($status === 0) {
        $pattern = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . $prefix . '_*.pdf';
        $files = glob($pattern) ?: [];
        if ($files) {
            natsort($files);
            return [true, array_values($files)];
        }
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'Python or PyMuPDF is not available for PDF splitting.';
    }
    error_log('PDF split failed: ' . $message);
    return [false, 'Could not split the PDF. Check that Python and PyMuPDF are available and verify the selected page range.'];
}

/** Rotate all pages by 90/180/270 degrees. */
function pdf_rotate(string $inputPath, string $outputPath, int $degrees): array
{
    if (!in_array($degrees, [90, 180, 270], true)) {
        return [false, 'Invalid rotation angle.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'rotate_pdf.py';
    if (!is_file($script)) {
        return [false, 'The PDF rotation conversion script is missing.'];
    }

    $command = 'python ' . escapeshellarg($script) . ' ' .
        escapeshellarg($inputPath) . ' ' .
        escapeshellarg($outputPath) . ' ' .
        escapeshellarg((string)$degrees) . ' 2>&1';

    exec($command, $lines, $status);
    if ($status === 0 && is_file($outputPath)) {
        return [true, $outputPath];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'Python or PyMuPDF is not available for PDF rotation.';
    }
    error_log('PDF rotation failed: ' . $message);
    return [false, 'Could not rotate the PDF. Check that Python and PyMuPDF are available and that the PDF is not password-protected.'];
}

/** Compress PDF using Ghostscript if available, else just re-emit via TCPDF. */
function pdf_compress(string $inputPath, string $outputPath): array
{
    // Try Ghostscript first (best quality/size).
    if (PHP_OS_FAMILY === 'Windows') {
        $gs = '';
        foreach (['gswin64c.exe', 'gswin32c.exe', 'gs.exe'] as $candidate) {
            $found = trim((string)shell_exec('where.exe ' . $candidate . ' 2>NUL'));
            if ($found !== '') {
                $gs = preg_split('/\r?\n/', $found)[0];
                break;
            }
        }
        if ($gs === '') {
            foreach ([
                'C:\\Program Files\\gs\\*\\bin\\gswin64c.exe',
                'C:\\Program Files (x86)\\gs\\*\\bin\\gswin32c.exe',
            ] as $pattern) {
                $matches = glob($pattern);
                if ($matches) {
                    $gs = $matches[0];
                    break;
                }
            }
        }
    } else {
        $gs = trim((string)shell_exec('command -v gs 2>/dev/null'));
    }

    if ($gs !== '') {
        $cmd = escapeshellarg($gs) . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 ' .
               '-dPDFSETTINGS=/ebook -dNOPAUSE -dQUIET -dBATCH ' .
               '-sOutputFile=' . escapeshellarg($outputPath) . ' ' . escapeshellarg($inputPath);
        shell_exec($cmd);
        if (is_file($outputPath) && filesize($outputPath) > 0) {
            return [true, $outputPath];
        }
    }
    // Fallback: re-render via FPDI (may still drop some redundancy)
    load_composer();
    if (!class_exists('setasign\Fpdi\Fpdi')) {
        return [false, 'Required library not installed and Ghostscript missing.'];
    }
    try {
        $pdf = new \setasign\Fpdi\Fpdi();
        $count = $pdf->setSourceFile($inputPath);
        for ($i = 1; $i <= $count; $i++) {
            $id = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($id);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($id);
        }
        $pdf->Output($outputPath, 'F');
        return [true, $outputPath];
    } catch (Throwable $e) {
        return [false, 'Compress failed: ' . $e->getMessage()];
    }
}

/** Protect PDF with owner + user password using TCPDF import. */
function pdf_protect(string $inputPath, string $outputPath, string $userPass, string $ownerPass = '', array $permissions = ['print']): array
{
    load_composer();
    if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
        return [false, 'Required FPDI TCPDF adapter is not installed. Run: composer install'];
    }
    try {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pages = $pdf->setSourceFile($inputPath);
        for ($i = 1; $i <= $pages; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
        }
        $pdf->SetProtection($permissions, $userPass, $ownerPass ?: $userPass);
        $pdf->Output($outputPath, 'F');
        return [true, $outputPath];
    } catch (Throwable $e) {
        return [false, 'Protect failed: ' . $e->getMessage()];
    }
}

/** Attempt to decrypt a password-protected PDF (requires qpdf). */
function pdf_unlock(string $inputPath, string $outputPath, string $password): array
{
    if (PHP_OS_FAMILY === 'Windows') {
        $found = trim((string)shell_exec('where.exe qpdf.exe 2>NUL'));
        $qpdf = $found !== '' ? preg_split('/\r?\n/', $found)[0] : '';

        if ($qpdf === '') {
            foreach ([
                'C:\\Program Files\\qpdf\\bin\\qpdf.exe',
                'C:\\Program Files (x86)\\qpdf\\bin\\qpdf.exe',
            ] as $candidate) {
                if (is_file($candidate)) {
                    $qpdf = $candidate;
                    break;
                }
            }
        }

        if ($qpdf === '') {
            foreach ([
                'C:\\Program Files\\qpdf*\\bin\\qpdf.exe',
                'C:\\Program Files (x86)\\qpdf*\\bin\\qpdf.exe',
            ] as $pattern) {
                $matches = glob($pattern);
                if ($matches) {
                    $qpdf = $matches[0];
                    break;
                }
            }
        }
    } else {
        $qpdf = trim((string)shell_exec('command -v qpdf 2>/dev/null'));
    }

    if ($qpdf === '') {
        $message = PHP_OS_FAMILY === 'Windows'
            ? 'Unlock requires qpdf. Install it from https://github.com/qpdf/qpdf/releases, add its bin folder to PATH, then restart Apache and try again.'
            : 'Unlock requires qpdf. Install it with your package manager (for example, apt install qpdf) and try again.';
        return [false, $message];
    }

    $cmd = escapeshellarg($qpdf) . ' --password=' . escapeshellarg($password) .
           ' --decrypt ' . escapeshellarg($inputPath) . ' ' . escapeshellarg($outputPath);
    exec($cmd . ' 2>&1', $lines, $status);
    if ($status === 0 && is_file($outputPath) && filesize($outputPath) > 0) {
        return [true, $outputPath];
    }

    safe_unlink($outputPath);
    $details = trim(implode("\n", $lines));
    return [false, 'Could not unlock the PDF. Check that the password is correct.' .
        ($details !== '' ? ' ' . $details : '')];
}

/** Add a watermark text on every page. */
function pdf_watermark(string $inputPath, string $outputPath, string $text, string $mode = 'text'): array
{
    load_composer();
    if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
        return [false, 'Required FPDI TCPDF adapter is not installed. Run: composer install'];
    }
    try {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pages = $pdf->setSourceFile($inputPath);
        for ($i = 1; $i <= $pages; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
            // Watermark
            $pdf->SetFont('helvetica', 'B', 48);
            $pdf->SetTextColor(220, 53, 69); // red
            $pdf->SetAlpha(0.2);
            $pdf->StartTransform();
            $pdf->Rotate(45, $size['width'] / 2, $size['height'] / 2);
            $pdf->Text($size['width'] / 2 - strlen($text) * 3, $size['height'] / 2 - 5, $text);
            $pdf->StopTransform();
            $pdf->SetAlpha(1);
        }
        $pdf->Output($outputPath, 'F');
        return [true, $outputPath];
    } catch (Throwable $e) {
        return [false, 'Watermark failed: ' . $e->getMessage()];
    }
}

/** Add page numbers at the bottom-center of every page. */
function pdf_page_numbers(string $inputPath, string $outputPath, string $position = 'bottom-center'): array
{
    load_composer();
    if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
        return [false, 'Required FPDI TCPDF adapter is not installed. Run: composer install'];
    }
    try {
        $pdf = new \setasign\Fpdi\Tcpdf\Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAutoPageBreak(false, 0);
        $pages = $pdf->setSourceFile($inputPath);
        for ($i = 1; $i <= $pages; $i++) {
            $tpl = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(80, 80, 80);
            $y = ($position === 'top-center') ? 8 : $size['height'] - 12;
            $pageNumber = (string)$i;
            $pdf->Text(($size['width'] - $pdf->GetStringWidth($pageNumber)) / 2, $y, $pageNumber);
        }
        $pdf->Output($outputPath, 'F');
        return [true, $outputPath];
    } catch (Throwable $e) {
        return [false, 'Page numbers failed: ' . $e->getMessage()];
    }
}

/** Convert each PDF page to a JPG image using PyMuPDF. */
function pdf_to_jpg(string $inputPath, string $outputDir): array
{
    if (!is_file($inputPath)) {
        return [false, 'The uploaded PDF could not be found.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pdf_to_jpg.py';
    if (!is_file($script)) {
        return [false, 'The PDF-to-JPG conversion script is missing.'];
    }

    $prefix = 'pdf_' . bin2hex(random_bytes(8)) . '_page';
    $command = 'python ' . escapeshellarg($script) . ' ' .
        escapeshellarg($inputPath) . ' ' .
        escapeshellarg($outputDir) . ' ' .
        escapeshellarg($prefix) . ' 150 2>&1';

    exec($command, $lines, $status);
    if ($status === 0) {
        $files = glob(rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR . $prefix . '_*.jpg') ?: [];
        if ($files) {
            natsort($files);
            return [true, array_values($files)];
        }
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'Python or PyMuPDF is not available for PDF-to-JPG conversion.';
    }
    error_log('PDF-to-JPG conversion failed: ' . $message);
    return [false, 'Could not convert the PDF to JPG. Check that Python and PyMuPDF are available.'];
}

/** Convert JPG/PNG images into a single PDF. */
function jpg_to_pdf(array $imagePaths, string $outputPath): array
{
    if (!$imagePaths) {
        return [false, 'Please select at least one image.'];
    }

    foreach ($imagePaths as $imagePath) {
        if (!is_file($imagePath)) {
            return [false, 'An uploaded image could not be found.'];
        }
        $imageInfo = @getimagesize($imagePath);
        if (!$imageInfo || !in_array($imageInfo['mime'] ?? '', ALLOWED_IMAGE_MIME, true)) {
            return [false, 'One of the uploaded files is not a supported image.'];
        }
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'images_to_pdf.py';
    if (!is_file($script)) {
        return [false, 'The image-to-PDF conversion script is missing.'];
    }

    $command = 'python ' . escapeshellarg($script) . ' ' . escapeshellarg($outputPath);
    foreach ($imagePaths as $imagePath) {
        $command .= ' ' . escapeshellarg($imagePath);
    }

    exec($command . ' 2>&1', $lines, $status);
    if ($status === 0 && is_file($outputPath)) {
        return [true, $outputPath];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'Python or PyMuPDF is not available for image-to-PDF conversion.';
    }
    error_log('JPG-to-PDF conversion failed: ' . $message);
    return [false, 'Could not convert the selected images to PDF. Check that Python and PyMuPDF are available.'];
}

/**
 * Prepare an XLSX workbook for print-friendly PDF conversion.
 */
function prepare_excel_xlsx_for_pdf(string $input): string
{
    if (!class_exists('ZipArchive')) {
        return $input;
    }

    $ext = strtolower(pathinfo($input, PATHINFO_EXTENSION));
    if ($ext !== 'xlsx') {
        return $input;
    }

    if (!is_dir(TEMP_DIR)) {
        @mkdir(TEMP_DIR, 0755, true);
    }

    $prepared = TEMP_DIR . 'excel-print-' . bin2hex(random_bytes(8)) . '.xlsx';
    if (!copy($input, $prepared)) {
        return $input;
    }

    $zip = new ZipArchive();
    if ($zip->open($prepared) !== true) {
        @unlink($prepared);
        return $input;
    }

    $styleMap = [];
    $stylesXml = $zip->getFromName('xl/styles.xml');
    if ($stylesXml) {
        libxml_use_internal_errors(true);
        $stylesDom = new DOMDocument();
        if ($stylesDom->loadXML($stylesXml)) {
            $ns = $stylesDom->documentElement->namespaceURI ?: 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

            $borders = $stylesDom->getElementsByTagName('borders')->item(0);
            if (!$borders) {
                $borders = $stylesDom->createElementNS($ns, 'borders');
                $stylesDom->documentElement->appendChild($borders);
            }

            $border = $stylesDom->createElementNS($ns, 'border');
            foreach (['left', 'right', 'top', 'bottom', 'diagonal'] as $edge) {
                $edgeNode = $stylesDom->createElementNS($ns, $edge);
                if ($edge !== 'diagonal') {
                    $edgeNode->setAttribute('style', 'thin');
                    $color = $stylesDom->createElementNS($ns, 'color');
                    $color->setAttribute('rgb', 'FF9A9A9A');
                    $edgeNode->appendChild($color);
                }
                $border->appendChild($edgeNode);
            }
            $borders->appendChild($border);
            $borderId = $borders->getElementsByTagName('border')->length - 1;

            $cellXfs = $stylesDom->getElementsByTagName('cellXfs')->item(0);
            if (!$cellXfs) {
                $cellXfs = $stylesDom->createElementNS($ns, 'cellXfs');
                $cellXfs->setAttribute('count', '0');
                $stylesDom->documentElement->appendChild($cellXfs);
            }

            $xfNodes = $cellXfs->getElementsByTagName('xf');
            $originalCount = $xfNodes->length;
            for ($i = 0; $i < $originalCount; $i++) {
                $xf = $xfNodes->item($i);
                $bordered = $xf->cloneNode(true);
                $bordered->setAttribute('applyBorder', '1');
                $bordered->setAttribute('borderId', (string)$borderId);
                $cellXfs->appendChild($bordered);
                $styleMap[$i] = $originalCount + $i;
            }
            if ($originalCount === 0) {
                $defaultXf = $stylesDom->createElementNS($ns, 'xf');
                $defaultXf->setAttribute('applyBorder', '1');
                $defaultXf->setAttribute('borderId', (string)$borderId);
                $cellXfs->appendChild($defaultXf);
                $styleMap[0] = 0;
            }
            $cellXfs->setAttribute('count', (string)$cellXfs->getElementsByTagName('xf')->length);
            $zip->addFromString('xl/styles.xml', $stylesDom->saveXML());
        }
    }

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (!$name || !preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
            continue;
        }

        $xml = $zip->getFromName($name);
        if (!$xml) {
            continue;
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        if (!$dom->loadXML($xml)) {
            continue;
        }

        $root = $dom->documentElement;
        $ns = $root->namespaceURI ?: 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

        $sheetViews = $root->getElementsByTagName('sheetViews')->item(0);
        if (!$sheetViews) {
            $sheetViews = $dom->createElementNS($ns, 'sheetViews');
            $sheetView = $dom->createElementNS($ns, 'sheetView');
            $sheetView->setAttribute('showGridLines', '1');
            $sheetView->setAttribute('workbookViewId', '0');
            $sheetViews->appendChild($sheetView);
            $first = $root->firstChild;
            $root->insertBefore($sheetViews, $first);
        } else {
            $sheetView = $sheetViews->getElementsByTagName('sheetView')->item(0);
            if ($sheetView) {
                $sheetView->setAttribute('showGridLines', '1');
            }
        }

        foreach ($dom->getElementsByTagName('c') as $cell) {
            $oldStyle = $cell->hasAttribute('s') ? (int)$cell->getAttribute('s') : 0;
            $cell->setAttribute('s', (string)($styleMap[$oldStyle] ?? $styleMap[0] ?? 0));
        }

        $sheetPr = $root->getElementsByTagName('sheetPr')->item(0);
        if (!$sheetPr) {
            $sheetPr = $dom->createElementNS($ns, 'sheetPr');
            $first = $root->firstChild;
            $root->insertBefore($sheetPr, $first);
        }
        $pageSetupPr = $sheetPr->getElementsByTagName('pageSetUpPr')->item(0);
        if (!$pageSetupPr) {
            $pageSetupPr = $dom->createElementNS($ns, 'pageSetUpPr');
            $sheetPr->appendChild($pageSetupPr);
        }
        $pageSetupPr->setAttribute('fitToPage', '1');

        $pageSetup = $root->getElementsByTagName('pageSetup')->item(0);
        if (!$pageSetup) {
            $pageSetup = $dom->createElementNS($ns, 'pageSetup');
            $root->appendChild($pageSetup);
        }
        $pageSetup->removeAttribute('scale');
        $pageSetup->setAttribute('orientation', 'landscape');
        $pageSetup->setAttribute('paperSize', '9');
        $pageSetup->setAttribute('fitToWidth', '1');
        $pageSetup->setAttribute('fitToHeight', '0');

        $pageMargins = $root->getElementsByTagName('pageMargins')->item(0);
        if (!$pageMargins) {
            $pageMargins = $dom->createElementNS($ns, 'pageMargins');
            $root->appendChild($pageMargins);
        }
        foreach ([
            'left' => '0.25',
            'right' => '0.25',
            'top' => '0.35',
            'bottom' => '0.35',
            'header' => '0.15',
            'footer' => '0.15',
        ] as $attr => $value) {
            $pageMargins->setAttribute($attr, $value);
        }

        $zip->addFromString($name, $dom->saveXML());
    }

    $zip->close();
    return $prepared;
}

function excel_to_pdf(string $input, string $outputDir): array
{
    $prepared = prepare_excel_xlsx_for_pdf($input);
    $result = libreoffice_convert($prepared, $outputDir);
    if ($prepared !== $input) {
        @unlink($prepared);
    }
    return $result;
}

/**
 * Generic LibreOffice conversion. Used for office<->pdf both ways.
 * @param string $input     Full path of source file.
 * @param string $outputDir Where LibreOffice should write the result.
 * @param string $filter    LibreOffice filter name, optional.
 */
function libreoffice_convert(string $input, string $outputDir, string $filter = ''): array
{
    $bin = LIBREOFFICE_PATH;
    if (PHP_OS_FAMILY === 'Windows') {
        if (!is_file($bin)) {
            $soffice = 'C:\\Program Files\\LibreOffice\\program\\soffice.com';
            if (!is_file($soffice)) $soffice = 'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe';
            $bin = $soffice;
        }
    }
    // Check availability
    $check = shell_exec('"' . $bin . '" --version 2>&1');
    if (stripos((string)$check, 'LibreOffice') === false) {
        return [false, 'LibreOffice is not installed or not on PATH. Install it from https://www.libreoffice.org/'];
    }
    if (!is_dir($outputDir)) {
        @mkdir($outputDir, 0755, true);
    }
    $cmd = '"' . $bin . '" --headless --norestore --nolockcheck ' .
           '--convert-to pdf ' . ($filter ? '--infilter="' . $filter . '" ' : '') .
           ' --outdir ' . escapeshellarg($outputDir) . ' ' . escapeshellarg($input);
    shell_exec($cmd . ' 2>&1');
    $expected = $outputDir . preg_replace('/\\.[^.]+$/', '', basename($input)) . '.pdf';
    if (is_file($expected)) {
        return [true, $expected];
    }
    return [false, 'LibreOffice conversion produced no output. Check the source file.'];
}

function powerpoint_to_pdf(string $input, string $outputDir): array
{
    if (PHP_OS_FAMILY !== 'Windows') {
        $result = libreoffice_convert($input, $outputDir);
        $result[2] = 'LibreOffice';
        return $result;
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'ppt_to_pdf.ps1';
    $systemRoot = getenv('SystemRoot') ?: 'C:\\Windows';
    $powerShell = $systemRoot . '\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';
    if (!is_file($script) || !is_file($powerShell)) {
        $result = libreoffice_convert($input, $outputDir);
        $result[2] = 'LibreOffice';
        return $result;
    }
    if (!is_dir($outputDir) && !mkdir($outputDir, 0755, true) && !is_dir($outputDir)) {
        return [false, 'Could not create the PDF output directory.'];
    }

    $output = rtrim($outputDir, '/\\') . DIRECTORY_SEPARATOR .
        unique_filename($outputDir, pathinfo($input, PATHINFO_FILENAME) . '.pdf');
    $command = escapeshellarg($powerShell) .
        ' -NoProfile -NonInteractive -ExecutionPolicy Bypass -File ' .
        escapeshellarg($script) . ' ' .
        escapeshellarg($input) . ' ' . escapeshellarg($output);
    exec($command . ' 2>&1', $lines, $status);
    if ($status === 0 && is_file($output)) {
        return [true, $output, 'PowerPoint'];
    }

    if (is_file($output)) {
        unlink($output);
    }

    error_log('PowerPoint PDF export failed; falling back to LibreOffice: ' . implode(' | ', $lines));
    $result = libreoffice_convert($input, $outputDir);
    $result[2] = 'LibreOffice';
    return $result;
}

/* ===================================================================
 * 10. SETTINGS / META
 * =================================================================== */

function setting(string $key, $default = null)
{
    if (!is_logged_in()) return $default;
    try {
        $stmt = db()->prepare('SELECT ' . $key . ' FROM user_settings WHERE user_id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        return $row[$key] ?? $default;
    } catch (Throwable $e) {
        return $default;
    }
}

/* ===================================================================
 * 11. TOOL ICONS (shared with navbar + dashboard)
 * =================================================================== */

/**
 * Return the Font Awesome icon class for a given tool slug.
 * Used by the navbar, all tools index, dashboard activity list, etc.
 */
function tool_icon(string $slug): string
{
    return [
        'pdf_to_word'    => 'fa-file-word',
        'word_to_pdf'    => 'fa-file-word',
        'pdf_to_excel'   => 'fa-file-excel',
        'excel_to_pdf'   => 'fa-file-excel',
        'pdf_to_ppt'     => 'fa-file-powerpoint',
        'ppt_to_pdf'     => 'fa-file-powerpoint',
        'pdf_to_jpg'     => 'fa-file-image',
        'jpg_to_pdf'     => 'fa-file-image',
        'merge_pdf'      => 'fa-object-group',
        'split_pdf'      => 'fa-scissors',
        'compress_pdf'   => 'fa-compress',
        'rotate_pdf'     => 'fa-rotate',
        'unlock_pdf'     => 'fa-lock-open',
        'protect_pdf'    => 'fa-lock',
        'watermark_pdf'  => 'fa-droplet',
        'page_numbers'   => 'fa-list-ol',
        'organize_pdf'   => 'fa-arrows-up-down-left-right',
        'pdf_editor'     => 'fa-pen',
    ][$slug] ?? 'fa-file-pdf';
}

/* ===================================================================
 * 12. DATE / TIME HELPERS
 * =================================================================== */

function time_ago(string $datetime): string
{
    $ts = strtotime($datetime);
    $diff = time() - $ts;
    if ($diff < 60)   return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 604800) return floor($diff / 86400) . ' day(s) ago';
    return date('M j, Y', $ts);
}

/* ===================================================================
 * 12. AUTOBOOT
 * =================================================================== */

smartpdf_bootstrap();
try_remember_me();
// Lightly clean temp files roughly once per 50 requests
if (random_int(1, 50) === 1) {
    cleanup_temp_files();
}
