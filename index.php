<?php
/**
 * Public Landing Page
 * 
 * This is the main entry point for public users (participants).
 * It shows a welcome page with option to register for an exam.
 */

require_once __DIR__ . '/init.php';

$pageTitle = 'Welcome';
require_once VIEWS_PATH . '/layout/header.php';
?>

<div class="row justify-content-center mt-3">
    <div class="col-md-8 text-center">
        <div class="mb-3">
            <img src="<?= asset('img/logo.png') ?>" alt="MIMOS Academy Logo" style="width: 320px; height: 100px; object-fit: cover; object-position: center;" class="rounded shadow-sm">
        </div>
        <p class="lead text-muted mb-3 fs-6">
            Online training assessment and certification exam platform.<br>
            Register and take your organization's exam to get evaluated.
        </p>
        <hr class="my-3">
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <a href="<?= url('register.php') ?>" class="btn btn-primary btn-lg px-4">
                <i class="bi bi-pencil-square me-2"></i>Register &amp; Take Exam
            </a>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-4 text-center mb-2">
        <i class="bi bi-building display-4 text-primary"></i>
        <h5 class="mt-2">Multi-Organization</h5>
        <p class="text-muted">Supports exams for multiple organizations, each with their own question banks.</p>
    </div>
    <div class="col-md-4 text-center mb-2">
        <i class="bi bi-clock-history display-4 text-primary"></i>
        <h5 class="mt-2">Timed Assessments</h5>
        <p class="text-muted">Take timed multiple-choice assessments with automatic scoring.</p>
    </div>
    <div class="col-md-4 text-center mb-2">
        <i class="bi bi-award display-4 text-primary"></i>
        <h5 class="mt-2">Instant Results</h5>
        <p class="text-muted">Get your results immediately after submitting the exam.</p>
    </div>
</div>

<?php require_once VIEWS_PATH . '/layout/footer.php'; ?>
