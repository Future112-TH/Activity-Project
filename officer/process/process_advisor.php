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
            // เพิ่มข้อมูลอาจารย์ที่ปรึกษา
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $prof_id = trim($_POST['prof_id']);
                $prof_fname = trim($_POST['prof_fname']);
                $prof_lname = trim($_POST['prof_lname']);
                $phone = isset($_POST['phone']) ? trim($_POST['phone']) : null;
                $email = isset($_POST['email']) ? trim($_POST['email']) : null;
                $status = trim($_POST['status']);
                $title_id = trim($_POST['title_id']);
                $major_id = trim($_POST['major_id']);
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($prof_id) || empty($prof_fname) || empty($prof_lname) || 
                    empty($status) || empty($title_id) || empty($major_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    header("Location: ../index.php?menu=6");
                    exit();
                }
                
                // ตรวจสอบรูปแบบรหัสอาจารย์
                if (!is_numeric($prof_id) || strlen($prof_id) !== 5) {
                    $_SESSION['error'] = "รหัสอาจารย์ต้องเป็นตัวเลข 5 หลัก";
                    header("Location: ../index.php?menu=6");
                    exit();
                }
                
                // ตรวจสอบว่ามีรหัสอาจารย์นี้อยู่แล้วหรือไม่
                $existingProf = $controller->getProfessorById($prof_id);
                if ($existingProf) {
                    $_SESSION['error'] = "รหัสอาจารย์นี้มีอยู่ในระบบแล้ว";
                    header("Location: ../index.php?menu=6");
                    exit();
                }
                
                // เพิ่มข้อมูลอาจารย์ที่ปรึกษา
                $result = $controller->insertProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $title_id, $major_id);
                
                if ($result) {
                    $_SESSION['success'] = "เพิ่มข้อมูลอาจารย์ที่ปรึกษาเรียบร้อยแล้ว";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                }
                
                header("Location: ../index.php?menu=6");
                exit();
            }
            break;
            
        case 'edit':
            // แก้ไขข้อมูลอาจารย์ที่ปรึกษา
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $prof_id = trim($_POST['edit_prof_id']);
                $prof_fname = trim($_POST['edit_prof_fname']);
                $prof_lname = trim($_POST['edit_prof_lname']);
                $phone = isset($_POST['edit_phone']) ? trim($_POST['edit_phone']) : null;
                $email = isset($_POST['edit_email']) ? trim($_POST['edit_email']) : null;
                $title_id = trim($_POST['edit_title_id']);
                $major_id = trim($_POST['edit_major_id']);
                
                // ตรวจสอบข้อมูลที่จำเป็น
                if (empty($prof_id) || empty($prof_fname) || empty($prof_lname) || 
                    empty($title_id) || empty($major_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                    header("Location: ../index.php?menu=6&edit=" . $prof_id);
                    exit();
                }
                
                // ตรวจสอบว่ามีรหัสอาจารย์นี้อยู่หรือไม่
                $existingProf = $controller->getProfessorById($prof_id);
                if (!$existingProf) {
                    $_SESSION['error'] = "ไม่พบข้อมูลอาจารย์ที่ต้องการแก้ไข";
                    header("Location: ../index.php?menu=6");
                    exit();
                }
                
                // อัปเดตข้อมูลอาจารย์ที่ปรึกษา
                $result = $controller->updateProfessor($prof_id, $prof_fname, $prof_lname, $phone, $email, $status, $title_id, $major_id);
                
                if ($result) {
                    $_SESSION['success'] = "อัปเดตข้อมูลอาจารย์ที่ปรึกษาเรียบร้อยแล้ว";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                }
                
                header("Location: ../index.php?menu=6");
                exit();
            }
            break;
            
        case 'delete':
            // ลบข้อมูลอาจารย์ที่ปรึกษา
            $prof_id = null;
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $prof_id = trim($_GET['id']);
            } else if (isset($_POST['id']) && !empty($_POST['id'])) {
                $prof_id = trim($_POST['id']);
            }
            
            if ($prof_id) {
                // ตรวจสอบว่ามีรหัสอาจารย์นี้อยู่หรือไม่
                $existingProf = $controller->getProfessorById($prof_id);
                if (!$existingProf) {
                    $_SESSION['error'] = "ไม่พบข้อมูลอาจารย์ที่ต้องการลบ";
                    header("Location: ../index.php?menu=6");
                    exit();
                }
                
                // ลบข้อมูลอาจารย์ที่ปรึกษา
                $result = $controller->deleteProfessor($prof_id);
                
                if ($result) {
                    $_SESSION['success'] = "ลบข้อมูลอาจารย์ที่ปรึกษาเรียบร้อยแล้ว";
                } else {
                    // ถ้าไม่สามารถลบได้ อาจมีข้อความแจ้งเตือนจาก controller
                    if (!isset($_SESSION['error'])) {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
                    }
                }
            } else {
                $_SESSION['error'] = "ไม่พบรหัสอาจารย์ที่ต้องการลบ";
            }
            
            header("Location: ../index.php?menu=6");
            exit();
            break;
            
        default:
            // ถ้าไม่มี action ที่ตรงกับเงื่อนไข ให้กลับไปที่หน้ารายการอาจารย์ที่ปรึกษา
            header("Location: ../index.php?menu=6");
            exit();
            break;
    }
} else {
    // ถ้าไม่มีการส่ง action มา ให้กลับไปที่หน้ารายการอาจารย์ที่ปรึกษา
    header("Location: ../index.php?menu=6");
    exit();
}
?>