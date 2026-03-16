<?php
/**
 * Public — Submit Exam
 * 
 * Handles final exam submission.
 */

require_once __DIR__ . '/init.php';
require_once MODELS_PATH . '/ExamAttempt.php';

// Guard
if (empty($_SESSION['attempt_id'])) {
    setFlash('error', 'No active exam session.');
    redirect(url('register.php'));
}

$attemptId = (int) $_SESSION['attempt_id'];
$attempt = ExamAttempt::findById($attemptId);

if (!$attempt || $attempt['status'] !== 'in_progress') {
    setFlash('error', 'This exam has already been submitted.');
    redirect(url('result.php'));
}

// Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ExamAttempt::submit($attemptId, 'submitted');
    setFlash('success', 'Your exam has been submitted successfully!');
    redirect(url('result.php'));
}

// If GET, redirect back to exam
redirect(url('exam.php'));
