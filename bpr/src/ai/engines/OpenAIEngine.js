/**
 * OpenAI 엔진 구현체
 */

const { OpenAI } = require('openai');

class OpenAIEngine {
    constructor(config) {
        this.client = new OpenAI({
            apiKey: config.apiKey
        });
        this.model = config.model;
        this.maxTokens = config.maxTokens;
        this.temperature = config.temperature;
    }

    /**
     * AI 요청 실행
     */
    async execute(request) {
        try {
            const messages = this.formatMessages(request);
            
            const completion = await this.client.chat.completions.create({
                model: this.model,
                messages: messages,
                max_tokens: this.maxTokens,
                temperature: this.temperature,
                response_format: request.responseFormat || undefined
            });

            return {
                content: completion.choices[0].message.content,
                usage: {
                    promptTokens: completion.usage.prompt_tokens,
                    completionTokens: completion.usage.completion_tokens,
                    totalTokens: completion.usage.total_tokens
                },
                model: completion.model,
                finishReason: completion.choices[0].finish_reason
            };

        } catch (error) {
            throw new Error(`OpenAI API error: ${error.message}`);
        }
    }

    /**
     * 메시지 포맷 변환
     */
    formatMessages(request) {
        const messages = [];

        if (request.systemPrompt) {
            messages.push({
                role: 'system',
                content: request.systemPrompt
            });
        }

        if (request.context) {
            messages.push({
                role: 'user',
                content: `Context: ${request.context}`
            });
        }

        messages.push({
            role: 'user',
            content: request.prompt
        });

        return messages;
    }

    /**
     * 엔진 상태 확인
     */
    async healthCheck() {
        try {
            await this.client.chat.completions.create({
                model: this.model,
                messages: [{ role: 'user', content: 'Health check' }],
                max_tokens: 5
            });
            return true;
        } catch (error) {
            throw new Error(`OpenAI health check failed: ${error.message}`);
        }
    }

    /**
     * 스트리밍 응답 (실시간 UI 업데이트용)
     */
    async executeStream(request, callback) {
        try {
            const messages = this.formatMessages(request);
            
            const stream = await this.client.chat.completions.create({
                model: this.model,
                messages: messages,
                max_tokens: this.maxTokens,
                temperature: this.temperature,
                stream: true
            });

            let fullContent = '';
            for await (const chunk of stream) {
                const content = chunk.choices[0]?.delta?.content || '';
                fullContent += content;
                
                if (callback) {
                    callback({
                        type: 'chunk',
                        content: content,
                        fullContent: fullContent
                    });
                }
            }

            return {
                content: fullContent,
                usage: null, // 스트리밍에서는 사용량 정보가 별도로 제공됨
                model: this.model
            };

        } catch (error) {
            throw new Error(`OpenAI streaming error: ${error.message}`);
        }
    }
}

module.exports = OpenAIEngine;