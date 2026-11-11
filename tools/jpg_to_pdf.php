<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - JPG to PDF
 */
$toolSlug      = 'jpg_to_pdf';
$toolName      = 'JPG to PDF';
$toolIcon      = 'fa-file-image';
$toolColor     = 'tc-pink';
$toolDesc      = 'Combine JPG, JPEG and PNG images into a single PDF. Each image becomes one page.';
$allowedExt    = ['.jpg', '.jpeg', '.png'];
$allowedTypes  = ALLOWED_IMAGE_MIME;
$allowMultiple = true;
$maxFiles      = 50;
$extraOptions  = function () {
    echo '<div class="form-check">';
    echo '<input class="form-check-input" type="checkbox" id="fitPage" name="fit_page" value="1" checked>';
    echo '<label class="form-check-label" for="fitPage">Fit each image to its own page size</label>';
    echo '</div>';
    echo '<p class="mb-0 small text-muted mt-2"><i class="fas fa-circle-info me-1"></i>' .
         'You can upload up to 50 images. They will be combined in the order shown in the file list (alphabetical recommended).' .
         '</p>';
};
require __DIR__ . '/../includes/tool_page.php';
