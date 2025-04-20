<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/controller.php';

// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // สร้างการเชื่อมต่อกับฐานข้อมูล
    $database = new Database();
    $db = $database->connect();
    $controller = new Controller($db);
    
    // รับค่าจากฟอร์ม
    $user_id = $_POST['user_id'];
    $title = $_POST['title'];  // ส่งชื่อเต็มของคำนำหน้า (นาย, นาง, นางสาว)
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $major_id = $_POST['major'];
    $user_role = $_POST['user_role'];
    
    // รับค่าข้อมูลใหม่ที่เพิ่มเข้ามา
    $birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : null;
    $religion = isset($_POST['religion']) ? $_POST['religion'] : null;
    $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : 'ไทย';
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id) || empty($title) || empty($firstname) || empty($lastname) || 
        empty($phone) || empty($email) || empty($major_id)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        header("Location: ../index.php?menu=1");
        exit();
    }
    
    // บันทึกข้อมูลลงฐานข้อมูล
    $result = false;
    
    if ($user_role == 'student') {
        // ตรวจสอบว่ามีนักศึกษาในฐานข้อมูลหรือไม่
        $student = $controller->getStudentById($user_id);
        
        if ($student) {
            // อัปเดตข้อมูลนักศึกษา
            $result = $controller->updateStudent(
                $user_id,
                $firstname,
                $lastname,
                $phone,
                $email,
                $birthdate, // เพิ่มการส่งค่าวันเกิด
                $religion,  // เพิ่มการส่งค่าศาสนา
                $nationality, // เพิ่มการส่งค่าสัญชาติ
                isset($student['Plan_id']) ? $student['Plan_id'] : $_SESSION['plan_id'],
                $title,  // เราต้องแปลงเป็น title_id ถ้าในฐานข้อมูลเก็บเป็น ID
                isset($student['Prof_id']) ? $student['Prof_id'] : null,
                $major_id
            );
        } else {
            // หากไม่พบในฐานข้อมูล แต่มีข้อมูลใน session
            // เราอาจจะต้องเพิ่มข้อมูลใหม่ หรือเก็บเฉพาะใน session
            $_SESSION['fullname'] = $title . ' ' . $firstname . ' ' . $lastname;
            $_SESSION['phone'] = $phone;
            $_SESSION['email'] = $email;
            
            // บันทึกข้อมูลเพิ่มเติมใน session
            $_SESSION['birthdate'] = $birthdate;
            $_SESSION['religion'] = $religion;
            $_SESSION['nationality'] = $nationality;
            
            $result = true; // สมมติว่าบันทึกสำเร็จ
        }
    } else {
        // กรณีเป็นอาจารย์หรือผู้ดูแลระบบ
        // เพิ่มโค้ดอัปเดตข้อมูลอาจารย์ตามโครงสร้างฐานข้อมูล
    }
    
    if ($result) {
        $_SESSION['success'] = "อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
    
    header("Location: ../index.php?menu=1");
    exit();
} else {
    // ถ้าไม่ได้ส่งข้อมูลมาทาง POST
    $_SESSION['error'] = "การเข้าถึงไม่ถูกต้อง";
    header("Location: ../index.php?menu=1");
    exit();
}
?>