<?php
class User{
    private $db;

    function __construct($conn){
        $this->db = $conn;
    }

    function getUser($username, $password){
        try {
            // ใส่ echo หรือ var_dump เพื่อดีบัก
            // echo "ตรวจสอบล็อกอิน: $username, $password<br>";
            
            // คำสั่ง SQL สำหรับดึงข้อมูลจากตาราง login
            $sql = "SELECT * FROM login WHERE user_id = :username AND user_pass = :password";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':password', $password);
            $stmt->execute();
            
            // เพิ่มการดีบักเพื่อเช็คข้อมูลที่ได้
            // echo "จำนวนแถวที่พบ: " . $stmt->rowCount() . "<br>";
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($result) {
                // ดีบักข้อมูลที่ได้
                echo "<pre>";
                // print_r($result);
                echo "</pre>";
                
                // ส่วนที่เหลือคงเดิม...
            }
            
            return $result;
        } catch (PDOException $e) {
            echo "Database error in getUser: " . $e->getMessage();
            return false;
        }
    }
}
?>