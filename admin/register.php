<?php
/**
 * Admin Registration Page
 * 
 * Allows users with @mimos.my email to create an admin account.
 * A verification email is sent after successful registration.
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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $fullName        = $_POST['full_name'] ?? '';
        $email           = $_POST['email'] ?? '';
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = AuthController::register($email, $password, $confirmPassword, $fullName);

        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Admin Registration';
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
            max-width: 480px;
            border: none;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        .domain-hint {
            color: #6b7280;
            font-size: 0.85rem;
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
                <p class="text-muted">Create Admin Account</p>
            </div>

            <?php if ($success): ?>
                <!-- Success State -->
                <div class="text-center">
                    <div class="mb-4">
                        <i class="bi bi-envelope-check text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Verification Email Sent!</h5>
                    <p class="text-muted">
                        We've sent a verification link to your email address. 
                        Please check your inbox and click the link to verify your account.
                    </p>
                    <?php if (($_ENV['MAIL_METHOD'] ?? 'log') === 'log'): ?>
                        <div class="alert alert-info small mt-3">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Dev Mode:</strong> Check <code>storage/mail_log.txt</code> for the verification link.
                        </div>
                    <?php endif; ?>
                    <a href="<?= url('admin/login.php') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Go to Login
                    </a>
                </div>
            <?php else: ?>
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" 
                                   class="form-control" 
                                   id="full_name" 
                                   name="full_name" 
                                   value="<?= e($_POST['full_name'] ?? '') ?>"
                                   placeholder="Enter your full name"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   value="<?= e($_POST['email'] ?? '') ?>"
                                   placeholder="your.name@mimos.my"
                                   required>
                        </div>
                        <div class="domain-hint mt-1">
                            <i class="bi bi-shield-check me-1"></i>Only <strong>@<?= e(ADMIN_EMAIL_DOMAIN) ?></strong> email addresses are allowed
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimum 8 characters"
                                   minlength="8"
                                   required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Re-enter your password"
                                   minlength="8"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>

                <!-- Login Link -->
                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="<?= url('admin/login.php') ?>" class="text-decoration-none fw-semibold ms-1">
                        Sign In
                    </a>
                </div>
            <?php endif; ?>

            <hr class="my-3">

            <!-- Back Link -->
            <div class="text-center">
                <a href="<?= url('/') ?>" class="text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Back to Home
                </a>
            </div>
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
