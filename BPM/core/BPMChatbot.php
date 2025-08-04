<?php
// 📁 C:\xampp\htdocs\BPM\core\BPMChatbot.php
// Create at 2508031200 Ver1.00

namespace BPM\Core;

/**
 * BPM AI 챗봇 헬프데스크 시스템
 * 시스템 사용법 안내, 메뉴얼 검색, 사용자 피드백 수집
 * 모듈: AI 지원 시스템 (색상: #00ff88)
 */
class BPMChatbot
{
    private static $instance = null;
    private $geminiAI;
    private $auth;
    private $database;
    private $security;
    
    // 챗봇 모드
    const MODE_HELP = 'help';           // 도움말 모드
    const MODE_MANUAL = 'manual';       // 메뉴얼 검색 모드
    const MODE_FEEDBACK = 'feedback';   // 피드백 수집 모드
    const MODE_GENERAL = 'general';     // 일반 대화 모드
    
    // 응답 타입
    const RESPONSE_ANSWER = 'answer';
    const RESPONSE_GUIDE = 'guide';
    const RESPONSE_ERROR = 'error';
    const RESPONSE_FEEDBACK_SAVED = 'feedback_saved';
    
    private function __construct()
    {
        $this->geminiAI = GeminiAI::getInstance();
        $this->auth = Auth::getInstance();
        $this->database = Database::getInstance();
        $this->security = Security::getInstance();
        
        // 필요한 테이블 생성
        $this->ensureTables();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 챗봇 메시지 처리 메인 메서드
     */
    public function processMessage(string $message, ?string $context = null, ?array $options = []): array
    {
        try {
            // 입력 검증 및 정제
            $message = $this->security->sanitizeInput($message, 'text');
            $context = $context ? $this->security->sanitizeInput($context, 'text') : null;
            
            if (empty(trim($message))) {
                return $this->createResponse(self::RESPONSE_ERROR, '메시지를 입력해주세요.');
            }
            
            // 사용량 제한 체크
            if (!$this->checkUsageLimit()) {
                return $this->createResponse(self::RESPONSE_ERROR, '일일 챗봇 사용량을 초과했습니다.');
            }
            
            // 메시지 의도 분석
            $intent = $this->analyzeIntent($message);
            
            // 의도에 따른 처리
            $response = $this->processIntent($intent, $message, $context, $options);
            
            // 대화 기록 저장
            $this->saveChatHistory($message, $response, $intent, $context);
            
            return $response;
            
        } catch (\Exception $e) {
            BPMLogger::error('챗봇 메시지 처리 중 오류', [
                'message' => $message,
                'error' => $e->getMessage(),
                'user_id' => $this->auth->getCurrentUser()['id'] ?? null
            ]);
            
            return $this->createResponse(self::RESPONSE_ERROR, '죄송합니다. 처리 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 사용자 피드백 저장
     */
    public function saveFeedback(array $feedbackData): array
    {
        try {
            $user = $this->auth->getCurrentUser();
            if (!$user) {
                return $this->createResponse(self::RESPONSE_ERROR, '로그인이 필요합니다.');
            }
            
            // 피드백 데이터 검증
            $requiredFields = ['type', 'title', 'description'];
            foreach ($requiredFields as $field) {
                if (empty($feedbackData[$field])) {
                    return $this->createResponse(self::RESPONSE_ERROR, "필수 필드가 누락되었습니다: {$field}");
                }
            }
            
            // 피드백 저장
            $feedbackId = $this->generateUUID();
            $stmt = $this->database->prepare("
                INSERT INTO bpm_user_feedback (
                    id, user_id, company_id, feedback_type, title, description,
                    page_url, user_agent, priority, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            
            $stmt->execute([
                $feedbackId,
                $user['id'],
                $user['company_id'],
                $feedbackData['type'],
                $feedbackData['title'],
                $feedbackData['description'],
                $feedbackData['page_url'] ?? '',
                $_SERVER['HTTP_USER_AGENT'] ?? '',
                $feedackData['priority'] ?? 'medium'
            ]);
            
            // 관리자에게 알림 (향후 구현)
            $this->notifyAdminsAboutFeedback($feedbackId, $feedbackData);
            
            return $this->createResponse(self::RESPONSE_FEEDBACK_SAVED, '피드백이 성공적으로 등록되었습니다. 검토 후 개선에 반영하겠습니다.');
            
        } catch (\Exception $e) {
            BPMLogger::error('피드백 저장 중 오류', [
                'feedback_data' => $feedbackData,
                'error' => $e->getMessage()
            ]);
            
            return $this->createResponse(self::RESPONSE_ERROR, '피드백 저장 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 메뉴얼 검색
     */
    public function searchManual(string $query): array
    {
        try {
            // 메뉴얼 데이터베이스에서 검색
            $manualResults = $this->searchManualDatabase($query);
            
            if (empty($manualResults)) {
                return $this->createResponse(self::RESPONSE_ANSWER, '해당 내용에 대한 메뉴얼을 찾을 수 없습니다. 구체적인 질문을 해주시거나 피드백으로 문의해주세요.');
            }
            
            // AI를 활용한 답변 생성
            $manualContent = $this->formatManualContent($manualResults);
            $aiResponse = $this->generateAIResponse($query, $manualContent);
            
            return $this->createResponse(self::RESPONSE_ANSWER, $aiResponse['text'], [
                'sources' => $manualResults,
                'confidence' => $aiResponse['confidence'] ?? 0.8
            ]);
            
        } catch (\Exception $e) {
            BPMLogger::error('메뉴얼 검색 중 오류', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            return $this->createResponse(self::RESPONSE_ERROR, '메뉴얼 검색 중 오류가 발생했습니다.');
        }
    }
    
    // ========== Private Helper Methods ==========
    
    /**
     * 메시지 의도 분석
     */
    private function analyzeIntent(string $message): string
    {
        $message = strtolower($message);
        
        // 키워드 기반 의도 분석
        $intentKeywords = [
            self::MODE_HELP => ['도움', '도와', '사용법', '어떻게', '방법', '모르겠', '헬프'],
            self::MODE_MANUAL => ['메뉴얼', '설명서', '가이드', '문서', '매뉴얼'],
            self::MODE_FEEDBACK => ['개선', '요청', '불편', '문제', '버그', '건의', '피드백', '제안'],
        ];
        
        foreach ($intentKeywords as $intent => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    return $intent;
                }
            }
        }
        
        return self::MODE_GENERAL;
    }
    
    /**
     * 의도에 따른 처리
     */
    private function processIntent(string $intent, string $message, ?string $context, array $options): array
    {
        switch ($intent) {
            case self::MODE_HELP:
                return $this->processHelpRequest($message, $context);
                
            case self::MODE_MANUAL:
                return $this->searchManual($message);
                
            case self::MODE_FEEDBACK:
                return $this->processFeedbackRequest($message, $context);
                
            case self::MODE_GENERAL:
            default:
                return $this->processGeneralChat($message, $context);
        }
    }
    
    /**
     * 도움말 요청 처리
     */
    private function processHelpRequest(string $message, ?string $context): array
    {
        // 현재 페이지 컨텍스트 기반 도움말 제공
        $contextualHelp = $this->getContextualHelp($context);
        
        // AI를 활용한 맞춤형 도움말 생성
        $prompt = $this->buildHelpPrompt($message, $context, $contextualHelp);
        $aiResponse = $this->geminiAI->generateText($prompt, [
            'temperature' => 0.3,
            'max_tokens' => 1000
        ]);
        
        if ($aiResponse['success']) {
            return $this->createResponse(self::RESPONSE_GUIDE, $aiResponse['text'], [
                'context' => $context,
                'help_type' => 'contextual'
            ]);
        }
        
        return $this->createResponse(self::RESPONSE_ERROR, '도움말 생성 중 오류가 발생했습니다.');
    }
    
    /**
     * 피드백 요청 처리
     */
    private function processFeedbackRequest(string $message, ?string $context): array
    {
        // 피드백 폼 생성 안내
        return $this->createResponse(self::RESPONSE_GUIDE, 
            '피드백을 남겨주셔서 감사합니다! 구체적인 내용을 입력해주시면 검토 후 개선에 반영하겠습니다.',
            [
                'action' => 'show_feedback_form',
                'context' => $context,
                'suggested_type' => $this->suggestFeedbackType($message)
            ]
        );
    }
    
    /**
     * 일반 대화 처리
     */
    private function processGeneralChat(string $message, ?string $context): array
    {
        // BPM 시스템 관련 일반적인 질의응답
        $prompt = $this->buildGeneralChatPrompt($message, $context);
        $aiResponse = $this->geminiAI->generateText($prompt, [
            'temperature' => 0.5,
            'max_tokens' => 800
        ]);
        
        if ($aiResponse['success']) {
            return $this->createResponse(self::RESPONSE_ANSWER, $aiResponse['text']);
        }
        
        return $this->createResponse(self::RESPONSE_ERROR, '답변 생성 중 오류가 발생했습니다.');
    }
    
    /**
     * 메뉴얼 데이터베이스 검색
     */
    private function searchManualDatabase(string $query): array
    {
        $stmt = $this->database->prepare("
            SELECT m.*, mc.category_name
            FROM bpm_system_manual m
            LEFT JOIN bpm_manual_categories mc ON m.category_id = mc.id
            WHERE m.status = 'active' 
            AND (
                MATCH(m.title, m.content) AGAINST(? IN NATURAL LANGUAGE MODE)
                OR m.title LIKE CONCAT('%', ?, '%')
                OR m.keywords LIKE CONCAT('%', ?, '%')
            )
            ORDER BY 
                MATCH(m.title, m.content) AGAINST(? IN NATURAL LANGUAGE MODE) DESC,
                m.priority DESC
            LIMIT 5
        ");
        
        $stmt->execute([$query, $query, $query, $query]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * 메뉴얼 내용 포맷팅
     */
    private function formatManualContent(array $manualResults): string
    {
        $content = "";
        foreach ($manualResults as $manual) {
            $content .= "## {$manual['title']}\n";
            $content .= "카테고리: {$manual['category_name']}\n";
            $content .= "{$manual['content']}\n\n";
        }
        return $content;
    }
    
    /**
     * AI 응답 생성
     */
    private function generateAIResponse(string $query, string $manualContent): array
    {
        $prompt = "
사용자 질문: {$query}

관련 메뉴얼 내용:
{$manualContent}

위 메뉴얼 내용을 바탕으로 사용자의 질문에 친절하고 정확하게 답변해주세요.
- 단계별로 설명하되 간결하게 작성
- 실무에 바로 적용할 수 있도록 구체적으로 안내
- 추가 도움이 필요하면 피드백 기능을 안내
- 한국어로 답변
";
        
        return $this->geminiAI->generateText($prompt, [
            'temperature' => 0.4,
            'max_tokens' => 1200
        ]);
    }
    
    /**
     * 컨텍스트별 도움말 반환
     */
    private function getContextualHelp(?string $context): array
    {
        $contextHelp = [
            'dashboard' => [
                'title' => '대시보드 사용법',
                'content' => '대시보드에서는 전체 업무 현황과 통계를 확인할 수 있습니다.'
            ],
            'job-description' => [
                'title' => 'AI 직무기술서 작성 방법',
                'content' => '직무 정보를 입력하고 AI 생성 버튼을 클릭하면 전문적인 직무기술서가 자동으로 생성됩니다.'
            ],
            'organization' => [
                'title' => '조직관리 기능',
                'content' => '회사 구조, 부서 관리, 조직도 설정 등을 관리할 수 있습니다.'
            ]
        ];
        
        return $contextHelp[$context] ?? [
            'title' => 'BPM 시스템 도움말',
            'content' => 'BPM 시스템의 전반적인 기능에 대해 안내해드립니다.'
        ];
    }
    
    /**
     * 도움말 프롬프트 생성
     */
    private function buildHelpPrompt(string $message, ?string $context, array $contextualHelp): string
    {
        return "
BPM 업무관리 시스템의 AI 어시스턴트입니다.

사용자 질문: {$message}
현재 페이지: {$context}
페이지 설명: {$contextualHelp['content']}

다음 지침에 따라 친절하고 정확한 도움말을 제공해주세요:
1. 현재 페이지 맥락을 고려한 맞춤형 안내
2. 단계별로 구체적인 사용법 설명
3. 실무에 바로 적용할 수 있는 실용적인 조언
4. 추가 질문이 있으면 언제든 물어보라고 안내
5. 한국어로 친근하게 답변

BPM 시스템 주요 기능:
- 조직관리: 회사/부서 구조 관리
- 구성원관리: 사용자 및 권한 관리  
- 업무관리: Task, Process, Workflow 관리
- 운영관리: 문서관리, AI 직무기술서 작성
- AI 기능: 각 모듈별 AI 지원 기능
";
    }
    
    /**
     * 일반 대화 프롬프트 생성
     */
    private function buildGeneralChatPrompt(string $message, ?string $context): string
    {
        return "
BPM(Business Process Management) 시스템의 AI 어시스턴트입니다.

사용자 메시지: {$message}
현재 위치: {$context}

BPM 시스템과 관련된 질문에 친절하고 정확하게 답변해주세요:
- 업무 프로세스 관리에 대한 조언
- 조직 운영 개선 방안
- 시스템 활용 팁
- 일반적인 업무 관리 가이드

답변 원칙:
1. 친근하고 전문적인 톤
2. 실무에 도움이 되는 구체적인 내용
3. BPM 시스템 기능과 연결한 설명
4. 한국어로 자연스럽게 답변
";
    }
    
    /**
     * 피드백 타입 제안
     */
    private function suggestFeedbackType(string $message): string
    {
        $message = strtolower($message);
        
        if (strpos($message, '버그') !== false || strpos($message, '오류') !== false) {
            return 'bug';
        } elseif (strpos($message, '개선') !== false || strpos($message, '제안') !== false) {
            return 'improvement';
        } elseif (strpos($message, '기능') !== false) {
            return 'feature_request';
        }
        
        return 'general';
    }
    
    /**
     * 사용량 제한 체크
     */
    private function checkUsageLimit(): bool
    {
        $user = $this->auth->getCurrentUser();
        if (!$user) return false;
        
        $dailyLimit = 50; // 하루 50회
        $today = date('Y-m-d');
        
        $stmt = $this->database->prepare("
            SELECT COUNT(*) as chat_count
            FROM bpm_chatbot_history
            WHERE user_id = ? AND DATE(created_at) = ?
        ");
        $stmt->execute([$user['id'], $today]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return ($result['chat_count'] ?? 0) < $dailyLimit;
    }
    
    /**
     * 대화 기록 저장
     */
    private function saveChatHistory(string $message, array $response, string $intent, ?string $context): void
    {
        try {
            $user = $this->auth->getCurrentUser();
            if (!$user) return;
            
            $stmt = $this->database->prepare("
                INSERT INTO bpm_chatbot_history (
                    id, user_id, company_id, user_message, bot_response, 
                    intent, context_page, response_type, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $this->generateUUID(),
                $user['id'],
                $user['company_id'],
                $message,
                json_encode($response),
                $intent,
                $context,
                $response['type']
            ]);
            
        } catch (\Exception $e) {
            BPMLogger::error('챗봇 기록 저장 실패', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * 관리자 알림 (향후 구현)
     */
    private function notifyAdminsAboutFeedback(string $feedbackId, array $feedbackData): void
    {
        // 이메일 알림, 시스템 알림 등 구현 예정
        BPMLogger::info('새로운 피드백 등록', [
            'feedback_id' => $feedbackId,
            'type' => $feedbackData['type'],
            'title' => $feedbackData['title']
        ]);
    }
    
    /**
     * 필요한 테이블 생성
     */
    private function ensureTables(): void
    {
        $tables = [
            // 챗봇 대화 기록
            "CREATE TABLE IF NOT EXISTS bmp_chatbot_history (
                id CHAR(36) PRIMARY KEY,
                user_id CHAR(36) NOT NULL,
                company_id CHAR(36) NOT NULL,
                user_message TEXT NOT NULL,
                bot_response JSON NOT NULL,
                intent VARCHAR(50) DEFAULT 'general',
                context_page VARCHAR(100),
                response_type VARCHAR(50) DEFAULT 'answer',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                
                INDEX idx_user_date (user_id, created_at),
                INDEX idx_company_date (company_id, created_at),
                INDEX idx_intent (intent)
            )",
            
            // 사용자 피드백
            "CREATE TABLE IF NOT EXISTS bpm_user_feedback (
                id CHAR(36) PRIMARY KEY,
                user_id CHAR(36) NOT NULL,
                company_id CHAR(36) NOT NULL,
                feedback_type ENUM('bug', 'improvement', 'feature_request', 'general') DEFAULT 'general',
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                page_url VARCHAR(500),
                user_agent TEXT,
                priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
                status ENUM('pending', 'in_review', 'in_progress', 'resolved', 'rejected') DEFAULT 'pending',
                admin_notes TEXT,
                resolved_by CHAR(36),
                resolved_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                INDEX idx_user_feedback (user_id, created_at),
                INDEX idx_status_priority (status, priority),
                INDEX idx_type_status (feedback_type, status)
            )",
            
            // 시스템 메뉴얼
            "CREATE TABLE IF NOT EXISTS bpm_system_manual (
                id CHAR(36) PRIMARY KEY,
                category_id CHAR(36),
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                keywords TEXT,
                priority INT DEFAULT 1,
                status ENUM('active', 'draft', 'archived') DEFAULT 'active',
                created_by CHAR(36),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                
                FULLTEXT(title, content),
                INDEX idx_category_status (category_id, status),
                INDEX idx_priority (priority)
            )",
            
            // 메뉴얼 카테고리
            "CREATE TABLE IF NOT EXISTS bpm_manual_categories (
                id CHAR(36) PRIMARY KEY,
                category_name VARCHAR(100) NOT NULL,
                description TEXT,
                display_order INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )"
        ];
        
        foreach ($tables as $sql) {
            try {
                $this->database->exec($sql);
            } catch (\Exception $e) {
                BPMLogger::error('챗봇 테이블 생성 실패', [
                    'sql' => $sql,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
    
    /**
     * 응답 생성
     */
    private function createResponse(string $type, string $message, array $data = []): array
    {
        return [
            'type' => $type,
            'message' => $message,
            'data' => $data,
            'timestamp' => time()
        ];
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
}