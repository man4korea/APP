<?php
// 📁 C:\xampp\htdocs\BPM\core\GeminiAI.php
// Create at 2508031030 Ver1.00

namespace BPM\Core;

/**
 * Google Gemini Flash API 연동 클래스
 * BPM 시스템의 AI 기능을 위한 Gemini API 통합
 */
class GeminiAI
{
    private static $instance = null;
    private $apiKey;
    private $model;
    private $baseUrl;
    private $maxTokens;
    private $temperature;
    
    // 기본 설정
    const DEFAULT_MODEL = 'gemini-1.5-flash';
    const DEFAULT_MAX_TOKENS = 4000;
    const DEFAULT_TEMPERATURE = 0.7;
    const API_BASE_URL = 'https://generativelanguage.googleapis.com/v1beta/models/';
    
    // 컨텍스트 타입
    const CONTEXT_JOB_DESCRIPTION = 'job_description';
    const CONTEXT_PROCESS_ANALYSIS = 'process_analysis';
    const CONTEXT_ORGANIZATION = 'organization';
    const CONTEXT_TASK_MANAGEMENT = 'task_management';
    const CONTEXT_DOCUMENT = 'document';
    
    private function __construct()
    {
        $this->apiKey = GEMINI_API_KEY;
        $this->model = $_ENV['GOOGLE_MODEL'] ?? self::DEFAULT_MODEL;
        $this->baseUrl = self::API_BASE_URL;
        $this->maxTokens = intval($_ENV['GOOGLE_MAX_TOKENS'] ?? self::DEFAULT_MAX_TOKENS);
        $this->temperature = floatval($_ENV['GOOGLE_TEMPERATURE'] ?? self::DEFAULT_TEMPERATURE);
        
        if (empty($this->apiKey)) {
            throw new \Exception('Gemini API 키가 설정되지 않았습니다.');
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Gemini API 텍스트 생성 요청
     */
    public function generateText(string $prompt, array $options = []): array
    {
        try {
            // 옵션 설정
            $model = $options['model'] ?? $this->model;
            $maxTokens = $options['max_tokens'] ?? $this->maxTokens;
            $temperature = $options['temperature'] ?? $this->temperature;
            
            // API 요청 URL
            $url = $this->baseUrl . $model . ':generateContent?key=' . $this->apiKey;
            
            // 요청 데이터
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $temperature,
                    'maxOutputTokens' => $maxTokens,
                    'topP' => 0.95,
                    'topK' => 64
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
                    ]
                ]
            ];
            
            // HTTP 요청 실행
            $response = $this->makeHttpRequest($url, 'POST', $requestData);
            
            // 응답 처리
            return $this->processResponse($response, $prompt);
            
        } catch (\Exception $e) {
            BPMLogger::error('Gemini API 요청 실패', [
                'error' => $e->getMessage(),
                'prompt_length' => strlen($prompt),
                'model' => $model ?? 'unknown'
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'fallback' => '죄송합니다. AI 서비스가 일시적으로 사용할 수 없습니다.'
            ];
        }
    }
    
    /**
     * 직무기술서 생성 전용 메서드
     */
    public function generateJobDescription(array $jobData): array
    {
        $prompt = $this->buildJobDescriptionPrompt($jobData);
        
        $options = [
            'temperature' => 0.6, // 더 정확한 응답을 위해 낮춤
            'max_tokens' => 3000
        ];
        
        return $this->generateText($prompt, $options);
    }
    
    /**
     * 프로세스 분석 및 최적화 제안
     */
    public function analyzeProcess(array $processData): array
    {
        $prompt = $this->buildProcessAnalysisPrompt($processData);
        
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 2500
        ];
        
        return $this->generateText($prompt, $options);
    }
    
    /**
     * 조직 구조 분석 및 개선 제안
     */
    public function analyzeOrganization(array $orgData): array
    {
        $prompt = $this->buildOrganizationAnalysisPrompt($orgData);
        
        $options = [
            'temperature' => 0.8,
            'max_tokens' => 2000
        ];
        
        return $this->generateText($prompt, $options);
    }
    
    /**
     * Task 관리 최적화 제안
     */
    public function optimizeTasks(array $taskData): array
    {
        $prompt = $this->buildTaskOptimizationPrompt($taskData);
        
        $options = [
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        return $this->generateText($prompt, $options);
    }
    
    /**
     * 문서 요약 및 분석
     */
    public function analyzeDocument(string $content, string $analysisType = 'summary'): array
    {
        $prompt = $this->buildDocumentAnalysisPrompt($content, $analysisType);
        
        $options = [
            'temperature' => 0.5,
            'max_tokens' => 1500
        ];
        
        return $this->generateText($prompt, $options);
    }
    
    // ========== Private Helper Methods ==========
    
    /**
     * HTTP 요청 실행
     */
    private function makeHttpRequest(string $url, string $method, array $data = []): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ]
        ]);
        
        if ($method === 'POST' && !empty($data)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("cURL 오류: $error");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("HTTP 오류: $httpCode");
        }
        
        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('JSON 디코딩 오류: ' . json_last_error_msg());
        }
        
        return $decodedResponse;
    }
    
    /**
     * API 응답 처리
     */
    private function processResponse(array $response, string $originalPrompt): array
    {
        if (isset($response['error'])) {
            throw new \Exception('Gemini API 오류: ' . $response['error']['message']);
        }
        
        if (!isset($response['candidates']) || empty($response['candidates'])) {
            throw new \Exception('응답 생성에 실패했습니다.');
        }
        
        $candidate = $response['candidates'][0];
        
        if (!isset($candidate['content']['parts'][0]['text'])) {
            throw new \Exception('텍스트 응답을 찾을 수 없습니다.');
        }
        
        $generatedText = trim($candidate['content']['parts'][0]['text']);
        
        // 사용량 정보 수집
        $usage = [
            'prompt_tokens' => mb_strlen($originalPrompt, 'UTF-8'),
            'completion_tokens' => mb_strlen($generatedText, 'UTF-8'),
            'total_tokens' => mb_strlen($originalPrompt . $generatedText, 'UTF-8')
        ];
        
        return [
            'success' => true,
            'text' => $generatedText,
            'usage' => $usage,
            'model' => $this->model,
            'finish_reason' => $candidate['finishReason'] ?? 'unknown'
        ];
    }
    
    /**
     * 직무기술서 생성 프롬프트 구성
     */
    private function buildJobDescriptionPrompt(array $jobData): string
    {
        $jobTitle = $jobData['job_title'] ?? '직무';
        $department = $jobData['department'] ?? '';
        $level = $jobData['level'] ?? '';
        $company = $jobData['company'] ?? 'EASYCORP';
        $industry = $jobData['industry'] ?? 'IT서비스업';
        $requirements = $jobData['requirements'] ?? [];
        
        $prompt = "
한국의 {$industry} 기업인 {$company}에서 사용할 {$jobTitle} 직무기술서를 작성해주세요.

## 기본 정보
- 직무명: {$jobTitle}
- 소속부서: {$department}
- 직급/레벨: {$level}
- 회사: {$company}
- 업종: {$industry}

## 작성 요구사항
1. **직무 개요**: 해당 직무의 핵심 역할과 책임을 간결하게 설명
2. **주요 업무**: 구체적인 업무 내용을 5-8개 항목으로 나열
3. **필수 자격요건**: 학력, 경력, 기술, 자격증 등
4. **우대사항**: 추가적으로 보유하면 좋은 역량들
5. **핵심 역량**: 성공적인 업무 수행을 위해 필요한 역량 3-5개
6. **근무 조건**: 근무 형태, 업무 환경 등

## 출력 형식
마크다운 형식으로 작성하되, 한국 기업 환경에 맞게 실무적이고 구체적으로 작성해주세요.
전문적이면서도 이해하기 쉽게 작성하고, 실제 채용공고에 바로 사용할 수 있는 수준으로 완성도를 높여주세요.
";
        
        if (!empty($requirements)) {
            $prompt .= "\n## 추가 고려사항\n";
            foreach ($requirements as $req) {
                $prompt .= "- {$req}\n";
            }
        }
        
        return $prompt;
    }
    
    /**
     * 프로세스 분석 프롬프트 구성
     */
    private function buildProcessAnalysisPrompt(array $processData): string
    {
        $processName = $processData['process_name'] ?? '업무 프로세스';
        $steps = $processData['steps'] ?? [];
        $issues = $processData['issues'] ?? [];
        $goals = $processData['goals'] ?? [];
        
        $prompt = "
다음 업무 프로세스를 분석하고 최적화 방안을 제안해주세요.

## 프로세스 정보
- 프로세스명: {$processName}
- 분석 목적: 효율성 개선 및 병목 구간 해결

## 현재 Process Steps
";
        
        if (!empty($steps)) {
            foreach ($steps as $index => $step) {
                $stepNum = $index + 1;
                $prompt .= "{$stepNum}. {$step}\n";
            }
        }
        
        if (!empty($issues)) {
            $prompt .= "\n## 현재 발견된 문제점\n";
            foreach ($issues as $issue) {
                $prompt .= "- {$issue}\n";
            }
        }
        
        if (!empty($goals)) {
            $prompt .= "\n## 개선 목표\n";
            foreach ($goals as $goal) {
                $prompt .= "- {$goal}\n";
            }
        }
        
        $prompt .= "
## 분석 요청사항
1. **병목 구간 식별**: 현재 프로세스에서 지연이나 비효율이 발생하는 구간
2. **최적화 방안**: 구체적이고 실행 가능한 개선 방안 3-5개
3. **자동화 가능 영역**: 시스템이나 도구로 자동화할 수 있는 부분
4. **측정 지표**: 개선 효과를 측정할 수 있는 KPI 제안
5. **구현 우선순위**: 효과대비 구현 난이도를 고려한 우선순위

마크다운 형식으로 체계적이고 실무에 바로 적용할 수 있도록 작성해주세요.
";
        
        return $prompt;
    }
    
    /**
     * 조직 분석 프롬프트 구성
     */
    private function buildOrganizationAnalysisPrompt(array $orgData): string
    {
        $companyName = $orgData['company_name'] ?? 'EASYCORP';
        $departments = $orgData['departments'] ?? [];
        $employeeCount = $orgData['employee_count'] ?? 0;
        $issues = $orgData['issues'] ?? [];
        
        $prompt = "
{$companyName}의 조직 구조를 분석하고 최적화 방안을 제안해주세요.

## 조직 현황
- 회사명: {$companyName}
- 총 직원 수: {$employeeCount}명

## 현재 부서 구조
";
        
        if (!empty($departments)) {
            foreach ($departments as $dept) {
                $deptName = $dept['name'] ?? '';
                $memberCount = $dept['member_count'] ?? 0;
                $prompt .= "- {$deptName}: {$memberCount}명\n";
            }
        }
        
        if (!empty($issues)) {
            $prompt .= "\n## 조직 운영상 이슈\n";
            foreach ($issues as $issue) {
                $prompt .= "- {$issue}\n";
            }
        }
        
        $prompt .= "
## 분석 요청사항
1. **조직 구조 분석**: 현재 구조의 장단점과 개선점
2. **커뮤니케이션 최적화**: 부서간 협업 효율성 향상 방안
3. **역할 분담 최적화**: 업무 중복이나 공백 해결 방안
4. **성장 대응**: 조직 확장 시 고려사항
5. **문화 개선**: 조직 문화 향상을 위한 제안

한국 기업 환경을 고려하여 실무적이고 구현 가능한 방안으로 제안해주세요.
";
        
        return $prompt;
    }
    
    /**
     * Task 최적화 프롬프트 구성
     */
    private function buildTaskOptimizationPrompt(array $taskData): string
    {
        $tasks = $taskData['tasks'] ?? [];
        $teamSize = $taskData['team_size'] ?? 1;
        $timeframe = $taskData['timeframe'] ?? '1개월';
        
        $prompt = "
다음 업무 목록을 분석하여 효율적인 업무 관리 방안을 제안해주세요.

## 팀 정보
- 팀 규모: {$teamSize}명
- 목표 기간: {$timeframe}

## 현재 Task 목록
";
        
        if (!empty($tasks)) {
            foreach ($tasks as $index => $task) {
                $taskNum = $index + 1;
                $taskName = $task['name'] ?? '';
                $priority = $task['priority'] ?? 'normal';
                $estimate = $task['estimate'] ?? '';
                $prompt .= "{$taskNum}. {$taskName} (우선순위: {$priority}, 예상시간: {$estimate})\n";
            }
        }
        
        $prompt .= "
## 최적화 요청사항
1. **우선순위 재정렬**: 업무 중요도와 의존성을 고려한 순서
2. **병목 업무 식별**: 전체 진행에 영향을 주는 핵심 업무
3. **병렬 처리 가능 업무**: 동시 진행할 수 있는 업무 그룹
4. **업무 분담 제안**: 팀원별 최적 업무 할당 방안
5. **리스크 관리**: 지연 위험이 있는 업무와 대응 방안

실무에서 바로 적용할 수 있는 구체적인 관리 방안을 제안해주세요.
";
        
        return $prompt;
    }
    
    /**
     * 문서 분석 프롬프트 구성
     */
    private function buildDocumentAnalysisPrompt(string $content, string $analysisType): string
    {
        $basePrompt = "다음 문서를 분석해주세요:\n\n";
        $basePrompt .= "=== 문서 내용 ===\n{$content}\n=== 문서 내용 끝 ===\n\n";
        
        switch ($analysisType) {
            case 'summary':
                return $basePrompt . "위 문서의 핵심 내용을 3-5개 요점으로 요약해주세요.";
                
            case 'action_items':
                return $basePrompt . "위 문서에서 실행해야 할 액션 아이템들을 추출하여 우선순위와 함께 정리해주세요.";
                
            case 'risks':
                return $basePrompt . "위 문서에서 언급된 위험 요소나 주의사항들을 식별하여 대응 방안과 함께 제시해주세요.";
                
            case 'improvements':
                return $basePrompt . "위 문서의 내용을 바탕으로 개선 가능한 영역과 구체적인 개선 방안을 제안해주세요.";
                
            default:
                return $basePrompt . "위 문서를 종합적으로 분석하여 주요 내용, 핵심 포인트, 그리고 실무적 시사점을 정리해주세요.";
        }
    }
}