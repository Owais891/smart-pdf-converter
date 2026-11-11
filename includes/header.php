<?php
/**
 * Smart PDF - Shared HTML <head> + opening layout.
 *
 * Variables you may pass before including:
 *   $pageTitle     string  Tab / page title (default: APP_NAME)
 *   $pageDescription string  Meta description
 *   $activePage    string  Highlights nav item
 *   $bodyClass     string  Extra classes for <body>
 *   $withHero      bool    Adds hero spacing
 *   $extraCss      array   Extra <link> stylesheets
 */
require_once __DIR__ . '/functions.php';

$pageTitle       = $pageTitle       ?? APP_NAME;
$pageDescription = $pageDescription ?? APP_TAGLINE;
$activePage      = $activePage      ?? '';
$bodyClass       = $bodyClass       ?? '';
$extraCss        = $extraCss        ?? [];
$theme           = current_theme();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= e($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="#dc3545">
    <title><?= e($pageTitle) ?> &middot; <?= e(APP_NAME) ?></title>

    <link rel="icon" type="image/svg+xml" href="<?= APP_URL ?>/assets/img/smartpdf-logo.svg">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css?v=<?= (int)filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/responsive.css?v=<?= (int)filemtime(__DIR__ . '/../assets/css/responsive.css') ?>">
    <?php
    $__script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($__script_name, '/dashboard/') === 0 || strpos($__script_name, '/admin/') === 0): ?>
        <link rel="stylesheet" href="<?= APP_URL ?>/assets/css/dashboard.css">
    <?php endif; ?>
    <?php foreach ($extraCss as $css): ?>
        <link rel="stylesheet" href="<?= e($css) ?>">
    <?php endforeach; ?>
</head>
<body class="<?= e($bodyClass) ?> <?= !empty($withHero) ? 'has-hero' : '' ?>">

<!-- Page loader -->
<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
</div>

<!-- Flash messages -->
<?php if ($flashes = get_flashes()): ?>
    <div class="flash-container" id="flashContainer">
        <?php foreach ($flashes as $f): ?>
            <div class="alert alert-<?= $f['type'] === 'error' ? 'danger' : $f['type'] ?> alert-dismissible fade show rounded-pill shadow-sm" role="alert">
                <i class="fas <?= [
                    'success' => 'fa-circle-check',
                    'error'   => 'fa-circle-exclamation',
                    'danger'  => 'fa-circle-exclamation',
                    'info'    => 'fa-circle-info',
                    'warning' => 'fa-triangle-exclamation',
                ][$f['type'] ?? 'info'] ?? 'fa-circle-info' ?> me-2"></i>
                <?= e($f['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/navbar.php'; ?>

<main class="page-main">
