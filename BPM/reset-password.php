<?php
// 📁 C:\xampp\htdocs\BPM\reset-password.php
// Create at 2508041155 Ver1.00

require_once __DIR__ . '/includes/config.php';

$resetToken = $_GET['token'] ?? null;
if (!$resetToken) {
    header('Location: /BPM/login.php');
    exit;
}

$pageTitle = "비밀번호 재설정";
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    
    <style>
        .reset-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
        }

        .reset-header {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .reset-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .reset-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .reset-form {
            padding: 40px;
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
            border-color: #e53e3e;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
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

        .password-strength {
            margin-top: 8px;
        }

        .strength-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak {
            width: 25%;
            background: #e53e3e;
        }

        .strength-fair {
            width: 50%;
            background: #ff9500;
        }

        .strength-good {
            width: 75%;
            background: #38a169;
        }

        .strength-strong {
            width: 100%;
            background: #22543d;
        }

        .strength-text {
            font-size: 0.75rem;
            font-weight: 600;
        }

        .text-weak {
            color: #e53e3e;
        }

        .text-fair {
            color: #ff9500;
        }

        .text-good {
            color: #38a169;
        }

        .text-strong {
            color: #22543d;
        }

        .btn-reset {
            width: 100%;
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
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

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(229, 62, 62, 0.3);
        }

        .btn-reset:disabled {
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

        .back-to-login {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .back-to-login a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .reset-header {
                padding: 30px 20px;
            }
            
            .reset-form {
                padding: 30px 20px;
            }
            
            .reset-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-card">
            <div class="reset-header">
                <h1>🔐 비밀번호 재설정</h1>
                <p>새로운 비밀번호를 설정하세요</p>
            </div>
            
            <div class="reset-form">
                <div id="loadingMessage">
                    <div style="text-align: center; padding: 20px;">
                        <div class="loading-spinner" style="display: inline-block; margin-right: 0;"></div>
                        <p style="margin-top: 15px;">재설정 토큰을 확인하는 중...</p>
                    </div>
                </div>

                <div id="resetContent" style="display: none;">
                    <!-- 오류 메시지 -->
                    <div class="alert alert-error" id="errorMessage" style="display: none;">
                        <!-- JavaScript로 동적 생성 -->
                    </div>

                    <!-- 성공 메시지 -->
                    <div class="alert alert-success" id="successMessage" style="display: none;">
                        <strong>재설정 완료!</strong> 비밀번호가 성공적으로 변경되었습니다.
                        <br>잠시 후 로그인 페이지로 이동합니다.
                    </div>

                    <!-- 비밀번호 재설정 폼 -->
                    <form id="resetForm">
                        <div class="form-group">
                            <label for="new_password">새 비밀번호 <span class="required">*</span></label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                            <div class="help-text">8자 이상의 안전한 비밀번호를 입력하세요</div>
                            <div class="password-strength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <div class="strength-text" id="strengthText">비밀번호를 입력하세요</div>
                            </div>
                            <div class="error-message" id="new_password_error"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">비밀번호 확인 <span class="required">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                            <div class="error-message" id="confirm_password_error"></div>
                        </div>

                        <button type="submit" class="btn-reset" id="submitBtn">
                            <span class="loading-spinner" id="loadingSpinner"></span>
                            <span id="submitText">비밀번호 재설정</span>
                        </button>
                    </form>
                </div>

                <div class="back-to-login">
                    <a href="/BPM/login.php">← 로그인 페이지로 돌아가기</a>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        const token = '<?= htmlspecialchars($resetToken) ?>';

        // 페이지 로드 시 초기화
        document.addEventListener('DOMContentLoaded', function() {
            checkToken();
            setupFormSubmission();
            setupPasswordStrength();
        });

        // 토큰 유효성 확인
        async function checkToken() {
            try {
                // 실제로는 API로 토큰 유효성을 확인해야 함
                // 여기서는 시뮬레이션
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // 토큰이 유효하다고 가정
                showResetForm();
                
            } catch (error) {
                console.error('Token check failed:', error);
                showError('재설정 링크가 유효하지 않거나 만료되었습니다.');
            }
        }

        // 재설정 폼 표시
        function showResetForm() {
            document.getElementById('loadingMessage').style.display = 'none';
            document.getElementById('resetContent').style.display = 'block';
        }

        // 폼 제출 설정
        function setupFormSubmission() {
            document.getElementById('resetForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                // 비밀번호 유효성 검사
                if (newPassword.length < 8) {
                    showFieldError('new_password', '비밀번호는 8자 이상이어야 합니다.');
                    return;
                }
                
                if (newPassword !== confirmPassword) {
                    showFieldError('confirm_password', '비밀번호가 일치하지 않습니다.');
                    return;
                }
                
                const submitBtn = document.getElementById('submitBtn');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const submitText = document.getElementById('submitText');
                
                // 로딩 시작
                submitBtn.disabled = true;
                loadingSpinner.style.display = 'inline-block';
                submitText.textContent = '재설정 중...';
                
                // 에러 메시지 초기화
                clearErrors();
                
                try {
                    const response = await fetch('/BPM/api/users.php/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            token: token,
                            new_password: newPassword
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // 성공
                        document.getElementById('resetForm').style.display = 'none';
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
                            showError('비밀번호 재설정 중 오류가 발생했습니다: ' + result.message);
                        }
                    }
                } catch (error) {
                    console.error('Reset password error:', error);
                    showError('비밀번호 재설정 중 오류가 발생했습니다.');
                } finally {
                    // 로딩 종료
                    submitBtn.disabled = false;
                    loadingSpinner.style.display = 'none';
                    submitText.textContent = '비밀번호 재설정';
                }
            });
        }

        // 비밀번호 강도 체크 설정
        function setupPasswordStrength() {
            document.getElementById('new_password').addEventListener('input', function(e) {
                const password = e.target.value;
                const strength = calculatePasswordStrength(password);
                updatePasswordStrength(strength);
            });
        }

        // 비밀번호 강도 계산
        function calculatePasswordStrength(password) {
            let score = 0;
            
            if (password.length >= 8) score += 25;
            if (password.length >= 12) score += 25;
            if (/[a-z]/.test(password)) score += 10;
            if (/[A-Z]/.test(password)) score += 10;
            if (/[0-9]/.test(password)) score += 15;
            if (/[^a-zA-Z0-9]/.test(password)) score += 15;
            
            if (score <= 25) return 'weak';
            if (score <= 50) return 'fair';
            if (score <= 75) return 'good';
            return 'strong';
        }

        // 비밀번호 강도 표시 업데이트
        function updatePasswordStrength(strength) {
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            const strengthData = {
                'weak': { text: '약함', class: 'strength-weak', textClass: 'text-weak' },
                'fair': { text: '보통', class: 'strength-fair', textClass: 'text-fair' },
                'good': { text: '좋음', class: 'strength-good', textClass: 'text-good' },
                'strong': { text: '강함', class: 'strength-strong', textClass: 'text-strong' }
            };
            
            const data = strengthData[strength];
            if (data) {
                strengthFill.className = 'strength-fill ' + data.class;
                strengthText.className = 'strength-text ' + data.textClass;
                strengthText.textContent = '비밀번호 강도: ' + data.text;
            }
        }

        // 오류 표시
        function showError(message) {
            const errorMessage = document.getElementById('errorMessage');
            errorMessage.textContent = message;
            errorMessage.style.display = 'block';
        }

        // 필드별 오류 표시
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