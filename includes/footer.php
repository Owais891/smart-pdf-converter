<?php
/**
 * Smart PDF - Footer
 */
$year = date('Y');
?>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand d-flex align-items-center gap-2 mb-3">
                    <span class="brand-logo"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt=""></span>
                    <span class="brand-text h5 mb-0">Smart<span class="brand-accent">PDF</span></span>
                </div>
                <p class="footer-text">
                    Smart PDF is an all-in-one online PDF toolkit. Convert, edit, merge,
                    split, compress, and protect PDF files - quickly, securely and free.
                </p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="GitHub"><i class="fab fa-github"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Convert</h6>
                <ul class="footer-links">
                    <li><a href="<?= APP_URL ?>/tools/pdf_to_word.php">PDF to Word</a></li>
                    <li><a href="<?= APP_URL ?>/tools/pdf_to_excel.php">PDF to Excel</a></li>
                    <li><a href="<?= APP_URL ?>/tools/pdf_to_ppt.php">PDF to PowerPoint</a></li>
                    <li><a href="<?= APP_URL ?>/tools/pdf_to_jpg.php">PDF to JPG</a></li>
                    <li><a href="<?= APP_URL ?>/tools/jpg_to_pdf.php">JPG to PDF</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Organize</h6>
                <ul class="footer-links">
                    <li><a href="<?= APP_URL ?>/tools/merge_pdf.php">Merge PDF</a></li>
                    <li><a href="<?= APP_URL ?>/tools/split_pdf.php">Split PDF</a></li>
                    <li><a href="<?= APP_URL ?>/tools/rotate_pdf.php">Rotate PDF</a></li>
                    <li><a href="<?= APP_URL ?>/tools/organize_pdf.php">Organize PDF</a></li>
                    <li><a href="<?= APP_URL ?>/tools/compress_pdf.php">Compress PDF</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Company</h6>
                <ul class="footer-links">
                    <li><a href="<?= APP_URL ?>/pages/about.php">About Us</a></li>
                    <li><a href="<?= APP_URL ?>/pages/contact.php">Contact</a></li>
                    <li><a href="<?= APP_URL ?>/pages/privacy.php">Privacy Policy</a></li>
                    <li><a href="<?= APP_URL ?>/pages/terms.php">Terms &amp; Conditions</a></li>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6">
                <h6 class="footer-title">Account</h6>
                <ul class="footer-links">
                    <?php if (is_logged_in()): ?>
                        <li><a href="<?= APP_URL ?>/dashboard/index.php">Dashboard</a></li>
                        <li><a href="<?= APP_URL ?>/dashboard/history.php">History</a></li>
                        <li><a href="<?= APP_URL ?>/dashboard/profile.php">Profile</a></li>
                        <li><a href="<?= APP_URL ?>/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="<?= APP_URL ?>/auth/login.php">Login</a></li>
                        <li><a href="<?= APP_URL ?>/auth/register.php">Sign Up</a></li>
                        <li><a href="<?= APP_URL ?>/auth/forgot_password.php">Forgot Password</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <hr class="footer-divider">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <p class="mb-0 footer-text small">
                &copy; <?= $year ?> <?= e(APP_NAME) ?>. All rights reserved. Built with PHP &amp; MySQL.
            </p>
            <p class="mb-0 footer-text small">
                <i class="fas fa-shield-halved me-1 text-success"></i>
                Files are processed securely &amp; auto-deleted within 1 hour.
            </p>
        </div>
    </div>
</footer>

<!-- Back to top -->
<button id="backToTop" class="back-to-top" aria-label="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- App JS -->
<script src="<?= APP_URL ?>/assets/js/main.js"></script>
<?php
$__script_name_footer = $_SERVER['SCRIPT_NAME'] ?? '';
if (strpos($__script_name_footer, '/dashboard/') === 0):
?>
    <script src="<?= APP_URL ?>/assets/js/dashboard.js"></script>
<?php endif; ?>
<script>
window.SMART_PDF = {
    appUrl: <?= json_encode(APP_URL) ?>,
    csrfToken: <?= json_encode(csrf_token()) ?>,
    maxUploadSize: <?= (int)MAX_UPLOAD_SIZE ?>
};
</script>
</body>
</html>
