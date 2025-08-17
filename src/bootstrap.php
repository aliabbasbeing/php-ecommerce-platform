<?php

// Bootstrap file for the e-commerce platform
// This file handles autoloading, configuration, and basic setup

session_start();

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $env = parse_ini_file(__DIR__ . '/../.env');
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
    }
}

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';

// Load configuration
$config = [
    'app' => require __DIR__ . '/../config/app.php',
    'database' => require __DIR__ . '/../config/database.php',
    'auth' => require __DIR__ . '/../config/auth.php',
    'payment' => require __DIR__ . '/../config/payment.php',
    'notifications' => require __DIR__ . '/../config/notifications.php',
];

// Make config globally available
define('APP_CONFIG', $config);

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Error reporting based on environment
if ($config['app']['app_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Helper functions
function config($key, $default = null) {
    $keys = explode('.', $key);
    $value = APP_CONFIG;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $default;
        }
    }
    
    return $value;
}

function asset($path) {
    return config('app.app_url') . '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    return config('app.app_url') . '/' . ltrim($path, '/');
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function old($key, $default = '') {
    return $_SESSION['old_input'][$key] ?? $default;
}

function flash($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type) {
    $message = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $message;
}

function csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Initialize database connection
try {
    $dbConfig = config('database');
    $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);
    
    // Make PDO globally available
    define('DB', $pdo);
} catch (PDOException $e) {
    if (config('app.app_debug')) {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Database connection failed. Please try again later.');
    }
}