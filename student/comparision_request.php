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
                                        <th width="15%">วันที่ยื่นคำร้อง</th>
                                        <th width="15%">ประเภทการขอเทียบ</th>
                                        <th width="25%">ชื่อกิจกรรม</th>
                                        <th width="10%">ภาคเรียน/ปีการศึกษา</th>
                                        <th width="10%">จำนวนชั่วโมง</th>
                                        <th width="10%">สถานะ</th>
                                        <th width="10%">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                        // ดึงข้อมูลการขอเทียบกิจกรรมของนักศึกษา
                                        $comparisions = $controller->getComparisionsByStudentId($_SESSION['user_id']);
                                        
                                        if ($comparisions) {
                                            $i = 1;
                                            foreach ($comparisions as $row) {
                                                // แปลงประเภทการขอเทียบเป็นภาษาไทย
                                                $requestTypeText = '';
                                                switch ($row['RequestType']) {
                                                    case 'position':
                                                        $requestTypeText = 'ตำแหน่งในการจัดกิจกรรม';
                                                        break;
                                                    case 'award':
                                                        $requestTypeText = 'รางวัล/การแข่งขัน';
                                                        break;
                                                    case 'helper':
                                                        $requestTypeText = 'ผู้ช่วยกิจกรรม';
                                                        break;
                                                    default:
                                                        $requestTypeText = $row['RequestType'];
                                                }
                                                
                                                // แปลงสถานะเป็นภาษาไทยและกำหนด CSS class
                                                $statusText = '';
                                                $statusClass = '';
                                                switch ($row['Status']) {
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
                                                    default:
                                                        $statusText = $row['Status'];
                                                        $statusClass = 'badge badge-secondary';
                                                }
                                                
                                                // จัดรูปแบบวันที่
                                                $requestDate = date('d/m/Y H:i', strtotime($row['RequestDate']));
                                                
                                                echo "<tr>
                                                        <td>{$i}</td>
                                                        <td>{$requestDate}</td>
                                                        <td>{$requestTypeText}</td>
                                                        <td>";
                                                        
                                                // เพิ่มเงื่อนไขตรวจสอบว่ามี Act_id หรือไม่
                                                if (!empty($row['Act_id'])) {
                                                    $activity = $controller->getActivityById($row['Act_id']);
                                                    if ($activity) {
                                                        echo $activity['Act_name'];
                                                    } else {
                                                        echo $row['RequestDetail'];
                                                    }
                                                } else {
                                                    echo $row['RequestDetail'];
                                                }

                                                echo "</td>
                                                        <td>{$row['ActSemester']}/{$row['ActYear']}</td>
                                                        <td>{$row['Act_hour']}</td>
                                                        <td><span class=\"{$statusClass}\">{$statusText}</span></td>
                                                        <td>";
                                                
                                                // แสดงปุ่มดูหลักฐาน
                                                if (!empty($row['Upload'])) {
                                                    echo "<a href=\"uploads/comparision/{$row['Upload']}\" target=\"_blank\" class=\"btn btn-info btn-sm\" title=\"ดูหลักฐาน\">
                                                            <i class=\"fas fa-file\"></i>
                                                          </a> ";
                                                }
                                                            
                                                // แสดงปุ่มลบเฉพาะรายการที่ยังไม่ได้รับการอนุมัติ
                                                if ($row['Status'] === 'pending') {
                                                    echo "<a href=\"./process/process_comparision.php?action=delete&id={$row['Com_id']}\" 
                                                           class=\"btn btn-danger btn-sm\" 
                                                           onclick=\"return confirm('คุณต้องการลบรายการนี้ใช่หรือไม่?')\" 
                                                           title=\"ลบ\">
                                                            <i class=\"fas fa-trash\"></i>
                                                        </a>";
                                                }
                                                
                                                // แสดงความคิดเห็นจากผู้ดูแลระบบ (ถ้ามี)
                                                if (!empty($row['Comment'])) {
                                                    echo " <button type=\"button\" class=\"btn btn-secondary btn-sm\" title=\"ความคิดเห็น\" 
                                                             data-toggle=\"popover\" data-content=\"{$row['Comment']}\">
                                                            <i class=\"fas fa-comment\"></i>
                                                          </button>";
                                                }
                                                
                                                echo "</td>
                                                     </tr>";
                                                $i++;
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col-md-12 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

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
                <form action="./process/process_comparision.php?action=add" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="request_type">ประเภทการขอเทียบ <span class="text-danger">*</span></label>
                            <select class="form-control" id="request_type" name="request_type" required>
                                <option value="">เลือกประเภทการขอเทียบ</option>
                                <option value="position">ตำแหน่งในการจัดกิจกรรม</option>
                                <option value="award">รางวัล/การแข่งขัน</option>
                                <option value="helper">ผู้ช่วยกิจกรรม</option>
                            </select>
                        </div>

                        <!-- เปลี่ยนฟอร์มเลือกกิจกรรม -->
                        <div class="form-group">
                            <label for="act_id">เลือกกิจกรรม <span class="text-danger">*</span></label>
                            <select class="form-control" id="act_id" name="act_id" required
                                onchange="updateActivityDetails()">
                                <option value="">เลือกกิจกรรม</option>
                                <?php 
                                    // ดึงข้อมูลกิจกรรมทั้งหมดจากฐานข้อมูล
                                    $activities = $controller->getActivities();
                                    
                                    if ($activities) {
                                        foreach ($activities as $activity) {
                                            echo "<option value=\"{$activity['Act_id']}\" 
                                                data-semester=\"{$activity['ActSemester']}\" 
                                                data-year=\"{$activity['ActYear']}\" 
                                                data-hour=\"{$activity['Act_hour']}\">
                                                {$activity['Act_name']} ({$activity['ActSemester']}/{$activity['ActYear']})
                                                </option>";
                                        }
                                    }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="act_amount">จำนวนครั้งที่เข้าร่วม <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="act_amount" name="act_amount" min="1"
                                        max="100" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="act_hour">จำนวนชั่วโมงที่ขอเทียบ <span
                                            class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="act_hour" name="act_hour" min="1"
                                        max="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="upload_file">หลักฐานประกอบการขอเทียบ (ถ้ามี)</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="upload_file" name="upload_file">
                                    <label class="custom-file-label" for="upload_file">เลือกไฟล์</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">รองรับไฟล์ PDF, JPG, PNG ขนาดไม่เกิน 2MB</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">ยื่นคำร้อง</button>
                    </div>
                </form>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
</div>

<!-- Page specific script -->
<script>
function updateActivityDetails() {
    var activitySelect = document.getElementById('act_id');
    var semesterSelect = document.getElementById('act_semester');
    var yearSelect = document.getElementById('act_year');
    var hourInput = document.getElementById('act_hour');

    // ถ้าไม่ได้เลือกกิจกรรม ให้ล้างค่าฟิลด์อื่นๆ
    if (activitySelect.value === '') {
        semesterSelect.value = '';
        yearSelect.value = '';
        hourInput.value = '';

        // เปิดให้แก้ไขได้
        semesterSelect.disabled = false;
        yearSelect.disabled = false;
        return;
    }

    // ดึงข้อมูลจาก data attributes
    var selectedOption = activitySelect.options[activitySelect.selectedIndex];
    var semester = selectedOption.getAttribute('data-semester');
    var year = selectedOption.getAttribute('data-year');
    var hour = selectedOption.getAttribute('data-hour');

    // กำหนดค่าให้ฟิลด์
    semesterSelect.value = semester;
    yearSelect.value = year;

    // ถ้าไม่มีค่า hour ที่กรอกไว้ ให้กำหนดเป็นค่าจากกิจกรรม
    if (hourInput.value === '' || hourInput.value === '0') {
        hourInput.value = hour;
    }

    // ล็อคฟิลด์ไม่ให้แก้ไข
    semesterSelect.disabled = true;
    yearSelect.disabled = true;

    // สร้าง hidden fields เพื่อส่งค่า
    var form = activitySelect.closest('form');

    // ตรวจสอบและสร้าง/อัปเดต hidden field สำหรับ semester
    var hiddenSemester = document.getElementById('hidden_semester');
    if (!hiddenSemester) {
        hiddenSemester = document.createElement('input');
        hiddenSemester.type = 'hidden';
        hiddenSemester.id = 'hidden_semester';
        hiddenSemester.name = 'act_semester';
        form.appendChild(hiddenSemester);
    }
    hiddenSemester.value = semester;

    // ตรวจสอบและสร้าง/อัปเดต hidden field สำหรับ year
    var hiddenYear = document.getElementById('hidden_year');
    if (!hiddenYear) {
        hiddenYear = document.createElement('input');
        hiddenYear.type = 'hidden';
        hiddenYear.id = 'hidden_year';
        hiddenYear.name = 'act_year';
        form.appendChild(hiddenYear);
    }
    hiddenYear.value = year;
}

// เรียกฟังก์ชันเมื่อโหลดหน้าเพจและเมื่อเปิด modal
$(document).ready(function() {
    // ทำงานเมื่อเลือกกิจกรรม
    $('#act_id').change(function() {
        updateActivityDetails();
    });

    // ทำงานเมื่อเปิด modal
    $('#modal-add-request').on('shown.bs.modal', function() {
        updateActivityDetails();
    });
});

// เรียกฟังก์ชันเมื่อเปิด modal เพื่อตั้งค่าเริ่มต้น
$(document).ready(function() {
    $('#modal-add-request').on('shown.bs.modal', function() {
        updateActivityDetails();
    });
});
</script>