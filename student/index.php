<?php 
// เริ่มต้น session ที่ส่วนบนของไฟล์
session_start();

require_once 'chk_login.php';
require_once 'includes/header.php';
// สร้างการเชื่อมต่อกับฐานข้อมูล
include_once '../config/database.php';
include_once '../config/controller.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- เพิ่ม style สำหรับ sticky footer -->
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        
        .wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .content-wrapper {
            flex: 1 0 auto;
        }
        
        .main-footer {
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="index.php?menu=1" class="nav-link">Home</a>
                </li>
            </ul>
            <ul class="navbar-nav ml-auto">
                <!-- Navbar Search -->
                <li class="nav-item">
                    <?php 
                    // แสดงชื่อผู้ใช้จาก session
                    if(isset($_SESSION['user_name'])) {
                        echo $_SESSION['user_name'];
                    } else if(isset($_SESSION['fullname'])) {
                        echo $_SESSION['fullname'];
                    } else {
                        echo "ผู้ใช้งาน";
                    }
                    ?>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?php require_once 'includes/menu.php' ?>


        <!-- Content Wrapper. Contains page content -->
        <?php 
        switch($menu){
            case 1: require_once 'profile.php'; break;
            case 2: require_once 'repass.php'; break;
            case 3: require_once 'participation_result.php'; break;
            case 4: require_once 'comparision_request.php'; break;

            default:require_once 'index.php'; break;
        }
        ?>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
            <div class="p-3">
                <h5>Title</h5>
                <p>Sidebar content</p>
            </div>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <?php require_once 'includes/footer.php' ?>
    </div>
    <!-- ./wrapper -->


    <?php require_once 'includes/tablejs.php' ?>
</body>

</html>