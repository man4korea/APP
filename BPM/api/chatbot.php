<?php
// 📁 C:\xampp\htdocs\BPM\api\chatbot.php
// Create at 2508031210 Ver1.00

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

use BPM\Core\BPMChatbot;
use BPM\Core\Auth;
use BPM\Core\Security;

try {
    // 인증 및 보안 초기화
    $auth = Auth::getInstance();
    $security = Security::getInstance();
    $chatbot = BPMChatbot::getInstance();
    
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
    $rateLimitKey = "chatbot_api_" . ($user['id'] ?? 'anonymous');
    
    if (!$security->checkRateLimit($rateLimitKey, 60, 3600)) { // 시간당 60회
        http_response_code(429);
        echo json_encode([
            'success' => false,
            'message' => '챗봇 사용이 너무 빈번합니다. 잠시 후 다시 시도해주세요.'
        ]);
        exit;
    }
    
    // 요청 메서드별 처리
    $method = $_SERVER['REQUEST_METHOD'];
    $path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
    $pathParts = explode('/', $path);
    
    // URL 파라미터 추출: /BPM/api/chatbot/{action}
    $action = $pathParts[3] ?? null;
    
    switch ($method) {
        case 'GET':
            handleGet($chatbot, $action);
            break;
            
        case 'POST':
            handlePost($chatbot, $action);
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
    BPMLogger::error('챗봇 API 오류', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'request' => $_REQUEST
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => '챗봇 서비스 처리 중 오류가 발생했습니다.'
    ]);
}

/**
 * GET 요청 처리
 */
function handleGet(BPMChatbot $chatbot, ?string $action): void
{
    switch ($action) {
        case 'history':
            // 대화 기록 조회
            handleChatHistory();
            break;
            
        case 'feedback':
            // 피드백 목록 조회 (관리자만)
            handleFeedbackList();
            break;
            
        case 'status':
            // 챗봇 상태 및 사용량 정보
            handleStatus();
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
function handlePost(BPMChatbot $chatbot, ?string $action): void
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
    
    switch ($action) {
        case 'chat':
            // 챗봇 대화
            handleChat($chatbot, $requestData);
            break;
            
        case 'feedback':
            // 피드백 저장
            handleSubmitFeedback($chatbot, $requestData);
            break;
            
        case 'manual':
            // 메뉴얼 검색
            handleManualSearch($chatbot, $requestData);
            break;
            
        default:
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => '지원하지 않는 POST 액션입니다.'
            ]);
            break;
    }
}

/**
 * 챗봇 대화 처리
 */
function handleChat(BPMChatbot $chatbot, array $requestData): void
{
    if (empty($requestData['message'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '메시지가 필요합니다.'
        ]);
        return;
    }
    
    $message = $requestData['message'];
    $context = $requestData['context'] ?? null;
    $options = $requestData['options'] ?? [];
    
    $response = $chatbot->processMessage($message, $context, $options);
    
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
}

/**
 * 피드백 제출 처리
 */
function handleSubmitFeedback(BPMChatbot $chatbot, array $requestData): void
{
    $requiredFields = ['type', 'title', 'description'];
    
    foreach ($requiredFields as $field) {
        if (empty($requestData[$field])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => "필수 필드가 누락되었습니다: {$field}"
            ]);
            return;
        }
    }
    
    $response = $chatbot->saveFeedback($requestData);
    
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
}

/**
 * 메뉴얼 검색 처리
 */
function handleManualSearch(BPMChatbot $chatbot, array $requestData): void
{
    if (empty($requestData['query'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => '검색 쿼리가 필요합니다.'
        ]);
        return;
    }
    
    $response = $chatbot->searchManual($requestData['query']);
    
    echo json_encode([
        'success' => true,
        'data' => $response
    ]);
}

/**
 * 대화 기록 조회
 */
function handleChatHistory(): void
{
    try {
        $auth = Auth::getInstance();
        $user = $auth->getCurrentUser();
        $database = Database::getInstance();
        
        $limit = min(intval($_GET['limit'] ?? 20), 50);
        $offset = max(intval($_GET['offset'] ?? 0), 0);
        
        $stmt = $database->prepare("
            SELECT user_message, bot_response, intent, context_page, created_at
            FROM bpm_chatbot_history 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute([$user['id'], $limit, $offset]);
        $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // JSON 디코딩
        foreach ($history as &$item) {
            $item['bot_response'] = json_decode($item['bot_response'], true);
        }
        
        echo json_encode([
            'success' => true,
            'data' => $history
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '대화 기록 조회 중 오류가 발생했습니다.'
        ]);
    }
}

/**
 * 피드백 목록 조회 (관리자만)
 */
function handleFeedbackList(): void
{
    try {
        $auth = Auth::getInstance();
        $user = $auth->getCurrentUser();
        
        // 관리자 권한 확인
        if (!in_array($user['role'], ['admin', 'founder'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => '관리자 권한이 필요합니다.'
            ]);
            return;
        }
        
        $database = Database::getInstance();
        $status = $_GET['status'] ?? 'all';
        $limit = min(intval($_GET['limit'] ?? 20), 100);
        $offset = max(intval($_GET['offset'] ?? 0), 0);
        
        $whereClause = $status !== 'all' ? "WHERE f.status = ?" : "";
        $params = $status !== 'all' ? [$status] : [];
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $database->prepare("
            SELECT 
                f.id, f.feedback_type, f.title, f.description, f.priority, f.status,
                f.page_url, f.created_at, f.updated_at,
                u.name as user_name, u.email as user_email,
                c.company_name
            FROM bpm_user_feedback f
            JOIN bmp_users u ON f.user_id = u.id
            JOIN bpm_companies c ON f.company_id = c.id
            {$whereClause}
            ORDER BY 
                CASE f.priority 
                    WHEN 'critical' THEN 1 
                    WHEN 'high' THEN 2 
                    WHEN 'medium' THEN 3 
                    ELSE 4 
                END,
                f.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute($params);
        $feedback = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $feedback
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '피드백 목록 조회 중 오류가 발생했습니다.'
        ]);
    }
}

/**
 * 챗봇 상태 및 사용량 정보
 */
function handleStatus(): void
{
    try {
        $auth = Auth::getInstance();
        $user = $auth->getCurrentUser();
        $database = Database::getInstance();
        
        // 오늘 사용량
        $stmt = $database->prepare("
            SELECT COUNT(*) as today_count
            FROM bpm_chatbot_history 
            WHERE user_id = ? AND DATE(created_at) = CURDATE()
        ");
        $stmt->execute([$user['id']]);
        $todayUsage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 이번 주 사용량
        $stmt = $database->prepare("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM bmp_chatbot_history 
            WHERE user_id = ? AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute([$user['id']]);
        $weeklyUsage = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'usage' => [
                    'today' => $todayUsage['today_count'] ?? 0,
                    'weekly' => $weeklyUsage,
                    'daily_limit' => 50
                ],
                'status' => 'active',
                'features' => [
                    'help_guide' => true,
                    'manual_search' => true,
                    'feedback_collection' => true,
                    'context_aware' => true
                ]
            ]
        ]);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => '상태 정보 조회 중 오류가 발생했습니다.'
        ]);
    }
}
?>