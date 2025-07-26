/**
 * BPR AI 기능 API 라우트
 */

const express = require('express');
const router = express.Router();
const BPRAIService = require('../services/BPRAIService');
const authMiddleware = require('../middleware/authMiddleware');
const premiumMiddleware = require('../middleware/premiumMiddleware');
const rateLimitMiddleware = require('../middleware/rateLimitMiddleware');
const { body, param, validationResult } = require('express-validator');

const bprAIService = new BPRAIService();

// 모든 AI 기능은 인증 및 프리미엄 권한 필요
router.use(authMiddleware);
router.use(premiumMiddleware);
router.use(rateLimitMiddleware.aiFeatures);

/**
 * POST /api/ai/task/manual
 * Task별 매뉴얼 자동 생성
 */
router.post('/task/manual', [
    body('taskId').isUUID().withMessage('유효한 Task ID가 필요합니다'),
    body('includeSteps').optional().isBoolean(),
    body('includeTroubleshooting').optional().isBoolean(),
    body('language').optional().isIn(['ko', 'en']).withMessage('지원되지 않는 언어입니다')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { taskId, includeSteps = true, includeTroubleshooting = true, language = 'ko' } = req.body;
        
        // Task 데이터 조회
        const taskData = await getTaskData(taskId);
        if (!taskData) {
            return res.status(404).json({
                success: false,
                message: 'Task를 찾을 수 없습니다'
            });
        }

        // AI 매뉴얼 생성
        const manual = await bprAIService.generateTaskManual({
            ...taskData,
            options: { includeSteps, includeTroubleshooting, language }
        }, req.user.id);

        res.json({
            success: true,
            data: {
                manual: manual,
                metadata: {
                    taskId: taskId,
                    generatedAt: new Date(),
                    language: language
                }
            }
        });

    } catch (error) {
        console.error('Task manual generation error:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'AI 매뉴얼 생성 중 오류가 발생했습니다'
        });
    }
});

/**
 * POST /api/ai/process/task-suggestions
 * Process별 Task 목록 자동 제안
 */
router.post('/process/task-suggestions', [
    body('processId').isUUID().withMessage('유효한 Process ID가 필요합니다'),
    body('analysisDepth').optional().isIn(['basic', 'detailed', 'comprehensive']),
    body('focusAreas').optional().isArray(),
    body('excludeCurrentTasks').optional().isBoolean()
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { processId, analysisDepth = 'detailed', focusAreas = [], excludeCurrentTasks = false } = req.body;
        
        const processData = await getProcessData(processId);
        if (!processData) {
            return res.status(404).json({
                success: false,
                message: 'Process를 찾을 수 없습니다'
            });
        }

        const suggestions = await bprAIService.suggestProcessTasks({
            ...processData,
            options: { analysisDepth, focusAreas, excludeCurrentTasks }
        }, req.user.id);

        res.json({
            success: true,
            data: {
                suggestions: suggestions,
                metadata: {
                    processId: processId,
                    analysisDepth: analysisDepth,
                    generatedAt: new Date()
                }
            }
        });

    } catch (error) {
        console.error('Task suggestion error:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Task 제안 생성 중 오류가 발생했습니다'
        });
    }
});

/**
 * POST /api/ai/process/optimize
 * 프로세스 최적화 제안
 */
router.post('/process/optimize', [
    body('processId').isUUID().withMessage('유효한 Process ID가 필요합니다'),
    body('optimizationGoals').optional().isArray(),
    body('constraints').optional().isObject(),
    body('includeROIAnalysis').optional().isBoolean()
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { processId, optimizationGoals = [], constraints = {}, includeROIAnalysis = true } = req.body;
        
        const processData = await getProcessData(processId);
        const performanceMetrics = await getProcessMetrics(processId);
        
        if (!processData) {
            return res.status(404).json({
                success: false,
                message: 'Process를 찾을 수 없습니다'
            });
        }

        const optimization = await bprAIService.optimizeProcess(
            { ...processData, optimizationGoals, constraints },
            performanceMetrics,
            req.user.id
        );

        res.json({
            success: true,
            data: {
                optimization: optimization,
                metadata: {
                    processId: processId,
                    includeROIAnalysis: includeROIAnalysis,
                    generatedAt: new Date()
                }
            }
        });

    } catch (error) {
        console.error('Process optimization error:', error);
        res.status(500).json({
            success: false,
            message: error.message || '프로세스 최적화 분석 중 오류가 발생했습니다'
        });
    }
});

/**
 * POST /api/ai/report/generate
 * BPR 분석 리포트 자동 생성 (스트리밍)
 */
router.post('/report/generate', [
    body('projectId').isUUID().withMessage('유효한 Project ID가 필요합니다'),
    body('reportType').isIn(['comprehensive', 'executive', 'technical']),
    body('sections').optional().isArray(),
    body('format').optional().isIn(['pdf', 'docx', 'html'])
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { projectId, reportType, sections, format = 'pdf' } = req.body;
        
        // SSE 설정 (Server-Sent Events로 실시간 진행 상황 전송)
        res.writeHead(200, {
            'Content-Type': 'text/event-stream',
            'Cache-Control': 'no-cache',
            'Connection': 'keep-alive',
            'Access-Control-Allow-Origin': '*'
        });

        const projectData = await getProjectData(projectId);
        if (!projectData) {
            res.write(`data: ${JSON.stringify({ error: 'Project를 찾을 수 없습니다' })}\n\n`);
            res.end();
            return;
        }

        // 진행 상황 콜백
        const progressCallback = (section, total) => {
            res.write(`data: ${JSON.stringify({
                type: 'progress',
                section: section,
                progress: Math.round((section / total) * 100)
            })}\n\n`);
        };

        try {
            const reportResult = await bprAIService.generateBPRReport({
                ...projectData,
                reportType,
                sections,
                format,
                progressCallback
            }, req.user.id);

            res.write(`data: ${JSON.stringify({
                type: 'complete',
                data: reportResult
            })}\n\n`);

        } catch (error) {
            res.write(`data: ${JSON.stringify({
                type: 'error',
                message: error.message
            })}\n\n`);
        }

        res.end();

    } catch (error) {
        console.error('Report generation error:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'BPR 리포트 생성 중 오류가 발생했습니다'
        });
    }
});

/**
 * POST /api/ai/organization/optimize
 * 조직 구조 최적화 제안
 */
router.post('/organization/optimize', [
    body('organizationId').isUUID().withMessage('유효한 Organization ID가 필요합니다'),
    body('optimizationScope').optional().isIn(['full', 'departmental', 'role-based']),
    body('constraints').optional().isObject()
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { organizationId, optimizationScope = 'full', constraints = {} } = req.body;
        
        const orgData = await getOrganizationData(organizationId);
        if (!orgData) {
            return res.status(404).json({
                success: false,
                message: '조직 정보를 찾을 수 없습니다'
            });
        }

        const optimization = await bprAIService.optimizeOrganization({
            ...orgData,
            optimizationScope,
            constraints
        }, req.user.id);

        res.json({
            success: true,
            data: {
                optimization: optimization,
                metadata: {
                    organizationId: organizationId,
                    optimizationScope: optimizationScope,
                    generatedAt: new Date()
                }
            }
        });

    } catch (error) {
        console.error('Organization optimization error:', error);
        res.status(500).json({
            success: false,
            message: error.message || '조직 최적화 분석 중 오류가 발생했습니다'
        });
    }
});

/**
 * GET /api/ai/usage
 * AI 사용량 조회
 */
router.get('/usage', async (req, res) => {
    try {
        const usage = await bprAIService.getUserUsage(req.user.id);
        
        res.json({
            success: true,
            data: {
                usage: usage,
                limits: {
                    monthlyTokens: parseInt(process.env.PREMIUM_MONTHLY_TOKEN_LIMIT) || 100000,
                    dailyRequests: parseInt(process.env.PREMIUM_DAILY_REQUEST_LIMIT) || 500
                }
            }
        });

    } catch (error) {
        console.error('Usage query error:', error);
        res.status(500).json({
            success: false,
            message: '사용량 조회 중 오류가 발생했습니다'
        });
    }
});

/**
 * GET /api/ai/engines/status
 * AI 엔진 상태 확인
 */
router.get('/engines/status', async (req, res) => {
    try {
        const status = await bprAIService.getEngineStatus();
        
        res.json({
            success: true,
            data: {
                engines: status,
                currentEngine: process.env.AI_ENGINE || 'openai',
                fallbackEngine: process.env.AI_FALLBACK_ENGINE || 'anthropic'
            }
        });

    } catch (error) {
        console.error('Engine status error:', error);
        res.status(500).json({
            success: false,
            message: '엔진 상태 확인 중 오류가 발생했습니다'
        });
    }
});

// 헬퍼 함수들
async function getTaskData(taskId) {
    // 실제 구현에서는 데이터베이스에서 조회
    // return await TaskModel.findById(taskId);
}

async function getProcessData(processId) {
    // 실제 구현에서는 데이터베이스에서 조회
    // return await ProcessModel.findById(processId);
}

async function getProcessMetrics(processId) {
    // 실제 구현에서는 성능 메트릭 데이터 조회
    // return await MetricsService.getProcessMetrics(processId);
}

async function getProjectData(projectId) {
    // 실제 구현에서는 프로젝트 데이터 조회
    // return await ProjectModel.findById(projectId);
}

async function getOrganizationData(organizationId) {
    // 실제 구현에서는 조직 데이터 조회
    // return await OrganizationModel.findById(organizationId);
}

module.exports = router;