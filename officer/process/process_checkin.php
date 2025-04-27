<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/controller.php';

try {
    $database = new Database();
    $db = $database->connect();
    $controller = new Controller($db);

    // รับค่าจากฟอร์ม
    $student_id = $_POST['student_id'];
    $act_id = $_POST['act_id'];
    $check_type = $_POST['check_type'];

    // ตรวจสอบว่ามีข้อมูลนักศึกษาในระบบหรือไม่
    $stmt = $db->prepare("SELECT * FROM student WHERE Stu_id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        $_SESSION['error'] = 'ไม่พบข้อมูลนักศึกษาในระบบ';
        header("Location: ../checkin.php?act_id=" . $act_id);
        exit();
    }

    // ดึงข้อมูลกิจกรรม
    $activity = $controller->getActivityById($act_id);
    if (!$activity) {
        $_SESSION['error'] = 'ไม่พบข้อมูลกิจกรรม';
        header("Location: ../checkin.php?act_id=" . $act_id);
        exit();
    }

    $current_time = date('Y-m-d H:i:s');

    if ($check_type === 'in') {
        // ตรวจสอบว่าเคยเช็คอินแล้วหรือไม่
        $stmt = $db->prepare("SELECT * FROM participation WHERE Stu_id = ? AND Act_id = ?");
        $stmt->execute([$student_id, $act_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'นักศึกษาได้เช็คอินกิจกรรมนี้ไปแล้ว';
            header("Location: ../checkin.php?act_id=" . $act_id);
            exit();
        }

        // บันทึกการเช็คอิน - กำหนด CheckOut เป็น NULL อย่างชัดเจน
        $sql = "INSERT INTO participation (Stu_id, Act_id, ActSemester, ActYear, Act_hour, CheckIn, CheckOut) 
                VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([
            $student_id,
            $act_id,
            $activity['ActSemester'],
            $activity['ActYear'],
            $activity['Act_hour'],
            $current_time
        ]);

        if ($result) {
            $_SESSION['success'] = "บันทึกการเช็คอินสำเร็จ\nรหัสนักศึกษา: {$student_id}\nชื่อ-สกุล: {$student['Stu_fname']} {$student['Stu_lname']}\nเวลา: " . date('H:i:s', strtotime($current_time));
        } else {
            $_SESSION['error'] = 'ไม่สามารถบันทึกการเช็คอินได้';
        }

    } else {
        // ตรวจสอบว่าได้เช็คอินแล้วหรือไม่ และยังไม่ได้เช็คเอาท์
        $stmt = $db->prepare("SELECT * FROM participation WHERE Stu_id = ? AND Act_id = ? AND CheckOut IS NULL");
        $stmt->execute([$student_id, $act_id]);
        $participation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$participation) {
            $_SESSION['error'] = 'ไม่พบข้อมูลการเช็คอิน หรือได้เช็คเอาท์ไปแล้ว';
            header("Location: ../checkin.php?act_id=" . $act_id);
            exit();
        }

        // อัปเดตเวลาเช็คเอาท์
        $sql = "UPDATE participation SET CheckOut = ? WHERE Stu_id = ? AND Act_id = ? AND CheckOut IS NULL";
        $stmt = $db->prepare($sql);
        $result = $stmt->execute([$current_time, $student_id, $act_id]);

        if ($result) {
            $_SESSION['success'] = "บันทึกการเช็คเอาท์สำเร็จ\nรหัสนักศึกษา: {$student_id}\nชื่อ-สกุล: {$student['Stu_fname']} {$student['Stu_lname']}\nเวลา: " . date('H:i:s', strtotime($current_time));
        } else {
            $_SESSION['error'] = 'ไม่สามารถบันทึกการเช็คเอาท์ได้';
        }
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
}

header("Location: ../checkin.php?act_id=" . $act_id);
exit();