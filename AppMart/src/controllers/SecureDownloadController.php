<?php
// C:\xampp\htdocs\AppMart\src\controllers\SecureDownloadController.php
// Create at 2508051330 Ver1.00

namespace controllers;

require_once __DIR__ . '/AuthController.php';

class SecureDownloadController
{
    private $allowedPath;
    private $downloadTokens;
    
    public function __construct()
    {
        $this->allowedPath = realpath(__DIR__ . '/../../uploads/');
        $this->downloadTokens = [];
    }
    
    /**
     * Generate secure download token
     */
    public function generateDownloadToken($appId, $userId, $expiryMinutes = 30)
    {
        global $pdo;
        
        try {
            // Verify user has access to this app
            $accessCheck = $this->verifyDownloadAccess($appId, $userId);
            if (!$accessCheck['allowed']) {
                return ['success' => false, 'message' => $accessCheck['reason']];
            }
            
            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));
            
            // Store token in database
            $stmt = $pdo->prepare("
                INSERT INTO download_tokens (token, user_id, application_id, expires_at, created_at)
                VALUES (?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    token = VALUES(token),
                    expires_at = VALUES(expires_at),
                    created_at = NOW()
            ");
            
            $stmt->execute([$token, $userId, $appId, $expiresAt]);
            
            return [
                'success' => true,
                'token' => $token,
                'expires_at' => $expiresAt,
                'download_url' => url('/download/secure?token=' . $token)
            ];
            
        } catch (Exception $e) {
            error_log("다운로드 토큰 생성 오류: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to generate download token'];
        }
    }
    
    /**
     * Secure file download with token verification
     */
    public function secureDownload()
    {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Download token required']);
            return;
        }
        
        global $pdo;
        
        try {
            // Verify token
            $stmt = $pdo->prepare("
                SELECT dt.*, a.file_path, a.title, a.version, a.file_size
                FROM download_tokens dt
                JOIN applications a ON dt.application_id = a.id
                WHERE dt.token = ? AND dt.expires_at > NOW() AND dt.used_at IS NULL
            ");
            
            $stmt->execute([$token]);
            $tokenData = $stmt->fetch();
            
            if (!$tokenData) {
                http_response_code(404);
                echo json_encode(['error' => 'Invalid or expired download token']);
                return;
            }
            
            // Additional security checks
            $securityCheck = $this->performDownloadSecurityChecks($tokenData);
            if (!$securityCheck['safe']) {
                http_response_code(403);
                echo json_encode(['error' => $securityCheck['reason']]);
                return;
            }
            
            // Verify file exists and is safe
            $filePath = $this->validateFilePath($tokenData['file_path']);
            if (!$filePath) {
                http_response_code(404);
                echo json_encode(['error' => 'File not found or access denied']);
                return;
            }
            
            // Mark token as used
            $updateStmt = $pdo->prepare("UPDATE download_tokens SET used_at = NOW() WHERE token = ?");
            $updateStmt->execute([$token]);
            
            // Update download statistics
            $this->updateDownloadStats($tokenData['application_id'], $tokenData['user_id']);
            
            // Log download activity
            $this->logDownloadActivity($tokenData['user_id'], $tokenData['application_id'], $filePath);
            
            // Serve file securely
            $this->serveFile($filePath, $tokenData);
            
        } catch (Exception $e) {
            error_log("보안 다운로드 오류: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Download failed']);
        }
    }
    
    /**
     * Verify user has download access to app
     */
    private function verifyDownloadAccess($appId, $userId)
    {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                SELECT a.price, a.status, p.id as purchase_id
                FROM applications a
                LEFT JOIN purchases p ON (a.id = p.application_id AND p.user_id = ? AND p.status = 'completed')
                WHERE a.id = ?
            ");
            
            $stmt->execute([$userId, $appId]);
            $app = $stmt->fetch();
            
            if (!$app) {
                return ['allowed' => false, 'reason' => 'Application not found'];
            }
            
            if ($app['status'] !== 'approved') {
                return ['allowed' => false, 'reason' => 'Application not available for download'];
            }
            
            // Check if free app or user has purchased
            if ($app['price'] > 0 && !$app['purchase_id']) {
                return ['allowed' => false, 'reason' => 'Purchase required to download this application'];
            }
            
            return ['allowed' => true];
            
        } catch (Exception $e) {
            error_log("다운로드 접근 확인 오류: " . $e->getMessage());
            return ['allowed' => false, 'reason' => 'Access verification failed'];
        }
    }
    
    /**
     * Perform additional security checks before download
     */
    private function performDownloadSecurityChecks($tokenData)
    {
        // Check if user is authenticated
        if (!AuthController::isAuthenticated()) {
            return ['safe' => false, 'reason' => 'Authentication required'];
        }
        
        $currentUser = AuthController::getUser();
        
        // Verify token belongs to current user
        if ($currentUser['id'] != $tokenData['user_id']) {
            return ['safe' => false, 'reason' => 'Token does not belong to current user'];
        }
        
        // Check rate limiting
        $rateLimitCheck = $this->checkDownloadRateLimit($currentUser['id']);
        if (!$rateLimitCheck['allowed']) {
            return ['safe' => false, 'reason' => $rateLimitCheck['reason']];
        }
        
        // Check for suspicious activity
        $suspiciousCheck = $this->checkSuspiciousActivity($currentUser['id']);
        if (!$suspiciousCheck['safe']) {
            return ['safe' => false, 'reason' => 'Suspicious activity detected'];
        }
        
        return ['safe' => true];
    }
    
    /**
     * Validate and secure file path
     */
    private function validateFilePath($filePath)
    {
        // Remove any relative path components
        $cleanPath = str_replace(['../', './'], '', $filePath);
        
        // Build full path
        $fullPath = realpath($this->allowedPath . '/' . ltrim($cleanPath, '/'));
        
        // Ensure path is within allowed directory
        if (!$fullPath || strpos($fullPath, $this->allowedPath) !== 0) {
            return false;
        }
        
        // Check if file exists and is readable
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return false;
        }
        
        // Additional security check - verify file has not been tampered with
        $securityCheck = $this->verifyFileIntegrity($fullPath);
        if (!$securityCheck['valid']) {
            error_log("파일 무결성 검증 실패: " . $fullPath);
            return false;
        }
        
        return $fullPath;
    }
    
    /**
     * Check download rate limiting
     */
    private function checkDownloadRateLimit($userId)
    {
        global $pdo;
        
        try {
            // Check downloads in last hour
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as download_count
                FROM download_logs 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
            ");
            
            $stmt->execute([$userId]);
            $hourlyDownloads = $stmt->fetchColumn();
            
            $hourlyLimit = 20; // Max 20 downloads per hour
            
            if ($hourlyDownloads >= $hourlyLimit) {
                return [
                    'allowed' => false,
                    'reason' => 'Download rate limit exceeded. Please wait before downloading more files.'
                ];
            }
            
            return ['allowed' => true];
            
        } catch (Exception $e) {
            error_log("다운로드 속도 제한 확인 오류: " . $e->getMessage());
            // Allow download on error
            return ['allowed' => true];
        }
    }
    
    /**
     * Check for suspicious download activity
     */
    private function checkSuspiciousActivity($userId)
    {
        global $pdo;
        
        try {
            // Check for rapid successive downloads
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as recent_downloads
                FROM download_logs 
                WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
            ");
            
            $stmt->execute([$userId]);
            $recentDownloads = $stmt->fetchColumn();
            
            if ($recentDownloads > 5) { // More than 5 downloads in 1 minute
                return ['safe' => false, 'reason' => 'Suspicious download pattern detected'];
            }
            
            return ['safe' => true];
            
        } catch (Exception $e) {
            error_log("의심스러운 활동 확인 오류: " . $e->getMessage());
            return ['safe' => true];
        }
    }
    
    /**
     * Verify file integrity
     */
    private function verifyFileIntegrity($filePath)
    {
        global $pdo;
        
        try {
            // Get stored file hash from database
            $stmt = $pdo->prepare("
                SELECT file_hash 
                FROM applications 
                WHERE file_path = ? AND status = 'approved'
            ");
            
            $stmt->execute([str_replace($this->allowedPath, '/uploads', $filePath)]);
            $storedHash = $stmt->fetchColumn();
            
            if (!$storedHash) {
                return ['valid' => false, 'reason' => 'File hash not found in database'];
            }
            
            // Calculate current file hash
            $currentHash = hash_file('sha256', $filePath);
            
            if ($currentHash !== $storedHash) {
                return ['valid' => false, 'reason' => 'File integrity check failed'];
            }
            
            return ['valid' => true];
            
        } catch (Exception $e) {
            error_log("파일 무결성 검증 오류: " . $e->getMessage());
            // Allow download on error to avoid breaking functionality
            return ['valid' => true];
        }
    }
    
    /**
     * Update download statistics
     */
    private function updateDownloadStats($appId, $userId)
    {
        global $pdo;
        
        try {
            // Update application download count
            $appStmt = $pdo->prepare("UPDATE applications SET download_count = download_count + 1 WHERE id = ?");
            $appStmt->execute([$appId]);
            
            // Update purchase download count if applicable
            $purchaseStmt = $pdo->prepare("
                UPDATE purchases 
                SET download_count = download_count + 1, last_download_at = NOW() 
                WHERE user_id = ? AND application_id = ? AND status = 'completed'
            ");
            $purchaseStmt->execute([$userId, $appId]);
            
        } catch (Exception $e) {
            error_log("다운로드 통계 업데이트 오류: " . $e->getMessage());
        }
    }
    
    /**
     * Log download activity
     */
    private function logDownloadActivity($userId, $appId, $filePath)
    {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO download_logs (user_id, application_id, file_path, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->execute([$userId, $appId, $filePath, $ipAddress, $userAgent]);
            
        } catch (Exception $e) {
            error_log("다운로드 활동 로그 오류: " . $e->getMessage());
        }
    }
    
    /**
     * Serve file securely with proper headers
     */
    private function serveFile($filePath, $tokenData)
    {
        // Clear any output buffers
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        $fileSize = filesize($filePath);
        $fileName = basename($tokenData['file_path']);
        
        // Set security headers
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: no-referrer');
        
        // Set download headers
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . $fileSize);
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Support for range requests (resume download)
        $this->handleRangeRequest($filePath, $fileSize);
    }
    
    /**
     * Handle HTTP range requests for resumable downloads
     */
    private function handleRangeRequest($filePath, $fileSize)
    {
        $start = 0;
        $end = $fileSize - 1;
        
        if (isset($_SERVER['HTTP_RANGE'])) {
            if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
                $start = intval($matches[1]);
                if (!empty($matches[2])) {
                    $end = intval($matches[2]);
                }
            }
            
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$fileSize");
            header('Content-Length: ' . ($end - $start + 1));
        }
        
        // Output file content
        $handle = fopen($filePath, 'rb');
        if ($handle) {
            fseek($handle, $start);
            
            $bufferSize = 8192; // 8KB chunks
            $remainingBytes = $end - $start + 1;
            
            while (!feof($handle) && $remainingBytes > 0) {
                $chunkSize = min($bufferSize, $remainingBytes);
                echo fread($handle, $chunkSize);
                $remainingBytes -= $chunkSize;
            }
            
            fclose($handle);
        }
    }
    
    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens()
    {
        global $pdo;
        
        try {
            $stmt = $pdo->prepare("DELETE FROM download_tokens WHERE expires_at < NOW()");
            $stmt->execute();
            
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("만료된 토큰 정리 오류: " . $e->getMessage());
            return 0;
        }
    }
}