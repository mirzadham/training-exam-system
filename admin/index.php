<?php
/**
 * Admin Dashboard
 * 
 * Protected admin landing page.
 * Full dashboard with stats will be implemented in Phase 8.
 */

require_once __DIR__ . '/../init.php';
requireAdmin();

$pageTitle = 'Dashboard';
require_once VIEWS_PATH . '/layout/admin_header.php';
?>

<h2 class="mb-4">
    <i class="bi bi-speedometer2 me-2"></i>Dashboard
</h2>

<div class="alert alert-info">
    <i class="bi bi-info-circle me-2"></i>
    Welcome, <strong><?= e(getAdminName()) ?></strong>. Dashboard statistics will be available in a later phase.
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card stat-card text-center p-3">
            <div class="stat-icon text-primary"><i class="bi bi-building"></i></div>
            <h6 class="mt-2 text-muted">Organizations</h6>
            <h3>—</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center p-3">
            <div class="stat-icon text-success"><i class="bi bi-collection"></i></div>
            <h6 class="mt-2 text-muted">Question Banks</h6>
            <h3>—</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center p-3">
            <div class="stat-icon text-warning"><i class="bi bi-people"></i></div>
            <h6 class="mt-2 text-muted">Participants</h6>
            <h3>—</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-center p-3">
            <div class="stat-icon text-danger"><i class="bi bi-bar-chart"></i></div>
            <h6 class="mt-2 text-muted">Exam Attempts</h6>
            <h3>—</h3>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/admin_footer.php'; ?>
