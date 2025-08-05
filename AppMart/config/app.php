<?php
/**
 * AppMart Application Configuration
 * C:\xampp\htdocs\AppMart\config\app.php
 * Create at 2508041600 Ver1.00
 */

return [
    // Application Settings
    'name' => $_ENV['APP_NAME'] ?? 'AppMart',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost:8080',
    
    // Security Configuration
    'jwt_secret' => $_ENV['JWT_SECRET'] ?? null,
    'session_secret' => $_ENV['SESSION_SECRET'] ?? null,
    'encryption_key' => $_ENV['ENCRYPTION_KEY'] ?? null,
    
    // File Upload Settings
    'max_upload_size' => (int)($_ENV['MAX_UPLOAD_SIZE'] ?? 10485760), // 10MB default
    'allowed_file_types' => explode(',', $_ENV['ALLOWED_FILE_TYPES'] ?? 'zip,rar,tar.gz'),
    'upload_path' => __DIR__ . '/../uploads/',
    
    // Session Configuration
    'session' => [
        'name' => 'APPMART_SESSION',
        'lifetime' => 7200, // 2 hours
        'path' => '/',
        'domain' => '',
        'secure' => ($_ENV['APP_ENV'] ?? 'development') === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ],
    
    // Pagination
    'pagination' => [
        'per_page' => 12,
        'max_per_page' => 50
    ],
    
    // Application Routes
    'routes' => [
        'home' => '/',
        'auth' => '/auth',
        'apps' => '/apps',
        'admin' => '/admin',
        'api' => '/api'
    ],
    
    // Application Features
    'features' => [
        'user_registration' => true,
        'app_upload' => true,
        'payment_processing' => false, // Will be enabled later
        'ai_recommendations' => false, // Future feature
        'admin_approval' => true
    ]
];