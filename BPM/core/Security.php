<?php
// 📁 C:\xampp\htdocs\BPM\core\Security.php
// Create at 2508022040 Ver1.00

namespace BPM\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

/**
 * BPM 보안 관리 클래스
 * JWT 토큰, CSRF 보호, 암호화, 권한 관리 등 포괄적인 보안 기능 제공
 */
class Security
{
    private static $instance = null;
    private $jwtSecret;
    private $csrfTokenLifetime;
    
    private function __construct()
    {
        $this->jwtSecret = JWT_SECRET;
        $this->csrfTokenLifetime = CSRF_TOKEN_LIFETIME;
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * JWT 토큰 생성
     */
    public function createJWTToken(array $payload, int $expiry = 3600): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $expiry;
        
        $payload = array_merge($payload, [
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'iss' => APP_URL,
            'aud' => APP_URL
        ]);
        
        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }
    
    /**
     * JWT 토큰 검증 및 디코드
     */
    public function verifyJWTToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            BPMLogger::warning('JWT 토큰 만료', ['token' => substr($token, 0, 20) . '...']);
            return null;
        } catch (\Exception $e) {
            BPMLogger::error('JWT 토큰 검증 실패', [
                'error' => $e->getMessage(),
                'token' => substr($token, 0, 20) . '...'
            ]);
            return null;
        }
    }
    
    /**
     * CSRF 토큰 생성
     */
    public function generateCSRFToken(): string
    {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > $this->csrfTokenLifetime) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF 토큰 검증
     */
    public function verifyCSRFToken(string $token): bool
    {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > $this->csrfTokenLifetime) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 비밀번호 해시 생성 (Argon2ID 사용)
     */
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,  // 64MB
            'time_cost' => 4,        // 4 iterations
            'threads' => 3           // 3 threads
        ]);
    }
    
    /**
     * 비밀번호 검증
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * 데이터 암호화 (AES-256-GCM)
     */
    public function encrypt(string $data): string
    {
        $key = hash('sha256', APP_KEY, true);
        $iv = random_bytes(16);
        $tag = '';
        
        $encrypted = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($encrypted === false) {
            throw new \Exception('암호화에 실패했습니다.');
        }
        
        return base64_encode($iv . $tag . $encrypted);
    }
    
    /**
     * 데이터 복호화
     */
    public function decrypt(string $data): string
    {
        $data = base64_decode($data);
        $key = hash('sha256', APP_KEY, true);
        
        $iv = substr($data, 0, 16);
        $tag = substr($data, 16, 16);
        $encrypted = substr($data, 32);
        
        $decrypted = openssl_decrypt($encrypted, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        
        if ($decrypted === false) {
            throw new \Exception('복호화에 실패했습니다.');
        }
        
        return $decrypted;
    }
    
    /**
     * 입력 데이터 검증 및 정제
     */
    public function sanitizeInput(string $input, string $type = 'string'): string
    {
        $input = trim($input);
        
        switch ($type) {
            case 'email':
                return filter_var($input, FILTER_SANITIZE_EMAIL);
            case 'url':
                return filter_var($input, FILTER_SANITIZE_URL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'html':
                return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            default:
                return htmlspecialchars(strip_tags($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
    }
    
    /**
     * XSS 방지를 위한 HTML 이스케이프
     */
    public function escapeHtml(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * SQL 인젝션 방지를 위한 파라미터 바인딩 확인
     */
    public function validateSQLParams(array $params): bool
    {
        foreach ($params as $param) {
            if (is_string($param) && preg_match('/[\'";]|--|\/*|\*\//', $param)) {
                BPMLogger::warning('의심스러운 SQL 파라미터 감지', ['param' => $param]);
                return false;
            }
        }
        return true;
    }
    
    /**
     * 파일 업로드 보안 검증
     */
    public function validateFileUpload(array $file): array
    {
        $errors = [];
        
        // 파일 크기 검증
        $maxSize = $this->parseFileSize(UPLOAD_MAX_FILESIZE);
        if ($file['size'] > $maxSize) {
            $errors[] = "파일 크기가 너무 큽니다. 최대 " . UPLOAD_MAX_FILESIZE . " 까지 업로드 가능합니다.";
        }
        
        // 파일 확장자 검증
        $allowedTypes = explode(',', ALLOWED_FILE_TYPES);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = "허용되지 않는 파일 형식입니다. 허용된 형식: " . ALLOWED_FILE_TYPES;
        }
        
        // MIME 타입 검증
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'zip' => 'application/zip'
        ];
        
        if (!isset($allowedMimes[$fileExtension]) || $mimeType !== $allowedMimes[$fileExtension]) {
            $errors[] = "파일 내용이 확장자와 일치하지 않습니다.";
        }
        
        return $errors;
    }
    
    /**
     * API 요청 제한 (Rate Limiting)
     */
    public function checkRateLimit(string $identifier, int $limit = 100, int $window = 3600): bool
    {
        $key = "rate_limit:" . $identifier;
        $current = $_SESSION[$key] ?? ['count' => 0, 'reset_time' => time() + $window];
        
        if (time() > $current['reset_time']) {
            $current = ['count' => 0, 'reset_time' => time() + $window];
        }
        
        $current['count']++;
        $_SESSION[$key] = $current;
        
        if ($current['count'] > $limit) {
            BPMLogger::warning('Rate limit exceeded', [
                'identifier' => $identifier,
                'count' => $current['count'],
                'limit' => $limit
            ]);
            return false;
        }
        
        return true;
    }
    
    /**
     * 보안 헤더 설정
     */
    public function setSecurityHeaders(): void
    {
        // XSS 보호
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Type Sniffing 방지
        header('X-Content-Type-Options: nosniff');
        
        // Clickjacking 방지
        header('X-Frame-Options: DENY');
        
        // HTTPS 강제 (프로덕션 환경)
        if (APP_ENV === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:;");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
    
    /**
     * 파일 크기 문자열을 바이트로 변환
     */
    private function parseFileSize(string $size): int
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);
        
        switch ($unit) {
            case 'G':
                return $value * 1024 * 1024 * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'K':
                return $value * 1024;
            default:
                return (int) $size;
        }
    }
    
    /**
     * 보안 감사 로그 기록
     */
    public function auditLog(string $action, array $context = []): void
    {
        $auditData = [
            'action' => $action,
            'user_id' => $_SESSION['user_id'] ?? 'anonymous',
            'company_id' => $_SESSION['company_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s'),
            'context' => $context
        ];
        
        BPMLogger::info("Security Audit: $action", $auditData);
    }
}