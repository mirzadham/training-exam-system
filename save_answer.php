<?php
/**
 * AJAX — Save Answer
 * 
 * Saves answer via AJAX (for auto-save without page reload).
 * Returns JSON response.
 */

require_once __DIR__ . '/init.php';
require_once MODELS_PATH . '/ExamAttempt.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
    exit;
}

if (empty($_SESSION['attempt_id'])) {
    echo json_encode(['success' => false, 'error' => 'No active attempt']);
    exit;
}

$attemptId = (int) $_SESSION['attempt_id'];
$questionId = (int) ($_POST['question_id'] ?? 0);
$selectedOption = $_POST['selected_option'] ?? null;

if (!$questionId || !$selectedOption || !in_array($selectedOption, ['A', 'B', 'C', 'D'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

try {
    ExamAttempt::saveAnswer($attemptId, $questionId, $selectedOption);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Save failed']);
}
