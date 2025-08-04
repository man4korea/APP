<?php
// 📁 C:\xampp\htdocs\BPM\api\users.php
// Create at 2508041125 Ver1.00

require_once __DIR__ . '/../includes/config.php';

use BPM\Core\Auth;
use BPM\Core\Tenant;
use BPM\Core\Permission;
use BPM\Core\Database;
use BPM\Core\Email;
use BPM\Core\Middleware\TenantMiddleware;
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
    $tenant = Tenant::getInstance();
    $permission = Permission::getInstance();
    $email = Email::getInstance();
    $tenantMiddleware = new TenantMiddleware();
    $permissionMiddleware = new PermissionMiddleware();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));
    
    switch ($method) {
        case 'GET':
            handleGetRequest($pathParts, $auth, $tenant, $permission);
            break;
            
        case 'POST':
            handlePostRequest($pathParts, $auth, $tenant, $permission, $email);
            break;
            
        case 'PUT':
            handlePutRequest($pathParts, $auth, $tenant, $permission, $email);
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
        case 'list':
            // 회사 사용자 목록 조회
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'view')) {
                throw new Exception('Permission denied', 403);
            }
            
            $users = getCompanyUsers($companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $users
            ]);
            break;
            
        case 'profile':
            // 사용자 프로필 조회
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $userId = $_GET['user_id'] ?? $currentUser['id'];
            $companyId = $tenant->getCurrentCompanyId();
            
            // 다른 사용자 프로필 조회 시 권한 확인
            if ($userId !== $currentUser['id']) {
                if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'view')) {
                    throw new Exception('Permission denied', 403);
                }
            }
            
            $profile = getUserProfile($userId, $companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $profile
            ]);
            break;
            
        case 'invitations':
            // 초대 목록 조회
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'invite')) {
                throw new Exception('Permission denied', 403);
            }
            
            $invitations = getCompanyInvitations($companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $invitations
            ]);
            break;
            
        case 'roles':
            // 사용 가능한 역할 목록
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            $availableRoles = getAvailableRoles($currentUser['id'], $companyId, $permission);
            
            echo json_encode([
                'success' => true,
                'data' => $availableRoles
            ]);
            break;
            
        case 'stats':
            // 사용자 통계
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'view')) {
                throw new Exception('Permission denied', 403);
            }
            
            $stats = getUserStats($companyId);
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * POST 요청 처리
 */
function handlePostRequest($pathParts, $auth, $tenant, $permission, $email)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'invite':
            // 사용자 초대
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 초대 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'invite')) {
                throw new Exception('Permission denied', 403);
            }
            
            $inviteEmail = $input['email'] ?? null;
            $roleType = $input['role_type'] ?? 'member';
            $department = $input['department'] ?? null;
            $jobTitle = $input['job_title'] ?? null;
            $message = $input['message'] ?? '';
            
            if (!$inviteEmail || !filter_var($inviteEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid email required', 400);
            }
            
            // 권한 레벨 확인 (자신보다 높은 권한 부여 불가)
            if (!canAssignRole($currentUser['id'], $companyId, $roleType, $permission)) {
                throw new Exception('Cannot assign higher role than your own', 403);
            }
            
            $result = inviteUser($companyId, $inviteEmail, $roleType, $department, $jobTitle, $message, $currentUser['id'], $email);
            
            echo json_encode([
                'success' => true,
                'message' => 'Invitation sent successfully',
                'data' => $result
            ]);
            break;
            
        case 'accept-invitation':
            // 초대 수락
            $token = $input['token'] ?? null;
            $userData = $input['user_data'] ?? [];
            
            if (!$token) {
                throw new Exception('Invitation token required', 400);
            }
            
            $result = acceptInvitation($token, $userData, $email);
            
            echo json_encode([
                'success' => true,
                'message' => 'Invitation accepted successfully',
                'data' => $result
            ]);
            break;
            
        case 'request-password-reset':
            // 비밀번호 재설정 요청
            $userEmail = $input['email'] ?? null;
            
            if (!$userEmail || !filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Valid email required', 400);
            }
            
            $result = requestPasswordReset($userEmail, $email);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password reset email sent if account exists'
            ]);
            break;
            
        case 'reset-password':
            // 비밀번호 재설정 실행
            $token = $input['token'] ?? null;
            $newPassword = $input['new_password'] ?? null;
            
            if (!$token || !$newPassword) {
                throw new Exception('Token and new password required', 400);
            }
            
            if (strlen($newPassword) < 8) {
                throw new Exception('Password must be at least 8 characters', 400);
            }
            
            $result = resetPassword($token, $newPassword);
            
            echo json_encode([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * PUT 요청 처리
 */
function handlePutRequest($pathParts, $auth, $tenant, $permission, $email)
{
    $endpoint = $pathParts[2] ?? '';
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($endpoint) {
        case 'profile':
            // 프로필 업데이트
            $currentUser = $auth->getCurrentUser();
            if (!$currentUser) {
                throw new Exception('Authentication required', 401);
            }
            
            $userId = $input['user_id'] ?? $currentUser['id'];
            $companyId = $tenant->getCurrentCompanyId();
            
            // 다른 사용자 프로필 수정 시 권한 확인
            if ($userId !== $currentUser['id']) {
                if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'edit')) {
                    throw new Exception('Permission denied', 403);
                }
            }
            
            $profileData = $input['profile_data'] ?? [];
            $result = updateUserProfile($userId, $companyId, $profileData, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $result
            ]);
            break;
            
        case 'role':
            // 사용자 권한 변경
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            $targetUserId = $input['user_id'] ?? null;
            $newRole = $input['new_role'] ?? null;
            
            if (!$targetUserId || !$newRole) {
                throw new Exception('User ID and new role required', 400);
            }
            
            // 권한 변경 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'manage_roles')) {
                throw new Exception('Permission denied', 403);
            }
            
            // 권한 레벨 확인
            if (!canAssignRole($currentUser['id'], $companyId, $newRole, $permission)) {
                throw new Exception('Cannot assign higher role than your own', 403);
            }
            
            $result = changeUserRole($targetUserId, $companyId, $newRole, $currentUser['id'], $email);
            
            echo json_encode([
                'success' => true,
                'message' => 'User role updated successfully',
                'data' => $result
            ]);
            break;
            
        case 'status':
            // 사용자 상태 변경
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'manage_status')) {
                throw new Exception('Permission denied', 403);
            }
            
            $targetUserId = $input['user_id'] ?? null;
            $newStatus = $input['new_status'] ?? null;
            
            if (!$targetUserId || !$newStatus) {
                throw new Exception('User ID and new status required', 400);
            }
            
            $result = changeUserStatus($targetUserId, $companyId, $newStatus, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'User status updated successfully'
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
        case 'remove':
            // 사용자 제거 (회사에서)
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 관리자 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'remove')) {
                throw new Exception('Permission denied', 403);
            }
            
            $targetUserId = $_GET['user_id'] ?? null;
            if (!$targetUserId) {
                throw new Exception('User ID required', 400);
            }
            
            $result = removeUserFromCompany($targetUserId, $companyId, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'User removed from company successfully'
            ]);
            break;
            
        case 'cancel-invitation':
            // 초대 취소
            $currentUser = $auth->getCurrentUser();
            $companyId = $tenant->getCurrentCompanyId();
            
            if (!$currentUser || !$companyId) {
                throw new Exception('Authentication and company context required', 401);
            }
            
            // 초대 권한 확인
            if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'invite')) {
                throw new Exception('Permission denied', 403);
            }
            
            $invitationId = $_GET['invitation_id'] ?? null;
            if (!$invitationId) {
                throw new Exception('Invitation ID required', 400);
            }
            
            $result = cancelInvitation($invitationId, $companyId, $currentUser['id']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Invitation cancelled successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid endpoint', 404);
    }
}

/**
 * 회사 사용자 목록 조회
 */
function getCompanyUsers($companyId)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                u.id,
                u.email,
                u.username,
                u.name,
                u.phone,
                u.status as user_status,
                u.last_login_at,
                cu.role_type,
                cu.department,
                cu.job_title,
                cu.employee_id,
                cu.status as company_status,
                cu.assigned_at,
                d.department_name
            FROM bpm_users u
            JOIN bpm_company_users cu ON u.id = cu.user_id
            LEFT JOIN bpm_departments d ON cu.department = d.department_name AND d.company_id = ?
            WHERE cu.company_id = ? AND cu.is_active = TRUE
            ORDER BY cu.role_type, u.name
        ");
        $stmt->execute([$companyId, $companyId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getCompanyUsers Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 사용자 프로필 조회
 */
function getUserProfile($userId, $companyId)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                u.id,
                u.email,
                u.username,
                u.name,
                u.first_name,
                u.last_name,
                u.phone,
                u.status,
                u.email_verified,
                u.last_login_at,
                u.login_count,
                cu.role_type,
                cu.department,
                cu.job_title,
                cu.employee_id,
                cu.assigned_at,
                d.department_name
            FROM bpm_users u
            LEFT JOIN bpm_company_users cu ON u.id = cu.user_id AND cu.company_id = ?
            LEFT JOIN bpm_departments d ON cu.department = d.department_name AND d.company_id = ?
            WHERE u.id = ?
        ");
        $stmt->execute([$companyId, $companyId, $userId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getUserProfile Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 회사 초대 목록 조회
 */
function getCompanyInvitations($companyId)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                id,
                email,
                role_type,
                department,
                job_title,
                status,
                invited_by,
                expires_at,
                created_at,
                (SELECT name FROM bpm_users WHERE id = invited_by) as inviter_name
            FROM bpm_user_invitations
            WHERE company_id = ? AND status IN ('pending', 'sent')
            ORDER BY created_at DESC
        ");
        $stmt->execute([$companyId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getCompanyInvitations Error: " . $e->getMessage());
        return [];
    }
}

/**
 * 사용 가능한 역할 목록 조회
 */
function getAvailableRoles($userId, $companyId, $permission)
{
    $userRole = $permission->getUserRole($userId, $companyId);
    $roleLevel = $permission->getRoleLevel($userRole);
    
    $allRoles = [
        'founder' => ['name' => '창립자', 'level' => 100],
        'admin' => ['name' => '관리자', 'level' => 80],
        'process_owner' => ['name' => '프로세스 담당자', 'level' => 60],
        'member' => ['name' => '일반 구성원', 'level' => 40]
    ];
    
    $availableRoles = [];
    foreach ($allRoles as $role => $info) {
        if ($info['level'] <= $roleLevel) {
            $availableRoles[$role] = $info['name'];
        }
    }
    
    return $availableRoles;
}

/**
 * 사용자 통계 조회
 */
function getUserStats($companyId)
{
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role_type = 'founder' THEN 1 ELSE 0 END) as founder_count,
                SUM(CASE WHEN role_type = 'admin' THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN role_type = 'process_owner' THEN 1 ELSE 0 END) as process_owner_count,
                SUM(CASE WHEN role_type = 'member' THEN 1 ELSE 0 END) as member_count,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN status = 'pending_approval' THEN 1 ELSE 0 END) as pending_users
            FROM bpm_company_users
            WHERE company_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$companyId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("getUserStats Error: " . $e->getMessage());
        return [];
    }
}

/**
 * 권한 할당 가능 여부 확인
 */
function canAssignRole($userId, $companyId, $targetRole, $permission)
{
    $userRole = $permission->getUserRole($userId, $companyId);
    $userLevel = $permission->getRoleLevel($userRole);
    $targetLevel = $permission->getRoleLevel($targetRole);
    
    return $userLevel >= $targetLevel;
}

/**
 * 사용자 초대
 */
function inviteUser($companyId, $email, $roleType, $department, $jobTitle, $message, $invitedBy, $emailService)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 중복 확인
        $stmt = $db->prepare("SELECT id FROM bpm_user_invitations WHERE company_id = ? AND email = ? AND status = 'pending'");
        $stmt->execute([$companyId, $email]);
        if ($stmt->fetch()) {
            throw new Exception('Invitation already sent to this email', 400);
        }
        
        // 초대 토큰 생성
        $inviteToken = bin2hex(random_bytes(32));
        $invitationId = generateUUID();
        
        // 초대 기록 저장
        $stmt = $db->prepare("
            INSERT INTO bpm_user_invitations (
                id, company_id, email, role_type, department, job_title, 
                message, invite_token, status, invited_by, expires_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, DATE_ADD(NOW(), INTERVAL 7 DAY))
        ");
        $stmt->execute([
            $invitationId, $companyId, $email, $roleType, $department, 
            $jobTitle, $message, $inviteToken, $invitedBy
        ]);
        
        // 회사 정보 조회
        $stmt = $db->prepare("SELECT company_name FROM bmp_companies WHERE id = ?");
        $stmt->execute([$companyId]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 초대자 정보 조회
        $stmt = $db->prepare("SELECT name FROM bpm_users WHERE id = ?");
        $stmt->execute([$invitedBy]);
        $inviter = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // 이메일 발송
        $emailSent = $emailService->sendCompanyInvitation(
            $email, 
            $company['company_name'], 
            $inviter['name'], 
            $inviteToken
        );
        
        if ($emailSent) {
            $stmt = $db->prepare("UPDATE bpm_user_invitations SET status = 'sent' WHERE id = ?");
            $stmt->execute([$invitationId]);
        }
        
        $db->commit();
        
        return [
            'invitation_id' => $invitationId,
            'email' => $email,
            'role_type' => $roleType,
            'email_sent' => $emailSent
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("inviteUser Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 초대 수락
 */
function acceptInvitation($token, $userData, $emailService)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 초대 정보 조회
        $stmt = $db->prepare("
            SELECT * FROM bpm_user_invitations 
            WHERE invite_token = ? AND status IN ('pending', 'sent') AND expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $invitation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$invitation) {
            throw new Exception('Invalid or expired invitation', 400);
        }
        
        // 사용자 생성 또는 업데이트
        $stmt = $db->prepare("SELECT id FROM bpm_users WHERE email = ?");
        $stmt->execute([$invitation['email']]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingUser) {
            $userId = $existingUser['id'];
        } else {
            // 새 사용자 생성
            $userId = generateUUID();
            $password = password_hash($userData['password'], PASSWORD_ARGON2ID);
            
            $stmt = $db->prepare("
                INSERT INTO bpm_users (id, email, username, name, password, status)
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            $stmt->execute([
                $userId,
                $invitation['email'],
                $userData['username'] ?? explode('@', $invitation['email'])[0],
                $userData['name'] ?? explode('@', $invitation['email'])[0],
                $password
            ]);
        }
        
        // 회사에 사용자 추가
        $stmt = $db->prepare("
            INSERT INTO bpm_company_users (
                user_id, company_id, role_type, department, job_title, 
                status, assigned_by, assigned_at
            ) VALUES (?, ?, ?, ?, ?, 'active', ?, NOW())
            ON DUPLICATE KEY UPDATE
            role_type = VALUES(role_type),
            department = VALUES(department),
            job_title = VALUES(job_title),
            status = 'active'
        ");
        $stmt->execute([
            $userId,
            $invitation['company_id'],
            $invitation['role_type'],
            $invitation['department'],
            $invitation['job_title'],
            $invitation['invited_by']
        ]);
        
        // 초대 상태 업데이트
        $stmt = $db->prepare("UPDATE bpm_user_invitations SET status = 'accepted' WHERE id = ?");
        $stmt->execute([$invitation['id']]);
        
        $db->commit();
        
        return [
            'user_id' => $userId,
            'company_id' => $invitation['company_id'],
            'role_type' => $invitation['role_type']
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("acceptInvitation Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 비밀번호 재설정 요청
 */
function requestPasswordReset($email, $emailService)
{
    try {
        $db = Database::getInstance()->getConnection();
        
        // 사용자 확인
        $stmt = $db->prepare("SELECT id, name, status FROM bpm_users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || $user['status'] !== 'active') {
            // 보안상 이유로 성공 메시지 반환
            return true;
        }
        
        // 재설정 토큰 생성
        $resetToken = bin2hex(random_bytes(32));
        
        // 토큰 저장
        $stmt = $db->prepare("
            INSERT INTO bpm_password_resets (user_id, token, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))
            ON DUPLICATE KEY UPDATE 
            token = VALUES(token), 
            expires_at = VALUES(expires_at),
            created_at = NOW()
        ");
        $stmt->execute([$user['id'], $resetToken]);
        
        // 이메일 발송
        $emailService->sendPasswordReset($email, $user['name'], $resetToken);
        
        return true;
        
    } catch (Exception $e) {
        error_log("requestPasswordReset Error: " . $e->getMessage());
        return true; // 보안상 이유로 성공 메시지 반환
    }
}

/**
 * 비밀번호 재설정 실행
 */
function resetPassword($token, $newPassword)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 토큰 확인
        $stmt = $db->prepare("
            SELECT pr.user_id 
            FROM bpm_password_resets pr 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used = FALSE
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset) {
            throw new Exception('Invalid or expired reset token', 400);
        }
        
        // 비밀번호 업데이트
        $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
        $stmt = $db->prepare("UPDATE bpm_users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $reset['user_id']]);
        
        // 토큰 사용 처리
        $stmt = $db->prepare("UPDATE bpm_password_resets SET used = TRUE WHERE token = ?");
        $stmt->execute([$token]);
        
        $db->commit();
        
        return true;
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("resetPassword Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 사용자 프로필 업데이트
 */
function updateUserProfile($userId, $companyId, $profileData, $updatedBy)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 사용자 기본 정보 업데이트
        if (isset($profileData['name']) || isset($profileData['phone'])) {
            $updateFields = [];
            $updateValues = [];
            
            if (isset($profileData['name'])) {
                $updateFields[] = "name = ?";
                $updateValues[] = $profileData['name'];
            }
            
            if (isset($profileData['phone'])) {
                $updateFields[] = "phone = ?";
                $updateValues[] = $profileData['phone'];
            }
            
            $updateValues[] = $userId;
            
            $stmt = $db->prepare("UPDATE bpm_users SET " . implode(', ', $updateFields) . " WHERE id = ?");
            $stmt->execute($updateValues);
        }
        
        // 회사별 정보 업데이트
        if (isset($profileData['department']) || isset($profileData['job_title']) || isset($profileData['employee_id'])) {
            $updateFields = [];
            $updateValues = [];
            
            if (isset($profileData['department'])) {
                $updateFields[] = "department = ?";
                $updateValues[] = $profileData['department'];
            }
            
            if (isset($profileData['job_title'])) {
                $updateFields[] = "job_title = ?";
                $updateValues[] = $profileData['job_title'];
            }
            
            if (isset($profileData['employee_id'])) {
                $updateFields[] = "employee_id = ?";
                $updateValues[] = $profileData['employee_id'];
            }
            
            $updateValues[] = $userId;
            $updateValues[] = $companyId;
            
            $stmt = $db->prepare("UPDATE bpm_company_users SET " . implode(', ', $updateFields) . " WHERE user_id = ? AND company_id = ?");
            $stmt->execute($updateValues);
        }
        
        $db->commit();
        
        return getUserProfile($userId, $companyId);
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("updateUserProfile Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 사용자 권한 변경
 */
function changeUserRole($userId, $companyId, $newRole, $changedBy, $emailService)
{
    try {
        $db = Database::getInstance()->getConnection();
        $db->beginTransaction();
        
        // 기존 권한 조회
        $stmt = $db->prepare("SELECT role_type FROM bpm_company_users WHERE user_id = ? AND company_id = ?");
        $stmt->execute([$userId, $companyId]);
        $oldRole = $stmt->fetchColumn();
        
        if (!$oldRole) {
            throw new Exception('User not found in company', 404);
        }
        
        // 권한 업데이트
        $stmt = $db->prepare("UPDATE bpm_company_users SET role_type = ? WHERE user_id = ? AND company_id = ?");
        $stmt->execute([$newRole, $userId, $companyId]);
        
        // 권한 변경 로그
        $stmt = $db->prepare("
            INSERT INTO bpm_role_change_logs (company_id, target_user_id, changed_by, action, old_role, new_role)
            VALUES (?, ?, ?, 'modify', ?, ?)
        ");
        $stmt->execute([$companyId, $userId, $changedBy, $oldRole, $newRole]);
        
        // 사용자 정보 조회 (이메일 알림用)
        $stmt = $db->prepare("
            SELECT u.email, u.name, c.company_name, (SELECT name FROM bpm_users WHERE id = ?) as changer_name
            FROM bpm_users u, bpm_companies c
            WHERE u.id = ? AND c.id = ?
        ");
        $stmt->execute([$changedBy, $userId, $companyId]);
        $info = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $db->commit();
        
        // 권한 변경 알림 이메일 발송
        if ($info) {
            $emailService->sendRoleChangeNotification(
                $info['email'],
                $info['name'],
                $info['company_name'],
                $oldRole,
                $newRole,
                $info['changer_name']
            );
        }
        
        return [
            'user_id' => $userId,
            'old_role' => $oldRole,
            'new_role' => $newRole
        ];
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("changeUserRole Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 사용자 상태 변경
 */
function changeUserStatus($userId, $companyId, $newStatus, $changedBy)
{
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE bpm_company_users SET status = ? WHERE user_id = ? AND company_id = ?");
        $stmt->execute([$newStatus, $userId, $companyId]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("changeUserStatus Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 회사에서 사용자 제거
 */
function removeUserFromCompany($userId, $companyId, $removedBy)
{
    try {
        $db = Database::getInstance()->getConnection();
        
        // 소프트 삭제
        $stmt = $db->prepare("UPDATE bpm_company_users SET is_active = FALSE WHERE user_id = ? AND company_id = ?");
        $stmt->execute([$userId, $companyId]);
        
        // 로그 기록
        $stmt = $db->prepare("
            INSERT INTO bpm_role_change_logs (company_id, target_user_id, changed_by, action)
            VALUES (?, ?, ?, 'revoke')
        ");
        $stmt->execute([$companyId, $userId, $removedBy]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("removeUserFromCompany Error: " . $e->getMessage());
        throw $e;
    }
}

/**
 * 초대 취소
 */
function cancelInvitation($invitationId, $companyId, $cancelledBy)
{
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("UPDATE bpm_user_invitations SET status = 'cancelled' WHERE id = ? AND company_id = ?");
        $stmt->execute([$invitationId, $companyId]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("cancelInvitation Error: " . $e->getMessage());
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