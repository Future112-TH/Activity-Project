<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/controller.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // ตรวจสอบว่ามีการล็อกอินหรือไม่
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        $_SESSION['error'] = "กรุณาเข้าสู่ระบบก่อนเปลี่ยนรหัสผ่าน";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // รับค่าจากฟอร์ม
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // ตรวจสอบว่ากรอกข้อมูลครบถ้วนหรือไม่
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // ตรวจสอบความยาวของรหัสผ่านใหม่
    if (strlen($new_password) < 8) {
        $_SESSION['error'] = "รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 8 ตัวอักษร";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // ตรวจสอบว่ารหัสผ่านใหม่และการยืนยันตรงกัน
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // สร้างการเชื่อมต่อกับฐานข้อมูล
    $database = new Database();
    $db = $database->connect();
    $controller = new Controller($db);
    
    // รับ user_id จาก session
    $user_id = $_SESSION['user_id'];
    
    // ตรวจสอบรหัสผ่านปัจจุบัน
    $isValidPassword = $controller->verifyPassword($user_id, $current_password);
    
    if (!$isValidPassword) {
        $_SESSION['error'] = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // เปลี่ยนรหัสผ่าน
    $isUpdated = $controller->updatePassword($user_id, $new_password);
    
    if ($isUpdated) {
        // เก็บข้อความแจ้งเตือนสำเร็จก่อนทำการ logout
        $_SESSION['success'] = "เปลี่ยนรหัสผ่านสำเร็จ กรุณา Login เข้าสู่ระบบใหม่ด้วยรหัสผ่านที่เปลี่ยนแปลง";
        
        // เก็บข้อความแจ้งเตือนไว้ในตัวแปรชั่วคราว
        $success_message = $_SESSION['success'];
        
        // ล้างข้อมูล session ทั้งหมด
        $_SESSION = array();
        
        // ลบ session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // ทำลาย session
        session_destroy();
        
        // เริ่ม session ใหม่เพื่อส่งข้อความแจ้งเตือน
        session_start();
        $_SESSION['success'] = $success_message;
        
        // ส่งกลับไปยังหน้า login
        header("Location: ../../login.php");
        exit();
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเปลี่ยนรหัสผ่าน";
        header("Location: ../index.php?menu=2");
        exit();
    }
} else {
    // ถ้าไม่ได้เข้าถึงผ่าน POST
    $_SESSION['error'] = "การเข้าถึงไม่ถูกต้อง";
    header("Location: ../index.php?menu=2");
    exit();
}
?>