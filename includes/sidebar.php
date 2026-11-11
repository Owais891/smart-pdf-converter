<?php
/**
 * Smart PDF - Dashboard Sidebar
 * Pass $active to highlight the current item.
 */
$active = $active ?? 'dashboard';
$items = [
    'dashboard' => ['fa-gauge',            'Overview',        '/dashboard/index.php'],
    'history'   => ['fa-clock-rotate-left','Conversion History','/dashboard/history.php'],
    'profile'   => ['fa-user',             'Profile',         '/dashboard/profile.php'],
    'settings'  => ['fa-gear',             'Settings',        '/dashboard/settings.php'],
    'tools'     => ['fa-toolbox',          'All Tools',       '/tools/index.php'],
    'contact'   => ['fa-envelope',         'Contact Support', '/pages/contact.php'],
];
$u = current_user();
?>
<aside class="dashboard-sidebar" id="dashSidebar">
    <div class="sidebar-header">
        <a href="<?= APP_URL ?>/index.php" class="d-flex align-items-center gap-2">
            <span class="brand-logo"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt=""></span>
            <span class="brand-text">Smart<span class="brand-accent">PDF</span></span>
        </a>
    </div>

    <div class="sidebar-user">
        <div class="avatar"><?= strtoupper(substr($u['full_name'] ?? 'U', 0, 1)) ?></div>
        <div class="sidebar-user-info">
            <div class="fw-semibold"><?= e($u['full_name'] ?? '') ?></div>
            <div class="sidebar-user-email"><?= e($u['email'] ?? '') ?></div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($items as $key => [$icon, $label, $path]): ?>
            <a href="<?= APP_URL . $path ?>" class="sidebar-link <?= $active === $key ? 'is-active' : '' ?>">
                <i class="fas <?= $icon ?>"></i>
                <span><?= $label ?></span>
            </a>
        <?php endforeach; ?>

        <?php if (($u['role'] ?? '') === 'admin'): ?>
            <a href="<?= APP_URL ?>/admin/index.php" class="sidebar-link sidebar-link-admin">
                <i class="fas fa-shield-halved"></i>
                <span>Admin Panel</span>
            </a>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= APP_URL ?>/auth/logout.php" class="sidebar-link sidebar-link-logout">
            <i class="fas fa-right-from-bracket"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>
