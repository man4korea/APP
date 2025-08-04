<?php
// 📁 C:\xampp\htdocs\BPM\core\Middleware\PermissionMiddleware.php
// Create at 2508040620 Ver1.00

namespace BPM\Core\Middleware;

use BPM\Core\Permission;
use BPM\Core\Auth;

/**
 * 권한 검증 미들웨어
 * API 엔드포인트별 권한 확인 및 접근 제어
 */
class PermissionMiddleware
{
    private Permission $permission;
    private Auth $auth;

    public function __construct()
    {
        $this->permission = Permission::getInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 모듈 권한 확인 미들웨어
     */
    public function requireModulePermission(string $module, string $action = 'view')
    {
        return function($request, $response, $next) use ($module, $action) {
            try {
                // 인증 확인
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required');
                }

                // 현재 회사 ID 확인
                $companyId = $this->getCurrentCompanyId($request);
                if (!$companyId) {
                    return $this->forbidden($response, 'Company context required');
                }

                // 권한 확인
                if (!$this->permission->hasModulePermission($user['id'], $companyId, $module, $action)) {
                    return $this->forbidden($response, "Insufficient permission for {$module}.{$action}");
                }

                // 미들웨어 통과
                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("PermissionMiddleware::requireModulePermission Error: " . $e->getMessage());
                return $this->serverError($response, 'Permission check failed');
            }
        };
    }

    /**
     * 관리자 권한 확인 미들웨어
     */
    public function requireAdminPermission(string $permission)
    {
        return function($request, $response, $next) use ($permission) {
            try {
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required');
                }

                $companyId = $this->getCurrentCompanyId($request);
                if (!$companyId) {
                    return $this->forbidden($response, 'Company context required');
                }

                if (!$this->permission->hasAdminPermission($user['id'], $companyId, $permission)) {
                    return $this->forbidden($response, "Admin permission required: {$permission}");
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("PermissionMiddleware::requireAdminPermission Error: " . $e->getMessage());
                return $this->serverError($response, 'Permission check failed');
            }
        };
    }

    /**
     * 최소 역할 레벨 확인 미들웨어
     */
    public function requireRole(string $minRole)
    {
        return function($request, $response, $next) use ($minRole) {
            try {
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required');
                }

                $companyId = $this->getCurrentCompanyId($request);
                if (!$companyId) {
                    return $this->forbidden($response, 'Company context required');
                }

                $userLevel = $this->permission->getUserRoleLevel($user['id'], $companyId);
                $requiredLevel = Permission::ROLE_LEVELS[$minRole] ?? 100;

                if ($userLevel < $requiredLevel) {
                    $userRole = $this->permission->getUserRole($user['id'], $companyId);
                    return $this->forbidden($response, "Role {$minRole} required, current: {$userRole}");
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("PermissionMiddleware::requireRole Error: " . $e->getMessage());
                return $this->serverError($response, 'Permission check failed');
            }
        };
    }

    /**
     * 창립자 전용 미들웨어
     */
    public function requireFounder()
    {
        return $this->requireRole(Permission::ROLE_FOUNDER);
    }

    /**
     * 관리자 이상 미들웨어
     */
    public function requireAdmin()
    {
        return $this->requireRole(Permission::ROLE_ADMIN);
    }

    /**
     * 프로세스 담당자 이상 미들웨어
     */
    public function requireProcessOwner()
    {
        return $this->requireRole(Permission::ROLE_PROCESS_OWNER);
    }

    /**
     * 인증된 사용자 미들웨어 (구성원 이상)
     */
    public function requireMember()
    {
        return $this->requireRole(Permission::ROLE_MEMBER);
    }

    /**
     * 회사별 데이터 접근 확인 미들웨어
     */
    public function requireCompanyAccess()
    {
        return function($request, $response, $next) {
            try {
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required');
                }

                $companyId = $this->getCurrentCompanyId($request);
                if (!$companyId) {
                    return $this->forbidden($response, 'Company context required');
                }

                // 사용자가 해당 회사에 소속되어 있는지 확인
                if (!$this->isUserInCompany($user['id'], $companyId)) {
                    return $this->forbidden($response, 'Access denied to company data');
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("PermissionMiddleware::requireCompanyAccess Error: " . $e->getMessage());
                return $this->serverError($response, 'Company access check failed');
            }
        };
    }

    /**
     * 조건부 권한 미들웨어 (여러 조건 중 하나만 만족하면 통과)
     */
    public function requireAnyPermission(array $conditions)
    {
        return function($request, $response, $next) use ($conditions) {
            try {
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required');
                }

                $companyId = $this->getCurrentCompanyId($request);
                if (!$companyId) {
                    return $this->forbidden($response, 'Company context required');
                }

                foreach ($conditions as $condition) {
                    if ($this->checkCondition($user['id'], $companyId, $condition)) {
                        return $next($request, $response);
                    }
                }

                return $this->forbidden($response, 'Insufficient permissions');

            } catch (\Exception $e) {
                error_log("PermissionMiddleware::requireAnyPermission Error: " . $e->getMessage());
                return $this->serverError($response, 'Permission check failed');
            }
        };
    }

    /**
     * 현재 회사 ID 조회
     */
    private function getCurrentCompanyId($request): ?string
    {
        // 요청에서 회사 ID 추출 (헤더, 파라미터, 세션 등)
        $companyId = null;

        // 1. X-Company-ID 헤더 확인
        if (isset($_SERVER['HTTP_X_COMPANY_ID'])) {
            $companyId = $_SERVER['HTTP_X_COMPANY_ID'];
        }
        // 2. URL 파라미터 확인
        elseif (isset($_GET['company_id'])) {
            $companyId = $_GET['company_id'];
        }
        // 3. POST 데이터 확인
        elseif (isset($_POST['company_id'])) {
            $companyId = $_POST['company_id'];
        }
        // 4. 세션에서 확인
        elseif (isset($_SESSION['company_id'])) {
            $companyId = $_SESSION['company_id'];
        }

        return $companyId;
    }

    /**
     * 사용자의 회사 소속 확인
     */
    private function isUserInCompany(string $userId, string $companyId): bool
    {
        try {
            $db = \BPM\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM bpm_company_users 
                WHERE user_id = ? AND company_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $companyId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("PermissionMiddleware::isUserInCompany Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 조건 확인
     */
    private function checkCondition(string $userId, string $companyId, array $condition): bool
    {
        $type = $condition['type'] ?? 'module';
        
        switch ($type) {
            case 'module':
                return $this->permission->hasModulePermission(
                    $userId, 
                    $companyId, 
                    $condition['module'], 
                    $condition['action'] ?? 'view'
                );
            
            case 'admin':
                return $this->permission->hasAdminPermission(
                    $userId, 
                    $companyId, 
                    $condition['permission']
                );
            
            case 'role':
                $userLevel = $this->permission->getUserRoleLevel($userId, $companyId);
                $requiredLevel = Permission::ROLE_LEVELS[$condition['role']] ?? 100;
                return $userLevel >= $requiredLevel;
            
            default:
                return false;
        }
    }

    /**
     * 401 Unauthorized 응답
     */
    private function unauthorized($response, string $message = 'Unauthorized')
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'code' => 401,
            'message' => $message
        ]);
        exit;
    }

    /**
     * 403 Forbidden 응답
     */
    private function forbidden($response, string $message = 'Forbidden')
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'code' => 403,
            'message' => $message
        ]);
        exit;
    }

    /**
     * 500 Server Error 응답
     */
    private function serverError($response, string $message = 'Internal Server Error')
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'code' => 500,
            'message' => $message
        ]);
        exit;
    }
}