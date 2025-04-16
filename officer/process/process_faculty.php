<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
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
        // รับข้อมูลจากฟอร์ม
        $faculty_id = isset($_POST['faculty_id']) ? trim($_POST['faculty_id']) : '';
        $faculty_name = isset($_POST['faculty_name']) ? trim($_POST['faculty_name']) : '';

        // ตรวจสอบความถูกต้องของข้อมูล
        $errors = [];

        if (empty($faculty_id)) {
            $errors[] = "กรุณากรอกรหัสคณะ";
        }

        if (empty($faculty_name)) {
            $errors[] = "กรุณากรอกชื่อคณะ";
        }

        // ถ้าไม่มีข้อผิดพลาด
        if (empty($errors)) {
            // เพิ่มข้อมูลคณะ
            $result = $controller->insertFaculty($faculty_id, $faculty_name);

            if ($result) {
                // บันทึกข้อความสำเร็จใน session
                $_SESSION['success'] = "เพิ่มคณะ $faculty_name สำเร็จ";
            } else {
                // บันทึกข้อความผิดพลาดใน session
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มคณะ อาจเป็นเพราะรหัสคณะซ้ำ";
            }
        } else {
            // มีข้อผิดพลาดในการตรวจสอบข้อมูล
            $_SESSION['error'] = implode(", ", $errors);
        }
        break;
        
    case 'update':
        // รับข้อมูลจากฟอร์ม
        $faculty_id = isset($_POST['edit_faculty_id']) ? trim($_POST['edit_faculty_id']) : '';
        $faculty_name = isset($_POST['edit_faculty_name']) ? trim($_POST['edit_faculty_name']) : '';
        
        // ตรวจสอบความถูกต้องของข้อมูล
        $errors = [];

        if (empty($faculty_id)) {
            $errors[] = "ไม่พบรหัสคณะ";
        }

        if (empty($faculty_name)) {
            $errors[] = "กรุณากรอกชื่อคณะ";
        }

        // ถ้าไม่มีข้อผิดพลาด
        if (empty($errors)) {
            // อัปเดทข้อมูลคณะ
            $result = $controller->updateFaculty($faculty_id, $faculty_name);

            if ($result) {
                // บันทึกข้อความสำเร็จใน session
                $_SESSION['success'] = "อัปเดทคณะ $faculty_name สำเร็จ";
            } else {
                // บันทึกข้อความผิดพลาดใน session
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดทคณะ รหัสคณะ $faculty_id อาจไม่มีอยู่";
            }
        } else {
            // มีข้อผิดพลาดในการตรวจสอบข้อมูล
            $_SESSION['error'] = implode(", ", $errors);
        }
        break;
        
    case 'delete':
        // ตรวจสอบ ID ที่ส่งมา
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            // ทำความสะอาดข้อมูล
            $faculty_id = htmlspecialchars(strip_tags($_GET['id']));

            // พยายามลบคณะ
            $result = $controller->deleteFaculty($faculty_id);

            if ($result) {
                // ลบสำเร็จ
                $_SESSION['success'] = "ลบคณะรหัส $faculty_id สำเร็จ";
            } else {
                // ลบไม่สำเร็จ
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบคณะ อาจเป็นเพราะมีข้อมูลที่เกี่ยวข้อง";
            }
        } else {
            // ไม่มี ID ที่ส่งมา
            $_SESSION['error'] = "ไม่พบรหัสคณะที่ต้องการลบ";
        }
        break;
        
    default:
        $_SESSION['error'] = "ไม่ระบุการทำงานที่ต้องการ";
        break;
}

// Redirect กลับไปยังหน้าหลัก
header("Location: ../index.php?menu=4");
exit();
?>