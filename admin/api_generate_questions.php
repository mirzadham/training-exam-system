<?php
/**
 * API Endpoint — Generate Questions via AI (OpenRouter)
 * 
 * Accepts a PDF upload + question count, calls OpenRouter API
 * with a free model, and returns generated questions as JSON for review.
 * 
 * Method: POST (multipart/form-data)
 * Auth:   Admin session + CSRF token
 */

require_once __DIR__ . '/../init.php';

header('Content-Type: application/json; charset=utf-8');

// ── Auth & CSRF ────────────────────────────────────────────────
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
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token. Please reload the page.']);
    exit;
}

// ── Input validation ───────────────────────────────────────────
$numQuestions = (int) ($_POST['num_questions'] ?? 10);
if ($numQuestions < 1 || $numQuestions > 50) {
    echo json_encode(['success' => false, 'error' => 'Number of questions must be between 1 and 50.']);
    exit;
}

if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload limit.',
        UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Server missing temp folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    ];
    $code = $_FILES['pdf_file']['error'] ?? UPLOAD_ERR_NO_FILE;
    $msg  = $uploadErrors[$code] ?? 'Unknown upload error.';
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$file     = $_FILES['pdf_file'];
$finfo    = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    echo json_encode(['success' => false, 'error' => 'Only PDF files are accepted. Detected: ' . $mimeType]);
    exit;
}
if ($file['size'] > 10 * 1024 * 1024) { // 10 MB limit for base64 payload
    echo json_encode(['success' => false, 'error' => 'File size exceeds 10 MB limit.']);
    exit;
}

// ── API Key ────────────────────────────────────────────────────
$apiKey = getenv('OPENROUTER_API_KEY');
if (!$apiKey) {
    echo json_encode(['success' => false, 'error' => 'OpenRouter API key is not configured. Add OPENROUTER_API_KEY to .env file.']);
    exit;
}

// ── Prepare PDF as base64 ──────────────────────────────────────
$fileContent = file_get_contents($file['tmp_name']);
$base64Pdf   = base64_encode($fileContent);
$dataUrl     = 'data:application/pdf;base64,' . $base64Pdf;

// ── Build OpenRouter request ───────────────────────────────────
$apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
$model  = getenv('AI_MODEL') ?: 'google/gemma-3-27b-it:free';

$systemPrompt = "You are an expert exam writer. Read the provided document and generate exactly {$numQuestions} multiple-choice questions based on the content. You MUST return your response as a raw, valid JSON array. Do NOT wrap the JSON in markdown formatting (like ```json). Each object in the array must have these exact keys: \"question_text\", \"option_a\", \"option_b\", \"option_c\", \"option_d\", \"correct_option\" (must be one of: \"A\", \"B\", \"C\", \"D\"), \"explanation\". Make sure questions are diverse, covering different parts of the document. Each question should have exactly one correct answer.";

$requestBody = json_encode([
    'model'    => $model,
    'messages' => [
        [
            'role'    => 'system',
            'content' => $systemPrompt,
        ],
        [
            'role'    => 'user',
            'content' => [
                [
                    'type' => 'file',
                    'file' => [
                        'filename'  => $file['name'],
                        'file_data' => $dataUrl,
                    ],
                ],
                [
                    'type' => 'text',
                    'text' => "Generate exactly {$numQuestions} multiple-choice questions from this document. Return ONLY a raw JSON array.",
                ],
            ],
        ],
    ],
    'plugins' => [
        [
            'id'  => 'file-parser',
            'pdf' => ['engine' => 'cloudflare-ai'],
        ],
    ],
    'temperature' => 0.7,
]);

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $requestBody,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
        'HTTP-Referer: http://training-exam-system.test',
        'X-Title: Training Exam System',
    ],
]);

$genResponse = curl_exec($ch);
$genHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$genError    = curl_error($ch);
curl_close($ch);

if ($genError) {
    echo json_encode(['success' => false, 'error' => 'API request failed: ' . $genError]);
    exit;
}
if ($genHttpCode !== 200) {
    $errBody = json_decode($genResponse, true);
    $errMsg  = $errBody['error']['message'] ?? ('HTTP ' . $genHttpCode);
    echo json_encode(['success' => false, 'error' => 'AI generation failed: ' . $errMsg]);
    exit;
}

// ── Parse OpenRouter response (OpenAI-compatible format) ───────
$genData = json_decode($genResponse, true);

$textContent = $genData['choices'][0]['message']['content'] ?? null;

if (!$textContent) {
    $finishReason = $genData['choices'][0]['finish_reason'] ?? 'unknown';
    echo json_encode(['success' => false, 'error' => 'AI returned an empty response. Finish reason: ' . $finishReason]);
    exit;
}

// Parse the JSON from the AI's text response
$questions = json_decode($textContent, true);

if (!is_array($questions) || empty($questions)) {
    // Attempt to extract JSON from markdown-wrapped response
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $textContent, $m)) {
        $questions = json_decode(trim($m[1]), true);
    }
    if (!is_array($questions) || empty($questions)) {
        echo json_encode(['success' => false, 'error' => 'Failed to parse questions from AI response. The model may have returned an invalid format.']);
        exit;
    }
}

// Validate each question structure
$validQuestions = [];
$requiredKeys  = ['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option'];
$validOptions  = ['A', 'B', 'C', 'D'];

foreach ($questions as $i => $q) {
    // Check required keys exist
    $missing = array_diff($requiredKeys, array_keys($q));
    if (!empty($missing)) continue;

    // Normalize correct_option
    $q['correct_option'] = strtoupper(trim($q['correct_option']));
    if (!in_array($q['correct_option'], $validOptions)) continue;

    // Ensure non-empty strings
    $valid = true;
    foreach (['question_text', 'option_a', 'option_b', 'option_c', 'option_d'] as $k) {
        if (empty(trim($q[$k]))) { $valid = false; break; }
    }
    if (!$valid) continue;

    $validQuestions[] = [
        'question_text'  => trim($q['question_text']),
        'option_a'       => trim($q['option_a']),
        'option_b'       => trim($q['option_b']),
        'option_c'       => trim($q['option_c']),
        'option_d'       => trim($q['option_d']),
        'correct_option' => $q['correct_option'],
        'explanation'    => trim($q['explanation'] ?? ''),
    ];
}

if (empty($validQuestions)) {
    echo json_encode(['success' => false, 'error' => 'AI generated questions but none had a valid structure. Please try again.']);
    exit;
}

echo json_encode([
    'success'   => true,
    'questions' => $validQuestions,
    'count'     => count($validQuestions),
]);
