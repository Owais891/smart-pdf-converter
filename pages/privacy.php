<?php
/**
 * Smart PDF - Privacy Policy
 */
require_once __DIR__ . '/../includes/functions.php';

$activePage = 'privacy';
$pageTitle = 'Privacy Policy';
$pageDescription = 'How Smart PDF handles, stores and protects your files and personal information.';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav><ol class="breadcrumb breadcrumb-smart">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Privacy Policy</li>
        </ol></nav>
        <h1>Privacy Policy</h1>
        <p>Last updated: <?= date('F j, Y') ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="dash-card">
                    <h2 class="h5 fw-bold">1. Introduction</h2>
                    <p>
                        Smart PDF ("we", "us", "our") is committed to protecting your privacy. This policy
                        explains what information we collect, how we use it, and the choices you have. By
                        using Smart PDF, you agree to the practices described below.
                    </p>

                    <h2 class="h5 fw-bold mt-4">2. Files You Upload</h2>
                    <p>
                        When you use our PDF tools, the files you upload are stored temporarily on the server
                        in the <code>storage/uploads/</code> and <code>storage/processed/</code> directories.
                        These files are:
                    </p>
                    <ul>
                        <li>Used only to perform the requested PDF operation.</li>
                        <li>Never shared with third parties.</li>
                        <li>Automatically deleted from disk within <strong>1 hour</strong> of upload.</li>
                        <li>Not indexed, archived or used for training any model.</li>
                    </ul>

                    <h2 class="h5 fw-bold mt-4">3. Personal Information</h2>
                    <p>
                        If you create an account, we store the following personal information in our MySQL
                        database (<code>smart_pdf_db</code>):
                    </p>
                    <ul>
                        <li><strong>Full name</strong> and <strong>email address</strong> (for identification).</li>
                        <li><strong>Password</strong> - stored as a one-way bcrypt hash via PHP's
                            <code>password_hash()</code>. We never see your plaintext password.</li>
                        <li><strong>Conversion history</strong> - tool name, original filename, status and timestamp.</li>
                        <li><strong>Settings</strong> - theme and language preferences.</li>
                    </ul>
                    <p>
                        We never sell, rent or share your personal information with marketers.
                    </p>

                    <h2 class="h5 fw-bold mt-4">4. Cookies &amp; Sessions</h2>
                    <p>
                        Smart PDF uses PHP sessions to keep you logged in. A session cookie is set in your
                        browser; it is marked <code>HttpOnly</code> and <code>SameSite=Lax</code> for
                        security. We also use <code>localStorage</code> in your browser to remember your
                        light/dark theme preference. No tracking or advertising cookies are used.
                    </p>

                    <h2 class="h5 fw-bold mt-4">5. Security</h2>
                    <p>We follow industry-standard security practices, including:</p>
                    <ul>
                        <li>PDO prepared statements for all database queries (SQL injection protection).</li>
                        <li>Output escaping with <code>htmlspecialchars()</code> (XSS protection).</li>
                        <li>CSRF tokens on every form submission.</li>
                        <li>Server-side MIME validation of uploaded files via <code>finfo</code>.</li>
                        <li>Hardened <code>.htaccess</code> rules blocking direct access to <code>/storage</code>,
                            <code>/config</code>, <code>/includes</code>, <code>/database</code> and
                            <code>/vendor</code> directories.</li>
                        <li>Automatic password hashing with bcrypt.</li>
                    </ul>

                    <h2 class="h5 fw-bold mt-4">6. Your Rights</h2>
                    <p>You may at any time:</p>
                    <ul>
                        <li>View and update your profile from the dashboard.</li>
                        <li>Delete individual conversion records or clear your entire history.</li>
                        <li>Request account deletion by contacting us at <?= e(APP_EMAIL) ?>.</li>
                    </ul>

                    <h2 class="h5 fw-bold mt-4">7. Contact Messages</h2>
                    <p>
                        When you submit the Contact Us form, we store your name, email, subject and message
                        in the <code>contact_messages</code> table so we can reply. These records are kept
                        until you request their deletion.
                    </p>

                    <h2 class="h5 fw-bold mt-4">8. Children's Privacy</h2>
                    <p>
                        Smart PDF is not directed at children under 13. We do not knowingly collect personal
                        information from children. If you believe a child has registered with us, please
                        contact us so we can remove the account.
                    </p>

                    <h2 class="h5 fw-bold mt-4">9. Changes to This Policy</h2>
                    <p>
                        We may update this Privacy Policy from time to time. Changes will be posted on this
                        page with an updated "Last updated" date. We encourage you to review this policy
                        periodically.
                    </p>

                    <h2 class="h5 fw-bold mt-4">10. Contact Us</h2>
                    <p>
                        If you have any questions about this Privacy Policy, please contact us:
                    </p>
                    <ul>
                        <li>Email: <?= e(APP_EMAIL) ?></li>
                        <li><a href="<?= APP_URL ?>/pages/contact.php">Contact form</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
