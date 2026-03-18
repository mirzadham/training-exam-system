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

<h1 class="mb-4" style="font-weight: 700; font-size: 1.75rem;">Dashboard</h1>

<div class="row g-4">
    <!-- Organizations -->
    <div class="col-md-6">
        <div class="card stat-card stat-card-blue">
            <div class="card-body">
                <div class="stat-info">
                    <h6>Organizations</h6>
                    <div class="stat-number"><?= $totalOrgs ?> <span class="stat-label">Active</span></div>
                </div>
                <div class="stat-icon-box">
                    <i class="bi bi-building"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Total Participants -->
    <div class="col-md-6">
        <div class="card stat-card stat-card-green">
            <div class="card-body">
                <div class="stat-info">
                    <h6>Total Participants</h6>
                    <div class="stat-number"><?= $totalParticipants ?></div>
                </div>
                <div class="stat-icon-box">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Exam Attempts -->
    <div class="col-md-6">
        <div class="card stat-card stat-card-red">
            <div class="card-body">
                <div class="stat-info">
                    <h6>Exam Attempts</h6>
                    <div class="stat-number"><?= $totalExams ?></div>
                </div>
                <div class="stat-icon-box">
                    <i class="bi bi-bar-chart"></i>
                </div>
            </div>
        </div>
    </div>
    <!-- Average Score -->
    <div class="col-md-6">
        <div class="card stat-card stat-card-yellow">
            <div class="card-body">
                <div class="stat-info">
                    <h6>Average Score</h6>
                    <div class="stat-number"><?= $avgScore ?>%</div>
                </div>
                <div class="stat-icon-box">
                    <i class="bi bi-percent"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/admin_footer.php'; ?>
