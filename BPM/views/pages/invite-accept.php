<!-- 📁 C:\xampp\htdocs\BPM\views\pages\invite-accept.php -->
<!-- Create at 2508041140 Ver1.00 -->

<?php
require_once __DIR__ . '/../../includes/config.php';

$inviteToken = $_GET['token'] ?? null;
if (!$inviteToken) {
    header('Location: /BPM/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>초대 수락 - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    
    <style>
        .accept-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .accept-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            overflow: hidden;
        }

        .accept-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .accept-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .accept-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .accept-form {
            padding: 40px;
        }

        .invitation-info {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .invitation-info h3 {
            margin: 0 0 15px 0;
            color: #2d3748;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
        }

        .info-value {
            color: #2d3748;
        }

        .role-badge {
            background: #667eea;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .form-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e2e8f0;
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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

        .help-text {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 5px;
        }

        .btn-accept {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .btn-accept:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-accept:disabled {
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

        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #22543d;
        }

        .existing-user-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .accept-header {
                padding: 30px 20px;
            }
            
            .accept-form {
                padding: 30px 20px;
            }
            
            .accept-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="accept-container">
        <div class="accept-card">
            <div class="accept-header">
                <h1>🎉 초대 수락</h1>
                <p>BPM 시스템에 오신 것을 환영합니다!</p>
            </div>
            
            <div class="accept-form">
                <div id="loadingMessage">
                    <div style="text-align: center; padding: 40px;">
                        <div class="loading-spinner" style="display: inline-block; margin-right: 0;"></div>
                        <p style="margin-top: 20px;">초대 정보를 확인하는 중...</p>
                    </div>
                </div>

                <div id="invitationContent" style="display: none;">
                    <!-- 초대 정보 표시 -->
                    <div class="invitation-info" id="invitationInfo">
                        <!-- JavaScript로 동적 생성 -->
                    </div>

                    <!-- 오류 메시지 -->
                    <div class="alert alert-error" id="errorMessage" style="display: none;">
                        <!-- JavaScript로 동적 생성 -->
                    </div>

                    <!-- 성공 메시지 -->
                    <div class="alert alert-success" id="successMessage" style="display: none;">
                        <strong>가입 완료!</strong> BPM 시스템에 성공적으로 가입되었습니다.
                        <br>잠시 후 로그인 페이지로 이동합니다.
                    </div>

                    <!-- 기존 사용자 알림 -->
                    <div class="existing-user-info" id="existingUserInfo" style="display: none;">
                        <strong>기존 계정 발견:</strong> 이미 가입된 계정이 있습니다. 새로운 회사에 추가됩니다.
                    </div>

                    <!-- 회원가입 폼 -->
                    <form id="acceptForm" style="display: none;">
                        <div class="form-section">
                            <div class="section-title">계정 정보</div>
                            
                            <div class="form-group">
                                <label for="username">사용자명 <span class="required">*</span></label>
                                <input type="text" id="username" name="username" class="form-control" required>
                                <div class="help-text">시스템에서 사용할 사용자명을 입력하세요</div>
                                <div class="error-message" id="username_error"></div>
                            </div>

                            <div class="form-group">
                                <label for="name">이름 <span class="required">*</span></label>
                                <input type="text" id="name" name="name" class="form-control" required>
                                <div class="error-message" id="name_error"></div>
                            </div>

                            <div class="form-group">
                                <label for="password">비밀번호 <span class="required">*</span></label>
                                <input type="password" id="password" name="password" class="form-control" required>
                                <div class="help-text">8자 이상의 안전한 비밀번호를 설정하세요</div>
                                <div class="error-message" id="password_error"></div>
                            </div>

                            <div class="form-group">
                                <label for="password_confirm">비밀번호 확인 <span class="required">*</span></label>
                                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
                                <div class="error-message" id="password_confirm_error"></div>
                            </div>
                        </div>

                        <div class="form-section">
                            <div class="section-title">추가 정보</div>
                            
                            <div class="form-group">
                                <label for="phone">전화번호</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="010-0000-0000">
                            </div>
                        </div>

                        <button type="submit" class="btn-accept" id="submitBtn">
                            <span class="loading-spinner" id="loadingSpinner"></span>
                            <span id="submitText">초대 수락 및 가입 완료</span>
                        </button>
                    </form>

                    <!-- 기존 사용자용 버튼 -->
                    <button class="btn-accept" id="existingUserBtn" style="display: none;" onclick="acceptAsExistingUser()">
                        <span class="loading-spinner" id="existingLoadingSpinner"></span>
                        <span id="existingSubmitText">회사에 합류하기</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        let invitationData = null;
        const token = '<?= htmlspecialchars($inviteToken) ?>';

        // 페이지 로드 시 초대 정보 확인
        document.addEventListener('DOMContentLoaded', function() {
            checkInvitation();
            setupFormSubmission();
        });

        // 초대 정보 확인
        async function checkInvitation() {
            try {
                // 실제로는 API를 통해 초대 정보를 확인해야 함
                // 여기서는 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // 임시 데이터 (실제로는 API에서 받아옴)
                invitationData = {
                    company_name: 'EASYCORP',
                    email: 'user@example.com',
                    role_type: 'member',
                    role_name: '일반 구성원',
                    department: '개발팀',
                    job_title: '개발자',
                    inviter_name: '김관리자',
                    expires_at: '2024-08-11',
                    is_existing_user: false
                };
                
                showInvitationContent();
                
            } catch (error) {
                console.error('Failed to check invitation:', error);
                showError('초대 정보를 확인할 수 없습니다. 초대 링크가 유효하지 않거나 만료되었을 수 있습니다.');
            }
        }

        // 초대 내용 표시
        function showInvitationContent() {
            document.getElementById('loadingMessage').style.display = 'none';
            document.getElementById('invitationContent').style.display = 'block';
            
            // 초대 정보 렌더링
            const invitationInfo = document.getElementById('invitationInfo');
            invitationInfo.innerHTML = `
                <h3>🏢 ${invitationData.company_name} 초대 정보</h3>
                <div class="info-item">
                    <span class="info-label">이메일:</span>
                    <span class="info-value">${invitationData.email}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">권한:</span>
                    <span class="role-badge">${invitationData.role_name}</span>
                </div>
                ${invitationData.department ? `
                <div class="info-item">
                    <span class="info-label">부서:</span>
                    <span class="info-value">${invitationData.department}</span>
                </div>
                ` : ''}
                ${invitationData.job_title ? `
                <div class="info-item">
                    <span class="info-label">직책:</span>
                    <span class="info-value">${invitationData.job_title}</span>
                </div>
                ` : ''}
                <div class="info-item">
                    <span class="info-label">초대자:</span>
                    <span class="info-value">${invitationData.inviter_name}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">만료일:</span>
                    <span class="info-value">${formatDate(invitationData.expires_at)}</span>
                </div>
            `;

            // 기존 사용자인지 확인
            if (invitationData.is_existing_user) {
                document.getElementById('existingUserInfo').style.display = 'block';
                document.getElementById('existingUserBtn').style.display = 'block';
            } else {
                document.getElementById('acceptForm').style.display = 'block';
            }
        }

        // 폼 제출 설정
        function setupFormSubmission() {
            document.getElementById('acceptForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // 비밀번호 확인
                const password = document.getElementById('password').value;
                const passwordConfirm = document.getElementById('password_confirm').value;
                
                if (password !== passwordConfirm) {
                    showFieldError('password_confirm', '비밀번호가 일치하지 않습니다.');
                    return;
                }

                if (password.length < 8) {
                    showFieldError('password', '비밀번호는 8자 이상이어야 합니다.');
                    return;
                }
                
                const submitBtn = document.getElementById('submitBtn');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const submitText = document.getElementById('submitText');
                
                // 로딩 시작
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'inline-block';
                submitText.textContent = '가입 처리 중...';
                
                // 에러 메시지 초기화
                clearErrors();
                
                try {
                    // 폼 데이터 수집
                    const formData = new FormData(this);
                    const userData = Object.fromEntries(formData);
                    delete userData.password_confirm; // 확인용 비밀번호 제거
                    
                    // API 요청
                    const response = await fetch('/BPM/api/users.php/accept-invitation', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: token,
                            user_data: userData
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // 성공
                        document.getElementById('acceptForm').style.display = 'none';
                        document.getElementById('successMessage').style.display = 'block';
                        
                        // 3초 후 로그인 페이지로 이동
                        setTimeout(() => {
                            window.location.href = '/BPM/login.php';
                        }, 3000);
                    } else {
                        // 오류 처리
                        if (result.errors) {
                            showErrors(result.errors);
                        } else {
                            showError('가입 처리 중 오류가 발생했습니다: ' + result.message);
                        }
                    }
                } catch (error) {
                    console.error('Accept invitation error:', error);
                    showError('가입 처리 중 오류가 발생했습니다. 다시 시도해주세요.');
                } finally {
                    // 로딩 종료
                    submitBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    submitText.textContent = '초대 수락 및 가입 완료';
                }
            });
        }

        // 기존 사용자로 수락
        async function acceptAsExistingUser() {
            const existingBtn = document.getElementById('existingUserBtn');
            const existingSpinner = document.getElementById('existingLoadingSpinner');
            const existingText = document.getElementById('existingSubmitText');
            
            // 로딩 시작
            existingBtn.disabled = true;
            existingSpinner.style.display = 'inline-block';
            existingText.textContent = '처리 중...';
            
            try {
                const response = await fetch('/BPM/api/users.php/accept-invitation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token: token,
                        user_data: {}
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    document.getElementById('existingUserBtn').style.display = 'none';
                    document.getElementById('existingUserInfo').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = '/BPM/login.php';
                    }, 3000);
                } else {
                    showError('회사 합류 처리 중 오류가 발생했습니다: ' + result.message);
                }
            } catch (error) {
                console.error('Accept as existing user error:', error);
                showError('회사 합류 처리 중 오류가 발생했습니다. 다시 시도해주세요.');
            } finally {
                existingBtn.disabled = false;
                existingSpinner.style.display = 'none';
                existingText.textContent = '회사에 합류하기';
            }
        }

        // 오류 메시지 표시
        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }

        // 필드별 오류 메시지 표시
        function showFieldError(fieldName, message) {
            const errorElement = document.getElementById(fieldName + '_error');
            const inputElement = document.getElementById(fieldName);
            
            if (errorElement && inputElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                inputElement.classList.add('error');
            }
        }

        // 에러 메시지 표시
        function showErrors(errors) {
            for (const [field, message] of Object.entries(errors)) {
                showFieldError(field, message);
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
            
            document.getElementById('errorMessage').style.display = 'none';
        }

        // 날짜 포맷팅
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ko-KR', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
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