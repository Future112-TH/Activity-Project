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

// เพิ่มการบันทึก log เพื่อดีบัก
error_log("process_activity.php called with action: " . (isset($_REQUEST['action']) ? $_REQUEST['action'] : 'none'));
error_log("REQUEST data: " . print_r($_REQUEST, true));
error_log("POST data: " . print_r($_POST, true));

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
                $act_semester = $_POST['act_semester']; // ค่าที่รวมมาจาก act_term และ act_year_number
                $act_status = $_POST['act_status'];
                $act_year = $_POST['act_year'];
                $act_type_id = $_POST['act_type_id'];
                $maj_id = $_POST['maj_id'];
                
                // บันทึกข้อมูลเพื่อตรวจสอบ
                error_log("Activity Add Form Data: " . json_encode([
                    'name' => $act_name,
                    'hour' => $act_hour,
                    'start_date' => $act_start_date,
                    'stop_date' => $act_stop_date,
                    'semester' => $act_semester,
                    'status' => $act_status,
                    'year' => $act_year,
                    'type_id' => $act_type_id,
                    'maj_id' => $maj_id
                ], JSON_UNESCAPED_UNICODE));
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($act_name) || empty($act_hour) || empty($act_start_date) || empty($act_stop_date) || 
                    empty($act_semester) || empty($act_status) || empty($act_year) || empty($act_type_id) || empty($maj_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    error_log("Error: Missing required fields for activity add");
                    header("Location: ../index.php?menu=10");
                    exit();
                }
                
                // ตรวจสอบวันที่
                $start = new DateTime($act_start_date);
                $stop = new DateTime($act_stop_date);
                
                if ($stop < $start) {
                    $_SESSION['error'] = "วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม";
                    error_log("Error: End date is before start date");
                    header("Location: ../index.php?menu=10");
                    exit();
                }
                
                // เพิ่มข้อมูลกิจกรรม (รหัสจะถูกสร้างอัตโนมัติในฟังก์ชัน insertActivity)
                $result = $controller->insertActivity($act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id);
                
                if ($result) {
                    $_SESSION['success'] = "เพิ่มข้อมูลกิจกรรมเรียบร้อยแล้ว";
                    error_log("Success: Activity added successfully");
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                    error_log("Error: Failed to add activity");
                }
                
                header("Location: ../index.php?menu=10");
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
                $act_semester = $_POST['edit_act_semester']; // ค่าที่รวมมาจาก edit_act_term และ edit_act_year_number
                $act_status = $_POST['edit_act_status'];
                $act_year = $_POST['edit_act_year'];
                $act_type_id = $_POST['edit_act_type_id'];
                $maj_id = $_POST['edit_maj_id'];
                
                // บันทึกข้อมูลเพื่อตรวจสอบ
                error_log("Activity Edit Form Data: " . json_encode([
                    'id' => $act_id,
                    'name' => $act_name,
                    'hour' => $act_hour,
                    'start_date' => $act_start_date,
                    'stop_date' => $act_stop_date,
                    'semester' => $act_semester,
                    'status' => $act_status,
                    'year' => $act_year,
                    'type_id' => $act_type_id,
                    'maj_id' => $maj_id
                ], JSON_UNESCAPED_UNICODE));
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($act_id) || empty($act_name) || empty($act_hour) || empty($act_start_date) || empty($act_stop_date) || 
                    empty($act_semester) || empty($act_status) || empty($act_year) || empty($act_type_id) || empty($maj_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    error_log("Error: Missing required fields for activity edit");
                    header("Location: ../index.php?menu=10&edit=" . $act_id);
                    exit();
                }
                
                // ตรวจสอบวันที่
                $start = new DateTime($act_start_date);
                $stop = new DateTime($act_stop_date);
                
                if ($stop < $start) {
                    $_SESSION['error'] = "วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม";
                    error_log("Error: End date is before start date");
                    header("Location: ../index.php?menu=10&edit=" . $act_id);
                    exit();
                }
                
                // อัปเดตข้อมูลกิจกรรม
                $result = $controller->updateActivity($act_id, $act_name, $act_hour, $act_start_date, $act_stop_date, $act_semester, $act_status, $act_year, $act_type_id, $maj_id);
                
                if ($result) {
                    $_SESSION['success'] = "อัปเดตข้อมูลกิจกรรมเรียบร้อยแล้ว";
                    error_log("Success: Activity updated successfully");
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                    error_log("Error: Failed to update activity");
                }
                
                header("Location: ../index.php?menu=10");
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
                    error_log("Success: Activity deleted successfully");
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
                    error_log("Error: Failed to delete activity");
                }
            } else {
                $_SESSION['error'] = "ไม่พบรหัสกิจกรรมที่ต้องการลบ";
                error_log("Error: Activity ID not provided for delete");
            }
            
            header("Location: ../index.php?menu=10");
            exit();
            break;
            
        default:
            // ถ้าไม่มี action ที่ตรงกับเงื่อนไข ให้กลับไปที่หน้ารายการกิจกรรม
            header("Location: ../index.php?menu=10");
            exit();
            break;
    }
} else {
    // ถ้าไม่มีการส่ง action มา ให้กลับไปที่หน้ารายการกิจกรรม
    header("Location: ../index.php?menu=10");
    exit();
}
?>