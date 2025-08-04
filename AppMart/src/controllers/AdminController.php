<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\controllers\AdminController.php
// Create at 2508041201 Ver1.00

require_once __DIR__ . '/../models/App.php';
require_once __DIR__ . '/AuthController.php'; // For isLoggedIn and getUserRole

class AdminController {
    private $app;

    public function __construct() {
        $this->app = new App();
    }

    private function checkAdminAccess() {
        if (!AuthController::isLoggedIn() || AuthController::getUserRole() !== 'admin') {
            header('Location: /login');
            exit();
        }
    }

    public function pendingApps() {
        $this->checkAdminAccess();

        $query = "SELECT id, title, owner_id, created_at FROM apps WHERE status = 'pending' ORDER BY created_at DESC";
        $stmt = $this->app->conn->prepare($query);
        $stmt->execute();

        $pending_apps_data = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $pending_apps_data[] = $row;
        }

        include_once __DIR__ . '/../views/admin/pending_apps.php';
    }

    public function approveApp() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $app_id = $_POST['id'] ?? null;
            if (!$app_id) {
                echo "유효하지 않은 앱 ID입니다.";
                return;
            }

            $query = "UPDATE apps SET status = 'approved' WHERE id = ?";
            $stmt = $this->app->conn->prepare($query);
            $stmt->bindParam(1, $app_id);

            if ($stmt->execute()) {
                echo "앱이 성공적으로 승인되었습니다.";
            } else {
                echo "앱 승인에 실패했습니다.";
            }
        } else {
            echo "잘못된 접근입니다.";
        }
    }

    public function rejectApp() {
        $this->checkAdminAccess();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $app_id = $_POST['id'] ?? null;
            if (!$app_id) {
                echo "유효하지 않은 앱 ID입니다.";
                return;
            }

            $query = "UPDATE apps SET status = 'rejected' WHERE id = ?";
            $stmt = $this->app->conn->prepare($query);
            $stmt->bindParam(1, $app_id);

            if ($stmt->execute()) {
                echo "앱이 성공적으로 반려되었습니다.";
            } else {
                echo "앱 반려에 실패했습니다.";
            }
        } else {
            echo "잘못된 접근입니다.";
        }
    }
}
