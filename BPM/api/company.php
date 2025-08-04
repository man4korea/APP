<?php
// 📁 C:\xampp\htdocs\BPM\api\company.php
// Create at 2508040650 Ver1.00

require_once __DIR__ . '/../includes/config.php';

use BPM\Core\Auth;
use BPM\Core\Tenant;
use BPM\Core\Permission;
use BPM\Core\Database;
use BPM\Core\Middleware\TenantMiddleware;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Company-ID');

// CORS 프리플라이트 요청 처리
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $auth = Auth::getInstance();
    $tenant = Tenant::getInstance();
    $permission = Permission::getInstance();
    $tenantMiddleware = new TenantMiddleware();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    switch ($method) {
        case 'GET':
            handleGetRequest($pathParts, $auth, $tenant, $permission);
            break;
            
        case 'POST':
            handlePostRequest($pathParts, $auth, $tenant, $permission);
            break;
            
        case 'PUT':
            handlePutRequest($pathParts, $auth, $tenant, $permission);
            break;
            
        case 'DELETE':
            handleDeleteRequest($pathParts, $auth, $tenant, $permission);
            break;
            
        default:
            throw new Exception('Method not allowed', 405);
    }
    
} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code);
    echo json_encode([
        'error' => true,
        'code' => $code,
        'message' => $e->getMessage()
    ]);
}

/**
 * GET 요청 처리
 */
function handleGetRequest($pathParts, $auth, $tenant, $permission)
{
    $endpoint = $pathParts[2] ?? '';
    
    switch ($endpoint) {
        case 'current':
            // 현재 회사 정보 조회
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $company = $tenant->getCurrentCompany();
            if (!$company) {
                throw new Exception('No company context', 400);
            }
            
            echo json_encode([
                'success' => true,
                'data' => $company
            ]);
            break;
            
        case 'list':
            // 사용자의 소속 회사 목록
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $companies = $tenant->getUserCompanies($currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'data' => $companies
            ]);
            break;
            
        case 'stats':
            // 회사 통계 정보
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $companyId = $tenant->getCurrentCompanyId();
            if (!$companyId) {
                throw new Exception('Company context required', 400);
            }
            
            $stats = $tenant->getCompanyStats();
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        case 'search':
            // 회사 검색 (시스템 관리자 전용)
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            // 시스템 관리자 권한 확인 (추후 구현)
            $query = $_GET['q'] ?? '';
            $companies = searchCompanies($query);
            
            echo json_encode([
                'success' => true,
                'data' => $companies
            ]);
            break;
            
        case 'settings':
            // 회사 설정 조회
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'organization', 'edit')) {
                throw new Exception('Admin permission required', 403);
            }
            
            $company = $tenant->getCurrentCompany();
            $settings = $company['settings'] ?? [];
            
            echo json_encode([
                'success' => true,
                'data' => $settings
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * POST 요청 처리
 */
function handlePostRequest($pathParts, $auth, $tenant, $permission)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'register':
            // 새 회사 등록
            if (empty($input['company_name']) || empty($input['tax_number']) || 
                empty($input['admin_email']) || empty($input['representative_name'])) {
                throw new Exception('Required fields missing', 400);
            }
            
            // 이메일 형식 확인
            if (!filter_var($input['admin_email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format', 400);
            }
            
            $result = $tenant->createCompany($input);
            
            echo json_encode([
                'success' => true,
                'message' => 'Company registered successfully',
                'data' => $result
            ]);
            break;
            
        case 'switch':
            // 회사 전환
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $companyId = $input['company_id'] ?? null;
            if (!$companyId) {
                throw new Exception('Company ID required', 400);
            }
            
            // 사용자가 해당 회사에 접근 권한이 있는지 확인
            $userCompanies = $tenant->getUserCompanies($currentUser['id']);
            $hasAccess = false;
            foreach ($userCompanies as $company) {
                if ($company['id'] === $companyId) {
                    $hasAccess = true;
                    break;
                }
            }
            
            if (!$hasAccess) {
                throw new Exception('Access denied to company', 403);
            }
            
            $tenant->setCurrentTenant($companyId);
            
            echo json_encode([
                'success' => true,
                'message' => 'Company switched successfully',
                'data' => [
                    'company_id' => $companyId
                ]
            ]);
            break;
            
        case 'invite-admin':
            // 초기 관리자 초대 (회사 등록 후)
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            $email = $input['email'] ?? null;
            $role = $input['role'] ?? 'admin';
            
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid email required', 400);
            }
            
            $result = inviteCompanyAdmin($companyId, $email, $role, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Admin invitation sent',
                'data' => $result
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * PUT 요청 처리
 */
function handlePutRequest($pathParts, $auth, $tenant, $permission)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'settings':
            // 회사 설정 업데이트
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'organization', 'edit')) {
                throw new Exception('Admin permission required', 403);
            }
            
            $settings = $input['settings'] ?? [];
            $result = $tenant->updateCompanySettings($companyId, $settings);
            
            if (!$result) {
                throw new Exception('Failed to update settings', 500);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Settings updated successfully'
            ]);
            break;
            
        case 'status':
            // 회사 상태 변경 (시스템 관리자 전용)
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $companyId = $input['company_id'] ?? null;
            $status = $input['status'] ?? null;
            
            if (!$companyId || !$status) {
                throw new Exception('Company ID and status required', 400);
            }
            
            // 시스템 관리자 권한 확인 (추후 구현)
            $result = $tenant->updateCompanyStatus($companyId, $status);
            
            if (!$result) {
                throw new Exception('Failed to update company status', 500);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Company status updated successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * DELETE 요청 처리
 */
function handleDeleteRequest($pathParts, $auth, $tenant, $permission)
{
    $endpoint = $pathParts[2] ?? '';
    
    switch ($endpoint) {
        case 'deactivate':
            // 회사 비활성화 (창립자 전용)
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 창립자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'organization', 'delete')) {
                throw new Exception('Founder permission required', 403);
            }
            
            $result = $tenant->updateCompanyStatus($companyId, 'inactive');
            
            if (!$result) {
                throw new Exception('Failed to deactivate company', 500);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Company deactivated successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * 회사 검색
 */
function searchCompanies($query)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                id,
                company_name,
                tax_number,
                company_type,
                status,
                created_at
            FROM bpm_companies 
            WHERE company_name LIKE ? OR tax_number LIKE ?
            ORDER BY company_name
            LIMIT 50
        ");
        
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("searchCompanies Error: " . $e->getMessage());
        return [];
    }
}

/**
 * 회사 관리자 초대
 */
function inviteCompanyAdmin($companyId, $email, $role, $invitedBy)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 사용자가 이미 존재하는지 확인
        $stmt = $db->prepare("SELECT id, status FROM bpm_users WHERE email = ?");
        $stmt->execute([$email]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            // 기존 사용자를 회사에 추가
            $stmt = $db->prepare("
                INSERT INTO bpm_company_users (user_id, company_id, role_type, status, invited_by)
                VALUES (?, ?, ?, 'active', ?)
                ON DUPLICATE KEY UPDATE 
                role_type = VALUES(role_type),
                status = 'active',
                updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([$existingUser['id'], $companyId, $role, $invitedBy]);
            
            $userId = $existingUser['id'];
        } else {
            // 새 사용자 생성 (초대 상태)
            $userId = generateUUID();
            $tempPassword = bin2hex(random_bytes(16));
            
            $stmt = $db->prepare("
                INSERT INTO bpm_users (id, email, name, password, status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->execute([
                $userId,
                $email,
                explode('@', $email)[0], // 이메일에서 이름 추출
                password_hash($tempPassword, PASSWORD_ARGON2ID)
            ]);
            
            // 회사에 추가
            $stmt = $db->prepare("
                INSERT INTO bpm_company_users (user_id, company_id, role_type, status, invited_by)
                VALUES (?, ?, ?, 'pending', ?)
            ");
            $stmt->execute([$userId, $companyId, $role, $invitedBy]);
        }
        
        $db->commit();
        
        // 초대 이메일 발송 (추후 구현)
        // sendInvitationEmail($email, $companyId, $role);
        
        return [
            'user_id' => $userId,
            'email' => $email,
            'role' => $role,
            'status' => $existingUser ? 'added' : 'invited'
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("inviteCompanyAdmin Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * UUID 생성
 */
function generateUUID()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
?>