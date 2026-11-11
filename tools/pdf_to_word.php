<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF to Word
 */
$toolSlug      = 'pdf_to_word';
$toolName      = 'PDF to Word';
$toolIcon      = 'fa-file-word';
$toolColor     = 'tc-blue';
$toolDesc      = 'Convert PDF pages into a Word document while preserving their original appearance.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Each PDF page is placed into Word as a high-resolution image to preserve its layout, fonts, and tables. The page content will not be editable as Word text.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
