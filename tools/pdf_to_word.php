<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF to Word
 */
$toolSlug      = 'pdf_to_word';
$toolName      = 'PDF to Word';
$toolIcon      = 'fa-file-word';
$toolColor     = 'tc-blue';
$toolDesc      = 'Convert a text-based PDF into an editable Word (.docx) document while preserving text styling and approximate page layout.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Font styles, sizes, colors, and text placement are preserved as closely as possible. Complex layouts, columns, and tables may need adjustment. ' .
         'Scanned PDFs (images of text) require OCR first.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
