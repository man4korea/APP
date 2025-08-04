<?php
// 📁 C:\xampp\htdocs\BPM\api\auth.php
// Create at 2508030920 Ver1.00

/**
 * 인증 API 엔드포인트
 * POST /api/auth/login - 로그인
 * POST /api/auth/logout - 로그아웃
 * POST /api/auth/refresh - 토큰 갱신
 * GET /api/auth/me - 현재 사용자 정보
 */

// CORS 및 보안 헤더 설정
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// 기본 설정 로드
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../core/bootstrap.php';

use BPM\Core\Auth;
use BPM\Core\Security;

// Auth 및 Security 인스턴스 생성
$auth = Auth::getInstance();
$security = Security::getInstance();

// 요청 메소드 및 경로 파싱
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// API 경로 확인 (예: /api/auth/login)
if (count($pathParts) < 3 || $pathParts[0] !== 'api' || $pathParts[1] !== 'auth') {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => '요청한 API 엔드포인트를 찾을 수 없습니다.']);
    exit;
}

$endpoint = $pathParts[2] ?? '';

try {
    // Rate Limiting 적용
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!$security->checkRateLimit("api_auth_$clientIP", 30, 600)) { // 10분 동안 30회
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'API 요청 한도를 초과했습니다. 잠시 후 다시 시도해주세요.'
        ]);
        exit;
    }
    
    // 보안 헤더 설정
    $security->setSecurityHeaders();
    
    // 라우팅 처리
    switch ($endpoint) {
        case 'login':
            handleLogin($method, $auth, $security);
            break;
            
        case 'logout':
            handleLogout($method, $auth, $security);
            break;
            
        case 'refresh':
            handleRefresh($method, $auth, $security);
            break;
            
        case 'me':
            handleMe($method, $auth, $security);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => '지원하지 않는 API 엔드포인트입니다.'
            ]);
    }
    
} catch (Exception $e) {
    BPMLogger::error('Auth API 오류', [
        'endpoint' => $endpoint,
        'method' => $method,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '서버 오류가 발생했습니다.'
    ]);
}

/**
 * 로그인 처리
 */
function handleLogin(string $method, Auth $auth, Security $security): void
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'POST 메소드만 허용됩니다.']);
        return;
    }
    
    // JSON 입력 데이터 파싱
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => '잘못된 JSON 형식입니다.']);
        return;
    }
    
    // 필수 필드 검증
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $remember = $data['remember'] ?? false;
    
    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '이메일과 비밀번호는 필수입니다.',
            'errors' => [
                'email' => empty($email) ? '이메일은 필수입니다.' : null,
                'password' => empty($password) ? '비밀번호는 필수입니다.' : null
            ]
        ]);
        return;
    }
    
    // CSRF 토큰 검증 (폼 기반 요청에서만)
    if (isset($data['_token'])) {
        if (!$security->verifyCSRFToken($data['_token'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'CSRF 토큰이 유효하지 않습니다.']);
            return;
        }
    }
    
    // 로그인 처리
    $result = $auth->login($email, $password, $remember);
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(401);
        echo json_encode($result);
    }
}

/**
 * 로그아웃 처리
 */
function handleLogout(string $method, Auth $auth, Security $security): void
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'POST 메소드만 허용됩니다.']);
        return;
    }
    
    // 로그인 상태 확인
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '로그인 상태가 아닙니다.']);
        return;
    }
    
    // 로그아웃 처리
    if ($auth->logout()) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => '로그아웃되었습니다.'
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '로그아웃 처리 중 오류가 발생했습니다.'
        ]);
    }
}

/**
 * 토큰 갱신 처리
 */
function handleRefresh(string $method, Auth $auth, Security $security): void
{
    if ($method !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'POST 메소드만 허용됩니다.']);
        return;
    }
    
    // JWT 토큰 검증
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Bearer 토큰이 필요합니다.']);
        return;
    }
    
    $token = $matches[1];
    $payload = $security->verifyJWTToken($token);
    
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '유효하지 않은 토큰입니다.']);
        return;
    }
    
    // 토큰 갱신 처리
    $result = $auth->refreshToken();
    
    if ($result['success']) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(401);
        echo json_encode($result);
    }
}

/**
 * 현재 사용자 정보 조회
 */
function handleMe(string $method, Auth $auth, Security $security): void
{
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'GET 메소드만 허용됩니다.']);
        return;
    }
    
    // 로그인 상태 확인
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => '인증이 필요합니다.']);
        return;
    }
    
    // 현재 사용자 정보 반환
    $user = $auth->getCurrentUser();
    
    if ($user) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => '사용자 정보를 찾을 수 없습니다.'
        ]);
    }
}

/**
 * JSON 응답 헬퍼 함수
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

/**
 * 오류 응답 헬퍼 함수
 */
function jsonError(string $message, int $statusCode = 400, array $errors = []): void
{
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if (!empty($errors)) {
        $response['errors'] = $errors;
    }
    
    jsonResponse($response, $statusCode);
}
?>