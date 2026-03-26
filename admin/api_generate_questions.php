<?php
/**
 * API Endpoint — Generate Questions via Gemini AI
 * 
 * Accepts a PDF upload + question count, calls Google Gemini API,
 * and returns generated questions as JSON for review.
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
if ($file['size'] > 20 * 1024 * 1024) { // 20 MB limit (Gemini free tier)
    echo json_encode(['success' => false, 'error' => 'File size exceeds 20 MB limit.']);
    exit;
}

// ── API Key ────────────────────────────────────────────────────
$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    echo json_encode(['success' => false, 'error' => 'Gemini API key is not configured. Add GEMINI_API_KEY to .env file.']);
    exit;
}

// ── Step 1: Upload file to Gemini File API ─────────────────────
$uploadUrl = 'https://generativelanguage.googleapis.com/upload/v1beta/files?key=' . urlencode($apiKey);

// Gemini requires multipart/related: Part 1 = JSON metadata, Part 2 = file binary
$boundary    = 'gemini_upload_' . bin2hex(random_bytes(16));
$fileContent = file_get_contents($file['tmp_name']);
$displayName = pathinfo($file['name'], PATHINFO_FILENAME);

$metadata = json_encode([
    'file' => ['display_name' => $displayName]
]);

$body  = "--{$boundary}\r\n";
$body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
$body .= $metadata . "\r\n";
$body .= "--{$boundary}\r\n";
$body .= "Content-Type: application/pdf\r\n\r\n";
$body .= $fileContent . "\r\n";
$body .= "--{$boundary}--\r\n";

$ch = curl_init($uploadUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $body,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 120,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: multipart/related; boundary=' . $boundary,
        'X-Goog-Upload-Protocol: multipart',
    ],
]);

$uploadResponse = curl_exec($ch);
$uploadHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$uploadError    = curl_error($ch);
curl_close($ch);

if ($uploadError) {
    echo json_encode(['success' => false, 'error' => 'Failed to upload file to Gemini: ' . $uploadError]);
    exit;
}
if ($uploadHttpCode !== 200) {
    $errBody = json_decode($uploadResponse, true);
    $errMsg  = $errBody['error']['message'] ?? ('HTTP ' . $uploadHttpCode);
    echo json_encode(['success' => false, 'error' => 'Gemini File Upload failed: ' . $errMsg]);
    exit;
}

$uploadData = json_decode($uploadResponse, true);
$fileUri    = $uploadData['file']['uri'] ?? null;

if (!$fileUri) {
    echo json_encode(['success' => false, 'error' => 'Gemini did not return a file URI. Response: ' . substr($uploadResponse, 0, 500)]);
    exit;
}

// ── Step 2: Generate questions via Gemini ──────────────────────
$generateUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . urlencode($apiKey);

$systemPrompt = "You are an expert exam writer. Read the provided document and generate exactly {$numQuestions} multiple-choice questions based on the content. You MUST return your response as a raw, valid JSON array. Do NOT wrap the JSON in markdown formatting (like ```json). Each object in the array must have these exact keys: \"question_text\", \"option_a\", \"option_b\", \"option_c\", \"option_d\", \"correct_option\" (must be one of: \"A\", \"B\", \"C\", \"D\"), \"explanation\". Make sure questions are diverse, covering different parts of the document. Each question should have exactly one correct answer.";

$requestBody = json_encode([
    'system_instruction' => [
        'parts' => [['text' => $systemPrompt]]
    ],
    'contents' => [
        [
            'parts' => [
                ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $fileUri]],
                ['text' => "Generate exactly {$numQuestions} multiple-choice questions from this document. Return ONLY a raw JSON array."]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature'     => 0.7,
        'responseMimeType' => 'application/json',
    ]
]);

$ch = curl_init($generateUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $requestBody,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 180,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);

$genResponse = curl_exec($ch);
$genHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$genError    = curl_error($ch);
curl_close($ch);

if ($genError) {
    echo json_encode(['success' => false, 'error' => 'Gemini API request failed: ' . $genError]);
    exit;
}
if ($genHttpCode !== 200) {
    $errBody = json_decode($genResponse, true);
    $errMsg  = $errBody['error']['message'] ?? ('HTTP ' . $genHttpCode);
    echo json_encode(['success' => false, 'error' => 'Gemini generation failed: ' . $errMsg]);
    exit;
}

// ── Step 3: Parse and validate Gemini response ─────────────────
$genData = json_decode($genResponse, true);

// Extract text content from the response
$textContent = $genData['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$textContent) {
    // Check for safety blocks
    $finishReason = $genData['candidates'][0]['finishReason'] ?? 'UNKNOWN';
    if ($finishReason === 'SAFETY') {
        echo json_encode(['success' => false, 'error' => 'Gemini blocked the response due to safety filters. Try a different document.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Gemini returned an empty response. Reason: ' . $finishReason]);
    }
    exit;
}

// Parse the JSON from Gemini's text response
$questions = json_decode($textContent, true);

if (!is_array($questions) || empty($questions)) {
    // Attempt to extract JSON from markdown-wrapped response
    if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $textContent, $m)) {
        $questions = json_decode(trim($m[1]), true);
    }
    if (!is_array($questions) || empty($questions)) {
        echo json_encode(['success' => false, 'error' => 'Failed to parse questions from Gemini response. The AI may have returned an invalid format.']);
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
    echo json_encode(['success' => false, 'error' => 'Gemini generated questions but none had a valid structure. Please try again.']);
    exit;
}

echo json_encode([
    'success'   => true,
    'questions' => $validQuestions,
    'count'     => count($validQuestions),
]);
