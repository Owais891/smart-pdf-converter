<?php
/**
 * Smart PDF - Dashboard layout wrapper
 *
 * Required before any dashboard page: require_once auth_check.php
 * Variables:
 *   $active        sidebar key
 *   $pageTitle     page title (also used in topbar)
 *   $pageBodyClass extra body classes
 *
 * Renders <html>, sidebar, topbar, opens .dash-content.
 * Close with: require __DIR__ . '/../includes/dashboard_footer.php';
 */
require_once __DIR__ . '/../includes/auth_check.php';

$active        = $active        ?? 'dashboard';
$pageTitle     = $pageTitle     ?? 'Dashboard';
$pageBodyClass = $pageBodyClass ?? 'dash-page';
$theme         = current_theme();

// Stat queries (used by dashboard/index.php)
$user = current_user();

// Pre-compute stats for sidebar badges if needed
$stats = ['conversions' => 0, 'uploads' => 0];
try {
    $stmt = db()->prepare('SELECT COUNT(*) FROM conversion_history WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $stats['conversions'] = (int)$stmt->fetchColumn();

    $stmt = db()->prepare('SELECT COUNT(*) FROM uploaded_files WHERE user_id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $stats['uploads'] = (int)$stmt->fetchColumn();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>%F0%9F%93%84</text></svg>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= (int)filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/responsive.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard.css">
</head>
<body class="<?= e($pageBodyClass) ?>">

<div class="dash-overlay" id="dashOverlay"></div>

<div class="dash-layout">
    <?php require __DIR__ . '/../includes/sidebar.php'; ?>

    <div class="dash-content">
        <div class="dash-topbar">
            <button type="button" class="sidebar-toggle d-lg-none" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="dash-title"><?= e($pageTitle) ?></div>
            <div class="ms-auto d-flex align-items-center gap-2">
                <button type="button" id="themeToggle" class="btn btn-icon" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <i class="fas <?= $theme === 'dark' ? 'fa-sun' : 'fa-moon' ?>"></i>
                </button>
                <a href="<?= APP_URL ?>/index.php" class="btn btn-ghost btn-sm">
                    <i class="fas fa-house me-1"></i> Home
                </a>
            </div>
        </div>

        <div class="container-fluid py-4">
