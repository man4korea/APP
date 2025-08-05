<?php
/**
 * AppMart Dashboard Controller
 * C:\xampp\htdocs\AppMart\src\controllers\DashboardController.php
 * Create at 2508041600 Ver1.00
 */

namespace controllers;

require_once __DIR__ . '/AuthController.php';

class DashboardController {
    
    public function index() {
        AuthController::requireAuth();
        
        global $pdo;
        $user = AuthController::getUser();
        
        try {
            if ($user['role'] === 'developer') {
                $this->developerDashboard($pdo, $user);
            } else {
                $this->userDashboard($pdo, $user);
            }
        } catch (Exception $e) {
            if (config('app.debug')) {
                echo "<h1>Dashboard Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                echo view('layouts/error', [
                    'title' => 'Dashboard Unavailable',
                    'message' => 'Unable to load dashboard. Please try again later.',
                    'code' => 503
                ]);
            }
        }
    }
    
    private function developerDashboard($pdo, $user) {
        // Get developer's applications
        $appsQuery = "
            SELECT a.*, c.name as category_name
            FROM applications a
            LEFT JOIN categories c ON a.category_id = c.id
            WHERE a.owner_id = ?
            ORDER BY a.created_at DESC
        ";
        $appsStmt = $pdo->prepare($appsQuery);
        $appsStmt->execute([$user['id']]);
        $apps = $appsStmt->fetchAll();
        
        // Get statistics
        $statsQuery = "
            SELECT 
                COUNT(*) as total_apps,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_apps,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_apps,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_apps,
                SUM(download_count) as total_downloads,
                AVG(rating_average) as avg_rating
            FROM applications 
            WHERE owner_id = ?
        ";
        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->execute([$user['id']]);
        $stats = $statsStmt->fetch();
        
        // Get earnings (if apps are paid)
        $earningsQuery = "
            SELECT 
                COUNT(*) as total_sales,
                SUM(amount) as total_earnings,
                COUNT(CASE WHEN purchased_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as sales_this_month,
                SUM(CASE WHEN purchased_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN amount ELSE 0 END) as earnings_this_month
            FROM purchases p
            JOIN applications a ON p.application_id = a.id
            WHERE a.owner_id = ? AND p.status = 'completed'
        ";
        $earningsStmt = $pdo->prepare($earningsQuery);
        $earningsStmt->execute([$user['id']]);
        $earnings = $earningsStmt->fetch();
        
        // Get recent reviews
        $reviewsQuery = "
            SELECT r.*, a.title as app_title, u.username as reviewer_username
            FROM reviews r
            JOIN applications a ON r.application_id = a.id
            JOIN users u ON r.user_id = u.id
            WHERE a.owner_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT 5
        ";
        $reviewsStmt = $pdo->prepare($reviewsQuery);
        $reviewsStmt->execute([$user['id']]);
        $recentReviews = $reviewsStmt->fetchAll();
        
        // Get monthly download chart data (last 6 months)
        $chartQuery = "
            SELECT 
                DATE_FORMAT(p.purchased_at, '%Y-%m') as month,
                COUNT(*) as downloads
            FROM purchases p
            JOIN applications a ON p.application_id = a.id
            WHERE a.owner_id = ? AND p.status = 'completed'
                AND p.purchased_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(p.purchased_at, '%Y-%m')
            ORDER BY month ASC
        ";
        $chartStmt = $pdo->prepare($chartQuery);
        $chartStmt->execute([$user['id']]);
        $chartData = $chartStmt->fetchAll();
        
        echo view('dashboard/developer', [
            'title' => 'Developer Dashboard - AppMart',
            'user' => $user,
            'apps' => $apps,
            'stats' => $stats,
            'earnings' => $earnings,
            'recent_reviews' => $recentReviews,
            'chart_data' => $chartData
        ]);
    }
    
    private function userDashboard($pdo, $user) {
        // Get user's purchases
        $purchasesQuery = "
            SELECT p.*, a.title, a.slug, a.version, a.thumbnail, u.username as owner_username
            FROM purchases p
            JOIN applications a ON p.application_id = a.id
            JOIN users u ON a.owner_id = u.id
            WHERE p.user_id = ?
            ORDER BY p.purchased_at DESC
        ";
        $purchasesStmt = $pdo->prepare($purchasesQuery);
        $purchasesStmt->execute([$user['id']]);
        $purchases = $purchasesStmt->fetchAll();
        
        // Get statistics
        $statsQuery = "
            SELECT 
                COUNT(*) as total_purchases,
                SUM(amount) as total_spent,
                COUNT(CASE WHEN purchased_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as purchases_this_month
            FROM purchases 
            WHERE user_id = ? AND status = 'completed'
        ";
        $statsStmt = $pdo->prepare($statsQuery);
        $statsStmt->execute([$user['id']]);
        $stats = $statsStmt->fetch();
        
        // Get recent reviews by user
        $reviewsQuery = "
            SELECT r.*, a.title as app_title, a.slug as app_slug
            FROM reviews r
            JOIN applications a ON r.application_id = a.id
            WHERE r.user_id = ?
            ORDER BY r.created_at DESC
            LIMIT 5
        ";
        $reviewsStmt = $pdo->prepare($reviewsQuery);
        $reviewsStmt->execute([$user['id']]);
        $userReviews = $reviewsStmt->fetchAll();
        
        // Get recommended apps (based on purchase history and categories)
        $recommendedQuery = "
            SELECT DISTINCT a.*, u.username as owner_username, c.name as category_name
            FROM applications a
            JOIN users u ON a.owner_id = u.id
            JOIN categories c ON a.category_id = c.id
            WHERE a.status = 'approved' 
                AND a.id NOT IN (
                    SELECT application_id FROM purchases 
                    WHERE user_id = ? AND status = 'completed'
                )
                AND (
                    a.category_id IN (
                        SELECT DISTINCT a2.category_id 
                        FROM purchases p2
                        JOIN applications a2 ON p2.application_id = a2.id
                        WHERE p2.user_id = ? AND p2.status = 'completed'
                    )
                    OR a.featured = 1
                )
            ORDER BY a.rating_average DESC, a.download_count DESC
            LIMIT 6
        ";
        $recommendedStmt = $pdo->prepare($recommendedQuery);
        $recommendedStmt->execute([$user['id'], $user['id']]);
        $recommendedApps = $recommendedStmt->fetchAll();
        
        echo view('dashboard/user', [
            'title' => 'My Dashboard - AppMart',
            'user' => $user,
            'purchases' => $purchases,
            'stats' => $stats,
            'user_reviews' => $userReviews,
            'recommended_apps' => $recommendedApps
        ]);
    }
    
    // User profile management
    public function profile() {
        AuthController::requireAuth();
        
        global $pdo;
        $user = AuthController::getUser();
        
        try {
            // Get full user data
            $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $userStmt->execute([$user['id']]);
            $userData = $userStmt->fetch();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->updateProfile($pdo, $userData);
                return;
            }
            
            echo view('dashboard/profile', [
                'title' => 'My Profile - AppMart',
                'user_data' => $userData
            ]);
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                echo "<h1>Profile Error</h1><p>" . $e->getMessage() . "</p>";
            } else {
                echo view('layouts/error', [
                    'title' => 'Profile Unavailable',
                    'message' => 'Unable to load profile. Please try again later.',
                    'code' => 503
                ]);
            }
        }
    }
    
    private function updateProfile($pdo, $currentUser) {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $githubUsername = trim($_POST['github_username'] ?? '');
        
        // Validation
        $errors = [];
        
        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }
        
        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }
        
        if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid website URL';
        }
        
        if (!empty($githubUsername) && !preg_match('/^[a-zA-Z0-9-]+$/', $githubUsername)) {
            $errors[] = 'Invalid GitHub username format';
        }
        
        if (!empty($errors)) {
            $_SESSION['profile_errors'] = $errors;
            redirect('/dashboard/profile');
            return;
        }
        
        try {
            $updateStmt = $pdo->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, bio = ?, website = ?, github_username = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $updateStmt->execute([
                $firstName,
                $lastName,
                $bio,
                $website,
                $githubUsername,
                $currentUser['id']
            ]);
            
            $_SESSION['profile_success'] = 'Profile updated successfully!';
            redirect('/dashboard/profile');
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                $_SESSION['profile_errors'] = ['Database error: ' . $e->getMessage()];
            } else {
                $_SESSION['profile_errors'] = ['Profile update failed. Please try again.'];
            }
            redirect('/dashboard/profile');
        }
    }
    
    // Change password
    public function changePassword() {
        AuthController::requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/dashboard/profile');
            return;
        }
        
        global $pdo;
        $user = AuthController::getUser();
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required';
        }
        
        if (empty($newPassword)) {
            $errors[] = 'New password is required';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'New passwords do not match';
        }
        
        if (!empty($errors)) {
            $_SESSION['password_errors'] = $errors;
            redirect('/dashboard/profile');
            return;
        }
        
        try {
            // Verify current password
            $userStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
            $userStmt->execute([$user['id']]);
            $userData = $userStmt->fetch();
            
            if (!password_verify($currentPassword, $userData['password_hash'])) {
                $_SESSION['password_errors'] = ['Current password is incorrect'];
                redirect('/dashboard/profile');
                return;
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$newPasswordHash, $user['id']]);
            
            $_SESSION['password_success'] = 'Password changed successfully!';
            redirect('/dashboard/profile');
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                $_SESSION['password_errors'] = ['Database error: ' . $e->getMessage()];
            } else {
                $_SESSION['password_errors'] = ['Password change failed. Please try again.'];
            }
            redirect('/dashboard/profile');
        }
    }
}