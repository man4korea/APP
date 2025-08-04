<!-- 📁 C:\xampp\htdocs\BPM\views\pages\profile.php -->
<!-- Create at 2508041145 Ver1.00 -->

<?php
require_once __DIR__ . '/../../includes/config.php';

// 로그인 상태 확인
$currentUser = $auth->getCurrentUser();
if (!$currentUser) {
    header('Location: /BPM/login.php');
    exit;
}

// 회사 컨텍스트 확인
$companyId = $tenant->getCurrentCompanyId();
if (!$companyId) {
    header('Location: /BPM/views/pages/company-register.php');
    exit;
}

// 조회할 사용자 ID (기본값: 본인)
$targetUserId = $_GET['user_id'] ?? $currentUser['id'];

// 다른 사용자 프로필 조회 권한 확인
if ($targetUserId !== $currentUser['id']) {
    if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'view')) {
        $errorMessage = "다른 사용자의 프로필을 볼 권한이 없습니다.";
        $targetUserId = $currentUser['id'];
    }
}

$isOwnProfile = ($targetUserId === $currentUser['id']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필 관리 - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modules.css') ?>">
    
    <style>
        /* 🟠 구성원관리 모듈 색상 (#ff9f43 / #fff8f0) */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff8f0;
            min-height: 100vh;
        }

        .profile-header {
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255, 159, 67, 0.3);
        }

        .profile-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 30px;
        }

        .profile-sidebar {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 5px solid #ff9f43;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 20px;
            font-weight: 700;
        }

        .profile-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .profile-role {
            background: #ff9f43;
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 15px;
        }

        .profile-meta {
            color: #718096;
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .profile-stats {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .stats-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            color: #4a5568;
        }

        .stat-value {
            font-weight: 600;
            color: #2d3748;
        }

        .profile-main {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }

        .profile-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ff9f43;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3748;
            flex: 1;
        }

        .btn-edit {
            background: #ff9f43;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-edit:hover {
            background: #e67e22;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            border-color: #ff9f43;
            box-shadow: 0 0 0 3px rgba(255, 159, 67, 0.1);
        }

        .form-control:disabled {
            background: #f7fafc;
            color: #718096;
            cursor: not-allowed;
        }

        .form-control.error {
            border-color: #e53e3e;
        }

        .error-message {
            color: #e53e3e;
            font-size: 0.875rem;
            margin-top: 5px;
            display: none;
        }

        .success-message {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #22543d;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: none;
        }

        .form-actions {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
        }

        .btn-save {
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 159, 67, 0.3);
        }

        .btn-save:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-cancel {
            background: #e2e8f0;
            color: #4a5568;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            background: #cbd5e0;
        }

        .loading-spinner {
            display: none;
            width: 16px;
            height: 16px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .password-section {
            border-top: 1px solid #e2e8f0;
            padding-top: 25px;
            margin-top: 25px;
        }

        .password-change-form {
            display: none;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }

        .password-change-form.active {
            display: grid;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #ff9f43;
        }

        .info-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 5px;
        }

        .info-value {
            color: #2d3748;
            font-size: 1rem;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c53030;
        }

        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                order: 2;
            }
            
            .profile-main {
                order: 1;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .password-change-form {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h1>👤 프로필 관리</h1>
            <p><?= $isOwnProfile ? '내 프로필 정보를 관리하고 업데이트하세요' : '구성원 프로필 정보를 확인하세요' ?></p>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <strong>오류:</strong> <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php endif; ?>

        <div class="profile-content">
            <!-- 프로필 사이드바 -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar" id="profileAvatar">
                        <!-- JavaScript로 동적 생성 -->
                    </div>
                    <div class="profile-name" id="profileName">로딩 중...</div>
                    <div class="profile-role" id="profileRole">-</div>
                    <div class="profile-meta" id="profileMeta">
                        <!-- JavaScript로 동적 생성 -->
                    </div>
                </div>

                <div class="profile-stats">
                    <div class="stats-title">활동 통계</div>
                    <div class="stat-item">
                        <span class="stat-label">마지막 로그인</span>
                        <span class="stat-value" id="lastLogin">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">로그인 횟수</span>
                        <span class="stat-value" id="loginCount">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">계정 상태</span>
                        <span class="stat-value" id="accountStatus">-</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">회사 가입일</span>
                        <span class="stat-value" id="joinDate">-</span>
                    </div>
                </div>
            </div>

            <!-- 프로필 메인 -->
            <div class="profile-main">
                <!-- 기본 정보 -->
                <div class="profile-section">
                    <div class="section-header">
                        <div class="section-title">기본 정보</div>
                        <?php if ($isOwnProfile): ?>
                            <button class="btn-edit" onclick="toggleEdit('basic')">편집</button>
                        <?php endif; ?>
                    </div>

                    <div class="success-message" id="basicSuccessMessage">
                        <strong>저장 완료!</strong> 기본 정보가 성공적으로 업데이트되었습니다.
                    </div>

                    <div id="basicInfo">
                        <!-- JavaScript로 동적 생성 -->
                    </div>

                    <form id="basicForm" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">이름</label>
                                <input type="text" id="name" name="name" class="form-control">
                                <div class="error-message" id="name_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">전화번호</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="010-0000-0000">
                                <div class="error-message" id="phone_error"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save" id="basicSaveBtn">
                                <span class="loading-spinner" id="basicLoadingSpinner"></span>
                                저장
                            </button>
                            <button type="button" class="btn-cancel" onclick="cancelEdit('basic')">취소</button>
                        </div>
                    </form>
                </div>

                <!-- 회사 정보 -->
                <div class="profile-section">
                    <div class="section-header">
                        <div class="section-title">회사 정보</div>
                        <?php if ($isOwnProfile && $permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'edit')): ?>
                            <button class="btn-edit" onclick="toggleEdit('company')">편집</button>
                        <?php endif; ?>
                    </div>

                    <div class="success-message" id="companySuccessMessage">
                        <strong>저장 완료!</strong> 회사 정보가 성공적으로 업데이트되었습니다.
                    </div>

                    <div id="companyInfo">
                        <!-- JavaScript로 동적 생성 -->
                    </div>

                    <form id="companyForm" style="display: none;">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="department">부서</label>
                                <input type="text" id="department" name="department" class="form-control">
                                <div class="error-message" id="department_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="job_title">직책</label>
                                <input type="text" id="job_title" name="job_title" class="form-control">
                                <div class="error-message" id="job_title_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="employee_id">사번</label>
                                <input type="text" id="employee_id" name="employee_id" class="form-control">
                                <div class="error-message" id="employee_id_error"></div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save" id="companySaveBtn">
                                <span class="loading-spinner" id="companyLoadingSpinner"></span>
                                저장
                            </button>
                            <button type="button" class="btn-cancel" onclick="cancelEdit('company')">취소</button>
                        </div>
                    </form>
                </div>

                <!-- 보안 설정 (본인만) -->
                <?php if ($isOwnProfile): ?>
                <div class="profile-section">
                    <div class="section-header">
                        <div class="section-title">보안 설정</div>
                    </div>

                    <div class="success-message" id="passwordSuccessMessage">
                        <strong>변경 완료!</strong> 비밀번호가 성공적으로 변경되었습니다.
                    </div>

                    <div class="password-section">
                        <button class="btn-edit" id="changePasswordBtn" onclick="togglePasswordChange()">
                            비밀번호 변경
                        </button>
                        
                        <form class="password-change-form" id="passwordForm">
                            <div class="form-group">
                                <label for="current_password">현재 비밀번호</label>
                                <input type="password" id="current_password" name="current_password" class="form-control" required>
                                <div class="error-message" id="current_password_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">새 비밀번호</label>
                                <input type="password" id="new_password" name="new_password" class="form-control" required>
                                <div class="error-message" id="new_password_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">비밀번호 확인</label>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                                <div class="error-message" id="confirm_password_error"></div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-save" id="passwordSaveBtn">
                                    <span class="loading-spinner" id="passwordLoadingSpinner"></span>
                                    비밀번호 변경
                                </button>
                                <button type="button" class="btn-cancel" onclick="cancelPasswordChange()">취소</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let profileData = null;
        const targetUserId = '<?= htmlspecialchars($targetUserId) ?>';
        const isOwnProfile = <?= $isOwnProfile ? 'true' : 'false' ?>;

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            loadProfile();
            setupFormSubmissions();
        });

        // 프로필 데이터 로드
        async function loadProfile() {
            try {
                const response = await fetch(`/BPM/api/users.php/profile?user_id=${targetUserId}`);
                const result = await response.json();
                
                if (result.success) {
                    profileData = result.data;
                    renderProfile();
                } else {
                    console.error('Failed to load profile:', result.message);
                }
            } catch (error) {
                console.error('Profile load error:', error);
            }
        }

        // 프로필 렌더링
        function renderProfile() {
            // 사이드바 정보
            const avatar = document.getElementById('profileAvatar');
            const name = document.getElementById('profileName');
            const role = document.getElementById('profileRole');
            const meta = document.getElementById('profileMeta');
            
            avatar.textContent = getInitials(profileData.name || profileData.username);
            name.textContent = profileData.name || profileData.username;
            role.textContent = getRoleName(profileData.role_type);
            meta.innerHTML = `
                <div>${profileData.email}</div>
                ${profileData.department ? `<div>${profileData.department}</div>` : ''}
                ${profileData.job_title ? `<div>${profileData.job_title}</div>` : ''}
            `;

            // 통계 정보
            document.getElementById('lastLogin').textContent = formatDate(profileData.last_login_at) || '없음';
            document.getElementById('loginCount').textContent = profileData.login_count || 0;
            document.getElementById('accountStatus').textContent = getStatusText(profileData.status);
            document.getElementById('joinDate').textContent = formatDate(profileData.assigned_at) || '-';

            // 기본 정보
            renderBasicInfo();
            renderCompanyInfo();
        }

        // 기본 정보 렌더링
        function renderBasicInfo() {
            const basicInfo = document.getElementById('basicInfo');
            basicInfo.innerHTML = `
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">이름</div>
                        <div class="info-value">${profileData.name || '-'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">사용자명</div>
                        <div class="info-value">${profileData.username}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">이메일</div>
                        <div class="info-value">${profileData.email}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">전화번호</div>
                        <div class="info-value">${profileData.phone || '-'}</div>
                    </div>
                </div>
            `;
        }

        // 회사 정보 렌더링
        function renderCompanyInfo() {
            const companyInfo = document.getElementById('companyInfo');
            companyInfo.innerHTML = `
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">권한</div>
                        <div class="info-value">${getRoleName(profileData.role_type)}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">부서</div>
                        <div class="info-value">${profileData.department || '-'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">직책</div>
                        <div class="info-value">${profileData.job_title || '-'}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">사번</div>
                        <div class="info-value">${profileData.employee_id || '-'}</div>
                    </div>
                </div>
            `;
        }

        // 편집 모드 토글
        function toggleEdit(section) {
            const infoDiv = document.getElementById(section + 'Info');
            const formDiv = document.getElementById(section + 'Form');
            
            if (formDiv.style.display === 'none' || !formDiv.style.display) {
                // 편집 모드로 전환
                infoDiv.style.display = 'none';
                formDiv.style.display = 'block';
                
                // 폼에 현재 데이터 채우기
                fillForm(section);
            }
        }

        // 편집 취소
        function cancelEdit(section) {
            const infoDiv = document.getElementById(section + 'Info');
            const formDiv = document.getElementById(section + 'Form');
            
            infoDiv.style.display = 'block';
            formDiv.style.display = 'none';
            
            // 폼 초기화
            document.getElementById(section + 'Form').reset();
            clearErrors();
        }

        // 폼에 데이터 채우기
        function fillForm(section) {
            if (section === 'basic') {
                document.getElementById('name').value = profileData.name || '';
                document.getElementById('phone').value = profileData.phone || '';
            } else if (section === 'company') {
                document.getElementById('department').value = profileData.department || '';
                document.getElementById('job_title').value = profileData.job_title || '';
                document.getElementById('employee_id').value = profileData.employee_id || '';
            }
        }

        // 폼 제출 설정
        function setupFormSubmissions() {
            // 기본 정보 폼
            document.getElementById('basicForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                await saveProfile('basic', this);
            });

            // 회사 정보 폼
            document.getElementById('companyForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                await saveProfile('company', this);
            });

            // 비밀번호 변경 폼
            if (isOwnProfile) {
                document.getElementById('passwordForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    await changePassword(this);
                });
            }
        }

        // 프로필 저장
        async function saveProfile(section, form) {
            const saveBtn = document.getElementById(section + 'SaveBtn');
            const spinner = document.getElementById(section + 'LoadingSpinner');
            const successMsg = document.getElementById(section + 'SuccessMessage');
            
            // 로딩 시작
            saveBtn.disabled = true;
            spinner.style.display = 'inline-block';
            
            clearErrors();
            successMsg.style.display = 'none';
            
            try {
                const formData = new FormData(form);
                const profileData = Object.fromEntries(formData);
                
                const response = await fetch('/BPM/api/users.php/profile', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_id: targetUserId,
                        profile_data: profileData
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // 성공
                    successMsg.style.display = 'block';
                    cancelEdit(section);
                    
                    // 프로필 데이터 새로고침
                    await loadProfile();
                } else {
                    if (result.errors) {
                        showErrors(result.errors);
                    } else {
                        alert('저장 중 오류가 발생했습니다: ' + result.message);
                    }
                }
            } catch (error) {
                console.error('Save profile error:', error);
                alert('저장 중 오류가 발생했습니다.');
            } finally {
                saveBtn.disabled = false;
                spinner.style.display = 'none';
            }
        }

        // 비밀번호 변경 토글
        function togglePasswordChange() {
            const form = document.getElementById('passwordForm');
            form.classList.toggle('active');
        }

        // 비밀번호 변경 취소
        function cancelPasswordChange() {
            const form = document.getElementById('passwordForm');
            form.classList.remove('active');
            form.reset();
            clearErrors();
        }

        // 비밀번호 변경
        async function changePassword(form) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // 비밀번호 확인
            if (newPassword !== confirmPassword) {
                showFieldError('confirm_password', '비밀번호가 일치하지 않습니다.');
                return;
            }
            
            if (newPassword.length < 8) {
                showFieldError('new_password', '비밀번호는 8자 이상이어야 합니다.');
                return;
            }
            
            const saveBtn = document.getElementById('passwordSaveBtn');
            const spinner = document.getElementById('passwordLoadingSpinner');
            const successMsg = document.getElementById('passwordSuccessMessage');
            
            // 로딩 시작
            saveBtn.disabled = true;
            spinner.style.display = 'inline-block';
            
            clearErrors();
            successMsg.style.display = 'none';
            
            try {
                const formData = new FormData(form);
                const passwordData = Object.fromEntries(formData);
                
                const response = await fetch('/BPM/api/users.php/change-password', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(passwordData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    successMsg.style.display = 'block';
                    cancelPasswordChange();
                } else {
                    if (result.errors) {
                        showErrors(result.errors);
                    } else {
                        alert('비밀번호 변경 중 오류가 발생했습니다: ' + result.message);
                    }
                }
            } catch (error) {
                console.error('Change password error:', error);
                alert('비밀번호 변경 중 오류가 발생했습니다.');
            } finally {
                saveBtn.disabled = false;
                spinner.style.display = 'none';
            }
        }

        // 유틸리티 함수들
        function getInitials(name) {
            if (!name) return '?';
            return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
        }

        function getRoleName(roleType) {
            const roleNames = {
                'founder': '창립자',
                'admin': '관리자',
                'process_owner': '프로세스 담당자',
                'member': '일반 구성원'
            };
            return roleNames[roleType] || roleType;
        }

        function getStatusText(status) {
            const statusNames = {
                'active': '활성',
                'inactive': '비활성',
                'pending': '대기중',
                'suspended': '정지됨'
            };
            return statusNames[status] || status;
        }

        function formatDate(dateString) {
            if (!dateString) return null;
            const date = new Date(dateString);
            return date.toLocaleDateString('ko-KR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function showFieldError(fieldName, message) {
            const errorElement = document.getElementById(fieldName + '_error');
            const inputElement = document.getElementById(fieldName);
            
            if (errorElement && inputElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                inputElement.classList.add('error');
            }
        }

        function showErrors(errors) {
            for (const [field, message] of Object.entries(errors)) {
                showFieldError(field, message);
            }
        }

        function clearErrors() {
            document.querySelectorAll('.error-message').forEach(element => {
                element.style.display = 'none';
            });
            
            document.querySelectorAll('.form-control').forEach(element => {
                element.classList.remove('error');
            });
        }

        // 실시간 유효성 검사
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('form-control')) {
                e.target.classList.remove('error');
                const errorElement = document.getElementById(e.target.id + '_error');
                if (errorElement) {
                    errorElement.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>