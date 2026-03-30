<?php
/**
 * Reset Password Page
 * 
 * Allows admins to set a new password using a valid reset token.
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(url('admin/'));
}

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = null;
$success = false;
$tokenValid = false;

// Check if token is valid (on GET request)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($token)) {
    $admin = Admin::findByResetToken($token);
    $tokenValid = $admin !== null;
    if (!$tokenValid) {
        $error = 'This password reset link has expired or is invalid. Please request a new one.';
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
        $tokenValid = true; // Keep the form visible
    } else {
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $result = AuthController::resetPassword($token, $password, $confirmPassword);

        if ($result['success']) {
            $success = true;
        } else {
            $error = $result['error'];
            // Check if token is still valid to show form or error
            $admin = Admin::findByResetToken($token);
            $tokenValid = $admin !== null;
        }
    }
}

$pageTitle = 'Reset Password';
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
                <p class="text-muted">Set New Password</p>
            </div>

            <?php if ($success): ?>
                <!-- Success State -->
                <div class="text-center">
                    <div class="mb-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Password Reset Successful!</h5>
                    <p class="text-muted">
                        Your password has been updated successfully. You can now log in with your new password.
                    </p>
                    <a href="<?= url('admin/login.php') ?>" class="btn btn-primary mt-3 px-4 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </a>
                </div>
            <?php elseif ($tokenValid): ?>
                <!-- Error Message -->
                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Reset Password Form -->
                <form method="POST" action="" novalidate>
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Minimum 8 characters"
                                   minlength="8"
                                   required 
                                   autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" 
                                   class="form-control" 
                                   id="confirm_password" 
                                   name="confirm_password" 
                                   placeholder="Re-enter your new password"
                                   minlength="8"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2">
                        <i class="bi bi-check-lg me-2"></i>Reset Password
                    </button>
                </form>
            <?php else: ?>
                <!-- Invalid/Expired Token -->
                <div class="text-center">
                    <div class="mb-4">
                        <i class="bi bi-x-circle text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="fw-semibold mb-3">Link Expired</h5>
                    <p class="text-muted"><?= e($error) ?></p>
                    <a href="<?= url('admin/forgot_password.php') ?>" class="btn btn-primary mt-3">
                        <i class="bi bi-send me-2"></i>Request New Link
                    </a>
                </div>
            <?php endif; ?>

            <hr class="my-4">

            <!-- Back Link -->
            <div class="text-center">
                <a href="<?= url('admin/login.php') ?>" class="text-muted text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Back to Login
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
