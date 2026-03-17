<?php
/**
 * Admin Dashboard
 * 
 * Protected admin landing page.
 * Full dashboard with stats will be implemented in Phase 8.
 */

require_once __DIR__ . '/../init.php';
require_once MODELS_PATH . '/Organization.php';
require_once MODELS_PATH . '/Participant.php';
require_once MODELS_PATH . '/ExamAttempt.php';

requireAdmin();

// Count organizations
$pdo = getDBConnection();
$stmt = $pdo->query("SELECT COUNT(*) FROM organizations WHERE status = 'active'");
$totalOrgs = (int) $stmt->fetchColumn();

// Count participants
$totalParticipants = Participant::count();

// Count completed exams
$stmt = $pdo->query("SELECT COUNT(*) FROM exam_attempts WHERE status IN ('submitted', 'time_up')");
$totalExams = (int) $stmt->fetchColumn();

// Compute average score
$stmt = $pdo->query("SELECT AVG(score_percent) FROM exam_attempts WHERE status IN ('submitted', 'time_up')");
$avgScore = round((float) $stmt->fetchColumn(), 1);

$pageTitle = 'Dashboard';
require_once VIEWS_PATH . '/layout/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-speedometer2 me-2"></i>Dashboard</h2>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Organizations</h6>
                        <h2 class="mb-0"><?= $totalOrgs ?> <small class="text-success fs-6">Active</small></h2>
                    </div>
                    <div class="text-primary bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-building fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Total Participants</h6>
                        <h2 class="mb-0"><?= $totalParticipants ?></h2>
                    </div>
                    <div class="text-success bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Exam Attempts</h6>
                        <h2 class="mb-0"><?= $totalExams ?></h2>
                    </div>
                    <div class="text-danger bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-bar-chart fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-2">Average Score</h6>
                        <h2 class="mb-0"><?= $avgScore ?>%</h2>
                    </div>
                    <div class="text-warning bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-percent fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/admin_footer.php'; ?>
