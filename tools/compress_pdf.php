<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Compress PDF
 */
$toolSlug      = 'compress_pdf';
$toolName      = 'Compress PDF';
$toolIcon      = 'fa-compress';
$toolColor     = 'tc-yellow';
$toolDesc      = 'Reduce the file size of your PDF using Ghostscript (ebook quality). Falls back to FPDI re-rendering.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Best results are achieved with Ghostscript (compression level: /ebook, 150 DPI). ' .
         'If Ghostscript is not installed, we re-emit the PDF via FPDI which still removes some redundancy.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
