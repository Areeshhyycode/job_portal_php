<?php
/**
 * Global config. Adjust DB credentials to match your MySQL setup.
 * Default values below match a standard XAMPP install (user root, no password).
 */

// ---- Database ----
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'job_portal');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

// ---- App ----
define('APP_NAME', 'JobHive');
// Base URL path. If the app runs at http://localhost:8000/ leave as ''.
// If it runs in a subfolder (e.g. htdocs/job-portal) set to '/job-portal'.
define('BASE_URL', rtrim(getenv('BASE_URL') ?: '', '/'));

define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/resumes');
define('MAX_RESUME_BYTES', 3 * 1024 * 1024); // 3 MB

// Jobs per page for pagination
define('PER_PAGE', 6);

date_default_timezone_set('Asia/Karachi');

// Show errors during development. Comment out in production.
error_reporting(E_ALL);
ini_set('display_errors', '1');
