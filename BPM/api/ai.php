<?php
// 📁 C:\xampp\htdocs\BPM\api\ai.php
// Create at 2508031110 Ver1.00

require_once __DIR__ . '/../includes/config.php';

// CORS 헤더 설정
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// OPTIONS 요청 처리 (CORS Preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

use BPM\Core\BPMAIHelper;
use BPM\Core\Auth;
use BPM\Core\Security;

try {
    // 인증 및 보안 초기화
    $auth = Auth::getInstance();
    $security = Security::getInstance();
    $aiHelper = BPMAIHelper::getInstance();
    
    // 로그인 확인
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => '로그인이 필요합니다.'
        ]);
        exit;
    }
    
    // Rate Limiting
    $user = $auth->getCurrentUser();
    $rateLimitKey = "ai_api_" . ($user['id'] ?? 'anonymous');
    
    if (!$security->checkRateLimit($rateLimitKey, 30, 3600)) { // 시간당 30회
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => 'AI 요청이 너무 빈번합니다. 잠시 후 다시 시도해주세요.'
        ]);
        exit;
    }
    
    // 요청 메서드별 처리
    $method = $_SERVER['REQUEST_METHOD'];
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $pathParts = explode('/', $path);
    
    // URL 파라미터 추출: /BPM/api/ai/{module}/{action}
    $module = $pathParts[3] ?? null;
    $action = $pathParts[4] ?? null;
    
    switch ($method) {
        case 'GET':
            handleGet($aiHelper, $module, $action);
            break;
            
        case 'POST':
            handlePost($aiHelper, $module, $action);
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => '지원하지 않는 HTTP 메서드입니다.'
            ]);
            break;
    }
    
} catch (Exception $e) {
    BPMLogger::error('AI API 오류', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request' => $_REQUEST
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI 서비스 처리 중 오류가 발생했습니다.'
    ]);
}

/**
 * GET 요청 처리
 */
function handleGet(BPMAIHelper $aiHelper, ?string $module, ?string $action): void
{
    switch ($action) {
        case 'modules':
            // 사용 가능한 모듈 목록 반환
            echo json_encode([
                'success' => true,
                'data' => [
                    'modules' => $aiHelper->getAvailableModules(),
                    'colors' => array_map([$aiHelper, 'getModuleColor'], array_keys($aiHelper->getAvailableModules()))
                ]
            ]);
            break;
            
        case 'features':
            // 모듈별 AI 기능 목록 반환
            if (!$module) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => '모듈명이 필요합니다.'
                ]);
                return;
            }
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'module' => $module,
                    'features' => $aiHelper->getModuleAIFeatures($module),
                    'color' => $aiHelper->getModuleColor($module)
                ]
            ]);
            break;
            
        case 'usage':
            // AI 사용량 통계 반환
            handleUsageStats();
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '지원하지 않는 GET 액션입니다.'
            ]);
            break;
    }
}

/**
 * POST 요청 처리
 */
function handlePost(BPMAIHelper $aiHelper, ?string $module, ?string $action): void
{
    // JSON 입력 데이터 파싱
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'JSON 형식이 올바르지 않습니다.'
        ]);
        return;
    }
    
    if (!$module || !$action) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '모듈명과 액션이 필요합니다.'
        ]);
        return;
    }
    
    // AI 요청 처리
    $result = $aiHelper->processAIRequest($module, $action, $requestData);
    
    // HTTP 상태 코드 설정
    $httpCode = $result['success'] ? 200 : 400;
    http_response_code($httpCode);
    
    echo json_encode($result);
}

/**
 * 사용량 통계 처리
 */
function handleUsageStats(): void
{
    try {
        $auth = Auth::getInstance();
        $user = $auth->getCurrentUser();
        $companyId = $user['company_id'] ?? null;
        
        if (!$companyId) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '회사 정보를 찾을 수 없습니다.'
            ]);
            return;
        }
        
        $database = Database::getInstance();
        
        // 오늘 사용량
        $stmt = $database->prepare("
            SELECT 
                module,
                COUNT(*) as request_count,
                SUM(tokens_used) as total_tokens,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count
            FROM bpm_ai_usage_logs 
            WHERE company_id = ? AND DATE(created_at) = CURDATE()
            GROUP BY module
        ");
        $stmt->execute([$companyId]);
        $todayUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 이번 주 사용량
        $stmt = $database->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as request_count,
                SUM(tokens_used) as total_tokens
            FROM bmp_ai_usage_logs 
            WHERE company_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$companyId]);
        $weeklyUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'today' => $todayUsage,
                'weekly' => $weeklyUsage,
                'limits' => [
                    'daily_limit' => 100,
                    'hourly_limit' => 30
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        BPMLogger::error('AI 사용량 통계 조회 오류', [
            'error' => $e->getMessage()
        ]);
        
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '사용량 통계 조회 중 오류가 발생했습니다.'
        ]);
    }
}

/**
 * CSRF 토큰 검증 (POST 요청용)
 */
function validateCSRFToken(): bool
{
    $security = Security::getInstance();
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    
    return $security->validateCSRFToken($token);
}

/**
 * 입력 데이터 검증
 */
function validateAIRequest(array $data): array
{
    $errors = [];
    
    // 기본 필드 검증
    if (empty($data)) {
        $errors[] = '요청 데이터가 비어있습니다.';
    }
    
    // 데이터 크기 제한 (1MB)
    $maxSize = 1024 * 1024; // 1MB
    if (strlen(json_encode($data)) > $maxSize) {
        $errors[] = '요청 데이터가 너무 큽니다. (최대 1MB)';
    }
    
    return $errors;
}

/**
 * 응답 데이터 정제
 */
function sanitizeResponse(array $response): array
{
    // 민감한 정보 제거
    unset($response['internal_debug']);
    unset($response['raw_gemini_response']);
    
    return $response;
}
?>