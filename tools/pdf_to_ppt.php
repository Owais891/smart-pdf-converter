<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF to PowerPoint
 */
$toolSlug      = 'pdf_to_ppt';
$toolName      = 'PDF to PowerPoint';
$toolIcon      = 'fa-file-powerpoint';
$toolColor     = 'tc-orange';
$toolDesc      = 'Convert PDF pages into PowerPoint slides with readable text added as editable objects.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Each page becomes a slide. Recognized text and detected square bullets are editable in line-based text boxes; scanned or incorrectly encoded text may remain part of the slide image.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
