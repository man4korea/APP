/**
 * AI 사용량 추적 및 제한 관리 시스템
 */

const Redis = require('redis');
const mysql = require('mysql2/promise');

class UsageTracker {
    constructor() {
        this.redis = Redis.createClient({
            host: process.env.REDIS_HOST || 'localhost',
            port: process.env.REDIS_PORT || 6379,
            password: process.env.REDIS_PASSWORD || undefined
        });
        
        this.dbPool = mysql.createPool({
            host: process.env.MYSQL_HOST,
            port: process.env.MYSQL_PORT,
            user: process.env.MYSQL_USER,
            password: process.env.MYSQL_PASSWORD,
            database: process.env.MYSQL_DATABASE,
            waitForConnections: true,
            connectionLimit: 10,
            queueLimit: 0
        });

        this.redis.on('error', (err) => {
            console.error('Redis connection error:', err);
        });
    }

    /**
     * AI 사용량 기록
     */
    async recordUsage(userId, featureType, usageData) {
        const timestamp = new Date();
        const usageRecord = {
            userId: userId,
            featureType: featureType,
            timestamp: timestamp,
            tokens: usageData.totalTokens || 0,
            promptTokens: usageData.promptTokens || 0,
            completionTokens: usageData.completionTokens || 0,
            model: usageData.model || 'unknown',
            requestDuration: usageData.duration || 0,
            success: usageData.success !== false,
            errorMessage: usageData.error || null
        };

        try {
            // 데이터베이스에 상세 기록 저장
            await this.saveUsageToDatabase(usageRecord);
            
            // Redis에 실시간 카운터 업데이트
            await this.updateRedisCounters(userId, featureType, usageRecord);
            
            // 사용량 제한 확인
            await this.checkAndEnforceUsageLimits(userId, usageRecord);
            
            console.log(`Usage recorded for user ${userId}: ${featureType} - ${usageRecord.tokens} tokens`);

        } catch (error) {
            console.error('Usage recording failed:', error);
            // 실패해도 메인 기능에 영향주지 않도록 에러만 로깅
        }
    }

    /**
     * 데이터베이스에 사용량 저장
     */
    async saveUsageToDatabase(usageRecord) {
        const query = `
            INSERT INTO ai_usage_logs (
                user_id, feature_type, timestamp, tokens, prompt_tokens, 
                completion_tokens, model, request_duration, success, error_message
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        `;

        const values = [
            usageRecord.userId,
            usageRecord.featureType,
            usageRecord.timestamp,
            usageRecord.tokens,
            usageRecord.promptTokens,
            usageRecord.completionTokens,
            usageRecord.model,
            usageRecord.requestDuration,
            usageRecord.success,
            usageRecord.errorMessage
        ];

        await this.dbPool.execute(query, values);
    }

    /**
     * Redis 실시간 카운터 업데이트
     */
    async updateRedisCounters(userId, featureType, usageRecord) {
        const pipe = this.redis.pipeline();
        const now = new Date();
        
        // 일일 카운터
        const dailyKey = `usage:daily:${userId}:${this.getDateKey(now)}`;
        pipe.hincrby(dailyKey, 'requests', 1);
        pipe.hincrby(dailyKey, 'tokens', usageRecord.tokens);
        pipe.hincrby(dailyKey, featureType, 1);
        pipe.expire(dailyKey, 86400 * 2); // 2일 보관

        // 월간 카운터
        const monthlyKey = `usage:monthly:${userId}:${this.getMonthKey(now)}`;
        pipe.hincrby(monthlyKey, 'requests', 1);
        pipe.hincrby(monthlyKey, 'tokens', usageRecord.tokens);
        pipe.hincrby(monthlyKey, featureType, 1);
        pipe.expire(monthlyKey, 86400 * 35); // 35일 보관

        // 시간별 카운터 (실시간 모니터링용)
        const hourlyKey = `usage:hourly:${userId}:${this.getHourKey(now)}`;
        pipe.hincrby(hourlyKey, 'requests', 1);
        pipe.hincrby(hourlyKey, 'tokens', usageRecord.tokens);
        pipe.expire(hourlyKey, 3600 * 25); // 25시간 보관

        // 기능별 특수 카운터
        if (featureType === 'bpr_report') {
            const reportKey = `usage:reports:${userId}:${this.getMonthKey(now)}`;
            pipe.incr(reportKey);
            pipe.expire(reportKey, 86400 * 35);
        }

        if (featureType === 'bulk_analysis') {
            const batchKey = `usage:batches:${userId}:${this.getMonthKey(now)}`;
            pipe.incr(batchKey);
            pipe.expire(batchKey, 86400 * 35);
        }

        await pipe.exec();
    }

    /**
     * 현재 사용량 조회
     */
    async getCurrentUsage(userId) {
        const now = new Date();
        const dailyKey = `usage:daily:${userId}:${this.getDateKey(now)}`;
        const monthlyKey = `usage:monthly:${userId}:${this.getMonthKey(now)}`;

        const [dailyData, monthlyData] = await Promise.all([
            this.redis.hgetall(dailyKey),
            this.redis.hgetall(monthlyKey)
        ]);

        return {
            daily: {
                requests: parseInt(dailyData?.requests || 0),
                tokens: parseInt(dailyData?.tokens || 0),
                taskManual: parseInt(dailyData?.task_manual || 0),
                taskSuggestion: parseInt(dailyData?.task_suggestion || 0),
                processOptimization: parseInt(dailyData?.process_optimization || 0),
                bprReport: parseInt(dailyData?.bpr_report || 0),
                orgOptimization: parseInt(dailyData?.org_optimization || 0)
            },
            monthly: {
                requests: parseInt(monthlyData?.requests || 0),
                tokens: parseInt(monthlyData?.tokens || 0),
                taskManual: parseInt(monthlyData?.task_manual || 0),
                taskSuggestion: parseInt(monthlyData?.task_suggestion || 0),
                processOptimization: parseInt(monthlyData?.process_optimization || 0),
                bprReport: parseInt(monthlyData?.bpr_report || 0),
                orgOptimization: parseInt(monthlyData?.org_optimization || 0)
            },
            // 편의 속성
            dailyRequests: parseInt(dailyData?.requests || 0),
            monthlyTokens: parseInt(monthlyData?.tokens || 0)
        };
    }

    /**
     * 월간 리포트 수 조회
     */
    async getMonthlyReportCount(userId) {
        const now = new Date();
        const reportKey = `usage:reports:${userId}:${this.getMonthKey(now)}`;
        const count = await this.redis.get(reportKey);
        return parseInt(count || 0);
    }

    /**
     * 월간 배치 분석 수 조회
     */
    async getMonthlyBatchCount(userId) {
        const now = new Date();
        const batchKey = `usage:batches:${userId}:${this.getMonthKey(now)}`;
        const count = await this.redis.get(batchKey);
        return parseInt(count || 0);
    }

    /**
     * 사용량 제한 확인 및 적용
     */
    async checkAndEnforceUsageLimits(userId, usageRecord) {
        const limits = this.getUsageLimits();
        const currentUsage = await this.getCurrentUsage(userId);

        // 제한 초과 확인
        const violations = [];

        if (currentUsage.monthlyTokens > limits.monthlyTokens) {
            violations.push({
                type: 'monthly_tokens',
                current: currentUsage.monthlyTokens,
                limit: limits.monthlyTokens
            });
        }

        if (currentUsage.dailyRequests > limits.dailyRequests) {
            violations.push({
                type: 'daily_requests',
                current: currentUsage.dailyRequests,
                limit: limits.dailyRequests
            });
        }

        // 제한 위반 기록
        if (violations.length > 0) {
            await this.recordLimitViolation(userId, violations, usageRecord);
        }

        // 경고 임계값 확인 (80%, 90%, 95%)
        await this.checkWarningThresholds(userId, currentUsage, limits);
    }

    /**
     * 제한 위반 기록
     */
    async recordLimitViolation(userId, violations, usageRecord) {
        const violationRecord = {
            userId: userId,
            timestamp: new Date(),
            violations: violations,
            context: usageRecord
        };

        // 데이터베이스에 위반 기록
        const query = `
            INSERT INTO usage_violations (
                user_id, timestamp, violation_type, current_usage, 
                limit_value, feature_type, violation_data
            ) VALUES ?
        `;

        const values = violations.map(v => [
            userId,
            violationRecord.timestamp,
            v.type,
            v.current,
            v.limit,
            usageRecord.featureType,
            JSON.stringify(violationRecord)
        ]);

        await this.dbPool.execute(query, [values]);

        // Redis에 위반 플래그 설정
        const violationKey = `violation:${userId}:${this.getDateKey(new Date())}`;
        await this.redis.setex(violationKey, 86400, JSON.stringify(violations));

        console.log(`Usage limit violation recorded for user ${userId}:`, violations);
    }

    /**
     * 경고 임계값 확인
     */
    async checkWarningThresholds(userId, currentUsage, limits) {
        const thresholds = [80, 90, 95];
        
        for (const threshold of thresholds) {
            // 토큰 사용량 경고
            const tokenPercentage = (currentUsage.monthlyTokens / limits.monthlyTokens) * 100;
            if (tokenPercentage >= threshold && tokenPercentage < threshold + 10) {
                await this.sendUsageWarning(userId, 'tokens', threshold, {
                    current: currentUsage.monthlyTokens,
                    limit: limits.monthlyTokens,
                    percentage: Math.round(tokenPercentage)
                });
            }

            // 요청 수 경고
            const requestPercentage = (currentUsage.dailyRequests / limits.dailyRequests) * 100;
            if (requestPercentage >= threshold && requestPercentage < threshold + 10) {
                await this.sendUsageWarning(userId, 'requests', threshold, {
                    current: currentUsage.dailyRequests,
                    limit: limits.dailyRequests,
                    percentage: Math.round(requestPercentage)
                });
            }
        }
    }

    /**
     * 사용량 경고 발송
     */
    async sendUsageWarning(userId, type, threshold, data) {
        const warningKey = `warning:${userId}:${type}:${threshold}:${this.getDateKey(new Date())}`;
        
        // 중복 경고 방지
        const exists = await this.redis.exists(warningKey);
        if (exists) return;

        await this.redis.setex(warningKey, 86400, '1');

        // 실제 알림 발송 (이메일, 웹소켓, 푸시 등)
        const warning = {
            userId: userId,
            type: type,
            threshold: threshold,
            data: data,
            timestamp: new Date()
        };

        console.log(`Usage warning sent to user ${userId}:`, warning);
        
        // 실제 구현에서는 알림 서비스 호출
        // await this.notificationService.sendUsageWarning(warning);
    }

    /**
     * 사용량 통계 조회
     */
    async getUsageStatistics(userId, period = 'month') {
        let query, params;
        
        switch (period) {
            case 'day':
                query = `
                    SELECT 
                        DATE(timestamp) as date,
                        feature_type,
                        COUNT(*) as requests,
                        SUM(tokens) as total_tokens,
                        AVG(request_duration) as avg_duration,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests
                    FROM ai_usage_logs 
                    WHERE user_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY DATE(timestamp), feature_type
                    ORDER BY date DESC, feature_type
                `;
                params = [userId];
                break;
                
            case 'month':
                query = `
                    SELECT 
                        DATE_FORMAT(timestamp, '%Y-%m') as month,
                        feature_type,
                        COUNT(*) as requests,
                        SUM(tokens) as total_tokens,
                        AVG(request_duration) as avg_duration,
                        SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful_requests
                    FROM ai_usage_logs 
                    WHERE user_id = ? AND timestamp >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(timestamp, '%Y-%m'), feature_type
                    ORDER BY month DESC, feature_type
                `;
                params = [userId];
                break;
                
            default:
                throw new Error('Invalid period specified');
        }

        const [rows] = await this.dbPool.execute(query, params);
        return rows;
    }

    /**
     * 사용량 한도 설정 조회
     */
    getUsageLimits() {
        return {
            monthlyTokens: parseInt(process.env.PREMIUM_MONTHLY_TOKEN_LIMIT) || 100000,
            dailyRequests: parseInt(process.env.PREMIUM_DAILY_REQUEST_LIMIT) || 500,
            hourlyRequests: 50, // 급격한 사용량 증가 방지
            maxRequestSize: 8000, // 최대 토큰 수
            maxConcurrentRequests: 5 // 동시 요청 수 제한
        };
    }

    /**
     * 사용량 리셋 (관리자 기능)
     */
    async resetUserUsage(userId, resetType = 'monthly') {
        const now = new Date();
        
        try {
            if (resetType === 'monthly') {
                const monthlyKey = `usage:monthly:${userId}:${this.getMonthKey(now)}`;
                await this.redis.del(monthlyKey);
            } else if (resetType === 'daily') {
                const dailyKey = `usage:daily:${userId}:${this.getDateKey(now)}`;
                await this.redis.del(dailyKey);
            } else if (resetType === 'all') {
                const pattern = `usage:*:${userId}:*`;
                const keys = await this.redis.keys(pattern);
                if (keys.length > 0) {
                    await this.redis.del(...keys);
                }
            }

            console.log(`Usage reset completed for user ${userId}: ${resetType}`);
            return true;

        } catch (error) {
            console.error('Usage reset failed:', error);
            return false;
        }
    }

    /**
     * 키 생성 헬퍼 메서드들
     */
    getDateKey(date) {
        return date.toISOString().split('T')[0]; // YYYY-MM-DD
    }

    getMonthKey(date) {
        return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`; // YYYY-MM
    }

    getHourKey(date) {
        return `${this.getDateKey(date)}-${String(date.getHours()).padStart(2, '0')}`; // YYYY-MM-DD-HH
    }

    /**
     * 정리 작업 (크론잡에서 실행)
     */
    async cleanup() {
        try {
            // 90일 이전 로그 삭제
            const cleanupQuery = `
                DELETE FROM ai_usage_logs 
                WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)
            `;
            await this.dbPool.execute(cleanupQuery);

            // 만료된 Redis 키 확인 및 정리는 Redis가 자동으로 처리

            console.log('Usage data cleanup completed');

        } catch (error) {
            console.error('Usage cleanup failed:', error);
        }
    }

    /**
     * 연결 종료
     */
    async close() {
        await this.redis.quit();
        await this.dbPool.end();
    }
}

module.exports = UsageTracker;