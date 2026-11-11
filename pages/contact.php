<?php
/**
 * Smart PDF - Contact Us
 */
require_once __DIR__ . '/../includes/functions.php';

$activePage = 'contact';
$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with the Smart PDF team for support, feedback or partnership enquiries.';

$errors = [];
$sent = false;
$old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['name']    = trim($_POST['name'] ?? '');
    $old['email']   = trim($_POST['email'] ?? '');
    $old['subject'] = trim($_POST['subject'] ?? '');
    $old['message'] = trim($_POST['message'] ?? '');

    if (mb_strlen($old['name']) < 2) {
        $errors['name'] = 'Please enter your name.';
    }
    if (!is_valid_email($old['email'])) {
        $errors['email'] = 'Please enter a valid email.';
    }
    if (mb_strlen($old['subject']) < 3) {
        $errors['subject'] = 'Subject is too short.';
    }
    if (mb_strlen($old['message']) < 10) {
        $errors['message'] = 'Message must be at least 10 characters.';
    }

    if (!$errors) {
        try {
            db()->prepare(
                'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)'
            )->execute([$old['name'], $old['email'], $old['subject'], $old['message']]);
            $sent = true;
            $old = ['name' => '', 'email' => '', 'subject' => '', 'message' => ''];
        } catch (Throwable $e) {
            $errors['general'] = 'Could not send your message. Please try again later.';
        }
    }
}

require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container">
        <nav><ol class="breadcrumb breadcrumb-smart">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item active">Contact Us</li>
        </ol></nav>
        <h1>Contact Us</h1>
        <p>Questions, feedback or partnership enquiries - we'd love to hear from you.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-7">
                <?php if ($sent): ?>
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-circle-check me-2"></i>
                        Thanks for reaching out! Your message has been received and we'll reply within 1-2 business days.
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger"><i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?></div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="name">Your Name</label>
                            <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                   id="name" name="name" value="<?= e($old['name']) ?>" required>
                            <?php if (isset($errors['name'])): ?><div class="invalid-feedback"><?= e($errors['name']) ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="email">Email</label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= e($old['email']) ?>" required>
                            <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>"
                               id="subject" name="subject" value="<?= e($old['subject']) ?>" required>
                        <?php if (isset($errors['subject'])): ?><div class="invalid-feedback"><?= e($errors['subject']) ?></div><?php endif; ?>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="message">Message</label>
                        <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>"
                                  id="message" name="message" rows="6" required><?= e($old['message']) ?></textarea>
                        <?php if (isset($errors['message'])): ?><div class="invalid-feedback"><?= e($errors['message']) ?></div><?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="fas fa-paper-plane me-2"></i>Send Message
                    </button>
                </form>
            </div>

            <div class="col-lg-5">
                <div class="dash-card mb-3">
                    <h5 class="fw-bold mb-3">Get in Touch</h5>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="feature-icon" style="margin:0"><i class="fas fa-envelope"></i></span>
                        <div>
                            <div class="fw-semibold">Email</div>
                            <div class="text-muted small"><?= e(APP_EMAIL) ?></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <span class="feature-icon" style="margin:0"><i class="fas fa-clock"></i></span>
                        <div>
                            <div class="fw-semibold">Response Time</div>
                            <div class="text-muted small">1-2 business days</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <span class="feature-icon" style="margin:0"><i class="fas fa-headset"></i></span>
                        <div>
                            <div class="fw-semibold">Support Hours</div>
                            <div class="text-muted small">Mon - Fri, 9 AM - 6 PM (PKT)</div>
                        </div>
                    </div>
                </div>

                <div class="dash-card">
                    <h5 class="fw-bold mb-2">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?= APP_URL ?>/tools/index.php">Browse All PDF Tools</a></li>
                        <li><a href="<?= APP_URL ?>/pages/about.php">About Smart PDF</a></li>
                        <li><a href="<?= APP_URL ?>/pages/privacy.php">Privacy Policy</a></li>
                        <li><a href="<?= APP_URL ?>/pages/terms.php">Terms &amp; Conditions</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
