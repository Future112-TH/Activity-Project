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
        case 'check_in':
            // ทำการเช็คอินกิจกรรม
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : $_SESSION['user_id'];
                $activity_id = isset($_POST['activity_id']) ? $_POST['activity_id'] : '';
                $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
                $year = isset($_POST['year']) ? $_POST['year'] : date('Y-m-d');
                
                // ตรวจสอบว่ามีข้อมูลจำเป็นครบถ้วนหรือไม่
                if (empty($student_id) || empty($activity_id) || empty($semester) || empty($year)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
                    header("Location: ../index.php?menu=3");
                    exit();
                }
                
                // ตรวจสอบว่ามีการเช็คอินแล้วหรือไม่
                $check_sql = "SELECT * FROM participation WHERE Stu_id = :student_id AND Act_id = :activity_id";
                $check_stmt = $db->prepare($check_sql);
                $check_stmt->bindParam(':student_id', $student_id);
                $check_stmt->bindParam(':activity_id', $activity_id);
                $check_stmt->execute();
                
                if ($check_stmt->rowCount() > 0) {
                    $_SESSION['error'] = "คุณได้เช็คอินกิจกรรมนี้ไปแล้ว";
                    header("Location: ../index.php?menu=3");
                    exit();
                }
                
                // ดึงข้อมูลกิจกรรม
                $activity = $controller->getActivityById($activity_id);
                
                if (!$activity) {
                    $_SESSION['error'] = "ไม่พบข้อมูลกิจกรรม";
                    header("Location: ../index.php?menu=3");
                    exit();
                }
                
                // เก็บจำนวนชั่วโมงจากกิจกรรม
                $hours = $activity['Act_hour'];
                
                // สร้างเวลาเช็คอินและเช็คเอาท์
                $check_in_time = date('Y-m-d H:i:s');
                $check_out_time = date('Y-m-d H:i:s', strtotime("+{$hours} hours"));
                
                // เพิ่มข้อมูลการเข้าร่วมกิจกรรม
                $insert_sql = "INSERT INTO participation (Stu_id, Act_id, ActSemester, ActYear, Act_hour, CheckIn, CheckOut) 
                              VALUES (:student_id, :activity_id, :semester, :year, :hours, :check_in, :check_out)";
                
                $insert_stmt = $db->prepare($insert_sql);
                $insert_stmt->bindParam(':student_id', $student_id);
                $insert_stmt->bindParam(':activity_id', $activity_id);
                $insert_stmt->bindParam(':semester', $semester);
                $insert_stmt->bindParam(':year', $year);
                $insert_stmt->bindParam(':hours', $hours);
                $insert_stmt->bindParam(':check_in', $check_in_time);
                $insert_stmt->bindParam(':check_out', $check_out_time);
                
                $result = $insert_stmt->execute();
                
                if ($result) {
                    $_SESSION['success'] = "เช็คอินกิจกรรมสำเร็จ";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเช็คอินกิจกรรม";
                }
                
                header("Location: ../index.php?menu=3");
                exit();
            }
            break;
            
        case 'check_out':
            // ทำการเช็คเอาท์กิจกรรม
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                // รับค่าจากฟอร์ม
                $student_id = isset($_POST['student_id']) ? $_POST['student_id'] : $_SESSION['user_id'];
                $activity_id = isset($_POST['activity_id']) ? $_POST['activity_id'] : '';
                
                // ตรวจสอบว่ามีข้อมูลจำเป็นครบถ้วนหรือไม่
                if (empty($student_id) || empty($activity_id)) {
                    $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบถ้วน";
                    header("Location: ../index.php?menu=3");
                    exit();
                }
                
                // อัปเดตเวลาเช็คเอาท์
                $check_out_time = date('Y-m-d H:i:s');
                
                $update_sql = "UPDATE participation 
                              SET CheckOut = :check_out
                              WHERE Stu_id = :student_id AND Act_id = :activity_id";
                
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bindParam(':check_out', $check_out_time);
                $update_stmt->bindParam(':student_id', $student_id);
                $update_stmt->bindParam(':activity_id', $activity_id);
                
                $result = $update_stmt->execute();
                
                if ($result) {
                    $_SESSION['success'] = "เช็คเอาท์กิจกรรมสำเร็จ";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการเช็คเอาท์กิจกรรม";
                }
                
                header("Location: ../index.php?menu=3");
                exit();
            }
            break;
            
        case 'delete':
            // ลบข้อมูลการเข้าร่วมกิจกรรม (สำหรับผู้ดูแลระบบ)
            if (isset($_GET['student_id']) && isset($_GET['activity_id'])) {
                $student_id = $_GET['student_id'];
                $activity_id = $_GET['activity_id'];
                
                // ตรวจสอบสิทธิ์ (เฉพาะ admin)
                if ($_SESSION['user_role'] !== 'admin') {
                    $_SESSION['error'] = "คุณไม่มีสิทธิ์ในการลบข้อมูล";
                    header("Location: ../index.php?menu=3");
                    exit();
                }
                
                $delete_sql = "DELETE FROM participation 
                              WHERE Stu_id = :student_id AND Act_id = :activity_id";
                
                $delete_stmt = $db->prepare($delete_sql);
                $delete_stmt->bindParam(':student_id', $student_id);
                $delete_stmt->bindParam(':activity_id', $activity_id);
                
                $result = $delete_stmt->execute();
                
                if ($result) {
                    $_SESSION['success'] = "ลบข้อมูลการเข้าร่วมกิจกรรมสำเร็จ";
                } else {
                    $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
                }
                
                header("Location: ../index.php?menu=3");
                exit();
            } else {
                $_SESSION['error'] = "ข้อมูลไม่ครบถ้วน";
                header("Location: ../index.php?menu=3");
                exit();
            }
            break;
            
        default:
            // ถ้าไม่มี action ที่ตรงกับเงื่อนไข ให้กลับไปที่หน้าแสดงผลการเข้าร่วมกิจกรรม
            header("Location: ../index.php?menu=3");
            exit();
            break;
    }
} else {
    // ถ้าไม่มีการส่ง action มา ให้ดึงข้อมูลของนักศึกษาที่ล็อกอินเข้าสู่ระบบ
    
    // รับรหัสนักศึกษาจาก session
    $user_id = $_SESSION['user_id'];
    
    // ตรวจสอบว่ารหัสผู้ใช้เป็น username จากตาราง login
    $sql_login = "SELECT * FROM login WHERE user_id = :user_id";
    $stmt_login = $db->prepare($sql_login);
    $stmt_login->bindParam(':user_id', $user_id);
    $stmt_login->execute();
    $login_data = $stmt_login->fetch(PDO::FETCH_ASSOC);
    
    // หากเป็น user จากตาราง login ที่มีการเชื่อมโยงกับนักศึกษา
    if ($login_data && !empty($login_data['Stu_id'])) {
        $student_id = $login_data['Stu_id'];
    } else {
        // ถ้าไม่มีข้อมูลในตาราง login หรือไม่ได้เชื่อมโยงกับนักศึกษา ให้ใช้ user_id เป็น student_id
        $student_id = $user_id;
    }
    
    // ดึงข้อมูลนักศึกษา
    $student = $controller->getStudentById($student_id);
    
    // ดึงข้อมูลการเข้าร่วมกิจกรรม
    $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, a.ActSemester, a.ActYear, at.ActType_Name 
            FROM participation p 
            LEFT JOIN activity a ON p.Act_id = a.Act_id 
            LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
            WHERE p.Stu_id = :stu_id 
            ORDER BY a.Act_start_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':stu_id', $student_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ดึงข้อมูลการเทียบโอนกิจกรรมที่อนุมัติแล้ว
    if (method_exists($controller, 'getApprovedComparisionsByStudentId')) {
        $approved_comparisions = $controller->getApprovedComparisionsByStudentId($student_id);
    } else {
        // ถ้าไม่มีเมธอดนี้ ให้ดึงข้อมูลโดยตรงจาก SQL
        $comp_sql = "SELECT * FROM comparision WHERE Stu_id = :stu_id AND Status = 'approved' ORDER BY RequestDate DESC";
        $comp_stmt = $db->prepare($comp_sql);
        $comp_stmt->bindParam(':stu_id', $student_id);
        $comp_stmt->execute();
        $approved_comparisions = $comp_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // คำนวณจำนวนชั่วโมงกิจกรรมทั้งหมด
    $total_hours = 0;
    foreach ($activities as $activity) {
        $total_hours += $activity['Act_hour'];
    }
    
    // เพิ่มชั่วโมงจากการเทียบโอนกิจกรรม
    foreach ($approved_comparisions as $comparision) {
        $total_hours += $comparision['Act_hour'];
    }
    
    // เก็บตัวแปรลงใน session เพื่อให้ participation_result.php นำไปใช้
    $_SESSION['student_data'] = $student;
    $_SESSION['activities_data'] = $activities;
    $_SESSION['approved_comparisions'] = $approved_comparisions;
    $_SESSION['total_hours'] = $total_hours;
    $_SESSION['real_student_id'] = $student_id;
    
    // กลับไปที่หน้าแสดงผลการเข้าร่วมกิจกรรม
    header("Location: ../index.php?menu=3");
    exit();
}
?>