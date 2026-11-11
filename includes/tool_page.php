<?php
/**
 * Smart PDF - Tool page helper
 * ------------------------------------------------------------------
 * Provides a consistent layout for every tool page. To use:
 *
 *   $toolSlug    = 'merge_pdf';
 *   $toolName    = 'Merge PDF';
 *   $toolIcon    = 'fa-object-group';
 *   $toolColor   = 'tc-red';
 *   $toolDesc    = 'Combine multiple PDFs into one document.';
 *   $allowedExt  = ['.pdf'];
 *   $allowedTypes= ALLOWED_PDF_MIME;
 *   $allowMultiple = true;
 *   $maxFiles    = 20;
 *   $extraOptions = function () { ... echo HTML ... };
 *   $extraJs     = "let cfg = { fromPage: 1 };";   // optional
 *   require __DIR__ . '/../includes/tool_page.php';
 *
 * The helper renders the page header, uploader, progress, result and
 * error placeholders, and wires up the JS ToolUpload instance. It
 * expects /api/upload.php and /api/process.php to exist.
 */
require_once __DIR__ . '/../includes/functions.php';

$toolSlug      = $toolSlug      ?? '';
$toolName      = $toolName      ?? 'PDF Tool';
$toolIcon      = $toolIcon      ?? 'fa-file-pdf';
$toolColor     = $toolColor     ?? 'tc-red';
$toolDesc      = $toolDesc      ?? '';
$allowedExt    = $allowedExt    ?? ['.pdf'];
$allowedTypes  = $allowedTypes  ?? ALLOWED_PDF_MIME;
$allowMultiple = $allowMultiple ?? false;
$maxFiles      = $maxFiles      ?? 1;
$extraOptions  = $extraOptions  ?? null;   // callable echoing extra HTML
$extraJs       = $extraJs       ?? '';     // extra JS string for ToolUpload config

$activePage = 'tools';
$pageTitle  = $toolName . ' - Smart PDF';
$pageDescription = $toolDesc;
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav>
            <ol class="breadcrumb breadcrumb-smart">
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= APP_URL ?>/tools/index.php">All Tools</a></li>
                <li class="breadcrumb-item active"><?= e($toolName) ?></li>
            </ol>
        </nav>
        <h1><?= e($toolName) ?></h1>
    </div>
</section>

<section class="tool-page">
    <div class="container">
        <div class="tool-card-frame">

            <!-- Drop zone -->
            <div class="uploader" id="dropZone">
                <div class="uploader-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                <h4>Drop your file<?= $allowMultiple ? 's' : '' ?> here</h4>
                <p class="text-muted mb-3">or click to browse &middot; <?= implode(', ', $allowedExt) ?> supported</p>
                <button type="button" class="btn btn-primary" id="browseBtn">
                    <i class="fas fa-folder-open me-1"></i> Select File<?= $allowMultiple ? 's' : '' ?>
                </button>
                <input type="file" id="fileInput" class="d-none"
                       accept="<?= implode(',', $allowedExt) ?>"
                       <?= $allowMultiple ? 'multiple' : '' ?>>
                <div class="hero-badges">
                    <span class="hero-badge"><i class="fas fa-lock"></i> Secure</span>
                    <span class="hero-badge"><i class="fas fa-trash-can"></i> Auto-deleted in 1h</span>
                    <span class="hero-badge"><i class="fas fa-bolt"></i> Fast processing</span>
                </div>
            </div>

            <!-- File list -->
            <div class="uploader-files" id="fileList"></div>

            <!-- Tool-specific options -->
            <?php if (is_callable($extraOptions)): ?>
                <div class="tool-options mt-3">
                    <?php $extraOptions(); ?>
                </div>
            <?php endif; ?>

            <!-- Process button -->
            <div class="tool-action-bar text-center" id="toolActionBar">
                <button type="button" class="btn btn-primary btn-lg" id="processBtn">
                    <i class="fas fa-play me-2"></i><?= e($toolName) ?>
                </button>
            </div>

            <!-- Progress -->
            <div class="tool-progress" id="toolProgress">
                <div class="progress"><div class="progress-bar" style="width:0%"></div></div>
                <div class="progress-text">Starting...</div>
            </div>

            <!-- Error -->
            <div class="tool-error" id="toolError"></div>

            <!-- Result -->
            <div class="tool-result" id="toolResult"></div>

            <!-- Start over link -->
            <div class="text-center mt-4">
                <a href="<?= APP_URL ?>/tools/index.php" class="text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Back to all tools
                </a>
            </div>
        </div>

        <!-- SEO / help text -->
        <div class="row g-4 mt-4">
            <div class="col-lg-8 mx-auto">
                <div class="dash-card">
                    <h5 class="fw-bold mb-2">How to use the <?= e($toolName) ?> tool</h5>
                    <ol class="mb-0">
                        <li>Click <strong>Select File<?= $allowMultiple ? 's' : '' ?></strong> or drag &amp; drop your file<?= $allowMultiple ? 's' : '' ?> onto the upload area above.</li>
                        <li>Adjust any options if shown.</li>
                        <li>Press the <strong><?= e($toolName) ?></strong> button and wait for processing to finish.</li>
                        <li>Download your result file. Your upload is automatically removed from the server within one hour.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="<?= APP_URL ?>/assets/js/upload.js"></script>
<script>
(function () {
    const cfg = {
        dropZone: '#dropZone',
        input: '#fileInput',
        list: '#fileList',
        btn: '#processBtn',
        browseBtn: '#browseBtn',
        uploadUrl: <?= json_encode(APP_URL . '/api/upload.php') ?>,
        processUrl: <?= json_encode(APP_URL . '/api/process.php') ?>,
        toolName: <?= json_encode($toolSlug) ?>,
        allowedTypes: <?= json_encode($allowedTypes) ?>,
        allowedExt: <?= json_encode($allowedExt) ?>,
        maxFiles: <?= (int)$maxFiles ?>,
        allowMultiple: <?= $allowMultiple ? 'true' : 'false' ?>,
        maxUploadSize: <?= (int)MAX_UPLOAD_SIZE ?>,
        extraFields: function () {
            const out = {};
            document.querySelectorAll('.tool-options [name]').forEach(el => {
                if (el.type === 'checkbox') {
                    if (el.checked) out[el.name] = '1';
                } else if (el.type === 'radio') {
                    if (el.checked) out[el.name] = el.value;
                } else if (el.value !== '') {
                    out[el.name] = el.value;
                }
            });
            return out;
        }
    };
    <?= $extraJs ?>
    window.toolUpload = new ToolUpload(cfg);
})();
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
