<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\core\bootstrap.php
// Create at 2508041111 Ver1.01

// Core bootstrap file
// This file will be used to initialize the application

// Start session
session_start();

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Seoul');

// Load environment variables if .env file exists
$envFile = __DIR__ . '/../../.env';
if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Define project constants
define('APP_ROOT', dirname(dirname(__DIR__)));
define('APP_URL', 'http://localhost/AppMart');

// Database configuration (default values if .env not found)
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'appmart_db');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Set charset
ini_set('default_charset', 'utf-8');
