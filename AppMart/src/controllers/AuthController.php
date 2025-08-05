<?php
/**
 * AppMart Authentication Controller
 * C:\xampp\htdocs\AppMart\src\controllers\AuthController.php
 * Create at 2508041600 Ver1.00
 */

namespace controllers;

require_once __DIR__ . '/../services/EmailService.php';

class AuthController {
    
    // Show login form
    public function showLogin() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
            return;
        }
        
        echo view('auth/login', [
            'title' => 'Login - AppMart'
        ]);
    }
    
    // Process login
    public function login() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/login');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        // Validation
        $errors = [];
        if (empty($email)) {
            $errors[] = 'Email is required';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        }
        
        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            $_SESSION['auth_old_input'] = ['email' => $email];
            redirect('/auth/login');
            return;
        }
        
        try {
            // Find user by email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if (!$user || !password_verify($password, $user['password_hash'])) {
                $_SESSION['auth_errors'] = ['Invalid email or password'];
                $_SESSION['auth_old_input'] = ['email' => $email];
                redirect('/auth/login');
                return;
            }
            
            // Create session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_username'] = $user['username'];
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Set remember me cookie if requested
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                // In production, store this token in database for security
            }
            
            $_SESSION['auth_success'] = 'Welcome back, ' . $user['first_name'] . '!';
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                redirect('/admin');
            } else {
                redirect('/dashboard');
            }
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                $_SESSION['auth_errors'] = ['Database error: ' . $e->getMessage()];
            } else {
                $_SESSION['auth_errors'] = ['Login failed. Please try again.'];
            }
            redirect('/auth/login');
        }
    }
    
    // Show registration form
    public function showRegister() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            redirect('/dashboard');
            return;
        }
        
        echo view('auth/register', [
            'title' => 'Register - AppMart'
        ]);
    }
    
    // Process registration
    public function register() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/register');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        $agreeTerms = isset($_POST['agree_terms']);
        
        // Validation
        $errors = [];
        
        if (empty($email)) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (empty($username)) {
            $errors[] = 'Username is required';
        } elseif (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, and underscores';
        }
        
        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }
        
        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }
        
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!in_array($role, ['user', 'developer'])) {
            $errors[] = 'Invalid role selected';
        }
        
        if (!$agreeTerms) {
            $errors[] = 'You must agree to the terms of service';
        }
        
        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            $_SESSION['auth_old_input'] = [
                'email' => $email,
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'role' => $role
            ];
            redirect('/auth/register');
            return;
        }
        
        try {
            // Check if email or username already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR username = ?");
            $checkStmt->execute([$email, $username]);
            $existingCount = $checkStmt->fetchColumn();
            
            if ($existingCount > 0) {
                // More specific check
                $checkEmailStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $checkEmailStmt->execute([$email]);
                $emailExists = $checkEmailStmt->fetchColumn() > 0;
                
                $checkUsernameStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                $checkUsernameStmt->execute([$username]);
                $usernameExists = $checkUsernameStmt->fetchColumn() > 0;
                
                $errors = [];
                if ($emailExists) $errors[] = 'Email already registered';
                if ($usernameExists) $errors[] = 'Username already taken';
                
                $_SESSION['auth_errors'] = $errors;
                $_SESSION['auth_old_input'] = [
                    'email' => $email,
                    'username' => $username,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'role' => $role
                ];
                redirect('/auth/register');
                return;
            }
            
            // Create new user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $insertStmt = $pdo->prepare("
                INSERT INTO users (email, password_hash, username, first_name, last_name, role, status, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $insertStmt->execute([
                $email,
                $passwordHash,
                $username,
                $firstName,
                $lastName,
                $role
            ]);
            
            $userId = $pdo->lastInsertId();
            
            // Generate email verification token
            $verificationToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
            
            try {
                // Store verification token
                $tokenStmt = $pdo->prepare("
                    INSERT INTO email_verifications (user_id, email, token, expires_at) 
                    VALUES (?, ?, ?, ?)
                ");
                $tokenStmt->execute([$userId, $email, $verificationToken, $expiresAt]);
                
                // Send welcome email with verification
                $emailService = new \EmailService();
                $emailSent = $emailService->sendWelcomeEmail($email, $firstName, $verificationToken);
                
                // Update email verification sent timestamp
                if ($emailSent) {
                    $updateStmt = $pdo->prepare("UPDATE users SET email_verification_sent_at = NOW() WHERE id = ?");
                    $updateStmt->execute([$userId]);
                }
            } catch (Exception $emailError) {
                error_log("이메일 발송 실패: " . $emailError->getMessage());
                // Continue with registration even if email fails
            }
            
            // Auto-login the new user
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_username'] = $username;
            
            $_SESSION['auth_success'] = 'Registration successful! Welcome to AppMart, ' . $firstName . 
                '! Please check your email to verify your account.';
            
            redirect('/dashboard');
            
        } catch (Exception $e) {
            if (config('app.debug')) {
                $_SESSION['auth_errors'] = ['Database error: ' . $e->getMessage()];
            } else {
                $_SESSION['auth_errors'] = ['Registration failed. Please try again.'];
            }
            redirect('/auth/register');
        }
    }
    
    // Logout
    public function logout() {
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Start new session for flash message
        session_start();
        $_SESSION['auth_success'] = 'You have been logged out successfully.';
        
        redirect('/');
    }
    
    // Check if user is authenticated
    public static function isAuthenticated() {
        return isset($_SESSION['user_id']);
    }
    
    // Check if user has specific role
    public static function hasRole($role) {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }
    
    // Get current user data
    public static function getUser() {
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'role' => $_SESSION['user_role'],
            'username' => $_SESSION['user_username']
        ];
    }
    
    // Require authentication (redirect if not logged in)
    public static function requireAuth() {
        if (!self::isAuthenticated()) {
            $_SESSION['auth_redirect'] = $_SERVER['REQUEST_URI'];
            redirect('/auth/login');
            exit;
        }
    }
    
    // Require specific role
    public static function requireRole($role) {
        self::requireAuth();
        
        if (!self::hasRole($role)) {
            http_response_code(403);
            echo view('layouts/error', [
                'title' => '403 - Access Denied',
                'message' => 'You do not have permission to access this page.',
                'code' => 403
            ]);
            exit;
        }
    }
    
    // Email verification
    public function verifyEmail() {
        global $pdo;
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['auth_errors'] = ['Invalid verification token'];
            redirect('/auth/login');
            return;
        }
        
        try {
            // Find verification record
            $stmt = $pdo->prepare("
                SELECT ev.*, u.id as user_id, u.first_name, u.username 
                FROM email_verifications ev 
                JOIN users u ON ev.user_id = u.id 
                WHERE ev.token = ? AND ev.expires_at > NOW() AND ev.verified_at IS NULL
            ");
            $stmt->execute([$token]);
            $verification = $stmt->fetch();
            
            if (!$verification) {
                $_SESSION['auth_errors'] = ['Invalid or expired verification token'];
                redirect('/auth/login');
                return;
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Mark email as verified
            $updateVerification = $pdo->prepare("
                UPDATE email_verifications 
                SET verified_at = NOW() 
                WHERE token = ?
            ");
            $updateVerification->execute([$token]);
            
            // Update user record
            $updateUser = $pdo->prepare("
                UPDATE users 
                SET email_verified_at = NOW() 
                WHERE id = ?
            ");
            $updateUser->execute([$verification['user_id']]);
            
            $pdo->commit();
            
            $_SESSION['auth_success'] = 'Email verified successfully! Welcome to AppMart, ' . 
                $verification['first_name'] . '!';
            
            // Auto-login if not already logged in
            if (!self::isAuthenticated()) {
                $_SESSION['user_id'] = $verification['user_id'];
                $_SESSION['user_email'] = $verification['email'];
                $_SESSION['user_username'] = $verification['username'];
                
                // Get user role
                $roleStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                $roleStmt->execute([$verification['user_id']]);
                $_SESSION['user_role'] = $roleStmt->fetchColumn();
            }
            
            redirect('/dashboard');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("이메일 인증 오류: " . $e->getMessage());
            $_SESSION['auth_errors'] = ['Email verification failed. Please try again.'];
            redirect('/auth/login');
        }
    }
    
    // Resend verification email
    public function resendVerification() {
        global $pdo;
        
        if (!self::isAuthenticated()) {
            redirect('/auth/login');
            return;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            // Check if already verified
            $userStmt = $pdo->prepare("SELECT email_verified_at, email, first_name FROM users WHERE id = ?");
            $userStmt->execute([$userId]);
            $user = $userStmt->fetch();
            
            if ($user['email_verified_at']) {
                $_SESSION['auth_success'] = 'Your email is already verified!';
                redirect('/dashboard');
                return;
            }
            
            // Generate new token
            $verificationToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60));
            
            // Delete old tokens
            $deleteStmt = $pdo->prepare("DELETE FROM email_verifications WHERE user_id = ?");
            $deleteStmt->execute([$userId]);
            
            // Insert new token
            $insertStmt = $pdo->prepare("
                INSERT INTO email_verifications (user_id, email, token, expires_at) 
                VALUES (?, ?, ?, ?)
            ");
            $insertStmt->execute([$userId, $user['email'], $verificationToken, $expiresAt]);
            
            // Send email
            $emailService = new \EmailService();
            $emailSent = $emailService->sendWelcomeEmail($user['email'], $user['first_name'], $verificationToken);
            
            if ($emailSent) {
                // Update sent timestamp
                $updateStmt = $pdo->prepare("UPDATE users SET email_verification_sent_at = NOW() WHERE id = ?");
                $updateStmt->execute([$userId]);
                
                $_SESSION['auth_success'] = 'Verification email sent! Please check your inbox.';
            } else {
                $_SESSION['auth_errors'] = ['Failed to send verification email. Please try again later.'];
            }
            
            redirect('/dashboard');
            
        } catch (Exception $e) {
            error_log("인증 이메일 재발송 오류: " . $e->getMessage());
            $_SESSION['auth_errors'] = ['Failed to resend verification email.'];
            redirect('/dashboard');
        }
    }
    
    // Show forgot password form
    public function showForgotPassword() {
        if (self::isAuthenticated()) {
            redirect('/dashboard');
            return;
        }
        
        echo view('auth/forgot-password', [
            'title' => 'Forgot Password - AppMart'
        ]);
    }
    
    // Process forgot password
    public function forgotPassword() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/forgot-password');
            return;
        }
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['auth_errors'] = ['Please enter a valid email address'];
            redirect('/auth/forgot-password');
            return;
        }
        
        try {
            // Find user
            $stmt = $pdo->prepare("SELECT id, first_name, username FROM users WHERE email = ? AND status = 'active'");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $resetToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', time() + (2 * 60 * 60)); // 2 hours
                
                // Delete old tokens
                $deleteStmt = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $deleteStmt->execute([$user['id']]);
                
                // Insert new token
                $insertStmt = $pdo->prepare("
                    INSERT INTO password_resets (user_id, email, token, expires_at) 
                    VALUES (?, ?, ?, ?)
                ");
                $insertStmt->execute([$user['id'], $email, $resetToken, $expiresAt]);
                
                // Send reset email
                $emailService = new \EmailService();
                $emailSent = $emailService->sendPasswordResetEmail($email, $user['first_name'], $resetToken);
                
                if ($emailSent) {
                    $_SESSION['auth_success'] = 'Password reset email sent! Please check your inbox.';
                } else {
                    $_SESSION['auth_errors'] = ['Failed to send reset email. Please try again later.'];
                }
            } else {
                // Always show success message for security (don't reveal if email exists)
                $_SESSION['auth_success'] = 'If an account with that email exists, a password reset email has been sent.';
            }
            
            redirect('/auth/forgot-password');
            
        } catch (Exception $e) {
            error_log("비밀번호 재설정 요청 오류: " . $e->getMessage());
            $_SESSION['auth_errors'] = ['Something went wrong. Please try again later.'];
            redirect('/auth/forgot-password');
        }
    }
    
    // Show reset password form
    public function showResetPassword() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['auth_errors'] = ['Invalid reset link'];
            redirect('/auth/forgot-password');
            return;
        }
        
        echo view('auth/reset-password', [
            'title' => 'Reset Password - AppMart',
            'token' => $token
        ]);
    }
    
    // Process reset password
    public function resetPassword() {
        global $pdo;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/auth/forgot-password');
            return;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        $errors = [];
        if (empty($token)) {
            $errors[] = 'Invalid reset token';
        }
        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }
        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
        
        if (!empty($errors)) {
            $_SESSION['auth_errors'] = $errors;
            redirect('/auth/reset-password?token=' . urlencode($token));
            return;
        }
        
        try {
            // Find valid reset token
            $stmt = $pdo->prepare("
                SELECT pr.*, u.first_name, u.username 
                FROM password_resets pr 
                JOIN users u ON pr.user_id = u.id 
                WHERE pr.token = ? AND pr.expires_at > NOW() AND pr.used_at IS NULL
            ");
            $stmt->execute([$token]);
            $resetRecord = $stmt->fetch();
            
            if (!$resetRecord) {
                $_SESSION['auth_errors'] = ['Invalid or expired reset token'];
                redirect('/auth/forgot-password');
                return;
            }
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Update password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $updateUserStmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $updateUserStmt->execute([$passwordHash, $resetRecord['user_id']]);
            
            // Mark token as used
            $updateTokenStmt = $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE token = ?");
            $updateTokenStmt->execute([$token]);
            
            $pdo->commit();
            
            $_SESSION['auth_success'] = 'Password reset successfully! You can now log in with your new password.';
            redirect('/auth/login');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("비밀번호 재설정 오류: " . $e->getMessage());
            $_SESSION['auth_errors'] = ['Password reset failed. Please try again.'];
            redirect('/auth/reset-password?token=' . urlencode($token));
        }
    }
}