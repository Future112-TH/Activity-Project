<?php 
session_start();
require_once 'config/sweetalert.php';

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    $alert = new SweetAlert('Warning','กรุณาเข้าสู่ระบบ','warning');
    echo $alert->setRedirectUrl('index.php');
    exit;
}
?>