<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Page Numbers
 */
$toolSlug      = 'page_numbers';
$toolName      = 'Page Numbers';
$toolIcon      = 'fa-list-ol';
$toolColor     = 'tc-teal';
$toolDesc      = 'Add page numbers to every page of a PDF. Choose top or bottom placement.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label">Position</label>
    <select class="form-select" name="position">
        <option value="bottom-center" selected>Bottom Center</option>
        <option value="top-center">Top Center</option>
    </select>
    <small class="form-text">Numbers start at 1 on the first page.</small>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
