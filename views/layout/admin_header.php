<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Dashboard') ?> — <?= e(APP_NAME) ?> Admin</title>

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

<!-- Admin Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <!-- Brand / Logo -->
    <div class="sidebar-brand">
        <a href="<?= url('admin/') ?>" class="sidebar-brand-link">
            <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" class="sidebar-logo">
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="sidebar-nav-list">
            <li>
                <a href="<?= url('admin/') ?>" class="sidebar-nav-link<?= ($pageTitle ?? '') === 'Dashboard' ? ' active' : '' ?>">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/organizations.php') ?>" class="sidebar-nav-link<?= ($pageTitle ?? '') === 'Organizations' || ($pageTitle ?? '') === 'Add Organization' || ($pageTitle ?? '') === 'Edit Organization' ? ' active' : '' ?>">
                    <i class="bi bi-building"></i>
                    <span>Organizations</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/question_banks.php') ?>" class="sidebar-nav-link<?= ($pageTitle ?? '') === 'Question Banks' || ($pageTitle ?? '') === 'Add Question Bank' || ($pageTitle ?? '') === 'Edit Question Bank' ? ' active' : '' ?>">
                    <i class="bi bi-collection"></i>
                    <span>Question Banks</span>
                </a>
            </li>
            <li>
                <a href="<?= url('admin/questions.php') ?>" class="sidebar-nav-link<?= ($pageTitle ?? '') === 'Questions' || ($pageTitle ?? '') === 'Add Question' || ($pageTitle ?? '') === 'Edit Question' ? ' active' : '' ?>">
                    <i class="bi bi-question-circle"></i>
                    <span>Questions</span>
                </a>
            </li>

            <li>
                <a href="<?= url('admin/results.php') ?>" class="sidebar-nav-link<?= ($pageTitle ?? '') === 'Exam Results' ? ' active' : '' ?>">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Results</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Admin Section (bottom) -->
    <div class="sidebar-admin-section">
        <div class="sidebar-admin-info">
            <div class="sidebar-admin-avatar">
                <i class="bi bi-person-circle"></i>
            </div>
            <span class="sidebar-admin-name"><?= e(getAdminName()) ?></span>
        </div>
        <a href="<?= url('admin/logout.php') ?>" class="sidebar-logout-btn">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>
</aside>

<!-- Admin Content Area -->
<div class="admin-content">

    <!-- Flash Messages -->
    <?php $flash = getFlash(); ?>
    <?php if ($flash): ?>
    <div class="px-4 pt-3">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Admin Main Content -->
    <main class="admin-main px-4 py-4">
