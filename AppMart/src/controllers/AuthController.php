<?php
/**
 * AppMart Authentication Controller
 * C:\xampp\htdocs\AppMart\src\controllers\AuthController.php
 * Create at 2508041600 Ver1.00
 */

namespace controllers;

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
            
            // Auto-login the new user
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_email'] = $email;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_username'] = $username;
            
            $_SESSION['auth_success'] = 'Registration successful! Welcome to AppMart, ' . $firstName . '!';
            
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
}