<?php

// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller ถ้ายังไม่ได้เรียกใช้
if (!isset($controller)) {
    require_once '../config/database.php';
    require_once '../config/controller.php';

    // สร้างการเชื่อมต่อกับฐานข้อมูล
    $database = new Database();
    $db = $database->connect();

    // สร้างอ็อบเจกต์ Controller
    $controller = new Controller($db);
}

// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id']) || $_SESSION['status'] !== 'student') {
    header("Location: login.php");
    exit();
}

// ดึงข้อมูลนักศึกษา
$studentInfo = $controller->getStudentById($_SESSION['user_id']);

?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">ยื่นคำร้องขอเทียบกิจกรรม</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ยื่นคำร้องขอเทียบกิจกรรม</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- ข้อความแจ้งเตือน -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check"></i> สำเร็จ!</h5>
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']);
                ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> เกิดข้อผิดพลาด!</h5>
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']);
                ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <!-- ตารางแสดงประวัติการยื่นคำร้อง -->
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">ประวัติการยื่นคำร้องขอเทียบกิจกรรม</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-primary" data-toggle="modal" 
                                    data-target="#modal-add-request">
                                    <i class="fas fa-plus"></i> ยื่นคำร้อง
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="table1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">ลำดับ</th>
                                        <th width="20%">ชื่อกิจกรรม</th>
                                        <th width="10%">จำนวนกิจกรรม</th>
                                        <th width="10%">จำนวนชั่วโมง</th>
                                        <th width="15%">ภาคเรียน/ปีการศึกษา</th>
                                        <th width="10%">สถานะ</th>
                                        <th width="15%">หลักฐาน/จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // ดึงข้อมูลการขอเทียบกิจกรรมของนักศึกษา
                                    $comparisions = $controller->getComparisionsByStudentId($_SESSION['user_id']);
                                    
                                    if ($comparisions) {
                                        $i = 1;
                                        foreach ($comparisions as $row) {
                                            // แปลงสถานะเป็นภาษาไทย
                                            $statusText = '';
                                            $statusClass = '';
                                            $status = $row['Com_status'] ?? 'pending';
                                            
                                            switch ($status) {
                                                case 'pending':
                                                    $statusText = 'รอการพิจารณา';
                                                    $statusClass = 'badge badge-warning';
                                                    break;
                                                case 'approved':
                                                    $statusText = 'อนุมัติแล้ว';
                                                    $statusClass = 'badge badge-success';
                                                    break;
                                                case 'rejected':
                                                    $statusText = 'ไม่อนุมัติ';
                                                    $statusClass = 'badge badge-danger';
                                                    break;
                                            }
                                            
                                            // จัดรูปแบบการแสดงผลภาคเรียน/ปีการศึกษา
                                            $semesterYear = $row['Com_semester'] . '/' . date('Y', strtotime($row['Com_year'])) + 543;

                                            echo "<tr>
                                                    <td>{$i}</td>
                                                    <td>{$row['Com_name']}</td>
                                                    <td>{$row['Com_amount']}</td>
                                                    <td>{$row['Com_hour']}</td>
                                                    <td>{$semesterYear}</td>
                                                    <td><span class=\"{$statusClass}\">{$statusText}</span></td>
                                                    <td>";
                                            
                                            // แสดงปุ่มดูหลักฐาน
                                            if (!empty($row['Upload'])) {
                                                echo "<a href=\"../uploads/comparision/{$row['Upload']}\" 
                                                      target=\"_blank\" 
                                                      class=\"btn btn-info btn-sm\" 
                                                      title=\"ดูหลักฐาน\">
                                                        <i class=\"fas fa-file\"></i>
                                                     </a> ";
                                            }
                                                    
                                            // แสดงปุ่มลบเฉพาะรายการที่ยังไม่ได้รับการอนุมัติ
                                            if ($status === 'pending') {
                                                echo "<a href=\"process/process_manage_comparision.php?action=delete&id={$row['Com_id']}\" 
                                                      class=\"btn btn-danger btn-sm\" 
                                                      onclick=\"return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?')\" 
                                                      title=\"ลบ\">
                                                        <i class=\"fas fa-trash\"></i>
                                                     </a>";
                                            }
                                            
                                            // แสดงความคิดเห็น (ถ้ามี)
                                            if (!empty($row['Comment'])) {
                                                echo " <button type=\"button\" 
                                                        class=\"btn btn-secondary btn-sm\" 
                                                        title=\"ความคิดเห็น\" 
                                                        data-toggle=\"popover\" 
                                                        data-content=\"{$row['Comment']}\">
                                                        <i class=\"fas fa-comment\"></i>
                                                      </button>";
                                            }
                                            
                                            echo "</td></tr>";
                                            $i++;
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal ยื่นคำร้อง -->
    <div class="modal fade" id="modal-add-request">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h4 class="modal-title">ยื่นคำร้องขอเทียบกิจกรรม</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="./process/process_manage_comparision.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="com_name">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="com_name" name="activity_name" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="com_amount">จำนวนกิจกรรม <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="com_amount" name="com_amount" 
                                        min="1" max="10" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="com_hour">จำนวนชั่วโมง <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="com_hour" name="activity_hour" 
                                        min="1" max="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="com_semester">ภาคเรียน <span class="text-danger">*</span></label>
                                    <select class="form-control" id="com_semester" name="semester" required>
                                        <option value="">เลือกภาคเรียน</option>
                                        <option value="1">ภาคเรียนที่ 1</option>
                                        <option value="2">ภาคเรียนที่ 2</option>
                                        <option value="3">ภาคเรียนที่ 3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="com_year">ปีการศึกษา <span class="text-danger">*</span></label>
                                    <select class="form-control" id="com_year" name="year" required>
                                        <option value="">เลือกปีการศึกษา</option>
                                        <?php
                                            // ปีปัจจุบัน
                                            $currentYear = (int)date('Y') + 543;
                                            // ปีที่เริ่มการศึกษา (ย้อนหลัง 4 ปี)
                                            $startYear = $currentYear - 4;
                                            
                                            // แสดงตัวเลือกปีการศึกษา
                                            for($year = $currentYear; $year >= $startYear; $year--) {
                                                echo "<option value=\"{$year}\">{$year}</option>";
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="upload_file">หลักฐานประกอบการพิจารณา <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" 
                                        class="custom-file-input" 
                                        id="upload_file" 
                                        name="upload_file" 
                                        accept=".pdf,.jpg,.jpeg,.png"
                                        required>
                                    <label class="custom-file-label" for="upload_file">เลือกไฟล์</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                กรุณาแนบหลักฐาน เช่น หนังสือรับรอง ประกาศนียบัตร ภาพถ่าย ฯลฯ (รองรับไฟล์ PDF, JPG, PNG ขนาดไม่เกิน 2MB)
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยื่นคำร้อง</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for form validation and file input -->
    <script>
    $(document).ready(function() {
        // Initialize custom file input
        bsCustomFileInput.init();

        // Form validation before submit
        $('form').on('submit', function(e) {
            var fileInput = $('#upload_file')[0];
            var fileSize = fileInput.files[0]?.size || 0;
            var fileType = fileInput.files[0]?.type || '';
            
            // Check file size (2MB = 2097152 bytes)
            if (fileSize > 2097152) {
                e.preventDefault();
                alert('ขนาดไฟล์ต้องไม่เกิน 2MB');
                return false;
            }

            // Check file type
            var allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
            if (!allowedTypes.includes(fileType)) {
                e.preventDefault();
                alert('รองรับเฉพาะไฟล์ PDF, JPG และ PNG เท่านั้น');
                return false;
            }
        });

        // Update filename on file select
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').html(fileName);
        });
    });
    </script>
</div>

<!-- Page specific script -->
<script>
$(document).ready(function() {
    // เปิดใช้งาน popover สำหรับแสดงความคิดเห็น
    $('[data-toggle="popover"]').popover({
        trigger: 'hover',
        placement: 'top'
    });
    
    // เปิดใช้งาน custom file input
    bsCustomFileInput.init();
});
</script>