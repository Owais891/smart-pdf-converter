<?php
/**
 * Smart PDF - Admin: Run cleanup
 *
 * Manually triggers the file cleanup routine and reports stats.
 */
$active = 'admin';
$pageTitle = 'File Cleanup';
require __DIR__ . '/../includes/dashboard_header.php';
require_admin();

// Run cleanup
$beforeUploads = 0;
$beforeProcessed = 0;
$beforeTemp = 0;

foreach ([UPLOAD_DIR, PROCESSED_DIR, TEMP_DIR] as $dir) {
    if (!is_dir($dir)) continue;
    foreach ((array)glob($dir . '*') as $f) {
        if (is_file($f)) {
            $beforeUploads += (strpos($dir, 'uploads') !== false) ? 1 : 0;
            $beforeProcessed += (strpos($dir, 'processed') !== false) ? 1 : 0;
            $beforeTemp += (strpos($dir, 'temp') !== false) ? 1 : 0;
        }
    }
}

cleanup_temp_files();

$afterUploads = 0;
$afterProcessed = 0;
$afterTemp = 0;
foreach ([UPLOAD_DIR, PROCESSED_DIR, TEMP_DIR] as $dir) {
    if (!is_dir($dir)) continue;
    foreach ((array)glob($dir . '*') as $f) {
        if (is_file($f)) {
            $afterUploads += (strpos($dir, 'uploads') !== false) ? 1 : 0;
            $afterProcessed += (strpos($dir, 'processed') !== false) ? 1 : 0;
            $afterTemp += (strpos($dir, 'temp') !== false) ? 1 : 0;
        }
    }
}

$deletedUploads = $beforeUploads - $afterUploads;
$deletedProcessed = $beforeProcessed - $afterProcessed;
$deletedTemp = $beforeTemp - $afterTemp;
$totalDeleted = $deletedUploads + $deletedProcessed + $deletedTemp;

flash('success', "Cleanup complete. $totalDeleted expired file(s) deleted.");
?>

<div class="dash-page-title">File Cleanup</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="dash-card text-center">
            <div class="stat-icon sc-red mx-auto mb-3"><i class="fas fa-upload"></i></div>
            <h3 class="fw-bold mb-0"><?= $deletedUploads ?></h3>
            <p class="text-muted mb-0">Expired uploads deleted</p>
            <p class="small text-muted mt-2 mb-0"><?= $afterUploads ?> file(s) remaining</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-card text-center">
            <div class="stat-icon sc-green mx-auto mb-3"><i class="fas fa-file-export"></i></div>
            <h3 class="fw-bold mb-0"><?= $deletedProcessed ?></h3>
            <p class="text-muted mb-0">Expired processed files deleted</p>
            <p class="small text-muted mt-2 mb-0"><?= $afterProcessed ?> file(s) remaining</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dash-card text-center">
            <div class="stat-icon sc-orange mx-auto mb-3"><i class="fas fa-trash"></i></div>
            <h3 class="fw-bold mb-0"><?= $deletedTemp ?></h3>
            <p class="text-muted mb-0">Temp files deleted</p>
            <p class="small text-muted mt-2 mb-0"><?= $afterTemp ?> file(s) remaining</p>
        </div>
    </div>
</div>

<div class="dash-card">
    <h5 class="fw-bold mb-2">How Cleanup Works</h5>
    <p class="mb-2">Smart PDF automatically deletes files older than <strong>1 hour</strong> from the
        <code>storage/uploads/</code>, <code>storage/processed/</code> and <code>storage/temp/</code>
        directories.</p>
    <p class="mb-2">This manual cleanup page lets administrators run the routine on demand. The
        automatic cleanup runs roughly once every 50 page requests.</p>
    <p class="mb-0">Expired rows in the <code>uploaded_files</code> table are also pruned, and old
        "success" rows in <code>conversion_history</code> are marked as "expired".</p>
</div>

<div class="text-center mt-3">
    <a href="<?= APP_URL ?>/admin/cleanup.php" class="btn btn-primary">
        <i class="fas fa-arrows-rotate me-1"></i> Run Cleanup Again
    </a>
    <a href="<?= APP_URL ?>/admin/index.php" class="btn btn-ghost">Back to Admin</a>
</div>

<?php require __DIR__ . '/../includes/dashboard_footer.php'; ?>
