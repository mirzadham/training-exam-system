<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(APP_NAME) ?> Admin</title>

    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
          rel="stylesheet" 
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YcnS/1WR6zNkzlAOIuSR0638MYECfKJfcGr" 
          crossorigin="anonymous">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" 
          rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<!-- Admin Navigation -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= url('admin/') ?>">
            <i class="bi bi-clipboard-check me-2"></i><?= e(APP_NAME) ?> <small class="text-muted">Admin</small>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNavbar">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/') ?>">
                        <i class="bi bi-speedometer2 me-1"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/organizations.php') ?>">
                        <i class="bi bi-building me-1"></i>Organizations
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/question_banks.php') ?>">
                        <i class="bi bi-collection me-1"></i>Question Banks
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/questions.php') ?>">
                        <i class="bi bi-question-circle me-1"></i>Questions
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/participants.php') ?>">
                        <i class="bi bi-people me-1"></i>Participants
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/results.php') ?>">
                        <i class="bi bi-bar-chart me-1"></i>Results
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item">
                    <span class="nav-link text-light">
                        <i class="bi bi-person-circle me-1"></i><?= e(getAdminName()) ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= url('admin/logout.php') ?>">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
<div class="container-fluid mt-3">
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Admin Main Content -->
<main class="container-fluid py-4">
