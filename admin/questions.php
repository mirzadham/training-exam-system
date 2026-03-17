<?php
/**
 * Admin — Question Management
 * 
 * Routes: ?action=create&bank_id=X, ?action=edit&id=X, ?action=delete&id=X
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/QuestionController.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(url('admin/questions.php'));
    }
    $question = Question::findById($id);
    $result = QuestionController::destroy($id);
    $redirectUrl = 'admin/questions.php';
    if ($question && !empty($_POST['bank_id'])) {
        $redirectUrl .= '?bank=' . (int) $_POST['bank_id'];
    }
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Question deleted successfully.' : $result['error']);
    redirect(url($redirectUrl));
}

// CREATE
if ($action === 'create') {
    $errors = [];
    $data = ['question_bank_id' => $_GET['bank_id'] ?? ''];
    $questionBanks = QuestionBank::getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect(url('admin/questions.php?action=create'));
        }

        $data = [
            'question_bank_id' => $_POST['question_bank_id'] ?? '',
            'question_text'    => trim($_POST['question_text'] ?? ''),
            'option_a'         => trim($_POST['option_a'] ?? ''),
            'option_b'         => trim($_POST['option_b'] ?? ''),
            'option_c'         => trim($_POST['option_c'] ?? ''),
            'option_d'         => trim($_POST['option_d'] ?? ''),
            'correct_option'   => $_POST['correct_option'] ?? '',
            'explanation'      => trim($_POST['explanation'] ?? ''),
        ];

        $result = QuestionController::store($data);
        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Question created successfully.');
            redirect(url('admin/questions.php?bank=' . $data['question_bank_id']));
        }
        $errors = $result['errors'];
        setOldInput($data);
    }

    $pageTitle = 'Add Question';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/questions/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// EDIT
if ($action === 'edit' && $id) {
    $question = Question::findById($id);
    if (!$question) {
        setFlash('error', 'Question not found.');
        redirect(url('admin/questions.php'));
    }

    $errors = [];
    $data = $question;
    $questionBanks = QuestionBank::getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect(url('admin/questions.php?action=edit&id=' . $id));
        }

        $data = [
            'question_bank_id' => $_POST['question_bank_id'] ?? '',
            'question_text'    => trim($_POST['question_text'] ?? ''),
            'option_a'         => trim($_POST['option_a'] ?? ''),
            'option_b'         => trim($_POST['option_b'] ?? ''),
            'option_c'         => trim($_POST['option_c'] ?? ''),
            'option_d'         => trim($_POST['option_d'] ?? ''),
            'correct_option'   => $_POST['correct_option'] ?? '',
            'explanation'      => trim($_POST['explanation'] ?? ''),
        ];

        $result = QuestionController::update($id, $data);
        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Question updated successfully.');
            redirect(url('admin/questions.php?bank=' . $data['question_bank_id']));
        }
        $errors = $result['errors'];
        setOldInput($data);
    }

    $pageTitle = 'Edit Question';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/questions/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// LIST (default)
$search = trim($_GET['search'] ?? '');
$bankFilter = trim($_GET['bank'] ?? '');
$questions = Question::getAll($search, $bankFilter);
$questionBanks = QuestionBank::getAll();

$pageTitle = 'Questions';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/questions/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
