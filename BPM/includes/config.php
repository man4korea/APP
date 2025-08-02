<?php
// 📁 C:\xampp\htdocs\BPM\includes\config.php
// Create at 2508022035 Ver1.00

/**
 * BPM Total Business Process Management 설정 파일
 * 멀티테넌트 환경과 고급 보안 기능을 지원하는 통합 설정
 */

// 세션 시작 (보안 강화)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 7200, // 2시간
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// .env 파일에서 환경 변수 로드
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // 따옴표 제거
        $value = trim($value, '"\'');
        
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }
    }
}

// 환경 변수에서 상수 정의
define('APP_NAME', $_ENV['APP_NAME'] ?? 'BPM Total Business Process Management');
define('APP_VERSION', $_ENV['APP_VERSION'] ?? '1.0.0');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/BPM');

// 데이터베이스 설정
define('DB_CONNECTION', $_ENV['DB_CONNECTION'] ?? 'mysql');
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_PORT', $_ENV['DB_PORT'] ?? 3306);
define('DB_DATABASE', $_ENV['DB_DATABASE'] ?? 'bpm_database');
define('DB_USERNAME', $_ENV['DB_USERNAME'] ?? 'root');
define('DB_PASSWORD', $_ENV['DB_PASSWORD'] ?? '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

// 보안 설정
define('APP_KEY', $_ENV['APP_KEY'] ?? 'BPM_SECRET_KEY_2025_CHANGE_THIS');
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? 'JWT_SECRET_KEY_FOR_BPM_SYSTEM');
define('CSRF_TOKEN_LIFETIME', intval($_ENV['CSRF_TOKEN_LIFETIME'] ?? 3600));
define('SESSION_LIFETIME', intval($_ENV['SESSION_LIFETIME'] ?? 7200));

// 멀티테넌트 설정
define('DEFAULT_COMPANY_SETTINGS', $_ENV['DEFAULT_COMPANY_SETTINGS'] ?? '{"admin_can_assign_admin": true, "approval_required": false}');
define('MAX_PROCESSES_PER_COMPANY', intval($_ENV['MAX_PROCESSES_PER_COMPANY'] ?? 1000));
define('MIN_ADMIN_COUNT', intval($_ENV['MIN_ADMIN_COUNT'] ?? 1));

// 파일 업로드 설정
define('UPLOAD_MAX_FILESIZE', $_ENV['UPLOAD_MAX_FILESIZE'] ?? '10M');
define('UPLOAD_PATH', $_ENV['UPLOAD_PATH'] ?? '/uploads');
define('ALLOWED_FILE_TYPES', $_ENV['ALLOWED_FILE_TYPES'] ?? 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,zip');

// 로그 설정
define('LOG_CHANNEL', $_ENV['LOG_CHANNEL'] ?? 'file');
define('LOG_LEVEL', $_ENV['LOG_LEVEL'] ?? 'debug');
define('LOG_PATH', $_ENV['LOG_PATH'] ?? 'logs');

// 타임존 설정
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Seoul');

// 에러 리포팅 설정
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE);
}

/**
 * 데이터베이스 연결 클래스 (싱글톤 패턴)
 */
class DatabaseConnection {
    private static $instance = null;
    private $pdo = null;
    
    private function __construct() {
        try {
            $dsn = DB_CONNECTION . ":host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_DATABASE . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
            
            // 멀티테넌트 설정
            $this->pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (PDOException $e) {
            BPMLogger::error("데이터베이스 연결 실패", ['error' => $e->getMessage()]);
            if (APP_DEBUG) {
                throw $e;
            }
            throw new Exception("데이터베이스 연결에 실패했습니다.");
        }
    }
    
    public static function getInstance(): DatabaseConnection {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection(): PDO {
        return $this->pdo;
    }
    
    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }
    
    public function commit(): bool {
        return $this->pdo->commit();
    }
    
    public function rollback(): bool {
        return $this->pdo->rollback();
    }
}

/**
 * 편의 함수: 데이터베이스 연결 가져오기
 */
function getDBConnection(): PDO {
    return DatabaseConnection::getInstance()->getConnection();
}

/**
 * 보안 유틸리티 클래스
 */
class SecurityUtils {
    
    /**
     * 안전한 HTML 출력
     */
    public static function escape(string $string): string {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
    
    /**
     * CSRF 토큰 생성
     */
    public static function generateCSRFToken(): string {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
            
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF 토큰 검증
     */
    public static function verifyCSRFToken(string $token): bool {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) ||
            (time() - $_SESSION['csrf_token_time']) > CSRF_TOKEN_LIFETIME) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * 비밀번호 해시 생성
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }
    
    /**
     * 비밀번호 검증
     */
    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }
    
    /**
     * JWT 토큰 생성 (기본 구현)
     */
    public static function createJWT(array $payload, int $expiry = 3600): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expiry;
        $payload['iat'] = time();
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }
    
    /**
     * 입력 데이터 정제
     */
    public static function sanitizeInput(string $input): string {
        $input = trim($input);
        $input = stripslashes($input);
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * 멀티테넌트 관리 클래스
 */
class TenantManager {
    
    /**
     * 현재 사용자의 회사 ID 가져오기
     */
    public static function getCurrentCompanyId(): ?string {
        return $_SESSION['company_id'] ?? null;
    }
    
    /**
     * 사용자 권한 확인
     */
    public static function checkUserPermission(string $userId, string $companyId, string $requiredRole): bool {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT bmp_check_user_permission(?, ?, ?) as has_permission
        ");
        $stmt->execute([$userId, $companyId, $requiredRole]);
        $result = $stmt->fetch();
        
        return $result && $result['has_permission'] == 1;
    }
    
    /**
     * 회사별 데이터 격리를 위한 WHERE 절 추가
     */
    public static function addTenantFilter(string $sql, string $tableAlias = ''): string {
        $companyId = self::getCurrentCompanyId();
        if (!$companyId) {
            throw new Exception("회사 정보가 없습니다.");
        }
        
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        $filter = " AND {$prefix}company_id = '$companyId'";
        
        // WHERE 절이 있는지 확인
        if (stripos($sql, 'WHERE') !== false) {
            return str_ireplace('WHERE', "WHERE{$filter} AND", $sql);
        } else {
            return $sql . " WHERE{$filter}";
        }
    }
}

/**
 * 로깅 클래스
 */
class BPMLogger {
    
    public static function log(string $level, string $message, array $context = []): void {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $logMessage = "[$timestamp] [$level] $message $contextStr" . PHP_EOL;
        
        $logDir = __DIR__ . "/../" . LOG_PATH . "/";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . date('Y-m-d') . '.log';
        error_log($logMessage, 3, $logFile);
    }
    
    public static function debug(string $message, array $context = []): void {
        if (APP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }
    
    public static function info(string $message, array $context = []): void {
        self::log('INFO', $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void {
        self::log('WARNING', $message, $context);
    }
    
    public static function error(string $message, array $context = []): void {
        self::log('ERROR', $message, $context);
    }
}

/**
 * 사용자 인증 관리 클래스
 */
class AuthManager {
    
    /**
     * 현재 사용자 ID 가져오기
     */
    public static function getCurrentUserId(): ?string {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * 현재 사용자 역할 가져오기
     */
    public static function getCurrentUserRole(): ?string {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * 로그인 상태 확인
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && isset($_SESSION['company_id']);
    }
    
    /**
     * 관리자 권한 확인
     */
    public static function isAdmin(): bool {
        $role = self::getCurrentUserRole();
        return in_array($role, ['founder', 'admin']);
    }
    
    /**
     * 로그인 필수 체크
     */
    public static function requireLogin(): void {
        if (!self::isLoggedIn()) {
            header('Location: ' . APP_URL . '/login.php');
            exit;
        }
    }
    
    /**
     * 관리자 권한 필수 체크
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!self::isAdmin()) {
            http_response_code(403);
            die('관리자 권한이 필요합니다.');
        }
    }
}

/**
 * 응답 헬퍼 클래스
 */
class ResponseHelper {
    
    /**
     * JSON 응답 전송
     */
    public static function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * 성공 응답
     */
    public static function success(array $data = [], string $message = '성공'): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }
    
    /**
     * 에러 응답
     */
    public static function error(string $message, int $status = 400, array $errors = []): void {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }
}

/**
 * 유틸리티 함수들
 */

/**
 * 현재 언어 감지
 */
function getCurrentLanguage(): string {
    return $_SESSION['language'] ?? 'ko';
}

/**
 * 날짜 형식 변환
 */
function formatDate(string $date, ?string $lang = null): string {
    if ($lang === null) {
        $lang = getCurrentLanguage();
    }
    
    $timestamp = strtotime($date);
    if ($lang === 'en') {
        return date('M j, Y', $timestamp);
    } else {
        return date('Y년 m월 d일', $timestamp);
    }
}

/**
 * 파일 크기를 사람이 읽기 쉬운 형태로 변환
 */
function formatFileSize(int $bytes): string {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * UUID v4 생성
 */
function generateUUID(): string {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

// 전역 에러 핸들러 설정
set_error_handler(function($severity, $message, $file, $line) {
    BPMLogger::error("PHP Error", [
        'severity' => $severity,
        'message' => $message,
        'file' => $file,
        'line' => $line
    ]);
    
    if (APP_DEBUG) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
});

// 전역 예외 핸들러 설정
set_exception_handler(function($exception) {
    BPMLogger::error("Uncaught Exception", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    if (APP_DEBUG) {
        echo "<h1>오류가 발생했습니다</h1>";
        echo "<p>메시지: " . $exception->getMessage() . "</p>";
        echo "<p>파일: " . $exception->getFile() . ":" . $exception->getLine() . "</p>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
    } else {
        http_response_code(500);
        echo "시스템 오류가 발생했습니다. 관리자에게 문의하세요.";
    }
});

// 애플리케이션 시작 로그
BPMLogger::info("BPM Application Started", [
    'version' => APP_VERSION,
    'environment' => APP_ENV,
    'timestamp' => date('Y-m-d H:i:s')
]);

?>