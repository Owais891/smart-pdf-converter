<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF to Excel
 */
$toolSlug      = 'pdf_to_excel';
$toolName      = 'PDF to Excel';
$toolIcon      = 'fa-file-excel';
$toolColor     = 'tc-green';
$toolDesc      = 'Extract tables and numeric data from a PDF into an Excel (.xlsx) spreadsheet.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Works best when the PDF contains real table structures. Scanned image-only PDFs cannot be converted without OCR.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
