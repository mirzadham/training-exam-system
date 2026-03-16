<?php
/**
 * Admin — Question Bank Management
 * 
 * Routes: ?action=create, ?action=edit&id=X, ?action=delete&id=X
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/QuestionBankController.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    $result = QuestionBankController::destroy($id);
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Question bank deleted successfully.' : $result['error']);
    redirect(url('admin/question_banks.php'));
}

// CREATE
if ($action === 'create') {
    $errors = [];
    $data = [];
    $organizations = Organization::getActive();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'organization_id'  => $_POST['organization_id'] ?? '',
            'title'            => trim($_POST['title'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'duration_minutes' => trim($_POST['duration_minutes'] ?? ''),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
            'status'           => $_POST['status'] ?? 'active',
        ];

        $result = QuestionBankController::store($data);
        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Question bank created successfully.');
            redirect(url('admin/question_banks.php'));
        }
        $errors = $result['errors'];
        setOldInput($data);
    }

    $pageTitle = 'Add Question Bank';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/question_banks/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// EDIT
if ($action === 'edit' && $id) {
    $bank = QuestionBank::findById($id);
    if (!$bank) {
        setFlash('error', 'Question bank not found.');
        redirect(url('admin/question_banks.php'));
    }

    $errors = [];
    $data = $bank;
    $organizations = Organization::getActive();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = [
            'organization_id'  => $_POST['organization_id'] ?? '',
            'title'            => trim($_POST['title'] ?? ''),
            'description'      => trim($_POST['description'] ?? ''),
            'duration_minutes' => trim($_POST['duration_minutes'] ?? ''),
            'is_active'        => isset($_POST['is_active']) ? 1 : 0,
            'status'           => $_POST['status'] ?? 'active',
        ];

        $result = QuestionBankController::update($id, $data);
        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Question bank updated successfully.');
            redirect(url('admin/question_banks.php'));
        }
        $errors = $result['errors'];
        setOldInput($data);
    }

    $pageTitle = 'Edit Question Bank';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/question_banks/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// LIST (default)
$search = trim($_GET['search'] ?? '');
$orgFilter = trim($_GET['org'] ?? '');
$questionBanks = QuestionBank::getAll($search, $orgFilter);
$organizations = Organization::getActive();

$pageTitle = 'Question Banks';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/question_banks/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
