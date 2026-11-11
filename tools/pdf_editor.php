<?php
require_once __DIR__ . '/../includes/functions.php';
/**
 * Smart PDF - PDF Editor (text overlay)
 *
 * A lightweight editor that lets the user place a text string on every
 * page at a chosen (x, y) coordinate. Full canvas-based per-page
 * editing is out of scope for a university project.
 */
$toolSlug      = 'pdf_editor';
$toolName      = 'PDF Editor';
$toolIcon      = 'fa-pen';
$toolColor     = 'tc-teal';
$toolDesc      = 'Add text overlays to every page of a PDF. Specify text content and position.';
$allowedExt    = ['.pdf'];
$allowedTypes  = ALLOWED_PDF_MIME;
$allowMultiple = false;
$maxFiles      = 1;
$extraOptions  = function () {
    ?>
    <label class="form-label" for="editor_text">Text to Add</label>
    <input type="text" class="form-control" id="editor_text" name="editor_text"
           placeholder="e.g. APPROVED" maxlength="120" required>
    <div class="row g-3 mt-1">
        <div class="col-6">
            <label class="form-label" for="x">X position (mm)</label>
            <input type="number" class="form-control" id="x" name="x" value="50" min="0" max="500">
        </div>
        <div class="col-6">
            <label class="form-label" for="y">Y position (mm)</label>
            <input type="number" class="form-control" id="y" name="y" value="50" min="0" max="500">
        </div>
    </div>
    <small class="form-text">The text is added to every page in red, bold, 14pt. Coordinates are in millimetres from the top-left.</small>
    <?php
};
require __DIR__ . '/../includes/tool_page.php';
