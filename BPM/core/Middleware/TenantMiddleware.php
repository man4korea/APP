<?php
// 📁 C:\xampp\htdocs\BPM\core\Middleware\TenantMiddleware.php
// Create at 2508040645 Ver1.00

namespace BPM\Core\Middleware;

use BPM\Core\Tenant;
use BPM\Core\Auth;
use BPM\Core\Database;

/**
 * 멀티테넌트 데이터 분리 미들웨어
 * 모든 데이터 접근 시 자동으로 company_id 필터링 적용
 * 회사별 데이터 완전 격리 보장
 */
class TenantMiddleware
{
    private Tenant $tenant;
    private Auth $auth;

    public function __construct()
    {
        $this->tenant = Tenant::getInstance();
        $this->auth = Auth::getInstance();
    }

    /**
     * 테넌트 컨텍스트 설정 미들웨어
     */
    public function setTenantContext()
    {
        return function($request, $response, $next) {
            try {
                // 인증된 사용자 확인
                $user = $this->auth->getCurrentUser();
                if (!$user) {
                    return $this->unauthorized($response, 'Authentication required for tenant access');
                }

                // 회사 ID 확인 및 설정
                $companyId = $this->extractCompanyId($request);
                if (!$companyId) {
                    return $this->badRequest($response, 'Company context required');
                }

                // 사용자가 해당 회사에 접근 권한이 있는지 확인
                if (!$this->validateUserCompanyAccess($user['id'], $companyId)) {
                    return $this->forbidden($response, 'Access denied to company data');
                }

                // 테넌트 컨텍스트 설정
                $this->tenant->setCurrentTenant($companyId);

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::setTenantContext Error: " . $e->getMessage());
                return $this->serverError($response, 'Tenant context setup failed');
            }
        };
    }

    /**
     * 데이터 격리 강제 미들웨어
     */
    public function enforceDataIsolation()
    {
        return function($request, $response, $next) {
            try {
                // 테넌트 컨텍스트 확인
                $this->tenant->ensureTenantIsolation();

                // PDO 쿼리 후킹 설정 (개발 모드에서만)
                if (defined('DEBUG_MODE') && DEBUG_MODE) {
                    $this->setupQueryHooking();
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::enforceDataIsolation Error: " . $e->getMessage());
                return $this->forbidden($response, $e->getMessage());
            }
        };
    }

    /**
     * 회사 소유자만 접근 가능한 미들웨어
     */
    public function requireCompanyOwnership()
    {
        return function($request, $response, $next) {
            try {
                $user = $this->auth->getCurrentUser();
                $companyId = $this->tenant->getCurrentCompanyId();

                if (!$this->isCompanyOwner($user['id'], $companyId)) {
                    return $this->forbidden($response, 'Company ownership required');
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::requireCompanyOwnership Error: " . $e->getMessage());
                return $this->serverError($response, 'Ownership check failed');
            }
        };
    }

    /**
     * 회사별 리소스 접근 제한 미들웨어
     */
    public function limitResourceAccess(array $resourceTypes = ['all'])
    {
        return function($request, $response, $next) use ($resourceTypes) {
            try {
                $companyId = $this->tenant->getCurrentCompanyId();
                $user = $this->auth->getCurrentUser();

                // 리소스 사용량 확인
                foreach ($resourceTypes as $resourceType) {
                    if (!$this->checkResourceLimit($companyId, $resourceType)) {
                        return $this->forbidden($response, "Resource limit exceeded for {$resourceType}");
                    }
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::limitResourceAccess Error: " . $e->getMessage());
                return $this->serverError($response, 'Resource limit check failed');
            }
        };
    }

    /**
     * 회사 간 데이터 전송 차단 미들웨어
     */
    public function preventCrossCompanyAccess()
    {
        return function($request, $response, $next) {
            try {
                $currentCompanyId = $this->tenant->getCurrentCompanyId();

                // 요청에서 다른 회사 ID 참조 확인
                $requestData = $this->getRequestData($request);
                $suspiciousIds = $this->findSuspiciousCompanyReferences($requestData, $currentCompanyId);

                if (!empty($suspiciousIds)) {
                    error_log("Cross-company access attempt detected: " . json_encode([
                        'current_company' => $currentCompanyId,
                        'suspicious_ids' => $suspiciousIds,
                        'user_id' => $this->auth->getCurrentUser()['id'] ?? 'unknown'
                    ]));
                    
                    return $this->forbidden($response, 'Cross-company data access denied');
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::preventCrossCompanyAccess Error: " . $e->getMessage());
                return $this->serverError($response, 'Cross-company access check failed');
            }
        };
    }

    /**
     * 회사 상태 확인 미들웨어
     */
    public function checkCompanyStatus(array $allowedStatuses = ['active'])
    {
        return function($request, $response, $next) use ($allowedStatuses) {
            try {
                $company = $this->tenant->getCurrentCompany();
                
                if (!$company) {
                    return $this->badRequest($response, 'Invalid company context');
                }

                if (!in_array($company['status'], $allowedStatuses)) {
                    return $this->forbidden($response, 'Company account is ' . $company['status']);
                }

                return $next($request, $response);

            } catch (\Exception $e) {
                error_log("TenantMiddleware::checkCompanyStatus Error: " . $e->getMessage());
                return $this->serverError($response, 'Company status check failed');
            }
        };
    }

    /**
     * 요청에서 회사 ID 추출
     */
    private function extractCompanyId($request): ?string
    {
        // 1. HTTP 헤더에서 확인
        if (isset($_SERVER['HTTP_X_COMPANY_ID'])) {
            return $_SERVER['HTTP_X_COMPANY_ID'];
        }

        // 2. URL 파라미터에서 확인
        if (isset($_GET['company_id'])) {
            return $_GET['company_id'];
        }

        // 3. POST 데이터에서 확인
        if (isset($_POST['company_id'])) {
            return $_POST['company_id'];
        }

        // 4. JSON 바디에서 확인
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['company_id'])) {
            return $input['company_id'];
        }

        // 5. 세션에서 확인
        if (isset($_SESSION['company_id'])) {
            return $_SESSION['company_id'];
        }

        return null;
    }

    /**
     * 사용자의 회사 접근 권한 확인
     */
    private function validateUserCompanyAccess(string $userId, string $companyId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM bpm_company_users 
                WHERE user_id = ? AND company_id = ? AND status = 'active'
            ");
            $stmt->execute([$userId, $companyId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("TenantMiddleware::validateUserCompanyAccess Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 회사 소유자 확인
     */
    private function isCompanyOwner(string $userId, string $companyId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM bpm_company_users 
                WHERE user_id = ? AND company_id = ? AND role_type IN ('founder', 'admin') AND status = 'active'
            ");
            $stmt->execute([$userId, $companyId]);
            
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            error_log("TenantMiddleware::isCompanyOwner Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 리소스 사용량 제한 확인
     */
    private function checkResourceLimit(string $companyId, string $resourceType): bool
    {
        // 추후 구현: 회사별 리소스 사용량 체크
        // 예: 사용자 수, 저장소 용량, API 호출 수 등
        
        switch ($resourceType) {
            case 'users':
                return $this->checkUserLimit($companyId);
            case 'storage':
                return $this->checkStorageLimit($companyId);
            case 'api_calls':
                return $this->checkApiCallLimit($companyId);
            default:
                return true; // 기본적으로 허용
        }
    }

    /**
     * 사용자 수 제한 확인
     */
    private function checkUserLimit(string $companyId): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT COUNT(*) 
                FROM bpm_company_users 
                WHERE company_id = ? AND status = 'active'
            ");
            $stmt->execute([$companyId]);
            $userCount = $stmt->fetchColumn();
            
            // 기본 제한: 100명 (추후 플랜별로 차별화)
            return $userCount < 100;
        } catch (\Exception $e) {
            error_log("TenantMiddleware::checkUserLimit Error: " . $e->getMessage());
            return true; // 오류 시 기본 허용
        }
    }

    /**
     * 저장소 제한 확인
     */
    private function checkStorageLimit(string $companyId): bool
    {
        // 추후 구현: 파일 저장소 사용량 체크
        return true;
    }

    /**
     * API 호출 제한 확인
     */
    private function checkApiCallLimit(string $companyId): bool
    {
        // 추후 구현: API 호출 수 제한 체크
        return true;
    }

    /**
     * 요청 데이터 추출
     */
    private function getRequestData($request): array
    {
        $data = [];
        
        // GET 파라미터
        $data = array_merge($data, $_GET);
        
        // POST 데이터
        $data = array_merge($data, $_POST);
        
        // JSON 바디
        $jsonInput = json_decode(file_get_contents('php://input'), true);
        if ($jsonInput) {
            $data = array_merge($data, $jsonInput);
        }
        
        return $data;
    }

    /**
     * 다른 회사 ID 참조 탐지
     */
    private function findSuspiciousCompanyReferences(array $data, string $currentCompanyId): array
    {
        $suspiciousIds = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value) && $this->looksLikeUUID($value) && $value !== $currentCompanyId) {
                // UUID 형태이면서 현재 회사 ID가 아닌 경우 의심스러운 ID로 간주
                if ($this->isCompanyId($value)) {
                    $suspiciousIds[] = $value;
                }
            }
        }
        
        return $suspiciousIds;
    }

    /**
     * UUID 형태 확인
     */
    private function looksLikeUUID(string $string): bool
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $string);
    }

    /**
     * 회사 ID 여부 확인
     */
    private function isCompanyId(string $id): bool
    {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT COUNT(*) FROM bpm_companies WHERE id = ?");
            $stmt->execute([$id]);
            
            return $stmt->fetchColumn() > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 쿼리 후킹 설정 (디버그용)
     */
    private function setupQueryHooking(): void
    {
        // 개발 모드에서 SQL 쿼리를 모니터링하여 company_id 필터링 누락 감지
        // 추후 구현
    }

    // HTTP 응답 메서드들
    private function unauthorized($response, string $message = 'Unauthorized')
    {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'code' => 401, 'message' => $message]);
        exit;
    }

    private function forbidden($response, string $message = 'Forbidden')
    {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'code' => 403, 'message' => $message]);
        exit;
    }

    private function badRequest($response, string $message = 'Bad Request')
    {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'code' => 400, 'message' => $message]);
        exit;
    }

    private function serverError($response, string $message = 'Internal Server Error')
    {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'code' => 500, 'message' => $message]);
        exit;
    }
}