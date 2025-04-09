<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/controller.php';

// เชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

// ตรวจสอบการทำงาน (action) ที่ต้องการ
$action = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
}

// ดำเนินการตาม action
switch ($action) {
    case 'add':
        // รับค่าจากฟอร์มเพิ่มข้อมูล
        $major_id = isset($_POST['major_id']) ? $_POST['major_id'] : '';
        $major_name = isset($_POST['major_name']) ? $_POST['major_name'] : '';
        $faculty_id = isset($_POST['faculty_id']) ? $_POST['faculty_id'] : '';
        
        // ตรวจสอบข้อมูล
        if (empty($major_id) || empty($major_name) || empty($faculty_id)) {
            $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        } else {
            // เพิ่มข้อมูลลงฐานข้อมูล
            if ($controller->insertMajor($major_id, $major_name, $faculty_id)) {
                $_SESSION['success'] = "เพิ่มสาขาวิชาเรียบร้อยแล้ว";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มสาขาวิชา";
            }
        }
        break;
        
    case 'update':
        // รับค่าจากฟอร์มแก้ไขข้อมูล
        $major_id = isset($_POST['edit_major_id']) ? $_POST['edit_major_id'] : '';
        $major_name = isset($_POST['edit_major_name']) ? $_POST['edit_major_name'] : '';
        $faculty_id = isset($_POST['edit_faculty_id']) ? $_POST['edit_faculty_id'] : '';
        
        // ตรวจสอบข้อมูล
        if (empty($major_id) || empty($major_name) || empty($faculty_id)) {
            $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        } else {
            // อัปเดตข้อมูลในฐานข้อมูล
            if ($controller->updateMajor($major_id, $major_name, $faculty_id)) {
                $_SESSION['success'] = "อัปเดตสาขาวิชาเรียบร้อยแล้ว";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตสาขาวิชา";
            }
        }
        break;
        
    case 'delete':
        // รับค่า ID ที่ต้องการลบ
        $major_id = isset($_GET['id']) ? $_GET['id'] : '';
        
        if (empty($major_id)) {
            $_SESSION['error'] = "ไม่พบรหัสสาขาวิชาที่ต้องการลบ";
        } else {
            // ลบข้อมูลจากฐานข้อมูล
            if ($controller->deleteMajor($major_id)) {
                $_SESSION['success'] = "ลบสาขาวิชาเรียบร้อยแล้ว";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบสาขาวิชา";
            }
        }
        break;
        
    default:
        $_SESSION['error'] = "ไม่ระบุการทำงานที่ต้องการ";
        break;
}

// กลับไปยังหน้าสาขาวิชา
header("Location: ../index.php?menu=5");
exit;
?>