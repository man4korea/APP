<!-- 📁 C:\xampp\htdocs\BPM\login.php -->
<!-- Create at 2508031650 Ver1.00 -->

<?php
/**
 * BPM 로그인 페이지
 * EASYCORP 브랜딩 적용 로그인 시스템
 */

// 기본 설정 로드
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/core/bootstrap.php';

// 이미 로그인된 경우 대시보드로 리다이렉트
if (is_logged_in()) {
    header('Location: ' . base_url('dashboard'));
    exit;
}

// 로그인 처리
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // 기본 validation
    if (empty($email) || empty($password)) {
        $error = '이메일과 비밀번호를 입력해주세요.';
    } else {
        // 임시 로그인 처리 (실제 인증은 추후 구현)
        if ($email === 'admin@easycorp.com' && $password === 'admin123') {
            // 세션 시작
            session_start();
            $_SESSION['user_id'] = 1;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = 'EASYCORP 관리자';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['company_id'] = 1;
            
            // Remember me 처리
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                // 실제로는 DB에 토큰 저장 필요
            }
            
            $success = '로그인에 성공했습니다. 잠시 후 대시보드로 이동합니다.';
            header('refresh:2;url=' . base_url('dashboard'));
        } else {
            $error = '이메일 또는 비밀번호가 올바르지 않습니다.';
        }
    }
}

// 페이지 설정
$title = 'EASYCORP BPM - 로그인';
$hideHeader = true;
$hideSidebar = true;
$hideBreadcrumb = true;
$bodyClass = 'login-page';

// 로그인 페이지 콘텐츠
$content = '
<div class="login-container">
    <div class="login-card">
        
        <!-- 로고 및 브랜딩 -->
        <div class="login-header">
            <div class="login-logo">
                <svg width="64" height="64" viewBox="0 0 100 100" fill="none" class="logo-svg">
                    <!-- EASYCORP 로고 -->
                    <defs>
                        <linearGradient id="logoGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#3742fa;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#5a67d8;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    
                    <!-- E 문자 디자인 -->
                    <rect x="20" y="25" width="50" height="8" fill="url(#logoGradient)" rx="4"/>
                    <rect x="20" y="42" width="35" height="8" fill="url(#logoGradient)" rx="4"/>
                    <rect x="20" y="59" width="50" height="8" fill="url(#logoGradient)" rx="4"/>
                    <rect x="20" y="25" width="8" height="42" fill="url(#logoGradient)" rx="4"/>
                    
                    <!-- 추가 디자인 요소 -->
                    <circle cx="75" cy="35" r="3" fill="url(#logoGradient)" opacity="0.6"/>
                    <circle cx="70" cy="50" r="2" fill="url(#logoGradient)" opacity="0.4"/>
                    <circle cx="75" cy="60" r="3" fill="url(#logoGradient)" opacity="0.6"/>
                </svg>
            </div>
            <h1 class="login-title">EASYCORP</h1>
            <p class="login-subtitle">Business Process Management</p>
        </div>
        
        <!-- 로그인 폼 -->
        <form method="POST" class="login-form" id="loginForm">
            
            <!-- 알림 메시지 -->
            ' . (!empty($error) ? '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>' : '') . '
            ' . (!empty($success) ? '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>' : '') . '
            
            <!-- 이메일 필드 -->
            <div class="form-group">
                <label for="email" class="form-label">이메일</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="이메일을 입력하세요" value="' . htmlspecialchars($_POST['email'] ?? '') . '" required>
                </div>
            </div>
            
            <!-- 비밀번호 필드 -->
            <div class="form-group">
                <label for="password" class="form-label">비밀번호</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <circle cx="12" cy="16" r="1"></circle>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="비밀번호를 입력하세요" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <svg class="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- 옵션 -->
            <div class="form-options">
                <label class="checkbox-wrapper">
                    <input type="checkbox" name="remember" id="remember">
                    <span class="checkbox-custom"></span>
                    <span class="checkbox-label">로그인 상태 유지</span>
                </label>
                <a href="#" class="forgot-password">비밀번호 찾기</a>
            </div>
            
            <!-- 로그인 버튼 -->
            <button type="submit" class="login-button">
                <span class="button-text">로그인</span>
                <svg class="button-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16,17 21,12 16,7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
            </button>
            
        </form>
        
        <!-- 추가 정보 -->
        <div class="login-footer">
            <p class="demo-info">
                <strong>데모 계정:</strong><br>
                이메일: admin@easycorp.com<br>
                비밀번호: admin123
            </p>
            <div class="login-links">
                <a href="#" class="link">회원가입</a>
                <span class="divider">|</span>
                <a href="#" class="link">고객지원</a>
            </div>
        </div>
        
    </div>
    
    <!-- 배경 장식 -->
    <div class="login-background">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
    </div>
</div>

<style>
/* 로그인 페이지 전용 스타일 */
.login-page {
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    overflow-x: hidden;
}

.login-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
}

.login-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    padding: 40px;
    width: 100%;
    max-width: 420px;
    position: relative;
    z-index: 10;
    animation: slideUp 0.6s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* 헤더 */
.login-header {
    text-align: center;
    margin-bottom: 32px;
}

.login-logo {
    margin-bottom: 16px;
}

.logo-svg {
    filter: drop-shadow(0 4px 8px rgba(55, 66, 250, 0.2));
}

.login-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1a202c;
    margin: 0 0 8px 0;
    letter-spacing: -0.5px;
}

.login-subtitle {
    color: #64748b;
    font-size: 0.875rem;
    margin: 0;
    font-weight: 500;
}

/* 폼 스타일 */
.login-form {
    margin-bottom: 24px;
}

.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.input-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: 12px 12px 12px 44px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    background: #fafafa;
}

.form-input:focus {
    outline: none;
    border-color: #3742fa;
    background: white;
    box-shadow: 0 0 0 3px rgba(55, 66, 250, 0.1);
}

.password-toggle {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.2s ease;
}

.password-toggle:hover {
    color: #6b7280;
}

/* 옵션 */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.checkbox-wrapper {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.checkbox-wrapper input[type="checkbox"] {
    display: none;
}

.checkbox-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #e5e7eb;
    border-radius: 4px;
    margin-right: 8px;
    position: relative;
    transition: all 0.2s ease;
}

.checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom {
    background: #3742fa;
    border-color: #3742fa;
}

.checkbox-wrapper input[type="checkbox"]:checked + .checkbox-custom::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 4px;
    height: 8px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.checkbox-label {
    font-size: 0.875rem;
    color: #374151;
}

.forgot-password {
    color: #3742fa;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: color 0.2s ease;
}

.forgot-password:hover {
    color: #1d4ed8;
}

/* 로그인 버튼 */
.login-button {
    width: 100%;
    background: linear-gradient(135deg, #3742fa 0%, #5a67d8 100%);
    color: white;
    border: none;
    padding: 14px 24px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.login-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 16px rgba(55, 66, 250, 0.3);
}

.login-button:active {
    transform: translateY(0);
}

.button-icon {
    transition: transform 0.2s ease;
}

.login-button:hover .button-icon {
    transform: translateX(2px);
}

/* 알림 */
.alert {
    padding: 12px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.alert-success {
    background: #f0fdf4;
    color: #16a34a;
    border: 1px solid #bbf7d0;
}

/* 푸터 */
.login-footer {
    text-align: center;
    padding-top: 24px;
    border-top: 1px solid #f3f4f6;
}

.demo-info {
    background: #f8fafc;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 0.875rem;
    color: #475569;
    line-height: 1.5;
}

.login-links {
    font-size: 0.875rem;
}

.link {
    color: #3742fa;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
}

.link:hover {
    color: #1d4ed8;
}

.divider {
    margin: 0 12px;
    color: #d1d5db;
}

/* 배경 장식 */
.login-background {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    overflow: hidden;
    z-index: 1;
}

.bg-shape {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 6s ease-in-out infinite;
}

.bg-shape-1 {
    width: 200px;
    height: 200px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.bg-shape-2 {
    width: 150px;
    height: 150px;
    top: 60%;
    right: 15%;
    animation-delay: 2s;
}

.bg-shape-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
}

/* 반응형 */
@media (max-width: 480px) {
    .login-card {
        padding: 24px;
        margin: 20px;
    }
    
    .login-title {
        font-size: 1.75rem;
    }
    
    .form-options {
        flex-direction: column;
        gap: 12px;
    }
}
</style>

<script>
// 비밀번호 표시/숨김 토글
function togglePassword() {
    const passwordInput = document.getElementById("password");
    const eyeIcon = document.querySelector(".eye-icon");
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        eyeIcon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    } else {
        passwordInput.type = "password";
        eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    }
}

// 폼 제출 시 로딩 상태
document.getElementById("loginForm").addEventListener("submit", function(e) {
    const button = document.querySelector(".login-button");
    const buttonText = document.querySelector(".button-text");
    
    button.disabled = true;
    buttonText.textContent = "로그인 중...";
    
    // 실제 제출 계속 진행
});

// 입력 필드 자동 포커스
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("email").focus();
});
</script>
';

// 레이아웃에 콘텐츠 포함
include __DIR__ . '/views/layouts/main.php';
?>