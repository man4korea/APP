<?php
// 📁 C:\xampp\htdocs\BPM\core\Permission.php
// Create at 2508040615 Ver1.00

namespace BPM\Core;

/**
 * BPM 권한 관리 클래스
 * 4단계 권한 시스템과 모듈별 접근 제어 관리
 * Founder/Admin/Process_Owner/Member 역할 기반 권한 제어
 */
class Permission
{
    // 4단계 권한 레벨 상수
    const ROLE_FOUNDER = 'founder';           // 100: 최고 권한 (시스템 모든 기능)
    const ROLE_ADMIN = 'admin';               // 80: 관리자 권한 (회사 관리 전반)
    const ROLE_PROCESS_OWNER = 'process_owner'; // 60: 프로세스 담당자 (해당 프로세스 관리)
    const ROLE_MEMBER = 'member';             // 40: 일반 구성원 (기본 사용 권한)

    // 권한 레벨 수치화
    const ROLE_LEVELS = [
        self::ROLE_FOUNDER => 100,
        self::ROLE_ADMIN => 80,
        self::ROLE_PROCESS_OWNER => 60,
        self::ROLE_MEMBER => 40
    ];

    // 모듈별 최소 요구 권한 레벨
    const MODULE_PERMISSIONS = [
        'organization' => [
            'view' => 40,   // 모든 사용자 조회 가능
            'create' => 80, // 관리자 이상
            'edit' => 80,   // 관리자 이상
            'delete' => 100 // 창립자만
        ],
        'members' => [
            'view' => 40,
            'create' => 80,
            'edit' => 80,
            'delete' => 100
        ],
        'tasks' => [
            'view' => 40,
            'create' => 60,  // 프로세스 담당자 이상
            'edit' => 60,
            'delete' => 80   // 관리자 이상
        ],
        'documents' => [
            'view' => 40,
            'create' => 40,  // 모든 사용자
            'edit' => 60,    // 프로세스 담당자 이상
            'delete' => 80
        ],
        'process_map' => [
            'view' => 40,
            'create' => 60,
            'edit' => 60,
            'delete' => 80
        ],
        'workflow' => [
            'view' => 40,
            'create' => 60,
            'edit' => 60,
            'delete' => 80
        ],
        'job_analysis' => [
            'view' => 60,    // 프로세스 담당자 이상
            'create' => 80,  // 관리자 이상
            'edit' => 80,
            'delete' => 100
        ],
        'innovation' => [
            'view' => 60,
            'create' => 80,
            'edit' => 80,
            'delete' => 100
        ],
        'hr' => [
            'view' => 80,    // 관리자 이상만
            'create' => 80,
            'edit' => 80,
            'delete' => 100
        ],
        'performance' => [
            'view' => 60,
            'create' => 80,
            'edit' => 80,
            'delete' => 100
        ]
    ];

    // 관리 기능별 권한
    const ADMIN_PERMISSIONS = [
        'company_settings' => 80,    // 회사 설정
        'user_management' => 80,     // 사용자 관리
        'role_assignment' => 80,     // 권한 할당
        'system_logs' => 80,         // 시스템 로그
        'backup_restore' => 100,     // 백업/복구
        'system_settings' => 100     // 시스템 설정
    ];

    private static $instance = null;
    private $currentUser = null;
    private $currentCompany = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 현재 사용자 설정
     */
    public function setCurrentUser(array $user): void
    {
        $this->currentUser = $user;
    }

    /**
     * 현재 회사 설정
     */
    public function setCurrentCompany(string $companyId): void
    {
        $this->currentCompany = $companyId;
    }

    /**
     * 사용자 권한 레벨 조회
     */
    public function getUserRoleLevel(string $userId, string $companyId): int
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT role_type 
                FROM bpm_company_users 
                WHERE user_id = ? AND company_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $companyId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($result) {
                return self::ROLE_LEVELS[$result['role_type']] ?? 0;
            }
            
            return 0; // 권한 없음
        } catch (\Exception $e) {
            error_log("Permission::getUserRoleLevel Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * 사용자 역할 조회
     */
    public function getUserRole(string $userId, string $companyId): ?string
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT role_type 
                FROM bpm_company_users 
                WHERE user_id = ? AND company_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $companyId]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            return $result['role_type'] ?? null;
        } catch (\Exception $e) {
            error_log("Permission::getUserRole Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 모듈 접근 권한 확인
     */
    public function hasModulePermission(string $userId, string $companyId, string $module, string $action = 'view'): bool
    {
        // 모듈이 정의되지 않은 경우 기본 허용
        if (!isset(self::MODULE_PERMISSIONS[$module])) {
            return true;
        }

        // 액션이 정의되지 않은 경우 기본 허용
        if (!isset(self::MODULE_PERMISSIONS[$module][$action])) {
            return true;
        }

        $userLevel = $this->getUserRoleLevel($userId, $companyId);
        $requiredLevel = self::MODULE_PERMISSIONS[$module][$action];

        return $userLevel >= $requiredLevel;
    }

    /**
     * 관리 기능 접근 권한 확인
     */
    public function hasAdminPermission(string $userId, string $companyId, string $permission): bool
    {
        if (!isset(self::ADMIN_PERMISSIONS[$permission])) {
            return false;
        }

        $userLevel = $this->getUserRoleLevel($userId, $companyId);
        $requiredLevel = self::ADMIN_PERMISSIONS[$permission];

        return $userLevel >= $requiredLevel;
    }

    /**
     * 현재 사용자 권한 확인 (세션 기반)
     */
    public function checkCurrentUserPermission(string $module, string $action = 'view'): bool
    {
        if (!$this->currentUser || !$this->currentCompany) {
            return false;
        }

        return $this->hasModulePermission(
            $this->currentUser['id'], 
            $this->currentCompany, 
            $module, 
            $action
        );
    }

    /**
     * 권한 부족 시 예외 발생
     */
    public function requirePermission(string $userId, string $companyId, string $module, string $action = 'view'): void
    {
        if (!$this->hasModulePermission($userId, $companyId, $module, $action)) {
            $userRole = $this->getUserRole($userId, $companyId) ?? 'none';
            throw new \Exception("Access denied. Required permission: {$module}.{$action}, User role: {$userRole}");
        }
    }

    /**
     * 사용자 권한별 접근 가능한 모듈 목록 조회
     */
    public function getAccessibleModules(string $userId, string $companyId): array
    {
        $userLevel = $this->getUserRoleLevel($userId, $companyId);
        $accessibleModules = [];

        foreach (self::MODULE_PERMISSIONS as $module => $permissions) {
            $moduleAccess = [];
            foreach ($permissions as $action => $requiredLevel) {
                if ($userLevel >= $requiredLevel) {
                    $moduleAccess[] = $action;
                }
            }
            
            if (!empty($moduleAccess)) {
                $accessibleModules[$module] = $moduleAccess;
            }
        }

        return $accessibleModules;
    }

    /**
     * 권한별 사이드바 메뉴 필터링
     */
    public function filterMenuItems(array $menuItems, string $userId, string $companyId): array
    {
        $filteredMenu = [];
        
        foreach ($menuItems as $menuItem) {
            $module = $menuItem['module'] ?? null;
            $requiredAction = $menuItem['required_action'] ?? 'view';
            
            // 모듈이 지정되지 않은 경우 기본 허용
            if (!$module || $this->hasModulePermission($userId, $companyId, $module, $requiredAction)) {
                // 하위 메뉴도 재귀적으로 필터링
                if (isset($menuItem['children'])) {
                    $menuItem['children'] = $this->filterMenuItems($menuItem['children'], $userId, $companyId);
                }
                $filteredMenu[] = $menuItem;
            }
        }
        
        return $filteredMenu;
    }

    /**
     * 권한 레벨별 표시명 조회
     */
    public static function getRoleDisplayName(string $role): string
    {
        $displayNames = [
            self::ROLE_FOUNDER => '창립자',
            self::ROLE_ADMIN => '관리자',
            self::ROLE_PROCESS_OWNER => '프로세스 담당자',
            self::ROLE_MEMBER => '구성원'
        ];

        return $displayNames[$role] ?? '알 수 없음';
    }

    /**
     * 모든 권한 역할 목록 조회
     */
    public static function getAllRoles(): array
    {
        return [
            ['value' => self::ROLE_FOUNDER, 'label' => self::getRoleDisplayName(self::ROLE_FOUNDER), 'level' => self::ROLE_LEVELS[self::ROLE_FOUNDER]],
            ['value' => self::ROLE_ADMIN, 'label' => self::getRoleDisplayName(self::ROLE_ADMIN), 'level' => self::ROLE_LEVELS[self::ROLE_ADMIN]],
            ['value' => self::ROLE_PROCESS_OWNER, 'label' => self::getRoleDisplayName(self::ROLE_PROCESS_OWNER), 'level' => self::ROLE_LEVELS[self::ROLE_PROCESS_OWNER]],
            ['value' => self::ROLE_MEMBER, 'label' => self::getRoleDisplayName(self::ROLE_MEMBER), 'level' => self::ROLE_LEVELS[self::ROLE_MEMBER]]
        ];
    }

    /**
     * 디버그용 권한 정보 출력
     */
    public function debugUserPermissions(string $userId, string $companyId): array
    {
        $role = $this->getUserRole($userId, $companyId);
        $level = $this->getUserRoleLevel($userId, $companyId);
        $accessibleModules = $this->getAccessibleModules($userId, $companyId);
        
        return [
            'user_id' => $userId,
            'company_id' => $companyId,
            'role' => $role,
            'role_display' => self::getRoleDisplayName($role ?? ''),
            'level' => $level,
            'accessible_modules' => $accessibleModules
        ];
    }
}