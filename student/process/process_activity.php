<?php
session_start();
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../../config/database.php';
include_once '../../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ตรวจสอบว่ามีการส่ง action มาหรือไม่
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    
    // แยกการทำงานตาม action
    switch ($action) {
        case 'add':
            // เพิ่มข้อมูลกิจกรรม
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $act_name = $_POST['act_name'];
                $act_hour = $_POST['act_hour'];
                $act_start_date = $_POST['act_start_date'];
                $act_stop_date = $_POST['act_stop_date'];
                $act_semester = $_POST['act_semester'];
                $act_status = $_POST['act_status'];
                $act_year = $_POST['act_year'];
                $act_type_id = $_POST['act_type_id'];
                $maj_id = $_POST['maj_id'];
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($act_name) || empty($act_hour) || empty($act_start_date) || empty($act_stop_date) || 
                    empty($act_semester) || empty($act_status) || empty($act_year) || empty($act_type_id) || empty($maj_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    header("Location: ../index.php?menu=9");
                    exit();
                }
                
                // ตรวจสอบวันที่
                $start = new DateTime($act_start_date);
                $stop = new DateTime($act_stop_date);
                
                if ($stop < $start) {
                    $_SESSION['error'] = "วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม";
                    header("Location: ../index.php?menu=9");
                    exit();
                }
                
                // เพิ่มข้อมูลกิจกรรม
                $result = $controller->insertActivity($act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id);
                
                if ($result) {
                    $_SESSION['success'] = "เพิ่มข้อมูลกิจกรรมเรียบร้อยแล้ว";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                }
                
                header("Location: ../index.php?menu=9");
                exit();
            }
            break;
            
        case 'edit':
            // แก้ไขข้อมูลกิจกรรม
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $act_id = $_POST['edit_act_id'];
                $act_name = $_POST['edit_act_name'];
                $act_hour = $_POST['edit_act_hour'];
                $act_start_date = $_POST['edit_act_start_date'];
                $act_stop_date = $_POST['edit_act_stop_date'];
                $act_semester = $_POST['edit_act_semester'];
                $act_status = $_POST['edit_act_status'];
                $act_year = $_POST['edit_act_year'];
                $act_type_id = $_POST['edit_act_type_id'];
                $maj_id = $_POST['edit_maj_id'];
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($act_id) || empty($act_name) || empty($act_hour) || empty($act_start_date) || empty($act_stop_date) || 
                    empty($act_semester) || empty($act_status) || empty($act_year) || empty($act_type_id) || empty($maj_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    header("Location: ../index.php?menu=10");
                    exit();
                }
                
                // ตรวจสอบวันที่
                $start = new DateTime($act_start_date);
                $stop = new DateTime($act_stop_date);
                
                if ($stop < $start) {
                    $_SESSION['error'] = "วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม";
                    header("Location: ../index.php?menu=10");
                    exit();
                }
                
                // อัปเดตข้อมูลกิจกรรม
                $result = $controller->updateActivity($act_id, $act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id);
                
                if ($result) {
                    $_SESSION['success'] = "อัปเดตข้อมูลกิจกรรมเรียบร้อยแล้ว";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                }
                
                header("Location: ../index.php?menu=9");
                exit();
            }
            break;
            
        case 'delete':
            // ลบข้อมูลกิจกรรม
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $act_id = $_GET['id'];
                
                // ลบข้อมูลกิจกรรม
                $result = $controller->deleteActivity($act_id);
                
                if ($result) {
                    $_SESSION['success'] = "ลบข้อมูลกิจกรรมเรียบร้อยแล้ว";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
                }
            } else {
                $_SESSION['error'] = "ไม่พบรหัสกิจกรรมที่ต้องการลบ";
            }
            
            header("Location: ../index.php?menu=9");
            exit();
            break;
            
        default:
            // ถ้าไม่มี action ที่ตรงกับเงื่อนไข ให้กลับไปที่หน้ารายการกิจกรรม
            header("Location: ../index.php?menu=9");
            exit();
            break;
    }
} else {
    // ถ้าไม่มีการส่ง action มา ให้กลับไปที่หน้ารายการกิจกรรม
    header("Location: ../index.php?menu=9");
    exit();
}
?>