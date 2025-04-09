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
        // รับค่าจากฟอร์ม
        $activity_type_id = isset($_POST['activity_type_id']) ? $_POST['activity_type_id'] : '';
        $activity_type_name = isset($_POST['activity_type_name']) ? $_POST['activity_type_name'] : '';
        
        // ตรวจสอบข้อมูล
        if (empty($activity_type_id) || empty($activity_type_name)) {
            $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
            break;
        }
        
        // ตรวจสอบว่า ID เป็นตัวเลขหรือไม่
        if (!is_numeric($activity_type_id)) {
            $_SESSION['error'] = "รหัสประเภทกิจกรรมต้องเป็นตัวเลขเท่านั้น";
            break;
        }
        
        // เพิ่มข้อมูลลงฐานข้อมูล
        if ($controller->insertActivityType($activity_type_id, $activity_type_name)) {
            $_SESSION['success'] = "เพิ่มประเภทกิจกรรมเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มประเภทกิจกรรม รหัสนี้อาจมีอยู่แล้วในระบบ";
        }
        break;
        
    case 'update':
        // รับค่าจากฟอร์ม
        $activity_type_id = isset($_POST['edit_activity_type_id']) ? $_POST['edit_activity_type_id'] : '';
        $activity_type_name = isset($_POST['edit_activity_type_name']) ? $_POST['edit_activity_type_name'] : '';
        
        // ตรวจสอบข้อมูล
        if (empty($activity_type_id) || empty($activity_type_name)) {
            $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
            break;
        }
        
        // อัปเดตข้อมูลในฐานข้อมูล
        if ($controller->updateActivityType($activity_type_id, $activity_type_name)) {
            $_SESSION['success'] = "อัปเดตประเภทกิจกรรมเรียบร้อยแล้ว";
        } else {
            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตประเภทกิจกรรม";
        }
        break;
        
    case 'delete':
        // ตรวจสอบว่ามีการส่ง ID มาหรือไม่
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $activity_type_id = $_GET['id'];
            
            // ลบข้อมูลจากฐานข้อมูล
            if ($controller->deleteActivityType($activity_type_id)) {
                $_SESSION['success'] = "ลบประเภทกิจกรรมเรียบร้อยแล้ว";
            } else {
                $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบประเภทกิจกรรม";
            }
        } else {
            $_SESSION['error'] = "ไม่พบรหัสประเภทกิจกรรมที่ต้องการลบ";
        }
        break;
        
    default:
        $_SESSION['error'] = "ไม่ระบุการทำงานที่ต้องการ";
        break;
}

// กลับไปยังหน้าประเภทกิจกรรม
header("Location: ../index.php?menu=8");
exit;
?>