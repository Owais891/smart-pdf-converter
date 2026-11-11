<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Organize PDF
 */
$toolSlug      = 'organize_pdf';
$toolName      = 'Organize PDF';
$toolIcon      = 'fa-arrows-up-down-left-right';
$toolColor     = 'tc-purple';
$toolDesc      = 'Delete unwanted pages from a PDF, or extract just the pages you want to keep.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="delete_pages">Pages to Delete</label>
            <input type="text" class="form-control" id="delete_pages" name="delete_pages"
                   placeholder="e.g. 1, 3, 5-8">
            <small class="form-text">Comma-separated list or ranges. Leave blank to use "keep" instead.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="keep_pages">Pages to Keep</label>
            <input type="text" class="form-control" id="keep_pages" name="keep_pages"
                   placeholder="e.g. 2, 4-10">
            <small class="form-text">Optional - if filled, "delete" is ignored.</small>
        </div>
    </div>
    <div class="alert alert-info mt-3 mb-0 small">
        <i class="fas fa-circle-info me-1"></i>
        Examples: <code>1, 3, 7</code> &middot; <code>5-12</code> &middot; <code>1, 4-6, 9</code>
    </div>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
