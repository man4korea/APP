<?php
// 📁 C:\xampp\htdocs\BPM\index.php
// Create at 2508022048 Ver1.00

/**
 * BPM Total Business Process Management
 * 메인 엔트리 포인트 - 시스템 초기화 및 라우팅
 */

// 설정 파일 로드
require_once __DIR__ . '/includes/config.php';

// 부트스트랩 로드
require_once __DIR__ . '/core/bootstrap.php';

// 라우터 초기화
use BPM\Core\Router;
use BPM\Core\Middlewares;

$router = new Router('/BPM');

// 글로벌 미들웨어 (모든 요청에 적용)
$router->addGlobalMiddleware(Middlewares::rateLimit(200, 3600)); // 시간당 200회 제한

// API 라우트 그룹
$router->group('/api/v1', [Middlewares::requireJson()], function($router) {
    
    // 인증 관련 라우트 (인증 불필요)
    $router->post('/auth/login', 'BPM\\Controllers\\AuthController@login');
    $router->post('/auth/register', 'BPM\\Controllers\\AuthController@register');
    $router->post('/auth/forgot-password', 'BPM\\Controllers\\AuthController@forgotPassword');
    $router->post('/auth/reset-password', 'BPM\\Controllers\\AuthController@resetPassword');
    
    // 인증이 필요한 API 라우트
    $router->group('', [Middlewares::requireAuth(), Middlewares::verifyCSRF()], function($router) {
        
        // 사용자 정보
        $router->get('/user/profile', 'BPM\\Controllers\\UserController@profile');
        $router->put('/user/profile', 'BPM\\Controllers\\UserController@updateProfile');
        $router->post('/auth/logout', 'BPM\\Controllers\\AuthController@logout');
        
        // 회사 관리 (관리자만)
        $router->group('/companies', [Middlewares::requireAdmin()], function($router) {
            $router->get('', 'BPM\\Controllers\\CompanyController@index');
            $router->post('', 'BPM\\Controllers\\CompanyController@store');
            $router->get('/{id}', 'BPM\\Controllers\\CompanyController@show');
            $router->put('/{id}', 'BPM\\Controllers\\CompanyController@update');
            $router->delete('/{id}', 'BPM\\Controllers\\CompanyController@destroy');
        });
        
        // 사용자 관리
        $router->group('/users', [], function($router) {
            $router->get('', 'BPM\\Controllers\\UserController@index');
            $router->get('/{id}', 'BPM\\Controllers\\UserController@show');
        });
        
        // 부서 관리
        $router->group('/departments', [], function($router) {
            $router->get('', 'BPM\\Controllers\\DepartmentController@index');
            $router->post('', 'BPM\\Controllers\\DepartmentController@store');
            $router->get('/{id}', 'BPM\\Controllers\\DepartmentController@show');
            $router->put('/{id}', 'BPM\\Controllers\\DepartmentController@update');
            $router->delete('/{id}', 'BPM\\Controllers\\DepartmentController@destroy');
        });
        
        // 프로세스 관리
        $router->group('/processes', [], function($router) {
            $router->get('', 'BPM\\Controllers\\ProcessController@index');
            $router->post('', 'BPM\\Controllers\\ProcessController@store');
            $router->get('/{id}', 'BPM\\Controllers\\ProcessController@show');
            $router->put('/{id}', 'BPM\\Controllers\\ProcessController@update');
            $router->delete('/{id}', 'BPM\\Controllers\\ProcessController@destroy');
        });
        
        // 태스크 관리
        $router->group('/tasks', [], function($router) {
            $router->get('', 'BPM\\Controllers\\TaskController@index');
            $router->post('', 'BPM\\Controllers\\TaskController@store');
            $router->get('/{id}', 'BPM\\Controllers\\TaskController@show');
            $router->put('/{id}', 'BPM\\Controllers\\TaskController@update');
            $router->delete('/{id}', 'BPM\\Controllers\\TaskController@destroy');
        });
        
        // 파일 업로드
        $router->post('/files/upload', 'BPM\\Controllers\\FileController@upload');
        $router->delete('/files/{id}', 'BPM\\Controllers\\FileController@delete');
        
        // 대시보드 데이터
        $router->get('/dashboard/stats', 'BPM\\Controllers\\DashboardController@stats');
        $router->get('/dashboard/recent-activities', 'BPM\\Controllers\\DashboardController@recentActivities');
    });
});

// 웹 페이지 라우트
$router->get('/', function() {
    // 로그인 여부에 따라 대시보드 또는 로그인 페이지로 이동
    if (AuthManager::isLoggedIn()) {
        include __DIR__ . '/views/dashboard.php';
    } else {
        include __DIR__ . '/views/login.php';
    }
});

$router->get('/login', function() {
    if (AuthManager::isLoggedIn()) {
        redirect(url('/'));
    }
    include __DIR__ . '/views/login.php';
});

$router->get('/register', function() {
    if (AuthManager::isLoggedIn()) {
        redirect(url('/'));
    }
    include __DIR__ . '/views/register.php';
});

$router->get('/dashboard', [Middlewares::requireAuth()], function() {
    include __DIR__ . '/views/dashboard.php';
});

// 모듈별 페이지 라우트
$modules = ['organization', 'members', 'tasks', 'documents', 'processes', 'workflows', 'analytics'];

foreach ($modules as $module) {
    $router->get("/{$module}", [Middlewares::requireAuth()], function() use ($module) {
        $moduleFile = __DIR__ . "/modules/{$module}/index.php";
        if (file_exists($moduleFile)) {
            include $moduleFile;
        } else {
            ResponseHelper::error('모듈을 찾을 수 없습니다.', 404);
        }
    });
}

// 관리자 페이지
$router->get('/admin', [Middlewares::requireAuth(), Middlewares::requireAdmin()], function() {
    include __DIR__ . '/views/admin/index.php';
});

// 정적 파일 처리 (개발 환경용)
if (APP_ENV === 'development') {
    $requestUri = $_SERVER['REQUEST_URI'];
    $parsedUrl = parse_url($requestUri);
    $path = $parsedUrl['path'];
    
    // CSS, JS, 이미지 파일 등
    if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
        $filePath = __DIR__ . $path;
        if (file_exists($filePath)) {
            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'png' => 'image/png',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject'
            ];
            
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
            
            header("Content-Type: $mimeType");
            header('Cache-Control: public, max-age=31536000'); // 1년 캐시
            readfile($filePath);
            exit;
        }
    }
}

// 에러 처리를 위한 예외 핸들러
try {
    // 라우터 실행
    $router->dispatch();
} catch (Exception $e) {
    BPMLogger::error('라우팅 중 예외 발생', [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    if (APP_DEBUG) {
        echo "<h1>오류 발생</h1>";
        echo "<p><strong>메시지:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
        echo "<p><strong>파일:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
        echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    } else {
        http_response_code(500);
        echo view('errors.500');
    }
}

// 출력 버퍼 플러시
if (ob_get_level()) {
    ob_end_flush();
}