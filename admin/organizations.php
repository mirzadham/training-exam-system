<?php
/**
 * Admin — Organization Management
 * 
 * Handles list, create, edit, delete actions via query parameter routing.
 * Routes: ?action=create, ?action=edit&id=X, ?action=delete&id=X
 */

require_once __DIR__ . '/../init.php';
require_once CONTROLLERS_PATH . '/OrganizationController.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// ============================================================
// DELETE action (POST only)
// ============================================================
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid security token.');
        redirect(url('admin/organizations.php'));
    }
    
    $result = OrganizationController::destroy($id);
    if ($result['success']) {
        setFlash('success', 'Organization deleted successfully.');
    } else {
        setFlash('error', $result['error']);
    }
    redirect(url('admin/organizations.php'));
}

// ============================================================
// CREATE action
// ============================================================
if ($action === 'create') {
    $errors = [];
    $data = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect(url('admin/organizations.php?action=create'));
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'active',
        ];

        $result = OrganizationController::store($data);

        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Organization created successfully.');
            redirect(url('admin/organizations.php'));
        } else {
            $errors = $result['errors'];
            setOldInput($data);
        }
    }

    $pageTitle = 'Add Organization';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/organizations/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// ============================================================
// EDIT action
// ============================================================
if ($action === 'edit' && $id) {
    $org = Organization::findById($id);
    if (!$org) {
        setFlash('error', 'Organization not found.');
        redirect(url('admin/organizations.php'));
    }

    $errors = [];
    $data = $org; // pre-fill form with existing data

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verify_csrf($_POST['csrf_token'] ?? '')) {
            setFlash('error', 'Invalid security token. Please try again.');
            redirect(url('admin/organizations.php?action=edit&id=' . $id));
        }

        $data = [
            'name'        => trim($_POST['name'] ?? ''),
            'code'        => trim($_POST['code'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => $_POST['status'] ?? 'active',
        ];

        $result = OrganizationController::update($id, $data);

        if ($result['success']) {
            clearOldInput();
            setFlash('success', 'Organization updated successfully.');
            redirect(url('admin/organizations.php'));
        } else {
            $errors = $result['errors'];
            setOldInput($data);
        }
    }

    $pageTitle = 'Edit Organization';
    require_once VIEWS_PATH . '/layout/admin_header.php';
    require_once VIEWS_PATH . '/admin/organizations/form.php';
    require_once VIEWS_PATH . '/layout/admin_footer.php';
    exit;
}

// ============================================================
// LIST action (default)
// ============================================================
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$currentPage = max(1, (int) ($_GET['page'] ?? 1));
$perPage = (int) ($_GET['per_page'] ?? 10);

$totalItems = Organization::countFiltered($search, $statusFilter);
$pagination = paginate($totalItems, $currentPage, $perPage);
$organizations = Organization::getPaginated($search, $statusFilter, $pagination['perPage'], $pagination['offset']);

$pageTitle = 'Organizations';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/organizations/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
