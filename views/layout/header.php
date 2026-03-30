<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?> — <?= e(APP_NAME) ?></title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" 
          rel="stylesheet">

    <!-- Google Fonts — Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>?v=<?= filemtime(__DIR__ . '/../../assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <?php if (basename($_SERVER['SCRIPT_NAME']) === 'exam.php'): ?>
            <span class="navbar-brand d-flex align-items-center">
                <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" style="width: 180px; height: 60px; object-fit: cover; object-position: center;" class="me-2 rounded">
            </span>
        <?php else: ?>
            <a class="navbar-brand d-flex align-items-center" href="<?= url('/') ?>">
                <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" style="width: 180px; height: 60px; object-fit: cover; object-position: center;" class="me-2 rounded">
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Flash Messages -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div class="container mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Main Content -->
<main class="container py-4">
