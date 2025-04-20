<?php 
// เริ่มต้น session ที่ส่วนบนของไฟล์
session_start();

require_once 'chk_login.php';
require_once 'includes/header.php';

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
                    // ดึงข้อมูลอาจารย์จากฐานข้อมูล
                    require_once '../config/database.php';
                    require_once '../config/controller.php';
                    
                    $database = new Database();
                    $db = $database->connect();
                    $controller = new Controller($db);
                    
                    // ใช้ professor_id จาก session
                    $professor_id = isset($_SESSION['professor_id']) ? $_SESSION['professor_id'] : null;
                    
                    if ($professor_id) {
                        // ดึงข้อมูลอาจารย์
                        $professor = $controller->getProfessorById($professor_id);
                        
                        if ($professor) {
                            // ดึงข้อมูลคำนำหน้า
                            $title = $controller->getTitleById($professor['Title_id']);
                            $title_name = $title ? $title['Title_name'] : '';
                            
                            // สร้างชื่อเต็ม
                            $full_name = $title_name . ' ' . $professor['Prof_fname'] . ' ' . $professor['Prof_lname'];
                            echo $full_name;
                        } else {
                            echo "ผู้ใช้งานระบบ";
                        }
                    } else {
                        echo "ผู้ใช้งานระบบ";
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
            case 1: require_once 'dashboard.php'; break;
            case 2: require_once 'profile.php'; break;
            
            // case 3: require_once ''; break;

            case 4: require_once 'faculty.php'; break;
            case 5: require_once 'major.php'; break;
            case 6: require_once 'advisor.php'; break;
            case 7: require_once 'student.php'; break;
            case 8: require_once 'criteria.php'; break;
            case 9: require_once 'activity_type.php'; break;

            case 10: require_once 'activity.php'; break;
            case 11: require_once 'comparision.php'; break;

            case 12: require_once 'result.php'; break;
            case 13: require_once 'transcript.php'; break;

            default:require_once 'dashboard.php'; break;
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