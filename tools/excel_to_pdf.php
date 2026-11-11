<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Excel to PDF
 */
$toolSlug      = 'excel_to_pdf';
$toolName      = 'Excel to PDF';
$toolIcon      = 'fa-file-excel';
$toolColor     = 'tc-green';
$toolDesc      = 'Convert XLS or XLSX spreadsheets (including CSV) into clean, print-ready PDF files.';
$allowedExt    = ['.xls', '.xlsx', '.ods', '.csv'];
$allowedTypes  = [
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.oasis.opendocument.spreadsheet',
    'text/csv',
];
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    echo '<p class="mb-0 small text-muted"><i class="fas fa-circle-info me-1"></i>' .
         'Supported formats: .xls, .xlsx, .ods, .csv. Each worksheet becomes a separate PDF page.' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
