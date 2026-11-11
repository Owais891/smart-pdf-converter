<?php
/**
 * Smart PDF - About Us
 */
require_once __DIR__ . '/../includes/functions.php';

$activePage = 'about';
$pageTitle = 'About Us';
$pageDescription = 'Learn about Smart PDF - our mission, our story, and the team behind the platform.';
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav><ol class="breadcrumb breadcrumb-smart">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">About Us</li>
        </ol></nav>
        <h1>About Smart PDF</h1>
        <p>Free, secure and powerful PDF tools for everyone.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-8">
                <h2>Our Mission</h2>
                <p class="lead">
                    Smart PDF was created with one simple goal: to make professional-grade PDF tools
                    accessible to everyone - students, professionals, small businesses - without paywalls,
                    watermarks, or complicated software installs.
                </p>
                <p>
                    We believe document management shouldn't require expensive subscriptions or
                    heavyweight desktop software. By combining the power of open-source PHP libraries
                    (TCPDF, FPDI, FPDF) with a clean, modern interface, Smart PDF delivers 18 essential
                    PDF tools right in your browser.
                </p>

                <h2 class="mt-5">Our Story</h2>
                <p>
                    Smart PDF began as a university project to demonstrate how PHP and MySQL could be
                    used to build a real, production-quality SaaS product. The codebase is intentionally
                    kept clean and beginner-friendly so that students learning PHP can read it, understand
                    it, and extend it with their own features.
                </p>
                <p>
                    Today, Smart PDF is a fully functional PDF toolkit - conversion, merging, splitting,
                    compression, password protection, watermarking, page numbering and more - all driven
                    by real PDF processing libraries, not static demos.
                </p>

                <h2 class="mt-5">Our Values</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-shield-halved"></i></div>
                            <h5>Privacy First</h5>
                            <p>Your files never leave your server. Everything is auto-deleted within one hour.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-code"></i></div>
                            <h5>Open Source Stack</h5>
                            <p>Built on PHP, MySQL and trusted PDF libraries. Fully auditable.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-gift"></i></div>
                            <h5>Free Forever</h5>
                            <p>No hidden fees, no premium tiers for core features, no watermarks.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="feature-card h-100">
                            <div class="feature-icon"><i class="fas fa-graduation-cap"></i></div>
                            <h5>Educational</h5>
                            <p>Code is well-commented and structured for learning, not just usage.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="dash-card">
                    <h5 class="fw-bold mb-3">At a Glance</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Founded</span><strong>2025</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">PDF Tools</span><strong>18</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">Tech Stack</span><strong>PHP &amp; MySQL</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2 border-bottom">
                            <span class="text-muted">License</span><strong>MIT</strong>
                        </li>
                        <li class="d-flex justify-content-between py-2">
                            <span class="text-muted">Price</span><strong>Free</strong>
                        </li>
                    </ul>
                </div>

                <div class="dash-card">
                    <h5 class="fw-bold mb-2">Contact</h5>
                    <p class="mb-2 text-muted">Have a question or suggestion?</p>
                    <a href="<?= APP_URL ?>/pages/contact.php" class="btn btn-primary w-100">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section bg-brand-soft">
    <div class="container">
        <div class="cta-section">
            <h2>Ready to give Smart PDF a try?</h2>
            <p>All 18 PDF tools are completely free - no signup required.</p>
            <a href="<?= APP_URL ?>/tools/index.php" class="btn btn-lg">
                <i class="fas fa-toolbox me-2"></i>Explore All Tools
            </a>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
