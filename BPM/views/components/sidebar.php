<!-- 📁 C:\xampp\htdocs\BPM\views\components\sidebar.php -->
<!-- Create at 2508031600 Ver1.00 -->

<?php
/**
 * BPM 동적 사이드바 네비게이션 컴포넌트
 * 2단계 메뉴구조: 5개 메인 메뉴, 각 3개 서브메뉴
 * 4단계 권한 시스템 통합 (Founder/Admin/Process_Owner/Member)
 */

use BPM\Core\Permission;
use BPM\Core\Auth;

// 현재 사용자 및 권한 확인
$auth = Auth::getInstance();
$permission = Permission::getInstance();
$currentUser = $auth->getCurrentUser();
$currentCompanyId = $_SESSION['company_id'] ?? null;

if (!$currentUser || !$currentCompanyId) {
    // 인증되지 않은 경우 로그인 페이지로 리다이렉트
    header('Location: ' . base_url('login'));
    exit;
}

// 현재 모듈 및 페이지 감지
$currentModule = get_current_module();
$currentPage = get_current_page();

// 사이드바 메뉴 구조 정의 (4단계 권한 시스템 기반)
$sidebarMenus = [
    'dashboard' => [
        'title' => '대시보드',
        'icon' => 'dashboard',
        'color' => '#3742fa',
        'url' => base_url('dashboard'),
        'module' => 'dashboard',
        'required_action' => 'view',
        'children' => [
            'overview' => [
                'title' => '전체 현황',
                'url' => base_url('dashboard/overview'),
                'icon' => 'overview',
                'module' => 'dashboard',
                'required_action' => 'view'
            ],
            'analytics' => [
                'title' => '분석 리포트',
                'url' => base_url('dashboard/analytics'),
                'icon' => 'chart',
                'module' => 'tasks',
                'required_action' => 'view'
            ],
            'notifications' => [
                'title' => '알림 센터',
                'url' => base_url('dashboard/notifications'),
                'icon' => 'bell',
                'module' => 'dashboard',
                'required_action' => 'view'
            ]
        ]
    ],
    
    'organization' => [
        'title' => '조직관리',
        'icon' => 'organization',
        'color' => '#ff6b6b',
        'url' => base_url('organization'),
        'module' => 'organization',
        'required_action' => 'view',
        'children' => [
            'companies' => [
                'title' => '회사 관리',
                'url' => base_url('organization/companies'),
                'icon' => 'building',
                'module' => 'organization',
                'required_action' => 'edit'
            ],
            'departments' => [
                'title' => '부서 관리',
                'url' => base_url('organization/departments'),
                'icon' => 'department',
                'module' => 'organization',
                'required_action' => 'edit'
            ],
            'hierarchy' => [
                'title' => '조직도',
                'url' => base_url('organization/hierarchy'),
                'icon' => 'hierarchy',
                'module' => 'organization',
                'required_action' => 'view'
            ]
        ]
    ],
    
    'members' => [
        'title' => '구성원관리',
        'icon' => 'members',
        'color' => '#ff9f43',
        'url' => base_url('members'),
        'module' => 'members',
        'required_action' => 'view',
        'children' => [
            'users' => [
                'title' => '사용자 관리',
                'url' => base_url('members/users'),
                'icon' => 'users',
                'module' => 'members',
                'required_action' => 'edit'
            ],
            'roles' => [
                'title' => '권한 관리',
                'url' => base_url('members/roles'),
                'icon' => 'shield',
                'module' => 'members',
                'required_action' => 'edit'
            ],
            'invitations' => [
                'title' => '초대 관리',
                'url' => base_url('members/invitations'),
                'icon' => 'mail',
                'module' => 'members',
                'required_action' => 'create'
            ]
        ]
    ],
    
    'workflow' => [
        'title' => '업무 관리',
        'icon' => 'workflow',
        'color' => '#feca57',
        'url' => base_url('workflow'),
        'module' => 'tasks',
        'required_action' => 'view',
        'children' => [
            'tasks' => [
                'title' => 'Task 관리',
                'url' => base_url('workflow/tasks'),
                'icon' => 'tasks',
                'color' => '#feca57',
                'module' => 'tasks',
                'required_action' => 'view'
            ],
            'processes' => [
                'title' => 'Process Map',
                'url' => base_url('workflow/processes'),
                'icon' => 'process',
                'color' => '#3742fa',
                'module' => 'process_map',
                'required_action' => 'view'
            ],
            'flows' => [
                'title' => '업무 Flow',
                'url' => base_url('workflow/flows'),
                'icon' => 'flow',
                'color' => '#a55eea',
                'module' => 'workflow',
                'required_action' => 'view'
            ]
        ]
    ],
    
    'management' => [
        'title' => '운영 관리',
        'icon' => 'management',
        'color' => '#55a3ff',
        'url' => base_url('management'),
        'module' => 'documents',
        'required_action' => 'view',
        'children' => [
            'documents' => [
                'title' => '문서 관리',
                'url' => base_url('management/documents'),
                'icon' => 'documents',
                'color' => '#55a3ff',
                'module' => 'documents',
                'required_action' => 'view'
            ],
            'analytics' => [
                'title' => 'AI 직무기술서',
                'url' => base_url('job-description.php'),
                'icon' => 'ai_robot',
                'color' => '#8b4513',
                'module' => 'job_analysis',
                'required_action' => 'view'
            ],
            'hr' => [
                'title' => '인사 관리',
                'url' => base_url('management/hr'),
                'icon' => 'hr',
                'color' => '#f1f2f6',
                'module' => 'hr',
                'required_action' => 'view'
            ]
        ]
    ]
];

// 권한에 따른 메뉴 필터링 (Permission 클래스 사용)
$filteredMenus = $permission->filterMenuItems($sidebarMenus, $currentUser['id'], $currentCompanyId);

// 사용자 권한 정보 조회 (디버그 및 UI 표시용)
$userRole = $permission->getUserRole($currentUser['id'], $currentCompanyId);
$userRoleDisplay = Permission::getRoleDisplayName($userRole);
$userLevel = $permission->getUserRoleLevel($currentUser['id'], $currentCompanyId);

// 사이드바 상태 (접힘/펼침) 감지
$sidebarCollapsed = $_COOKIE['sidebar_collapsed'] ?? 'false';
?>

<aside id="main-sidebar" class="main-sidebar <?= $sidebarCollapsed === 'true' ? 'collapsed' : '' ?>" data-current-module="<?= $currentModule ?>">
    
    <!-- 사이드바 헤더 -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <svg width="24" height="24" viewBox="0 0 100 100" fill="none">
                    <!-- 간소화된 EASYCORP 로고 -->
                    <rect x="20" y="30" width="60" height="6" fill="currentColor" rx="3"/>
                    <rect x="20" y="47" width="45" height="6" fill="currentColor" rx="3"/>
                    <rect x="20" y="64" width="60" height="6" fill="currentColor" rx="3"/>
                    <rect x="20" y="30" width="6" height="40" fill="currentColor" rx="3"/>
                </svg>
            </div>
            <div class="logo-text">
                <div class="brand-mini">EC</div>
                <div class="brand-full">EASYCORP</div>
            </div>
        </div>
        
        <!-- 사이드바 토글 버튼 -->
        <button type="button" class="sidebar-toggle-btn" id="sidebar-toggle-main" aria-label="사이드바 토글">
            <svg class="toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="9" y1="9" x2="9" y2="15"></line>
            </svg>
        </button>
    </div>

    <!-- 메인 네비게이션 -->
    <nav class="sidebar-nav" role="navigation" aria-label="메인 네비게이션">
        <ul class="nav-menu">
            
            <?php foreach ($filteredMenus as $menuKey => $menu): ?>
            <li class="nav-item <?= $currentModule === $menuKey ? 'active' : '' ?>" data-module="<?= $menuKey ?>">
                
                <!-- 메인 메뉴 링크 -->
                <a href="<?= $menu['url'] ?>" 
                   class="nav-link main-nav-link" 
                   data-module="<?= $menuKey ?>"
                   data-color="<?= $menu['color'] ?>"
                   title="<?= $menu['title'] ?>">
                   
                    <!-- 아이콘 -->
                    <span class="nav-icon" style="color: <?= $menu['color'] ?>">
                        <?= render_nav_icon($menu['icon']) ?>
                    </span>
                    
                    <!-- 텍스트 -->
                    <span class="nav-text"><?= $menu['title'] ?></span>
                    
                    <!-- 서브메뉴 화살표 -->
                    <?php if (!empty($menu['children'])): ?>
                    <span class="nav-arrow">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </span>
                    <?php endif; ?>
                    
                    <!-- 활성 표시기 -->
                    <span class="nav-indicator" style="background-color: <?= $menu['color'] ?>"></span>
                </a>
                
                <!-- 서브메뉴 -->
                <?php if (!empty($menu['children'])): ?>
                <ul class="sub-menu" data-parent="<?= $menuKey ?>">
                    <?php foreach ($menu['children'] as $subKey => $subMenu): ?>
                    <li class="sub-item <?= $currentPage === $subKey ? 'active' : '' ?>">
                        <a href="<?= $subMenu['url'] ?>" 
                           class="sub-link"
                           data-page="<?= $subKey ?>"
                           title="<?= $subMenu['title'] ?>">
                           
                            <!-- 서브메뉴 아이콘 -->
                            <span class="sub-icon" style="color: <?= $subMenu['color'] ?? $menu['color'] ?>">
                                <?= render_nav_icon($subMenu['icon'], 14) ?>
                            </span>
                            
                            <!-- 서브메뉴 텍스트 -->
                            <span class="sub-text"><?= $subMenu['title'] ?></span>
                            
                            <!-- 서브메뉴 활성 표시기 -->
                            <span class="sub-indicator" style="background-color: <?= $subMenu['color'] ?? $menu['color'] ?>"></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
            </li>
            <?php endforeach; ?>
            
        </ul>
    </nav>

    <!-- 사이드바 푸터 -->
    <div class="sidebar-footer">
        <!-- 설정 바로가기 -->
        <a href="<?= base_url('settings') ?>" class="footer-link" title="시스템 설정">
            <span class="footer-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </span>
            <span class="footer-text">설정</span>
        </a>
        
        <!-- 도움말 바로가기 -->
        <a href="<?= base_url('help') ?>" class="footer-link" title="도움말">
            <span class="footer-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </span>
            <span class="footer-text">도움말</span>
        </a>
    </div>

</aside>

<!-- 사이드바 오버레이 (모바일용) -->
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<!-- 사이드바 전용 JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
});

function initializeSidebar() {
    const sidebar = document.getElementById('main-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const toggleBtn = document.getElementById('sidebar-toggle-main');
    const headerToggleBtn = document.getElementById('sidebar-toggle');
    
    // 사이드바 토글 기능
    function toggleSidebar() {
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
            setCookie('sidebar_collapsed', 'false', 30);
        } else {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
            setCookie('sidebar_collapsed', 'true', 30);
        }
        
        // 모바일에서 사이드바 열 때 오버레이 표시
        if (window.innerWidth <= 768) {
            if (!isCollapsed) {
                overlay.classList.add('active');
                document.body.classList.add('sidebar-open');
            }
        }
    }
    
    // 토글 버튼 이벤트
    if (toggleBtn) {
        toggleBtn.addEventListener('click', toggleSidebar);
    }
    
    if (headerToggleBtn) {
        headerToggleBtn.addEventListener('click', toggleSidebar);
    }
    
    // 오버레이 클릭시 사이드바 닫기
    if (overlay) {
        overlay.addEventListener('click', function() {
            sidebar.classList.add('collapsed');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            document.body.classList.add('sidebar-collapsed');
        });
    }
    
    // 서브메뉴 토글
    const mainNavLinks = document.querySelectorAll('.main-nav-link');
    mainNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const parentItem = this.closest('.nav-item');
            const subMenu = parentItem.querySelector('.sub-menu');
            
            if (subMenu) {
                e.preventDefault();
                
                // 다른 열린 메뉴들 닫기
                document.querySelectorAll('.nav-item.expanded').forEach(item => {
                    if (item !== parentItem) {
                        item.classList.remove('expanded');
                    }
                });
                
                // 현재 메뉴 토글
                parentItem.classList.toggle('expanded');
            }
        });
    });
    
    // 현재 활성 메뉴의 부모 메뉴 자동 펼치기
    const activeItem = document.querySelector('.nav-item.active');
    if (activeItem) {
        activeItem.classList.add('expanded');
    }
    
    // 활성 서브메뉴의 부모 메뉴 펼치기
    const activeSubItem = document.querySelector('.sub-item.active');
    if (activeSubItem) {
        const parentNavItem = activeSubItem.closest('.nav-item');
        if (parentNavItem) {
            parentNavItem.classList.add('expanded');
        }
    }
    
    // 반응형 처리
    function handleResize() {
        if (window.innerWidth > 768) {
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    }
    
    window.addEventListener('resize', handleResize);
    
    // 쿠키 설정 헬퍼
    function setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
    }
}
</script>

<?php
/**
 * 네비게이션 아이콘 렌더링 헬퍼
 */
function render_nav_icon($iconName, $size = 20) {
    $icons = [
        'dashboard' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>',
        'organization' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path></svg>',
        'members' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'workflow' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="8" height="8" rx="2"></rect><path d="M4 8V6a2 2 0 0 1 2-2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M16 4h2a2 2 0 0 1 2 2v2"></path><path d="M16 20h2a2 2 0 0 0 2-2v-2"></path></svg>',
        'management' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        
        // 서브메뉴 아이콘들
        'overview' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline></svg>',
        'chart' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
        'bell' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>',
        'building' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2"></path></svg>',
        'department' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8"></path><path d="M12 17v4"></path><path d="M3 13h18l-2-8H5l-2 8Z"></path><circle cx="18" cy="4" r="2"></circle><circle cx="6" cy="4" r="2"></circle><circle cx="12" cy="4" r="2"></circle></svg>',
        'hierarchy' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="5,12 19,12"></polyline><polyline points="12,5 12,19"></polyline><polyline points="5,12 12,5"></polyline><polyline points="12,19 19,12"></polyline></svg>',
        'users' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'shield' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'mail' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'tasks' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        'process' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 4v6"></path><path d="M20.2 7.8l-5.7 5.7-2.8-2.8-5.7 5.7"></path></svg>',
        'flow' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5,4.21 12,6.81 16.5,4.21"></polyline><polyline points="7.5,19.79 7.5,14.6 3,12"></polyline><polyline points="21,12 16.5,14.6 16.5,19.79"></polyline><polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'documents' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        'analytics' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
        'ai_robot' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="10" rx="2" ry="2"></rect><circle cx="12" cy="5" r="2"></circle><path d="M12 7v4"></path><line x1="8" y1="16" x2="8" y2="16"></line><line x1="16" y1="16" x2="16" y2="16"></line><path d="M1 16h6m10 0h6"></path></svg>',
        'hr' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>'
    ];
    
    return $icons[$iconName] ?? '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>';
}

/**
 * 현재 페이지 감지 헬퍼
 */
function get_current_page() {
    $path = $_SERVER['REQUEST_URI'] ?? '';
    $path = parse_url($path, PHP_URL_PATH);
    $segments = array_filter(explode('/', $path));
    
    // URL에서 페이지명 추출 (예: /BPM/organization/companies -> companies)
    if (count($segments) >= 3) {
        return $segments[2];
    }
    
    return '';
}
?>