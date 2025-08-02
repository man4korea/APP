<?php
// 📁 C:\xampp\htdocs\BPM\core\bootstrap.php
// Create at 2508022045 Ver1.00

/**
 * BPM 애플리케이션 부트스트랩 파일
 * 시스템 초기화, 오토로더 설정, 기본 설정 로드
 */

// 시작 시간 기록 (성능 측정용)
define('BPM_START_TIME', microtime(true));

// 기본 경로 설정
define('BPM_ROOT_PATH', dirname(__DIR__));
define('BPM_CORE_PATH', BPM_ROOT_PATH . '/core');
define('BPM_MODULES_PATH', BPM_ROOT_PATH . '/modules');
define('BPM_SHARED_PATH', BPM_ROOT_PATH . '/shared');
define('BPM_UPLOADS_PATH', BPM_ROOT_PATH . '/uploads');
define('BPM_LOGS_PATH', BPM_ROOT_PATH . '/logs');
define('BPM_CACHE_PATH', BPM_ROOT_PATH . '/cache');

// 필수 디렉토리 생성
$requiredDirs = [
    BPM_LOGS_PATH,
    BPM_CACHE_PATH,
    BPM_UPLOADS_PATH,
    BPM_UPLOADS_PATH . '/temp',
    BPM_UPLOADS_PATH . '/documents',
    BPM_UPLOADS_PATH . '/images',
    BPM_UPLOADS_PATH . '/avatars'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Composer 오토로더 (존재하는 경우)
$composerAutoload = BPM_ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// 커스텀 오토로더 등록
spl_autoload_register(function ($className) {
    // BPM 네임스페이스 처리
    if (strpos($className, 'BPM\\') === 0) {
        $relativePath = str_replace('BPM\\', '', $className);
        $relativePath = str_replace('\\', '/', $relativePath);
        
        // Core 클래스
        if (strpos($className, 'BPM\\Core\\') === 0) {
            $file = BPM_CORE_PATH . '/' . str_replace('BPM\\Core\\', '', $relativePath) . '.php';
        }
        // Modules 클래스
        elseif (strpos($className, 'BPM\\Modules\\') === 0) {
            $file = BPM_MODULES_PATH . '/' . str_replace('BPM\\Modules\\', '', $relativePath) . '.php';
        }
        // Shared 클래스
        elseif (strpos($className, 'BPM\\Shared\\') === 0) {
            $file = BPM_SHARED_PATH . '/' . str_replace('BPM\\Shared\\', '', $relativePath) . '.php';
        }
        // 기본 BPM 클래스
        else {
            $file = BPM_ROOT_PATH . '/src/' . $relativePath . '.php';
        }
        
        if (isset($file) && file_exists($file)) {
            require_once $file;
        }
    }
});

// 설정 파일 로드 (이미 config.php에서 로드됨)
// 이 시점에서 config.php가 이미 포함되어 있어야 함

// 보안 헤더 설정
if (class_exists('BPM\\Core\\Security')) {
    BPM\Core\Security::getInstance()->setSecurityHeaders();
}

// 에러 핸들링 초기화
ini_set('log_errors', 1);
ini_set('error_log', BPM_LOGS_PATH . '/php_errors.log');

// 세션 설정 강화
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_lifetime', SESSION_LIFETIME);
    
    session_start();
}

// 시간대 검증
if (!date_default_timezone_get()) {
    date_default_timezone_set('Asia/Seoul');
}

// 메모리 제한 설정 (필요시)
ini_set('memory_limit', '256M');

// 버퍼링 시작
if (!ob_get_level()) {
    ob_start();
}

/**
 * 애플리케이션 유틸리티 함수들
 */

/**
 * 환경 변수 가져오기
 */
function env(string $key, $default = null) {
    return $_ENV[$key] ?? $default;
}

/**
 * 설정값 가져오기
 */
function config(string $key, $default = null) {
    static $config = [];
    
    if (empty($config)) {
        // 여기서 설정 파일들을 로드할 수 있음
        $config = [
            'app.name' => APP_NAME,
            'app.version' => APP_VERSION,
            'app.env' => APP_ENV,
            'app.debug' => APP_DEBUG,
            'app.url' => APP_URL,
            'db.host' => DB_HOST,
            'db.database' => DB_DATABASE,
            'db.username' => DB_USERNAME,
            'upload.max_size' => UPLOAD_MAX_FILESIZE,
            'upload.allowed_types' => ALLOWED_FILE_TYPES,
        ];
    }
    
    return $config[$key] ?? $default;
}

/**
 * 경로 생성 헬퍼
 */
function asset_path(string $path): string {
    return APP_URL . '/assets/' . ltrim($path, '/');
}

function upload_path(string $path = ''): string {
    return APP_URL . '/uploads/' . ltrim($path, '/');
}

function module_path(string $module, string $path = ''): string {
    return APP_URL . '/modules/' . $module . '/' . ltrim($path, '/');
}

/**
 * URL 생성 헬퍼
 */
function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}

function api_url(string $path = ''): string {
    return APP_URL . '/api/' . ltrim($path, '/');
}

/**
 * 뷰 렌더링 헬퍼
 */
function view(string $template, array $data = []): string {
    extract($data);
    
    $templateFile = BPM_ROOT_PATH . '/views/' . str_replace('.', '/', $template) . '.php';
    
    if (!file_exists($templateFile)) {
        throw new Exception("템플릿 파일을 찾을 수 없습니다: {$template}");
    }
    
    ob_start();
    include $templateFile;
    return ob_get_clean();
}

/**
 * 리다이렉트 헬퍼
 */
function redirect(string $url, int $status = 302): void {
    header("Location: $url", true, $status);
    exit;
}

/**
 * 현재 사용자 정보 헬퍼
 */
function current_user(): ?array {
    if (!AuthManager::isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => AuthManager::getCurrentUserId(),
        'role' => AuthManager::getCurrentUserRole(),
        'company_id' => TenantManager::getCurrentCompanyId()
    ];
}

/**
 * 권한 체크 헬퍼
 */
function can(string $permission): bool {
    $user = current_user();
    if (!$user) {
        return false;
    }
    
    // 간단한 권한 체크 로직
    switch ($permission) {
        case 'admin':
            return in_array($user['role'], ['founder', 'admin']);
        case 'manage_processes':
            return in_array($user['role'], ['founder', 'admin', 'process_owner']);
        case 'view_tasks':
            return in_array($user['role'], ['founder', 'admin', 'process_owner', 'member']);
        default:
            return false;
    }
}

/**
 * 캐시 헬퍼
 */
function cache_get(string $key, $default = null) {
    $cacheFile = BPM_CACHE_PATH . '/' . md5($key) . '.cache';
    
    if (!file_exists($cacheFile)) {
        return $default;
    }
    
    $data = unserialize(file_get_contents($cacheFile));
    
    if ($data['expires'] > 0 && $data['expires'] < time()) {
        unlink($cacheFile);
        return $default;
    }
    
    return $data['value'];
}

function cache_put(string $key, $value, int $ttl = 3600): void {
    $cacheFile = BPM_CACHE_PATH . '/' . md5($key) . '.cache';
    $data = [
        'value' => $value,
        'expires' => $ttl > 0 ? time() + $ttl : 0
    ];
    
    file_put_contents($cacheFile, serialize($data), LOCK_EX);
}

function cache_forget(string $key): void {
    $cacheFile = BPM_CACHE_PATH . '/' . md5($key) . '.cache';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * 디버그 헬퍼
 */
function dd(...$vars): void {
    if (!APP_DEBUG) {
        return;
    }
    
    echo '<pre style="background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px; font-family: monospace;">';
    foreach ($vars as $var) {
        var_dump($var);
        echo "\n" . str_repeat('-', 50) . "\n";
    }
    echo '</pre>';
    exit;
}

/**
 * 다국어 지원 헬퍼 (기본 구현)
 */
function __(string $key, array $replace = []): string {
    static $translations = [];
    
    if (empty($translations)) {
        $lang = getCurrentLanguage();
        $langFile = BPM_ROOT_PATH . "/lang/{$lang}.php";
        
        if (file_exists($langFile)) {
            $translations = include $langFile;
        }
    }
    
    $text = $translations[$key] ?? $key;
    
    foreach ($replace as $search => $replacement) {
        $text = str_replace(":$search", $replacement, $text);
    }
    
    return $text;
}

// 시스템 준비 완료 로그
BPMLogger::info('BPM System Bootstrap Completed', [
    'execution_time' => round((microtime(true) - BPM_START_TIME) * 1000, 2) . 'ms',
    'memory_usage' => round(memory_get_usage() / 1024 / 1024, 2) . 'MB',
    'php_version' => PHP_VERSION,
    'environment' => APP_ENV
]);

// 개발 모드에서 성능 정보 출력
if (APP_DEBUG) {
    register_shutdown_function(function() {
        $executionTime = round((microtime(true) - BPM_START_TIME) * 1000, 2);
        $memoryUsage = round(memory_get_peak_usage() / 1024 / 1024, 2);
        
        echo "<!-- BPM Debug Info: Execution Time: {$executionTime}ms, Memory Usage: {$memoryUsage}MB -->";
    });
}