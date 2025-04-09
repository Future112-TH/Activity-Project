<?php 
// ตรวจสอบว่า session ถูกเริ่มต้นหรือไม่
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/sweetalert.php';

// ตรวจสอบการ login ทั่วไป
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $alert = new SweetAlert('Warning','กรุณาเข้าสู่ระบบ','warning');
    echo $alert->setRedirectUrl('../login.php');
    exit;
}

// ตรวจสอบสิทธิ์ตามโฟลเดอร์
$currentFolder = basename(dirname($_SERVER['PHP_SELF']));

// function สำหรับเคลียร์ session และบังคับให้ login ใหม่
function forceRelogin($message) {
    global $alert;
    // เคลียร์ session ทั้งหมด
    session_unset();
    session_destroy();
    // สร้าง session ใหม่เพื่อใช้กับ SweetAlert
    session_start();
    // บันทึกลงล็อก (optional)
    error_log("ตรวจพบความพยายามเข้าถึงที่ไม่ได้รับอนุญาต: IP " . $_SERVER['REMOTE_ADDR']);
    // แสดงข้อความและเปลี่ยนเส้นทาง
    $alert = new SweetAlert('Security Alert', $message, 'error');
    echo $alert->setRedirectUrl('../login.php');
    exit;
}

// ตรวจสอบสิทธิ์ตามโฟลเดอร์อย่างเข้มงวด
switch ($currentFolder) {
    case 'student':
        if ($_SESSION['status'] !== 'student') {
            forceRelogin('ตรวจพบความพยายามเข้าถึงส่วนของนักศึกษาที่ไม่ได้รับอนุญาต กรุณาเข้าสู่ระบบใหม่');
        }
        break;
    case 'advisor':
        if ($_SESSION['status'] !== 'professor') {
            forceRelogin('ตรวจพบความพยายามเข้าถึงส่วนของอาจารย์ที่ไม่ได้รับอนุญาต กรุณาเข้าสู่ระบบใหม่');
        }
        break;
    case 'officer':
        if ($_SESSION['status'] !== 'admin' || $_SESSION['is_admin'] !== true) {
            forceRelogin('ตรวจพบความพยายามเข้าถึงส่วนของผู้ดูแลระบบที่ไม่ได้รับอนุญาต กรุณาเข้าสู่ระบบใหม่');
        }
        break;
    default:
        // ป้องกันการเข้าถึงโฟลเดอร์ที่ไม่ได้กำหนดสิทธิ์
        forceRelogin('ตรวจพบความพยายามเข้าถึงพื้นที่ที่ไม่ได้รับอนุญาต กรุณาเข้าสู่ระบบใหม่');
}

// เพิ่มการตรวจสอบการพยายามเข้าถึงข้ามไดเรกทอรี่
$requestUri = $_SERVER['REQUEST_URI'];
if (strpos($requestUri, '..') !== false || strpos($requestUri, '../') !== false) {
    forceRelogin('ตรวจพบรูปแบบการเข้าถึงที่น่าสงสัย กรุณาเข้าสู่ระบบใหม่');
}

// เพิ่มการตรวจสอบ referer หากต้องการ
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    
    // ตรวจสอบว่า referer มาจากเว็บไซต์เดียวกันหรือไม่
    if (strpos($referer, $baseUrl) !== 0) {
        // มาจากภายนอก - อาจเป็นการพยายามที่น่าสงสัย
        forceRelogin('ตรวจพบการเข้าถึงจากแหล่งที่น่าสงสัย กรุณาเข้าสู่ระบบใหม่');
    }
}
?>