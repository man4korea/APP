// 📁 C:\xampp\htdocs\BPM\assets\js\breadcrumb.js
// Create at 2508031630 Ver1.00

/**
 * BPM 브레드크럼 네비게이션 JavaScript
 * 동적 업데이트 및 인터랙션 관리
 * 모듈: 브레드크럼 네비게이션 (색상: 동적 모듈별)
 */

class BreadcrumbManager {
    constructor() {
        this.breadcrumbNav = null;
        this.breadcrumbList = null;
        this.bookmarkBtn = null;
        this.currentModule = '';
        this.moduleColors = {
            'dashboard': '#3742fa',
            'organization': '#ff6b6b',
            'members': '#ff9f43',
            'workflow': '#feca57',
            'management': '#55a3ff'
        };
        
        this.init();
    }
    
    /**
     * 브레드크럼 매니저 초기화
     */
    init() {
        // DOM 요소 캐싱
        this.breadcrumbNav = document.querySelector('.breadcrumb-nav');
        this.breadcrumbList = document.querySelector('.breadcrumb-list');
        this.bookmarkBtn = document.getElementById('bookmark-toggle');
        
        if (!this.breadcrumbNav) {
            console.warn('브레드크럼 네비게이션을 찾을 수 없습니다.');
            return;
        }
        
        // 현재 모듈 정보 가져오기
        this.currentModule = this.breadcrumbNav.dataset.module || 'dashboard';
        
        // 이벤트 리스너 등록
        this.bindEvents();
        
        // 초기 테마 설정
        this.updateTheme(this.currentModule);
        
        // 브레드크럼 상태 복원
        this.restoreState();
        
        console.log('브레드크럼 매니저 초기화 완료:', this.currentModule);
    }
    
    /**
     * 이벤트 리스너 바인딩
     */
    bindEvents() {
        // 즐겨찾기 토글
        if (this.bookmarkBtn) {
            this.bookmarkBtn.addEventListener('click', this.toggleBookmark.bind(this));
        }
        
        // 브레드크럼 링크 호버 효과
        this.bindHoverEffects();
        
        // 키보드 네비게이션
        this.bindKeyboardNavigation();
        
        // 윈도우 리사이즈
        window.addEventListener('resize', this.handleResize.bind(this));
        
        // 브라우저 뒤로가기/앞으로가기 감지
        window.addEventListener('popstate', this.handlePopState.bind(this));
    }
    
    /**
     * 호버 효과 바인딩
     */
    bindHoverEffects() {
        const breadcrumbLinks = document.querySelectorAll('.breadcrumb-link');
        
        breadcrumbLinks.forEach(link => {
            link.addEventListener('mouseenter', (e) => {
                const color = e.target.closest('.breadcrumb-item').dataset.color;
                if (color) {
                    e.target.style.backgroundColor = this.hexToRgba(color, 0.1);
                    e.target.style.borderColor = color;
                }
            });
            
            link.addEventListener('mouseleave', (e) => {
                e.target.style.backgroundColor = '';
                e.target.style.borderColor = '';
            });
        });
    }
    
    /**
     * 키보드 네비게이션 바인딩
     */
    bindKeyboardNavigation() {
        this.breadcrumbNav.addEventListener('keydown', (e) => {
            const focusableElements = this.breadcrumbNav.querySelectorAll(
                '.breadcrumb-link, .breadcrumb-action-btn'
            );
            const currentIndex = Array.from(focusableElements).indexOf(e.target);
            
            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    if (currentIndex > 0) {
                        focusableElements[currentIndex - 1].focus();
                    }
                    break;
                    
                case 'ArrowRight':
                    e.preventDefault();
                    if (currentIndex < focusableElements.length - 1) {
                        focusableElements[currentIndex + 1].focus();
                    }
                    break;
                    
                case 'Home':
                    e.preventDefault();
                    focusableElements[0].focus();
                    break;
                    
                case 'End':
                    e.preventDefault();
                    focusableElements[focusableElements.length - 1].focus();
                    break;
            }
        });
    }
    
    /**
     * 즐겨찾기 토글
     */
    toggleBookmark() {
        const isActive = this.bookmarkBtn.classList.contains('active');
        
        if (isActive) {
            this.bookmarkBtn.classList.remove('active');
            this.removeBookmark();
        } else {
            this.bookmarkBtn.classList.add('active');
            this.addBookmark();
        }
        
        // 상태 저장
        this.saveState();
        
        // 피드백 애니메이션
        this.animateBookmarkToggle();
    }
    
    /**
     * 즐겨찾기 추가
     */
    addBookmark() {
        const currentPath = window.location.pathname;
        const currentTitle = this.getCurrentPageTitle();
        
        // 로컬 스토리지에 즐겨찾기 저장
        const bookmarks = this.getBookmarks();
        const bookmarkData = {
            path: currentPath,
            title: currentTitle,
            module: this.currentModule,
            timestamp: Date.now()
        };
        
        // 중복 제거
        const existingIndex = bookmarks.findIndex(b => b.path === currentPath);
        if (existingIndex >= 0) {
            bookmarks[existingIndex] = bookmarkData;
        } else {
            bookmarks.push(bookmarkData);
        }
        
        localStorage.setItem('bpm_bookmarks', JSON.stringify(bookmarks));
        
        console.log('즐겨찾기 추가됨:', bookmarkData);
        
        // 사용자 알림 (향후 토스트 시스템과 연동)
        this.showNotification('즐겨찾기에 추가되었습니다.', 'success');
    }
    
    /**
     * 즐겨찾기 제거
     */
    removeBookmark() {
        const currentPath = window.location.pathname;
        const bookmarks = this.getBookmarks();
        
        const filteredBookmarks = bookmarks.filter(b => b.path !== currentPath);
        localStorage.setItem('bmp_bookmarks', JSON.stringify(filteredBookmarks));
        
        console.log('즐겨찾기 제거됨:', currentPath);
        
        // 사용자 알림
        this.showNotification('즐겨찾기에서 제거되었습니다.', 'info');
    }
    
    /**
     * 즐겨찾기 목록 가져오기
     */
    getBookmarks() {
        try {
            const bookmarks = localStorage.getItem('bpm_bookmarks');
            return bookmarks ? JSON.parse(bookmarks) : [];
        } catch (error) {
            console.error('즐겨찾기 데이터 파싱 오류:', error);
            return [];
        }
    }
    
    /**
     * 현재 페이지 제목 가져오기
     */
    getCurrentPageTitle() {
        const activeItem = document.querySelector('.breadcrumb-item.active .breadcrumb-text');
        return activeItem ? activeItem.textContent.trim() : document.title;
    }
    
    /**
     * 테마 업데이트
     */
    updateTheme(module) {
        const color = this.moduleColors[module] || this.moduleColors.dashboard;
        
        // CSS 변수 업데이트
        document.documentElement.style.setProperty('--breadcrumb-primary-color', color);
        
        // 브레드크럼 네비게이션 데이터 속성 업데이트
        if (this.breadcrumbNav) {
            this.breadcrumbNav.dataset.module = module;
        }
        
        console.log(`브레드크럼 테마 업데이트: ${module} (${color})`);
    }
    
    /**
     * 브레드크럼 동적 업데이트
     */
    updateBreadcrumb(pathSegments, moduleData) {
        if (!this.breadcrumbList) return;
        
        // 기존 브레드크럼 클리어
        this.breadcrumbList.innerHTML = '';
        
        // 새 브레드크럼 아이템 생성
        pathSegments.forEach((segment, index) => {
            const isLast = index === pathSegments.length - 1;
            const listItem = this.createBreadcrumbItem(segment, isLast, index + 1);
            this.breadcrumbList.appendChild(listItem);
        });
        
        // 테마 업데이트
        if (moduleData && moduleData.module) {
            this.updateTheme(moduleData.module);
        }
        
        // 애니메이션 트리거
        this.animateBreadcrumbUpdate();
    }
    
    /**
     * 브레드크럼 아이템 생성
     */
    createBreadcrumbItem(segment, isActive, position) {
        const li = document.createElement('li');
        li.className = `breadcrumb-item ${isActive ? 'active' : ''}`;
        li.setAttribute('itemscope', '');
        li.setAttribute('itemprop', 'itemListElement');
        li.setAttribute('itemtype', 'http://schema.org/ListItem');
        li.dataset.color = segment.color || this.moduleColors[this.currentModule];
        
        if (isActive) {
            // 현재 페이지 (링크 없음)
            li.innerHTML = `
                <span class="breadcrumb-current" itemprop="item" style="color: ${segment.color}">
                    <span class="breadcrumb-icon" style="color: ${segment.color}">
                        ${this.getIconSvg(segment.icon)}
                    </span>
                    <span class="breadcrumb-text" itemprop="name">${segment.title}</span>
                </span>
                <meta itemprop="position" content="${position}">
            `;
        } else {
            // 링크 가능한 브레드크럼
            li.innerHTML = `
                <a href="${segment.url}" class="breadcrumb-link" itemprop="item" 
                   title="${segment.title}" style="color: ${segment.color}">
                    <span class="breadcrumb-icon" style="color: ${segment.color}">
                        ${this.getIconSvg(segment.icon)}
                    </span>
                    <span class="breadcrumb-text" itemprop="name">${segment.title}</span>
                </a>
                <meta itemprop="position" content="${position}">
                <span class="breadcrumb-separator" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="9,18 15,12 9,6"></polyline>
                    </svg>
                </span>
            `;
        }
        
        return li;
    }
    
    /**
     * 아이콘 SVG 가져오기
     */
    getIconSvg(iconName) {
        const icons = {
            'home': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9,22 9,12 15,12 15,22"></polyline></svg>',
            'dashboard': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>',
            'organization': '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18"></path><path d="M5 21V7l8-4v18"></path><path d="M19 21V11l-6-4"></path></svg>'
        };
        
        return icons[iconName] || icons['home'];
    }
    
    /**
     * 상태 저장
     */
    saveState() {
        const state = {
            module: this.currentModule,
            bookmarked: this.bookmarkBtn?.classList.contains('active') || false,
            timestamp: Date.now()
        };
        
        sessionStorage.setItem('bpm_breadcrumb_state', JSON.stringify(state));
    }
    
    /**
     * 상태 복원
     */
    restoreState() {
        try {
            const state = sessionStorage.getItem('bpm_breadcrumb_state');
            if (state) {
                const parsedState = JSON.parse(state);
                
                // 즐겨찾기 상태 복원
                if (parsedState.bookmarked && this.isCurrentPageBookmarked()) {
                    this.bookmarkBtn?.classList.add('active');
                }
            }
        } catch (error) {
            console.error('브레드크럼 상태 복원 오류:', error);
        }
        
        // 현재 페이지가 즐겨찾기인지 확인
        if (this.isCurrentPageBookmarked()) {
            this.bookmarkBtn?.classList.add('active');
        }
    }
    
    /**
     * 현재 페이지가 즐겨찾기인지 확인
     */
    isCurrentPageBookmarked() {
        const currentPath = window.location.pathname;
        const bookmarks = this.getBookmarks();
        return bookmarks.some(b => b.path === currentPath);
    }
    
    /**
     * 알림 표시
     */
    showNotification(message, type = 'info') {
        // 임시 콘솔 출력 (향후 토스트 시스템과 연동)
        console.log(`[${type.toUpperCase()}] ${message}`);
        
        // 간단한 브라우저 알림 (개발용)
        if (window.BPM && window.BPM.debug) {
            alert(message);
        }
    }
    
    /**
     * 16진수 색상을 RGBA로 변환
     */
    hexToRgba(hex, alpha = 1) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }
    
    /**
     * 즐겨찾기 토글 애니메이션
     */
    animateBookmarkToggle() {
        if (!this.bookmarkBtn) return;
        
        this.bookmarkBtn.style.transform = 'scale(0.8)';
        this.bookmarkBtn.style.transition = 'transform 0.15s ease-in-out';
        
        setTimeout(() => {
            this.bookmarkBtn.style.transform = 'scale(1)';
            setTimeout(() => {
                this.bookmarkBtn.style.transform = '';
                this.bookmarkBtn.style.transition = '';
            }, 150);
        }, 75);
    }
    
    /**
     * 브레드크럼 업데이트 애니메이션
     */
    animateBreadcrumbUpdate() {
        const items = this.breadcrumbList.querySelectorAll('.breadcrumb-item');
        
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease-out, transform 0.3s ease-out';
                item.style.opacity = '1';
                item.style.transform = 'translateX(0)';
                
                setTimeout(() => {
                    item.style.transition = '';
                }, 300);
            }, index * 50);
        });
    }
    
    /**
     * 윈도우 리사이즈 처리
     */
    handleResize() {
        // 모바일에서 브레드크럼 표시 최적화
        if (window.innerWidth <= 767) {
            this.optimizeForMobile();
        } else {
            this.optimizeForDesktop();
        }
    }
    
    /**
     * 모바일 최적화
     */
    optimizeForMobile() {
        const items = this.breadcrumbList.querySelectorAll('.breadcrumb-item');
        
        // 첫 번째와 마지막 아이템만 표시
        items.forEach((item, index) => {
            if (index > 0 && index < items.length - 1) {
                item.style.display = 'none';
            } else {
                item.style.display = 'flex';
            }
        });
        
        // 생략 표시 추가
        if (items.length > 2) {
            const firstItem = items[0];
            if (!firstItem.querySelector('.breadcrumb-ellipsis')) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'breadcrumb-ellipsis';
                ellipsis.textContent = '...';
                ellipsis.style.color = 'var(--breadcrumb-separator-color)';
                ellipsis.style.marginLeft = 'var(--breadcrumb-gap)';
                firstItem.appendChild(ellipsis);
            }
        }
    }
    
    /**
     * 데스크톱 최적화
     */
    optimizeForDesktop() {
        const items = this.breadcrumbList.querySelectorAll('.breadcrumb-item');
        
        // 모든 아이템 표시
        items.forEach(item => {
            item.style.display = 'flex';
        });
        
        // 생략 표시 제거
        const ellipsis = this.breadcrumbList.querySelector('.breadcrumb-ellipsis');
        if (ellipsis) {
            ellipsis.remove();
        }
    }
    
    /**
     * 브라우저 히스토리 변경 처리
     */
    handlePopState(event) {
        // 페이지 변경시 브레드크럼 업데이트
        setTimeout(() => {
            const newModule = this.detectCurrentModule();
            if (newModule !== this.currentModule) {
                this.currentModule = newModule;
                this.updateTheme(newModule);
                this.saveState();
            }
        }, 100);
    }
    
    /**
     * 현재 모듈 감지
     */
    detectCurrentModule() {
        const path = window.location.pathname;
        const segments = path.split('/').filter(s => s && s !== 'BPM');
        
        if (segments.length > 0 && this.moduleColors[segments[0]]) {
            return segments[0];
        }
        
        return 'dashboard';
    }
}

// 전역 브레드크럼 매니저 인스턴스
let breadcrumbManager = null;

// DOM 로드 완료시 초기화
document.addEventListener('DOMContentLoaded', function() {
    breadcrumbManager = new BreadcrumbManager();
});

// 전역 함수로 노출 (다른 스크립트에서 사용 가능)
window.updateBreadcrumbTheme = function(module) {
    if (breadcrumbManager) {
        breadcrumbManager.updateTheme(module);
    }
};

window.updateBreadcrumb = function(pathSegments, moduleData) {
    if (breadcrumbManager) {
        breadcrumbManager.updateBreadcrumb(pathSegments, moduleData);
    }
};

// 브레드크럼 유틸리티 함수
window.BreadcrumbUtils = {
    /**
     * 현재 경로에서 브레드크럼 데이터 생성
     */
    generateFromPath: function(path) {
        const segments = path.split('/').filter(s => s && s !== 'BPM');
        // 구현 필요 (PHP 로직과 동기화)
        return segments;
    },
    
    /**
     * 즐겨찾기 목록 가져오기
     */
    getBookmarks: function() {
        return breadcrumbManager ? breadcrumbManager.getBookmarks() : [];
    },
    
    /**
     * 브레드크럼 상태 저장
     */
    saveState: function() {
        if (breadcrumbManager) {
            breadcrumbManager.saveState();
        }
    }
};