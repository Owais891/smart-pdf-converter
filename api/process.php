<?php
/**
 * Smart PDF - API: process.php
 *
 * Receives a list of uploaded files + tool options and runs the
 * requested PDF operation. Returns JSON with download URL(s).
 *
 * POST params:
 *   tool            string  e.g. "merge_pdf"
 *   files[]         array   of stored filenames (returned by upload.php)
 *   originals[]     array   of original filenames (parallel to files[])
 *   csrf_token      string
 *   + tool-specific options (see below)
 *
 * Response:
 *   { success:true, downloads:[{name,url}], message }
 * or
 *   { success:false, error:"..." }
 */
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_error('Method not allowed.', 405);
}
csrf_verify();

$tool   = trim($_POST['tool'] ?? '');
$files  = $_POST['files']     ?? [];
$originals = $_POST['originals'] ?? [];

if (!$tool) {
    send_json_error('Missing tool name.');
}
if (empty($files) || !is_array($files)) {
    send_json_error('No files to process.');
}

// Normalise to arrays
$files = array_values($files);
$originals = array_values($originals);
if (count($originals) < count($files)) {
    $originals = array_pad($originals, count($files), basename($files[0]));
}

// Resolve each stored file to a full path
$inputPaths = [];
foreach ($files as $f) {
    $p = UPLOAD_DIR . basename($f);
    if (!is_file($p)) {
        send_json_error('File not found: ' . basename($f));
    }
    $inputPaths[] = $p;
}

$userId = is_logged_in() ? (int)$_SESSION['user_id'] : null;
$originalNameForLog = $originals[0] ?? 'document.pdf';

/**
 * Helper: build a download URL pointing to /api/download.php
 */
function build_download_url(string $filename, ?string $displayName = null): string
{
    $url = APP_URL . '/api/download.php?file=' . urlencode($filename);
    if ($displayName !== null && $displayName !== '') {
        $url .= '&name=' . urlencode($displayName);
    }
    return $url;
}

/**
 * Preserve PDF pages in a DOCX using high-resolution page images.
 */
function pdf_pages_to_docx(string $input, string $output): array
{
    if (!is_file($input)) {
        return [false, 'The uploaded PDF could not be found.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pdf_to_docx.py';
    $command = 'python ' .
        escapeshellarg($script) . ' ' .
        escapeshellarg($input) . ' ' .
        escapeshellarg($output) . ' 2>&1';

    exec($command, $lines, $status);
    if ($status === 0 && is_file($output)) {
        return [true, $output];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'The PDF did not produce a Word document.';
    }
    return [false, $message];
}

/**
 * Extract PDF tables or text into a real XLSX workbook.
 */
function pdf_to_excel(string $input, string $output): array
{
    if (!is_file($input)) {
        return [false, 'The uploaded PDF could not be found.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pdf_to_excel.py';
    $command = 'python ' .
        escapeshellarg($script) . ' ' .
        escapeshellarg($input) . ' ' .
        escapeshellarg($output) . ' 2>&1';

    exec($command, $lines, $status);
    if ($status === 0 && is_file($output)) {
        return [true, $output];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'The PDF could not be converted to Excel.';
    }
    return [false, $message];
}

/**
 * Render every PDF page as a PowerPoint slide.
 */
function pdf_to_ppt(string $input, string $output): array
{
    if (!is_file($input)) {
        return [false, 'The uploaded PDF could not be found.'];
    }

    $script = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'pdf_to_ppt.py';
    $command = 'python ' .
        escapeshellarg($script) . ' ' .
        escapeshellarg($input) . ' ' .
        escapeshellarg($output) . ' 2>&1';

    exec($command, $lines, $status);
    if ($status === 0 && is_file($output)) {
        return [true, $output];
    }

    $message = trim(implode("\n", $lines));
    if ($message === '') {
        $message = 'The PDF could not be converted to PowerPoint.';
    }
    return [false, $message];
}

/**
 * Helper: process a single-input PDF tool that produces one output file.
 */
function process_single_pdf_tool(callable $fn, array $inputPaths, string $outExt, string $tool, array $originals, ?int $userId, array $extraArgs = []): void
{
    $input = $inputPaths[0];
    $baseName = pathinfo($originals[0] ?? basename($input), PATHINFO_FILENAME);
    $stored   = unique_filename(PROCESSED_DIR, 'result.' . $outExt);
    $output   = PROCESSED_DIR . $stored;
    $original = $baseName . '.' . $outExt;

    [$ok, $result] = $fn($input, $output, ...$extraArgs);

    if (!$ok) {
        log_conversion($userId, $tool, $originals[0] ?? basename($input), null, null, 'failed', $result);
        safe_unlink($input);
        send_json_error($result);
    }

    log_conversion($userId, $tool, $originals[0] ?? basename($input), $stored, filesize($output), 'success');

    // Clean up the uploaded source
    safe_unlink($input);

    send_json_success([
        'downloads' => [[
            'name' => $original,
            'url'  => build_download_url($stored, $original),
        ]],
        'message' => 'File processed successfully.',
    ]);
}

/* -----------------------------------------------------------------
 * Dispatch by tool name
 * ----------------------------------------------------------------- */

switch ($tool) {

    /* ===== Merge PDF ===== */
    case 'merge_pdf':
        $output = PROCESSED_DIR . unique_filename(PROCESSED_DIR, 'merged.pdf');
        [$ok, $res] = pdf_merge($inputPaths, $output);
        if (!$ok) {
            log_conversion($userId, $tool, 'merge-request', null, null, 'failed', $res);
            send_json_error($res);
        }
        $totalSize = 0;
        foreach ($inputPaths as $p) { $totalSize += filesize($p); safe_unlink($p); }
        $firstInputName = basename(str_replace('\\', '/', (string)($originals[0] ?? 'document.pdf')));
        $firstInputBase = pathinfo($firstInputName, PATHINFO_FILENAME);
        $firstInputBase = trim(sanitize_filename(substr($firstInputBase, 0, 180)), " ._-");
        $downloadName = ($firstInputBase !== '' ? $firstInputBase : 'document') . '_merged.pdf';
        log_conversion($userId, $tool, count($inputPaths) . ' files merged', $downloadName, filesize($output), 'success');
        send_json_success([
            'downloads' => [[
                'name' => $downloadName,
                'url' => build_download_url(basename($output), $downloadName),
            ]],
            'message' => 'Merged ' . count($inputPaths) . ' PDFs into one file.',
        ]);
        break;

    /* ===== Split PDF ===== */
    case 'split_pdf':
        $mode = $_POST['split_mode'] ?? 'each'; // 'each' or 'range'
        $from = (int)($_POST['from_page'] ?? 1);
        $to   = isset($_POST['to_page']) && $_POST['to_page'] !== '' ? (int)$_POST['to_page'] : null;
        [$ok, $res] = pdf_split($inputPaths[0], PROCESSED_DIR, $from, $to, $mode);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? basename($inputPaths[0]), null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $downloads = [];
        $totalSize = 0;
        $sourceName = basename(str_replace('\\', '/', (string)($originals[0] ?? basename($inputPaths[0]))));
        $sourceBase = pathinfo($sourceName, PATHINFO_FILENAME);
        $sourceBase = trim(sanitize_filename(substr($sourceBase, 0, 170)), " ._-");
        if ($sourceBase === '') {
            $sourceBase = 'document';
        }
        foreach ($res as $p) {
            $totalSize += filesize($p);
            $storedName = basename($p);
            if (preg_match('/_page_(\d+)\.pdf$/i', $storedName, $match)) {
                $downloadName = $sourceBase . '_page_' . $match[1] . '.pdf';
            } elseif (preg_match('/_pages_(\d+-\d+)\.pdf$/i', $storedName, $match)) {
                $downloadName = $sourceBase . '_pages_' . $match[1] . '.pdf';
            } else {
                $downloadName = $sourceBase . '_split.pdf';
            }
            $downloads[] = [
                'name' => $downloadName,
                'url' => build_download_url($storedName, $downloadName),
            ];
        }
        log_conversion($userId, $tool, $originals[0] ?? 'split', count($downloads) . ' files', $totalSize, 'success');
        send_json_success([
            'downloads' => $downloads,
            'message' => count($downloads) . ' file(s) generated from split.',
        ]);
        break;

    /* ===== Rotate PDF ===== */
    case 'rotate_pdf':
        $degrees = (int)($_POST['degrees'] ?? 90);
        if (!in_array($degrees, [90, 180, 270], true)) {
            send_json_error('Invalid rotation angle.');
        }
        process_single_pdf_tool(
            function ($in, $out, $deg) { return pdf_rotate($in, $out, $deg); },
            $inputPaths, 'pdf', $tool, $originals, $userId, [$degrees]
        );
        break;

    /* ===== Compress PDF ===== */
    case 'compress_pdf':
        $originalSize = filesize($inputPaths[0]);
        $fn = function ($in, $out) use ($originalSize) {
            [$ok, $path] = pdf_compress($in, $out);
            if (!$ok) return [$ok, $path];
            $newSize = filesize($path);
            $saved = $originalSize > 0 ? round((1 - $newSize / $originalSize) * 100, 1) : 0;
            return [true, ['path' => $path, 'saved' => $saved, 'original' => $originalSize, 'new' => $newSize]];
        };
        $sourceName = basename(str_replace('\\', '/', (string)($originals[0] ?? basename($inputPaths[0]))));
        $baseName = trim(sanitize_filename(substr(pathinfo($sourceName, PATHINFO_FILENAME), 0, 180)), " ._-");
        if ($baseName === '') {
            $baseName = 'document';
        }
        $downloadName = $baseName . '-compressed.pdf';
        $stored   = unique_filename(PROCESSED_DIR, 'compressed.pdf');
        $output   = PROCESSED_DIR . $stored;
        [$ok, $result] = $fn($inputPaths[0], $output);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? '', null, null, 'failed', $result);
            send_json_error($result);
        }
        log_conversion($userId, $tool, $originals[0] ?? '', $downloadName, $result['new'], 'success');
        safe_unlink($inputPaths[0]);
        $sizeChange = $result['saved'] > 0
            ? 'File size reduced by ' . $result['saved'] . '%'
            : ($result['saved'] < 0
                ? 'Output file is ' . abs($result['saved']) . '% larger'
                : 'File size is unchanged');
        send_json_success([
            'downloads' => [['name' => $downloadName, 'url' => build_download_url($stored, $downloadName)]],
            'message' => $sizeChange . ' (' .
                         format_bytes($result['original']) . ' -> ' . format_bytes($result['new']) . ').',
        ]);
        break;

    /* ===== Protect PDF ===== */
    case 'protect_pdf':
        $password = (string)($_POST['password'] ?? '');
        if (strlen($password) < 4) {
            send_json_error('Password must be at least 4 characters.');
        }
        $permissions = [];
        if (!empty($_POST['allow_print']))    $permissions[] = 'print';
        if (!empty($_POST['allow_copy']))     $permissions[] = 'copy';
        if (!empty($_POST['allow_modify']))   $permissions[] = 'modify';
        if (empty($permissions))              $permissions = ['print'];
        $fn = function ($in, $out, $pw, $perms) {
            return pdf_protect($in, $out, $pw, $pw, $perms);
        };
        process_single_pdf_tool($fn, $inputPaths, 'pdf', $tool, $originals, $userId, [$password, $permissions]);
        break;

    /* ===== Unlock PDF ===== */
    case 'unlock_pdf':
        $password = (string)($_POST['password'] ?? '');
        if (strlen($password) < 1) {
            send_json_error('Please provide the password to remove.');
        }
        $fn = function ($in, $out, $pw) { return pdf_unlock($in, $out, $pw); };
        process_single_pdf_tool($fn, $inputPaths, 'pdf', $tool, $originals, $userId, [$password]);
        break;

    /* ===== Watermark PDF ===== */
    case 'watermark_pdf':
        $text = trim($_POST['watermark_text'] ?? '');
        if (mb_strlen($text) < 1 || mb_strlen($text) > 80) {
            send_json_error('Watermark text must be 1-80 characters.');
        }
        $fn = function ($in, $out, $t) { return pdf_watermark($in, $out, $t); };
        process_single_pdf_tool($fn, $inputPaths, 'pdf', $tool, $originals, $userId, [$text]);
        break;

    /* ===== Page Numbers ===== */
    case 'page_numbers':
        $position = $_POST['position'] ?? 'bottom-center';
        $fn = function ($in, $out, $pos) { return pdf_page_numbers($in, $out, $pos); };
        process_single_pdf_tool($fn, $inputPaths, 'pdf', $tool, $originals, $userId, [$position]);
        break;

    /* ===== PDF to JPG ===== */
    case 'pdf_to_jpg':
        [$ok, $res] = pdf_to_jpg($inputPaths[0], PROCESSED_DIR);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? basename($inputPaths[0]), null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $downloads = [];
        $sourceName = basename(str_replace('\\', '/', (string)($originals[0] ?? basename($inputPaths[0]))));
        $sourceName = sanitize_filename($sourceName);
        $sourceBase = pathinfo($sourceName, PATHINFO_FILENAME);
        $sourceBase = $sourceBase !== '' ? $sourceBase : 'document';
        foreach ($res as $index => $p) {
            $displayName = $sourceBase . '_page_' . str_pad((string)($index + 1), 3, '0', STR_PAD_LEFT) . '.jpg';
            $downloads[] = [
                'name' => $displayName,
                'url' => build_download_url(basename($p), $displayName),
            ];
        }
        log_conversion($userId, $tool, $originals[0] ?? 'pdf', count($downloads) . ' images', 0, 'success');
        send_json_success([
            'downloads' => $downloads,
            'message' => 'Generated ' . count($downloads) . ' image(s).',
        ]);
        break;

    /* ===== JPG to PDF ===== */
    case 'jpg_to_pdf':
        $output = PROCESSED_DIR . unique_filename(PROCESSED_DIR, 'images.pdf');
        [$ok, $res] = jpg_to_pdf($inputPaths, $output);
        if (!$ok) {
            log_conversion($userId, $tool, 'images', null, null, 'failed', $res);
            send_json_error($res);
        }
        foreach ($inputPaths as $p) safe_unlink($p);
        $imageNames = array_map(
            static function ($name) {
                return pathinfo(basename(str_replace('\\', '/', (string)$name)), PATHINFO_FILENAME);
            },
            $originals
        );
        $downloadBase = count($imageNames) === 1
            ? $imageNames[0]
            : ($imageNames[0] ?? 'images') . '_combined';
        $downloadBase = trim(sanitize_filename($downloadBase), " ._-");
        if ($downloadBase === '') {
            $downloadBase = 'images';
        }
        $downloadName = $downloadBase . '.pdf';
        log_conversion($userId, $tool, count($inputPaths) . ' images', $downloadName, filesize($output), 'success');
        send_json_success([
            'downloads' => [[
                'name' => $downloadName,
                'url' => build_download_url(basename($output), $downloadName),
            ]],
            'message' => 'Combined ' . count($inputPaths) . ' images into one PDF.',
        ]);
        break;

    /* ===== Organize PDF (delete pages) ===== */
    case 'organize_pdf':
        $pagesToDelete = trim($_POST['delete_pages'] ?? '');
        $pagesToKeep = trim($_POST['keep_pages'] ?? '');
        if ($pagesToDelete === '' && $pagesToKeep === '') {
            send_json_error('Please specify pages to delete or keep.');
        }
        // Determine the full list of pages to keep
        load_composer();
        if (!class_exists('setasign\Fpdi\Fpdi')) {
            send_json_error('Required library setasign/fpdi is not installed. Run: composer require setasign/fpdi');
        }
        try {
            $pdf = new \setasign\Fpdi\Fpdi();
            $total = $pdf->setSourceFile($inputPaths[0]);
            $keepSet = [];

            if ($pagesToKeep !== '') {
                $keepSet = parse_page_ranges($pagesToKeep, $total);
            } else {
                $deleteSet = parse_page_ranges($pagesToDelete, $total);
                for ($i = 1; $i <= $total; $i++) {
                    if (!in_array($i, $deleteSet, true)) $keepSet[] = $i;
                }
            }
            if (empty($keepSet)) {
                send_json_error('No pages left after organising.');
            }

            $out = new \setasign\Fpdi\Fpdi();
            foreach ($keepSet as $i) {
                $tpl = $out->setSourceFile($inputPaths[0]);
                $id  = $out->importPage($i);
                $size = $out->getTemplateSize($id);
                $out->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $out->useTemplate($id);
            }
            $stored = unique_filename(PROCESSED_DIR, 'organized.pdf');
            $output = PROCESSED_DIR . $stored;
            $out->Output($output, 'F');

            $sourceName = basename(str_replace('\\', '/', (string)($originals[0] ?? 'document.pdf')));
            $sourceBase = trim(sanitize_filename(substr(pathinfo($sourceName, PATHINFO_FILENAME), 0, 180)), " ._-");
            $downloadName = ($sourceBase !== '' ? $sourceBase : 'document') . '_organized.pdf';

            safe_unlink($inputPaths[0]);
            log_conversion($userId, $tool, $originals[0] ?? '', $downloadName, filesize($output), 'success');
            send_json_success([
                'downloads' => [['name' => $downloadName, 'url' => build_download_url($stored, $downloadName)]],
                'message' => 'Kept ' . count($keepSet) . ' of ' . $total . ' pages.',
            ]);
        } catch (Throwable $e) {
            log_conversion($userId, $tool, $originals[0] ?? '', null, null, 'failed', $e->getMessage());
            send_json_error('Organize failed: ' . $e->getMessage());
        }
        break;

    /* ===== Office -> PDF ===== */
    case 'word_to_pdf':
        [$ok, $res] = libreoffice_convert($inputPaths[0], PROCESSED_DIR);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? basename($inputPaths[0]), null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.pdf', 'url' => build_download_url(basename($res), $baseName . '.pdf')]],
            'message' => 'Converted to PDF via LibreOffice.',
        ]);
        break;

    case 'ppt_to_pdf':
        [$ok, $res, $engine] = powerpoint_to_pdf($inputPaths[0], PROCESSED_DIR);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? basename($inputPaths[0]), null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.pdf', 'url' => build_download_url(basename($res), $baseName . '.pdf')]],
            'message' => $engine === 'PowerPoint'
                ? 'Converted to PDF using Microsoft PowerPoint for improved layout and font fidelity.'
                : 'Converted to PDF using LibreOffice. Check the PDF for font or layout differences.',
        ]);
        break;

    /* ===== Excel -> PDF with print-area fit ===== */
    case 'excel_to_pdf':
        [$ok, $res] = excel_to_pdf($inputPaths[0], PROCESSED_DIR);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? basename($inputPaths[0]), null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.pdf', 'url' => build_download_url(basename($res), $baseName . '.pdf')]],
            'message' => 'Converted to a print-ready PDF with fit-to-page formatting.',
        ]);
        break;

    /* ===== PDF -> Word/Excel/PPT ===== */
    case 'pdf_to_word':
        $stored = unique_filename(PROCESSED_DIR, 'converted.docx');
        $output = PROCESSED_DIR . $stored;
        [$ok, $res] = pdf_pages_to_docx($inputPaths[0], $output);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? '', null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.docx', 'url' => build_download_url(basename($res), $baseName . '.docx')]],
            'message' => 'Converted to Word with the original page appearance preserved. Page content is embedded as images and is not editable as Word text.',
        ]);
        break;

    case 'pdf_to_excel':
        $stored = unique_filename(PROCESSED_DIR, 'converted.xlsx');
        $output = PROCESSED_DIR . $stored;
        [$ok, $res] = pdf_to_excel($inputPaths[0], $output);
        if (!$ok) {
            [$ok, $res] = libreoffice_convert_filter($inputPaths[0], PROCESSED_DIR, 'pdf', 'xlsx');
        }
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? '', null, null, 'failed', $res);
            send_json_error($res . ' PDF to Excel works best when the PDF contains real tables.');
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.xlsx', 'url' => build_download_url(basename($res), $baseName . '.xlsx')]],
            'message' => 'Converted to Excel via LibreOffice.',
        ]);
        break;

    case 'pdf_to_ppt':
        $stored = unique_filename(PROCESSED_DIR, 'converted.pptx');
        $output = PROCESSED_DIR . $stored;
        [$ok, $res] = pdf_to_ppt($inputPaths[0], $output);
        if (!$ok) {
            log_conversion($userId, $tool, $originals[0] ?? '', null, null, 'failed', $res);
            send_json_error($res);
        }
        safe_unlink($inputPaths[0]);
        $baseName = pathinfo($originals[0] ?? basename($res), PATHINFO_FILENAME);
        log_conversion($userId, $tool, $originals[0] ?? '', basename($res), filesize($res), 'success');
        send_json_success([
            'downloads' => [['name' => $baseName . '.pptx', 'url' => build_download_url(basename($res), $baseName . '.pptx')]],
            'message' => 'Converted to PowerPoint. Recognized text and detected square bullets are editable in line-based text boxes; scanned or incorrectly encoded text remains part of the slide image.',
        ]);
        break;

    /* ===== PDF Editor (text overlay demo) ===== */
    case 'pdf_editor':
        $text = trim($_POST['editor_text'] ?? '');
        $x = (float)($_POST['x'] ?? 50);
        $y = (float)($_POST['y'] ?? 50);
        if ($text === '') send_json_error('Please enter the text to add.');
        load_composer();
        if (!class_exists('setasign\Fpdi\Tcpdf\Fpdi')) {
            send_json_error('Required FPDI TCPDF adapter is not installed. Run: composer install');
        }
        try {
            $pdf = new \setasign\Fpdi\Tcpdf\Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pages = $pdf->setSourceFile($inputPaths[0]);
            for ($i = 1; $i <= $pages; $i++) {
                $tpl = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
                $pdf->SetFont('helvetica', 'B', 14);
                $pdf->SetTextColor(220, 53, 69);
                $pdf->Text($x, $y, $text);
            }
            $sourceName = basename(str_replace('\\', '/', (string)($originals[0] ?? 'document.pdf')));
            $sourceBase = trim(sanitize_filename(substr(pathinfo($sourceName, PATHINFO_FILENAME), 0, 180)), " ._-");
            $downloadName = ($sourceBase !== '' ? $sourceBase : 'document') . '-edited.pdf';
            $stored = unique_filename(PROCESSED_DIR, 'edited.pdf');
            $output = PROCESSED_DIR . $stored;
            $pdf->Output($output, 'F');
            safe_unlink($inputPaths[0]);
            log_conversion($userId, $tool, $originals[0] ?? '', $downloadName, filesize($output), 'success');
            send_json_success([
                'downloads' => [['name' => $downloadName, 'url' => build_download_url($stored, $downloadName)]],
                'message' => 'Text added to PDF.',
            ]);
        } catch (Throwable $e) {
            send_json_error('Editor failed: ' . $e->getMessage());
        }
        break;

    default:
        send_json_error('Unknown tool: ' . $tool);
}

/* ===================================================================
 * Helpers
 * =================================================================== */

/**
 * Parse "1,3,5-8" into [1,3,5,6,7,8]. Caps at $max.
 */
function parse_page_ranges(string $input, int $max): array
{
    $out = [];
    $parts = explode(',', $input);
    foreach ($parts as $part) {
        $part = trim($part);
        if ($part === '') continue;
        if (strpos($part, '-') !== false) {
            [$a, $b] = explode('-', $part, 2);
            $a = (int)$a; $b = (int)$b;
            if ($a > $b) { [$a, $b] = [$b, $a]; }
            for ($i = max(1, $a); $i <= min($max, $b); $i++) {
                $out[] = $i;
            }
        } else {
            $n = (int)$part;
            if ($n >= 1 && $n <= $max) $out[] = $n;
        }
    }
    return array_values(array_unique($out));
}

/**
 * Convert a file using LibreOffice with a specific filter (e.g. pdf->docx).
 */
function libreoffice_convert_filter(string $input, string $outputDir, string $fromExt, string $toExt): array
{
    $bin = LIBREOFFICE_PATH;
    if (PHP_OS_FAMILY === 'Windows') {
        if (!is_file($bin)) {
            $soffice = 'C:\\Program Files\\LibreOffice\\program\\soffice.com';
            if (!is_file($soffice)) $soffice = 'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe';
            $bin = $soffice;
        }
    }
    $check = shell_exec('"' . $bin . '" --version 2>&1');
    if (stripos((string)$check, 'LibreOffice') === false) {
        return [false, 'LibreOffice is not installed or not on PATH. Install it from https://www.libreoffice.org/'];
    }
    if (!is_dir($outputDir)) @mkdir($outputDir, 0755, true);

    $cmd = '"' . $bin . '" --headless --norestore --nolockcheck ' .
           '--convert-to ' . escapeshellarg($toExt) . ' ' .
           '--outdir ' . escapeshellarg($outputDir) . ' ' . escapeshellarg($input);
    shell_exec($cmd . ' 2>&1');

    $expected = $outputDir . preg_replace('/\\.[^.]+$/', '', basename($input)) . '.' . $toExt;
    if (is_file($expected)) {
        return [true, $expected];
    }
    return [false, 'LibreOffice could not convert to ' . strtoupper($toExt) . ' (the source file may be a scanned PDF or contain no extractable text).'];
}
