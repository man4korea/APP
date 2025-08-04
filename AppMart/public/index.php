<?php
// AppMart Application Entry Point - Production Version
session_start();

// Load environment configuration
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Environment file not found: $path");
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
loadEnv(__DIR__ . '/.env');

// Set error reporting based on environment
if (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Get the current page parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AppMart - <?php echo ucfirst($page); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .success { background: #e8f5e8; color: #2e7d32; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #ffebee; color: #c62828; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .nav { margin: 20px 0; }
        .nav a { display: inline-block; margin-right: 15px; padding: 8px 16px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .nav a:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <?php
        switch ($page) {
            case 'home':
            default:
                echo "<h1>🚀 AppMart - Production Environment</h1>";
                
                echo "<div class='info'>";
                echo "<strong>Environment Information:</strong><br>";
                echo "Environment: " . (isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : 'Unknown') . "<br>";
                echo "Database: " . (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'Unknown') . "/" . (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'Unknown') . "<br>";
                echo "Debug Mode: " . (isset($_ENV['APP_DEBUG']) && $_ENV['APP_DEBUG'] === 'true' ? 'ON' : 'OFF') . "<br>";
                echo "PHP Version: " . phpversion() . "<br>";
                echo "Server Time: " . date('Y-m-d H:i:s') . "<br>";
                echo "</div>";
                
                echo "<div class='nav'>";
                echo "<a href='?page=test-db'>Test Database Connection</a>";
                echo "<a href='?page=phpinfo'>PHP Info</a>";
                echo "<a href='?page=env'>Environment Variables</a>";
                echo "</div>";
                
                echo "<h2>🎯 AppMart Project Status</h2>";
                echo "<p>✅ Web server is running correctly</p>";
                echo "<p>✅ PHP 8.4.10 is active</p>";
                echo "<p>✅ Environment configuration loaded</p>";
                echo "<p>🔄 Ready for application development</p>";
                break;
                
            case 'test-db':
                echo "<h1>🔗 Database Connection Test</h1>";
                
                try {
                    if (!isset($_ENV['DB_HOST']) || !isset($_ENV['DB_NAME']) || !isset($_ENV['DB_USER'])) {
                        throw new Exception("Database configuration missing in environment variables");
                    }
                    
                    $pdo = new PDO(
                        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4", 
                        $_ENV['DB_USER'], 
                        $_ENV['DB_PASS'],
                        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                    );
                    
                    echo "<div class='success'>";
                    echo "✅ Database connection successful!<br>";
                    echo "Host: " . $_ENV['DB_HOST'] . "<br>";
                    echo "Database: " . $_ENV['DB_NAME'] . "<br>";
                    echo "User: " . $_ENV['DB_USER'] . "<br>";
                    echo "Connection: Active";
                    echo "</div>";
                    
                } catch (PDOException $e) {
                    echo "<div class='error'>";
                    echo "❌ Database connection failed!<br>";
                    echo "Error: " . $e->getMessage();
                    echo "</div>";
                } catch (Exception $e) {
                    echo "<div class='error'>";
                    echo "❌ Configuration error!<br>";
                    echo "Error: " . $e->getMessage();
                    echo "</div>";
                }
                
                echo "<div class='nav'><a href='?page=home'>← Back to Home</a></div>";
                break;
                
            case 'phpinfo':
                echo "<h1>🔧 PHP Information</h1>";
                phpinfo();
                break;
                
            case 'env':
                echo "<h1>🔧 Environment Variables</h1>";
                echo "<div class='info'>";
                echo "<strong>Loaded Environment Variables:</strong><br>";
                foreach ($_ENV as $key => $value) {
                    if (strpos($key, 'PASS') !== false || strpos($key, 'KEY') !== false) {
                        echo "$key: *** (hidden for security) <br>";
                    } else {
                        echo "$key: $value <br>";
                    }
                }
                echo "</div>";
                echo "<div class='nav'><a href='?page=home'>← Back to Home</a></div>";
                break;
        }
        ?>
    </div>
</body>
</html>