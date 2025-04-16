<?php
session_start();
include_once '../../config/database.php';
include_once '../../config/controller.php';

// เชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

// ตรวจสอบการทำงาน (action) ที่ต้องการ
$action = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
}

// บันทึกข้อมูลสำหรับการดีบัก
error_log("process_criteria.php called with action: " . $action);
error_log("POST data: " . print_r($_POST, true));

// ดำเนินการตาม action
try {
    switch ($action) {
    // ส่วนของการเพิ่มข้อมูลหลักเกณฑ์ในไฟล์ process_criteria.php

    case 'add':
        // รับค่าจากฟอร์ม
        $crit_name = isset($_POST['crit_name']) ? trim($_POST['crit_name']) : '';
        $curri_id = isset($_POST['curri_id']) ? trim($_POST['curri_id']) : '';
        $plan_id = isset($_POST['plan_id']) ? trim($_POST['plan_id']) : '';
        $act_type_id = isset($_POST['act_type_id']) ? trim($_POST['act_type_id']) : '';
        $act_hour = isset($_POST['act_hour']) ? intval($_POST['act_hour']) : 0;
        $act_amount = isset($_POST['act_amount']) ? intval($_POST['act_amount']) : 0;
        
        // ตรวจสอบข้อมูล
        $errors = [];
        
        if (empty($crit_name)) {
            $errors[] = "กรุณาระบุชื่อหลักเกณฑ์";
        }
        
        if (empty($curri_id)) {
            $errors[] = "กรุณาเลือกหลักสูตร";
        }
        
        if (empty($plan_id)) {
            $errors[] = "กรุณาเลือกแผนการศึกษา";
        }
        
        if (empty($act_type_id)) {
            $errors[] = "กรุณาเลือกประเภทกิจกรรม";
        }
        
        if ($act_hour <= 0) {
            $errors[] = "จำนวนชั่วโมงต้องมากกว่า 0";
        }
        
        if ($act_amount <= 0) {
            $errors[] = "จำนวนกิจกรรมต้องมากกว่า 0";
        }
        
        // ถ้ามีข้อผิดพลาด
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: ../index.php?menu=8");
            exit;
        }
        
        // เพิ่มข้อมูลลงฐานข้อมูล - ใช้เมธอด insertCriteria ที่มีการสร้างรหัสอัตโนมัติ
        $result = $controller->insertCriteria($crit_name, $curri_id, $plan_id, $act_type_id, $act_hour, $act_amount);
        
        if ($result) {
            $_SESSION['success'] = "เพิ่มหลักเกณฑ์ '$crit_name' เรียบร้อยแล้ว";
        } else {
            throw new Exception("ไม่สามารถเพิ่มข้อมูลได้ โปรดลองอีกครั้ง");
        }
        
        header("Location: ../index.php?menu=8");
        break;
        
    case 'update':
        // รับค่าจากฟอร์ม
        $crit_id = isset($_POST['edit_crit_id']) ? trim($_POST['edit_crit_id']) : '';
        $crit_name = isset($_POST['edit_crit_name']) ? trim($_POST['edit_crit_name']) : '';
        $curri_id = isset($_POST['edit_curri_id']) ? trim($_POST['edit_curri_id']) : '';
        $plan_id = isset($_POST['edit_plan_id']) ? trim($_POST['edit_plan_id']) : '';
        $act_type_id = isset($_POST['edit_act_type_id']) ? trim($_POST['edit_act_type_id']) : '';
        $act_hour = isset($_POST['edit_act_hour']) ? intval($_POST['edit_act_hour']) : 0;
        $act_amount = isset($_POST['edit_act_amount']) ? intval($_POST['edit_act_amount']) : 0;
        
        // ตรวจสอบข้อมูล
        $errors = [];
        
        if (empty($crit_id)) {
            $errors[] = "ไม่พบรหัสหลักเกณฑ์";
        }
        
        if (empty($crit_name)) {
            $errors[] = "กรุณาระบุชื่อหลักเกณฑ์";
        }
        
        if (empty($curri_id)) {
            $errors[] = "กรุณาเลือกหลักสูตร";
        }
        
        if (empty($plan_id)) {
            $errors[] = "กรุณาเลือกแผนการศึกษา";
        }
        
        if (empty($act_type_id)) {
            $errors[] = "กรุณาเลือกประเภทกิจกรรม";
        }
        
        if ($act_hour <= 0) {
            $errors[] = "จำนวนชั่วโมงต้องมากกว่า 0";
        }
        
        if ($act_amount <= 0) {
            $errors[] = "จำนวนกิจกรรมต้องมากกว่า 0";
        }
        
        // ถ้ามีข้อผิดพลาด
        if (!empty($errors)) {
            $_SESSION['error'] = implode("<br>", $errors);
            header("Location: ../index.php?menu=8&edit=" . $crit_id);
            exit;
        }
        
        // อัพเดทข้อมูลในฐานข้อมูล
        $result = $controller->updateCriteria($crit_id, $crit_name, $curri_id, $plan_id, $act_type_id, $act_hour, $act_amount);
        
        if ($result) {
            $_SESSION['success'] = "แก้ไขหลักเกณฑ์ '$crit_name' เรียบร้อยแล้ว";
        } else {
            throw new Exception("ไม่สามารถแก้ไขข้อมูลได้ โปรดลองอีกครั้ง");
        }
        
        header("Location: ../index.php?menu=8");
        break;
    
    case 'delete':
        // รับค่า ID ที่ต้องการลบ
        $crit_id = isset($_GET['id']) ? trim($_GET['id']) : '';
        
        if (empty($crit_id)) {
            throw new Exception("ไม่พบรหัสหลักเกณฑ์ที่ต้องการลบ");
        }
        
        // ลบข้อมูล
        $result = $controller->deleteCriteria($crit_id);
        
        if ($result) {
            $_SESSION['success'] = "ลบหลักเกณฑ์รหัส '$crit_id' เรียบร้อยแล้ว";
        } else {
            throw new Exception("ไม่สามารถลบข้อมูลได้ โปรดลองอีกครั้ง");
        }
        
        header("Location: ../index.php?menu=8");
        break;
    }
} catch (Exception $e) {
    error_log("Error in process_criteria.php: " . $e->getMessage());
    $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    header("Location: ../index.php?menu=8");
}


?>