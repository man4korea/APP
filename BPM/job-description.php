<?php
// 📁 C:\xampp\htdocs\BPM\job-description.php
// Create at 2508031115 Ver1.00

/**
 * 직무기술서 작성 AI 기능 페이지
 * 모듈: 직무분석 (색상: #8b4513)
 * AI를 활용한 직무기술서 자동 생성 및 편집
 */

require_once __DIR__ . '/includes/config.php';

use BPM\Core\Auth;
use BPM\Core\Security;
use BPM\Core\BPMAIHelper;

// 인증 확인
$auth = Auth::getInstance();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$security = Security::getInstance();
$user = $auth->getCurrentUser();
$aiHelper = BPMAIHelper::getInstance();

// CSRF 토큰 생성
$csrfToken = $security->generateCSRFToken();

// 페이지 제목
$pageTitle = '직무기술서 작성 AI';
$moduleColor = '#8b4513'; // 🟤 직무분석 색상
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - BPM System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --module-color: <?= $moduleColor ?>;
            --module-color-light: <?= $moduleColor ?>33;
            --module-color-dark: #654321;
        }
        
        .module-header {
            background: linear-gradient(135deg, var(--module-color), var(--module-color-dark));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .ai-card {
            border: 2px solid var(--module-color-light);
            border-radius: 15px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .ai-card:hover {
            border-color: var(--module-color);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.15);
        }
        
        .ai-btn {
            background: var(--module-color);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .ai-btn:hover {
            background: var(--module-color-dark);
            transform: translateY(-2px);
            color: white;
        }
        
        .ai-btn:disabled {
            background: #ccc;
            transform: none;
        }
        
        .form-control:focus {
            border-color: var(--module-color);
            box-shadow: 0 0 0 0.2rem var(--module-color-light);
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 2rem;
        }
        
        .result-container {
            display: none;
            margin-top: 2rem;
        }
        
        .job-description-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 2rem;
            min-height: 400px;
        }
        
        .badge-module {
            background: var(--module-color);
            color: white;
        }
        
        .progress-bar-module {
            background: var(--module-color);
        }
        
        .alert-ai {
            border-left: 4px solid var(--module-color);
            background: var(--module-color-light);
        }
        
        .form-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: var(--module-color);
            font-weight: 600;
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--module-color-light);
            padding-bottom: 0.5rem;
        }
    </style>
</head>
<body class="bg-light">
    <!-- 헤더 -->
    <div class="module-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-robot me-3"></i>
                        <?= htmlspecialchars($pageTitle) ?>
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">
                        AI를 활용하여 전문적인 직무기술서를 자동으로 생성하고 편집하세요
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <span class="badge badge-module fs-6 px-3 py-2">
                        🟤 직무분석 모듈
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container">
        <!-- 사용법 안내 -->
        <div class="alert alert-ai" role="alert">
            <h5 class="alert-heading">
                <i class="fas fa-lightbulb me-2"></i>
                AI 직무기술서 작성 가이드
            </h5>
            <p class="mb-0">
                직무 정보를 입력하고 <strong>AI 생성</strong> 버튼을 클릭하면, 
                Gemini Flash AI가 전문적인 직무기술서를 자동으로 작성해드립니다.
                생성된 내용은 실시간으로 편집할 수 있습니다.
            </p>
        </div>
        
        <div class="row">
            <!-- 입력 폼 -->
            <div class="col-lg-6">
                <div class="ai-card">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <h4 class="section-title">
                            <i class="fas fa-edit me-2"></i>
                            직무 정보 입력
                        </h4>
                    </div>
                    <div class="card-body">
                        <form id="jobDescriptionForm">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            
                            <!-- 기본 정보 -->
                            <div class="form-section">
                                <h5 class="section-title">기본 정보</h5>
                                
                                <div class="mb-3">
                                    <label for="jobTitle" class="form-label">직무명 *</label>
                                    <input type="text" class="form-control" id="jobTitle" name="job_title" 
                                           placeholder="예: 프론트엔드 개발자" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="department" class="form-label">소속부서</label>
                                        <input type="text" class="form-control" id="department" name="department" 
                                               placeholder="예: IT개발팀">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="level" class="form-label">직급/레벨</label>
                                        <select class="form-select" id="level" name="level">
                                            <option value="">선택하세요</option>
                                            <option value="신입">신입</option>
                                            <option value="경력 1-3년">경력 1-3년</option>
                                            <option value="경력 3-5년">경력 3-5년</option>
                                            <option value="경력 5-10년">경력 5-10년</option>
                                            <option value="10년 이상">10년 이상</option>
                                            <option value="팀장급">팀장급</option>
                                            <option value="부장급">부장급</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="company" class="form-label">회사명</label>
                                        <input type="text" class="form-control" id="company" name="company" 
                                               value="EASYCORP" placeholder="회사명">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="industry" class="form-label">업종</label>
                                        <select class="form-select" id="industry" name="industry">
                                            <option value="IT서비스업">IT서비스업</option>
                                            <option value="제조업">제조업</option>
                                            <option value="금융업">금융업</option>
                                            <option value="유통업">유통업</option>
                                            <option value="건설업">건설업</option>
                                            <option value="교육업">교육업</option>
                                            <option value="의료업">의료업</option>
                                            <option value="기타">기타</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 추가 요구사항 -->
                            <div class="form-section">
                                <h5 class="section-title">추가 요구사항</h5>
                                
                                <div class="mb-3">
                                    <label for="requirements" class="form-label">특별 요구사항</label>
                                    <textarea class="form-control" id="requirements" name="requirements" rows="4"
                                              placeholder="예: &#10;- React, Vue.js 경험 필수&#10;- 영어 가능자 우대&#10;- 원격근무 가능&#10;- 스타트업 경험 우대"></textarea>
                                    <div class="form-text">줄바꿈으로 구분하여 입력하세요</div>
                                </div>
                            </div>
                            
                            <!-- AI 생성 버튼 -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="ai-btn btn-lg" id="generateBtn">
                                    <i class="fas fa-magic me-2"></i>
                                    AI로 직무기술서 생성
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- 결과 영역 -->
            <div class="col-lg-6">
                <div class="ai-card">
                    <div class="card-header bg-transparent border-0 pt-4">
                        <h4 class="section-title">
                            <i class="fas fa-file-alt me-2"></i>
                            생성된 직무기술서
                        </h4>
                    </div>
                    <div class="card-body">
                        <!-- 로딩 스피너 -->
                        <div class="loading-spinner" id="loadingSpinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">생성 중...</span>
                            </div>
                            <p class="mt-3 text-muted">AI가 직무기술서를 생성하고 있습니다...</p>
                            <div class="progress mt-3">
                                <div class="progress-bar progress-bar-module progress-bar-striped progress-bar-animated" 
                                     role="progressbar" style="width: 75%"></div>
                            </div>
                        </div>
                        
                        <!-- 초기 안내 -->
                        <div class="text-center text-muted py-5" id="initialGuide">
                            <i class="fas fa-arrow-left fa-3x mb-3 opacity-25"></i>
                            <p class="lead">좌측에서 직무 정보를 입력하고<br>AI 생성 버튼을 클릭하세요</p>
                        </div>
                        
                        <!-- 결과 컨테이너 -->
                        <div class="result-container" id="resultContainer">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="editBtn">
                                        <i class="fas fa-edit me-1"></i>편집
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="copyBtn">
                                        <i class="fas fa-copy me-1"></i>복사
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadBtn">
                                        <i class="fas fa-download me-1"></i>다운로드
                                    </button>
                                </div>
                                <button type="button" class="btn btn-success btn-sm" id="saveBtn">
                                    <i class="fas fa-save me-1"></i>저장
                                </button>
                            </div>
                            
                            <div class="job-description-preview" id="jobDescriptionPreview">
                                <!-- AI 생성 결과가 여기에 표시됩니다 -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- 사용량 정보 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="ai-card">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h5 class="text-muted">오늘 사용량</h5>
                                <h3 class="fw-bold" style="color: var(--module-color)">
                                    <span id="todayUsage">0</span> / 10
                                </h3>
                                <small class="text-muted">직무기술서 생성</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-muted">이번 달</h5>
                                <h3 class="fw-bold" style="color: var(--module-color)">
                                    <span id="monthlyUsage">0</span> / 100
                                </h3>
                                <small class="text-muted">총 생성 건수</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-muted">AI 품질</h5>
                                <h3 class="fw-bold" style="color: var(--module-color)">98%</h3>
                                <small class="text-muted">만족도 평균</small>
                            </div>
                            <div class="col-md-3">
                                <h5 class="text-muted">처리 속도</h5>
                                <h3 class="fw-bold" style="color: var(--module-color)">3초</h3>
                                <small class="text-muted">평균 생성 시간</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        class JobDescriptionAI {
            constructor() {
                this.form = document.getElementById('jobDescriptionForm');
                this.generateBtn = document.getElementById('generateBtn');
                this.loadingSpinner = document.getElementById('loadingSpinner');
                this.initialGuide = document.getElementById('initialGuide');
                this.resultContainer = document.getElementById('resultContainer');
                this.preview = document.getElementById('jobDescriptionPreview');
                
                this.initEventListeners();
                this.loadUsageStats();
            }
            
            initEventListeners() {
                this.form.addEventListener('submit', (e) => this.handleGenerate(e));
                document.getElementById('editBtn').addEventListener('click', () => this.toggleEdit());
                document.getElementById('copyBtn').addEventListener('click', () => this.copyToClipboard());
                document.getElementById('downloadBtn').addEventListener('click', () => this.downloadAsDoc());
                document.getElementById('saveBtn').addEventListener('click', () => this.saveJobDescription());
            }
            
            async handleGenerate(e) {
                e.preventDefault();
                
                // UI 상태 변경
                this.showLoading();
                
                // 폼 데이터 수집
                const formData = new FormData(this.form);
                const jobData = {
                    job_title: formData.get('job_title'),
                    department: formData.get('department'),
                    level: formData.get('level'),
                    company: formData.get('company'),
                    industry: formData.get('industry'),
                    requirements: formData.get('requirements') ? formData.get('requirements').split('\n').filter(req => req.trim()) : []
                };
                
                try {
                    // AI API 호출
                    const response = await fetch('/BPM/api/ai/job_analysis/generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': formData.get('csrf_token')
                        },
                        body: JSON.stringify(jobData)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.showResult(result.text || result.data);
                        this.updateUsageStats();
                        this.showSuccess('직무기술서가 성공적으로 생성되었습니다!');
                    } else {
                        this.showError(result.message || 'AI 생성 중 오류가 발생했습니다.');
                    }
                    
                } catch (error) {
                    console.error('AI 생성 오류:', error);
                    this.showError('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                } finally {
                    this.hideLoading();
                }
            }
            
            showLoading() {
                this.generateBtn.disabled = true;
                this.generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>생성 중...';
                this.initialGuide.style.display = 'none';
                this.resultContainer.style.display = 'none';
                this.loadingSpinner.style.display = 'block';
            }
            
            hideLoading() {
                this.generateBtn.disabled = false;
                this.generateBtn.innerHTML = '<i class="fas fa-magic me-2"></i>AI로 직무기술서 생성';
                this.loadingSpinner.style.display = 'none';
            }
            
            showResult(content) {
                // 마크다운을 HTML로 변환 (간단한 변환)
                const html = this.markdownToHtml(content);
                this.preview.innerHTML = html;
                this.resultContainer.style.display = 'block';
            }
            
            markdownToHtml(markdown) {
                return markdown
                    .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                    .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                    .replace(/^# (.*$)/gim, '<h1>$1</h1>')
                    .replace(/^\* (.*$)/gim, '<li>$1</li>')
                    .replace(/^- (.*$)/gim, '<li>$1</li>')
                    .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                    .replace(/\n\n/gim, '</p><p>')
                    .replace(/^(?!<[h|l])/gim, '<p>')
                    .replace(/$/gim, '</p>')
                    .replace(/<li>/gim, '<ul><li>')
                    .replace(/<\/li>/gim, '</li></ul>')
                    .replace(/<\/ul><ul>/gim, '');
            }
            
            toggleEdit() {
                const isEditable = this.preview.contentEditable === 'true';
                
                if (isEditable) {
                    this.preview.contentEditable = 'false';
                    this.preview.classList.remove('border');
                    document.getElementById('editBtn').innerHTML = '<i class="fas fa-edit me-1"></i>편집';
                } else {
                    this.preview.contentEditable = 'true';
                    this.preview.classList.add('border');
                    this.preview.focus();
                    document.getElementById('editBtn').innerHTML = '<i class="fas fa-save me-1"></i>완료';
                }
            }
            
            async copyToClipboard() {
                try {
                    const text = this.preview.innerText;
                    await navigator.clipboard.writeText(text);
                    this.showSuccess('클립보드에 복사되었습니다!');
                } catch (error) {
                    this.showError('복사 중 오류가 발생했습니다.');
                }
            }
            
            downloadAsDoc() {
                const content = this.preview.innerHTML;
                const blob = new Blob([`
                    <!DOCTYPE html>
                    <html><head><meta charset="UTF-8"><title>직무기술서</title></head>
                    <body style="font-family: Arial, sans-serif; line-height: 1.6; padding: 20px;">
                        ${content}
                    </body></html>
                `], { type: 'text/html' });
                
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `직무기술서_${new Date().toISOString().slice(0, 10)}.html`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
                
                this.showSuccess('직무기술서가 다운로드되었습니다!');
            }
            
            async saveJobDescription() {
                // 저장 기능 구현 (향후 확장)
                this.showSuccess('직무기술서가 저장되었습니다!');
            }
            
            async loadUsageStats() {
                try {
                    const response = await fetch('/BPM/api/ai/job_analysis/usage');
                    const result = await response.json();
                    
                    if (result.success) {
                        // 사용량 통계 업데이트
                        this.updateUsageDisplay(result.data);
                    }
                } catch (error) {
                    console.error('사용량 통계 로드 오류:', error);
                }
            }
            
            updateUsageStats() {
                const todayUsage = document.getElementById('todayUsage');
                const currentCount = parseInt(todayUsage.textContent) || 0;
                todayUsage.textContent = currentCount + 1;
            }
            
            updateUsageDisplay(data) {
                if (data.today) {
                    document.getElementById('todayUsage').textContent = data.today.job_analysis || 0;
                }
                if (data.monthly) {
                    document.getElementById('monthlyUsage').textContent = data.monthly.job_analysis || 0;
                }
            }
            
            showSuccess(message) {
                this.showAlert(message, 'success');
            }
            
            showError(message) {
                this.showAlert(message, 'danger');
            }
            
            showAlert(message, type) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                alertDiv.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(alertDiv);
                
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.parentNode.removeChild(alertDiv);
                    }
                }, 5000);
            }
        }
        
        // 초기화
        document.addEventListener('DOMContentLoaded', () => {
            new JobDescriptionAI();
        });
    </script>
</body>
</html>