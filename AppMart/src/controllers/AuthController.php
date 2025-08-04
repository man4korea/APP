<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\controllers\AuthController.php
// Create at 2508041132 Ver1.00

require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->email = $_POST['email'];
            $this->user->nickname = $_POST['nickname'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                echo "비밀번호가 일치하지 않습니다.";
                return;
            }

            // Check if email already exists
            $this->user->findByEmail();
            if ($this->user->id) {
                echo "이미 등록된 이메일 주소입니다.";
                return;
            }

            $this->user->password_hash = password_hash($password, PASSWORD_BCRYPT);
            $this->user->role = 'user'; // Default role

            if ($this->user->create()) {
                echo "회원가입 성공!";
            } else {
                echo "회원가입 실패.";
            }
        } else {
            include_once __DIR__ . '/../views/register.php';
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->user->email = $_POST['email'];
            $password = $_POST['password'];

            if ($this->user->findByEmail()) {
                if (password_verify($password, $this->user->password_hash)) {
                    session_start();
                    $_SESSION['user_id'] = $this->user->id;
                    $_SESSION['user_email'] = $this->user->email;
                    $_SESSION['user_nickname'] = $this->user->nickname;
                    $_SESSION['user_role'] = $this->user->role;
                    echo "로그인 성공!";
                } else {
                    echo "비밀번호가 일치하지 않습니다.";
                }
            } else {
                echo "등록되지 않은 이메일 주소입니다.";
            }
        } else {
            include_once __DIR__ . '/../views/login.php';
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        echo "로그아웃 되었습니다.";
    }

    public static function isLoggedIn() {
        session_start();
        return isset($_SESSION['user_id']);
    }

    public static function getUserRole() {
        session_start();
        return $_SESSION['user_role'] ?? null;
    }
}
