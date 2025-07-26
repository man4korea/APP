/**
 * AI Engine Manager
 * 다중 AI 엔진을 통합 관리하는 핵심 클래스
 */

const OpenAIEngine = require('./engines/OpenAIEngine');
const AnthropicEngine = require('./engines/AnthropicEngine');
const GoogleEngine = require('./engines/GoogleEngine');
const AzureEngine = require('./engines/AzureEngine');
const UsageTracker = require('../tracking/UsageTracker');

class AIEngineManager {
    constructor() {
        this.engines = new Map();
        this.primaryEngine = null;
        this.fallbackEngine = null;
        this.usageTracker = new UsageTracker();
        this.initializeEngines();
    }

    /**
     * 설정된 AI 엔진들을 초기화
     */
    initializeEngines() {
        // OpenAI 엔진 초기화
        if (process.env.OPENAI_API_KEY) {
            this.engines.set('openai', new OpenAIEngine({
                apiKey: process.env.OPENAI_API_KEY,
                model: process.env.OPENAI_MODEL || 'gpt-4',
                maxTokens: parseInt(process.env.OPENAI_MAX_TOKENS) || 4000,
                temperature: parseFloat(process.env.OPENAI_TEMPERATURE) || 0.7
            }));
        }

        // Anthropic 엔진 초기화
        if (process.env.ANTHROPIC_API_KEY) {
            this.engines.set('anthropic', new AnthropicEngine({
                apiKey: process.env.ANTHROPIC_API_KEY,
                model: process.env.ANTHROPIC_MODEL || 'claude-3-sonnet-20240229',
                maxTokens: parseInt(process.env.ANTHROPIC_MAX_TOKENS) || 4000,
                temperature: parseFloat(process.env.ANTHROPIC_TEMPERATURE) || 0.7
            }));
        }

        // Google 엔진 초기화
        if (process.env.GOOGLE_API_KEY) {
            this.engines.set('google', new GoogleEngine({
                apiKey: process.env.GOOGLE_API_KEY,
                model: process.env.GOOGLE_MODEL || 'gemini-pro',
                maxTokens: parseInt(process.env.GOOGLE_MAX_TOKENS) || 4000,
                temperature: parseFloat(process.env.GOOGLE_TEMPERATURE) || 0.7
            }));
        }

        // Azure 엔진 초기화
        if (process.env.AZURE_OPENAI_API_KEY && process.env.AZURE_OPENAI_ENDPOINT) {
            this.engines.set('azure', new AzureEngine({
                apiKey: process.env.AZURE_OPENAI_API_KEY,
                endpoint: process.env.AZURE_OPENAI_ENDPOINT,
                deploymentName: process.env.AZURE_OPENAI_DEPLOYMENT_NAME,
                apiVersion: process.env.AZURE_OPENAI_API_VERSION
            }));
        }

        // 기본 및 폴백 엔진 설정
        this.primaryEngine = this.engines.get(process.env.AI_PRIMARY_ENGINE || 'openai');
        this.fallbackEngine = this.engines.get(process.env.AI_FALLBACK_ENGINE || 'anthropic');

        console.log(`AI Engine Manager 초기화 완료: ${this.engines.size}개 엔진 등록됨`);
    }

    /**
     * AI 요청 처리 (폴백 메커니즘 포함)
     */
    async processRequest(prompt, options = {}) {
        const startTime = Date.now();
        let result = null;
        let usedEngine = null;
        let error = null;

        try {
            // 기본 엔진으로 시도
            if (this.primaryEngine) {
                try {
                    result = await this.primaryEngine.generate(prompt, options);
                    usedEngine = process.env.AI_PRIMARY_ENGINE;
                    console.log(`기본 엔진(${usedEngine}) 요청 성공`);
                } catch (primaryError) {
                    console.warn(`기본 엔진 실패: ${primaryError.message}`);
                    error = primaryError;
                }
            }

            // 폴백 엔진으로 재시도
            if (!result && this.fallbackEngine) {
                try {
                    result = await this.fallbackEngine.generate(prompt, options);
                    usedEngine = process.env.AI_FALLBACK_ENGINE;
                    console.log(`폴백 엔진(${usedEngine}) 요청 성공`);
                } catch (fallbackError) {
                    console.error(`폴백 엔진 실패: ${fallbackError.message}`);
                    error = fallbackError;
                }
            }

            if (!result) {
                throw new Error('모든 AI 엔진 요청 실패');
            }

            // 사용량 추적
            const processingTime = Date.now() - startTime;
            await this.usageTracker.trackUsage({
                userId: options.userId,
                engine: usedEngine,
                promptTokens: this.estimateTokens(prompt),
                completionTokens: this.estimateTokens(result.content),
                processingTime,
                success: true
            });

            return {
                content: result.content,
                engine: usedEngine,
                processingTime,
                tokenUsage: {
                    prompt: this.estimateTokens(prompt),
                    completion: this.estimateTokens(result.content),
                    total: this.estimateTokens(prompt) + this.estimateTokens(result.content)
                }
            };

        } catch (finalError) {
            // 실패 시 사용량 추적
            const processingTime = Date.now() - startTime;
            await this.usageTracker.trackUsage({
                userId: options.userId,
                engine: usedEngine || 'unknown',
                promptTokens: this.estimateTokens(prompt),
                completionTokens: 0,
                processingTime,
                success: false,
                error: finalError.message
            });

            throw finalError;
        }
    }

    /**
     * 스트리밍 요청 처리
     */
    async processStreamingRequest(prompt, options = {}) {
        const engine = this.primaryEngine || this.fallbackEngine;
        if (!engine) {
            throw new Error('사용 가능한 AI 엔진이 없습니다');
        }

        if (!engine.generateStream) {
            throw new Error('선택된 엔진이 스트리밍을 지원하지 않습니다');
        }

        return engine.generateStream(prompt, options);
    }

    /**
     * 토큰 수 추정 (대략적인 계산)
     */
    estimateTokens(text) {
        if (!text) return 0;
        // 영어: 약 4글자당 1토큰, 한국어: 약 2-3글자당 1토큰
        const koreanChars = (text.match(/[가-힣]/g) || []).length;
        const otherChars = text.length - koreanChars;
        return Math.ceil(koreanChars / 2.5 + otherChars / 4);
    }

    /**
     * 사용 가능한 엔진 목록 반환
     */
    getAvailableEngines() {
        return Array.from(this.engines.keys());
    }

    /**
     * 특정 엔진의 상태 확인
     */
    async checkEngineHealth(engineName) {
        const engine = this.engines.get(engineName);
        if (!engine) {
            return { status: 'not_found', message: '엔진을 찾을 수 없습니다' };
        }

        try {
            await engine.healthCheck();
            return { status: 'healthy', message: '정상 작동 중' };
        } catch (error) {
            return { status: 'unhealthy', message: error.message };
        }
    }

    /**
     * 엔진 동적 전환
     */
    switchEngine(newPrimaryEngine, newFallbackEngine = null) {
        if (!this.engines.has(newPrimaryEngine)) {
            throw new Error(`엔진 '${newPrimaryEngine}'을 찾을 수 없습니다`);
        }

        this.primaryEngine = this.engines.get(newPrimaryEngine);
        
        if (newFallbackEngine && this.engines.has(newFallbackEngine)) {
            this.fallbackEngine = this.engines.get(newFallbackEngine);
        }

        console.log(`엔진 전환 완료: Primary=${newPrimaryEngine}, Fallback=${newFallbackEngine || 'none'}`);
    }

    /**
     * 엔진별 사용량 통계
     */
    async getEngineStats() {
        return await this.usageTracker.getEngineStats();
    }

    /**
     * 리소스 정리
     */
    async cleanup() {
        for (const [name, engine] of this.engines) {
            if (engine.cleanup) {
                try {
                    await engine.cleanup();
                    console.log(`엔진 ${name} 정리 완료`);
                } catch (error) {
                    console.error(`엔진 ${name} 정리 실패:`, error);
                }
            }
        }
        this.engines.clear();
    }
}

module.exports = AIEngineManager;