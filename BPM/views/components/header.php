<!-- 📁 C:\xampp\htdocs\BPM\views\components\header.php -->
<!-- Create at 2508031547 Ver1.00 -->

<?php
/**
 * BPM 메인 헤더 컴포넌트
 * EASYCORP 브랜딩 및 네비게이션
 */

// 현재 사용자 정보 가져오기 (세션에서)
$currentUser = current_user();
$isLoggedIn = !empty($currentUser);

// 현재 모듈 감지 (URL 기반)
$currentModule = get_current_module();
$moduleData = get_module_info($currentModule);
?>

<header id="main-header" class="main-header" data-module="<?= $currentModule ?>">
    <div class="header-container">
        
        <!-- 좌측 브랜딩 영역 -->
        <div class="header-left">
            <!-- 사이드바 토글 버튼 (모바일) -->
            <button 
                type="button" 
                class="sidebar-toggle"
                id="sidebar-toggle"
                aria-label="메뉴 토글"
                title="메뉴 열기/닫기"
            >
                <svg class="hamburger-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>

            <!-- EASYCORP 로고 및 제품명 -->
            <div class="brand-section">
                <a href="<?= base_url() ?>" class="brand-link" title="EASYCORP BPM 홈">
                    <!-- EASYCORP 로고 -->
                    <div class="logo-container">
                        <svg class="logo-svg" width="32" height="32" viewBox="0 0 100 100" fill="none">
                            <!-- E자 형태의 아티스틱 로고 -->
                            <rect x="20" y="20" width="60" height="8" fill="currentColor" rx="4"/>
                            <rect x="20" y="46" width="45" height="8" fill="currentColor" rx="4"/>
                            <rect x="20" y="72" width="60" height="8" fill="currentColor" rx="4"/>
                            <rect x="20" y="20" width="8" height="60" fill="currentColor" rx="4"/>
                        </svg>
                    </div>

                    <!-- 브랜드 텍스트 -->
                    <div class="brand-text">
                        <div class="brand-name">EASYCORP</div>
                        <div class="product-name">Business Process Management</div>
                    </div>
                </a>
            </div>

            <!-- 현재 모듈 표시 -->
            <?php if ($moduleData): ?>
            <div class="current-module-indicator">
                <span class="module-dot" style="background-color: <?= $moduleData['color'] ?>"></span>
                <span class="module-name"><?= $moduleData['name'] ?></span>
            </div>
            <?php endif; ?>
        </div>

        <!-- 우측 기능 영역 -->
        <div class="header-right">
            
            <!-- 검색 버튼 (확장 가능) -->
            <button 
                type="button" 
                class="header-action-btn search-btn"
                id="global-search-btn"
                aria-label="전체 검색"
                title="전체 검색"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>

            <!-- 알림 버튼 -->
            <button 
                type="button" 
                class="header-action-btn notification-btn"
                id="notification-btn"
                aria-label="알림"
                title="알림"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                </svg>
                <!-- 알림 배지 (알림이 있을 때만 표시) -->
                <span class="notification-badge" id="notification-badge" style="display: none;">3</span>
            </button>

            <!-- 설정 버튼 -->
            <button 
                type="button" 
                class="header-action-btn settings-btn"
                id="settings-btn"
                aria-label="시스템 설정"
                title="시스템 설정"
            >
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </button>

            <?php if ($isLoggedIn): ?>
                <!-- 로그인 사용자 메뉴 -->
                <div class="user-menu-container">
                    <button 
                        type="button" 
                        class="user-menu-trigger"
                        id="user-menu-trigger"
                        aria-label="사용자 메뉴"
                        aria-expanded="false"
                    >
                        <!-- 사용자 아바타 -->
                        <div class="user-avatar">
                            <?php if (!empty($currentUser['avatar'])): ?>
                                <img 
                                    src="<?= $currentUser['avatar'] ?>" 
                                    alt="<?= htmlspecialchars($currentUser['name']) ?>"
                                    class="avatar-img"
                                >
                            <?php else: ?>
                                <span class="avatar-text">
                                    <?= strtoupper(mb_substr($currentUser['name'] ?? 'U', 0, 1)) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- 사용자 정보 (데스크톱용) -->
                        <div class="user-info">
                            <div class="user-name"><?= htmlspecialchars($currentUser['name'] ?? '사용자') ?></div>
                            <div class="user-role"><?= htmlspecialchars($currentUser['role_name'] ?? '구성원') ?></div>
                        </div>

                        <!-- 드롭다운 아이콘 -->
                        <svg class="dropdown-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6,9 12,15 18,9"></polyline>
                        </svg>
                    </button>

                    <!-- 사용자 드롭다운 메뉴 -->
                    <div class="user-dropdown-menu" id="user-dropdown-menu">
                        <div class="dropdown-header">
                            <div class="user-full-info">
                                <div class="user-name"><?= htmlspecialchars($currentUser['name'] ?? '사용자') ?></div>
                                <div class="user-email"><?= htmlspecialchars($currentUser['email'] ?? '') ?></div>
                                <div class="user-company"><?= htmlspecialchars($currentUser['company_name'] ?? '') ?></div>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>

                        <div class="dropdown-items">
                            <a href="<?= base_url('profile') ?>" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                프로필 관리
                            </a>

                            <a href="<?= base_url('preferences') ?>" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 1v6m0 4v6"></path>
                                    <path d="M2 8h20"></path>
                                    <path d="M2 16h20"></path>
                                    <circle cx="12" cy="8" r="2"></circle>
                                    <circle cx="12" cy="16" r="2"></circle>
                                </svg>
                                환경설정
                            </a>

                            <div class="dropdown-divider"></div>

                            <button type="button" class="dropdown-item logout-btn" id="logout-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                    <polyline points="16,17 21,12 16,7"></polyline>
                                    <line x1="21" y1="12" x2="9" y2="12"></line>
                                </svg>
                                로그아웃
                            </button>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- 로그인 버튼 -->
                <a href="<?= base_url('login') ?>" class="login-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10,17 15,12 10,7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    <span>로그인</span>
                </a>
            <?php endif; ?>

        </div>
    </div>

    <!-- 검색 오버레이 (확장시 표시) -->
    <div class="search-overlay" id="search-overlay">
        <div class="search-container">
            <input 
                type="text" 
                class="search-input" 
                placeholder="전체 검색..."
                id="global-search-input"
            >
            <button type="button" class="search-close-btn" id="search-close-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
</header>

<!-- 헤더 전용 JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 헤더 기능 초기화
    initializeHeader();
});

function initializeHeader() {
    // 사이드바 토글
    const sidebarToggle = document.getElementById('sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    // 검색 기능
    const searchBtn = document.getElementById('global-search-btn');
    const searchOverlay = document.getElementById('search-overlay');
    const searchCloseBtn = document.getElementById('search-close-btn');
    const searchInput = document.getElementById('global-search-input');

    if (searchBtn && searchOverlay) {
        searchBtn.addEventListener('click', function() {
            searchOverlay.classList.add('active');
            searchInput.focus();
        });

        searchCloseBtn.addEventListener('click', function() {
            searchOverlay.classList.remove('active');
            searchInput.value = '';
        });

        // ESC 키로 검색 닫기
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && searchOverlay.classList.contains('active')) {
                searchOverlay.classList.remove('active');
                searchInput.value = '';
            }
        });
    }

    // 사용자 메뉴 드롭다운
    const userMenuTrigger = document.getElementById('user-menu-trigger');
    const userDropdownMenu = document.getElementById('user-dropdown-menu');

    if (userMenuTrigger && userDropdownMenu) {
        userMenuTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            
            this.setAttribute('aria-expanded', !isExpanded);
            userDropdownMenu.classList.toggle('show', !isExpanded);
        });

        // 외부 클릭시 메뉴 닫기
        document.addEventListener('click', function() {
            userMenuTrigger.setAttribute('aria-expanded', 'false');
            userDropdownMenu.classList.remove('show');
        });
    }

    // 로그아웃 처리
    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('정말 로그아웃하시겠습니까?')) {
                window.location.href = '<?= base_url('logout') ?>';
            }
        });
    }

    // 설정 버튼
    const settingsBtn = document.getElementById('settings-btn');
    if (settingsBtn) {
        settingsBtn.addEventListener('click', function() {
            window.location.href = '<?= base_url('settings') ?>';
        });
    }

    // 알림 버튼
    const notificationBtn = document.getElementById('notification-btn');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function() {
            // TODO: 알림 패널 토글 구현
            console.log('알림 기능은 추후 구현됩니다.');
        });
    }
}
</script>