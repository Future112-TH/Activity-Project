<?php
class Database {
    private $host = "localhost";
    private $db_name = "activitydb";
    private $username = "root";
    private $password = "";
    public $conn;

    // เชื่อมต่อกับฐานข้อมูล
    public function connect() {
        $this->conn = null;
        try {
            $dns = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8";
            $this->conn = new PDO($dns, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // echo "Connection successfully";
        } catch(PDOException $exception) {
            echo "Connection failed: " . $exception->getMessage();
            die();
        }
        return $this->conn;
    }
}

// สร้าง instance ของ Database และเชื่อมต่อฐานข้อมูล
$database = new Database();
$conn = $database->connect();

// โหลด class ที่จำเป็น
require_once "controller.php";
require_once "user.php";

// สร้าง instance ของ Controller และ User
$controller = new Controller($conn);
$user = new User($conn);
?>