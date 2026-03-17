<?php
/**
 * Public — Exam Result Page
 * 
 * Shows the participant their final score, classification, 
 * and a summary of their performance. Clears exam session.
 */

require_once __DIR__ . '/init.php';
require_once MODELS_PATH . '/ExamAttempt.php';

// Must have an attempt to view results
if (empty($_SESSION['attempt_id'])) {
    redirect(url('/'));
}

$attemptId = (int) $_SESSION['attempt_id'];
$attempt = ExamAttempt::findById($attemptId);

if (!$attempt) {
    redirect(url('/'));
}

// If they somehow got here but attempt is still in progress, redirect to exam
if ($attempt['status'] === 'in_progress') {
    redirect(url('exam.php'));
}

// Once results are displayed, clear the exam session so they can't go back
// We keep participant_id in case we need it later, but clear attempt_id
unset($_SESSION['attempt_id']);
unset($_SESSION['bank_id']); // Also clear bank to prevent re-taking

$pageTitle = 'Exam Results';
require_once VIEWS_PATH . '/layout/header.php';

// Prepare badge classes based on result
$badgeClass = 'bg-secondary';
$iconClass = 'bi-info-circle';
if ($attempt['result'] === 'excellent') {
    $badgeClass = 'bg-success';
    $iconClass = 'bi-award-fill';
} elseif ($attempt['result'] === 'pass') {
    $badgeClass = 'bg-primary';
    $iconClass = 'bi-check-circle-fill';
} elseif ($attempt['result'] === 'fail') {
    $badgeClass = 'bg-danger';
    $iconClass = 'bi-x-circle-fill';
}
?>

<div class="row justify-content-center mt-4">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg text-center border-0 rounded-4">
            
            <div class="card-header bg-dark text-white p-4 rounded-top-4">
                <h3 class="mb-0">Exam Submitted</h3>
                <p class="mb-0 text-white-50"><small>Completed on <?= date('d M Y, h:i A', strtotime($attempt['submitted_at'])) ?></small></p>
            </div>

            <div class="card-body p-5">
                <i class="bi <?= $iconClass ?> display-1 mb-3 text-<?= str_replace('bg-', '', $badgeClass) ?>"></i>
                
                <h4 class="mb-2"><?= e($attempt['participant_name']) ?></h4>
                <p class="text-muted mb-4"><?= e($attempt['bank_title']) ?></p>

                <div class="display-2 fw-bold mb-2">
                    <?= number_format((float) $attempt['score_percent'], 1) ?>%
                </div>
                
                <div class="mb-5">
                    <span class="badge rounded-pill <?= $badgeClass ?> fs-5 px-4 py-2 text-uppercase tracking-wide">
                        <?= e($attempt['result']) ?>
                    </span>
                </div>

                <div class="row g-3 text-start bg-light rounded-3 p-3">
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm">
                            <h6 class="text-muted mb-1"><i class="bi bi-list-ol me-2"></i>Total Questions</h6>
                            <span class="fs-4 fw-bold"><?= $attempt['total_questions'] ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm border-start border-success border-4">
                            <h6 class="text-muted mb-1"><i class="bi bi-check-lg me-2 text-success"></i>Correct</h6>
                            <span class="fs-4 fw-bold text-success"><?= $attempt['correct_count'] ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm border-start border-danger border-4">
                            <h6 class="text-muted mb-1"><i class="bi bi-x-lg me-2 text-danger"></i>Wrong</h6>
                            <span class="fs-4 fw-bold text-danger"><?= $attempt['wrong_count'] ?></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-white rounded shadow-sm border-start border-warning border-4">
                            <h6 class="text-muted mb-1"><i class="bi bi-dash-circle me-2 text-warning"></i>Unanswered</h6>
                            <span class="fs-4 fw-bold text-warning"><?= $attempt['unanswered_count'] ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="card-footer bg-white border-top-0 pb-4">
                <a href="<?= url('/') ?>" class="btn btn-outline-primary px-4 rounded-pill">
                    <i class="bi bi-house me-1"></i> Return to Home
                </a>
            </div>
            
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
