<?php
/**
 * Email Verification Page
 * 
 * Verifies an admin account when they click the link from their email.
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(url('admin/'));
}

$token = $_GET['token'] ?? '';
$error = null;
$success = false;

if (!empty($token)) {
    $result = AuthController::verifyEmail($token);
    if ($result['success']) {
        $success = true;
    } else {
        $error = $result['error'];
    }
} else {
    $error = 'No verification token provided. Please check your email for the correct link.';
}

$pageTitle = 'Email Verification';
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
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="card login-card">
        <div class="card-body p-4 p-md-5 text-center">
            <!-- Header -->
            <div class="mb-4">
                <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" style="width: 240px; height: 80px; object-fit: cover; object-position: center;" class="mb-3 rounded">
            </div>

            <?php if ($success): ?>
                <div class="mb-4">
                    <i class="bi bi-check-circle text-success" style="font-size: 3.5rem;"></i>
                </div>
                <h4 class="fw-semibold mb-3">Email Verified!</h4>
                <p class="text-muted">
                    Your email address has been verified successfully. 
                    You can now log in to the admin panel.
                </p>
                <a href="<?= url('admin/login.php') ?>" class="btn btn-primary mt-3 px-4 py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </a>
            <?php else: ?>
                <div class="mb-4">
                    <i class="bi bi-x-circle text-danger" style="font-size: 3.5rem;"></i>
                </div>
                <h4 class="fw-semibold mb-3">Verification Failed</h4>
                <p class="text-muted"><?= e($error) ?></p>
                <div class="mt-3">
                    <a href="<?= url('admin/register.php') ?>" class="btn btn-outline-primary me-2">
                        <i class="bi bi-person-plus me-1"></i>Register Again
                    </a>
                    <a href="<?= url('admin/login.php') ?>" class="btn btn-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Sign In
                    </a>
                </div>
            <?php endif; ?>

            <hr class="my-4">

            <a href="<?= url('/') ?>" class="text-muted text-decoration-none">
                <i class="bi bi-arrow-left me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" 
        crossorigin="anonymous"></script>
</body>
</html>
