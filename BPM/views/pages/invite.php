<!-- 📁 C:\xampp\htdocs\BPM\views\pages\invite.php -->
<!-- Create at 2508041135 Ver1.00 -->

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

// 초대 권한 확인
if (!$permission->hasModulePermission($currentUser['id'], $companyId, 'members', 'invite')) {
    $errorMessage = "구성원 초대 권한이 없습니다.";
}

// 초대 토큰으로 접근한 경우 (초대 수락 페이지)
$inviteToken = $_GET['token'] ?? null;
if ($inviteToken) {
    include __DIR__ . '/invite-accept.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>구성원 초대 - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/modules.css') ?>">
    
    <style>
        /* 🟠 구성원관리 모듈 색상 (#ff9f43 / #fff8f0) */
        .invite-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff8f0;
            min-height: 100vh;
        }

        .invite-header {
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(255, 159, 67, 0.3);
        }

        .invite-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .invite-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .invite-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .invite-form-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #ff9f43;
        }

        .invite-list-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            max-height: 600px;
            overflow-y: auto;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff9f43;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #4a5568;
        }

        .required {
            color: #e53e3e;
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

        .form-control.error {
            border-color: #e53e3e;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .role-selector {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 10px;
        }

        .role-option {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .role-option:hover {
            border-color: #ff9f43;
            background: #fff8f0;
        }

        .role-option.selected {
            border-color: #ff9f43;
            background: #fff8f0;
        }

        .role-option input {
            display: none;
        }

        .role-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .role-description {
            font-size: 0.875rem;
            color: #718096;
        }

        .role-level {
            position: absolute;
            top: 5px;
            right: 8px;
            background: #ff9f43;
            color: white;
            font-size: 0.75rem;
            padding: 2px 6px;
            border-radius: 10px;
        }

        .btn-invite {
            width: 100%;
            background: linear-gradient(135deg, #ff9f43 0%, #e67e22 100%);
            color: white;
            border: none;
            padding: 16px 24px;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-invite:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 159, 67, 0.3);
        }

        .btn-invite:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .loading-spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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

        .help-text {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 5px;
        }

        /* 초대 목록 스타일 */
        .invitation-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .invitation-item:hover {
            background: #fff8f0;
            border-color: #ff9f43;
        }

        .invitation-info {
            flex: 1;
        }

        .invitation-email {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .invitation-meta {
            font-size: 0.875rem;
            color: #718096;
        }

        .invitation-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-sent {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-expired {
            background: #f8d7da;
            color: #721c24;
        }

        .invitation-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 0.875rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel {
            background: #e53e3e;
            color: white;
        }

        .btn-cancel:hover {
            background: #c53030;
        }

        .btn-resend {
            background: #ff9f43;
            color: white;
        }

        .btn-resend:hover {
            background: #e67e22;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        @media (max-width: 768px) {
            .invite-content {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .role-selector {
                grid-template-columns: 1fr;
            }
            
            .invite-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="invite-container">
        <div class="invite-header">
            <h1>👥 구성원 초대</h1>
            <p>새로운 팀원을 BPM 시스템에 초대하여 함께 효율적인 업무 프로세스를 관리하세요</p>
        </div>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-error">
                <strong>오류:</strong> <?= htmlspecialchars($errorMessage) ?>
            </div>
        <?php else: ?>
            <div class="invite-content">
                <!-- 초대 폼 -->
                <div class="invite-form-section">
                    <div class="section-title">새 구성원 초대</div>
                    
                    <div class="success-message" id="successMessage">
                        <strong>초대 완료!</strong> 초대 이메일이 성공적으로 발송되었습니다.
                    </div>
                    
                    <form id="inviteForm">
                        <div class="form-group">
                            <label for="invite_email">이메일 주소 <span class="required">*</span></label>
                            <input type="email" id="invite_email" name="invite_email" class="form-control" required>
                            <div class="help-text">초대받을 사용자의 이메일 주소를 입력하세요</div>
                            <div class="error-message" id="invite_email_error"></div>
                        </div>

                        <div class="form-group">
                            <label>권한 레벨 <span class="required">*</span></label>
                            <div class="role-selector" id="roleSelector">
                                <!-- 역할 옵션은 JavaScript로 동적 생성 -->
                            </div>
                            <div class="error-message" id="role_type_error"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="department">부서</label>
                                <input type="text" id="department" name="department" class="form-control" placeholder="예: 개발팀">
                            </div>
                            
                            <div class="form-group">
                                <label for="job_title">직책</label>
                                <input type="text" id="job_title" name="job_title" class="form-control" placeholder="예: 선임 개발자">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="message">초대 메시지</label>
                            <textarea id="message" name="message" class="form-control" rows="3" placeholder="초대받을 분에게 전달할 메시지를 입력하세요 (선택사항)"></textarea>
                            <div class="help-text">이 메시지는 초대 이메일에 포함됩니다</div>
                        </div>

                        <button type="submit" class="btn-invite" id="submitBtn">
                            <span class="loading-spinner" id="loadingSpinner"></span>
                            <span id="submitText">초대 이메일 발송</span>
                        </button>
                    </form>
                </div>

                <!-- 초대 목록 -->
                <div class="invite-list-section">
                    <div class="section-title">
                        초대 현황
                        <button class="btn-small btn-resend" onclick="refreshInvitations()" style="float: right;">
                            🔄 새로고침
                        </button>
                    </div>
                    
                    <div id="invitationsList">
                        <div class="empty-state">
                            <div class="empty-state-icon">📧</div>
                            <p>초대 내역을 불러오는 중...</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- JavaScript -->
    <script>
        let availableRoles = {};

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            loadAvailableRoles();
            loadInvitations();
            setupFormSubmission();
        });

        // 사용 가능한 역할 로드
        async function loadAvailableRoles() {
            try {
                const response = await fetch('/BPM/api/users.php/roles');
                const result = await response.json();
                
                if (result.success) {
                    availableRoles = result.data;
                    renderRoleSelector();
                }
            } catch (error) {
                console.error('Failed to load roles:', error);
            }
        }

        // 역할 선택기 렌더링
        function renderRoleSelector() {
            const roleSelector = document.getElementById('roleSelector');
            const roleDescriptions = {
                'member': '기본적인 프로세스 참여 및 태스크 수행',
                'process_owner': '프로세스 관리 및 태스크 배정 권한',
                'admin': '전체 시스템 관리 및 사용자 권한 관리',
                'founder': '최고 관리자 권한 (모든 기능 접근 가능)'
            };

            const roleLevels = {
                'member': '40',
                'process_owner': '60', 
                'admin': '80',
                'founder': '100'
            };

            roleSelector.innerHTML = '';
            
            Object.entries(availableRoles).forEach(([roleKey, roleName], index) => {
                const roleOption = document.createElement('div');
                roleOption.className = 'role-option';
                if (index === 0) roleOption.classList.add('selected');
                
                roleOption.innerHTML = `
                    <input type="radio" name="role_type" value="${roleKey}" ${index === 0 ? 'checked' : ''}>
                    <div class="role-level">${roleLevels[roleKey]}</div>
                    <div class="role-title">${roleName}</div>
                    <div class="role-description">${roleDescriptions[roleKey] || ''}</div>
                `;
                
                roleOption.onclick = () => selectRole(roleOption, roleKey);
                roleSelector.appendChild(roleOption);
            });
        }

        // 역할 선택
        function selectRole(element, roleKey) {
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            element.classList.add('selected');
            document.querySelector(`input[value="${roleKey}"]`).checked = true;
        }

        // 폼 제출 설정
        function setupFormSubmission() {
            document.getElementById('inviteForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitBtn = document.getElementById('submitBtn');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const submitText = document.getElementById('submitText');
                const successMessage = document.getElementById('successMessage');
                
                // 로딩 시작
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'inline-block';
                submitText.textContent = '발송 중...';
                
                // 에러 메시지 초기화
                clearErrors();
                successMessage.style.display = 'none';
                
                try {
                    // 폼 데이터 수집
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData);
                    
                    // API 요청
                    const response = await fetch('/BPM/api/users.php/invite', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // 성공
                        successMessage.style.display = 'block';
                        this.reset();
                        renderRoleSelector(); // 역할 선택기 리셋
                        loadInvitations(); // 초대 목록 새로고침
                    } else {
                        // 오류 처리
                        if (result.errors) {
                            showErrors(result.errors);
                        } else {
                            alert('초대 발송 중 오류가 발생했습니다: ' + result.message);
                        }
                    }
                } catch (error) {
                    console.error('Invitation error:', error);
                    alert('초대 발송 중 오류가 발생했습니다. 다시 시도해주세요.');
                } finally {
                    // 로딩 종료
                    submitBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    submitText.textContent = '초대 이메일 발송';
                }
            });
        }

        // 초대 목록 로드
        async function loadInvitations() {
            try {
                const response = await fetch('/BPM/api/users.php/invitations');
                const result = await response.json();
                
                if (result.success) {
                    renderInvitations(result.data);
                }
            } catch (error) {
                console.error('Failed to load invitations:', error);
            }
        }

        // 초대 목록 렌더링
        function renderInvitations(invitations) {
            const invitationsList = document.getElementById('invitationsList');
            
            if (invitations.length === 0) {
                invitationsList.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📧</div>
                        <p>아직 초대한 구성원이 없습니다<br>새로운 팀원을 초대해보세요!</p>
                    </div>
                `;
                return;
            }
            
            invitationsList.innerHTML = invitations.map(invitation => `
                <div class="invitation-item">
                    <div class="invitation-info">
                        <div class="invitation-email">${invitation.email}</div>
                        <div class="invitation-meta">
                            ${availableRoles[invitation.role_type] || invitation.role_type} • 
                            ${invitation.department || '부서 미지정'} • 
                            ${formatDate(invitation.created_at)}
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                        <div class="invitation-status status-${invitation.status}">
                            ${getStatusText(invitation.status)}
                        </div>
                        <div class="invitation-actions">
                            ${invitation.status === 'pending' || invitation.status === 'sent' ? `
                                <button class="btn-small btn-resend" onclick="resendInvitation('${invitation.id}')">
                                    재발송
                                </button>
                                <button class="btn-small btn-cancel" onclick="cancelInvitation('${invitation.id}')">
                                    취소
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // 상태 텍스트 변환
        function getStatusText(status) {
            const statusMap = {
                'pending': '대기중',
                'sent': '발송됨',
                'accepted': '수락됨',
                'expired': '만료됨',
                'cancelled': '취소됨'
            };
            return statusMap[status] || status;
        }

        // 날짜 포맷팅
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ko-KR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // 초대 재발송
        async function resendInvitation(invitationId) {
            if (!confirm('초대 이메일을 다시 발송하시겠습니까?')) return;
            
            try {
                // 구현 예정: 재발송 API
                alert('재발송 기능은 곧 구현될 예정입니다.');
            } catch (error) {
                console.error('Resend failed:', error);
                alert('재발송 중 오류가 발생했습니다.');
            }
        }

        // 초대 취소
        async function cancelInvitation(invitationId) {
            if (!confirm('이 초대를 취소하시겠습니까?')) return;
            
            try {
                const response = await fetch(`/BPM/api/users.php/cancel-invitation?invitation_id=${invitationId}`, {
                    method: 'DELETE'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadInvitations(); // 목록 새로고침
                } else {
                    alert('초대 취소 중 오류가 발생했습니다: ' + result.message);
                }
            } catch (error) {
                console.error('Cancel failed:', error);
                alert('초대 취소 중 오류가 발생했습니다.');
            }
        }

        // 초대 목록 새로고침
        function refreshInvitations() {
            loadInvitations();
        }

        // 에러 메시지 표시
        function showErrors(errors) {
            for (const [field, message] of Object.entries(errors)) {
                const errorElement = document.getElementById(field + '_error');
                const inputElement = document.getElementById(field);
                
                if (errorElement && inputElement) {
                    errorElement.textContent = message;
                    errorElement.style.display = 'block';
                    inputElement.classList.add('error');
                }
            }
        }

        // 에러 메시지 초기화
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