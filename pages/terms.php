<?php
/**
 * Smart PDF - Terms and Conditions
 */
require_once __DIR__ . '/../includes/functions.php';

$activePage = 'terms';
$pageTitle = 'Terms and Conditions';
$pageDescription = 'The terms governing your use of Smart PDF.';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav><ol class="breadcrumb breadcrumb-smart">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Terms &amp; Conditions</li>
        </ol></nav>
        <h1>Terms &amp; Conditions</h1>
        <p>Last updated: <?= date('F j, Y') ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="dash-card">
                    <h2 class="h5 fw-bold">1. Acceptance of Terms</h2>
                    <p>
                        By accessing or using Smart PDF ("the Service"), you agree to be bound by these
                        Terms and Conditions. If you do not agree, please discontinue use of the Service.
                    </p>

                    <h2 class="h5 fw-bold mt-4">2. Description of Service</h2>
                    <p>
                        Smart PDF provides free online tools to convert, merge, split, compress, rotate,
                        protect, unlock, watermark, add page numbers to, organise and edit PDF documents.
                        The Service is provided "as is" without warranties of any kind.
                    </p>

                    <h2 class="h5 fw-bold mt-4">3. Acceptable Use</h2>
                    <p>You agree NOT to use Smart PDF to:</p>
                    <ul>
                        <li>Upload or process documents that violate any law or third-party right.</li>
                        <li>Attempt to crack passwords on PDFs you do not own or have rights to.</li>
                        <li>Upload malware, viruses or any malicious content.</li>
                        <li>Attempt to overload, hack or reverse-engineer the Service.</li>
                        <li>Use automated scripts to abuse the upload endpoints.</li>
                    </ul>
                    <p>
                        Violations may result in account suspension and permanent IP blocking.
                    </p>

                    <h2 class="h5 fw-bold mt-4">4. File Handling &amp; Retention</h2>
                    <p>
                        All uploaded files are automatically deleted from the server within one hour of
                        upload. Smart PDF does not guarantee permanent storage of any file. You are
                        responsible for downloading your processed files before they expire.
                    </p>

                    <h2 class="h5 fw-bold mt-4">5. Accounts</h2>
                    <p>
                        Creating an account is optional for using the tools but required for history
                        tracking. You are responsible for safeguarding your password and for all activity
                        under your account. We use bcrypt password hashing and never store plaintext
                        passwords.
                    </p>

                    <h2 class="h5 fw-bold mt-4">6. Intellectual Property</h2>
                    <p>
                        The Smart PDF source code is released under the MIT License. The PDF processing
                        libraries (TCPDF, FPDI, FPDF) retain their respective licenses. You retain full
                        ownership of all documents you upload.
                    </p>

                    <h2 class="h5 fw-bold mt-4">7. Disclaimer of Warranties</h2>
                    <p>
                        The Service is provided "AS IS" and "AS AVAILABLE" without warranties of any kind,
                        express or implied, including but not limited to merchantability, fitness for a
                        particular purpose, or non-infringement. PDF conversions may occasionally produce
                        imperfect output due to source file complexity.
                    </p>

                    <h2 class="h5 fw-bold mt-4">8. Limitation of Liability</h2>
                    <p>
                        To the maximum extent permitted by law, Smart PDF shall not be liable for any
                        indirect, incidental, special or consequential damages, including loss of data,
                        profits or business opportunities, arising out of or related to the use of the
                        Service.
                    </p>

                    <h2 class="h5 fw-bold mt-4">9. Third-Party Tools</h2>
                    <p>
                        Smart PDF relies on third-party open-source software including LibreOffice,
                        TCPDF, FPDI, FPDF, ImageMagick, Ghostscript and Poppler. We are not responsible
                        for defects or behaviours introduced by these dependencies.
                    </p>

                    <h2 class="h5 fw-bold mt-4">10. Changes to the Service</h2>
                    <p>
                        We reserve the right to modify, suspend or discontinue any part of the Service
                        at any time, with or without notice. We will not be liable to you for any
                        modification, suspension or discontinuance.
                    </p>

                    <h2 class="h5 fw-bold mt-4">11. Termination</h2>
                    <p>
                        We may terminate or suspend your account immediately, without prior notice, for
                        any violation of these Terms. You may close your account at any time by contacting
                        us at <?= e(APP_EMAIL) ?>.
                    </p>

                    <h2 class="h5 fw-bold mt-4">12. Governing Law</h2>
                    <p>
                        These Terms shall be governed by and construed in accordance with the laws of
                        Pakistan, without regard to its conflict of law provisions.
                    </p>

                    <h2 class="h5 fw-bold mt-4">13. Contact</h2>
                    <p>
                        Questions about these Terms? Contact us at <?= e(APP_EMAIL) ?> or through our
                        <a href="<?= APP_URL ?>/pages/contact.php">contact form</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
