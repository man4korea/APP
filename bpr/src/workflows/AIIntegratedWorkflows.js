/**
 * BPR 프로젝트 AI 통합 워크플로우
 * 실제 사용 시나리오별 Shrimp + AI 연동 워크플로우 정의
 */

const ShrimpAIIntegrationService = require('../services/ShrimpAIIntegrationService');
const WorkflowLogger = require('../utils/WorkflowLogger');

class AIIntegratedWorkflows {
    constructor() {
        this.integrationService = new ShrimpAIIntegrationService();
        this.logger = new WorkflowLogger();
    }

    /**
     * 워크플로우 1: 신규 BPR 프로젝트 시작
     * Shrimp: init_project_rules + plan_task
     * AI: ai_suggest_tasks + ai_optimize_process
     */
    async startNewBPRProject(projectData, userId, options = {}) {
        const workflowId = `new_project_${Date.now()}`;
        
        try {
            this.logger.logWorkflowStart(workflowId, 'new_bpr_project', userId);

            // 1단계: 프로젝트 규칙 초기화 (Shrimp)
            const projectRules = await this.integrationService.shrimpService.initProjectRules(
                projectData.rules, userId
            );
            this.logger.logStep(workflowId, 'project_rules_initialized', projectRules);

            // 2단계: AI 향상된 작업 계획 수립
            const enhancedPlan = await this.integrationService.planTaskWithAI(
                projectData.requirements, 
                userId, 
                { enableAISuggestions: options.useAI !== false }
            );
            this.logger.logStep(workflowId, 'enhanced_planning_complete', enhancedPlan);

            // 3단계: AI 프로세스 최적화 권고 (Premium 사용자만)
            let optimizationRecommendations = null;
            if (options.includeOptimization && await this.integrationService.validatePremiumAccess(userId)) {
                optimizationRecommendations = await this.integrationService.aiService.optimizeProcess(
                    enhancedPlan.plan, null, userId
                );
                this.logger.logStep(workflowId, 'optimization_recommendations', optimizationRecommendations);
            }

            // 4단계: 결과 통합 및 반환
            const result = {
                workflowId: workflowId,
                projectRules: projectRules,
                enhancedPlan: enhancedPlan,
                optimizationRecommendations: optimizationRecommendations,
                nextSteps: this.generateProjectNextSteps(enhancedPlan, optimizationRecommendations),
                estimatedTimeline: this.calculateProjectTimeline(enhancedPlan.plan),
                aiEnhancementLevel: optimizationRecommendations ? 'premium' : 'basic'
            };

            this.logger.logWorkflowComplete(workflowId, result);
            return result;

        } catch (error) {
            this.logger.logWorkflowError(workflowId, error);
            throw new WorkflowError('신규 BPR 프로젝트 시작 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 워크플로우 2: 기존 프로세스 분석 및 개선
     * Shrimp: analyze_task + process_thought
     * AI: ai_optimize_process + ai_process_mining
     */
    async analyzeAndImproveProcess(processId, userId, options = {}) {
        const workflowId = `process_improvement_${Date.now()}`;
        
        try {
            this.logger.logWorkflowStart(workflowId, 'process_improvement', userId);

            // 1단계: Shrimp 기본 분석
            const basicAnalysis = await this.integrationService.shrimpService.analyzeTask(processId, userId);
            this.logger.logStep(workflowId, 'basic_analysis_complete', basicAnalysis);

            // 2단계: AI 향상된 분석 (Premium)
            let enhancedAnalysis = null;
            if (await this.integrationService.validatePremiumAccess(userId)) {
                enhancedAnalysis = await this.integrationService.analyzeTaskWithAI(
                    processId, userId, { enableAIAnalysis: true }
                );
                this.logger.logStep(workflowId, 'ai_enhanced_analysis', enhancedAnalysis);
            }

            // 3단계: 심층 사고 프로세스 (Shrimp)
            const thoughtProcess = await this.integrationService.shrimpService.processThought(
                enhancedAnalysis?.analysis || basicAnalysis, userId
            );
            this.logger.logStep(workflowId, 'thought_process_complete', thoughtProcess);

            // 4단계: AI 프로세스 마이닝 (Enterprise)
            let processMining = null;
            if (options.includeProcessMining && await this.validateEnterpriseAccess(userId)) {
                const processData = await this.integrationService.shrimpService.getTaskDetail(processId, userId);
                processMining = await this.integrationService.aiService.analyzeProcessMining(
                    processData, userId
                );
                this.logger.logStep(workflowId, 'process_mining_complete', processMining);
            }

            // 5단계: 통합 개선 계획 생성
            const improvementPlan = this.generateImprovementPlan(
                basicAnalysis, enhancedAnalysis, thoughtProcess, processMining
            );

            const result = {
                workflowId: workflowId,
                currentState: {
                    basicAnalysis: basicAnalysis,
                    enhancedAnalysis: enhancedAnalysis,
                    thoughtProcess: thoughtProcess,
                    processMining: processMining
                },
                improvementPlan: improvementPlan,
                priority: this.calculateImprovementPriority(improvementPlan),
                estimatedROI: this.estimateImprovementROI(improvementPlan),
                implementationComplexity: this.assessImplementationComplexity(improvementPlan)
            };

            this.logger.logWorkflowComplete(workflowId, result);
            return result;

        } catch (error) {
            this.logger.logWorkflowError(workflowId, error);
            throw new WorkflowError('프로세스 분석 및 개선 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 워크플로우 3: Task 실행 및 실시간 AI 지원
     * Shrimp: execute_task
     * AI: ai_generate_manual + ai_assistant_chat
     */
    async executeTaskWithRealTimeSupport(taskId, userId, options = {}) {
        const workflowId = `task_execution_${Date.now()}`;
        
        try {
            this.logger.logWorkflowStart(workflowId, 'task_execution_with_ai', userId);

            // 1단계: 실행 전 AI 매뉴얼 생성 (선택적)
            let aiManual = null;
            if (options.generateManual && await this.integrationService.validatePremiumAccess(userId)) {
                const taskData = await this.integrationService.shrimpService.getTaskDetail(taskId, userId);
                aiManual = await this.integrationService.aiService.generateTaskManual(taskData, userId);
                this.logger.logStep(workflowId, 'ai_manual_generated', { manualId: aiManual.id });
            }

            // 2단계: AI 어시스턴트 세션 초기화
            let assistantSession = null;
            if (options.enableRealTimeSupport && await this.integrationService.validatePremiumAccess(userId)) {
                assistantSession = await this.integrationService.initializeAIAssistantSession(taskId, userId);
                this.logger.logStep(workflowId, 'ai_assistant_initialized', { 
                    sessionId: assistantSession.sessionId 
                });
            }

            // 3단계: Task 실행 시작
            const executionResult = await this.integrationService.executeTaskWithAI(
                taskId, userId, {
                    generateManual: !!aiManual,
                    enableAIAssistant: !!assistantSession
                }
            );
            this.logger.logStep(workflowId, 'task_execution_started', executionResult);

            // 4단계: 실행 모니터링 및 실시간 지원
            const monitoringResult = await this.monitorTaskExecution(
                taskId, executionResult, assistantSession, workflowId
            );

            const result = {
                workflowId: workflowId,
                executionResult: executionResult,
                aiSupport: {
                    manual: aiManual,
                    assistantSession: assistantSession,
                    realTimeInteractions: monitoringResult.interactions
                },
                executionMetrics: monitoringResult.metrics,
                completionStatus: monitoringResult.status
            };

            this.logger.logWorkflowComplete(workflowId, result);
            return result;

        } catch (error) {
            this.logger.logWorkflowError(workflowId, error);
            throw new WorkflowError('Task 실행 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 워크플로우 4: 프로젝트 완료 및 종합 분석
     * Shrimp: verify_task + reflect_task
     * AI: ai_generate_report + ai_predict_performance
     */
    async completeProjectWithComprehensiveAnalysis(projectId, userId, options = {}) {
        const workflowId = `project_completion_${Date.now()}`;
        
        try {
            this.logger.logWorkflowStart(workflowId, 'project_completion', userId);

            // 1단계: 모든 Task 검증
            const projectTasks = await this.integrationService.shrimpService.listTasks(projectId, userId);
            const verificationResults = await Promise.all(
                projectTasks.map(task => 
                    this.integrationService.verifyTaskWithAI(
                        task.id, userId, { enableAIVerification: true }
                    )
                )
            );
            this.logger.logStep(workflowId, 'all_tasks_verified', { 
                totalTasks: projectTasks.length,
                verifiedTasks: verificationResults.filter(r => r.verification.status === 'completed').length
            });

            // 2단계: 프로젝트 전체 회고
            const projectReflection = await this.integrationService.shrimpService.reflectTask(
                projectId, userId, { includeAllTasks: true }
            );
            this.logger.logStep(workflowId, 'project_reflection_complete', projectReflection);

            // 3단계: AI 종합 BPR 리포트 생성 (Premium)
            let comprehensiveReport = null;
            if (await this.integrationService.validatePremiumAccess(userId)) {
                comprehensiveReport = await this.integrationService.generateIntegratedBPRReport(
                    projectId, userId, {
                        reportType: options.reportType || 'comprehensive',
                        includePerformancePrediction: true,
                        includeROIAnalysis: true
                    }
                );
                this.logger.logStep(workflowId, 'comprehensive_report_generated', { 
                    reportId: comprehensiveReport.metadata.reportId 
                });
            }

            // 4단계: 미래 성과 예측 (AI)
            let performancePrediction = null;
            if (options.includePrediction && await this.integrationService.validatePremiumAccess(userId)) {
                const projectData = await this.integrationService.collectShrimpProjectData(projectId, userId);
                performancePrediction = await this.integrationService.aiService.predictProjectPerformance(
                    projectData, userId
                );
                this.logger.logStep(workflowId, 'performance_prediction_complete', performancePrediction);
            }

            // 5단계: 최종 결과 통합
            const result = {
                workflowId: workflowId,
                projectCompletion: {
                    totalTasks: projectTasks.length,
                    completedTasks: verificationResults.filter(r => r.verification.status === 'completed').length,
                    completionRate: this.calculateCompletionRate(verificationResults)
                },
                reflection: projectReflection,
                comprehensiveReport: comprehensiveReport,
                performancePrediction: performancePrediction,
                finalRecommendations: this.generateFinalRecommendations(
                    projectReflection, comprehensiveReport, performancePrediction
                ),
                projectSuccess: this.assessProjectSuccess(verificationResults, performancePrediction)
            };

            this.logger.logWorkflowComplete(workflowId, result);
            return result;

        } catch (error) {
            this.logger.logWorkflowError(workflowId, error);
            throw new WorkflowError('프로젝트 완료 분석 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 워크플로우 5: 실시간 AI 어시스턴트 상호작용
     */
    async handleAIAssistantInteraction(sessionId, userMessage, userId) {
        try {
            const session = await this.getAssistantSession(sessionId);
            if (!session || !session.isActive) {
                throw new Error('유효하지 않은 AI 어시스턴트 세션입니다.');
            }

            // 현재 Task 컨텍스트 업데이트
            const currentContext = await this.updateAssistantContext(session);
            
            // AI 응답 생성
            const aiResponse = await this.integrationService.aiService.generateAssistantResponse(
                userMessage, currentContext, userId
            );

            // 상호작용 기록
            await this.recordAssistantInteraction(sessionId, userMessage, aiResponse);

            return {
                sessionId: sessionId,
                response: aiResponse,
                contextUpdated: true,
                suggestions: this.generateContextualSuggestions(currentContext, aiResponse)
            };

        } catch (error) {
            console.error('AI assistant interaction failed:', error);
            throw error;
        }
    }

    /**
     * 헬퍼 메서드들
     */
    generateProjectNextSteps(enhancedPlan, optimizationRecommendations) {
        const nextSteps = [];
        
        // 기본 계획의 다음 단계
        if (enhancedPlan.plan.tasks && enhancedPlan.plan.tasks.length > 0) {
            nextSteps.push({
                type: 'execute_tasks',
                description: '계획된 작업 실행 시작',
                priority: 'high',
                estimatedDuration: this.estimateTasksDuration(enhancedPlan.plan.tasks.slice(0, 3))
            });
        }

        // AI 최적화 권고사항 적용
        if (optimizationRecommendations && optimizationRecommendations.recommendations.length > 0) {
            nextSteps.push({
                type: 'apply_optimizations',
                description: 'AI 최적화 권고사항 검토 및 적용',
                priority: 'medium',
                optimizations: optimizationRecommendations.recommendations.slice(0, 5)
            });
        }

        return nextSteps;
    }

    calculateProjectTimeline(plan) {
        if (!plan.tasks) return null;
        
        const totalTasks = plan.tasks.length;
        const estimatedHoursPerTask = 4; // 기본 추정치
        const parallelismFactor = 0.7; // 병렬 처리 효율성
        
        return {
            totalTasks: totalTasks,
            estimatedHours: totalTasks * estimatedHoursPerTask * parallelismFactor,
            estimatedDays: Math.ceil((totalTasks * estimatedHoursPerTask * parallelismFactor) / 8),
            milestones: this.generateProjectMilestones(plan.tasks)
        };
    }

    generateImprovementPlan(basicAnalysis, enhancedAnalysis, thoughtProcess, processMining) {
        const improvements = [];
        
        // 기본 분석에서 도출된 개선사항
        if (basicAnalysis.recommendations) {
            improvements.push(...basicAnalysis.recommendations.map(rec => ({
                ...rec,
                source: 'basic_analysis',
                confidence: 0.7
            })));
        }

        // AI 향상 분석에서 도출된 개선사항
        if (enhancedAnalysis && enhancedAnalysis.analysis.recommendedActions) {
            improvements.push(...enhancedAnalysis.analysis.recommendedActions.map(action => ({
                ...action,
                source: 'ai_analysis',
                confidence: enhancedAnalysis.confidence
            })));
        }

        // 프로세스 마이닝에서 도출된 개선사항
        if (processMining && processMining.suggestions) {
            improvements.push(...processMining.suggestions.map(suggestion => ({
                ...suggestion,
                source: 'process_mining',
                confidence: 0.9
            })));
        }

        return {
            improvements: improvements,
            prioritizedActions: this.prioritizeImprovements(improvements),
            implementationPhases: this.createImplementationPhases(improvements)
        };
    }

    async monitorTaskExecution(taskId, executionResult, assistantSession, workflowId) {
        const monitoring = {
            interactions: [],
            metrics: {
                startTime: new Date(),
                aiAssistanceRequests: 0,
                userSatisfactionScore: null
            },
            status: 'in_progress'
        };

        // 실시간 모니터링 로직 (WebSocket 등을 통한 실제 구현 필요)
        // 여기서는 기본 구조만 제시

        return monitoring;
    }

    async validateEnterpriseAccess(userId) {
        // Enterprise 플랜 사용자인지 확인
        const user = await this.getUserSubscription(userId);
        return user.plan === 'enterprise';
    }
}

class WorkflowError extends Error {
    constructor(message, originalError) {
        super(message);
        this.name = 'WorkflowError';
        this.originalError = originalError;
    }
}

module.exports = AIIntegratedWorkflows;