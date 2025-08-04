<?php
// 📁 C:\xampp\htdocs\BPM\core\BPMAIHelper.php
// Create at 2508031100 Ver1.00

namespace BPM\Core;

/**
 * BPM AI Helper 통합 관리 클래스
 * 각 모듈별 AI 기능을 중앙에서 관리하고 통합된 인터페이스 제공
 * 모듈: AI 지원 시스템 (색상: #00ff00)
 */
class BPMAIHelper
{
    private static $instance = null;
    private $geminiAI;
    private $auth;
    private $security;
    private $database;
    
    // BPM 모듈 상수
    const MODULE_ORGANIZATION = 'organization';      // 🔴 조직관리
    const MODULE_MEMBERS = 'members';               // 🟠 구성원관리
    const MODULE_TASKS = 'tasks';                   // 🟡 Task관리
    const MODULE_DOCUMENTS = 'documents';           // 🟢 문서관리
    const MODULE_PROCESS_MAP = 'process_map';       // 🔵 Process Map
    const MODULE_WORKFLOW = 'workflow';             // 🟣 업무Flow
    const MODULE_JOB_ANALYSIS = 'job_analysis';     // 🟤 직무분석
    
    // AI 기능 타입
    const AI_TYPE_GENERATE = 'generate';
    const AI_TYPE_ANALYZE = 'analyze';
    const AI_TYPE_OPTIMIZE = 'optimize';
    const AI_TYPE_SUMMARIZE = 'summarize';
    const AI_TYPE_SUGGEST = 'suggest';
    
    // 모듈별 색상 매핑
    private $moduleColors = [
        self::MODULE_ORGANIZATION => '#ff6b6b',
        self::MODULE_MEMBERS => '#ff9f43',
        self::MODULE_TASKS => '#feca57',
        self::MODULE_DOCUMENTS => '#55a3ff',
        self::MODULE_PROCESS_MAP => '#3742fa',
        self::MODULE_WORKFLOW => '#a55eea',
        self::MODULE_JOB_ANALYSIS => '#8b4513'
    ];
    
    private function __construct()
    {
        $this->geminiAI = GeminiAI::getInstance();
        $this->auth = Auth::getInstance();
        $this->security = Security::getInstance();
        $this->database = Database::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 통합 AI 요청 처리 메서드
     */
    public function processAIRequest(string $module, string $aiType, array $requestData, ?array $options = []): array
    {
        try {
            // 권한 검증
            if (!$this->validatePermissions($module, $aiType)) {
                return $this->createErrorResponse('AI 기능 사용 권한이 없습니다.');
            }
            
            // 입력 데이터 검증
            $validatedData = $this->validateRequestData($module, $aiType, $requestData);
            if (!$validatedData['success']) {
                return $validatedData;
            }
            
            // 사용량 제한 체크
            if (!$this->checkUsageLimit($module)) {
                return $this->createErrorResponse('일일 AI 사용량을 초과했습니다.');
            }
            
            // 모듈별 AI 처리
            $result = $this->processModuleAI($module, $aiType, $validatedData['data'], $options);
            
            // 사용량 기록
            $this->recordUsage($module, $aiType, $result);
            
            // 로깅
            $this->logAIRequest($module, $aiType, $result['success']);
            
            return $result;
            
        } catch (\Exception $e) {
            BPMLogger::error('AI 요청 처리 중 오류 발생', [
                'module' => $module,
                'ai_type' => $aiType,
                'error' => $e->getMessage(),
                'user_id' => $this->auth->getCurrentUser()['id'] ?? null
            ]);
            
            return $this->createErrorResponse('AI 처리 중 오류가 발생했습니다.');
        }
    }
    
    // ========== 모듈별 AI 기능 ==========
    
    /**
     * 🔴 조직관리 AI 기능
     */
    public function organizationAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeOrganizationStructure($data);
            case self::AI_TYPE_OPTIMIZE:
                return $this->optimizeOrganization($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestOrganizationChanges($data);
            default:
                return $this->createErrorResponse('지원하지 않는 조직관리 AI 기능입니다.');
        }
    }
    
    /**
     * 🟠 구성원관리 AI 기능
     */
    public function membersAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeMemberCapabilities($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestMemberRoles($data);
            case self::AI_TYPE_GENERATE:
                return $this->generateTrainingPlan($data);
            default:
                return $this->createErrorResponse('지원하지 않는 구성원관리 AI 기능입니다.');
        }
    }
    
    /**
     * 🟡 Task관리 AI 기능
     */
    public function tasksAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_OPTIMIZE:
                return $this->optimizeTasks($data);
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeTaskLoad($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestTaskPriorities($data);
            default:
                return $this->createErrorResponse('지원하지 않는 Task관리 AI 기능입니다.');
        }
    }
    
    /**
     * 🟢 문서관리 AI 기능
     */
    public function documentsAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_SUMMARIZE:
                return $this->summarizeDocument($data);
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeDocumentContent($data);
            case self::AI_TYPE_GENERATE:
                return $this->generateDocumentTemplate($data);
            default:
                return $this->createErrorResponse('지원하지 않는 문서관리 AI 기능입니다.');
        }
    }
    
    /**
     * 🔵 Process Map AI 기능
     */
    public function processMapAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeProcessMap($data);
            case self::AI_TYPE_OPTIMIZE:
                return $this->optimizeProcess($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestProcessAutomation($data);
            default:
                return $this->createErrorResponse('지원하지 않는 Process Map AI 기능입니다.');
        }
    }
    
    /**
     * 🟣 업무Flow AI 기능
     */
    public function workflowAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeWorkflow($data);
            case self::AI_TYPE_OPTIMIZE:
                return $this->optimizeWorkflow($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestWorkflowImprovements($data);
            default:
                return $this->createErrorResponse('지원하지 않는 업무Flow AI 기능입니다.');
        }
    }
    
    /**
     * 🟤 직무분석 AI 기능
     */
    public function jobAnalysisAI(string $aiType, array $data): array
    {
        switch ($aiType) {
            case self::AI_TYPE_GENERATE:
                return $this->generateJobDescription($data);
            case self::AI_TYPE_ANALYZE:
                return $this->analyzeJobRequirements($data);
            case self::AI_TYPE_SUGGEST:
                return $this->suggestJobCompetencies($data);
            default:
                return $this->createErrorResponse('지원하지 않는 직무분석 AI 기능입니다.');
        }
    }
    
    // ========== Private Helper Methods ==========
    
    /**
     * 모듈별 AI 처리 라우팅
     */
    private function processModuleAI(string $module, string $aiType, array $data, array $options): array
    {
        switch ($module) {
            case self::MODULE_ORGANIZATION:
                return $this->organizationAI($aiType, $data);
            case self::MODULE_MEMBERS:
                return $this->membersAI($aiType, $data);
            case self::MODULE_TASKS:
                return $this->tasksAI($aiType, $data);
            case self::MODULE_DOCUMENTS:
                return $this->documentsAI($aiType, $data);
            case self::MODULE_PROCESS_MAP:
                return $this->processMapAI($aiType, $data);
            case self::MODULE_WORKFLOW:
                return $this->workflowAI($aiType, $data);
            case self::MODULE_JOB_ANALYSIS:
                return $this->jobAnalysisAI($aiType, $data);
            default:
                return $this->createErrorResponse('지원하지 않는 모듈입니다.');
        }
    }
    
    /**
     * 권한 검증
     */
    private function validatePermissions(string $module, string $aiType): bool
    {
        if (!$this->auth->isLoggedIn()) {
            return false;
        }
        
        $user = $this->auth->getCurrentUser();
        $userRole = $user['role'] ?? 'member';
        
        // 기본적으로 모든 로그인한 사용자는 AI 기능 사용 가능
        // 단, 관리 권한이 필요한 기능들은 별도 체크
        $adminRequiredModules = [self::MODULE_ORGANIZATION];
        
        if (in_array($module, $adminRequiredModules) && !in_array($userRole, ['admin', 'founder'])) {
            return false;
        }
        
        return true;
    }
    
    /**
     * 요청 데이터 검증
     */
    private function validateRequestData(string $module, string $aiType, array $data): array
    {
        // 기본 검증
        if (empty($data)) {
            return [
                'success' => false,
                'message' => '요청 데이터가 비어있습니다.'
            ];
        }
        
        // 모듈별 필수 필드 검증
        $requiredFields = $this->getRequiredFields($module, $aiType);
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => "필수 필드가 누락되었습니다: {$field}"
                ];
            }
        }
        
        // 데이터 정제
        $sanitizedData = $this->sanitizeData($data);
        
        return [
            'success' => true,
            'data' => $sanitizedData
        ];
    }
    
    /**
     * 모듈별 필수 필드 반환
     */
    private function getRequiredFields(string $module, string $aiType): array
    {
        $fieldMap = [
            self::MODULE_ORGANIZATION => ['departments', 'employee_count'],
            self::MODULE_MEMBERS => ['members'],
            self::MODULE_TASKS => ['tasks'],
            self::MODULE_DOCUMENTS => ['content'],
            self::MODULE_PROCESS_MAP => ['process_data'],
            self::MODULE_WORKFLOW => ['workflow_steps'],
            self::MODULE_JOB_ANALYSIS => ['job_title']
        ];
        
        return $fieldMap[$module] ?? [];
    }
    
    /**
     * 데이터 정제
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = $this->security->sanitizeInput($value, 'text');
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * 사용량 제한 체크
     */
    private function checkUsageLimit(string $module): bool
    {
        $user = $this->auth->getCurrentUser();
        $userId = $user['id'] ?? null;
        $companyId = $user['company_id'] ?? null;
        
        if (!$userId || !$companyId) {
            return false;
        }
        
        // 일일 사용량 체크 (회사별 제한)
        $dailyLimit = 100; // 하루 100회
        $today = date('Y-m-d');
        
        $stmt = $this->database->prepare("
            SELECT COUNT(*) as usage_count
            FROM bpm_ai_usage_logs
            WHERE company_id = ? AND DATE(created_at) = ?
        ");
        $stmt->execute([$companyId, $today]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return ($result['usage_count'] ?? 0) < $dailyLimit;
    }
    
    /**
     * 사용량 기록
     */
    private function recordUsage(string $module, string $aiType, array $result): void
    {
        try {
            $user = $this->auth->getCurrentUser();
            $userId = $user['id'] ?? null;
            $companyId = $user['company_id'] ?? null;
            
            if (!$userId || !$companyId) {
                return;
            }
            
            // AI 사용량 로그 테이블이 없다면 생성
            $this->ensureUsageLogTable();
            
            $stmt = $this->database->prepare("
                INSERT INTO bpm_ai_usage_logs (
                    id, company_id, user_id, module, ai_type, 
                    success, tokens_used, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $logId = bin2hex(random_bytes(16));
            $tokensUsed = $result['usage']['total_tokens'] ?? 0;
            
            $stmt->execute([
                $logId, $companyId, $userId, $module, $aiType,
                $result['success'] ? 1 : 0, $tokensUsed
            ]);
            
        } catch (\Exception $e) {
            BPMLogger::error('AI 사용량 기록 실패', [
                'error' => $e->getMessage(),
                'module' => $module,
                'ai_type' => $aiType
            ]);
        }
    }
    
    /**
     * AI 사용량 로그 테이블 생성 (필요시)
     */
    private function ensureUsageLogTable(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS bpm_ai_usage_logs (
                id CHAR(32) PRIMARY KEY,
                company_id CHAR(36) NOT NULL,
                user_id CHAR(36) NOT NULL,
                module VARCHAR(50) NOT NULL,
                ai_type VARCHAR(50) NOT NULL,
                success BOOLEAN DEFAULT FALSE,
                tokens_used INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_company_date (company_id, created_at),
                INDEX idx_user_module (user_id, module),
                INDEX idx_created_at (created_at)
            )
        ";
        
        $this->database->exec($sql);
    }
    
    /**
     * AI 요청 로깅
     */
    private function logAIRequest(string $module, string $aiType, bool $success): void
    {
        $this->security->auditLog('ai_request', [
            'module' => $module,
            'ai_type' => $aiType,
            'success' => $success,
            'user_id' => $this->auth->getCurrentUser()['id'] ?? null,
            'company_id' => $this->auth->getCurrentUser()['company_id'] ?? null
        ]);
    }
    
    // ========== 구체적인 AI 기능 구현 ==========
    
    /**
     * 조직 구조 분석
     */
    private function analyzeOrganizationStructure(array $data): array
    {
        return $this->geminiAI->analyzeOrganization($data);
    }
    
    /**
     * 조직 최적화
     */
    private function optimizeOrganization(array $data): array
    {
        // 조직 최적화 전용 프롬프트로 Gemini 호출
        $optimizationData = array_merge($data, [
            'analysis_type' => 'optimization',
            'focus_areas' => ['efficiency', 'communication', 'scalability']
        ]);
        
        return $this->geminiAI->analyzeOrganization($optimizationData);
    }
    
    /**
     * 직무기술서 생성
     */
    private function generateJobDescription(array $data): array
    {
        return $this->geminiAI->generateJobDescription($data);
    }
    
    /**
     * Task 최적화
     */
    private function optimizeTasks(array $data): array
    {
        return $this->geminiAI->optimizeTasks($data);
    }
    
    /**
     * 문서 요약
     */
    private function summarizeDocument(array $data): array
    {
        $content = $data['content'] ?? '';
        return $this->geminiAI->analyzeDocument($content, 'summary');
    }
    
    /**
     * 프로세스 분석
     */
    private function analyzeProcessMap(array $data): array
    {
        return $this->geminiAI->analyzeProcess($data);
    }
    
    // ========== 추가 구현이 필요한 메서드들 (기본 구조만 제공) ==========
    
    private function suggestOrganizationChanges(array $data): array
    {
        return $this->createSuccessResponse('조직 변경 제안 기능은 준비 중입니다.');
    }
    
    private function analyzeMemberCapabilities(array $data): array
    {
        return $this->createSuccessResponse('구성원 역량 분석 기능은 준비 중입니다.');
    }
    
    private function suggestMemberRoles(array $data): array
    {
        return $this->createSuccessResponse('구성원 역할 제안 기능은 준비 중입니다.');
    }
    
    private function generateTrainingPlan(array $data): array
    {
        return $this->createSuccessResponse('교육 계획 생성 기능은 준비 중입니다.');
    }
    
    private function analyzeTaskLoad(array $data): array
    {
        return $this->createSuccessResponse('업무 부하 분석 기능은 준비 중입니다.');
    }
    
    private function suggestTaskPriorities(array $data): array
    {
        return $this->createSuccessResponse('업무 우선순위 제안 기능은 준비 중입니다.');
    }
    
    private function analyzeDocumentContent(array $data): array
    {
        $content = $data['content'] ?? '';
        return $this->geminiAI->analyzeDocument($content, 'analysis');
    }
    
    private function generateDocumentTemplate(array $data): array
    {
        return $this->createSuccessResponse('문서 템플릿 생성 기능은 준비 중입니다.');
    }
    
    private function optimizeProcess(array $data): array
    {
        return $this->geminiAI->analyzeProcess($data);
    }
    
    private function suggestProcessAutomation(array $data): array
    {
        return $this->createSuccessResponse('프로세스 자동화 제안 기능은 준비 중입니다.');
    }
    
    private function analyzeWorkflow(array $data): array
    {
        return $this->createSuccessResponse('워크플로우 분석 기능은 준비 중입니다.');
    }
    
    private function optimizeWorkflow(array $data): array
    {
        return $this->createSuccessResponse('워크플로우 최적화 기능은 준비 중입니다.');
    }
    
    private function suggestWorkflowImprovements(array $data): array
    {
        return $this->createSuccessResponse('워크플로우 개선 제안 기능은 준비 중입니다.');
    }
    
    private function analyzeJobRequirements(array $data): array
    {
        return $this->createSuccessResponse('직무 요구사항 분석 기능은 준비 중입니다.');
    }
    
    private function suggestJobCompetencies(array $data): array
    {
        return $this->createSuccessResponse('직무 역량 제안 기능은 준비 중입니다.');
    }
    
    /**
     * 성공 응답 생성
     */
    private function createSuccessResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }
    
    /**
     * 오류 응답 생성
     */
    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }
    
    /**
     * 모듈별 색상 반환
     */
    public function getModuleColor(string $module): string
    {
        return $this->moduleColors[$module] ?? '#cccccc';
    }
    
    /**
     * 사용 가능한 모듈 목록 반환
     */
    public function getAvailableModules(): array
    {
        return [
            self::MODULE_ORGANIZATION => '조직관리',
            self::MODULE_MEMBERS => '구성원관리',
            self::MODULE_TASKS => 'Task관리',
            self::MODULE_DOCUMENTS => '문서관리',
            self::MODULE_PROCESS_MAP => 'Process Map',
            self::MODULE_WORKFLOW => '업무Flow',
            self::MODULE_JOB_ANALYSIS => '직무분석'
        ];
    }
    
    /**
     * 모듈별 사용 가능한 AI 기능 반환
     */
    public function getModuleAIFeatures(string $module): array
    {
        $features = [
            self::MODULE_ORGANIZATION => [
                self::AI_TYPE_ANALYZE => '조직 구조 분석',
                self::AI_TYPE_OPTIMIZE => '조직 최적화',
                self::AI_TYPE_SUGGEST => '개선 제안'
            ],
            self::MODULE_MEMBERS => [
                self::AI_TYPE_ANALYZE => '역량 분석',
                self::AI_TYPE_SUGGEST => '역할 제안',
                self::AI_TYPE_GENERATE => '교육 계획'
            ],
            self::MODULE_TASKS => [
                self::AI_TYPE_OPTIMIZE => 'Task 최적화',
                self::AI_TYPE_ANALYZE => '업무 부하 분석',
                self::AI_TYPE_SUGGEST => '우선순위 제안'
            ],
            self::MODULE_DOCUMENTS => [
                self::AI_TYPE_SUMMARIZE => '문서 요약',
                self::AI_TYPE_ANALYZE => '내용 분석',
                self::AI_TYPE_GENERATE => '템플릿 생성'
            ],
            self::MODULE_PROCESS_MAP => [
                self::AI_TYPE_ANALYZE => '프로세스 분석',
                self::AI_TYPE_OPTIMIZE => '프로세스 최적화',
                self::AI_TYPE_SUGGEST => '자동화 제안'
            ],
            self::MODULE_WORKFLOW => [
                self::AI_TYPE_ANALYZE => '워크플로우 분석',
                self::AI_TYPE_OPTIMIZE => '워크플로우 최적화',
                self::AI_TYPE_SUGGEST => '개선 제안'
            ],
            self::MODULE_JOB_ANALYSIS => [
                self::AI_TYPE_GENERATE => '직무기술서 생성',
                self::AI_TYPE_ANALYZE => '직무 요구사항 분석',
                self::AI_TYPE_SUGGEST => '역량 제안'
            ]
        ];
        
        return $features[$module] ?? [];
    }
}