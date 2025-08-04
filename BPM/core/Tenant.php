<?php
// 📁 C:\xampp\htdocs\BPM\core\Tenant.php
// Create at 2508040640 Ver1.00

namespace BPM\Core;

use BPM\Core\Database;

/**
 * BPM 멀티테넌트 관리 클래스
 * 회사별 데이터 완전 분리 및 테넌트 컨텍스트 관리
 * 모든 데이터 접근 시 자동으로 company_id 필터링 적용
 */
class Tenant
{
    private static $instance = null;
    private ?string $currentCompanyId = null;
    private ?array $currentCompany = null;
    private array $companyCache = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 현재 테넌트 설정
     */
    public function setCurrentTenant(string $companyId): void
    {
        $this->currentCompanyId = $companyId;
        $this->currentCompany = null; // 캐시 초기화
        
        // 세션에 저장
        $_SESSION['company_id'] = $companyId;
    }

    /**
     * 현재 테넌트 ID 조회
     */
    public function getCurrentCompanyId(): ?string
    {
        if ($this->currentCompanyId) {
            return $this->currentCompanyId;
        }

        // 세션에서 조회
        if (isset($_SESSION['company_id'])) {
            $this->currentCompanyId = $_SESSION['company_id'];
            return $this->currentCompanyId;
        }

        // 헤더에서 조회
        if (isset($_SERVER['HTTP_X_COMPANY_ID'])) {
            $this->currentCompanyId = $_SERVER['HTTP_X_COMPANY_ID'];
            return $this->currentCompanyId;
        }

        return null;
    }

    /**
     * 현재 테넌트 정보 조회
     */
    public function getCurrentCompany(): ?array
    {
        if ($this->currentCompany) {
            return $this->currentCompany;
        }

        $companyId = $this->getCurrentCompanyId();
        if (!$companyId) {
            return null;
        }

        $this->currentCompany = $this->getCompanyById($companyId);
        return $this->currentCompany;
    }

    /**
     * 회사 ID로 회사 정보 조회
     */
    public function getCompanyById(string $companyId): ?array
    {
        // 캐시 확인
        if (isset($this->companyCache[$companyId])) {
            return $this->companyCache[$companyId];
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT 
                    id,
                    company_name,
                    tax_number,
                    business_type,
                    company_type,
                    parent_company_id,
                    branch_name,
                    representative_name,
                    representative_phone,
                    admin_email,
                    admin_phone,
                    address,
                    postal_code,
                    establishment_date,
                    phone,
                    email,
                    website,
                    status,
                    settings,
                    created_at,
                    updated_at
                FROM bpm_companies 
                WHERE id = ? AND status IN ('active', 'pending_integration')
            ");
            
            $stmt->execute([$companyId]);
            $company = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($company) {
                // JSON 설정 파싱
                if ($company['settings']) {
                    $company['settings'] = json_decode($company['settings'], true) ?? [];
                }
                
                // 캐시에 저장
                $this->companyCache[$companyId] = $company;
                return $company;
            }
            
            return null;
        } catch (\Exception $e) {
            error_log("Tenant::getCompanyById Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 새 회사 등록
     */
    public function createCompany(array $companyData): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $db->beginTransaction();

            // 필수 필드 확인
            $requiredFields = ['company_name', 'tax_number', 'admin_email', 'representative_name'];
            foreach ($requiredFields as $field) {
                if (empty($companyData[$field])) {
                    throw new \Exception("Required field missing: {$field}");
                }
            }

            // 사업자등록번호 중복 확인
            $stmt = $db->prepare("SELECT id FROM bpm_companies WHERE tax_number = ?");
            $stmt->execute([$companyData['tax_number']]);
            if ($stmt->fetch()) {
                throw new \Exception("Tax number already exists");
            }

            // 회사 ID 생성
            $companyId = $this->generateUUID();
            
            // 기본값 설정
            $defaultData = [
                'id' => $companyId,
                'company_type' => 'headquarters',
                'status' => 'active',
                'settings' => json_encode([
                    'timezone' => 'Asia/Seoul',
                    'language' => 'ko',
                    'date_format' => 'Y-m-d',
                    'currency' => 'KRW'
                ])
            ];

            $insertData = array_merge($defaultData, $companyData);

            // 회사 정보 삽입
            $fields = implode(', ', array_keys($insertData));
            $placeholders = ':' . implode(', :', array_keys($insertData));
            
            $stmt = $db->prepare("
                INSERT INTO bpm_companies ({$fields})
                VALUES ({$placeholders})
            ");
            
            $stmt->execute($insertData);

            // 초기 부서 생성 (본사)
            $this->createInitialDepartment($companyId, $companyData['company_name']);

            $db->commit();
            
            // 캐시 갱신
            unset($this->companyCache[$companyId]);
            
            return [
                'company_id' => $companyId,
                'company_name' => $companyData['company_name'],
                'status' => 'active'
            ];

        } catch (\Exception $e) {
            $db->rollBack();
            error_log("Tenant::createCompany Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * 회사 설정 업데이트
     */
    public function updateCompanySettings(string $companyId, array $settings): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            // 기존 설정 조회
            $company = $this->getCompanyById($companyId);
            if (!$company) {
                throw new \Exception("Company not found");
            }
            
            // 설정 병합
            $currentSettings = $company['settings'] ?? [];
            $newSettings = array_merge($currentSettings, $settings);
            
            $stmt = $db->prepare("
                UPDATE bmp_companies 
                SET settings = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([json_encode($newSettings), $companyId]);
            
            // 캐시 초기화
            unset($this->companyCache[$companyId]);
            
            return true;
        } catch (\Exception $e) {
            error_log("Tenant::updateCompanySettings Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 사용자의 소속 회사 목록 조회
     */
    public function getUserCompanies(string $userId): array
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT 
                    c.id,
                    c.company_name,
                    c.company_type,
                    c.status,
                    cu.role_type,
                    cu.status as user_status,
                    cu.joined_at
                FROM bpm_companies c
                JOIN bpm_company_users cu ON c.id = cu.company_id
                WHERE cu.user_id = ? AND cu.status = 'active'
                ORDER BY cu.joined_at DESC
            ");
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log("Tenant::getUserCompanies Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 데이터 격리 확인
     */
    public function ensureTenantIsolation(): void
    {
        $companyId = $this->getCurrentCompanyId();
        if (!$companyId) {
            throw new \Exception("No tenant context set. Access denied.");
        }
    }

    /**
     * 테넌트별 쿼리 빌더
     */
    public function addTenantFilter(string $baseQuery, string $tableAlias = ''): string
    {
        $companyId = $this->getCurrentCompanyId();
        if (!$companyId) {
            throw new \Exception("No tenant context for query filtering");
        }

        $prefix = $tableAlias ? "{$tableAlias}." : '';
        
        // WHERE 절이 있는지 확인
        if (stripos($baseQuery, 'WHERE') !== false) {
            return $baseQuery . " AND {$prefix}company_id = '{$companyId}'";
        } else {
            return $baseQuery . " WHERE {$prefix}company_id = '{$companyId}'";
        }
    }

    /**
     * 안전한 데이터 삽입 (company_id 자동 추가)
     */
    public function prepareTenantInsert(array $data): array
    {
        $companyId = $this->getCurrentCompanyId();
        if (!$companyId) {
            throw new \Exception("No tenant context for data insertion");
        }

        $data['company_id'] = $companyId;
        return $data;
    }

    /**
     * 회사 상태 변경
     */
    public function updateCompanyStatus(string $companyId, string $status): bool
    {
        $validStatuses = ['active', 'suspended', 'inactive', 'pending_integration'];
        if (!in_array($status, $validStatuses)) {
            throw new \Exception("Invalid company status: {$status}");
        }

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                UPDATE bpm_companies 
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            $stmt->execute([$status, $companyId]);
            
            // 캐시 초기화
            unset($this->companyCache[$companyId]);
            
            return $stmt->rowCount() > 0;
        } catch (\Exception $e) {
            error_log("Tenant::updateCompanyStatus Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 회사별 통계 조회
     */
    public function getCompanyStats(): array
    {
        $companyId = $this->getCurrentCompanyId();
        if (!$companyId) {
            return [];
        }

        try {
            $db = Database::getInstance()->getConnection();
            
            // 구성원 수
            $stmt = $db->prepare("
                SELECT COUNT(*) as user_count
                FROM bpm_company_users 
                WHERE company_id = ? AND status = 'active'
            ");
            $stmt->execute([$companyId]);
            $userCount = $stmt->fetchColumn();

            // 부서 수 (추후 구현)
            $departmentCount = 0;

            // Task 수 (추후 구현)
            $taskCount = 0;

            return [
                'users' => (int)$userCount,
                'departments' => $departmentCount,
                'tasks' => $taskCount,
                'company_id' => $companyId
            ];
        } catch (\Exception $e) {
            error_log("Tenant::getCompanyStats Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 초기 부서 생성
     */
    private function createInitialDepartment(string $companyId, string $companyName): void
    {
        try {
            $db = Database::getInstance()->getConnection();
            
            // 추후 부서 테이블이 생성되면 활성화
            /*
            $stmt = $db->prepare("
                INSERT INTO bpm_departments (id, company_id, name, description, parent_id, level)
                VALUES (?, ?, ?, ?, NULL, 0)
            ");
            
            $stmt->execute([
                $this->generateUUID(),
                $companyId,
                $companyName . ' 본사',
                '회사 본사 부서'
            ]);
            */
        } catch (\Exception $e) {
            error_log("Tenant::createInitialDepartment Error: " . $e->getMessage());
        }
    }

    /**
     * UUID 생성
     */
    private function generateUUID(): string
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

    /**
     * 캐시 초기화
     */
    public function clearCache(): void
    {
        $this->companyCache = [];
        $this->currentCompany = null;
    }

    /**
     * 테넌트 정보 디버그
     */
    public function debugTenantContext(): array
    {
        return [
            'current_company_id' => $this->getCurrentCompanyId(),
            'current_company' => $this->getCurrentCompany(),
            'session_company_id' => $_SESSION['company_id'] ?? null,
            'header_company_id' => $_SERVER['HTTP_X_COMPANY_ID'] ?? null,
            'cache_size' => count($this->companyCache)
        ];
    }
}