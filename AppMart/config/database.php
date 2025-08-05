<?php
/**
 * AppMart Database Configuration
 * C:\xampp\htdocs\AppMart\config\database.php
 * Create at 2508041600 Ver1.00
 */

return [
    'default' => 'mysql',
    
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_NAME'] ?? 'appmart_db',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        
        // Production database configuration
        'production' => [
            'driver' => 'mysql',
            'host' => $_ENV['PRODUCTION_DB_HOST'] ?? $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['PRODUCTION_DB_NAME'] ?? $_ENV['DB_NAME'],
            'username' => $_ENV['PRODUCTION_DB_USER'] ?? $_ENV['DB_USER'],
            'password' => $_ENV['PRODUCTION_DB_PASS'] ?? $_ENV['DB_PASS'],
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ]
    ],
    
    // Migration Settings
    'migrations' => [
        'table' => 'migrations',
        'path' => __DIR__ . '/../database/migrations'
    ]
];