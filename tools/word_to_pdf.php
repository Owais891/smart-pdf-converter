<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Word to PDF
 */
$toolSlug      = 'word_to_pdf';
$toolName      = 'Word to PDF';
$toolIcon      = 'fa-file-word';
$toolColor     = 'tc-blue';
$toolDesc      = 'Convert DOC or DOCX documents into high-quality PDF files using LibreOffice.';
$allowedExt    = ['.doc', '.docx', '.odt', '.txt'];
$allowedTypes  = [
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.oasis.opendocument.text',
    'text/plain',
];
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Supported formats: .doc, .docx, .odt, .txt. Conversion runs through LibreOffice for accurate formatting.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
