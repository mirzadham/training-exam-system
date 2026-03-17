<?php
/**
 * Admin — Participant Management
 * 
 * View registered participants with search/filter.
 */

require_once __DIR__ . '/../init.php';
require_once MODELS_PATH . '/Participant.php';
require_once MODELS_PATH . '/Organization.php';
requireAdmin();

$search = trim($_GET['search'] ?? '');
$orgFilter = trim($_GET['org'] ?? '');
$participants = Participant::getAll($search, $orgFilter);
$organizations = Organization::getActive();

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="participants_' . date('Ymd_His') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // Header row
    fputcsv($output, ['Full Name', 'IC Number', 'Organization', 'Email', 'Phone', 'Course Name', 'Registered At']);
    
    // Data rows
    foreach ($participants as $p) {
        fputcsv($output, [
            $p['full_name'],
            $p['ic_number'],
            $p['organization_name'],
            $p['email'],
            $p['phone'],
            $p['course_name'],
            $p['created_at']
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = 'Participants';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/participants/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
