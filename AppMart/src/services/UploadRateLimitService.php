<?php
// C:\xampp\htdocs\AppMart\src\services\UploadRateLimitService.php  
// Create at 2508051330 Ver1.00

class UploadRateLimitService 
{
    private $pdo;
    private $maxUploadsPerHour;
    private $maxUploadsPerDay;
    private $blockDurationMinutes;
    
    public function __construct()
    {
        $this->pdo = getDBConnection();
        $this->maxUploadsPerHour = (int)(getenv('MAX_UPLOADS_PER_HOUR') ?: 5);
        $this->maxUploadsPerDay = (int)(getenv('MAX_UPLOADS_PER_DAY') ?: 20);
        $this->blockDurationMinutes = (int)(getenv('BLOCK_DURATION_MINUTES') ?: 60);
    }
    
    /**
     * Check if user can upload a file
     */
    public function canUpload($userId, $ipAddress = null)
    {
        try {
            $ipAddress = $ipAddress ?: $this->getClientIP();
            
            // Check if user is currently blocked
            $blockCheck = $this->isUserBlocked($userId, $ipAddress);
            if ($blockCheck['blocked']) {
                return [
                    'allowed' => false,
                    'reason' => 'Rate limit exceeded. Try again after ' . $blockCheck['remaining_time'],
                    'retry_after' => $blockCheck['retry_after']
                ];
            }
            
            // Check hourly limit
            $hourlyCount = $this->getUploadCount($userId, $ipAddress, 'hour');
            if ($hourlyCount >= $this->maxUploadsPerHour) {
                $this->blockUser($userId, $ipAddress, 'hourly_limit');
                return [
                    'allowed' => false,
                    'reason' => "Hourly upload limit ({$this->maxUploadsPerHour}) exceeded. Please wait before uploading again.",
                    'retry_after' => 3600
                ];
            }
            
            // Check daily limit
            $dailyCount = $this->getUploadCount($userId, $ipAddress, 'day');
            if ($dailyCount >= $this->maxUploadsPerDay) {
                $this->blockUser($userId, $ipAddress, 'daily_limit');
                return [
                    'allowed' => false,
                    'reason' => "Daily upload limit ({$this->maxUploadsPerDay}) exceeded. Please try again tomorrow.",
                    'retry_after' => 86400
                ];
            }
            
            // Check upload frequency (prevent spam)
            $recentUploadCheck = $this->checkRecentUpload($userId, $ipAddress);
            if (!$recentUploadCheck['allowed']) {
                return $recentUploadCheck;
            }
            
            return ['allowed' => true];
            
        } catch (Exception $e) {
            error_log("업로드 제한 확인 오류: " . $e->getMessage());
            // Allow upload on error to avoid breaking functionality
            return ['allowed' => true];
        }
    }
    
    /**
     * Record successful upload
     */
    public function recordUpload($userId, $ipAddress = null, $fileSize = 0, $fileName = '')
    {
        try {
            $ipAddress = $ipAddress ?: $this->getClientIP();
            
            $stmt = $this->pdo->prepare("
                INSERT INTO upload_rate_limits (user_id, ip_address, upload_count, window_start, file_size, file_name) 
                VALUES (?, ?, 1, NOW(), ?, ?)
                ON DUPLICATE KEY UPDATE 
                    upload_count = upload_count + 1,
                    updated_at = NOW(),
                    file_size = file_size + VALUES(file_size)
            ");
            
            $stmt->execute([$userId, $ipAddress, $fileSize, $fileName]);
            
        } catch (Exception $e) {
            error_log("업로드 기록 저장 오류: " . $e->getMessage());
        }
    }
    
    /**
     * Check if user is currently blocked
     */
    private function isUserBlocked($userId, $ipAddress)
    {
        $stmt = $this->pdo->prepare("
            SELECT blocked_until, TIMESTAMPDIFF(SECOND, NOW(), blocked_until) as remaining_seconds
            FROM upload_rate_limits 
            WHERE (user_id = ? OR ip_address = ?) 
                AND blocked_until IS NOT NULL 
                AND blocked_until > NOW()
            ORDER BY blocked_until DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $ipAddress]);
        $block = $stmt->fetch();
        
        if ($block) {
            $remainingTime = $this->formatRemainingTime($block['remaining_seconds']);
            return [
                'blocked' => true,
                'remaining_time' => $remainingTime,
                'retry_after' => $block['remaining_seconds']
            ];
        }
        
        return ['blocked' => false];
    }
    
    /**
     * Get upload count within time window
     */
    private function getUploadCount($userId, $ipAddress, $window)
    {
        $timeCondition = match($window) {
            'hour' => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
            'day' => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)',
            'week' => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)',
            default => 'created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'
        };
        
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(SUM(upload_count), 0) as total_uploads
            FROM upload_rate_limits 
            WHERE (user_id = ? OR ip_address = ?) AND {$timeCondition}
        ");
        
        $stmt->execute([$userId, $ipAddress]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Check for recent uploads (prevent rapid successive uploads)
     */
    private function checkRecentUpload($userId, $ipAddress)
    {
        $stmt = $this->pdo->prepare("
            SELECT updated_at, TIMESTAMPDIFF(SECOND, updated_at, NOW()) as seconds_ago
            FROM upload_rate_limits 
            WHERE (user_id = ? OR ip_address = ?) 
            ORDER BY updated_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$userId, $ipAddress]);
        $recent = $stmt->fetch();
        
        $minIntervalSeconds = 30; // Minimum 30 seconds between uploads
        
        if ($recent && $recent['seconds_ago'] < $minIntervalSeconds) {
            $waitTime = $minIntervalSeconds - $recent['seconds_ago']; 
            return [
                'allowed' => false,
                'reason' => "Please wait {$waitTime} seconds before uploading another file.",
                'retry_after' => $waitTime
            ];
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Block user for specified duration
     */
    private function blockUser($userId, $ipAddress, $reason)
    {
        $blockUntil = date('Y-m-d H:i:s', time() + ($this->blockDurationMinutes * 60));
        
        $stmt = $this->pdo->prepare("
            UPDATE upload_rate_limits 
            SET blocked_until = ?, updated_at = NOW() 
            WHERE user_id = ? OR ip_address = ?
        ");
        
        $stmt->execute([$blockUntil, $userId, $ipAddress]);
        
        // Log the blocking event
        $this->logSecurityEvent($userId, $ipAddress, 'rate_limit_exceeded', $reason);
    }
    
    /**
     * Get client IP address (with proxy support)
     */
    private function getClientIP()
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);
                
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Format remaining time for display
     */
    private function formatRemainingTime($seconds)
    {
        if ($seconds < 60) {
            return "{$seconds} seconds";
        } elseif ($seconds < 3600) {
            $minutes = ceil($seconds / 60);
            return "{$minutes} minutes";
        } else {
            $hours = ceil($seconds / 3600);
            return "{$hours} hours";
        }
    }
    
    /**
     * Log security events
     */
    private function logSecurityEvent($userId, $ipAddress, $eventType, $details)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO security_incidents (
                    incident_type, user_id, ip_address, user_agent, reason, severity
                ) VALUES (?, ?, ?, ?, ?, 'medium')
            ");
            
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            $stmt->execute(['suspicious_upload', $userId, $ipAddress, $userAgent, $details]);
            
        } catch (Exception $e) {
            error_log("보안 이벤트 로그 실패: " . $e->getMessage());
        }
    }
    
    /**
     * Clean up old rate limit records
     */
    public function cleanup($daysOld = 7)
    {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM upload_rate_limits 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)
                    AND (blocked_until IS NULL OR blocked_until < NOW())
            ");
            
            $stmt->execute([$daysOld]);
            return $stmt->rowCount();
            
        } catch (Exception $e) {
            error_log("속도 제한 정리 오류: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get user's current rate limit status
     */
    public function getUserStatus($userId, $ipAddress = null)
    {
        $ipAddress = $ipAddress ?: $this->getClientIP();
        
        $hourlyCount = $this->getUploadCount($userId, $ipAddress, 'hour');
        $dailyCount = $this->getUploadCount($userId, $ipAddress, 'day');
        $blockStatus = $this->isUserBlocked($userId, $ipAddress);
        
        return [
            'hourly_uploads' => $hourlyCount,
            'hourly_limit' => $this->maxUploadsPerHour,
            'hourly_remaining' => max(0, $this->maxUploadsPerHour - $hourlyCount),
            'daily_uploads' => $dailyCount,
            'daily_limit' => $this->maxUploadsPerDay,
            'daily_remaining' => max(0, $this->maxUploadsPerDay - $dailyCount),
            'is_blocked' => $blockStatus['blocked'],
            'block_remaining' => $blockStatus['remaining_time'] ?? null
        ];
    }
    
    /**
     * Admin function: unblock user
     */
    public function unblockUser($userId, $ipAddress = null)
    {
        try {
            if ($ipAddress) {
                $stmt = $this->pdo->prepare("
                    UPDATE upload_rate_limits 
                    SET blocked_until = NULL, updated_at = NOW()
                    WHERE ip_address = ?
                ");
                $stmt->execute([$ipAddress]);
            } else {
                $stmt = $this->pdo->prepare("
                    UPDATE upload_rate_limits 
                    SET blocked_until = NULL, updated_at = NOW()
                    WHERE user_id = ?
                ");
                $stmt->execute([$userId]);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("사용자 차단 해제 오류: " . $e->getMessage());
            return false;
        }
    }
}