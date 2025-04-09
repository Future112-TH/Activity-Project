<?php
// เริ่มต้น session ถ้ายังไม่ได้เริ่ม
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ล้างข้อมูล session ทั้งหมด
$_SESSION = array();
// ลบคุกกี้ session (ถ้ามี)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// ทำลาย session
session_destroy();

// ส่งกลับไปยังหน้าล็อกอิน
header("Location: ../login.php");
exit;
?>