// 📁 C:\xampp\htdocs\BPM\assets\js\permission.js
// Create at 2508040630 Ver1.00

/**
 * BPM 권한 제어 JavaScript 시스템
 * 프론트엔드에서 권한별 UI 요소 표시/숨김 처리
 */

class PermissionManager {
    constructor() {
        this.currentUser = null;
        this.currentCompany = null;
        this.userPermissions = null;
        this.accessibleModules = null;
        this.apiBase = '/BPM/api/permissions.php';
        
        this.init();
    }

    /**
     * 초기화
     */
    async init() {
        try {
            // 현재 사용자 정보 로드
            await this.loadCurrentUser();
            
            // 권한 정보 로드
            await this.loadUserPermissions();
            
            // UI 권한 적용
            this.applyPermissions();
            
            console.log('PermissionManager initialized successfully');
        } catch (error) {
            console.error('PermissionManager initialization failed:', error);
        }
    }

    /**
     * 현재 사용자 정보 로드
     */
    async loadCurrentUser() {
        try {
            const response = await fetch('/BPM/api/auth.php/current-user', {
                headers: {
                    'X-Company-ID': this.getCompanyId()
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.currentUser = data.data;
                this.currentCompany = this.getCompanyId();
            }
        } catch (error) {
            console.error('Failed to load current user:', error);
        }
    }

    /**
     * 사용자 권한 정보 로드
     */
    async loadUserPermissions() {
        if (!this.currentUser || !this.currentCompany) {
            return;
        }

        try {
            // 사용자 권한 정보 로드
            const permissionsResponse = await fetch(
                `${this.apiBase}/user-permissions?user_id=${this.currentUser.id}`,
                {
                    headers: {
                        'X-Company-ID': this.currentCompany
                    }
                }
            );

            if (permissionsResponse.ok) {
                const permissionsData = await permissionsResponse.json();
                this.userPermissions = permissionsData.data;
            }

            // 접근 가능한 모듈 로드
            const modulesResponse = await fetch(
                `${this.apiBase}/accessible-modules?user_id=${this.currentUser.id}`,
                {
                    headers: {
                        'X-Company-ID': this.currentCompany
                    }
                }
            );

            if (modulesResponse.ok) {
                const modulesData = await modulesResponse.json();
                this.accessibleModules = modulesData.data;
            }

        } catch (error) {
            console.error('Failed to load user permissions:', error);
        }
    }

    /**
     * UI에 권한 적용
     */
    applyPermissions() {
        this.hideUnauthorizedElements();
        this.updateRoleBasedUI();
        this.bindPermissionEvents();
    }

    /**
     * 권한 없는 UI 요소 숨김
     */
    hideUnauthorizedElements() {
        // data-permission 속성을 가진 모든 요소 확인
        const permissionElements = document.querySelectorAll('[data-permission]');
        
        permissionElements.forEach(element => {
            const requiredPermission = element.dataset.permission;
            const [module, action = 'view'] = requiredPermission.split('.');
            
            if (!this.hasModulePermission(module, action)) {
                element.style.display = 'none';
                element.classList.add('permission-hidden');
            }
        });

        // data-role 속성을 가진 요소 확인
        const roleElements = document.querySelectorAll('[data-role]');
        
        roleElements.forEach(element => {
            const requiredRole = element.dataset.role;
            const minRoles = requiredRole.split(',').map(r => r.trim());
            
            if (!this.hasAnyRole(minRoles)) {
                element.style.display = 'none';
                element.classList.add('permission-hidden');
            }
        });
    }

    /**
     * 역할 기반 UI 업데이트
     */
    updateRoleBasedUI() {
        if (!this.userPermissions) return;

        // 사용자 역할 표시
        const roleElements = document.querySelectorAll('[data-user-role]');
        roleElements.forEach(element => {
            element.textContent = this.userPermissions.role_display || '구성원';
        });

        // 권한 레벨 표시
        const levelElements = document.querySelectorAll('[data-user-level]');
        levelElements.forEach(element => {
            element.textContent = this.userPermissions.level || '0';
        });

        // 관리자 전용 UI
        if (this.isAdmin()) {
            document.body.classList.add('user-admin');
        }

        // 창립자 전용 UI
        if (this.isFounder()) {
            document.body.classList.add('user-founder');
        }
    }

    /**
     * 권한 관련 이벤트 바인딩
     */
    bindPermissionEvents() {
        // 권한 확인 버튼 클릭 이벤트
        document.addEventListener('click', (e) => {
            if (e.target.hasAttribute('data-check-permission')) {
                e.preventDefault();
                
                const requiredPermission = e.target.dataset.checkPermission;
                const [module, action = 'view'] = requiredPermission.split('.');
                
                if (!this.hasModulePermission(module, action)) {
                    this.showPermissionDenied(module, action);
                    return false;
                }
                
                // 권한이 있으면 원래 동작 수행
                const originalHref = e.target.dataset.originalHref || e.target.href;
                if (originalHref) {
                    window.location.href = originalHref;
                }
            }
        });

        // 폼 제출 시 권한 확인
        document.addEventListener('submit', (e) => {
            const form = e.target;
            if (form.hasAttribute('data-permission-required')) {
                const requiredPermission = form.dataset.permissionRequired;
                const [module, action = 'create'] = requiredPermission.split('.');
                
                if (!this.hasModulePermission(module, action)) {
                    e.preventDefault();
                    this.showPermissionDenied(module, action);
                    return false;
                }
            }
        });
    }

    /**
     * 모듈 권한 확인
     */
    hasModulePermission(module, action = 'view') {
        if (!this.accessibleModules || !this.accessibleModules[module]) {
            return false;
        }
        
        return this.accessibleModules[module].includes(action);
    }

    /**
     * 역할 확인
     */
    hasRole(role) {
        return this.userPermissions && this.userPermissions.role === role;
    }

    /**
     * 여러 역할 중 하나라도 가지고 있는지 확인
     */
    hasAnyRole(roles) {
        if (!this.userPermissions) return false;
        
        const roleLevel = this.userPermissions.level || 0;
        const roleLevels = {
            'founder': 100,
            'admin': 80,
            'process_owner': 60,
            'member': 40
        };

        return roles.some(role => roleLevel >= (roleLevels[role] || 0));
    }

    /**
     * 관리자 이상 권한 확인
     */
    isAdmin() {
        return this.userPermissions && this.userPermissions.level >= 80;
    }

    /**
     * 창립자 권한 확인
     */
    isFounder() {
        return this.userPermissions && this.userPermissions.level >= 100;
    }

    /**
     * 프로세스 담당자 이상 권한 확인
     */
    isProcessOwner() {
        return this.userPermissions && this.userPermissions.level >= 60;
    }

    /**
     * 권한 부족 알림 표시
     */
    showPermissionDenied(module, action) {
        const message = `${module} 모듈의 ${action} 권한이 필요합니다.\n현재 권한: ${this.userPermissions?.role_display || '없음'}`;
        
        // 사용자 정의 알림 창 또는 기본 alert 사용
        if (typeof showNotification === 'function') {
            showNotification('권한 부족', message, 'warning');
        } else {
            alert(message);
        }
    }

    /**
     * 동적 권한 확인 (AJAX)
     */
    async checkPermissionAsync(module, action = 'view', userId = null) {
        try {
            const response = await fetch(`${this.apiBase}/check-permission`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Company-ID': this.currentCompany
                },
                body: JSON.stringify({
                    user_id: userId || this.currentUser.id,
                    module: module,
                    action: action
                })
            });

            if (response.ok) {
                const data = await response.json();
                return data.data.has_permission;
            }
            
            return false;
        } catch (error) {
            console.error('Permission check failed:', error);
            return false;
        }
    }

    /**
     * 사용자 역할 변경 (관리자 전용)
     */
    async assignRole(userId, role) {
        if (!this.isAdmin()) {
            throw new Error('Admin permission required');
        }

        try {
            const response = await fetch(`${this.apiBase}/assign-role`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Company-ID': this.currentCompany
                },
                body: JSON.stringify({
                    user_id: userId,
                    role: role
                })
            });

            if (response.ok) {
                const data = await response.json();
                return data;
            } else {
                const error = await response.json();
                throw new Error(error.message);
            }
        } catch (error) {
            console.error('Role assignment failed:', error);
            throw error;
        }
    }

    /**
     * 회사 ID 조회
     */
    getCompanyId() {
        // 다양한 소스에서 회사 ID 조회
        return localStorage.getItem('company_id') || 
               sessionStorage.getItem('company_id') ||
               document.body.dataset.companyId ||
               null;
    }

    /**
     * 권한 새로고침
     */
    async refresh() {
        await this.loadUserPermissions();
        this.applyPermissions();
    }

    /**
     * 디버그 정보 출력
     */
    debug() {
        console.group('PermissionManager Debug');
        console.log('Current User:', this.currentUser);
        console.log('Current Company:', this.currentCompany);
        console.log('User Permissions:', this.userPermissions);
        console.log('Accessible Modules:', this.accessibleModules);
        console.groupEnd();
    }
}

/**
 * 전역 권한 관리자 인스턴스
 */
let permissionManager = null;

/**
 * DOM 로드 완료 시 권한 관리자 초기화
 */
document.addEventListener('DOMContentLoaded', () => {
    permissionManager = new PermissionManager();
    
    // 전역 접근을 위해 window 객체에 추가
    window.PermissionManager = permissionManager;
});

/**
 * 편의 함수들
 */

// 모듈 권한 확인
function hasPermission(module, action = 'view') {
    return permissionManager ? permissionManager.hasModulePermission(module, action) : false;
}

// 역할 확인
function hasRole(role) {
    return permissionManager ? permissionManager.hasRole(role) : false;
}

// 관리자 확인
function isAdmin() {
    return permissionManager ? permissionManager.isAdmin() : false;
}

// 창립자 확인
function isFounder() {
    return permissionManager ? permissionManager.isFounder() : false;
}

// 비동기 권한 확인
async function checkPermission(module, action = 'view', userId = null) {
    return permissionManager ? await permissionManager.checkPermissionAsync(module, action, userId) : false;
}

// 권한 새로고침
async function refreshPermissions() {
    if (permissionManager) {
        await permissionManager.refresh();
    }
}

// jQuery 플러그인 (jQuery 사용 시)
if (typeof jQuery !== 'undefined') {
    jQuery.fn.checkPermission = function(module, action = 'view') {
        return this.each(function() {
            const $element = jQuery(this);
            if (!hasPermission(module, action)) {
                $element.hide().addClass('permission-hidden');
            }
        });
    };
}