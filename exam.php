<?php
/**
 * Public — Exam Page
 * 
 * Handles exam start, question display, navigation, and answer saving.
 * Flow: register.php → exam.php (start attempt) → exam.php?q=N (take exam)
 */

require_once __DIR__ . '/init.php';
require_once MODELS_PATH . '/ExamAttempt.php';
require_once MODELS_PATH . '/Question.php';
require_once MODELS_PATH . '/QuestionBank.php';

// ============================================================
// Guard: must have participant session
// ============================================================
if (empty($_SESSION['participant_id']) || empty($_SESSION['bank_id'])) {
    setFlash('error', 'Please register before starting the exam.');
    redirect(url('register.php'));
}

$participantId = (int) $_SESSION['participant_id'];
$bankId = (int) $_SESSION['bank_id'];

// ============================================================
// Check if already completed
// ============================================================
if (ExamAttempt::hasCompleted($participantId, $bankId)) {
    setFlash('error', 'You have already completed this exam.');
    redirect(url('result.php'));
}

// ============================================================
// Get or create the attempt
// ============================================================
$attempt = ExamAttempt::getInProgress($participantId, $bankId);

if (!$attempt) {
    // Load questions from the bank
    $questions = Question::getByBankId($bankId);

    if (empty($questions)) {
        setFlash('error', 'No questions available for this exam. Please contact your administrator.');
        redirect(url('register.php'));
    }

    // Create new attempt (randomizes question order)
    $attemptId = ExamAttempt::create($participantId, $bankId, $questions);
    $attempt = ExamAttempt::findById($attemptId);
} else {
    $attempt = ExamAttempt::findById($attempt['id']);
}

// Store attempt ID in session
$_SESSION['attempt_id'] = $attempt['id'];

// ============================================================
// Check time remaining
// ============================================================
$remainingSeconds = ExamAttempt::getRemainingSeconds($attempt);

if ($remainingSeconds <= 0) {
    // Auto-submit: time's up
    ExamAttempt::submit($attempt['id'], 'time_up');
    setFlash('info', 'Time is up! Your exam has been auto-submitted.');
    redirect(url('result.php'));
}

// ============================================================
// Handle answer save (POST) 
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_answer'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token. Please try again.');
        redirect(url('exam.php?q=' . ((int)($_GET['q'] ?? 1))));
    }
    
    $questionId = (int) ($_POST['question_id'] ?? 0);
    $selectedOption = $_POST['selected_option'] ?? null;

    if ($questionId && $selectedOption && in_array($selectedOption, ['A', 'B', 'C', 'D'])) {
        ExamAttempt::saveAnswer($attempt['id'], $questionId, $selectedOption);
    }

    // Navigate to next question or same
    $nextQ = (int) ($_POST['next_q'] ?? ($_GET['q'] ?? 1));
    redirect(url('exam.php?q=' . $nextQ));
}

// ============================================================
// Load current question
// ============================================================
$currentQ = max(1, (int) ($_GET['q'] ?? 1));
$totalQuestions = $attempt['total_questions'];

// Clamp to valid range
if ($currentQ > $totalQuestions) {
    $currentQ = $totalQuestions;
}

$currentAnswer = ExamAttempt::getAnswerByOrder($attempt['id'], $currentQ);

if (!$currentAnswer) {
    setFlash('error', 'Question not found.');
    redirect(url('exam.php?q=1'));
}

// Load all answers for progress indicator
$allAnswers = ExamAttempt::getAnswers($attempt['id']);
$answeredCount = 0;
foreach ($allAnswers as $a) {
    if ($a['selected_option'] !== null) {
        $answeredCount++;
    }
}

// ============================================================
// Render exam page
// ============================================================
$pageTitle = 'Exam — Question ' . $currentQ . ' of ' . $totalQuestions;
require_once VIEWS_PATH . '/layout/header.php';
require_once VIEWS_PATH . '/exam/question.php';
require_once VIEWS_PATH . '/layout/footer.php';
