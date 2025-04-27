<?php
session_start();
require_once '../config/database.php';
require_once '../config/controller.php';

$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

// ตรวจสอบว่ามีการส่ง act_id มาหรือไม่
if (!isset($_GET['act_id'])) {
    $_SESSION['error'] = "ไม่พบรหัสกิจกรรม";
    header("Location: index.php?menu=10");
    exit();
}

$act_id = $_GET['act_id'];
$activity = $controller->getActivityById($act_id);

if (!$activity) {
    $_SESSION['error'] = "ไม่พบข้อมูลกิจกรรม";
    header("Location: index.php?menu=10");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>ระบบเช็คอินกิจกรรม</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php require_once 'includes/header.php'; ?>
</head>
<body class="hold-transition">
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i> ระบบเช็คอินกิจกรรม
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?php 
                                    echo $_SESSION['success']; 
                                    unset($_SESSION['success']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <?php 
                                    echo $_SESSION['error']; 
                                    unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <div class="bg-light p-3 mb-4 rounded">
                            <h5 class="text-primary">
                                <i class="fas fa-info-circle mr-2"></i>ข้อมูลกิจกรรม
                            </h5>
                            <div class="mt-3">
                                <p class="mb-2"><strong>ชื่อกิจกรรม:</strong> <?php echo $activity['Act_name']; ?></p>
                                <p class="mb-2"><strong>วันที่:</strong> <?php echo date('d/m/Y', strtotime($activity['Act_start_date'])); ?></p>
                                <p class="mb-0"><strong>เวลา:</strong> <?php echo date('H:i', strtotime($activity['Act_start_date'])); ?> - 
                                    <?php echo date('H:i', strtotime($activity['Act_stop_date'])); ?> น.</p>
                            </div>
                        </div>

                        <form action="process/process_checkin.php" method="POST">
                            <input type="hidden" name="act_id" value="<?php echo $act_id; ?>">
                            <div class="form-group">
                                <label for="student_id">
                                    <i class="fas fa-id-card mr-1"></i>
                                    รหัสนักศึกษา <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-lg" 
                                       id="student_id" name="student_id" 
                                       required maxlength="13" autofocus
                                       placeholder="กรุณากรอกรหัสนักศึกษา">
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-clock mr-1"></i>ประเภทการเช็ค</label>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="checkin" name="check_type" 
                                        value="in" class="custom-control-input" checked>
                                    <label class="custom-control-label" for="checkin">เช็คอิน</label>
                                </div>
                                <div class="custom-control custom-radio">
                                    <input type="radio" id="checkout" name="check_type" 
                                        value="out" class="custom-control-input">
                                    <label class="custom-control-label" for="checkout">เช็คเอาท์</label>
                                </div>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary btn-lg btn-block">
                                    <i class="fas fa-save mr-1"></i> บันทึกการเช็ค
                                </button>
                                <a href="index.php?menu=10" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-arrow-left mr-1"></i> กลับหน้ารายการ
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Focus ที่ช่องกรอกรหัสนักศึกษาเมื่อโหลดหน้า
        document.getElementById('student_id').focus();
    </script>
</body>
</html>