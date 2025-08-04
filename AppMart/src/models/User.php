<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\models\User.php
// Create at 2508041130 Ver1.00

require_once __DIR__ . '/../core/Database.php';

class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $email;
    public $password_hash;
    public $nickname;
    public $role;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET email=:email, password_hash=:password_hash, nickname=:nickname, role=:role";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->password_hash=htmlspecialchars(strip_tags($this->password_hash));
        $this->nickname=htmlspecialchars(strip_tags($this->nickname));
        $this->role=htmlspecialchars(strip_tags($this->role));

        // bind values
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password_hash", $this->password_hash);
        $stmt->bindParam(":nickname", $this->nickname);
        $stmt->bindParam(":role", $this->role);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function findByEmail() {
        $query = "SELECT id, email, password_hash, nickname, role, created_at FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->email = $row['email'];
            $this->password_hash = $row['password_hash'];
            $this->nickname = $row['nickname'];
            $this->role = $row['role'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }
}
