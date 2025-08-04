<?php
// 📁 C:\xampp\htdocs\BPM\api\permissions.php
// Create at 2508040625 Ver1.00

require_once __DIR__ . '/../includes/config.php';

use BPM\Core\Auth;
use BPM\Core\Permission;
use BPM\Core\Database;
use BPM\Core\Middleware\PermissionMiddleware;

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
    $permission = Permission::getInstance();
    $permissionMiddleware = new PermissionMiddleware();
    
    // 인증 확인
    $currentUser = $auth->getCurrentUser();
    if (!$currentUser) {
        throw new Exception('Authentication required', 401);
    }
    
    // 회사 ID 확인
    $companyId = $_SERVER['HTTP_X_COMPANY_ID'] ?? $_GET['company_id'] ?? $_SESSION['company_id'] ?? null;
    if (!$companyId) {
        throw new Exception('Company context required', 400);
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    // 기본 권한 확인 (관리자 이상만 권한 관리 API 접근 가능)
    if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'edit')) {
        throw new Exception('Admin permission required for permission management', 403);
    }
    
    switch ($method) {
        case 'GET':
            handleGetRequest($pathParts, $permission, $currentUser, $companyId);
            break;
            
        case 'POST':
            handlePostRequest($pathParts, $permission, $currentUser, $companyId);
            break;
            
        case 'PUT':
            handlePutRequest($pathParts, $permission, $currentUser, $companyId);
            break;
            
        case 'DELETE':
            handleDeleteRequest($pathParts, $permission, $currentUser, $companyId);
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
function handleGetRequest($pathParts, $permission, $currentUser, $companyId)
{
    $endpoint = $pathParts[2] ?? '';
    
    switch ($endpoint) {
        case 'user-permissions':
            // 특정 사용자 권한 조회
            $userId = $_GET['user_id'] ?? $currentUser['id'];
            $permissions = $permission->debugUserPermissions($userId, $companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $permissions
            ]);
            break;
            
        case 'accessible-modules':
            // 접근 가능한 모듈 목록
            $userId = $_GET['user_id'] ?? $currentUser['id'];
            $modules = $permission->getAccessibleModules($userId, $companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $modules
            ]);
            break;
            
        case 'all-roles':
            // 모든 권한 역할 목록
            $roles = Permission::getAllRoles();
            
            echo json_encode([
                'success' => true,
                'data' => $roles
            ]);
            break;
            
        case 'company-users':
            // 회사 구성원 및 권한 목록
            $users = getCompanyUsersWithRoles($companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;
            
        case 'module-permissions':
            // 모듈별 권한 매트릭스
            $matrix = Permission::MODULE_PERMISSIONS;
            
            echo json_encode([
                'success' => true,
                'data' => $matrix
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * POST 요청 처리
 */
function handlePostRequest($pathParts, $permission, $currentUser, $companyId)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'assign-role':
            // 사용자에게 역할 할당
            $userId = $input['user_id'] ?? null;
            $role = $input['role'] ?? null;
            
            if (!$userId || !$role) {
                throw new Exception('User ID and role required', 400);
            }
            
            // 창립자만 다른 창립자를 생성할 수 있음
            if ($role === Permission::ROLE_FOUNDER) {
                $currentUserRole = $permission->getUserRole($currentUser['id'], $companyId);
                if ($currentUserRole !== Permission::ROLE_FOUNDER) {
                    throw new Exception('Only founders can assign founder role', 403);
                }
            }
            
            $result = assignUserRole($userId, $companyId, $role);
            
            echo json_encode([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => $result
            ]);
            break;
            
        case 'check-permission':
            // 권한 확인
            $userId = $input['user_id'] ?? null;
            $module = $input['module'] ?? null;
            $action = $input['action'] ?? 'view';
            
            if (!$userId || !$module) {
                throw new Exception('User ID and module required', 400);
            }
            
            $hasPermission = $permission->hasModulePermission($userId, $companyId, $module, $action);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'has_permission' => $hasPermission,
                    'user_id' => $userId,
                    'module' => $module,
                    'action' => $action
                ]
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * PUT 요청 처리
 */
function handlePutRequest($pathParts, $permission, $currentUser, $companyId)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'update-role':
            // 사용자 역할 업데이트
            $userId = $input['user_id'] ?? null;
            $newRole = $input['role'] ?? null;
            
            if (!$userId || !$newRole) {
                throw new Exception('User ID and role required', 400);
            }
            
            // 자기 자신의 권한은 변경할 수 없음
            if ($userId === $currentUser['id']) {
                throw new Exception('Cannot modify your own role', 403);
            }
            
            $result = updateUserRole($userId, $companyId, $newRole);
            
            echo json_encode([
                'success' => true,
                'message' => 'Role updated successfully',
                'data' => $result
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * DELETE 요청 처리
 */
function handleDeleteRequest($pathParts, $permission, $currentUser, $companyId)
{
    $endpoint = $pathParts[2] ?? '';
    
    switch ($endpoint) {
        case 'remove-user':
            // 회사에서 사용자 제거
            $userId = $_GET['user_id'] ?? null;
            
            if (!$userId) {
                throw new Exception('User ID required', 400);
            }
            
            // 자기 자신은 제거할 수 없음
            if ($userId === $currentUser['id']) {
                throw new Exception('Cannot remove yourself', 403);
            }
            
            $result = removeUserFromCompany($userId, $companyId);
            
            echo json_encode([
                'success' => true,
                'message' => 'User removed successfully',
                'data' => $result
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * 회사 구성원 및 권한 목록 조회
 */
function getCompanyUsersWithRoles($companyId)
{
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.email,
            u.name,
            u.status as user_status,
            cu.role_type,
            cu.status as company_status,
            cu.joined_at,
            cu.updated_at
        FROM bpm_users u
        JOIN bpm_company_users cu ON u.id = cu.user_id
        WHERE cu.company_id = ? AND cu.status = 'active'
        ORDER BY 
            FIELD(cu.role_type, 'founder', 'admin', 'process_owner', 'member'),
            u.name
    ");
    
    $stmt->execute([$companyId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 권한 표시명 추가
    foreach ($users as &$user) {
        $user['role_display'] = Permission::getRoleDisplayName($user['role_type']);
        $user['role_level'] = Permission::ROLE_LEVELS[$user['role_type']] ?? 0;
    }
    
    return $users;
}

/**
 * 사용자에게 역할 할당
 */
function assignUserRole($userId, $companyId, $role)
{
    $db = Database::getInstance()->getConnection();
    
    // 이미 존재하는지 확인
    $stmt = $db->prepare("
        SELECT id FROM bpm_company_users 
        WHERE user_id = ? AND company_id = ?
    ");
    $stmt->execute([$userId, $companyId]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        // 기존 역할 업데이트
        $stmt = $db->prepare("
            UPDATE bpm_company_users 
            SET role_type = ?, status = 'active', updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ? AND company_id = ?
        ");
        $stmt->execute([$role, $userId, $companyId]);
    } else {
        // 새 역할 할당
        $stmt = $db->prepare("
            INSERT INTO bpm_company_users (user_id, company_id, role_type, status)
            VALUES (?, ?, ?, 'active')
        ");
        $stmt->execute([$userId, $companyId, $role]);
    }
    
    return [
        'user_id' => $userId,
        'company_id' => $companyId,
        'role' => $role,
        'role_display' => Permission::getRoleDisplayName($role)
    ];
}

/**
 * 사용자 역할 업데이트
 */
function updateUserRole($userId, $companyId, $newRole)
{
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        UPDATE bmp_company_users 
        SET role_type = ?, updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ? AND company_id = ? AND status = 'active'
    ");
    
    $stmt->execute([$newRole, $userId, $companyId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('User not found or not active in company', 404);
    }
    
    return [
        'user_id' => $userId,
        'company_id' => $companyId,
        'new_role' => $newRole,
        'role_display' => Permission::getRoleDisplayName($newRole)
    ];
}

/**
 * 회사에서 사용자 제거
 */
function removeUserFromCompany($userId, $companyId)
{
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        UPDATE bpm_company_users 
        SET status = 'inactive', updated_at = CURRENT_TIMESTAMP
        WHERE user_id = ? AND company_id = ?
    ");
    
    $stmt->execute([$userId, $companyId]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('User not found in company', 404);
    }
    
    return [
        'user_id' => $userId,
        'company_id' => $companyId,
        'status' => 'removed'
    ];
}
?>