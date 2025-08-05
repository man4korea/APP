<?php
// C:\xampp\htdocs\AppMart\src\controllers\AdminController.php
// Create at 2508051200 Ver1.00

namespace controllers;

require_once __DIR__ . '/../services/EmailService.php';

class AdminController {
    
    public function __construct() {
        // Require admin role for all admin actions
        \controllers\AuthController::requireRole('admin');
    }
    
    // Admin dashboard
    public function dashboard() {
        global $pdo;
        
        try {
            // Get statistics
            $stats = $this->getAdminStats($pdo);
            
            // Get pending apps (for review)
            $pendingApps = $this->getPendingApps($pdo, 5);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($pdo);
            
            // Get latest reviews
            $latestReviews = $this->getLatestReviews($pdo);
            
            // Get system alerts
            $alerts = $this->getSystemAlerts($pdo);
            
            // Get system info
            $systemInfo = $this->getSystemInfo();
            
            echo view('admin/dashboard', [
                'title' => 'Admin Dashboard - AppMart',
                'stats' => $stats,
                'pending_apps' => $pendingApps,
                'recent_activities' => $recentActivities,
                'latest_reviews' => $latestReviews,
                'alerts' => $alerts,
                'system_info' => $systemInfo
            ]);
            
        } catch (Exception $e) {
            error_log("관리자 대시보드 오류: " . $e->getMessage());
            echo view('layouts/error', [
                'title' => 'Error - Admin Dashboard',
                'message' => 'Failed to load admin dashboard.',
                'code' => 500
            ]);
        }
    }
    
    // Quick app review (approve/reject)
    public function quickReview() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        // Parse JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        $appId = $input['app_id'] ?? null;
        $action = $input['action'] ?? null;
        
        if (!$appId || !in_array($action, ['approved', 'rejected'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }
        
        try {
            // Get app and owner info
            $stmt = $pdo->prepare("
                SELECT a.*, u.email, u.first_name, u.username 
                FROM applications a 
                JOIN users u ON a.owner_id = u.id 
                WHERE a.id = ?
            ");
            $stmt->execute([$appId]);
            $app = $stmt->fetch();
            
            if (!$app) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'App not found']);
                return;
            }
            
            // Update app status
            $updateStmt = $pdo->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$action, $appId]);
            
            // Send notification email
            $emailService = new \EmailService();
            $reason = $action === 'rejected' ? 'Standard quality review criteria not met. Please review and resubmit.' : null;
            
            $emailSent = $emailService->sendAppStatusNotification(
                $app['email'],
                $app['first_name'],
                $app['title'],
                $action,
                $reason
            );
            
            if (!$emailSent) {
                error_log("앱 상태 알림 이메일 발송 실패 - App ID: {$appId}, User: {$app['email']}");
            }
            
            // Log admin activity
            $this->logAdminActivity($pdo, $_SESSION['user_id'], 'app_review', 
                "App '{$app['title']}' {$action} by admin");
            
            echo json_encode([
                'success' => true, 
                'message' => "App {$action} successfully" . ($emailSent ? ' and notification sent' : '')
            ]);
            
        } catch (Exception $e) {
            error_log("앱 빠른 검토 오류: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Review failed']);
        }
    }
    
    // Bulk app actions
    public function bulkAction() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $appIds = $input['app_ids'] ?? [];
        $action = $input['action'] ?? null;
        
        if (empty($appIds) || !in_array($action, ['approve', 'reject'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            return;
        }
        
        try {
            $pdo->beginTransaction();
            
            $status = $action === 'approve' ? 'approved' : 'rejected';
            $successCount = 0;
            $emailService = new \EmailService();
            
            foreach ($appIds as $appId) {
                // Get app and owner info
                $stmt = $pdo->prepare("
                    SELECT a.*, u.email, u.first_name, u.username 
                    FROM applications a 
                    JOIN users u ON a.owner_id = u.id 
                    WHERE a.id = ?
                ");
                $stmt->execute([$appId]);
                $app = $stmt->fetch();
                
                if ($app) {
                    // Update app status
                    $updateStmt = $pdo->prepare("UPDATE applications SET status = ?, updated_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$status, $appId]);
                    
                    // Send notification email
                    $reason = $status === 'rejected' ? 'Bulk review action - standard criteria not met' : null;
                    $emailSent = $emailService->sendAppStatusNotification(
                        $app['email'],
                        $app['first_name'],
                        $app['title'],
                        $status,
                        $reason
                    );
                    
                    if (!$emailSent) {
                        error_log("벌크 앱 상태 알림 이메일 발송 실패 - App ID: {$appId}");
                    }
                    
                    $successCount++;
                }
            }
            
            // Log bulk activity
            $this->logAdminActivity($pdo, $_SESSION['user_id'], 'bulk_app_review', 
                "Bulk {$action}: {$successCount} apps processed");
            
            $pdo->commit();
            
            echo json_encode([
                'success' => true,
                'count' => $successCount,
                'message' => "{$successCount} apps {$action}d successfully"
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("벌크 앱 작업 오류: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Bulk action failed']);
        }
    }
    
    // App detailed review page
    public function reviewApp() {
        global $pdo;
        
        $appId = $_GET['id'] ?? null;
        
        if (!$appId) {
            redirect('/admin/apps');
            return;
        }
        
        try {
            // Get app details with owner info
            $stmt = $pdo->prepare("
                SELECT a.*, u.username, u.first_name, u.last_name, u.email,
                       c.name as category_name
                FROM applications a 
                LEFT JOIN users u ON a.owner_id = u.id 
                LEFT JOIN categories c ON a.category_id = c.id
                WHERE a.id = ?
            ");
            $stmt->execute([$appId]);
            $app = $stmt->fetch();
            
            if (!$app) {
                $_SESSION['admin_error'] = 'App not found';
                redirect('/admin/apps');
                return;
            }
            
            echo view('admin/app-review', [
                'title' => 'Review App: ' . $app['title'] . ' - AppMart Admin',
                'app' => $app
            ]);
            
        } catch (Exception $e) {
            error_log("앱 상세 검토 페이지 오류: " . $e->getMessage());
            $_SESSION['admin_error'] = 'Failed to load app details';
            redirect('/admin/apps');
        }
    }
    
    // Process detailed app review
    public function processReview() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/apps');
            return;
        }
        
        $appId = $_POST['app_id'] ?? null;
        $action = $_POST['action'] ?? null;
        $adminNotes = trim($_POST['admin_notes'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        
        if (!$appId || !in_array($action, ['approved', 'rejected'])) {
            $_SESSION['admin_error'] = 'Invalid review data';
            redirect('/admin/apps');
            return;
        }
        
        try {
            // Get app and owner info
            $stmt = $pdo->prepare("
                SELECT a.*, u.email, u.first_name, u.username 
                FROM applications a 
                JOIN users u ON a.owner_id = u.id 
                WHERE a.id = ?
            ");
            $stmt->execute([$appId]);
            $app = $stmt->fetch();
            
            if (!$app) {
                $_SESSION['admin_error'] = 'App not found';
                redirect('/admin/apps');
                return;
            }
            
            // Update app with review details
            $updateStmt = $pdo->prepare("
                UPDATE applications 
                SET status = ?, admin_notes = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $updateStmt->execute([$action, $adminNotes, $appId]);
            
            // Send detailed notification email
            $emailService = new \EmailService();
            $finalReason = !empty($reason) ? $reason : ($action === 'rejected' ? $adminNotes : null);
            
            $emailSent = $emailService->sendAppStatusNotification(
                $app['email'],
                $app['first_name'],
                $app['title'],
                $action,
                $finalReason
            );
            
            // Log detailed review
            $this->logAdminActivity($pdo, $_SESSION['user_id'], 'detailed_app_review', 
                "Detailed review: App '{$app['title']}' {$action} with notes");
            
            $_SESSION['admin_success'] = "App '{$app['title']}' has been {$action}" . 
                ($emailSent ? ' and developer notified via email' : '');
            
            redirect('/admin/apps');
            
        } catch (Exception $e) {
            error_log("상세 앱 검토 처리 오류: " . $e->getMessage());
            $_SESSION['admin_error'] = 'Review processing failed';
            redirect('/admin/apps/review?id=' . $appId);
        }
    }
    
    // Helper methods
    private function getAdminStats($pdo) {
        $stats = [];
        
        // Total users
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $stats['total_users'] = $stmt->fetchColumn();
        
        // New users this month
        $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $stats['new_users_this_month'] = $stmt->fetchColumn();
        
        // Total apps
        $stmt = $pdo->query("SELECT COUNT(*) FROM applications");
        $stats['total_apps'] = $stmt->fetchColumn();
        
        // Pending apps
        $stmt = $pdo->query("SELECT COUNT(*) FROM applications WHERE status = 'pending'");
        $stats['pending_apps'] = $stmt->fetchColumn();
        
        // Revenue (placeholder - will be implemented with payment system)
        $stats['total_revenue'] = 0;
        $stats['revenue_this_month'] = 0;
        
        // Downloads (placeholder)
        $stats['total_downloads'] = 0;
        $stats['downloads_this_week'] = 0;
        
        return $stats;
    }
    
    private function getPendingApps($pdo, $limit = 5) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.username as owner_username, c.name as category_name
            FROM applications a 
            LEFT JOIN users u ON a.owner_id = u.id 
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'pending' 
            ORDER BY a.created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    private function getRecentActivities($pdo) {
        // This would be expanded with a proper activity log table
        return [
            [
                'type' => 'user_registered',
                'description' => 'New user registered: testuser123',
                'created_at' => date('Y-m-d H:i:s', time() - 3600)
            ],
            [
                'type' => 'app_uploaded',
                'description' => 'New app uploaded: Amazing Calculator',
                'created_at' => date('Y-m-d H:i:s', time() - 7200)
            ]
        ];
    }
    
    private function getLatestReviews($pdo) {
        // Placeholder - will be implemented with review system
        return [];
    }
    
    private function getSystemAlerts($pdo) {
        $alerts = [];
        
        // Check for apps pending too long
        $stmt = $pdo->query("
            SELECT COUNT(*) FROM applications 
            WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $oldPending = $stmt->fetchColumn();
        
        if ($oldPending > 0) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Apps Pending Review',
                'message' => "{$oldPending} apps have been pending review for over 7 days"
            ];
        }
        
        return $alerts;
    }
    
    private function getSystemInfo() {
        return [
            'disk_usage' => '45%',
            'memory_usage' => '67%',
            'uptime' => '15 days'
        ];
    }
    
    private function logAdminActivity($pdo, $adminId, $action, $description) {
        try {
            // This would typically go into an admin_activities table
            error_log("Admin Activity - User {$adminId}: {$action} - {$description}");
        } catch (Exception $e) {
            error_log("관리자 활동 로그 실패: " . $e->getMessage());
        }
    }
}