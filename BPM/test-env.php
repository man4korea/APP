<?php
// 📁 C:\xampp\htdocs\BPM\test-env.php
// Create at 2508030945 Ver1.00

/**
 * 환경 변수 및 API 키 설정 테스트 파일
 * 개발용 테스트 파일 - 프로덕션에서는 삭제 필요
 */

require_once __DIR__ . '/includes/config.php';

echo "<h1>BPM 환경 설정 테스트</h1>";

echo "<h2>기본 설정</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>상수명</th><th>값</th><th>상태</th></tr>";

$configs = [
    'APP_NAME' => APP_NAME,
    'APP_VERSION' => APP_VERSION,
    'APP_ENV' => APP_ENV,
    'APP_DEBUG' => APP_DEBUG ? 'true' : 'false',
    'APP_URL' => APP_URL,
    'DB_HOST' => DB_HOST,
    'DB_DATABASE' => DB_DATABASE,
    'DB_USERNAME' => DB_USERNAME,
    'HTTPS_ENABLED' => defined('HTTPS_ENABLED') && HTTPS_ENABLED ? 'true' : 'false'
];

foreach ($configs as $name => $value) {
    $status = !empty($value) ? '✅' : '❌';
    echo "<tr><td>$name</td><td>" . htmlspecialchars($value) . "</td><td>$status</td></tr>";
}
echo "</table>";

echo "<h2>API 키 설정</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>API</th><th>키 상태</th><th>키 길이</th></tr>";

$apiKeys = [
    'OpenAI' => OPENAI_API_KEY,
    'Google/Gemini' => GOOGLE_API_KEY,
    'Anthropic Claude' => ANTHROPIC_API_KEY,
    'Tavily Search' => TAVILY_API_KEY,
    'YouTube' => YOUTUBE_API_KEY,
    'Grok' => GROK_API_KEY,
    'Notion' => NOTION_API_KEY,
    'Figma' => FIGMA_API_KEY,
    'GitHub Token' => GITHUB_TOKEN
];

foreach ($apiKeys as $name => $key) {
    $status = !empty($key) ? '✅ 설정됨' : '❌ 없음';
    $length = !empty($key) ? strlen($key) . '자' : '0자';
    echo "<tr><td>$name</td><td>$status</td><td>$length</td></tr>";
}
echo "</table>";

echo "<h2>보안 설정</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>설정</th><th>값</th></tr>";

$securityConfigs = [
    'APP_KEY' => substr(APP_KEY, 0, 20) . '...',
    'JWT_SECRET' => substr(JWT_SECRET, 0, 20) . '...',
    'CSRF_TOKEN_LIFETIME' => CSRF_TOKEN_LIFETIME . '초',
    'SESSION_LIFETIME' => SESSION_LIFETIME . '초'
];

foreach ($securityConfigs as $name => $value) {
    echo "<tr><td>$name</td><td>" . htmlspecialchars($value) . "</td></tr>";
}
echo "</table>";

echo "<h2>GitHub 설정</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>설정</th><th>값</th></tr>";

$githubConfigs = [
    'GITHUB_USERNAME' => GITHUB_USERNAME,
    'GITHUB_REPO' => GITHUB_REPO,
    'GIT_USER_NAME' => GIT_USER_NAME,
    'GIT_USER_EMAIL' => GIT_USER_EMAIL
];

foreach ($githubConfigs as $name => $value) {
    $status = !empty($value) ? $value : '❌ 설정되지 않음';
    echo "<tr><td>$name</td><td>" . htmlspecialchars($status) . "</td></tr>";
}
echo "</table>";

echo "<h2>파일 경로 확인</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>파일</th><th>경로</th><th>존재</th></tr>";

$files = [
    '.env' => __DIR__ . '/.env',
    'config.php' => __DIR__ . '/includes/config.php',
    'Auth.php' => __DIR__ . '/core/Auth.php',
    'Security.php' => __DIR__ . '/core/Security.php',
    'Router.php' => __DIR__ . '/core/Router.php'
];

foreach ($files as $name => $path) {
    $exists = file_exists($path) ? '✅ 존재' : '❌ 없음';
    echo "<tr><td>$name</td><td>" . htmlspecialchars($path) . "</td><td>$exists</td></tr>";
}
echo "</table>";

echo "<p style='color: red; font-weight: bold;'>⚠️ 보안상 이 파일은 테스트 후 반드시 삭제하세요!</p>";
?>