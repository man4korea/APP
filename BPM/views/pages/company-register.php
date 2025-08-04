<!-- 📁 C:\xampp\htdocs\BPM\views\pages\company-register.php -->
<!-- Create at 2508040655 Ver1.00 -->

<?php
require_once __DIR__ . '/../../includes/config.php';

// 로그인 상태 확인 (선택적 - 개인 사용자도 회사 등록 가능)
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회사 등록 - BPM</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/common.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    
    <style>
        .company-register-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
        }

        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .register-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .register-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .register-form {
            padding: 40px;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
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

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-register:disabled {
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

        .company-type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .type-option {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .type-option:hover {
            border-color: #667eea;
        }

        .type-option.selected {
            border-color: #667eea;
            background: #f7fafc;
        }

        .type-option input {
            display: none;
        }

        .type-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .type-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 5px;
        }

        .type-description {
            font-size: 0.875rem;
            color: #718096;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .company-type-selector {
                grid-template-columns: 1fr;
            }
            
            .register-header {
                padding: 30px 20px;
            }
            
            .register-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="company-register-container">
        <div class="register-card">
            <div class="register-header">
                <h1>🏢 회사 등록</h1>
                <p>BPM 시스템에 새로운 회사를 등록하세요</p>
            </div>
            
            <div class="register-form">
                <div class="success-message" id="successMessage">
                    <strong>등록 완료!</strong> 회사가 성공적으로 등록되었습니다.
                </div>
                
                <form id="companyRegisterForm">
                    <!-- 기본 회사 정보 -->
                    <div class="form-section">
                        <div class="section-title">🏢 기본 회사 정보</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="company_name">회사명 <span class="required">*</span></label>
                                <input type="text" id="company_name" name="company_name" class="form-control" required>
                                <div class="error-message" id="company_name_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="tax_number">사업자등록번호 <span class="required">*</span></label>
                                <input type="text" id="tax_number" name="tax_number" class="form-control" placeholder="000-00-00000" required>
                                <div class="help-text">하이픈(-) 포함하여 입력하세요</div>
                                <div class="error-message" id="tax_number_error"></div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="business_type">업종</label>
                            <input type="text" id="business_type" name="business_type" class="form-control" placeholder="예: 소프트웨어 개발업">
                        </div>
                        
                        <div class="form-group">
                            <label>회사 형태</label>
                            <div class="company-type-selector">
                                <div class="type-option selected" onclick="selectCompanyType('headquarters')">
                                    <input type="radio" name="company_type" value="headquarters" checked>
                                    <div class="type-icon">🏢</div>
                                    <div class="type-title">본사</div>
                                    <div class="type-description">독립적인 회사 또는 그룹의 본사</div>
                                </div>
                                <div class="type-option" onclick="selectCompanyType('branch')">
                                    <input type="radio" name="company_type" value="branch">
                                    <div class="type-icon">🏪</div>
                                    <div class="type-title">지점</div>    
                                    <div class="type-description">본사의 지점 또는 사업소</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 대표자 정보 -->
                    <div class="form-section">
                        <div class="section-title">👤 대표자 정보</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="representative_name">대표자명 <span class="required">*</span></label>
                                <input type="text" id="representative_name" name="representative_name" class="form-control" required>
                                <div class="error-message" id="representative_name_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="representative_phone">대표 전화번호</label>
                                <input type="tel" id="representative_phone" name="representative_phone" class="form-control" placeholder="000-0000-0000">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 관리자 정보 -->
                    <div class="form-section">
                        <div class="section-title">⚙️ 시스템 관리자 정보</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="admin_email">관리자 이메일 <span class="required">*</span></label>
                                <input type="email" id="admin_email" name="admin_email" class="form-control" required>
                                <div class="help-text">시스템 접속에 사용할 이메일 주소</div>
                                <div class="error-message" id="admin_email_error"></div>
                            </div>
                            
                            <div class="form-group">
                                <label for="admin_phone">관리자 전화번호</label>
                                <input type="tel" id="admin_phone" name="admin_phone" class="form-control" placeholder="000-0000-0000">
                            </div>
                        </div>
                    </div>
                    
                    <!-- 주소 정보 -->
                    <div class="form-section">
                        <div class="section-title">📍 회사 주소</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="postal_code">우편번호</label>
                                <input type="text" id="postal_code" name="postal_code" class="form-control" placeholder="00000">
                            </div>
                            
                            <div class="form-group">
                                <label for="establishment_date">설립일자</label>
                                <input type="date" id="establishment_date" name="establishment_date" class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">주소</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="상세 주소를 입력하세요">
                        </div>
                    </div>
                    
                    <!-- 연락처 정보 -->
                    <div class="form-section">
                        <div class="section-title">📞 연락처 정보</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">대표 전화번호</label>
                                <input type="tel" id="phone" name="phone" class="form-control" placeholder="000-0000-0000">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">대표 이메일</label>
                                <input type="email" id="email" name="email" class="form-control" placeholder="info@company.com">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="website">웹사이트</label>
                            <input type="url" id="website" name="website" class="form-control" placeholder="https://www.company.com">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-register" id="submitBtn">
                        <span class="loading-spinner" id="loadingSpinner"></span>
                        <span id="submitText">회사 등록하기</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // 회사 형태 선택
        function selectCompanyType(type) {
            document.querySelectorAll('.type-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[value="${type}"]`).checked = true;
        }

        // 폼 제출 처리
        document.getElementById('companyRegisterForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('submitBtn');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const submitText = document.getElementById('submitText');
            const successMessage = document.getElementById('successMessage');
            
            // 로딩 시작
            submitBtn.disabled = true;
            loadingSpinner.style.display = 'inline-block';
            submitText.textContent = '등록 중...';
            
            // 에러 메시지 초기화
            clearErrors();
            
            try {
                // 폼 데이터 수집
                const formData = new FormData(this);
                const data = Object.fromEntries(formData);
                
                // API 요청
                const response = await fetch('/BPM/api/company.php/register', {
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
                    
                    // 3초 후 로그인 페이지로 이동
                    setTimeout(() => {
                        window.location.href = '/BPM/login.php';
                    }, 3000);
                } else {
                    // 오류 처리
                    if (result.errors) {
                        showErrors(result.errors);
                    } else {
                        alert('등록 중 오류가 발생했습니다: ' + result.message);
                    }
                }
            } catch (error) {
                console.error('Registration error:', error);
                alert('등록 중 오류가 발생했습니다. 다시 시도해주세요.');
            } finally {
                // 로딩 종료
                submitBtn.disabled = false;
                loadingSpinner.style.display = 'none';
                submitText.textContent = '회사 등록하기';
            }
        });

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

        // 사업자등록번호 형식 자동 설정
        document.getElementById('tax_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length >= 3) {
                value = value.slice(0, 3) + '-' + value.slice(3);
            }
            if (value.length >= 6) {
                value = value.slice(0, 6) + '-' + value.slice(6);
            }
            if (value.length > 12) {
                value = value.slice(0, 12);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>