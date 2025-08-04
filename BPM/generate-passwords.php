<?php
// 📁 C:\xampp\htdocs\BPM\generate-passwords.php
// Create at 2508031020 Ver1.00

/**
 * 데모 사용자 비밀번호 해시 생성 스크립트
 * 개발용 도구 - 프로덕션에서는 삭제 필요
 */

require_once __DIR__ . '/includes/config.php';

echo "<h1>BPM 데모 사용자 비밀번호 해시 생성</h1>";

// 비밀번호 해시 생성
$passwords = [
    'admin123' => 'admin@easycorp.com',
    'user123' => 'user@easycorp.com', 
    'owner123' => 'owner@easycorp.com'
];

echo "<h2>생성된 해시값</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>계정</th><th>비밀번호</th><th>해시값</th></tr>";

$updateQueries = [];

foreach ($passwords as $password => $email) {
    $hash = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,  // 64MB
        'time_cost' => 4,        // 4 iterations
        'threads' => 3           // 3 threads
    ]);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($email) . "</td>";
    echo "<td>" . htmlspecialchars($password) . "</td>";
    echo "<td style='font-family: monospace; font-size: 10px;'>" . htmlspecialchars($hash) . "</td>";
    echo "</tr>";
    
    $updateQueries[] = "UPDATE bpm_users SET password = '$hash' WHERE email = '$email';";
}

echo "</table>";

echo "<h2>데이터베이스 업데이트 쿼리</h2>";
echo "<textarea rows='10' cols='100' style='width: 100%; font-family: monospace;'>";
foreach ($updateQueries as $query) {
    echo $query . "\n";
}
echo "</textarea>";

echo "<h2>검증 테스트</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>비밀번호</th><th>해시 검증</th></tr>";

foreach ($passwords as $password => $email) {
    $hash = password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
    
    $verified = password_verify($password, $hash) ? '✅ 성공' : '❌ 실패';
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($password) . "</td>";
    echo "<td>$verified</td>";
    echo "</tr>";
}

echo "</table>";

echo "<p style='color: red; font-weight: bold;'>⚠️ 보안상 이 파일은 테스트 후 반드시 삭제하세요!</p>";

echo "<h2>사용법</h2>";
echo "<ol>";
echo "<li>위의 UPDATE 쿼리를 복사하여 phpMyAdmin이나 MySQL 클라이언트에서 실행</li>";
echo "<li>또는 아래 자동 업데이트 버튼 클릭</li>";
echo "</ol>";

// 자동 업데이트 처리
if (isset($_POST['auto_update'])) {
    try {
        $database = DatabaseConnection::getInstance()->getConnection();
        
        echo "<h3>자동 업데이트 결과</h3>";
        
        foreach ($passwords as $password => $email) {
            $hash = password_hash($password, PASSWORD_ARGON2ID, [
                'memory_cost' => 65536,
                'time_cost' => 4,
                'threads' => 3
            ]);
            
            $stmt = $database->prepare("UPDATE bpm_users SET password = ? WHERE email = ?");
            $result = $stmt->execute([$hash, $email]);
            
            if ($result) {
                echo "<p>✅ $email 비밀번호 업데이트 성공</p>";
            } else {
                echo "<p>❌ $email 비밀번호 업데이트 실패</p>";
            }
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ 데이터베이스 오류: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<form method="POST" style="margin: 20px 0;">
    <button type="submit" name="auto_update" value="1" 
            style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;"
            onclick="return confirm('정말로 비밀번호를 자동 업데이트하시겠습니까?')">
        자동 업데이트 실행
    </button>
</form>