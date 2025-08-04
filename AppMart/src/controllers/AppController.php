<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\controllers\AppController.php
// Create at 2508041146 Ver1.00

require_once __DIR__ . '/../models/App.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/AuthController.php'; // For isLoggedIn and getUserRole

class AppController {
    private $app;

    public function __construct() {
        $this->app = new App();
    }

    public function registerApp() {
        if (!AuthController::isLoggedIn() || AuthController::getUserRole() !== 'developer') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->app->title = $_POST['title'];
            $this->app->description = $_POST['description'];
            $this->app->tech_stack = $_POST['tech_stack'];
            $this->app->db_type = $_POST['db_type'];
            $this->app->file_url = $_POST['file_url'];
            $this->app->thumbnail = $_POST['thumbnail'];
            $this->app->price = $_POST['price'];
            $this->app->owner_id = $_SESSION['user_id'];

            if ($this->app->create()) {
                echo "앱 등록 성공!";
            } else {
                echo "앱 등록 실패.";
            }
        } else {
            include_once __DIR__ . '/../views/app_form.php';
        }
    }

    public function updateApp() {
        if (!AuthController::isLoggedIn() || AuthController::getUserRole() !== 'developer') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->app->id = $_GET['id'] ?? null;
            if (!$this->app->id) {
                echo "유효하지 않은 앱 ID입니다.";
                return;
            }

            $this->app->title = $_POST['title'];
            $this->app->description = $_POST['description'];
            $this->app->tech_stack = $_POST['tech_stack'];
            $this->app->db_type = $_POST['db_type'];
            $this->app->file_url = $_POST['file_url'];
            $this->app->thumbnail = $_POST['thumbnail'];
            $this->app->price = $_POST['price'];
            $this->app->owner_id = $_SESSION['user_id'];

            if ($this->app->update()) {
                echo "앱 수정 성공!";
            } else {
                echo "앱 수정 실패.";
            }
        } else {
            $this->app->id = $_GET['id'] ?? null;
            if ($this->app->id && $this->app->findById()) {
                if ($this->app->owner_id !== $_SESSION['user_id']) {
                    echo "이 앱을 수정할 권한이 없습니다.";
                    return;
                }
                $app_data = [
                    'id' => $this->app->id,
                    'title' => $this->app->title,
                    'description' => $this->app->description,
                    'tech_stack' => $this->app->tech_stack,
                    'db_type' => $this->app->db_type,
                    'file_url' => $this->app->file_url,
                    'thumbnail' => $this->app->thumbnail,
                    'price' => $this->app->price
                ];
                include_once __DIR__ . '/../views/app_form.php';
            } else {
                echo "앱을 찾을 수 없습니다.";
            }
        }
    }

    public function deleteApp() {
        if (!AuthController::isLoggedIn() || AuthController::getUserRole() !== 'developer') {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->app->id = $_POST['id'] ?? null;
            if (!$this->app->id) {
                echo "유효하지 않은 앱 ID입니다.";
                return;
            }
            $this->app->owner_id = $_SESSION['user_id'];

            if ($this->app->delete()) {
                echo "앱 삭제 성공!";
            } else {
                echo "앱 삭제 실패.";
            }
        } else {
            echo "잘못된 접근입니다.";
        }
    }

    public function myApps() {
        if (!AuthController::isLoggedIn() || AuthController::getUserRole() !== 'developer') {
            header('Location: /login');
            exit();
        }

        $this->app->owner_id = $_SESSION['user_id'];
        $stmt = $this->app->findByOwnerId();
        $apps_data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $apps_data[] = $row;
        }

        include_once __DIR__ . '/../views/my_apps.php';
    }

    public function listApps() {
        $stmt = $this->app->readAllApproved();
        $apps_data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $apps_data[] = $row;
        }
        include_once __DIR__ . '/../views/app_list.php';
    }

    public function appDetail() {
        $this->app->id = $_GET['id'] ?? null;
        if ($this->app->id && $this->app->findById()) {
            $app_data = [
                'id' => $this->app->id,
                'title' => $this->app->title,
                'description' => $this->app->description,
                'tech_stack' => $this->app->tech_stack,
                'db_type' => $this->app->db_type,
                'file_url' => $this->app->file_url,
                'thumbnail' => $this->app->thumbnail,
                'price' => $this->app->price,
                'status' => $this->app->status,
                'owner_id' => $this->app->owner_id,
                'created_at' => $this->app->created_at
            ];
            include_once __DIR__ . '/../views/app_detail.php';
        } else {
            echo "앱을 찾을 수 없습니다.";
        }
    }

    public function downloadApp() {
        $this->app->id = $_GET['id'] ?? null;
        if (!$this->app->id || !$this->app->findById()) {
            echo "유효하지 않거나 찾을 수 없는 앱입니다.";
            return;
        }

        if ($this->app->price > 0) {
            echo "유료 앱은 다운로드할 수 없습니다.";
            return;
        }

        if ($this->app->status !== 'approved') {
            echo "승인되지 않은 앱은 다운로드할 수 없습니다.";
            return;
        }

        $file_path = $this->app->file_url;
        $file_name = basename($file_path);

        if (file_exists($file_path)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "파일을 찾을 수 없습니다.";
        }
    }
}
