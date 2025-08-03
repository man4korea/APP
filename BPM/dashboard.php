<!-- 📁 C:\xampp\htdocs\BPM\dashboard.php -->
<!-- Create at 2508031655 Ver1.00 -->

<?php
/**
 * BPM 대시보드 페이지
 * 통합 대시보드 및 모든 컴포넌트 통합 테스트
 */

// 기본 설정 로드
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/core/bootstrap.php';

// 로그인 확인
if (!is_logged_in()) {
    header('Location: ' . base_url('login.php'));
    exit;
}

// 현재 사용자 정보
$currentUser = current_user();
$currentModule = 'dashboard';

// 페이지 설정
$title = 'EASYCORP BPM - 대시보드';
$bodyClass = 'dashboard-page';

// 대시보드 데이터 (임시 데이터)
$dashboardStats = [
    'total_users' => 156,
    'active_tasks' => 89,
    'pending_approvals' => 23,
    'completed_processes' => 1247
];

$recentActivities = [
    [
        'type' => 'task_completed',
        'user' => '김영희',
        'action' => '업무 승인 요청',
        'target' => '신제품 기획서 검토',
        'time' => '5분 전',
        'module' => 'workflow'
    ],
    [
        'type' => 'user_added',
        'user' => '이철수',
        'action' => '새 사용자 등록',
        'target' => '마케팅팀',
        'time' => '15분 전',
        'module' => 'members'
    ],
    [
        'type' => 'document_uploaded',
        'user' => '박미영',
        'action' => '문서 업로드',
        'target' => '2024년 사업계획서.pdf',
        'time' => '1시간 전',
        'module' => 'management'
    ],
    [
        'type' => 'department_created',
        'user' => '최관리자',
        'action' => '새 부서 생성',
        'target' => 'AI개발팀',
        'time' => '2시간 전',
        'module' => 'organization'
    ]
];

// 대시보드 콘텐츠
$content = '
<div class="dashboard-container">
    
    <!-- 대시보드 헤더 -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1 class="dashboard-title">환영합니다, ' . htmlspecialchars($currentUser['name'] ?? '사용자') . '님!</h1>
            <p class="dashboard-subtitle">오늘도 효율적인 업무 관리를 시작해보세요.</p>
        </div>
        <div class="quick-actions">
            <button class="quick-action-btn" data-module="workflow">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                새 작업
            </button>
            <button class="quick-action-btn" data-module="members">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="8.5" cy="7" r="4"></circle>
                    <line x1="20" y1="8" x2="20" y2="14"></line>
                    <line x1="23" y1="11" x2="17" y2="11"></line>
                </svg>
                사용자 초대
            </button>
            <button class="quick-action-btn" data-module="management">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14,2 14,8 20,8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
                문서 업로드
            </button>
        </div>
    </div>
    
    <!-- 통계 카드 -->
    <div class="stats-grid">
        <div class="stat-card" data-module="members">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">' . number_format($dashboardStats['total_users']) . '</h3>
                <p class="stat-label">전체 사용자</p>
                <span class="stat-change positive">+12 이번 달</span>
            </div>
        </div>
        
        <div class="stat-card" data-module="workflow">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="8" y="8" width="8" height="8" rx="2"></rect>
                    <path d="M4 8V6a2 2 0 0 1 2-2h2"></path>
                    <path d="M4 16v2a2 2 0 0 0 2 2h2"></path>
                    <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                    <path d="M16 20h2a2 2 0 0 0 2-2v-2"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">' . number_format($dashboardStats['active_tasks']) . '</h3>
                <p class="stat-label">진행중 작업</p>
                <span class="stat-change positive">+5 오늘</span>
            </div>
        </div>
        
        <div class="stat-card" data-module="workflow">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12,6 12,12 16,14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">' . number_format($dashboardStats['pending_approvals']) . '</h3>
                <p class="stat-label">승인 대기</p>
                <span class="stat-change negative">-3 어제</span>
            </div>
        </div>
        
        <div class="stat-card" data-module="management">
            <div class="stat-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9,11 12,14 22,4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
            </div>
            <div class="stat-content">
                <h3 class="stat-number">' . number_format($dashboardStats['completed_processes']) . '</h3>
                <p class="stat-label">완료된 프로세스</p>
                <span class="stat-change positive">+89 이번 주</span>
            </div>
        </div>
    </div>
    
    <!-- 메인 컨텐츠 그리드 -->
    <div class="main-content-grid">
        
        <!-- 최근 활동 -->
        <div class="content-card recent-activities">
            <div class="card-header">
                <h2 class="card-title">최근 활동</h2>
                <button class="card-action">전체보기</button>
            </div>
            <div class="card-content">
                <div class="activity-list">
';

// 최근 활동 렌더링
foreach ($recentActivities as $activity) {
    $moduleColors = [
        'workflow' => '#feca57',
        'members' => '#ff9f43', 
        'management' => '#55a3ff',
        'organization' => '#ff6b6b'
    ];
    $color = $moduleColors[$activity['module']] ?? '#3742fa';
    
    $content .= '
                    <div class="activity-item" data-module="' . $activity['module'] . '">
                        <div class="activity-avatar" style="background: ' . $color . '20; color: ' . $color . '">
                            ' . strtoupper(substr($activity['user'], 0, 1)) . '
                        </div>
                        <div class="activity-content">
                            <p class="activity-text">
                                <strong>' . htmlspecialchars($activity['user']) . '</strong>님이 
                                <span class="activity-action">' . htmlspecialchars($activity['action']) . '</span>했습니다.
                                <br><span class="activity-target">' . htmlspecialchars($activity['target']) . '</span>
                            </p>
                            <span class="activity-time">' . htmlspecialchars($activity['time']) . '</span>
                        </div>
                    </div>';
}

$content .= '
                </div>
            </div>
        </div>
        
        <!-- 빠른 접근 -->
        <div class="content-card quick-access">
            <div class="card-header">
                <h2 class="card-title">빠른 접근</h2>
            </div>
            <div class="card-content">
                <div class="quick-access-grid">
                    <a href="#" class="quick-access-item" data-module="organization">
                        <div class="access-icon" style="background: #ff6b6b20; color: #ff6b6b;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 21h18"></path>
                                <path d="M5 21V7l8-4v18"></path>
                                <path d="M19 21V11l-6-4"></path>
                            </svg>
                        </div>
                        <span class="access-label">조직관리</span>
                    </a>
                    
                    <a href="#" class="quick-access-item" data-module="members">
                        <div class="access-icon" style="background: #ff9f4320; color: #ff9f43;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <span class="access-label">구성원관리</span>
                    </a>
                    
                    <a href="#" class="quick-access-item" data-module="workflow">
                        <div class="access-icon" style="background: #feca5720; color: #feca57;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="8" y="8" width="8" height="8" rx="2"></rect>
                                <path d="M4 8V6a2 2 0 0 1 2-2h2"></path>
                                <path d="M4 16v2a2 2 0 0 0 2 2h2"></path>
                                <path d="M16 4h2a2 2 0 0 1 2 2v2"></path>
                                <path d="M16 20h2a2 2 0 0 0 2-2v-2"></path>
                            </svg>
                        </div>
                        <span class="access-label">업무관리</span>
                    </a>
                    
                    <a href="#" class="quick-access-item" data-module="management">
                        <div class="access-icon" style="background: #55a3ff20; color: #55a3ff;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14,2 14,8 20,8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </div>
                        <span class="access-label">운영관리</span>
                    </a>
                </div>
            </div>
        </div>
        
    </div>
    
    <!-- 테마 테스트 섹션 -->
    <div class="content-card theme-test">
        <div class="card-header">
            <h2 class="card-title">🎨 통합 테마 시스템 테스트</h2>
            <button class="card-action" onclick="resetTheme()">테마 초기화</button>
        </div>
        <div class="card-content">
            <p class="theme-description">
                BPM 시스템의 동적 테마 전환 기능을 테스트해보세요. 
                각 모듈별로 다른 색상 테마가 헤더, 사이드바, 브레드크럼에 일괄 적용됩니다.
            </p>
            
            <div class="theme-buttons">
                <button class="theme-btn" data-theme="dashboard" style="background: #3742fa;">
                    <span>대시보드</span>
                </button>
                <button class="theme-btn" data-theme="organization" style="background: #ff6b6b;">
                    <span>조직관리</span>
                </button>
                <button class="theme-btn" data-theme="members" style="background: #ff9f43;">
                    <span>구성원관리</span>
                </button>
                <button class="theme-btn" data-theme="workflow" style="background: #feca57; color: #333;">
                    <span>업무관리</span>
                </button>
                <button class="theme-btn" data-theme="management" style="background: #55a3ff;">
                    <span>운영관리</span>
                </button>
            </div>
            
            <div class="theme-controls">
                <button class="control-btn" onclick="toggleDarkMode()">
                    🌙 다크모드 토글
                </button>
                <button class="control-btn" onclick="toggleHighContrast()">
                    🔍 고대비 모드
                </button>
            </div>
            
            <div class="keyboard-shortcuts">
                <h4>키보드 단축키:</h4>
                <ul>
                    <li><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>D</kbd>: 다크모드 토글</li>
                    <li><kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>C</kbd>: 고대비 모드 토글</li>
                </ul>
            </div>
        </div>
    </div>
    
</div>

<style>
/* 대시보드 전용 스타일 */
.dashboard-page .main-content {
    padding-top: 0;
}

.dashboard-container {
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
}

/* 대시보드 헤더 */
.dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 32px;
    padding: 24px 0;
}

.welcome-section h1 {
    font-size: 2rem;
    font-weight: 700;
    color: var(--theme-primary, #3742fa);
    margin: 0 0 8px 0;
}

.welcome-section p {
    color: var(--text-secondary, #64748b);
    font-size: 1rem;
    margin: 0;
}

.quick-actions {
    display: flex;
    gap: 12px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: white;
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    color: var(--text-primary, #1a202c);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quick-action-btn:hover {
    border-color: var(--theme-primary, #3742fa);
    color: var(--theme-primary, #3742fa);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* 통계 그리드 */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid var(--border-color, #f1f5f9);
    transition: all 0.2s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.stat-card[data-module="members"]:hover {
    border-color: #ff9f43;
}

.stat-card[data-module="workflow"]:hover {
    border-color: #feca57;
}

.stat-card[data-module="management"]:hover {
    border-color: #55a3ff;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 16px;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--theme-light, #f0f8ff);
    color: var(--theme-primary, #3742fa);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-content h3 {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--text-primary, #1a202c);
    margin: 0 0 4px 0;
}

.stat-content p {
    color: var(--text-secondary, #64748b);
    font-size: 0.875rem;
    margin: 0 0 8px 0;
}

.stat-change {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 4px;
}

.stat-change.positive {
    background: #dcfce7;
    color: #166534;
}

.stat-change.negative {
    background: #fef2f2;
    color: #dc2626;
}

/* 메인 컨텐츠 그리드 */
.main-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

.content-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    border: 1px solid var(--border-color, #f1f5f9);
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color, #f1f5f9);
}

.card-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary, #1a202c);
    margin: 0;
}

.card-action {
    background: none;
    border: none;
    color: var(--theme-primary, #3742fa);
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background 0.2s ease;
}

.card-action:hover {
    background: var(--theme-light, #f0f8ff);
}

.card-content {
    padding: 24px;
}

/* 활동 목록 */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 8px;
    transition: background 0.2s ease;
}

.activity-item:hover {
    background: var(--bg-secondary, #f8f9fa);
}

.activity-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
    min-width: 0;
}

.activity-text {
    font-size: 0.875rem;
    color: var(--text-primary, #1a202c);
    margin: 0 0 4px 0;
    line-height: 1.4;
}

.activity-action {
    color: var(--theme-primary, #3742fa);
    font-weight: 500;
}

.activity-target {
    color: var(--text-secondary, #64748b);
    font-size: 0.8125rem;
}

.activity-time {
    font-size: 0.75rem;
    color: var(--text-secondary, #64748b);
}

/* 빠른 접근 */
.quick-access-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.quick-access-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px 12px;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.2s ease;
    border: 1px solid var(--border-color, #f1f5f9);
}

.quick-access-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.access-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.access-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary, #1a202c);
}

/* 테마 테스트 */
.theme-test {
    margin-bottom: 32px;
}

.theme-description {
    color: var(--text-secondary, #64748b);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 20px;
}

.theme-buttons {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.theme-btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    color: white;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.theme-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.theme-controls {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.control-btn {
    padding: 8px 16px;
    background: var(--bg-secondary, #f8f9fa);
    border: 1px solid var(--border-color, #e2e8f0);
    border-radius: 6px;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.control-btn:hover {
    background: var(--theme-light, #f0f8ff);
    border-color: var(--theme-primary, #3742fa);
}

.keyboard-shortcuts {
    padding: 16px;
    background: var(--bg-secondary, #f8f9fa);
    border-radius: 8px;
}

.keyboard-shortcuts h4 {
    margin: 0 0 8px 0;
    font-size: 0.875rem;
    color: var(--text-primary, #1a202c);
}

.keyboard-shortcuts ul {
    margin: 0;
    padding: 0;
    list-style: none;
}

.keyboard-shortcuts li {
    font-size: 0.8125rem;
    color: var(--text-secondary, #64748b);
    margin-bottom: 4px;
}

kbd {
    background: var(--text-primary, #1a202c);
    color: white;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.75rem;
    font-family: monospace;
}

/* 반응형 */
@media (max-width: 1023px) {
    .main-content-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 767px) {
    .dashboard-container {
        padding: 16px;
    }
    
    .dashboard-header {
        flex-direction: column;
        gap: 16px;
    }
    
    .quick-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .quick-action-btn {
        flex: 1;
        justify-content: center;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .theme-buttons {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// 대시보드 JavaScript
document.addEventListener("DOMContentLoaded", function() {
    console.log("대시보드 로드 완료");
    
    // 테마 버튼 이벤트
    document.querySelectorAll(".theme-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const theme = this.dataset.theme;
            if (window.switchTheme) {
                window.switchTheme(theme);
                console.log("테마 변경:", theme);
            }
        });
    });
    
    // 빠른 접근 클릭시 테마 변경
    document.querySelectorAll(".quick-access-item, .stat-card").forEach(item => {
        item.addEventListener("click", function(e) {
            e.preventDefault();
            const module = this.dataset.module;
            if (module && window.switchTheme) {
                window.switchTheme(module);
            }
        });
    });
    
    // 빠른 액션 버튼
    document.querySelectorAll(".quick-action-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            const module = this.dataset.module;
            if (module && window.switchTheme) {
                window.switchTheme(module);
            }
            alert(`${module} 모듈로 이동합니다. (데모)`);
        });
    });
    
    // 현재 테마 표시 업데이트
    document.addEventListener("themeChange", function(e) {
        const { newTheme } = e.detail;
        console.log("테마 변경 이벤트 감지:", newTheme);
        
        // 활성 테마 버튼 표시
        document.querySelectorAll(".theme-btn").forEach(btn => {
            btn.style.opacity = btn.dataset.theme === newTheme ? "1" : "0.7";
        });
    });
});

// 전역 함수들
function resetTheme() {
    if (window.resetTheme) {
        window.resetTheme();
    }
}

function toggleDarkMode() {
    if (window.toggleDarkMode) {
        window.toggleDarkMode();
    }
}

function toggleHighContrast() {
    if (window.toggleHighContrast) {
        window.toggleHighContrast();
    }
}
</script>
';

// 레이아웃에 콘텐츠 포함
include __DIR__ . '/views/layouts/main.php';
?>