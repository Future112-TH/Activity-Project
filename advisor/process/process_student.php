<?php
session_start();
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
            // เพิ่มข้อมูลนักศึกษา
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                try {
                    // รับค่าจากฟอร์ม
                    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
                    $title_id = isset($_POST['title_id']) ? trim($_POST['title_id']) : '';
                    $student_fname = isset($_POST['student_fname']) ? trim($_POST['student_fname']) : '';
                    $student_lname = isset($_POST['student_lname']) ? trim($_POST['student_lname']) : '';
                    $student_phone = isset($_POST['student_phone']) ? trim($_POST['student_phone']) : '';
                    $student_email = isset($_POST['student_email']) ? trim($_POST['student_email']) : '';
                    $birthdate = isset($_POST['birthdate']) && !empty($_POST['birthdate']) ? trim($_POST['birthdate']) : null;
                    $religion = isset($_POST['religion']) ? trim($_POST['religion']) : '';
                    $nationality = isset($_POST['nationality']) ? trim($_POST['nationality']) : 'ไทย';
                    $plan_id = isset($_POST['plan_id']) ? trim($_POST['plan_id']) : '';
                    $prof_id = isset($_POST['prof_id']) ? trim($_POST['prof_id']) : '';
                    $major_id = isset($_POST['major_id']) ? trim($_POST['major_id']) : '';
                    
                    // บันทึก log เพื่อตรวจสอบค่า
                    error_log("Add Student Data: " . json_encode([
                        'stu_id' => $student_id,
                        'title_id' => $title_id,
                        'fname' => $student_fname,
                        'lname' => $student_lname,
                        'phone' => $student_phone,
                        'email' => $student_email,
                        'birthdate' => $birthdate,
                        'religion' => $religion,
                        'nationality' => $nationality,
                        'plan_id' => $plan_id,
                        'prof_id' => $prof_id,
                        'major_id' => $major_id
                    ], JSON_UNESCAPED_UNICODE));
                    
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (empty($student_id) || empty($title_id) || empty($student_fname) || empty($student_lname) ||
                        empty($student_phone) || empty($plan_id) || empty($prof_id) || empty($major_id)) {
                        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // ตรวจสอบรูปแบบรหัสนักศึกษา
                    if (strlen($student_id) !== 13) {
                        $_SESSION['error'] = "รหัสนักศึกษาต้องมี 13 หลัก";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // ตรวจสอบรูปแบบเบอร์โทรศัพท์
                    if (strlen($student_phone) !== 10 || !is_numeric($student_phone)) {
                        $_SESSION['error'] = "เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // ตรวจสอบว่ามีรหัสนักศึกษานี้ในระบบแล้วหรือไม่
                    $existingStudent = $controller->getStudentById($student_id);
                    if ($existingStudent) {
                        $_SESSION['error'] = "รหัสนักศึกษานี้มีอยู่ในระบบแล้ว";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // เพิ่มข้อมูลนักศึกษา
                    $result = $controller->insertStudent(
                        $student_id, 
                        $student_fname, 
                        $student_lname, 
                        $student_phone, 
                        $student_email,
                        $birthdate, 
                        $religion, 
                        $nationality, 
                        $plan_id, 
                        $title_id, 
                        $prof_id, 
                        $major_id
                    );
                    
                    if ($result) {
                        $_SESSION['success'] = "เพิ่มข้อมูลนักศึกษา $student_fname $student_lname เรียบร้อยแล้ว";
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการเพิ่มข้อมูล";
                        error_log("Insert student failed for ID: $student_id");
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
                    error_log("Exception in add student: " . $e->getMessage());
                }
                
                header("Location: ../index.php?menu=7");
                exit();
            }
            break;
            
        case 'edit':
            // แก้ไขข้อมูลนักศึกษา
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                try {
                    // รับค่าจากฟอร์ม
                    $student_id = isset($_POST['student_id']) ? trim($_POST['student_id']) : '';
                    $title_id = isset($_POST['edit_title_id']) ? trim($_POST['edit_title_id']) : '';
                    $student_fname = isset($_POST['edit_student_fname']) ? trim($_POST['edit_student_fname']) : '';
                    $student_lname = isset($_POST['edit_student_lname']) ? trim($_POST['edit_student_lname']) : '';
                    $student_phone = isset($_POST['edit_student_phone']) ? trim($_POST['edit_student_phone']) : '';
                    $student_email = isset($_POST['edit_student_email']) ? trim($_POST['edit_student_email']) : '';
                    $birthdate = isset($_POST['edit_birthdate']) && !empty($_POST['edit_birthdate']) ? trim($_POST['edit_birthdate']) : null;
                    $religion = isset($_POST['edit_religion']) ? trim($_POST['edit_religion']) : '';
                    $nationality = isset($_POST['edit_nationality']) ? trim($_POST['edit_nationality']) : 'ไทย';
                    $plan_id = isset($_POST['edit_plan_id']) ? trim($_POST['edit_plan_id']) : '';
                    $prof_id = isset($_POST['edit_prof_id']) ? trim($_POST['edit_prof_id']) : '';
                    $major_id = isset($_POST['edit_major_id']) ? trim($_POST['edit_major_id']) : '';
                    
                    // บันทึก log เพื่อตรวจสอบค่า
                    error_log("Edit Student Data: " . json_encode([
                        'stu_id' => $student_id,
                        'title_id' => $title_id,
                        'fname' => $student_fname,
                        'lname' => $student_lname,
                        'phone' => $student_phone,
                        'email' => $student_email,
                        'birthdate' => $birthdate,
                        'religion' => $religion,
                        'nationality' => $nationality,
                        'plan_id' => $plan_id,
                        'prof_id' => $prof_id,
                        'major_id' => $major_id
                    ], JSON_UNESCAPED_UNICODE));
                    
                    // ตรวจสอบข้อมูลที่จำเป็น
                    if (empty($student_id) || empty($title_id) || empty($student_fname) || empty($student_lname) ||
                        empty($student_phone) || empty($plan_id) || empty($prof_id) || empty($major_id)) {
                        $_SESSION['error'] = "กรุณากรอกข้อมูลให้ครบทุกช่อง";
                        header("Location: ../index.php?menu=7&edit=" . $student_id);
                        exit();
                    }
                    
                    // ตรวจสอบรูปแบบเบอร์โทรศัพท์
                    if (strlen($student_phone) !== 10 || !is_numeric($student_phone)) {
                        $_SESSION['error'] = "เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก";
                        header("Location: ../index.php?menu=7&edit=" . $student_id);
                        exit();
                    }
                    
                    // ตรวจสอบว่ามีข้อมูลนักศึกษานี้ในระบบหรือไม่
                    $existingStudent = $controller->getStudentById($student_id);
                    if (!$existingStudent) {
                        $_SESSION['error'] = "ไม่พบข้อมูลนักศึกษาที่ต้องการแก้ไข";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // อัปเดตข้อมูลนักศึกษา
                    $result = $controller->updateStudent(
                        $student_id, 
                        $student_fname, 
                        $student_lname, 
                        $student_phone, 
                        $student_email,
                        $birthdate, 
                        $religion, 
                        $nationality, 
                        $plan_id, 
                        $title_id, 
                        $prof_id, 
                        $major_id
                    );
                    
                    if ($result) {
                        $_SESSION['success'] = "อัปเดตข้อมูลนักศึกษา $student_fname $student_lname เรียบร้อยแล้ว";
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
                        error_log("Update student failed for ID: $student_id");
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
                    error_log("Exception in edit student: " . $e->getMessage());
                }
                
                header("Location: ../index.php?menu=7");
                exit();
            }
            break;
            
        case 'delete':
            // ลบข้อมูลนักศึกษา
            $student_id = null;
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $student_id = trim($_GET['id']);
            } else if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
                $student_id = trim($_POST['student_id']);
            }
            
            if ($student_id) {
                try {
                    // ตรวจสอบว่ามีข้อมูลนักศึกษานี้ในระบบหรือไม่
                    $existingStudent = $controller->getStudentById($student_id);
                    if (!$existingStudent) {
                        $_SESSION['error'] = "ไม่พบข้อมูลนักศึกษาที่ต้องการลบ";
                        header("Location: ../index.php?menu=7");
                        exit();
                    }
                    
                    // ลบข้อมูลนักศึกษา
                    $result = $controller->deleteStudent($student_id);
                    
                    if ($result) {
                        $_SESSION['success'] = "ลบข้อมูลนักศึกษา " . $existingStudent['Stu_fname'] . " " . $existingStudent['Stu_lname'] . " เรียบร้อยแล้ว";
                    } else {
                        $_SESSION['error'] = "เกิดข้อผิดพลาดในการลบข้อมูล";
                        error_log("Delete student failed for ID: $student_id");
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
                    error_log("Exception in delete student: " . $e->getMessage());
                }
            } else {
                $_SESSION['error'] = "ไม่พบรหัสนักศึกษาที่ต้องการลบ";
            }
            
            header("Location: ../index.php?menu=7");
            exit();
            break;
            
        case 'import':
            // นำเข้าข้อมูลนักศึกษาจากไฟล์ Excel
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['import_file'])) {
                try {
                    $result = $controller->importStudentsFromExcel($_FILES['import_file']);
                    
                    if ($result['status']) {
                        $_SESSION['success'] = "นำเข้าข้อมูลนักศึกษาสำเร็จ " . $result['inserted'] . " รายการ";
                        if ($result['errors'] > 0) {
                            $_SESSION['success'] .= " มีข้อผิดพลาด " . $result['errors'] . " รายการ";
                        }
                    } else {
                        $_SESSION['error'] = $result['message'];
                        error_log("Import students failed: " . $result['message']);
                    }
                } catch (Exception $e) {
                    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
                    error_log("Exception in import students: " . $e->getMessage());
                }
                
                header("Location: ../index.php?menu=7");
                exit();
            } else {
                $_SESSION['error'] = "ไม่พบไฟล์ที่อัพโหลด";
                header("Location: ../index.php?menu=7");
                exit();
            }
            break;
            
        default:
            // ถ้าไม่มี action ที่ตรงกับเงื่อนไข ให้กลับไปที่หน้ารายการนักศึกษา
            header("Location: ../index.php?menu=7");
            exit();
            break;
    }
} else {
    // ถ้าไม่มีการส่ง action มา ให้กลับไปที่หน้ารายการนักศึกษา
    header("Location: ../index.php?menu=7");
    exit();
}
?>