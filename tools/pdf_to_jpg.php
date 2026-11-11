<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF to JPG
 */
$toolSlug      = 'pdf_to_jpg';
$toolName      = 'PDF to JPG';
$toolIcon      = 'fa-file-image';
$toolColor     = 'tc-pink';
$toolDesc      = 'Convert each page of a PDF into a high-resolution JPG image using PyMuPDF.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Output resolution is 150 DPI. Each page produces one JPG. You can download them individually after conversion.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
