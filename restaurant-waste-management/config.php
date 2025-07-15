<?php
// Load environment variables from .env file
$env = parse_ini_file(__DIR__ . '/.env');

// Define database constants using environment variables
define('DB_HOST', $env['DB_HOST'] ?? 'localhost');
define('DB_USER', $env['DB_USER'] ?? 'root');
define('DB_PASS', $env['DB_PASS'] ?? '');
define('DB_NAME', $env['DB_NAME'] ?? 'restaurant_management');
?>