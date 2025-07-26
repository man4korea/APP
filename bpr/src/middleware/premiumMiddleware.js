/**
 * 프리미엄 회원 권한 검증 미들웨어
 */

const UserService = require('../services/UserService');
const SubscriptionService = require('../services/SubscriptionService');
const UsageTracker = require('../tracking/UsageTracker');

class PremiumMiddleware {
    constructor() {
        this.userService = new UserService();
        this.subscriptionService = new SubscriptionService();
        this.usageTracker = new UsageTracker();
    }

    /**
     * 기본 프리미엄 권한 확인
     */
    async checkPremiumAccess(req, res, next) {
        try {
            const userId = req.user.id;
            
            // 사용자 구독 정보 조회
            const subscription = await this.subscriptionService.getUserSubscription(userId);
            
            if (!subscription) {
                return res.status(403).json({
                    success: false,
                    error: 'SUBSCRIPTION_REQUIRED',
                    message: '구독이 필요한 서비스입니다.',
                    upgradeUrl: '/premium/upgrade'
                });
            }

            // 구독 상태 확인
            if (!this.isValidSubscription(subscription)) {
                return res.status(403).json({
                    success: false,
                    error: 'SUBSCRIPTION_EXPIRED',
                    message: '구독이 만료되었거나 비활성 상태입니다.',
                    renewUrl: '/premium/renew'
                });
            }

            // 프리미엄 플랜 확인
            if (!this.isPremiumPlan(subscription.planType)) {
                return res.status(403).json({
                    success: false,
                    error: 'PREMIUM_REQUIRED',
                    message: 'AI 기능은 프리미엄 플랜에서만 이용 가능합니다.',
                    upgradeUrl: '/premium/upgrade'
                });
            }

            // 요청 객체에 구독 정보 추가
            req.subscription = subscription;
            next();

        } catch (error) {
            console.error('Premium access check failed:', error);
            res.status(500).json({
                success: false,
                error: 'SUBSCRIPTION_CHECK_FAILED',
                message: '구독 정보 확인 중 오류가 발생했습니다.'
            });
        }
    }

    /**
     * AI 기능별 세부 권한 확인
     */
    async checkAIFeatureAccess(featureType) {
        return async (req, res, next) => {
            try {
                const subscription = req.subscription;
                const userId = req.user.id;

                // 기능별 권한 매트릭스 확인
                const hasAccess = await this.checkFeaturePermission(subscription, featureType);
                if (!hasAccess) {
                    return res.status(403).json({
                        success: false,
                        error: 'FEATURE_NOT_AVAILABLE',
                        message: `${featureType} 기능은 현재 플랜에서 이용할 수 없습니다.`,
                        requiredPlan: this.getRequiredPlan(featureType)
                    });
                }

                // 사용량 제한 확인
                const usageCheck = await this.checkUsageLimits(userId, featureType);
                if (!usageCheck.allowed) {
                    return res.status(429).json({
                        success: false,
                        error: 'USAGE_LIMIT_EXCEEDED',
                        message: usageCheck.message,
                        resetDate: usageCheck.resetDate,
                        currentUsage: usageCheck.currentUsage,
                        limit: usageCheck.limit
                    });
                }

                req.usageInfo = usageCheck;
                next();

            } catch (error) {
                console.error('AI feature access check failed:', error);
                res.status(500).json({
                    success: false,
                    error: 'FEATURE_CHECK_FAILED',
                    message: '기능 권한 확인 중 오류가 발생했습니다.'
                });
            }
        };
    }

    /**
     * 사용량 기반 제한 확인
     */
    async checkUsageLimits(userId, featureType) {
        const limits = this.getUsageLimits(featureType);
        const currentUsage = await this.usageTracker.getCurrentUsage(userId);

        // 월간 토큰 사용량 확인
        if (currentUsage.monthlyTokens >= limits.monthlyTokens) {
            return {
                allowed: false,
                message: '월간 토큰 사용량 한도를 초과했습니다.',
                resetDate: this.getMonthlyResetDate(),
                currentUsage: currentUsage.monthlyTokens,
                limit: limits.monthlyTokens
            };
        }

        // 일일 요청 수 확인
        if (currentUsage.dailyRequests >= limits.dailyRequests) {
            return {
                allowed: false,
                message: '일일 요청 수 한도를 초과했습니다.',
                resetDate: this.getDailyResetDate(),
                currentUsage: currentUsage.dailyRequests,
                limit: limits.dailyRequests
            };
        }

        // 기능별 특수 제한 확인
        const featureLimit = await this.checkFeatureSpecificLimits(userId, featureType, currentUsage);
        if (!featureLimit.allowed) {
            return featureLimit;
        }

        return {
            allowed: true,
            remainingTokens: limits.monthlyTokens - currentUsage.monthlyTokens,
            remainingRequests: limits.dailyRequests - currentUsage.dailyRequests
        };
    }

    /**
     * 구독 유효성 검증
     */
    isValidSubscription(subscription) {
        if (!subscription) return false;
        
        const now = new Date();
        const expiryDate = new Date(subscription.expiryDate);
        
        return subscription.status === 'active' && 
               expiryDate > now &&
               !subscription.isCancelled;
    }

    /**
     * 프리미엄 플랜 확인
     */
    isPremiumPlan(planType) {
        const premiumPlans = ['premium', 'premium_plus', 'enterprise'];
        return premiumPlans.includes(planType.toLowerCase());
    }

    /**
     * 기능별 권한 확인
     */
    async checkFeaturePermission(subscription, featureType) {
        const featureMatrix = {
            'task_manual': ['premium', 'premium_plus', 'enterprise'],
            'task_suggestion': ['premium', 'premium_plus', 'enterprise'],
            'process_optimization': ['premium_plus', 'enterprise'],
            'bpr_report': ['premium_plus', 'enterprise'],
            'org_optimization': ['enterprise'],
            'bulk_analysis': ['enterprise'],
            'api_access': ['premium_plus', 'enterprise']
        };

        const allowedPlans = featureMatrix[featureType] || [];
        return allowedPlans.includes(subscription.planType.toLowerCase());
    }

    /**
     * 사용량 제한 설정
     */
    getUsageLimits(featureType) {
        const baseLimits = {
            monthlyTokens: parseInt(process.env.PREMIUM_MONTHLY_TOKEN_LIMIT) || 100000,
            dailyRequests: parseInt(process.env.PREMIUM_DAILY_REQUEST_LIMIT) || 500
        };

        // 기능별 추가 제한
        const featureLimits = {
            'bpr_report': {
                ...baseLimits,
                monthlyReports: 10,
                maxReportSize: 50 // MB
            },
            'bulk_analysis': {
                ...baseLimits,
                batchSize: 100,
                monthlyBatches: 20
            }
        };

        return featureLimits[featureType] || baseLimits;
    }

    /**
     * 기능별 특수 제한 확인
     */
    async checkFeatureSpecificLimits(userId, featureType, currentUsage) {
        switch (featureType) {
            case 'bpr_report':
                const monthlyReports = await this.usageTracker.getMonthlyReportCount(userId);
                if (monthlyReports >= 10) {
                    return {
                        allowed: false,
                        message: '월간 리포트 생성 한도를 초과했습니다.',
                        resetDate: this.getMonthlyResetDate(),
                        currentUsage: monthlyReports,
                        limit: 10
                    };
                }
                break;

            case 'bulk_analysis':
                const monthlyBatches = await this.usageTracker.getMonthlyBatchCount(userId);
                if (monthlyBatches >= 20) {
                    return {
                        allowed: false,
                        message: '월간 대량 분석 한도를 초과했습니다.',
                        resetDate: this.getMonthlyResetDate(),
                        currentUsage: monthlyBatches,
                        limit: 20
                    };
                }
                break;
        }

        return { allowed: true };
    }

    /**
     * 필요한 플랜 정보 반환
     */
    getRequiredPlan(featureType) {
        const planRequirements = {
            'task_manual': 'Premium',
            'task_suggestion': 'Premium',
            'process_optimization': 'Premium Plus',
            'bpr_report': 'Premium Plus',
            'org_optimization': 'Enterprise',
            'bulk_analysis': 'Enterprise',
            'api_access': 'Premium Plus'
        };

        return planRequirements[featureType] || 'Premium';
    }

    /**
     * 리셋 날짜 계산
     */
    getMonthlyResetDate() {
        const now = new Date();
        const nextMonth = new Date(now.getFullYear(), now.getMonth() + 1, 1);
        return nextMonth;
    }

    getDailyResetDate() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        tomorrow.setHours(0, 0, 0, 0);
        return tomorrow;
    }

    /**
     * 사용량 추적 헬퍼
     */
    async recordFeatureUsage(userId, featureType, usage) {
        await this.usageTracker.recordUsage(userId, featureType, usage);
        
        // 사용량 경고 확인
        const currentUsage = await this.usageTracker.getCurrentUsage(userId);
        const limits = this.getUsageLimits(featureType);
        
        if (currentUsage.monthlyTokens > limits.monthlyTokens * 0.8) {
            await this.sendUsageWarning(userId, 'tokens', currentUsage.monthlyTokens, limits.monthlyTokens);
        }
        
        if (currentUsage.dailyRequests > limits.dailyRequests * 0.8) {
            await this.sendUsageWarning(userId, 'requests', currentUsage.dailyRequests, limits.dailyRequests);
        }
    }

    /**
     * 사용량 경고 발송
     */
    async sendUsageWarning(userId, type, current, limit) {
        const percentage = Math.round((current / limit) * 100);
        
        // 알림 발송 로직 (이메일, 웹소켓 등)
        console.log(`Usage warning for user ${userId}: ${type} usage at ${percentage}%`);
        
        // 실제 구현에서는 알림 서비스 호출
        // await this.notificationService.sendUsageWarning(userId, type, percentage);
    }
}

// 미들웨어 팩토리 함수들
const premiumMiddleware = new PremiumMiddleware();

module.exports = {
    // 기본 프리미엄 권한 확인
    checkPremium: (req, res, next) => premiumMiddleware.checkPremiumAccess(req, res, next),
    
    // AI 기능별 권한 확인
    checkAIFeature: (featureType) => premiumMiddleware.checkAIFeatureAccess(featureType),
    
    // 사용량 추적
    trackUsage: (req, res, next) => {
        res.on('finish', async () => {
            if (res.statusCode === 200 && req.user && req.aiUsage) {
                await premiumMiddleware.recordFeatureUsage(
                    req.user.id, 
                    req.aiFeatureType || 'general', 
                    req.aiUsage
                );
            }
        });
        next();
    },
    
    // 프리미엄 미들웨어 인스턴스
    instance: premiumMiddleware
};