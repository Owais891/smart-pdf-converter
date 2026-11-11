<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Merge PDF
 */
$toolSlug      = 'merge_pdf';
$toolName      = 'Merge PDF';
$toolIcon      = 'fa-object-group';
$toolColor     = 'tc-red';
$toolDesc      = 'Combine two or more PDF files into a single document. Files are merged in the order shown.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = true;
$maxFiles      = 20;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Drag the files in the order you want them merged. Up to 20 PDFs, 100 MB each.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
