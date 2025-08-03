// 📁 C:\xampp\htdocs\BPM\assets\js\theme.js
// Create at 2508031645 Ver1.00

/**
 * BPM 동적 테마 전환 시스템
 * 모듈별 색상 테마와 다크모드 지원
 * 모듈: 테마 관리 (색상: 동적)
 */

class ThemeManager {
    constructor() {
        this.currentTheme = 'dashboard';
        this.isDarkMode = false;
        this.isHighContrast = false;
        this.moduleColors = {
            'dashboard': {
                primary: '#3742fa',
                secondary: '#5a67d8',
                accent: '#667eea',
                light: '#f0f8ff',
                gradient: 'linear-gradient(135deg, #3742fa 0%, #5a67d8 100%)'
            },
            'organization': {
                primary: '#ff6b6b',
                secondary: '#ee5a24',
                accent: '#ff7675',
                light: '#fff5f5',
                gradient: 'linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%)'
            },
            'members': {
                primary: '#ff9f43',
                secondary: '#f39c12',
                accent: '#fdcb6e',
                light: '#fff8f0',
                gradient: 'linear-gradient(135deg, #ff9f43 0%, #f39c12 100%)'
            },
            'workflow': {
                primary: '#feca57',
                secondary: '#f1c40f',
                accent: '#fdcb6e',
                light: '#fffcf0',
                gradient: 'linear-gradient(135deg, #feca57 0%, #f1c40f 100%)'
            },
            'management': {
                primary: '#55a3ff',
                secondary: '#3498db',
                accent: '#74b9ff',
                light: '#f0fff4',
                gradient: 'linear-gradient(135deg, #55a3ff 0%, #3498db 100%)'
            },
            'processes': {
                primary: '#a55eea',
                secondary: '#8e44ad',
                accent: '#b084cc',
                light: '#f8f0ff',
                gradient: 'linear-gradient(135deg, #a55eea 0%, #8e44ad 100%)'
            },
            'analytics': {
                primary: '#8b4513',
                secondary: '#6b3410',
                accent: '#a0522d',
                light: '#faf0e6',
                gradient: 'linear-gradient(135deg, #8b4513 0%, #6b3410 100%)'
            },
            'innovation': {
                primary: '#2f3542',
                secondary: '#1e272e',
                accent: '#57606f',
                light: '#f1f2f6',
                gradient: 'linear-gradient(135deg, #2f3542 0%, #1e272e 100%)'
            },
            'hr': {
                primary: '#f1f2f6',
                secondary: '#ddd9e4',
                accent: '#c7ecee',
                light: '#ffffff',
                gradient: 'linear-gradient(135deg, #f1f2f6 0%, #ddd9e4 100%)'
            },
            'performance': {
                primary: '#ff6b9d',
                secondary: '#e84393',
                accent: '#fd79a8',
                light: '#fff0f8',
                gradient: 'linear-gradient(135deg, #ff6b9d 0%, #e84393 100%)'
            }
        };
        
        this.init();
    }
    
    /**
     * 테마 매니저 초기화
     */
    init() {
        // 저장된 테마 설정 복원
        this.loadSavedSettings();
        
        // 현재 모듈 감지
        this.detectCurrentModule();
        
        // 이벤트 리스너 등록
        this.bindEvents();
        
        // 초기 테마 적용
        this.applyTheme(this.currentTheme);
        
        // 시스템 다크모드 감지
        this.detectSystemDarkMode();
        
        console.log('테마 매니저 초기화 완료:', {
            currentTheme: this.currentTheme,
            isDarkMode: this.isDarkMode,
            isHighContrast: this.isHighContrast
        });
    }
    
    /**
     * 이벤트 리스너 바인딩
     */
    bindEvents() {
        // 시스템 다크모드 변경 감지
        if (window.matchMedia) {
            const darkModeQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeQuery.addListener(this.handleSystemDarkModeChange.bind(this));
            
            const highContrastQuery = window.matchMedia('(prefers-contrast: high)');
            highContrastQuery.addListener(this.handleHighContrastChange.bind(this));
        }
        
        // 사이드바 메뉴 클릭시 테마 변경
        document.addEventListener('click', (e) => {
            const navLink = e.target.closest('.main-nav-link, .sub-link');
            if (navLink) {
                const module = navLink.dataset.module || 
                              navLink.closest('.nav-item')?.dataset.module;
                if (module && this.moduleColors[module]) {
                    this.switchTheme(module);
                }
            }
        });
        
        // 테마 스위처 버튼 (향후 추가)
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-theme-toggle]')) {
                const theme = e.target.dataset.themeToggle;
                this.switchTheme(theme);
            }
            
            if (e.target.matches('[data-dark-mode-toggle]')) {
                this.toggleDarkMode();
            }
            
            if (e.target.matches('[data-contrast-toggle]')) {
                this.toggleHighContrast();
            }
        });
        
        // 키보드 단축키
        document.addEventListener('keydown', (e) => {
            // Ctrl + Shift + D: 다크모드 토글
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.toggleDarkMode();
            }
            
            // Ctrl + Shift + C: 고대비 모드 토글
            if (e.ctrlKey && e.shiftKey && e.key === 'C') {
                e.preventDefault();
                this.toggleHighContrast();
            }
        });
    }
    
    /**
     * 현재 모듈 감지
     */
    detectCurrentModule() {
        // URL에서 모듈 감지
        const path = window.location.pathname;
        const segments = path.split('/').filter(s => s && s !== 'BPM');
        
        if (segments.length > 0) {
            const module = segments[0];
            if (this.moduleColors[module]) {
                this.currentTheme = module;
                return;
            }
        }
        
        // 사이드바 활성 메뉴에서 감지
        const activeNavItem = document.querySelector('.nav-item.active');
        if (activeNavItem) {
            const module = activeNavItem.dataset.module;
            if (module && this.moduleColors[module]) {
                this.currentTheme = module;
                return;
            }
        }
        
        // 기본값: dashboard
        this.currentTheme = 'dashboard';
    }
    
    /**
     * 테마 전환
     */
    switchTheme(moduleName) {
        if (!this.moduleColors[moduleName]) {
            console.warn('알 수 없는 모듈:', moduleName);
            return;
        }
        
        const oldTheme = this.currentTheme;
        this.currentTheme = moduleName;
        
        // 테마 적용
        this.applyTheme(moduleName);
        
        // 상태 저장
        this.saveSettings();
        
        // 커스텀 이벤트 발생
        this.dispatchThemeChangeEvent(oldTheme, moduleName);
        
        // 애니메이션 효과
        this.animateThemeTransition();
        
        console.log(`테마 전환: ${oldTheme} → ${moduleName}`);
    }
    
    /**
     * 테마 적용
     */
    applyTheme(moduleName) {
        const theme = this.moduleColors[moduleName];
        if (!theme) return;
        
        const root = document.documentElement;
        
        // CSS 변수 업데이트
        root.style.setProperty('--theme-primary', theme.primary);
        root.style.setProperty('--theme-secondary', theme.secondary);
        root.style.setProperty('--theme-accent', theme.accent);
        root.style.setProperty('--theme-light', theme.light);
        root.style.setProperty('--theme-gradient', theme.gradient);
        
        // 기존 색상 변수도 업데이트 (호환성)
        root.style.setProperty('--primary-color', theme.primary);
        root.style.setProperty('--sidebar-primary-color', theme.primary);
        root.style.setProperty('--breadcrumb-primary-color', theme.primary);
        root.style.setProperty('--header-primary-color', theme.primary);
        
        // body 클래스 업데이트
        document.body.className = document.body.className
            .replace(/theme-\w+/g, '') + ` theme-${moduleName}`;
        
        // 메타 테마 컬러 업데이트 (모바일 브라우저용)
        this.updateMetaThemeColor(theme.primary);
        
        // 컴포넌트별 테마 업데이트
        this.updateComponentThemes(moduleName, theme);
    }
    
    /**
     * 컴포넌트별 테마 업데이트
     */
    updateComponentThemes(moduleName, theme) {
        // 헤더 테마 업데이트
        const header = document.querySelector('.main-header');
        if (header) {
            header.dataset.module = moduleName;
        }
        
        // 사이드바 테마 업데이트
        const sidebar = document.querySelector('.main-sidebar');
        if (sidebar) {
            sidebar.dataset.module = moduleName;
        }
        
        // 브레드크럼 테마 업데이트
        const breadcrumb = document.querySelector('.breadcrumb-nav');
        if (breadcrumb) {
            breadcrumb.dataset.module = moduleName;
        }
        
        // 외부 테마 업데이트 함수 호출
        if (window.updateBreadcrumbTheme) {
            window.updateBreadcrumbTheme(moduleName);
        }
        
        if (window.updateSidebarTheme) {
            window.updateSidebarTheme(moduleName);
        }
        
        if (window.updateHeaderTheme) {
            window.updateHeaderTheme(moduleName);
        }
    }
    
    /**
     * 다크모드 토글
     */
    toggleDarkMode() {
        this.isDarkMode = !this.isDarkMode;
        this.applyDarkMode();
        this.saveSettings();
        
        // 피드백 효과
        this.showThemeChangeNotification(
            this.isDarkMode ? '다크모드 활성화' : '라이트모드 활성화'
        );
        
        console.log('다크모드:', this.isDarkMode ? '활성화' : '비활성화');
    }
    
    /**
     * 다크모드 적용
     */
    applyDarkMode() {
        const root = document.documentElement;
        
        if (this.isDarkMode) {
            document.body.classList.add('dark-mode');
            root.style.setProperty('--bg-primary', '#1a202c');
            root.style.setProperty('--bg-secondary', '#2d3748');
            root.style.setProperty('--text-primary', '#f7fafc');
            root.style.setProperty('--text-secondary', '#e2e8f0');
            root.style.setProperty('--border-color', '#4a5568');
        } else {
            document.body.classList.remove('dark-mode');
            root.style.setProperty('--bg-primary', '#ffffff');
            root.style.setProperty('--bg-secondary', '#f8f9fa');
            root.style.setProperty('--text-primary', '#1a202c');
            root.style.setProperty('--text-secondary', '#4a5568');
            root.style.setProperty('--border-color', '#e2e8f0');
        }
    }
    
    /**
     * 고대비 모드 토글
     */
    toggleHighContrast() {
        this.isHighContrast = !this.isHighContrast;
        this.applyHighContrast();
        this.saveSettings();
        
        this.showThemeChangeNotification(
            this.isHighContrast ? '고대비 모드 활성화' : '고대비 모드 비활성화'
        );
        
        console.log('고대비 모드:', this.isHighContrast ? '활성화' : '비활성화');
    }
    
    /**
     * 고대비 모드 적용
     */
    applyHighContrast() {
        if (this.isHighContrast) {
            document.body.classList.add('high-contrast');
        } else {
            document.body.classList.remove('high-contrast');
        }
    }
    
    /**
     * 시스템 다크모드 감지
     */
    detectSystemDarkMode() {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // 사용자 설정이 없으면 시스템 설정 따라가기
            if (!localStorage.getItem('bpm_dark_mode')) {
                this.isDarkMode = true;
                this.applyDarkMode();
            }
        }
    }
    
    /**
     * 시스템 다크모드 변경 처리
     */
    handleSystemDarkModeChange(e) {
        // 사용자가 수동으로 설정하지 않았다면 시스템 설정 따라가기
        if (!localStorage.getItem('bmp_dark_mode')) {
            this.isDarkMode = e.matches;
            this.applyDarkMode();
        }
    }
    
    /**
     * 고대비 모드 변경 처리
     */
    handleHighContrastChange(e) {
        if (!localStorage.getItem('bpm_high_contrast')) {
            this.isHighContrast = e.matches;
            this.applyHighContrast();
        }
    }
    
    /**
     * 메타 테마 컬러 업데이트
     */
    updateMetaThemeColor(color) {
        let metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (!metaThemeColor) {
            metaThemeColor = document.createElement('meta');
            metaThemeColor.name = 'theme-color';
            document.head.appendChild(metaThemeColor);
        }
        metaThemeColor.content = color;
    }
    
    /**
     * 테마 전환 애니메이션
     */
    animateThemeTransition() {
        const body = document.body;
        
        // 부드러운 전환 효과 추가
        body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
        
        // 펄스 효과
        body.style.animation = 'themeTransition 0.6s ease-out';
        
        setTimeout(() => {
            body.style.transition = '';
            body.style.animation = '';
        }, 600);
    }
    
    /**
     * 테마 변경 알림
     */
    showThemeChangeNotification(message) {
        // 임시 알림 요소 생성
        const notification = document.createElement('div');
        notification.className = 'theme-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: fixed;
            top: 80px;
            right: 20px;
            background: var(--theme-primary);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            font-size: 14px;
            font-weight: 500;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // 애니메이션
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 2000);
    }
    
    /**
     * 테마 변경 이벤트 발생
     */
    dispatchThemeChangeEvent(oldTheme, newTheme) {
        const event = new CustomEvent('themeChange', {
            detail: {
                oldTheme,
                newTheme,
                colors: this.moduleColors[newTheme],
                isDarkMode: this.isDarkMode,
                isHighContrast: this.isHighContrast
            }
        });
        
        document.dispatchEvent(event);
    }
    
    /**
     * 설정 저장
     */
    saveSettings() {
        const settings = {
            currentTheme: this.currentTheme,
            isDarkMode: this.isDarkMode,
            isHighContrast: this.isHighContrast,
            timestamp: Date.now()
        };
        
        localStorage.setItem('bpm_theme_settings', JSON.stringify(settings));
        localStorage.setItem('bpm_dark_mode', this.isDarkMode);
        localStorage.setItem('bpm_high_contrast', this.isHighContrast);
    }
    
    /**
     * 저장된 설정 로드
     */
    loadSavedSettings() {
        try {
            const settings = localStorage.getItem('bpm_theme_settings');
            if (settings) {
                const parsed = JSON.parse(settings);
                this.currentTheme = parsed.currentTheme || 'dashboard';
                this.isDarkMode = parsed.isDarkMode || false;
                this.isHighContrast = parsed.isHighContrast || false;
                
                // 다크모드/고대비 모드 즉시 적용
                if (this.isDarkMode) {
                    this.applyDarkMode();
                }
                if (this.isHighContrast) {
                    this.applyHighContrast();
                }
            }
        } catch (error) {
            console.error('테마 설정 로드 오류:', error);
        }
    }
    
    /**
     * 현재 테마 정보 가져오기
     */
    getCurrentTheme() {
        return {
            name: this.currentTheme,
            colors: this.moduleColors[this.currentTheme],
            isDarkMode: this.isDarkMode,
            isHighContrast: this.isHighContrast
        };
    }
    
    /**
     * 사용 가능한 테마 목록 가져오기
     */
    getAvailableThemes() {
        return Object.keys(this.moduleColors).map(key => ({
            name: key,
            colors: this.moduleColors[key]
        }));
    }
    
    /**
     * 테마 초기화
     */
    resetTheme() {
        this.currentTheme = 'dashboard';
        this.isDarkMode = false;
        this.isHighContrast = false;
        
        this.applyTheme('dashboard');
        this.applyDarkMode();
        this.applyHighContrast();
        
        // 저장된 설정 제거
        localStorage.removeItem('bpm_theme_settings');
        localStorage.removeItem('bmp_dark_mode');
        localStorage.removeItem('bpm_high_contrast');
        
        this.showThemeChangeNotification('테마가 초기화되었습니다');
    }
}

// CSS 애니메이션 정의
const themeAnimationCSS = `
@keyframes themeTransition {
    0% { opacity: 1; }
    50% { opacity: 0.8; }
    100% { opacity: 1; }
}

.theme-notification {
    pointer-events: none;
}

/* 테마별 전환 효과 */
body.theme-dashboard { transition: all 0.3s ease; }
body.theme-organization { transition: all 0.3s ease; }
body.theme-members { transition: all 0.3s ease; }
body.theme-workflow { transition: all 0.3s ease; }
body.theme-management { transition: all 0.3s ease; }

/* 고대비 모드 스타일 */
body.high-contrast {
    filter: contrast(1.5);
}

body.high-contrast * {
    border-width: 2px !important;
    font-weight: 600 !important;
}
`;

// CSS 스타일 삽입
const styleElement = document.createElement('style');
styleElement.textContent = themeAnimationCSS;
document.head.appendChild(styleElement);

// 전역 테마 매니저 인스턴스
let themeManager = null;

// DOM 로드 완료시 초기화
document.addEventListener('DOMContentLoaded', function() {
    themeManager = new ThemeManager();
});

// 전역 함수로 노출
window.switchTheme = function(moduleName) {
    if (themeManager) {
        themeManager.switchTheme(moduleName);
    }
};

window.toggleDarkMode = function() {
    if (themeManager) {
        themeManager.toggleDarkMode();
    }
};

window.toggleHighContrast = function() {
    if (themeManager) {
        themeManager.toggleHighContrast();
    }
};

window.getCurrentTheme = function() {
    return themeManager ? themeManager.getCurrentTheme() : null;
};

window.resetTheme = function() {
    if (themeManager) {
        themeManager.resetTheme();
    }
};

// 테마 유틸리티 함수
window.ThemeUtils = {
    /**
     * 색상 밝기 계산
     */
    getColorBrightness: function(hexColor) {
        const rgb = this.hexToRgb(hexColor);
        return (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    },
    
    /**
     * 16진수를 RGB로 변환
     */
    hexToRgb: function(hex) {
        const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    },
    
    /**
     * 색상의 대비색 계산
     */
    getContrastColor: function(hexColor) {
        const brightness = this.getColorBrightness(hexColor);
        return brightness > 128 ? '#000000' : '#ffffff';
    },
    
    /**
     * 색상 투명도 적용
     */
    addAlpha: function(hexColor, alpha) {
        const rgb = this.hexToRgb(hexColor);
        return `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, ${alpha})`;
    }
};