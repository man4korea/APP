<?php
/**
 * AppMart Application Entry Point
 * C:\xampp\htdocs\AppMart\public\index.php
 * Create at 2508041600 Ver1.00
 */

// Load the application bootstrap
require_once __DIR__ . '/../bootstrap.php';

// Simple router implementation
class Router {
    private $routes = [];
    private $current_route = '';
    
    public function __construct() {
        $this->current_route = $_GET['route'] ?? '';
    }
    
    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }
    
    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }
    
    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $this->current_route;
        
        // Handle routes
        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            
            if (is_callable($handler)) {
                return call_user_func($handler);
            } elseif (is_string($handler)) {
                return $this->handleControllerAction($handler);
            }
        }
        
        // 404 handling
        $this->handle404();
    }
    
    private function handleControllerAction($handler) {
        list($controller, $action) = explode('@', $handler);
        $controllerFile = __DIR__ . "/../src/controllers/{$controller}.php";
        
        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            $controllerClass = "controllers\\{$controller}";
            
            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $action)) {
                    return $instance->$action();
                }
            }
        }
        
        $this->handle404();
    }
    
    private function handle404() {
        http_response_code(404);
        echo view('layouts/error', [
            'title' => '404 - Page Not Found',
            'message' => 'The requested page could not be found.',
            'code' => 404
        ]);
        exit;
    }
}

// Initialize router
$router = new Router();

// Define routes
$router->get('', function() {
    require_once __DIR__ . '/../src/controllers/HomeController.php';
    $controller = new controllers\HomeController();
    return $controller->index();
});

$router->get('home', function() {
    require_once __DIR__ . '/../src/controllers/HomeController.php';
    $controller = new controllers\HomeController();
    return $controller->index();
});

// Authentication routes
$router->get('auth/login', 'AuthController@showLogin');
$router->post('auth/login', 'AuthController@login');
$router->get('auth/register', 'AuthController@showRegister');
$router->post('auth/register', 'AuthController@register');
$router->get('auth/logout', 'AuthController@logout');

// App routes
$router->get('apps', 'AppController@index');
$router->get('apps/show', 'AppController@show');
$router->get('apps/create', 'AppController@create');
$router->post('apps/store', 'AppController@store');

// User dashboard
$router->get('dashboard', 'DashboardController@index');

// Admin routes
$router->get('admin', 'AdminController@index');
$router->get('admin/apps', 'AdminController@apps');
$router->get('admin/users', 'AdminController@users');

// API routes
$router->get('api/apps', 'ApiController@apps');
$router->get('api/categories', 'ApiController@categories');

// System routes
$router->get('system/info', function() {
    if (!config('app.debug')) {
        http_response_code(403);
        die('Access denied');
    }
    
    phpinfo();
});

$router->get('system/test-db', function() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT 1 as test");
        $result = $stmt->fetch();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Database connection successful',
            'test_result' => $result['test'],
            'server_info' => $pdo->getAttribute(PDO::ATTR_SERVER_INFO)
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
    }
});

// Dispatch the request
try {
    $router->dispatch();
} catch (Exception $e) {
    if (config('app.debug')) {
        echo "<h1>Application Error</h1>";
        echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>File:</strong> " . $e->getFile() . ":" . $e->getLine() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    } else {
        echo view('layouts/error', [
            'title' => 'Application Error',
            'message' => 'An unexpected error occurred. Please try again later.',
            'code' => 500
        ]);
    }
}