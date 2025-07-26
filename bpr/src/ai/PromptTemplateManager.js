/**
 * AI 프롬프트 템플릿 관리자
 * BPR 관련 다양한 AI 작업을 위한 프롬프트 템플릿 관리
 */

const fs = require('fs').promises;
const path = require('path');
const Handlebars = require('handlebars');

class PromptTemplateManager {
    constructor() {
        this.templatesPath = process.env.PROMPT_TEMPLATES_PATH || path.join(__dirname, '../prompts');
        this.templates = new Map();
        this.systemPrompts = new Map();
        this.loadedAt = null;
        
        // Handlebars 헬퍼 등록
        this.registerHandlebarsHelpers();
    }

    /**
     * 모든 템플릿 로드
     */
    async loadTemplates() {
        try {
            await this.loadSystemPrompts();
            await this.loadFeatureTemplates();
            this.loadedAt = new Date();
            
            console.log(`Loaded ${this.templates.size} prompt templates and ${this.systemPrompts.size} system prompts`);
        } catch (error) {
            console.error('Failed to load prompt templates:', error);
            throw error;
        }
    }

    /**
     * 시스템 프롬프트 로드
     */
    async loadSystemPrompts() {
        const systemPromptsPath = path.join(this.templatesPath, 'system');
        
        const systemPromptFiles = {
            'bpr_expert': 'bpr_expert.txt',
            'bpr_optimizer': 'bpr_optimizer.txt',
            'bpr_consultant': 'bpr_consultant.txt',
            'org_consultant': 'org_consultant.txt',
            'task_analyst': 'task_analyst.txt'
        };

        for (const [key, filename] of Object.entries(systemPromptFiles)) {
            try {
                const filePath = path.join(systemPromptsPath, filename);
                const content = await fs.readFile(filePath, 'utf-8');
                this.systemPrompts.set(key, content.trim());
            } catch (error) {
                console.warn(`Failed to load system prompt ${key}:`, error.message);
                // 기본 시스템 프롬프트 사용
                this.systemPrompts.set(key, this.getDefaultSystemPrompt(key));
            }
        }
    }

    /**
     * 기능별 템플릿 로드
     */
    async loadFeatureTemplates() {
        const featureTemplates = {
            'task_manual': 'task_manual.hbs',
            'task_suggestions': 'task_suggestions.hbs',
            'process_optimization': 'process_optimization.hbs',
            'bpr_report_executive_summary': 'bpr_report/executive_summary.hbs',
            'bpr_report_current_state': 'bpr_report/current_state_analysis.hbs',
            'bpr_report_gap_analysis': 'bpr_report/gap_analysis.hbs',
            'bpr_report_recommendations': 'bpr_report/optimization_recommendations.hbs',
            'bpr_report_roadmap': 'bpr_report/implementation_roadmap.hbs',
            'bpr_report_risk_assessment': 'bpr_report/risk_assessment.hbs',
            'bpr_report_roi_projection': 'bpr_report/roi_projection.hbs',
            'org_optimization': 'org_optimization.hbs'
        };

        for (const [key, templatePath] of Object.entries(featureTemplates)) {
            try {
                const filePath = path.join(this.templatesPath, templatePath);
                const content = await fs.readFile(filePath, 'utf-8');
                const compiledTemplate = Handlebars.compile(content);
                this.templates.set(key, compiledTemplate);
            } catch (error) {
                console.warn(`Failed to load template ${key}:`, error.message);
                // 기본 템플릿 사용
                this.templates.set(key, this.getDefaultTemplate(key));
            }
        }
    }

    /**
     * 템플릿 가져오기
     */
    async getTemplate(templateKey, data = {}) {
        // 템플릿이 로드되지 않았으면 로드
        if (!this.loadedAt) {
            await this.loadTemplates();
        }

        const template = this.templates.get(templateKey);
        if (!template) {
            throw new Error(`Template not found: ${templateKey}`);
        }

        try {
            return template(data);
        } catch (error) {
            console.error(`Template rendering failed for ${templateKey}:`, error);
            throw new Error(`Failed to render template ${templateKey}: ${error.message}`);
        }
    }

    /**
     * 시스템 프롬프트 가져오기
     */
    async getSystemPrompt(promptKey) {
        if (!this.loadedAt) {
            await this.loadTemplates();
        }

        const systemPrompt = this.systemPrompts.get(promptKey);
        if (!systemPrompt) {
            throw new Error(`System prompt not found: ${promptKey}`);
        }

        return systemPrompt;
    }

    /**
     * 기본 시스템 프롬프트 생성
     */
    getDefaultSystemPrompt(key) {
        const defaultPrompts = {
            'bpr_expert': `당신은 비즈니스 프로세스 리엔지니어링(BPR) 전문가입니다.
조직의 업무 프로세스를 분석하고 최적화하는 데 특화되어 있습니다.
다음 지침을 따라 작업해주세요:

1. 명확하고 실행 가능한 조언 제공
2. 업계 모범 사례 기반 권고
3. 단계별 구체적 실행 방안 제시
4. 위험 요소와 완화 방안 포함
5. ROI 관점에서의 가치 평가

답변은 한국어로 작성하며, 전문적이면서도 이해하기 쉽게 설명해주세요.`,

            'bpr_optimizer': `당신은 프로세스 최적화 전문가입니다.
기존 프로세스를 분석하여 효율성 향상과 비용 절감을 위한 개선안을 제시합니다.

전문 영역:
- 프로세스 병목 지점 식별
- 자동화 기회 발굴
- 리소스 배분 최적화
- 성과 지표 개선
- 프로세스 표준화

분석 시 다음을 고려하세요:
- 현재 성과 지표
- 업계 벤치마크 비교
- 기술적 실현 가능성
- 변화 관리 요소
- 비용 대비 효과`,

            'bpr_consultant': `당신은 경영 컨설팅 전문가로서 BPR 프로젝트를 이끄는 역할을 합니다.
전략적 관점에서 조직 전체의 프로세스 혁신을 지원합니다.

핵심 역할:
- 현황 진단 및 문제점 식별
- 목표 상태 설계
- 변화 관리 전략 수립
- 이해관계자 관리
- 성과 측정 체계 구축

답변은 경영진이 이해할 수 있는 수준으로 작성하며, 
구체적인 액션 플랜과 타임라인을 포함해주세요.`,

            'org_consultant': `당신은 조직 구조 및 인사 전문가입니다.
효과적인 조직 운영을 위한 구조 설계와 역할 정의를 담당합니다.

전문 분야:
- 조직 구조 설계
- 역할과 책임 정의
- 의사소통 체계 구축
- 성과 관리 시스템
- 인재 배치 최적화

분석 시 고려사항:
- 조직 문화와 가치
- 사업 전략과의 연계
- 확장성과 유연성
- 의사결정 효율성
- 직원 만족도와 생산성`,

            'task_analyst': `당신은 업무 분석 전문가입니다.
세부 업무(Task)를 체계적으로 분석하고 개선 방안을 제시합니다.

분석 영역:
- 업무 절차 및 단계
- 소요 시간 및 리소스
- 필요 역량 및 도구
- 품질 기준 및 체크포인트
- 위험 요소 및 대응 방안

매뉴얼 작성 시:
- 초보자도 이해할 수 있는 명확한 설명
- 단계별 체크리스트 제공
- 문제 상황별 해결 방법
- 관련 자료 및 참고 링크
- 지속적 개선을 위한 피드백 방법`
        };

        return defaultPrompts[key] || `당신은 BPR 전문가입니다. 사용자의 요청을 도와주세요.`;
    }

    /**
     * 기본 템플릿 생성
     */
    getDefaultTemplate(key) {
        const defaultTemplates = {
            'task_manual': Handlebars.compile(`
# {{taskName}} 매뉴얼

## 개요
{{taskDescription}}

## 사전 요구사항
{{#each taskRequirements}}
- {{this}}
{{/each}}

## 실행 단계
{{#each taskSteps}}
### {{@index}}. {{this.title}}
{{this.description}}

{{#if this.checkpoints}}
**체크포인트:**
{{#each this.checkpoints}}
- [ ] {{this}}
{{/each}}
{{/if}}
{{/each}}

## 문제 해결
일반적인 문제상황과 해결방법을 제시해주세요.

## 관련 프로세스
{{#each relatedProcesses}}
- {{this}}
{{/each}}
`),

            'task_suggestions': Handlebars.compile(`
# {{processName}} 프로세스 Task 분석

## 현재 상황
**프로세스 목표:** {{processGoal}}

**현재 Task 목록:**
{{#each currentTasks}}
- {{this.name}}: {{this.description}}
{{/each}}

## 분석 요청
다음 관점에서 Task 목록을 분석하고 개선안을 제시해주세요:

1. **추가 권장 Task**
   - 프로세스 목표 달성을 위해 누락된 Task
   - 품질 향상을 위한 추가 Task
   - 리스크 관리를 위한 점검 Task

2. **삭제 검토 Task**
   - 중복되거나 불필요한 Task
   - ROI가 낮은 Task
   - 자동화 가능한 Task

3. **수정 권장 Task**
   - 효율성 향상을 위한 개선
   - 명확성 증대를 위한 재정의
   - 순서 조정이 필요한 Task

## 유사 프로세스 참고
{{#each similarProcesses}}
**{{this.name}}**
- 목표: {{this.goal}}
- 주요 Task: {{join this.tasks ", "}}
{{/each}}

답변은 JSON 형식으로 제공해주세요.
`),

            'process_optimization': Handlebars.compile(`
# {{processData.name}} 프로세스 최적화 분석

## 현재 상태
**프로세스 목표:** {{processData.goal}}
**현재 성과 지표:**
{{#each currentMetrics}}
- {{@key}}: {{this}}
{{/each}}

## 식별된 병목 지점
{{#each bottlenecks}}
**{{this.area}}**
- 문제: {{this.issue}}
- 영향도: {{this.impact}}
- 현재 소요시간: {{this.currentTime}}
{{/each}}

## 최적화 목표
{{#each processData.optimizationGoals}}
- {{this}}
{{/each}}

## 분석 요청
다음 영역에서 최적화 방안을 제시해주세요:

1. **프로세스 흐름 개선**
2. **자동화 기회**
3. **리소스 배분 최적화**
4. **품질 관리 개선**
5. **성과 지표 향상**

각 개선안에 대해 구현 난이도, 예상 효과, 소요 기간을 포함해주세요.
`)
        };

        return defaultTemplates[key] || Handlebars.compile('기본 템플릿: {{prompt}}');
    }

    /**
     * Handlebars 헬퍼 등록
     */
    registerHandlebarsHelpers() {
        // 배열 조인 헬퍼
        Handlebars.registerHelper('join', function(array, separator) {
            if (!Array.isArray(array)) return '';
            return array.join(separator || ', ');
        });

        // 인덱스 + 1 헬퍼
        Handlebars.registerHelper('inc', function(value) {
            return parseInt(value) + 1;
        });

        // 날짜 포맷 헬퍼
        Handlebars.registerHelper('formatDate', function(date, format) {
            if (!date) return '';
            const d = new Date(date);
            if (format === 'short') {
                return d.toLocaleDateString('ko-KR');
            }
            return d.toLocaleString('ko-KR');
        });

        // 조건부 렌더링 헬퍼
        Handlebars.registerHelper('ifCond', function(v1, operator, v2, options) {
            switch (operator) {
                case '==':
                    return (v1 == v2) ? options.fn(this) : options.inverse(this);
                case '===':
                    return (v1 === v2) ? options.fn(this) : options.inverse(this);
                case '!=':
                    return (v1 != v2) ? options.fn(this) : options.inverse(this);
                case '!==':
                    return (v1 !== v2) ? options.fn(this) : options.inverse(this);
                case '<':
                    return (v1 < v2) ? options.fn(this) : options.inverse(this);
                case '<=':
                    return (v1 <= v2) ? options.fn(this) : options.inverse(this);
                case '>':
                    return (v1 > v2) ? options.fn(this) : options.inverse(this);
                case '>=':
                    return (v1 >= v2) ? options.fn(this) : options.inverse(this);
                case '&&':
                    return (v1 && v2) ? options.fn(this) : options.inverse(this);
                case '||':
                    return (v1 || v2) ? options.fn(this) : options.inverse(this);
                default:
                    return options.inverse(this);
            }
        });

        // 반복 번호 헬퍼
        Handlebars.registerHelper('times', function(n, block) {
            let accum = '';
            for (let i = 0; i < n; ++i) {
                accum += block.fn({ index: i, number: i + 1 });
            }
            return accum;
        });

        // 문자열 길이 제한 헬퍼
        Handlebars.registerHelper('truncate', function(str, len) {
            if (!str || typeof str !== 'string') return '';
            if (str.length <= len) return str;
            return str.substring(0, len) + '...';
        });

        // 마크다운 리스트 헬퍼
        Handlebars.registerHelper('markdownList', function(items, options) {
            if (!Array.isArray(items)) return '';
            return items.map(item => `- ${item}`).join('\n');
        });

        // 숫자 포맷 헬퍼
        Handlebars.registerHelper('number', function(num) {
            if (typeof num !== 'number') return num;
            return num.toLocaleString('ko-KR');
        });
    }

    /**
     * 템플릿 리로드 (개발/운영 중 동적 업데이트)
     */
    async reloadTemplates() {
        this.templates.clear();
        this.systemPrompts.clear();
        await this.loadTemplates();
        console.log('Prompt templates reloaded successfully');
    }

    /**
     * 템플릿 유효성 검사
     */
    async validateTemplate(templateKey, sampleData = {}) {
        try {
            const result = await this.getTemplate(templateKey, sampleData);
            return {
                valid: true,
                result: result
            };
        } catch (error) {
            return {
                valid: false,
                error: error.message
            };
        }
    }

    /**
     * 사용 가능한 템플릿 목록
     */
    getAvailableTemplates() {
        return {
            templates: Array.from(this.templates.keys()),
            systemPrompts: Array.from(this.systemPrompts.keys()),
            loadedAt: this.loadedAt
        };
    }

    /**
     * 동적 템플릿 추가
     */
    addTemplate(key, templateString) {
        try {
            const compiledTemplate = Handlebars.compile(templateString);
            this.templates.set(key, compiledTemplate);
            return true;
        } catch (error) {
            console.error(`Failed to compile template ${key}:`, error);
            return false;
        }
    }

    /**
     * 동적 시스템 프롬프트 추가
     */
    addSystemPrompt(key, promptString) {
        this.systemPrompts.set(key, promptString.trim());
        return true;
    }
}

module.exports = PromptTemplateManager;