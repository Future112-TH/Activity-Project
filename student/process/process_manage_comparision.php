<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/controller.php';

// สร้างการเชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            try {
                // ตรวจสอบและทำความสะอาดข้อมูล
                $studentId = $_SESSION['user_id'];
                $activityName = filter_var($_POST['activity_name'], FILTER_SANITIZE_STRING);
                $activityHour = filter_var($_POST['activity_hour'], FILTER_VALIDATE_INT);
                $comAmount = filter_var($_POST['com_amount'], FILTER_VALIDATE_INT);
                $semester = filter_var($_POST['semester'], FILTER_SANITIZE_STRING);
                $year = filter_var($_POST['year'], FILTER_SANITIZE_STRING);

                // ตรวจสอบความถูกต้องของข้อมูล
                if (!$activityHour || $activityHour < 1 || $activityHour > 100) {
                    throw new Exception('จำนวนชั่วโมงไม่ถูกต้อง');
                }

                if (!$comAmount || $comAmount < 1 || $comAmount > 10) {
                    throw new Exception('จำนวนกิจกรรมไม่ถูกต้อง');
                }

                // ตรวจสอบภาคเรียน
                if (!in_array($semester, ['1', '2', '3'])) {
                    throw new Exception('ภาคเรียนไม่ถูกต้อง');
                }

                // ตรวจสอบปีการศึกษา
                $currentYear = (int)date('Y') + 543;
                $startYear = $currentYear - 4;
                $year = (int)$year;
                
                if ($year < $startYear || $year > $currentYear) {
                    throw new Exception('ปีการศึกษาไม่ถูกต้อง');
                }

                // จัดการไฟล์แนบ
                $uploadFile = '';
                if (isset($_FILES['upload_file'])) {
                    // Debug log
                    error_log("File upload details: " . print_r($_FILES['upload_file'], true));

                    if ($_FILES['upload_file']['error'] !== 0) {
                        throw new Exception('เกิดข้อผิดพลาดในการอัปโหลดไฟล์: ' . $_FILES['upload_file']['error']);
                    }

                    // สร้างโฟลเดอร์ถ้ายังไม่มี
                    $uploadDir = '../../uploads/comparision/';
                    if (!file_exists($uploadDir)) {
                        if (!mkdir($uploadDir, 0777, true)) {
                            throw new Exception('ไม่สามารถสร้างโฟลเดอร์สำหรับเก็บไฟล์ได้');
                        }
                    }

                    // ตรวจสอบการเขียนไฟล์
                    if (!is_writable($uploadDir)) {
                        throw new Exception('ไม่มีสิทธิ์ในการเขียนไฟล์: ' . $uploadDir);
                    }

                    $fileExtension = strtolower(pathinfo($_FILES['upload_file']['name'], PATHINFO_EXTENSION));
                    
                    // ตรวจสอบประเภทไฟล์
                    $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
                    if (!in_array($fileExtension, $allowedTypes)) {
                        throw new Exception('รองรับเฉพาะไฟล์ PDF, JPG และ PNG เท่านั้น');
                    }

                    // ตรวจสอบขนาดไฟล์
                    if ($_FILES['upload_file']['size'] > 2097152) {
                        throw new Exception('ขนาดไฟล์ต้องไม่เกิน 2MB');
                    }

                    // สร้างชื่อไฟล์ใหม่
                    $newFileName = uniqid() . '_' . time() . '.' . $fileExtension;
                    $uploadFile = $newFileName;
                    $fullPath = $uploadDir . $newFileName;

                    // อัปโหลดไฟล์
                    if (!move_uploaded_file($_FILES['upload_file']['tmp_name'], $fullPath)) {
                        error_log("Upload failed. Full path: " . $fullPath);
                        error_log("Upload error details: " . error_get_last()['message']);
                        throw new Exception('ไม่สามารถอัปโหลดไฟล์ได้');
                    }

                    // ตรวจสอบว่าไฟล์ถูกอัปโหลดจริง
                    if (!file_exists($fullPath)) {
                        throw new Exception('ไฟล์ไม่ถูกอัปโหลด');
                    }

                    error_log("File uploaded successfully to: " . $fullPath);
                }

                // สร้าง Com_id ใหม่
                $stmt = $db->query("SELECT MAX(CAST(Com_id AS UNSIGNED)) as max_id FROM comparision");
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $newId = str_pad($row['max_id'] + 1, 5, '0', STR_PAD_LEFT);

                // เตรียมข้อมูลสำหรับบันทึก
                $data = [
                    'Com_id' => $newId,
                    'Com_name' => $activityName,
                    'Com_amount' => $comAmount,
                    'Com_hour' => $activityHour,
                    'Com_semester' => $semester,
                    'Com_year' => $year,
                    'Com_status' => 'pending',
                    'Stu_id' => $studentId,
                    'Upload' => $uploadFile
                ];

                // บันทึกข้อมูล
                if ($controller->addComparision($data)) {
                    $_SESSION['success'] = 'ยื่นคำร้องสำเร็จ กรุณารอการพิจารณา';
                } else {
                    throw new Exception('ไม่สามารถบันทึกข้อมูลได้');
                }

            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
            }
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete') {
    try {
        // ตรวจสอบว่ามี ID ที่จะลบหรือไม่
        if (!isset($_GET['id'])) {
            throw new Exception('ไม่พบข้อมูลที่ต้องการลบ');
        }

        $comId = $_GET['id'];

        // ตรวจสอบว่าเป็นเจ้าของข้อมูลหรือไม่
        $stmt = $db->prepare("SELECT Stu_id, Com_status FROM comparision WHERE Com_id = ?");
        $stmt->execute([$comId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception('ไม่พบข้อมูลที่ต้องการลบ');
        }

        if ($row['Stu_id'] !== $_SESSION['user_id']) {
            throw new Exception('คุณไม่มีสิทธิ์ลบข้อมูลนี้');
        }

        if ($row['Com_status'] !== 'pending') {
            throw new Exception('ไม่สามารถลบรายการที่ได้รับการพิจารณาแล้ว');
        }

        // ดำเนินการลบ
        if ($controller->deleteComparision($comId)) {
            $_SESSION['success'] = 'ลบข้อมูลสำเร็จ';
        } else {
            throw new Exception('ไม่สามารถลบข้อมูลได้');
        }

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    // ย้อนกลับไปหน้าเดิม
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

// ย้อนกลับไปหน้าเดิม
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit();
?>