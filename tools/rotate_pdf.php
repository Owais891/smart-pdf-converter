<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Rotate PDF
 */
$toolSlug      = 'rotate_pdf';
$toolName      = 'Rotate PDF';
$toolIcon      = 'fa-rotate';
$toolColor     = 'tc-purple';
$toolDesc      = 'Rotate all pages of a PDF by 90, 180 or 270 degrees.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label">Rotation Angle</label>
    <div class="btn-group w-100" role="group">
        <input type="radio" class="btn-check" name="degrees" id="deg90" value="90" checked>
        <label class="btn btn-outline-primary" for="deg90"><i class="fas fa-rotate-right me-1"></i>90°</label>
        <input type="radio" class="btn-check" name="degrees" id="deg180" value="180">
        <label class="btn btn-outline-primary" for="deg180"><i class="fas fa-arrows-rotate me-1"></i>180°</label>
        <input type="radio" class="btn-check" name="degrees" id="deg270" value="270">
        <label class="btn btn-outline-primary" for="deg270"><i class="fas fa-rotate-left me-1"></i>270°</label>
    </div>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
