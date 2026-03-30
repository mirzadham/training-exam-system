<?php
/**
 * Forgot Password Page
 * 
 * Allows admins to request a password reset link via email.
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(url('admin/'));
}

// Handle form submission
$error = null;
$success = false;
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $email = $_POST['email'] ?? '';

        $result = AuthController::forgotPassword($email);

        if ($result['success']) {
            $success = true;
            $successMessage = $result['message'] ?? 'Password reset link sent.';
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Forgot Password';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — <?= e(APP_NAME) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" 
          rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">

    <style>
        .login-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="card login-card">
        <div class="card-body p-4 p-md-5">
            <!-- Header -->
            <div class="text-center mb-4">
                <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" style="width: 240px; height: 80px; object-fit: cover; object-position: center;" class="mb-3 rounded">
                <p class="text-muted">Reset Your Password</p>
            </div>

            <?php if ($success): ?>
                <!-- Success State -->
                <div class="text-center">
                    <div class="mb-4">
                        <i class="bi bi-envelope-check text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Check Your Email</h5>
                    <p class="text-muted"><?= e($successMessage) ?></p>
                    <?php if (($_ENV['MAIL_METHOD'] ?? 'log') === 'log'): ?>
                        <div class="alert alert-info small mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Dev Mode:</strong> Check <code>storage/mail_log.txt</code> for the reset link.
                        </div>
                    <?php endif; ?>
                    <a href="<?= url('admin/login.php') ?>" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-arrow-left me-2"></i>Back to Login
                    </a>
                </div>
            <?php else: ?>
                <!-- Info Message -->
                <div class="alert alert-info small" role="alert">
                    <i class="bi bi-info-circle me-1"></i>
                    Enter your email address and we'll send you a link to reset your password.
                </div>

                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Forgot Password Form -->
                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= e($_POST['email'] ?? '') ?>"
                                   placeholder="Enter your @mimos.my email"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-send me-2"></i>Send Reset Link
                    </button>
                </form>

                <!-- Back to Login -->
                <div class="text-center mt-4">
                    <a href="<?= url('admin/login.php') ?>" class="text-muted text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        crossorigin="anonymous"></script>

<!-- Custom JS -->
<script src="<?= asset('js/app.js') ?>?v=<?= filemtime(__DIR__ . '/../assets/js/app.js') ?>"></script>
</body>
</html>
