<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Watermark PDF
 */
$toolSlug      = 'watermark_pdf';
$toolName      = 'Add Watermark';
$toolIcon      = 'fa-droplet';
$toolColor     = 'tc-teal';
$toolDesc      = 'Stamp a diagonal text watermark across every page of your PDF.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label" for="watermark_text">Watermark Text</label>
    <input type="text" class="form-control" id="watermark_text" name="watermark_text"
           placeholder="e.g. CONFIDENTIAL" maxlength="80" required>
    <small class="form-text">Text is placed diagonally at 45° on every page, semi-transparent red.</small>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
