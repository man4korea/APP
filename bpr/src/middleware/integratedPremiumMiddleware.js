/**
 * Shrimp + AI 통합 프리미엄 권한 관리 미들웨어
 * 기존 13가지 Shrimp 기능과 8가지 AI 기능의 권한 통합 관리
 */

const UsageTracker = require('../tracking/UsageTracker');
const SubscriptionService = require('../services/SubscriptionService');
const RateLimiter = require('../utils/RateLimiter');

class IntegratedPremiumMiddleware {
    constructor() {
        this.usageTracker = new UsageTracker();
        this.subscriptionService = new SubscriptionService();
        this.rateLimiter = new RateLimiter();
        
        // 기능별 권한 레벨 정의
        this.featurePermissions = {
            // 기존 Shrimp 기능 (기본적으로 모든 사용자 접근 가능)
            'init_project_rules': { minPlan: 'free', dailyLimit: 10 },
            'plan_task': { minPlan: 'free', dailyLimit: 20 },
            'analyze_task': { minPlan: 'free', dailyLimit: 50 },
            'process_thought': { minPlan: 'free', dailyLimit: 30 },
            'reflect_task': { minPlan: 'free', dailyLimit: 40 },
            'split_tasks': { minPlan: 'free', dailyLimit: 15 },
            'list_tasks': { minPlan: 'free', dailyLimit: 100 },
            'query_task': { minPlan: 'free', dailyLimit: 80 },
            'get_task_detail': { minPlan: 'free', dailyLimit: 100 },
            'delete_task': { minPlan: 'free', dailyLimit: 30 },
            'execute_task': { minPlan: 'free', dailyLimit: 50 },
            'verify_task': { minPlan: 'free', dailyLimit: 40 },
            'clear_all_tasks': { minPlan: 'free', dailyLimit: 5 },
            
            // AI 기능 (프리미엄 플랜 이상 필요)
            'ai_generate_manual': { minPlan: 'premium', dailyLimit: 20, tokenCost: 3000 },
            'ai_suggest_tasks': { minPlan: 'premium', dailyLimit: 15, tokenCost: 2500 },
            'ai_optimize_process': { minPlan: 'premium', dailyLimit: 10, tokenCost: 4000 },
            'ai_generate_report': { minPlan: 'premium', dailyLimit: 5, tokenCost: 8000 },
            'ai_optimize_organization': { minPlan: 'enterprise', dailyLimit: 3, tokenCost: 5000 },
            'ai_assistant_chat': { minPlan: 'premium', dailyLimit: 100, tokenCost: 500 },
            'ai_predict_performance': { minPlan: 'premium', dailyLimit: 15, tokenCost: 3500 },
            'ai_process_mining': { minPlan: 'enterprise', dailyLimit: 5, tokenCost: 6000 },
            
            // 통합 기능 (Enhanced Shrimp + AI)
            'enhanced_plan_task': { minPlan: 'premium', dailyLimit: 10, tokenCost: 3000 },
            'enhanced_analyze_task': { minPlan: 'premium', dailyLimit: 8, tokenCost: 4500 },
            'enhanced_execute_task': { minPlan: 'premium', dailyLimit: 12, tokenCost: 2000 },
            'enhanced_verify_task': { minPlan: 'premium', dailyLimit: 10, tokenCost: 3000 },
            'integrated_bpr_report': { minPlan: 'premium', dailyLimit: 2, tokenCost: 10000 }
        };

        // 플랜별 월간 제한
        this.planLimits = {
            'free': {
                monthlyTokens: 0,
                monthlyAIRequests: 0,
                concurrentSessions: 1,
                projectLimit: 3,
                taskLimit: 50
            },
            'premium': {
                monthlyTokens: 100000,
                monthlyAIRequests: 1000,
                concurrentSessions: 3,
                projectLimit: 10,
                taskLimit: 500
            },
            'enterprise': {
                monthlyTokens: -1, // 무제한
                monthlyAIRequests: -1, // 무제한
                concurrentSessions: 10,
                projectLimit: -1, // 무제한
                taskLimit: -1 // 무제한
            }
        };
    }

    /**
     * 기능별 권한 검증 미들웨어
     */
    validateFeatureAccess(featureName) {
        return async (req, res, next) => {
            try {
                const userId = req.user?.id;
                if (!userId) {
                    return res.status(401).json({
                        success: false,
                        error: 'AUTHENTICATION_REQUIRED',
                        message: '인증이 필요합니다.'
                    });
                }

                // 기능 권한 정보 조회
                const featurePermission = this.featurePermissions[featureName];
                if (!featurePermission) {
                    return res.status(400).json({
                        success: false,
                        error: 'UNKNOWN_FEATURE',
                        message: '알 수 없는 기능입니다.'
                    });
                }

                // 사용자 구독 정보 조회
                const userSubscription = await this.subscriptionService.getUserSubscription(userId);
                
                // 플랜 레벨 검증
                const hasRequiredPlan = this.validatePlanLevel(
                    userSubscription.plan, featurePermission.minPlan
                );
                
                if (!hasRequiredPlan) {
                    return res.status(403).json({
                        success: false,
                        error: 'PLAN_UPGRADE_REQUIRED',
                        message: `이 기능은 ${featurePermission.minPlan} 플랜 이상에서 사용할 수 있습니다.`,
                        requiredPlan: featurePermission.minPlan,
                        currentPlan: userSubscription.plan,
                        upgradeUrl: process.env.UPGRADE_URL
                    });
                }

                // Rate Limiting 검사
                const rateLimitCheck = await this.rateLimiter.checkLimit(
                    userId, featureName, featurePermission.dailyLimit
                );
                
                if (!rateLimitCheck.allowed) {
                    return res.status(429).json({
                        success: false,
                        error: 'RATE_LIMIT_EXCEEDED',
                        message: `일일 사용 한도(${featurePermission.dailyLimit}회)를 초과했습니다.`,
                        resetTime: rateLimitCheck.resetTime,
                        remainingRequests: rateLimitCheck.remaining
                    });
                }

                // 토큰 사용량 검사 (AI 기능)
                if (featurePermission.tokenCost && featurePermission.tokenCost > 0) {
                    const tokenCheck = await this.validateTokenUsage(
                        userId, userSubscription.plan, featurePermission.tokenCost
                    );
                    
                    if (!tokenCheck.allowed) {
                        return res.status(429).json({
                            success: false,
                            error: 'TOKEN_QUOTA_EXCEEDED',
                            message: tokenCheck.message,
                            currentUsage: tokenCheck.currentUsage,
                            monthlyLimit: tokenCheck.monthlyLimit,
                            resetDate: tokenCheck.resetDate
                        });
                    }
                }

                // 동시 세션 제한 검사
                const sessionCheck = await this.validateConcurrentSessions(userId, userSubscription.plan);
                if (!sessionCheck.allowed) {
                    return res.status(429).json({
                        success: false,
                        error: 'SESSION_LIMIT_EXCEEDED',
                        message: `동시 세션 한도(${sessionCheck.limit}개)를 초과했습니다.`,
                        currentSessions: sessionCheck.currentSessions
                    });
                }

                // 요청 메타데이터 추가
                req.featurePermission = featurePermission;
                req.userSubscription = userSubscription;
                req.remainingQuota = {
                    dailyRequests: rateLimitCheck.remaining,
                    monthlyTokens: tokenCheck?.remaining || null
                };

                next();

            } catch (error) {
                console.error('Permission validation error:', error);
                res.status(500).json({
                    success: false,
                    error: 'PERMISSION_CHECK_FAILED',
                    message: '권한 검증 중 오류가 발생했습니다.'
                });
            }
        };
    }

    /**
     * AI 기능 사용량 추적 미들웨어
     */
    trackAIUsage() {
        return async (req, res, next) => {
            const originalSend = res.json;
            const userId = req.user?.id;
            const featureName = req.route?.path?.split('/').pop();
            
            res.json = async function(data) {
                try {
                    // AI 기능 성공적 사용 시 사용량 추적
                    if (data.success && req.featurePermission?.tokenCost) {
                        await this.usageTracker.recordUsage(userId, featureName, {
                            tokens: req.featurePermission.tokenCost,
                            feature: featureName,
                            timestamp: new Date(),
                            metadata: {
                                requestId: req.headers['x-request-id'],
                                userAgent: req.headers['user-agent'],
                                responseSize: JSON.stringify(data).length
                            }
                        });

                        // 응답에 사용량 정보 추가
                        data.usage = {
                            tokensUsed: req.featurePermission.tokenCost,
                            remainingQuota: req.remainingQuota
                        };
                    }
                } catch (error) {
                    console.error('Usage tracking error:', error);
                    // 사용량 추적 실패해도 응답은 정상 처리
                }
                
                originalSend.call(this, data);
            }.bind(this);

            next();
        };
    }

    /**
     * 통합 워크플로우 권한 검증
     */
    validateWorkflowAccess(workflowName) {
        return async (req, res, next) => {
            try {
                const userId = req.user?.id;
                const userSubscription = await this.subscriptionService.getUserSubscription(userId);
                
                // 워크플로우별 필요 권한 정의
                const workflowRequirements = {
                    'new_bpr_project': { minPlan: 'premium', features: ['enhanced_plan_task'] },
                    'process_improvement': { minPlan: 'premium', features: ['enhanced_analyze_task', 'ai_optimize_process'] },
                    'task_execution_with_ai': { minPlan: 'premium', features: ['enhanced_execute_task', 'ai_generate_manual'] },
                    'project_completion': { minPlan: 'premium', features: ['enhanced_verify_task', 'integrated_bpr_report'] },
                    'enterprise_process_mining': { minPlan: 'enterprise', features: ['ai_process_mining', 'ai_optimize_organization'] }
                };

                const requirements = workflowRequirements[workflowName];
                if (!requirements) {
                    return res.status(400).json({
                        success: false,
                        error: 'UNKNOWN_WORKFLOW',
                        message: '알 수 없는 워크플로우입니다.'
                    });
                }

                // 플랜 레벨 검증
                if (!this.validatePlanLevel(userSubscription.plan, requirements.minPlan)) {
                    return res.status(403).json({
                        success: false,
                        error: 'WORKFLOW_ACCESS_DENIED',
                        message: `이 워크플로우는 ${requirements.minPlan} 플랜 이상에서 사용할 수 있습니다.`,
                        requiredPlan: requirements.minPlan,
                        currentPlan: userSubscription.plan
                    });
                }

                // 필요한 기능들의 사용량 검증
                const featureChecks = await Promise.all(
                    requirements.features.map(feature => 
                        this.checkFeatureQuota(userId, feature, userSubscription.plan)
                    )
                );

                const blockedFeatures = featureChecks.filter(check => !check.allowed);
                if (blockedFeatures.length > 0) {
                    return res.status(429).json({
                        success: false,
                        error: 'WORKFLOW_QUOTA_EXCEEDED',
                        message: '워크플로우에 필요한 기능의 사용량 한도를 초과했습니다.',
                        blockedFeatures: blockedFeatures.map(f => f.feature)
                    });
                }

                req.workflowRequirements = requirements;
                req.userSubscription = userSubscription;
                next();

            } catch (error) {
                console.error('Workflow validation error:', error);
                res.status(500).json({
                    success: false,
                    error: 'WORKFLOW_VALIDATION_FAILED',
                    message: '워크플로우 권한 검증 중 오류가 발생했습니다.'
                });
            }
        };
    }

    /**
     * 헬퍼 메서드들
     */
    validatePlanLevel(userPlan, requiredPlan) {
        const planHierarchy = { 'free': 0, 'premium': 1, 'enterprise': 2 };
        return planHierarchy[userPlan] >= planHierarchy[requiredPlan];
    }

    async validateTokenUsage(userId, userPlan, requiredTokens) {
        const monthlyUsage = await this.usageTracker.getMonthlyUsage(userId);
        const planLimit = this.planLimits[userPlan];
        
        if (planLimit.monthlyTokens === -1) {
            return { allowed: true, remaining: -1 };
        }
        
        const wouldExceed = (monthlyUsage.tokens + requiredTokens) > planLimit.monthlyTokens;
        
        return {
            allowed: !wouldExceed,
            currentUsage: monthlyUsage.tokens,
            monthlyLimit: planLimit.monthlyTokens,
            remaining: planLimit.monthlyTokens - monthlyUsage.tokens,
            resetDate: monthlyUsage.resetDate,
            message: wouldExceed ? 
                `월간 토큰 한도(${planLimit.monthlyTokens.toLocaleString()})를 초과합니다.` : 
                null
        };
    }

    async validateConcurrentSessions(userId, userPlan) {
        const currentSessions = await this.usageTracker.getActiveSessions(userId);
        const planLimit = this.planLimits[userPlan];
        
        return {
            allowed: currentSessions.count < planLimit.concurrentSessions,
            currentSessions: currentSessions.count,
            limit: planLimit.concurrentSessions
        };
    }

    async checkFeatureQuota(userId, featureName, userPlan) {
        const featurePermission = this.featurePermissions[featureName];
        const dailyUsage = await this.usageTracker.getDailyUsage(userId, featureName);
        
        return {
            feature: featureName,
            allowed: dailyUsage < featurePermission.dailyLimit,
            currentUsage: dailyUsage,
            dailyLimit: featurePermission.dailyLimit
        };
    }

    /**
     * 권한 정보 조회 API 헬퍼
     */
    async getUserPermissionSummary(userId) {
        const userSubscription = await this.subscriptionService.getUserSubscription(userId);
        const monthlyUsage = await this.usageTracker.getMonthlyUsage(userId);
        const planLimit = this.planLimits[userSubscription.plan];
        
        return {
            subscription: {
                plan: userSubscription.plan,
                expiresAt: userSubscription.expiresAt,
                isActive: userSubscription.isActive
            },
            usage: {
                monthly: {
                    tokens: monthlyUsage.tokens,
                    aiRequests: monthlyUsage.aiRequests,
                    resetDate: monthlyUsage.resetDate
                },
                limits: planLimit,
                utilizationRate: {
                    tokens: planLimit.monthlyTokens === -1 ? 0 : (monthlyUsage.tokens / planLimit.monthlyTokens) * 100,
                    requests: planLimit.monthlyAIRequests === -1 ? 0 : (monthlyUsage.aiRequests / planLimit.monthlyAIRequests) * 100
                }
            },
            availableFeatures: this.getAvailableFeatures(userSubscription.plan),
            upgradeRecommendations: this.getUpgradeRecommendations(userSubscription.plan, monthlyUsage)
        };
    }

    getAvailableFeatures(userPlan) {
        return Object.entries(this.featurePermissions)
            .filter(([feature, permission]) => this.validatePlanLevel(userPlan, permission.minPlan))
            .map(([feature, permission]) => ({
                feature: feature,
                dailyLimit: permission.dailyLimit,
                tokenCost: permission.tokenCost || 0
            }));
    }

    getUpgradeRecommendations(currentPlan, usage) {
        const recommendations = [];
        
        if (currentPlan === 'free' && usage.aiRequests > 0) {
            recommendations.push({
                reason: 'AI 기능 사용을 위해 Premium 플랜 업그레이드를 권장합니다.',
                targetPlan: 'premium',
                benefits: ['월 100K 토큰', '모든 AI 기능 이용', '실시간 AI 어시스턴트']
            });
        }
        
        if (currentPlan === 'premium' && usage.tokens > 80000) {
            recommendations.push({
                reason: '높은 토큰 사용량으로 Enterprise 플랜 업그레이드를 권장합니다.',
                targetPlan: 'enterprise',
                benefits: ['무제한 토큰', '프로세스 마이닝', '조직 최적화 AI']
            });
        }
        
        return recommendations;
    }
}

module.exports = IntegratedPremiumMiddleware;