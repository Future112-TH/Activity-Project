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
                    $request_detail = isset($_POST['request_detail']) ? trim($_POST['request_detail']) : '';
                    $act_amount = isset($_POST['act_amount']) ? (int)trim($_POST['act_amount']) : 0;
                    $act_hour = isset($_POST['act_hour']) ? (int)trim($_POST['act_hour']) : 0;
                    $act_semester = isset($_POST['act_semester']) ? trim($_POST['act_semester']) : '';
                    $act_year = isset($_POST['act_year']) ? trim($_POST['act_year']) : '';
                    
                    // รับรหัสนักศึกษาจาก session
                    $student_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
                    
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (empty($request_detail) || empty($act_amount) || empty($act_hour) || 
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
                    
                    // ค้นหารหัสบันทึกล่าสุด
                    $query = "SELECT MAX(CAST(Com_id AS UNSIGNED)) as max_id FROM comparision";
                    $stmt = $db->prepare($query);
                    $stmt->execute();
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // กำหนดรหัสใหม่ (เริ่มที่ 00001 หรือเพิ่มจากรหัสล่าสุด)
                    $next_id = 1;
                    if ($result && $result['max_id']) {
                        $next_id = intval($result['max_id']) + 1;
                    }
                    $com_id = str_pad($next_id, 5, '0', STR_PAD_LEFT); // รูปแบบ 00001
                    
                    // แปลงปีการศึกษาเป็นวันที่
                    $act_year_date = date('Y-m-d', strtotime($act_year . '-01-01'));
                    
                    // เตรียมข้อมูลสำหรับบันทึกตามโครงสร้างฐานข้อมูล
                    $data = [
                        'Com_id' => $com_id,
                        'Com_name' => $request_detail,  // รายละเอียดกิจกรรมเก็บในฟิลด์ Com_name
                        'Com_amount' => $act_amount,
                        'Com_hour' => $act_hour,
                        'Upload' => $upload_file,
                        'Com_semester' => $act_semester,
                        'Com_year' => $act_year_date,
                        'Com_status' => 'pending',
                        'Stu_id' => $student_id,
                        'RequestType' => $request_type // เก็บประเภทการขอเทียบเพิ่มเติม
                    ];
                    
                    // บันทึกข้อมูล
                    $result = $controller->insert('comparision', $data);
                    
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
            
        case 'delete':
            // ลบข้อมูลการขอเทียบกิจกรรม
            if (isset($_REQUEST['id'])) {
                $com_id = $_REQUEST['id'];
                $student_id = $_SESSION['user_id'];
                
                // ดึงข้อมูลคำร้องเพื่อตรวจสอบ
                $comparision = $controller->getComparisionById($com_id);
                
                // ตรวจสอบว่าคำร้องนี้เป็นของนักศึกษาที่ล็อกอินหรือไม่
                if ($comparision && $comparision['Stu_id'] == $student_id) {
                    // ตรวจสอบว่าสถานะคำร้องเป็น pending หรือไม่
                    if ($comparision['Com_status'] === 'pending' || $comparision['Status'] === 'pending') {
                        // ลบไฟล์ที่อัพโหลด (ถ้ามี)
                        if (!empty($comparision['Upload'])) {
                            $upload_path = '../../uploads/comparision/' . $comparision['Upload'];
                            if (file_exists($upload_path)) {
                                unlink($upload_path);
                            }
                        }
                        
                        // ลบข้อมูลคำร้อง
                        $result = $controller->deleteComparision($com_id);
                        
                        if ($result) {
                            $_SESSION['success'] = "ลบคำร้องขอเทียบกิจกรรมเรียบร้อยแล้ว";
                        } else {
                            $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบคำร้อง";
                        }
                    } else {
                        $_SESSION['error'] = "ไม่สามารถลบคำร้องที่อยู่ระหว่างการพิจารณาหรือพิจารณาแล้ว";
                    }
                } else {
                    $_SESSION['error'] = "ไม่พบคำร้องหรือคุณไม่มีสิทธิ์ลบคำร้องนี้";
                }
                
                header("Location: ../index.php?menu=4");
                exit();
            }
            break;
    }
}
?>