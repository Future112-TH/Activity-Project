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
            // เพิ่มข้อมูลการขอเทียบกิจกรรม
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                try {
                    // รับค่าจากฟอร์ม
                    $request_type = isset($_POST['request_type']) ? trim($_POST['request_type']) : '';
                    $act_id = isset($_POST['act_id']) ? trim($_POST['act_id']) : '';
                    // ดึงชื่อกิจกรรมจาก act_id
                    $activity = $controller->getActivityById($act_id);
                    if ($activity) {
                        $request_detail = $activity['Act_name']; // เก็บชื่อกิจกรรมใน request_detail
                    } else {
                        $_SESSION['error'] = "ไม่พบข้อมูลกิจกรรมที่เลือก";
                        header("Location: ../index.php?menu=4");
                        exit();
                    }
                    $act_amount = isset($_POST['act_amount']) ? (int)trim($_POST['act_amount']) : 0;
                    $act_hour = isset($_POST['act_hour']) ? (int)trim($_POST['act_hour']) : 0;
                    $act_semester = isset($_POST['act_semester']) ? trim($_POST['act_semester']) : '';
                    $act_year = isset($_POST['act_year']) ? trim($_POST['act_year']) : '';
                    
                    // รับรหัสนักศึกษาจาก session
                    $student_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
                    
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (empty($request_type) || empty($request_detail) || empty($act_amount) || empty($act_hour) || 
                        empty($act_semester) || empty($act_year) || empty($student_id)) {
                        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                        header("Location: ../index.php?menu=4");
                        exit();
                    }
                    
                    $upload_file = null; // ค่าเริ่มต้นเป็น null
                    $upload_path = null;

                    if (isset($_FILES['upload_file']) && $_FILES['upload_file']['error'] == 0) {
                        $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
                        $max_size = 2 * 1024 * 1024; // 2MB
                        
                        $file_name = $_FILES['upload_file']['name'];
                        $file_tmp = $_FILES['upload_file']['tmp_name'];
                        $file_size = $_FILES['upload_file']['size'];
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        
                        // ตรวจสอบนามสกุลไฟล์
                        if (!in_array($file_ext, $allowed_types)) {
                            $_SESSION['error'] = "รองรับเฉพาะไฟล์ PDF, JPG, PNG เท่านั้น";
                            header("Location: ../index.php?menu=4");
                            exit();
                        }
                        
                        // ตรวจสอบขนาดไฟล์
                        if ($file_size > $max_size) {
                            $_SESSION['error'] = "ไฟล์มีขนาดใหญ่เกินไป (สูงสุด 2MB)";
                            header("Location: ../index.php?menu=4");
                            exit();
                        }
                        
                        // สร้างชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
                        $new_file_name = $student_id . '_' . date('YmdHis') . '.' . $file_ext;
                        $upload_dir = '../../uploads/comparision/';
                        
                        // สร้างโฟลเดอร์ถ้ายังไม่มี
                        if (!is_dir($upload_dir)) {
                            mkdir($upload_dir, 0777, true);
                        }
                        
                        $upload_path = $upload_dir . $new_file_name;
                        
                        // อัปโหลดไฟล์
                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            $upload_file = $new_file_name;
                        } else {
                            $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
                            header("Location: ../index.php?menu=4");
                            exit();
                        }
                    }
                    
                    // สร้างวันที่ปัจจุบัน
                    $request_date = date('Y-m-d H:i:s');
                    
                    // เพิ่มข้อมูลการขอเทียบกิจกรรม
                    $result = $controller->insertComparision(
                        $request_type,
                        $request_detail,
                        $act_amount, 
                        $act_hour, 
                        $upload_file, 
                        $act_semester, 
                        $act_year,
                        $request_date,
                        $act_id, 
                        $student_id
                    );
                    
                    if ($result) {
                        $_SESSION['success'] = "ยื่นคำร้องขอเทียบกิจกรรมเรียบร้อยแล้ว";
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการยื่นคำร้อง";
                        
                        // ลบไฟล์ที่อัพโหลดหากบันทึกข้อมูลไม่สำเร็จ
                        if (!empty($upload_file) && file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
                }
                
                header("Location: ../index.php?menu=4");
                exit();
            }
            break;
            
        // ส่วนอื่น ๆ ของโค้ดยังคงเหมือนเดิม (edit, delete)...
        
    }
}