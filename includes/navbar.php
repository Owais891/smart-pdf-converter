<?php
/**
 * Smart PDF - Top Navigation Bar
 * Required vars: $currentPage (optional)
 */
$currentPage = $currentPage ?? '';
$user = current_user();
$theme = current_theme();
$toolsMenu = [
    'Convert' => [
        ['pdf_to_word', 'PDF to Word'],
        ['word_to_pdf', 'Word to PDF'],
        ['pdf_to_excel', 'PDF to Excel'],
        ['excel_to_pdf', 'Excel to PDF'],
        ['pdf_to_ppt',  'PDF to PowerPoint'],
        ['ppt_to_pdf',  'PowerPoint to PDF'],
        ['pdf_to_jpg',  'PDF to JPG'],
        ['jpg_to_pdf',  'JPG to PDF'],
    ],
    'Organize' => [
        ['merge_pdf',    'Merge PDF'],
        ['split_pdf',    'Split PDF'],
        ['rotate_pdf',   'Rotate PDF'],
        ['organize_pdf', 'Organize PDF'],
    ],
    'Optimize' => [
        ['compress_pdf', 'Compress PDF'],
    ],
    'Edit' => [
        ['watermark_pdf',  'Add Watermark'],
        ['page_numbers',   'Page Numbers'],
        ['pdf_editor',     'PDF Editor'],
    ],
    'Security' => [
        ['protect_pdf', 'Protect PDF'],
        ['unlock_pdf',  'Unlock PDF'],
    ],
];
?>
<nav class="navbar navbar-expand-lg navbar-smart fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= APP_URL ?>/index.php">
            <span class="brand-logo">
                <img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt="">
            </span>
            <span class="brand-text">Smart<span class="brand-accent">PDF</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'home' ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php">Home</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= $currentPage === 'tools' ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        All Tools
                    </a>
                    <div class="dropdown-menu dropdown-menu-wide p-3">
                        <?php foreach ($toolsMenu as $category => $items): ?>
                            <div class="dropdown-group">
                                <h6 class="dropdown-group-title"><?= $category ?></h6>
                                <?php foreach ($items as [$slug, $label]): ?>
                                    <a class="dropdown-item d-flex align-items-center gap-2" href="<?= APP_URL ?>/tools/<?= $slug ?>.php">
                                        <i class="fas <?= tool_icon($slug) ?> text-primary"></i>
                                        <span><?= $label ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/about.php">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>" href="<?= APP_URL ?>/pages/contact.php">Contact</a>
                </li>
            </ul>

            <ul class="navbar-nav align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <button type="button" id="themeToggle" class="btn btn-icon" title="Toggle dark mode" aria-label="Toggle dark mode">
                        <i class="fas fa-moon"></i>
                    </button>
                </li>

                <?php if ($user): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-sm"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
                            <span class="d-none d-lg-inline"><?= e($user['full_name']) ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/dashboard/index.php"><i class="fas fa-gauge me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/dashboard/history.php"><i class="fas fa-clock-rotate-left me-2"></i> Conversion History</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/dashboard/profile.php"><i class="fas fa-user me-2"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="<?= APP_URL ?>/dashboard/settings.php"><i class="fas fa-gear me-2"></i> Settings</a></li>
                            <?php if (($user['role'] ?? '') === 'admin'): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?= APP_URL ?>/admin/index.php"><i class="fas fa-shield-halved me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= APP_URL ?>/auth/logout.php"><i class="fas fa-right-from-bracket me-2"></i> Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-ghost" href="<?= APP_URL ?>/auth/login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary-soft" href="<?= APP_URL ?>/auth/register.php">Sign Up</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php
// tool_icon() is defined in includes/functions.php and is always available
// after smartpdf_bootstrap() runs (which happens automatically when
// functions.php is loaded).
