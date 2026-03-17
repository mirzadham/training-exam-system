<?php
/**
 * Admin — Exam Results Management
 * 
 * View all exam results with search, filter, and CSV export.
 */

require_once __DIR__ . '/../init.php';
require_once MODELS_PATH . '/ExamAttempt.php';
require_once MODELS_PATH . '/Organization.php';
requireAdmin();

$search = trim($_GET['search'] ?? '');
$orgFilter = trim($_GET['org'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$attempts = ExamAttempt::getAll($search, $orgFilter, $statusFilter);
$organizations = Organization::getActive();

$pageTitle = 'Exam Results';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/results/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
