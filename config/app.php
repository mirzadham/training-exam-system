<?php
/**
 * Application Configuration
 * 
 * Central place for app-wide constants and settings.
 * Keep business rules here so they are easy to find and change.
 */

// Set Default Timezone for Malaysia (fixes 8 hour timer offset)
date_default_timezone_set('Asia/Kuala_Lumpur');

// Application
define('APP_NAME', 'Training Exam System');
define('APP_VERSION', '1.0.0');

// Base URL — update for production
// For Laragon (.test domains), this should be empty
define('BASE_URL', '');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONTROLLERS_PATH', ROOT_PATH . '/controllers');
define('MODELS_PATH', ROOT_PATH . '/models');

// Scoring rules
define('SCORE_PASS_PERCENT', 50);

// Timer rule
define('DEFAULT_MINUTES_PER_QUESTION', 1);

// Result classifications
define('RESULT_FAIL', 'fail');
define('RESULT_PASS', 'pass');

// Exam attempt statuses
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_SUBMITTED', 'submitted');
define('STATUS_TIME_UP', 'time_up');
