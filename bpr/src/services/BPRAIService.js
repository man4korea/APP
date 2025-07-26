/**
 * BPR AI 서비스 - 비즈니스 로직과 AI 기능 통합
 */

const AIEngineManager = require('../ai/AIEngineManager');
const PromptTemplateManager = require('../ai/PromptTemplateManager');
const CacheManager = require('../cache/CacheManager');
const UsageTracker = require('../tracking/UsageTracker');

class BPRAIService {
    constructor() {
        this.aiEngine = new AIEngineManager();
        this.promptManager = new PromptTemplateManager();
        this.cacheManager = new CacheManager();
        this.usageTracker = new UsageTracker();
    }

    /**
     * Task별 매뉴얼 자동 생성
     */
    async generateTaskManual(taskData, userId) {
        try {
            // 권한 확인
            await this.validatePremiumAccess(userId);
            
            // 캐시 확인
            const cacheKey = `manual_${taskData.id}_${taskData.lastModified}`;
            const cached = await this.cacheManager.get(cacheKey);
            if (cached) return cached;

            // 프롬프트 준비
            const prompt = await this.promptManager.getTemplate('task_manual', {
                taskName: taskData.name,
                taskDescription: taskData.description,
                taskSteps: taskData.steps,
                taskRequirements: taskData.requirements,
                relatedProcesses: taskData.relatedProcesses
            });

            // AI 요청
            const request = {
                type: 'task_manual_generation',
                userId: userId,
                systemPrompt: await this.promptManager.getSystemPrompt('bpr_expert'),
                prompt: prompt,
                responseFormat: { type: 'json_object' }
            };

            const result = await this.aiEngine.executeRequest(request);
            
            // 결과 파싱 및 구조화
            const manual = this.parseManualResponse(result.content);
            
            // 캐시에 저장
            await this.cacheManager.set(cacheKey, manual, 3600); // 1시간 캐시
            
            // 사용량 추적
            await this.usageTracker.recordUsage(userId, 'task_manual', result.usage);
            
            return manual;

        } catch (error) {
            console.error('Task manual generation failed:', error);
            throw new AIServiceError('매뉴얼 생성 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * Process별 Task 목록 자동 제안
     */
    async suggestProcessTasks(processData, userId) {
        try {
            await this.validatePremiumAccess(userId);

            const cacheKey = `task_suggestions_${processData.id}_${processData.lastModified}`;
            const cached = await this.cacheManager.get(cacheKey);
            if (cached) return cached;

            // 현재 프로세스와 유사한 프로세스들 분석
            const similarProcesses = await this.findSimilarProcesses(processData);
            
            const prompt = await this.promptManager.getTemplate('task_suggestions', {
                processName: processData.name,
                processGoal: processData.goal,
                currentTasks: processData.tasks,
                similarProcesses: similarProcesses,
                industryBestPractices: await this.getIndustryBestPractices(processData.industry)
            });

            const request = {
                type: 'task_suggestion',
                userId: userId,
                systemPrompt: await this.promptManager.getSystemPrompt('bpr_optimizer'),
                prompt: prompt,
                responseFormat: { type: 'json_object' }
            };

            const result = await this.aiEngine.executeRequest(request);
            const suggestions = this.parseTaskSuggestions(result.content);
            
            await this.cacheManager.set(cacheKey, suggestions, 1800); // 30분 캐시
            await this.usageTracker.recordUsage(userId, 'task_suggestion', result.usage);
            
            return suggestions;

        } catch (error) {
            console.error('Task suggestion failed:', error);
            throw new AIServiceError('Task 제안 생성 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 프로세스 최적화 제안
     */
    async optimizeProcess(processData, performanceMetrics, userId) {
        try {
            await this.validatePremiumAccess(userId);

            const prompt = await this.promptManager.getTemplate('process_optimization', {
                processData: processData,
                currentMetrics: performanceMetrics,
                bottlenecks: await this.identifyBottlenecks(processData, performanceMetrics),
                optimizationGoals: processData.optimizationGoals || []
            });

            const request = {
                type: 'process_optimization',
                userId: userId,
                systemPrompt: await this.promptManager.getSystemPrompt('bpr_consultant'),
                prompt: prompt,
                responseFormat: { type: 'json_object' }
            };

            const result = await this.aiEngine.executeRequest(request);
            const optimization = this.parseOptimizationResponse(result.content);
            
            await this.usageTracker.recordUsage(userId, 'process_optimization', result.usage);
            
            return optimization;

        } catch (error) {
            console.error('Process optimization failed:', error);
            throw new AIServiceError('프로세스 최적화 분석 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * BPR 분석 리포트 자동 생성
     */
    async generateBPRReport(projectData, userId) {
        try {
            await this.validatePremiumAccess(userId);

            // 대용량 분석을 위한 스트리밍 처리
            const reportSections = [
                'executive_summary',
                'current_state_analysis', 
                'gap_analysis',
                'optimization_recommendations',
                'implementation_roadmap',
                'risk_assessment',
                'roi_projection'
            ];

            const report = {
                metadata: {
                    projectName: projectData.name,
                    generatedAt: new Date(),
                    userId: userId
                },
                sections: {}
            };

            // 각 섹션을 순차적으로 생성
            for (const section of reportSections) {
                const sectionContent = await this.generateReportSection(section, projectData, userId);
                report.sections[section] = sectionContent;
                
                // 진행 상황 알림 (WebSocket 등으로 실시간 업데이트)
                await this.notifyProgress(userId, section, reportSections.length);
            }

            // 리포트 저장
            const reportPath = await this.saveReport(report, userId);
            
            await this.usageTracker.recordUsage(userId, 'bpr_report', {
                sections: reportSections.length,
                totalTokens: report.metadata.totalTokens
            });

            return {
                report: report,
                downloadUrl: reportPath,
                summary: await this.generateReportSummary(report)
            };

        } catch (error) {
            console.error('BPR report generation failed:', error);
            throw new AIServiceError('BPR 리포트 생성 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 조직 구조 최적화 제안
     */
    async optimizeOrganization(orgData, userId) {
        try {
            await this.validatePremiumAccess(userId);

            const prompt = await this.promptManager.getTemplate('org_optimization', {
                currentStructure: orgData.structure,
                roles: orgData.roles,
                responsibilities: orgData.responsibilities,
                communicationPatterns: orgData.communicationPatterns,
                performanceIssues: orgData.performanceIssues || []
            });

            const request = {
                type: 'organization_optimization',
                userId: userId,
                systemPrompt: await this.promptManager.getSystemPrompt('org_consultant'),
                prompt: prompt,
                responseFormat: { type: 'json_object' }
            };

            const result = await this.aiEngine.executeRequest(request);
            const orgOptimization = this.parseOrgOptimization(result.content);
            
            await this.usageTracker.recordUsage(userId, 'org_optimization', result.usage);
            
            return orgOptimization;

        } catch (error) {
            console.error('Organization optimization failed:', error);
            throw new AIServiceError('조직 최적화 분석 중 오류가 발생했습니다.', error);
        }
    }

    /**
     * 프리미엄 권한 검증
     */
    async validatePremiumAccess(userId) {
        const user = await this.getUserSubscription(userId);
        
        if (!user.isPremium) {
            throw new AccessDeniedError('AI 기능은 프리미엄 회원만 사용할 수 있습니다.');
        }

        // 사용량 제한 확인
        const usage = await this.usageTracker.getMonthlyUsage(userId);
        const limit = parseInt(process.env.PREMIUM_MONTHLY_TOKEN_LIMIT) || 100000;
        
        if (usage.tokens >= limit) {
            throw new QuotaExceededError('월간 AI 사용량 한도를 초과했습니다.');
        }
    }

    /**
     * AI 응답 파싱 헬퍼 메서드들
     */
    parseManualResponse(content) {
        try {
            const parsed = JSON.parse(content);
            return {
                title: parsed.title,
                overview: parsed.overview,
                prerequisites: parsed.prerequisites || [],
                steps: parsed.steps || [],
                tips: parsed.tips || [],
                troubleshooting: parsed.troubleshooting || [],
                relatedResources: parsed.relatedResources || []
            };
        } catch (error) {
            throw new Error('Invalid manual response format');
        }
    }

    parseTaskSuggestions(content) {
        try {
            const parsed = JSON.parse(content);
            return {
                recommendations: {
                    add: parsed.recommendations?.add || [],
                    remove: parsed.recommendations?.remove || [],
                    modify: parsed.recommendations?.modify || []
                },
                reasoning: parsed.reasoning || '',
                impactAnalysis: parsed.impactAnalysis || {},
                priority: parsed.priority || 'medium'
            };
        } catch (error) {
            throw new Error('Invalid task suggestion response format');
        }
    }

    // 추가 헬퍼 메서드들...
}

// 커스텀 에러 클래스들
class AIServiceError extends Error {
    constructor(message, originalError) {
        super(message);
        this.name = 'AIServiceError';
        this.originalError = originalError;
    }
}

class AccessDeniedError extends Error {
    constructor(message) {
        super(message);
        this.name = 'AccessDeniedError';
    }
}

class QuotaExceededError extends Error {
    constructor(message) {
        super(message);
        this.name = 'QuotaExceededError';
    }
}

module.exports = BPRAIService;