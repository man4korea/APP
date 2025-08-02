<?php
// 📁 C:\xampp\htdocs\BPM\core\Router.php
// Create at 2508022042 Ver1.00

namespace BPM\Core;

/**
 * BPM API 라우터 클래스
 * RESTful API 라우팅, 미들웨어 지원, 권한 검증 포함
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private array $globalMiddlewares = [];
    private string $basePath = '';
    
    public function __construct(string $basePath = '')
    {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * GET 라우트 등록
     */
    public function get(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }
    
    /**
     * POST 라우트 등록
     */
    public function post(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }
    
    /**
     * PUT 라우트 등록
     */
    public function put(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }
    
    /**
     * DELETE 라우트 등록
     */
    public function delete(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }
    
    /**
     * PATCH 라우트 등록
     */
    public function patch(string $path, $handler, array $middlewares = []): void
    {
        $this->addRoute('PATCH', $path, $handler, $middlewares);
    }
    
    /**
     * 라우트 그룹 (공통 미들웨어 적용)
     */
    public function group(string $prefix, array $middlewares, callable $callback): void
    {
        $oldMiddlewares = $this->middlewares;
        $this->middlewares = array_merge($this->middlewares, $middlewares);
        
        $oldBasePath = $this->basePath;
        $this->basePath = $this->basePath . '/' . trim($prefix, '/');
        
        $callback($this);
        
        $this->middlewares = $oldMiddlewares;
        $this->basePath = $oldBasePath;
    }
    
    /**
     * 글로벌 미들웨어 추가
     */
    public function addGlobalMiddleware(callable $middleware): void
    {
        $this->globalMiddlewares[] = $middleware;
    }
    
    /**
     * 라우트 등록
     */
    private function addRoute(string $method, string $path, $handler, array $middlewares = []): void
    {
        $fullPath = $this->basePath . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        
        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => array_merge($this->middlewares, $middlewares),
            'pattern' => $this->convertToPattern($fullPath)
        ];
    }
    
    /**
     * 라우트 패턴을 정규식으로 변환
     */
    private function convertToPattern(string $path): string
    {
        // {id}, {name} 등의 파라미터를 정규식으로 변환
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }
    
    /**
     * 요청 라우팅 실행
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // CORS 처리
        $this->handleCORS();
        
        // OPTIONS 요청 처리 (CORS preflight)
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // 라우트 매칭
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                
                // 파라미터 추출
                $params = [];
                foreach ($matches as $key => $value) {
                    if (!is_numeric($key)) {
                        $params[$key] = $value;
                    }
                }
                
                try {
                    // 글로벌 미들웨어 실행
                    foreach ($this->globalMiddlewares as $middleware) {
                        $result = $middleware();
                        if ($result === false) {
                            return;
                        }
                    }
                    
                    // 라우트별 미들웨어 실행
                    foreach ($route['middlewares'] as $middleware) {
                        $result = $middleware();
                        if ($result === false) {
                            return;
                        }
                    }
                    
                    // 핸들러 실행
                    $this->executeHandler($route['handler'], $params);
                    return;
                    
                } catch (\Exception $e) {
                    BPMLogger::error('라우트 실행 중 오류 발생', [
                        'method' => $method,
                        'uri' => $uri,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    
                    ResponseHelper::error('서버 오류가 발생했습니다.', 500);
                    return;
                }
            }
        }
        
        // 404 Not Found
        ResponseHelper::error('요청한 리소스를 찾을 수 없습니다.', 404);
    }
    
    /**
     * 핸들러 실행
     */
    private function executeHandler($handler, array $params = []): void
    {
        if (is_callable($handler)) {
            // 함수 또는 클로저
            $handler($params);
        } elseif (is_string($handler)) {
            // "ClassName@methodName" 형식
            if (strpos($handler, '@') !== false) {
                list($class, $method) = explode('@', $handler);
                
                if (class_exists($class)) {
                    $instance = new $class();
                    if (method_exists($instance, $method)) {
                        $instance->$method($params);
                    } else {
                        throw new \Exception("메소드 {$method}를 찾을 수 없습니다.");
                    }
                } else {
                    throw new \Exception("클래스 {$class}를 찾을 수 없습니다.");
                }
            } else {
                // 파일 포함
                if (file_exists($handler)) {
                    include $handler;
                } else {
                    throw new \Exception("파일 {$handler}를 찾을 수 없습니다.");
                }
            }
        } elseif (is_array($handler) && count($handler) === 2) {
            // [ClassName, 'methodName'] 형식
            list($class, $method) = $handler;
            
            if (is_object($class)) {
                $class->$method($params);
            } elseif (is_string($class) && class_exists($class)) {
                $instance = new $class();
                $instance->$method($params);
            } else {
                throw new \Exception("잘못된 핸들러 형식입니다.");
            }
        } else {
            throw new \Exception("지원하지 않는 핸들러 형식입니다.");
        }
    }
    
    /**
     * CORS 처리
     */
    private function handleCORS(): void
    {
        // 개발 환경에서만 CORS 허용
        if (APP_ENV === 'development') {
            header('Access-Control-Allow-Origin: *');
        } else {
            // 프로덕션에서는 특정 도메인만 허용
            $allowedOrigins = [
                'https://yourdomain.com',
                'https://app.yourdomain.com'
            ];
            
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if (in_array($origin, $allowedOrigins)) {
                header("Access-Control-Allow-Origin: $origin");
            }
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, X-CSRF-Token');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400'); // 24시간
    }
    
    /**
     * 현재 요청의 JSON 데이터 파싱
     */
    public static function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            ResponseHelper::error('잘못된 JSON 형식입니다.', 400);
        }
        
        return $data ?? [];
    }
    
    /**
     * 요청 파라미터 검증
     */
    public static function validateRequired(array $data, array $required): array
    {
        $errors = [];
        
        foreach ($required as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[] = "{$field} 필드는 필수입니다.";
            }
        }
        
        if (!empty($errors)) {
            ResponseHelper::error('필수 필드가 누락되었습니다.', 400, $errors);
        }
        
        return $data;
    }
}

/**
 * 미들웨어 클래스들
 */
class Middlewares
{
    /**
     * 인증 필수 미들웨어
     */
    public static function requireAuth(): callable
    {
        return function() {
            if (!AuthManager::isLoggedIn()) {
                ResponseHelper::error('인증이 필요합니다.', 401);
                return false;
            }
            return true;
        };
    }
    
    /**
     * 관리자 권한 필수 미들웨어
     */
    public static function requireAdmin(): callable
    {
        return function() {
            if (!AuthManager::isLoggedIn()) {
                ResponseHelper::error('인증이 필요합니다.', 401);
                return false;
            }
            
            if (!AuthManager::isAdmin()) {
                ResponseHelper::error('관리자 권한이 필요합니다.', 403);
                return false;
            }
            
            return true;
        };
    }
    
    /**
     * CSRF 토큰 검증 미들웨어
     */
    public static function verifyCSRF(): callable
    {
        return function() {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
            
            if (!Security::getInstance()->verifyCSRFToken($token)) {
                ResponseHelper::error('CSRF 토큰이 유효하지 않습니다.', 403);
                return false;
            }
            
            return true;
        };
    }
    
    /**
     * Rate Limiting 미들웨어
     */
    public static function rateLimit(int $limit = 100, int $window = 3600): callable
    {
        return function() use ($limit, $window) {
            $identifier = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            
            if (!Security::getInstance()->checkRateLimit($identifier, $limit, $window)) {
                ResponseHelper::error('요청 한도를 초과했습니다. 잠시 후 다시 시도해주세요.', 429);
                return false;
            }
            
            return true;
        };
    }
    
    /**
     * JWT 토큰 검증 미들웨어
     */
    public static function verifyJWT(): callable
    {
        return function() {
            $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            
            if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
                ResponseHelper::error('Bearer 토큰이 필요합니다.', 401);
                return false;
            }
            
            $token = $matches[1];
            $payload = Security::getInstance()->verifyJWTToken($token);
            
            if (!$payload) {
                ResponseHelper::error('유효하지 않은 토큰입니다.', 401);
                return false;
            }
            
            // 토큰 정보를 글로벌 변수에 저장
            $_SESSION['jwt_payload'] = $payload;
            
            return true;
        };
    }
    
    /**
     * JSON Content-Type 필수 미들웨어
     */
    public static function requireJson(): callable
    {
        return function() {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            
            if (strpos($contentType, 'application/json') !== 0) {
                ResponseHelper::error('Content-Type은 application/json이어야 합니다.', 400);
                return false;
            }
            
            return true;
        };
    }
}