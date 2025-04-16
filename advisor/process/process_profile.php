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
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $title_id = isset($_POST['title']) ? $_POST['title'] : '';
    $firstname = isset($_POST['firstname']) ? $_POST['firstname'] : '';
    $lastname = isset($_POST['lastname']) ? $_POST['lastname'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $major_id = isset($_POST['major']) ? $_POST['major'] : '';
    $user_role = isset($_POST['user_role']) ? $_POST['user_role'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : $user_role;
    
    // เพิ่มการดีบัก
    error_log("Update profile data: " . json_encode($_POST));
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (empty($user_id) || empty($title_id) || empty($firstname) || empty($lastname) || 
        empty($phone) || empty($email) || empty($major_id)) {
        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // ตรวจสอบรูปแบบเบอร์โทรศัพท์
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $_SESSION['error'] = "เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก";
        header("Location: ../index.php?menu=2");
        exit();
    }
    
    // อัปเดตข้อมูลตามบทบาทของผู้ใช้
    $result = false;
    
    if ($user_role == 'student') {
        // อัปเดตข้อมูลนักศึกษา
        $student = $controller->getStudentById($user_id);
        if ($student) {
            $result = $controller->updateStudent(
                $user_id,
                $firstname,
                $lastname,
                $phone,
                $email,
                $student['Birthdate'],
                $student['Religion'],
                $student['Nationality'],
                $student['Plan_id'],
                $title_id,
                $student['Prof_id'],
                $major_id
            );
        }
    } else {
        // อัปเดตข้อมูลอาจารย์หรือผู้ดูแลระบบ
        $professor = $controller->getProfessorById($user_id);
        if ($professor) {
            $result = $controller->updateProfessor(
                $user_id,
                $firstname,
                $lastname,
                $phone,
                $email,
                $title_id,
                $major_id
            );
        } else {
            // ถ้าไม่พบข้อมูลในฐานข้อมูล ให้เพิ่มข้อมูลใหม่
            $result = $controller->insertProfessor(
                $user_id,
                $firstname,
                $lastname,
                $phone,
                $email,
                $title_id,
                $major_id
            );
        }
    }
    
    if ($result) {
        $_SESSION['success'] = "อัปเดตข้อมูลส่วนตัวเรียบร้อยแล้ว";
        
        // อัปเดต session
        if (isset($_SESSION['fullname'])) {
            // ดึงชื่อคำนำหน้าจาก title_id
            $titleObj = $controller->getTitleById($title_id);
            $title_name = $titleObj ? $titleObj['Title_name'] : '';
            
            $_SESSION['fullname'] = $title_name . ' ' . $firstname . ' ' . $lastname;
            $_SESSION['phone'] = $phone;
            $_SESSION['email'] = $email;
            
            // อัปเดตชื่อสาขาใน session
            $majorObj = $controller->getMajorById($major_id);
            if ($majorObj) {
                $_SESSION['major_name'] = $majorObj['Maj_name'];
                $_SESSION['major_id'] = $major_id;
            }
        }
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
    
    header("Location: ../index.php?menu=2");
    exit();
} else {
    // ถ้าไม่ได้ส่งข้อมูลมาทาง POST
    $_SESSION['error'] = "การเข้าถึงไม่ถูกต้อง";
    header("Location: ../index.php?menu=2");
    exit();
}