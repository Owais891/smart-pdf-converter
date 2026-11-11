<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - Split PDF
 */
$toolSlug      = 'split_pdf';
$toolName      = 'Split PDF';
$toolIcon      = 'fa-scissors';
$toolColor     = 'tc-red';
$toolDesc      = 'Split a PDF into separate files - either one file per page, or extract a page range.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label d-block">Split Mode</label>
    <div class="form-check">
        <input class="form-check-input" type="radio" name="split_mode" id="modeEach" value="each" checked>
        <label class="form-check-label" for="modeEach">Extract every page into its own PDF</label>
    </div>
    <div class="form-check mb-2">
        <input class="form-check-input" type="radio" name="split_mode" id="modeRange" value="range">
        <label class="form-check-label" for="modeRange">Extract a page range into a single PDF</label>
    </div>
    <div class="row g-2" id="rangeFields" style="display:none">
        <div class="col">
            <label class="form-label" for="from_page">From page</label>
            <input type="number" class="form-control" id="from_page" name="from_page" min="1" value="1">
        </div>
        <div class="col">
            <label class="form-label" for="to_page">To page (blank = end)</label>
            <input type="number" class="form-control" id="to_page" name="to_page" min="1">
        </div>
    </div>
    <script>
    document.querySelectorAll('input[name="split_mode"]').forEach(r => {
        r.addEventListener('change', () => {
            document.getElementById('rangeFields').style.display =
                document.querySelector('input[name="split_mode"]:checked').value === 'range' ? '' : 'none';
        });
    });
    </script>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
