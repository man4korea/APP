<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\core\Database.php
// Create at 2508041131 Ver1.00

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Load .env file
        $env_path = __DIR__ . '/../../.env';
        if (file_exists($env_path)) {
            $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }
                list($name, $value) = explode('=', $line, 2);
                $_ENV[$name] = $value;
            }
        }

        $this->host = $_ENV['DB_HOST'] ?? DB_HOST;
        $this->db_name = $_ENV['DB_NAME'] ?? DB_NAME;
        $this->username = $_ENV['DB_USER'] ?? DB_USER;
        $this->password = $_ENV['DB_PASS'] ?? DB_PASS;
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
