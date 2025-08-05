<?php
/**
 * AppMart Application Bootstrap
 * C:\xampp\htdocs\AppMart\bootstrap.php
 * Create at 2508041600 Ver1.00
 */

// Configure session before starting
if (session_status() === PHP_SESSION_NONE) {
    // Configure session settings before starting
    ini_set('session.name', 'APPMART_SESSION');
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 7200);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
    ini_set('session.cookie_samesite', 'Lax');
    
    session_start();
}

// Set error reporting based on environment
function loadEnvironment($path = __DIR__ . '/.env') {
    if (!file_exists($path)) {
        // Try to copy from example if .env doesn't exist
        if (file_exists(__DIR__ . '/.env.example')) {
            copy(__DIR__ . '/.env.example', $path);
            die("Please configure your .env file with proper database credentials.");
        }
        die("Environment configuration file not found. Please create .env file.");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue; // Skip comments
        }
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Load environment variables
loadEnvironment();

// Load configuration
$config = [];
$configFiles = glob(__DIR__ . '/config/*.php');
foreach ($configFiles as $file) {
    $name = basename($file, '.php');
    $config[$name] = require $file;
}

// Set error reporting
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Session configuration is already set above

// Autoloader function
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/src/' . $class . '.php';
    
    if (file_exists($file)) {
        require_once $file;
    }
});

// Database connection function
function getDatabaseConnection($connectionName = null) {
    global $config;
    
    $dbConfig = $config['database'];
    $connectionName = $connectionName ?? $dbConfig['default'];
    
    if ($config['app']['env'] === 'production' && isset($dbConfig['connections']['production'])) {
        $connectionName = 'production';
    }
    
    $connection = $dbConfig['connections'][$connectionName];
    
    $dsn = "{$connection['driver']}:host={$connection['host']};port={$connection['port']};dbname={$connection['database']};charset={$connection['charset']}";
    
    try {
        $pdo = new PDO($dsn, $connection['username'], $connection['password'], $connection['options']);
        return $pdo;
    } catch (PDOException $e) {
        if ($config['app']['debug']) {
            die("Database connection failed: " . $e->getMessage());
        } else {
            die("Database connection failed. Please contact administrator.");
        }
    }
}

// Helper functions
function config($key, $default = null) {
    global $config;
    $keys = explode('.', $key);
    $value = $config;
    
    foreach ($keys as $k) {
        if (!isset($value[$k])) {
            return $default;
        }
        $value = $value[$k];
    }
    
    return $value;
}

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

function asset($path) {
    return config('app.url') . '/assets/' . ltrim($path, '/');
}

function url($path = '') {
    return config('app.url') . '/' . ltrim($path, '/');
}

function redirect($url) {
    header("Location: " . $url);
    exit;
}

function view($template, $data = []) {
    extract($data);
    $templatePath = __DIR__ . "/src/views/{$template}.php";
    
    if (!file_exists($templatePath)) {
        die("View not found: {$template}");
    }
    
    ob_start();
    include $templatePath;
    return ob_get_clean();
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
    $pdo = getDatabaseConnection();
} catch (Exception $e) {
    if (config('app.debug')) {
        die("Failed to initialize database: " . $e->getMessage());
    } else {
        die("System initialization failed. Please contact administrator.");
    }
}