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

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="exam_results_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // Header row
    fputcsv($output, ['Participant Name', 'IC Number', 'Organization', 'Question Bank', 'Score (%)', 'Correct', 'Wrong', 'Unanswered', 'Result', 'Status', 'Started At', 'Submitted At']);
    
    // Data rows
    foreach ($attempts as $a) {
        fputcsv($output, [
            $a['participant_name'],
            $a['ic_number'],
            $a['organization_name'],
            $a['bank_title'],
            $a['score_percent'],
            $a['correct_count'],
            $a['wrong_count'],
            $a['unanswered_count'],
            $a['result'] ? strtoupper($a['result']) : 'N/A',
            $a['status'],
            $a['started_at'],
            $a['submitted_at'] ?: 'N/A'
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = 'Exam Results';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/results/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
