<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\core\Router.php
// Create at 2508041210 Ver1.00

class Router {
    private $routes = [];
    private $basePath;

    public function __construct($basePath = '') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function get($uri, $callback) {
        $this->addRoute('GET', $uri, $callback);
    }

    public function post($uri, $callback) {
        $this->addRoute('POST', $uri, $callback);
    }

    private function addRoute($method, $uri, $callback) {
        $this->routes[$method][$this->formatUri($uri)] = $callback;
    }

    private function formatUri($uri) {
        return rtrim($uri, '/');
    }

    public function dispatch() {
        $uri = $this->getUri();
        $method = $_SERVER['REQUEST_METHOD'];

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routeUri => $callback) {
                if ($routeUri === $uri) {
                    if (is_callable($callback)) {
                        call_user_func($callback);
                    } elseif (is_string($callback)) {
                        // Handle Controller@method string format
                        list($controllerName, $methodName) = explode('@', $callback);
                        $controllerPath = __DIR__ . '/../controllers/' . str_replace('Controller', '', $controllerName) . 'Controller.php';
                        
                        if (file_exists($controllerPath)) {
                            require_once $controllerPath;
                            $controller = new $controllerName();
                            if (method_exists($controller, $methodName)) {
                                $controller->$methodName();
                            } else {
                                http_response_code(500);
                                echo "Error: Method {$methodName} not found in {$controllerName}.";
                            }
                        } else {
                            http_response_code(500);
                            echo "Error: Controller {$controllerName} not found.";
                        }
                    }
                    return;
                }
            }
        }

        http_response_code(404);
        echo "<h1>404 Not Found</h1>";
    }

    private function getUri() {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = strtok($uri, '?'); // Remove query string

        // Remove base path
        if ($this->basePath && strpos($uri, $this->basePath) === 0) {
            $uri = substr($uri, strlen($this->basePath));
        }
        
        return rtrim($uri, '/');
    }
}
