<?php
// 📁 C:\xampp\htdocs\BPM\core\Auth.php
// Create at 2508030900 Ver1.00

namespace BPM\Core;

/**
 * BPM 인증 관리 클래스
 * JWT 토큰 기반 인증, 세션 관리, 로그인/로그아웃 처리
 * 기존 Security.php와 연동하여 보안 강화
 */
class Auth
{
    private static $instance = null;
    private $security;
    private $database;
    
    // 세션 키 상수
    const SESSION_USER_ID = 'user_id';
    const SESSION_USER_EMAIL = 'user_email';
    const SESSION_USER_NAME = 'user_name';
    const SESSION_USER_ROLE = 'user_role';
    const SESSION_COMPANY_ID = 'company_id';
    const SESSION_JWT_TOKEN = 'jwt_token';
    const SESSION_LAST_ACTIVITY = 'last_activity';
    
    // JWT 토큰 만료 시간 (초)
    const TOKEN_EXPIRY = 3600; // 1시간
    const REFRESH_TOKEN_EXPIRY = 2592000; // 30일
    const REMEMBER_TOKEN_EXPIRY = 2592000; // 30일
    
    private function __construct()
    {
        $this->security = Security::getInstance();
        $this->database = Database::getInstance();
        
        // 세션 시작 (아직 시작되지 않은 경우)
        if (session_status() === PHP_SESSION_NONE) {
            $this->startSecureSession();
        }
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 보안 세션 시작
     */
    private function startSecureSession(): void
    {
        // 보안 세션 설정
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', defined('HTTPS_ENABLED') && HTTPS_ENABLED ? 1 : 0);
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.use_strict_mode', 1);
        
        session_start();
        
        // 세션 하이재킹 방지 - IP 및 User-Agent 검증
        $this->validateSessionSecurity();
    }
    
    /**
     * 세션 보안 검증
     */
    private function validateSessionSecurity(): void
    {
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
        $currentUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (isset($_SESSION['session_ip']) && isset($_SESSION['session_user_agent'])) {
            if ($_SESSION['session_ip'] !== $currentIP || 
                $_SESSION['session_user_agent'] !== $currentUserAgent) {
                
                $this->security->auditLog('session_hijack_attempt', [
                    'original_ip' => $_SESSION['session_ip'],
                    'current_ip' => $currentIP,
                    'original_user_agent' => $_SESSION['session_user_agent'],
                    'current_user_agent' => $currentUserAgent
                ]);
                
                $this->logout();
                return;
            }
        } else {
            $_SESSION['session_ip'] = $currentIP;
            $_SESSION['session_user_agent'] = $currentUserAgent;
        }
        
        // 세션 만료 검증
        $this->validateSessionExpiry();
    }
    
    /**
     * 세션 만료 검증
     */
    private function validateSessionExpiry(): void
    {
        $maxInactivity = 3600; // 1시간 비활성
        
        if (isset($_SESSION[self::SESSION_LAST_ACTIVITY])) {
            $inactiveTime = time() - $_SESSION[self::SESSION_LAST_ACTIVITY];
            
            if ($inactiveTime > $maxInactivity) {
                $this->security->auditLog('session_expired', [
                    'user_id' => $_SESSION[self::SESSION_USER_ID] ?? null,
                    'inactive_time' => $inactiveTime
                ]);
                
                $this->logout();
                return;
            }
        }
        
        $_SESSION[self::SESSION_LAST_ACTIVITY] = time();
    }
    
    /**
     * 사용자 로그인 처리
     */
    public function login(string $email, string $password, bool $remember = false): array
    {
        try {
            // 입력값 검증 및 정제
            $email = $this->security->sanitizeInput($email, 'email');
            $password = trim($password);
            
            if (empty($email) || empty($password)) {
                return $this->createErrorResponse('이메일과 비밀번호를 입력해주세요.');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->createErrorResponse('유효한 이메일 주소를 입력해주세요.');
            }
            
            // Rate Limiting 검증
            if (!$this->security->checkRateLimit("login_$email", 5, 900)) { // 15분 동안 5회
                return $this->createErrorResponse('로그인 시도 횟수가 초과되었습니다. 15분 후 다시 시도해주세요.');
            }
            
            // 사용자 정보 조회
            $user = $this->getUserByEmail($email);
            
            if (!$user) {
                $this->security->auditLog('login_failed_user_not_found', ['email' => $email]);
                return $this->createErrorResponse('이메일 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 비밀번호 검증
            if (!$this->security->verifyPassword($password, $user['password'])) {
                $this->security->auditLog('login_failed_wrong_password', [
                    'user_id' => $user['id'],
                    'email' => $email
                ]);
                return $this->createErrorResponse('이메일 또는 비밀번호가 올바르지 않습니다.');
            }
            
            // 계정 상태 확인
            if ($user['status'] !== 'active') {
                $this->security->auditLog('login_failed_inactive_account', [
                    'user_id' => $user['id'],
                    'status' => $user['status']
                ]);
                return $this->createErrorResponse('비활성화된 계정입니다. 관리자에게 문의하세요.');
            }
            
            // 로그인 성공 - 세션 및 토큰 생성
            $this->createUserSession($user);
            $jwt_token = $this->createJWTToken($user);
            
            // Remember Me 처리
            if ($remember) {
                $this->setRememberToken($user);
            }
            
            // 마지막 로그인 시간 업데이트
            $this->updateLastLogin($user['id']);
            
            $this->security->auditLog('login_success', [
                'user_id' => $user['id'],
                'email' => $email,
                'remember' => $remember
            ]);
            
            return [
                'success' => true,
                'message' => '로그인에 성공했습니다.',
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'company_id' => $user['company_id']
                ],
                'token' => $jwt_token,
                'expires_in' => self::TOKEN_EXPIRY
            ];
            
        } catch (\Exception $e) {
            BPMLogger::error('로그인 처리 중 오류 발생', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->createErrorResponse('로그인 처리 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 사용자 로그아웃 처리
     */
    public function logout(): bool
    {
        try {
            $user_id = $_SESSION[self::SESSION_USER_ID] ?? null;
            
            // Remember Token 삭제
            if ($user_id) {
                $this->clearRememberToken($user_id);
            }
            
            // 쿠키 삭제
            if (isset($_COOKIE['remember_token'])) {
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            }
            
            // 세션 데이터 모두 삭제
            $_SESSION = [];
            
            // 세션 쿠키 삭제
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // 세션 파괴
            session_destroy();
            
            $this->security->auditLog('logout_success', ['user_id' => $user_id]);
            
            return true;
            
        } catch (\Exception $e) {
            BPMLogger::error('로그아웃 처리 중 오류 발생', [
                'user_id' => $user_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * 로그인 상태 확인
     */
    public function isLoggedIn(): bool
    {
        if (!isset($_SESSION[self::SESSION_USER_ID]) || 
            !isset($_SESSION[self::SESSION_JWT_TOKEN])) {
            return false;
        }
        
        // JWT 토큰 검증
        $token = $_SESSION[self::SESSION_JWT_TOKEN];
        $payload = $this->security->verifyJWTToken($token);
        
        if (!$payload) {
            $this->logout();
            return false;
        }
        
        // 세션 보안 검증
        $this->validateSessionSecurity();
        
        return isset($_SESSION[self::SESSION_USER_ID]);
    }
    
    /**
     * JWT 토큰 갱신
     */
    public function refreshToken(): array
    {
        if (!$this->isLoggedIn()) {
            return $this->createErrorResponse('인증이 필요합니다.');
        }
        
        try {
            $user_id = $_SESSION[self::SESSION_USER_ID];
            $user = $this->getUserById($user_id);
            
            if (!$user) {
                $this->logout();
                return $this->createErrorResponse('사용자 정보를 찾을 수 없습니다.');
            }
            
            // 새 JWT 토큰 생성
            $new_token = $this->createJWTToken($user);
            $_SESSION[self::SESSION_JWT_TOKEN] = $new_token;
            
            return [
                'success' => true,
                'message' => '토큰이 갱신되었습니다.',
                'token' => $new_token,
                'expires_in' => self::TOKEN_EXPIRY
            ];
            
        } catch (\Exception $e) {
            BPMLogger::error('토큰 갱신 중 오류 발생', [
                'user_id' => $_SESSION[self::SESSION_USER_ID] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return $this->createErrorResponse('토큰 갱신 중 오류가 발생했습니다.');
        }
    }
    
    /**
     * 현재 사용자 정보 반환
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION[self::SESSION_USER_ID] ?? null,
            'email' => $_SESSION[self::SESSION_USER_EMAIL] ?? null,
            'name' => $_SESSION[self::SESSION_USER_NAME] ?? null,
            'role' => $_SESSION[self::SESSION_USER_ROLE] ?? null,
            'company_id' => $_SESSION[self::SESSION_COMPANY_ID] ?? null
        ];
    }
    
    /**
     * Remember Token으로 자동 로그인
     */
    public function attemptRememberLogin(): bool
    {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        try {
            $token = $_COOKIE['remember_token'];
            $user = $this->getUserByRememberToken($token);
            
            if (!$user) {
                // 유효하지 않은 토큰 삭제
                setcookie('remember_token', '', time() - 3600, '/', '', false, true);
                return false;
            }
            
            // 자동 로그인 성공
            $this->createUserSession($user);
            $jwt_token = $this->createJWTToken($user);
            
            $this->security->auditLog('auto_login_success', [
                'user_id' => $user['id'],
                'method' => 'remember_token'
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            BPMLogger::error('Remember 로그인 실패', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    // ========== Private Helper Methods ==========
    
    /**
     * 사용자 세션 생성
     */
    private function createUserSession(array $user): void
    {
        $_SESSION[self::SESSION_USER_ID] = $user['id'];
        $_SESSION[self::SESSION_USER_EMAIL] = $user['email'];
        $_SESSION[self::SESSION_USER_NAME] = $user['name'];
        $_SESSION[self::SESSION_USER_ROLE] = $user['role'];
        $_SESSION[self::SESSION_COMPANY_ID] = $user['company_id'];
        $_SESSION[self::SESSION_LAST_ACTIVITY] = time();
        
        // 세션 재생성 (세션 고정 공격 방지)
        session_regenerate_id(true);
    }
    
    /**
     * JWT 토큰 생성
     */
    private function createJWTToken(array $user): string
    {
        $payload = [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
            'company_id' => $user['company_id']
        ];
        
        $token = $this->security->createJWTToken($payload, self::TOKEN_EXPIRY);
        $_SESSION[self::SESSION_JWT_TOKEN] = $token;
        
        return $token;
    }
    
    /**
     * Remember Token 설정
     */
    private function setRememberToken(array $user): void
    {
        $token = bin2hex(random_bytes(32));
        $expires = time() + self::REMEMBER_TOKEN_EXPIRY;
        
        // DB에 토큰 저장
        $stmt = $this->database->prepare("
            UPDATE bpm_users 
            SET remember_token = ?, remember_expires = ? 
            WHERE id = ?
        ");
        $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $user['id']]);
        
        // 쿠키 설정
        setcookie('remember_token', $token, $expires, '/', '', false, true);
    }
    
    /**
     * Remember Token 삭제
     */
    private function clearRememberToken(string $user_id): void
    {
        $stmt = $this->database->prepare("
            UPDATE bpm_users 
            SET remember_token = NULL, remember_expires = NULL 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
    }
    
    /**
     * 이메일로 사용자 조회 (회사 정보 포함)
     */
    private function getUserByEmail(string $email): ?array
    {
        $stmt = $this->database->prepare("
            SELECT 
                u.id, u.email, u.name, u.password, u.status, u.created_at,
                cu.role_type as role, cu.company_id, cu.status as company_status
            FROM bpm_users u
            LEFT JOIN bpm_company_users cu ON u.id = cu.user_id AND cu.is_active = TRUE
            WHERE u.email = ? AND u.deleted_at IS NULL
        ");
        $stmt->execute([$email]);
        
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }
    
    /**
     * ID로 사용자 조회 (회사 정보 포함)
     */
    private function getUserById(string $user_id): ?array
    {
        $stmt = $this->database->prepare("
            SELECT 
                u.id, u.email, u.name, u.status,
                cu.role_type as role, cu.company_id, cu.status as company_status
            FROM bpm_users u
            LEFT JOIN bpm_company_users cu ON u.id = cu.user_id AND cu.is_active = TRUE
            WHERE u.id = ? AND u.deleted_at IS NULL
        ");
        $stmt->execute([$user_id]);
        
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }
    
    /**
     * Remember Token으로 사용자 조회 (회사 정보 포함)
     */
    private function getUserByRememberToken(string $token): ?array
    {
        $stmt = $this->database->prepare("
            SELECT 
                u.id, u.email, u.name, u.status,
                cu.role_type as role, cu.company_id, cu.status as company_status
            FROM bpm_users u
            LEFT JOIN bpm_company_users cu ON u.id = cu.user_id AND cu.is_active = TRUE
            WHERE u.remember_token = ? AND u.remember_expires > NOW() AND u.deleted_at IS NULL
        ");
        $stmt->execute([$token]);
        
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $user ?: null;
    }
    
    /**
     * 마지막 로그인 시간 업데이트
     */
    private function updateLastLogin(string $user_id): void
    {
        $stmt = $this->database->prepare("
            UPDATE bpm_users 
            SET last_login_at = NOW(), login_count = login_count + 1 
            WHERE id = ?
        ");
        $stmt->execute([$user_id]);
    }
    
    /**
     * 오류 응답 생성
     */
    private function createErrorResponse(string $message): array
    {
        return [
            'success' => false,
            'message' => $message
        ];
    }
}