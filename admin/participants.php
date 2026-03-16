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

$pageTitle = 'Participants';
require_once VIEWS_PATH . '/layout/admin_header.php';
require_once VIEWS_PATH . '/admin/participants/list.php';
require_once VIEWS_PATH . '/layout/admin_footer.php';
