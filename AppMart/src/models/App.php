<?php
// C:\Users\man4k\OneDrive\문서\APP\AppMart\src\models\App.php
// Create at 2508041145 Ver1.00

require_once __DIR__ . '/../core/Database.php';

class App {
    private $conn;
    private $table_name = "apps";

    public $id;
    public $title;
    public $description;
    public $tech_stack;
    public $db_type;
    public $file_url;
    public $thumbnail;
    public $price;
    public $status;
    public $owner_id;
    public $created_at;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, description=:description, tech_stack=:tech_stack, db_type=:db_type, file_url=:file_url, thumbnail=:thumbnail, price=:price, owner_id=:owner_id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->tech_stack=htmlspecialchars(strip_tags($this->tech_stack));
        $this->db_type=htmlspecialchars(strip_tags($this->db_type));
        $this->file_url=htmlspecialchars(strip_tags($this->file_url));
        $this->thumbnail=htmlspecialchars(strip_tags($this->thumbnail));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->owner_id=htmlspecialchars(strip_tags($this->owner_id));

        // bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tech_stack", $this->tech_stack);
        $stmt->bindParam(":db_type", $this->db_type);
        $stmt->bindParam(":file_url", $this->file_url);
        $stmt->bindParam(":thumbnail", $this->thumbnail);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":owner_id", $this->owner_id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET title=:title, description=:description, tech_stack=:tech_stack, db_type=:db_type, file_url=:file_url, thumbnail=:thumbnail, price=:price WHERE id=:id AND owner_id=:owner_id";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->title=htmlspecialchars(strip_tags($this->title));
        $this->description=htmlspecialchars(strip_tags($this->description));
        $this->tech_stack=htmlspecialchars(strip_tags($this->tech_stack));
        $this->db_type=htmlspecialchars(strip_tags($this->db_type));
        $this->file_url=htmlspecialchars(strip_tags($this->file_url));
        $this->thumbnail=htmlspecialchars(strip_tags($this->thumbnail));
        $this->price=htmlspecialchars(strip_tags($this->price));
        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->owner_id=htmlspecialchars(strip_tags($this->owner_id));

        // bind values
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":tech_stack", $this->tech_stack);
        $stmt->bindParam(":db_type", $this->db_type);
        $stmt->bindParam(":file_url", $this->file_url);
        $stmt->bindParam(":thumbnail", $this->thumbnail);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":owner_id", $this->owner_id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ? AND owner_id = ?";

        $stmt = $this->conn->prepare($query);

        // sanitize
        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->owner_id=htmlspecialchars(strip_tags($this->owner_id));

        // bind values
        $stmt->bindParam(1, $this->id);
        $stmt->bindParam(2, $this->owner_id);

        if($stmt->execute()){
            return true;
        }

        return false;
    }

    public function findById() {
        $query = "SELECT id, title, description, tech_stack, db_type, file_url, thumbnail, price, status, owner_id, created_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->tech_stack = $row['tech_stack'];
            $this->db_type = $row['db_type'];
            $this->file_url = $row['file_url'];
            $this->thumbnail = $row['thumbnail'];
            $this->price = $row['price'];
            $this->status = $row['status'];
            $this->owner_id = $row['owner_id'];
            $this->created_at = $row['created_at'];
            return true;
        }

        return false;
    }

    public function findByOwnerId() {
        $query = "SELECT id, title, description, tech_stack, db_type, file_url, thumbnail, price, status, owner_id, created_at FROM " . $this->table_name . " WHERE owner_id = ? ORDER BY created_at DESC";

        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $this->owner_id);
        $stmt->execute();

        return $stmt;
    }

    public function readAllApproved() {
        $query = "SELECT id, title, description, tech_stack, db_type, file_url, thumbnail, price, owner_id, created_at FROM " . $this->table_name . " WHERE status = 'approved' ORDER BY created_at DESC";

        $stmt = $this->conn->prepare( $query );
        $stmt->execute();

        return $stmt;
    }
}
