<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PowerPoint to PDF
 */
$toolSlug      = 'ppt_to_pdf';
$toolName      = 'PowerPoint to PDF';
$toolIcon      = 'fa-file-powerpoint';
$toolColor     = 'tc-orange';
$toolDesc      = 'Convert PPT or PPTX presentations into PDF files, preserving slides and formatting.';
$allowedExt    = ['.ppt', '.pptx', '.odp'];
$allowedTypes  = [
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'application/vnd.oasis.opendocument.presentation',
];
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Supported formats: .ppt, .pptx, .odp. Animations are flattened but content is preserved.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
