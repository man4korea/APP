/**
 * Shrimp Task Manager와 AI 기능 통합 서비스
 * 기존 13가지 Shrimp 기능과 8가지 AI 기능의 상호 연동 관리
 */

const BPRAIService = require('./BPRAIService');
const ShrimpTaskService = require('./ShrimpTaskService');
const UsageTracker = require('../tracking/UsageTracker');
const EventEmitter = require('events');

class ShrimpAIIntegrationService extends EventEmitter {
    constructor() {
        super();
        this.aiService = new BPRAIService();
        this.shrimpService = new ShrimpTaskService();
        this.usageTracker = new UsageTracker();
        
        // AI-Shrimp 연동 매핑
        this.integrationMappings = {
            // 기존 Shrimp 기능 + AI 지원
            'plan_task': ['ai_suggest_tasks', 'ai_optimize_process'],
            'analyze_task': ['ai_optimize_process', 'ai_predict_performance'],
            'execute_task': ['ai_generate_manual', 'ai_assistant_chat'],
            'verify_task': ['ai_predict_performance', 'ai_process_mining'],
            
            // AI 전용 기능
            'ai_generate_report': ['list_tasks', 'get_task_detail'],
            'ai_optimize_organization': ['analyze_task', 'process_thought']
        };
    }

    /**
     * 1. ENHANCED plan_task: AI 제안 통합
     */
    async planTaskWithAI(requirements, userId, options = {}) {
        try {
            // 기본 Shrimp 계획 수립
            const basicPlan = await this.shrimpService.planTask(requirements, userId);
            
            // AI 제안 활성화 여부 확인
            if (options.enableAISuggestions && await this.validatePremiumAccess(userId)) {
                
                // AI Task 제안 병렬 실행
                const [aiSuggestions, processOptimization] = await Promise.all([
                    this.aiService.suggestProcessTasks(basicPlan, userId),
                    this.aiService.optimizeProcess(basicPlan, null, userId)
                ]);

                // AI 제안과 기본 계획 융합
                const enhancedPlan = this.mergePlanWithAISuggestions(basicPlan, aiSuggestions, processOptimization);
                
                // 사용량 추적
                await this.usageTracker.recordIntegratedUsage(userId, 'enhanced_planning', {
                    shrimpTasks: basicPlan.tasks.length,
                    aiSuggestions: aiSuggestions.recommendations.add.length,
                    optimizations: processOptimization.recommendations.length
                });

                return {
                    type: 'ai_enhanced',
                    plan: enhancedPlan,
                    aiInsights: {
                        suggestions: aiSuggestions,
                        optimizations: processOptimization
                    },
                    confidence: this.calculatePlanConfidence(enhancedPlan, aiSuggestions)
                };
            }

            return {
                type: 'basic',
                plan: basicPlan
            };

        } catch (error) {
            console.error('Enhanced planning failed:', error);
            // AI 실패 시 기본 Shrimp 기능으로 fallback
            const fallbackPlan = await this.shrimpService.planTask(requirements, userId);
            return { type: 'fallback', plan: fallbackPlan, error: error.message };
        }
    }

    /**
     * 2. ENHANCED analyze_task: AI 최적화 분석 통합
     */
    async analyzeTaskWithAI(taskId, userId, options = {}) {
        try {
            // Shrimp 기본 분석
            const basicAnalysis = await this.shrimpService.analyzeTask(taskId, userId);
            
            if (options.enableAIAnalysis && await this.validatePremiumAccess(userId)) {
                
                // Task 데이터 조회
                const taskData = await this.shrimpService.getTaskDetail(taskId, userId);
                
                // AI 성과 예측 및 프로세스 최적화
                const [performancePrediction, processOptimization] = await Promise.all([
                    this.aiService.predictTaskPerformance(taskData, userId),
                    this.aiService.optimizeProcess(taskData.process, taskData.metrics, userId)
                ]);

                const enhancedAnalysis = {
                    ...basicAnalysis,
                    aiPredictions: performancePrediction,
                    optimizationRecommendations: processOptimization,
                    riskAssessment: this.calculateRiskAssessment(basicAnalysis, performancePrediction),
                    recommendedActions: this.generateActionRecommendations(basicAnalysis, processOptimization)
                };

                return {
                    type: 'ai_enhanced',
                    analysis: enhancedAnalysis,
                    confidence: this.calculateAnalysisConfidence(enhancedAnalysis)
                };
            }

            return { type: 'basic', analysis: basicAnalysis };

        } catch (error) {
            console.error('Enhanced analysis failed:', error);
            const fallbackAnalysis = await this.shrimpService.analyzeTask(taskId, userId);
            return { type: 'fallback', analysis: fallbackAnalysis, error: error.message };
        }
    }

    /**
     * 3. ENHANCED execute_task: AI 매뉴얼 생성 및 실시간 지원
     */
    async executeTaskWithAI(taskId, userId, options = {}) {
        try {
            // 실행 전 AI 매뉴얼 생성 (선택사항)
            let aiManual = null;
            if (options.generateManual && await this.validatePremiumAccess(userId)) {
                const taskData = await this.shrimpService.getTaskDetail(taskId, userId);
                aiManual = await this.aiService.generateTaskManual(taskData, userId);
            }

            // Shrimp 작업 실행 시작
            const executionResult = await this.shrimpService.executeTask(taskId, userId);

            // AI 어시스턴트 세션 초기화 (실시간 지원)
            let assistantSession = null;
            if (options.enableAIAssistant && await this.validatePremiumAccess(userId)) {
                assistantSession = await this.initializeAIAssistantSession(taskId, userId);
            }

            return {
                type: executionResult.type,
                result: executionResult,
                aiSupport: {
                    manual: aiManual,
                    assistantSessionId: assistantSession?.sessionId,
                    realTimeSupport: !!assistantSession
                }
            };

        } catch (error) {
            console.error('Enhanced execution failed:', error);
            throw error;
        }
    }

    /**
     * 4. ENHANCED verify_task: AI 성과 예측 및 품질 검증
     */
    async verifyTaskWithAI(taskId, userId, options = {}) {
        try {
            // Shrimp 기본 검증
            const basicVerification = await this.shrimpService.verifyTask(taskId, userId);
            
            if (options.enableAIVerification && await this.validatePremiumAccess(userId)) {
                
                const taskData = await this.shrimpService.getTaskDetail(taskId, userId);
                
                // AI 성과 분석 및 프로세스 마이닝
                const [performanceAnalysis, processMining] = await Promise.all([
                    this.aiService.predictTaskPerformance(taskData, userId),
                    this.aiService.analyzeProcessExecution(taskData, userId)
                ]);

                const enhancedVerification = {
                    ...basicVerification,
                    aiVerification: {
                        performanceScore: performanceAnalysis.score,
                        qualityMetrics: performanceAnalysis.metrics,
                        processEfficiency: processMining.efficiency,
                        improvementSuggestions: processMining.suggestions,
                        nextStepRecommendations: this.generateNextStepRecommendations(
                            basicVerification, performanceAnalysis, processMining
                        )
                    }
                };

                return {
                    type: 'ai_enhanced',
                    verification: enhancedVerification,
                    qualityScore: this.calculateQualityScore(enhancedVerification)
                };
            }

            return { type: 'basic', verification: basicVerification };

        } catch (error) {
            console.error('Enhanced verification failed:', error);
            const fallbackVerification = await this.shrimpService.verifyTask(taskId, userId);
            return { type: 'fallback', verification: fallbackVerification, error: error.message };
        }
    }

    /**
     * 5. AI 전용 기능: BPR 통합 리포트 생성
     */
    async generateIntegratedBPRReport(projectId, userId, options = {}) {
        try {
            await this.validatePremiumAccess(userId);

            // Shrimp 데이터 수집
            const shrimpData = await this.collectShrimpProjectData(projectId, userId);
            
            // AI 리포트 생성 (스트리밍)
            const reportStream = await this.aiService.generateBPRReport({
                ...shrimpData,
                reportType: options.reportType || 'comprehensive',
                includeAIInsights: true
            }, userId);

            return reportStream;

        } catch (error) {
            console.error('Integrated BPR report generation failed:', error);
            throw error;
        }
    }

    /**
     * 6. AI 실시간 어시스턴트 (모든 Shrimp 기능에서 활용)
     */
    async initializeAIAssistantSession(taskId, userId) {
        try {
            await this.validatePremiumAccess(userId);

            const sessionId = this.generateSessionId();
            const context = await this.buildAssistantContext(taskId, userId);
            
            // WebSocket 세션 초기화
            const session = {
                sessionId: sessionId,
                userId: userId,
                taskId: taskId,
                context: context,
                startTime: new Date(),
                isActive: true
            };

            // 세션 저장
            await this.storeAssistantSession(session);
            
            return session;

        } catch (error) {
            console.error('AI assistant session initialization failed:', error);
            throw error;
        }
    }

    /**
     * 권한 검증
     */
    async validatePremiumAccess(userId) {
        return await this.aiService.validatePremiumAccess(userId);
    }

    /**
     * 헬퍼 메서드들
     */
    mergePlanWithAISuggestions(basicPlan, aiSuggestions, optimization) {
        // AI 제안을 기본 계획에 통합하는 로직
        return {
            ...basicPlan,
            tasks: [
                ...basicPlan.tasks,
                ...aiSuggestions.recommendations.add.map(task => ({
                    ...task,
                    source: 'ai_suggestion',
                    confidence: task.confidence
                }))
            ],
            optimizations: optimization.recommendations,
            aiEnhanced: true
        };
    }

    calculatePlanConfidence(plan, aiSuggestions) {
        // 계획의 신뢰도 계산 로직
        const baseConfidence = 0.7;
        const aiBoost = aiSuggestions.confidence * 0.3;
        return Math.min(baseConfidence + aiBoost, 1.0);
    }

    calculateRiskAssessment(basicAnalysis, aiPrediction) {
        // 리스크 평가 로직
        return {
            level: this.assessRiskLevel(basicAnalysis.risks, aiPrediction.risks),
            factors: [...basicAnalysis.risks, ...aiPrediction.risks],
            mitigation: aiPrediction.riskMitigation
        };
    }

    generateActionRecommendations(analysis, optimization) {
        // 액션 권고사항 생성
        return [
            ...analysis.recommendations,
            ...optimization.recommendations.map(rec => ({
                ...rec,
                source: 'ai_optimization'
            }))
        ];
    }

    async collectShrimpProjectData(projectId, userId) {
        // 프로젝트의 모든 Shrimp 데이터 수집
        const tasks = await this.shrimpService.listTasks(projectId, userId);
        const analyses = await Promise.all(
            tasks.map(task => this.shrimpService.getTaskDetail(task.id, userId))
        );
        
        return {
            project: { id: projectId },
            tasks: tasks,
            analyses: analyses,
            summary: this.generateProjectSummary(tasks, analyses)
        };
    }

    generateSessionId() {
        return `ai_session_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
    }

    async buildAssistantContext(taskId, userId) {
        const task = await this.shrimpService.getTaskDetail(taskId, userId);
        const relatedTasks = await this.shrimpService.getRelatedTasks(taskId, userId);
        
        return {
            currentTask: task,
            relatedTasks: relatedTasks,
            userProfile: await this.getUserProfile(userId),
            projectContext: await this.getProjectContext(task.projectId)
        };
    }
}

module.exports = ShrimpAIIntegrationService;