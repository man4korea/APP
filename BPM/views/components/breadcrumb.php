<!-- 📁 C:\xampp\htdocs\BPM\views\components\breadcrumb.php -->
<!-- Create at 2508031620 Ver1.00 -->

<?php
/**
 * BPM 브레드크럼 네비게이션 컴포넌트
 * 현재 위치를 시각적으로 표시하는 브레드크럼 시스템
 * 모듈: 브레드크럼 네비게이션 (색상: 현재 모듈별 동적)
 */

// 현재 URL 파싱 및 경로 추출
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$currentPath = parse_url($currentPath, PHP_URL_PATH);
$pathSegments = array_filter(explode('/', trim($currentPath, '/')));

// BPM 기본 세그먼트 제거
if (!empty($pathSegments) && $pathSegments[0] === 'BPM') {
    array_shift($pathSegments);
}

// 현재 모듈 및 색상 정보 가져오기
$currentModule = get_current_module();
$moduleInfo = get_module_info($currentModule);
$moduleColor = $moduleInfo['color'] ?? '#3742fa';

// 브레드크럼 아이템 구성
$breadcrumbItems = [];

// 홈 (대시보드) 항상 첫 번째
$breadcrumbItems[] = [
    'title' => '대시보드',
    'icon' => 'home',
    'url' => base_url('dashboard'),
    'active' => empty($pathSegments) || (count($pathSegments) === 1 && $pathSegments[0] === 'dashboard'),
    'color' => '#3742fa'
];

// 모듈별 브레드크럼 매핑
$moduleMapping = [
    'dashboard' => [
        'title' => '대시보드',
        'icon' => 'dashboard',
        'color' => '#3742fa',
        'children' => [
            'overview' => '전체 현황',
            'analytics' => '분석 리포트',
            'notifications' => '알림 센터'
        ]
    ],
    'organization' => [
        'title' => '조직관리',
        'icon' => 'organization',
        'color' => '#ff6b6b',
        'children' => [
            'companies' => '회사 관리',
            'departments' => '부서 관리',
            'hierarchy' => '조직도'
        ]
    ],
    'members' => [
        'title' => '구성원관리',
        'icon' => 'members',
        'color' => '#ff9f43',
        'children' => [
            'users' => '사용자 관리',
            'roles' => '권한 관리',
            'invitations' => '초대 관리'
        ]
    ],
    'workflow' => [
        'title' => '업무 관리',
        'icon' => 'workflow',
        'color' => '#feca57',
        'children' => [
            'tasks' => 'Task 관리',
            'processes' => 'Process Map',
            'flows' => '업무 Flow'
        ]
    ],
    'management' => [
        'title' => '운영 관리',
        'icon' => 'management',
        'color' => '#55a3ff',
        'children' => [
            'documents' => '문서 관리',
            'analytics' => '직무 분석',
            'hr' => '인사 관리'
        ]
    ]
];

// URL 세그먼트 기반으로 브레드크럼 구성
if (!empty($pathSegments)) {
    $currentSegment = $pathSegments[0];
    
    // 메인 모듈 추가
    if (isset($moduleMapping[$currentSegment])) {
        $module = $moduleMapping[$currentSegment];
        $breadcrumbItems[] = [
            'title' => $module['title'],
            'icon' => $module['icon'],
            'url' => base_url($currentSegment),
            'active' => count($pathSegments) === 1,
            'color' => $module['color']
        ];
        
        // 서브 페이지 추가
        if (count($pathSegments) >= 2) {
            $subPage = $pathSegments[1];
            if (isset($module['children'][$subPage])) {
                $breadcrumbItems[] = [
                    'title' => $module['children'][$subPage],
                    'icon' => $subPage,
                    'url' => base_url($currentSegment . '/' . $subPage),
                    'active' => count($pathSegments) === 2,
                    'color' => $module['color']
                ];
                
                // 상세 페이지 추가 (ID 등)
                if (count($pathSegments) >= 3) {
                    $detailPage = $pathSegments[2];
                    $breadcrumbItems[] = [
                        'title' => is_numeric($detailPage) ? '상세보기' : ucfirst($detailPage),
                        'icon' => 'detail',
                        'url' => '',
                        'active' => true,
                        'color' => $module['color']
                    ];
                }
            }
        }
    }
}

// 브레드크럼이 비어있으면 대시보드만 표시
if (empty($breadcrumbItems) || count($breadcrumbItems) === 1) {
    $breadcrumbItems[0]['active'] = true;
}
?>

<nav class="breadcrumb-nav" aria-label="브레드크럼 네비게이션" data-module="<?= $currentModule ?>">
    <div class="breadcrumb-container">
        
        <!-- 브레드크럼 리스트 -->
        <ol class="breadcrumb-list" itemscope itemtype="http://schema.org/BreadcrumbList">
            
            <?php foreach ($breadcrumbItems as $index => $item): ?>
            <li class="breadcrumb-item <?= $item['active'] ? 'active' : '' ?>" 
                itemscope itemprop="itemListElement" itemtype="http://schema.org/ListItem"
                data-color="<?= $item['color'] ?>">
                
                <?php if (!$item['active'] && !empty($item['url'])): ?>
                <!-- 링크 가능한 브레드크럼 -->
                <a href="<?= $item['url'] ?>" 
                   class="breadcrumb-link"
                   itemprop="item"
                   title="<?= $item['title'] ?>"
                   style="color: <?= $item['color'] ?>">
                   
                    <!-- 아이콘 -->
                    <span class="breadcrumb-icon" style="color: <?= $item['color'] ?>">
                        <?= render_breadcrumb_icon($item['icon']) ?>
                    </span>
                    
                    <!-- 텍스트 -->
                    <span class="breadcrumb-text" itemprop="name"><?= $item['title'] ?></span>
                </a>
                
                <?php else: ?>
                <!-- 현재 페이지 (링크 없음) -->
                <span class="breadcrumb-current" 
                      itemprop="item"
                      style="color: <?= $item['color'] ?>">
                      
                    <!-- 아이콘 -->
                    <span class="breadcrumb-icon" style="color: <?= $item['color'] ?>">
                        <?= render_breadcrumb_icon($item['icon']) ?>
                    </span>
                    
                    <!-- 텍스트 -->
                    <span class="breadcrumb-text" itemprop="name"><?= $item['title'] ?></span>
                </span>
                
                <?php endif; ?>
                
                <!-- 구조화 데이터 -->
                <meta itemprop="position" content="<?= $index + 1 ?>">
                
                <!-- 구분자 (마지막 아이템 제외) -->
                <?php if ($index < count($breadcrumbItems) - 1): ?>
                <span class="breadcrumb-separator" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </span>
                <?php endif; ?>
                
            </li>
            <?php endforeach; ?>
            
        </ol>
        
        <!-- 추가 액션 (옵션) -->
        <div class="breadcrumb-actions">
            <!-- 새로고침 버튼 -->
            <button type="button" 
                    class="breadcrumb-action-btn" 
                    onclick="location.reload()" 
                    title="페이지 새로고침"
                    aria-label="페이지 새로고침">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="23,4 23,10 17,10"></polyline>
                    <polyline points="1,20 1,14 7,14"></polyline>
                    <path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path>
                </svg>
            </button>
            
            <!-- 즐겨찾기 버튼 (향후 확장) -->
            <button type="button" 
                    class="breadcrumb-action-btn" 
                    id="bookmark-toggle"
                    title="즐겨찾기 추가/제거"
                    aria-label="즐겨찾기">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"></polygon>
                </svg>
            </button>
        </div>
        
    </div>
</nav>

<!-- 브레드크럼 전용 JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeBreadcrumb();
});

function initializeBreadcrumb() {
    const breadcrumbNav = document.querySelector('.breadcrumb-nav');
    const bookmarkBtn = document.getElementById('bookmark-toggle');
    
    // 현재 모듈 색상으로 브레드크럼 하이라이트
    if (breadcrumbNav) {
        const currentModule = breadcrumbNav.dataset.module;
        updateBreadcrumbTheme(currentModule);
    }
    
    // 즐겨찾기 토글 기능 (향후 확장)
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            // 실제 즐겨찾기 로직은 향후 구현
            console.log('브레드크럼 즐겨찾기 토글');
        });
    }
    
    // 브레드크럼 항목 호버 효과
    const breadcrumbLinks = document.querySelectorAll('.breadcrumb-link');
    breadcrumbLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const color = this.parentElement.dataset.color;
            this.style.backgroundColor = color + '10'; // 투명도 추가
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
}

// 브레드크럼 테마 업데이트 함수
function updateBreadcrumbTheme(module) {
    const moduleColors = {
        'dashboard': '#3742fa',
        'organization': '#ff6b6b',
        'members': '#ff9f43',
        'workflow': '#feca57',
        'management': '#55a3ff'
    };
    
    const color = moduleColors[module] || '#3742fa';
    document.documentElement.style.setProperty('--breadcrumb-primary-color', color);
}
</script>

<?php
/**
 * 브레드크럼 아이콘 렌더링 헬퍼
 */
function render_breadcrumb_icon($iconName, $size = 16) {
    $icons = [
        'home' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9,22 9,12 15,12 15,22"></polyline></svg>',
        'dashboard' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>',
        'organization' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path></svg>',
        'members' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'workflow' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="8" width="8" height="8" rx="2"></rect><path d="M4 8V6a2 2 0 0 1 2-2h2"></path><path d="M4 16v2a2 2 0 0 0 2 2h2"></path><path d="M16 4h2a2 2 0 0 1 2 2v2"></path><path d="M16 20h2a2 2 0 0 0 2-2v-2"></path></svg>',
        'management' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        
        // 서브페이지 아이콘들
        'overview' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline></svg>',
        'analytics' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
        'notifications' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path></svg>',
        'companies' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path><path d="M6 12H4a2 2 0 0 0-2 2v8h20v-8a2 2 0 0 0-2-2h-2"></path></svg>',
        'departments' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8"></path><path d="M12 17v4"></path><path d="M3 13h18l-2-8H5l-2 8Z"></path><circle cx="18" cy="4" r="2"></circle><circle cx="6" cy="4" r="2"></circle><circle cx="12" cy="4" r="2"></circle></svg>',
        'hierarchy' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="5,12 19,12"></polyline><polyline points="12,5 12,19"></polyline><polyline points="5,12 12,5"></polyline><polyline points="12,19 19,12"></polyline></svg>',
        'users' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
        'roles' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>',
        'invitations' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>',
        'tasks' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        'processes' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 1v6m0 4v6"></path><path d="M20.2 7.8l-5.7 5.7-2.8-2.8-5.7 5.7"></path></svg>',
        'flows' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5,4.21 12,6.81 16.5,4.21"></polyline><polyline points="7.5,19.79 7.5,14.6 3,12"></polyline><polyline points="21,12 16.5,14.6 16.5,19.79"></polyline><polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>',
        'documents' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14,2 14,8 20,8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10,9 9,9 8,9"></polyline></svg>',
        'hr' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
        'detail' => '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    ];
    
    return $icons[$iconName] ?? '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>';
}
?>