<?php
/**
 * Smart PDF - User Login
 */
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    redirect(APP_URL . '/dashboard/index.php');
}

$errors = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $old['email']    = trim($_POST['email'] ?? '');
    $password        = $_POST['password'] ?? '';
    $remember        = !empty($_POST['remember']);

    if (!is_valid_email($old['email'])) {
        $errors['email'] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 1) {
        $errors['password'] = 'Please enter your password.';
    }

    if (!$errors) {
        try {
            $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$old['email']]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $errors['general'] = 'Invalid email or password.';
            } elseif ((int)$user['is_active'] !== 1) {
                $errors['general'] = 'Your account has been disabled. Please contact support.';
            } else {
                login_user((int)$user['id'], $remember);
                flash('success', 'Welcome back, ' . e($user['full_name']) . '!');
                $next = $_SESSION['redirect_after_login'] ?? (APP_URL . '/dashboard/index.php');
                unset($_SESSION['redirect_after_login']);
                redirect($next);
            }
        } catch (Throwable $e) {
            error_log('Login error: ' . $e->getMessage());
            $errors['general'] = 'Could not reach the database. Please try again later.';
        }
    }
}

// Save redirect target if user was bounced from a protected page
if (!empty($_GET['next'])) {
    $_SESSION['redirect_after_login'] = $_GET['next'];
}

$pageTitle = 'Login to Your Account';
$pageDescription = 'Sign in to Smart PDF to access your dashboard and conversion history.';
$bodyClass = 'auth-page';
require __DIR__ . '/../includes/header.php';
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="form-card">
                <div class="form-title">
                    <div class="brand-logo mb-3 mx-auto"><img src="<?= APP_URL ?>/assets/img/smartpdf-logo.svg" alt="Smart PDF logo"></div>
                    <h2>Welcome back</h2>
                    <p>Login to continue to your Smart PDF dashboard.</p>
                </div>

                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-circle-exclamation me-2"></i><?= e($errors['general']) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                               id="email" name="email" value="<?= e($old['email']) ?>"
                               placeholder="you@example.com" required autofocus>
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback"><?= e($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <label class="form-label" for="password">Password</label>
                            <a href="<?= APP_URL ?>/auth/forgot_password.php" class="small">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" placeholder="Your password" required>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="password">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback d-block"><?= e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Keep me logged in for 30 days</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-right-to-bracket me-2"></i>Login
                    </button>

                    <div class="auth-divider">or continue with</div>
                    <div>
                        <a href="<?= APP_URL ?>/auth/social_login.php?provider=google" class="social-auth-btn w-100 text-center d-block">
                            <i class="fab fa-google me-1 text-danger"></i> Google
                        </a>
                    </div>

                    <p class="text-center text-muted mt-3 mb-0">
                        Don't have an account?
                        <a href="<?= APP_URL ?>/auth/register.php">Sign up free</a>
                    </p>
                </form>

                <div class="alert alert-info mt-3 mb-0 small">
                    <i class="fas fa-circle-info me-1"></i>
                    <strong>Demo admin:</strong> admin@smartpdf.local / Admin@12345
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-toggle-password');
        const input = document.getElementById(id);
        if (!input) return;
        const isPwd = input.type === 'password';
        input.type = isPwd ? 'text' : 'password';
        btn.querySelector('i').className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
