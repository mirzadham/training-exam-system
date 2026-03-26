<?php
/**
 * API Endpoint — Bulk Save AI-Generated Questions
 * 
 * Accepts a JSON payload of reviewed questions and saves them
 * to the database in a single transaction.
 * 
 * Method: POST (application/json)
 * Auth:   Admin session + CSRF token
 */

require_once __DIR__ . '/../init.php';
require_once MODELS_PATH . '/Question.php';
require_once MODELS_PATH . '/QuestionBank.php';

header('Content-Type: application/json; charset=utf-8');

// ── Auth ───────────────────────────────────────────────────────
if (!isAdminLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed.']);
    exit;
}

// ── Read JSON body ─────────────────────────────────────────────
$rawInput = file_get_contents('php://input');
$input    = json_decode($rawInput, true);

if (!$input || !is_array($input)) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON payload.']);
    exit;
}

// ── CSRF ───────────────────────────────────────────────────────
if (!verify_csrf($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token. Please reload the page.']);
    exit;
}

// ── Validate question bank ─────────────────────────────────────
$bankId = (int) ($input['question_bank_id'] ?? 0);
if ($bankId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Please select a question bank.']);
    exit;
}
$bank = QuestionBank::findById($bankId);
if (!$bank) {
    echo json_encode(['success' => false, 'error' => 'Selected question bank does not exist.']);
    exit;
}

// ── Validate questions array ───────────────────────────────────
$questions = $input['questions'] ?? [];
if (!is_array($questions) || empty($questions)) {
    echo json_encode(['success' => false, 'error' => 'No questions to save.']);
    exit;
}

$validOptions = ['A', 'B', 'C', 'D'];
$errors       = [];

foreach ($questions as $i => $q) {
    $num = $i + 1;
    if (empty(trim($q['question_text'] ?? ''))) {
        $errors[] = "Question #{$num}: Question text is required.";
    }
    foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $opt) {
        if (empty(trim($q[$opt] ?? ''))) {
            $label    = strtoupper(substr($opt, -1));
            $errors[] = "Question #{$num}: Option {$label} is required.";
        }
    }
    $co = strtoupper(trim($q['correct_option'] ?? ''));
    if (!in_array($co, $validOptions)) {
        $errors[] = "Question #{$num}: Correct answer must be A, B, C, or D.";
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode("\n", $errors)]);
    exit;
}

// ── Save in transaction ────────────────────────────────────────
try {
    $pdo = getDBConnection();
    $pdo->beginTransaction();

    $saved = 0;
    foreach ($questions as $q) {
        Question::create([
            'question_bank_id' => $bankId,
            'question_text'    => trim($q['question_text']),
            'option_a'         => trim($q['option_a']),
            'option_b'         => trim($q['option_b']),
            'option_c'         => trim($q['option_c']),
            'option_d'         => trim($q['option_d']),
            'correct_option'   => strtoupper(trim($q['correct_option'])),
            'explanation'      => trim($q['explanation'] ?? ''),
        ]);
        $saved++;
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'count' => $saved]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
