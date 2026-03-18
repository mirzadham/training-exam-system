<?php
/**
 * Admin Login Page
 * 
 * Entry point for admin authentication.
 * If already logged in, redirect to dashboard.
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/AuthController.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(url('admin/'));
}

// Handle form submission
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = AuthController::login($username, $password);

    if ($result['success']) {
        setFlash('success', 'Welcome back, ' . e(getAdminName()) . '!');
        redirect(url('admin/index.php'));
    } else {
        $error = $result['error'];
    }
}
}

$pageTitle = 'Admin Login';
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
                <p class="text-muted">Admin Login</p>
            </div>

            <!-- Flash Messages -->
            <?php $flash = getFlash(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
                    <i class="bi bi-info-circle me-2"></i><?= e($flash['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= e($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="POST" action="" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="username" 
                               name="username" 
                               value="<?= e($_POST['username'] ?? '') ?>"
                               placeholder="Enter your username"
                               required 
                               autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                </button>
            </form>

            <!-- Back Link -->
            <div class="text-center mt-4">
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
