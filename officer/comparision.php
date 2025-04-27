<?php
// เรียกใช้ไฟล์ที่จำเป็น
include_once '../config/database.php';
include_once '../config/controller.php';
include_once 'includes/header.php';

// สร้างการเชื่อมต่อฐานข้อมูล
$database = new Database();
$db = $database->connect();

// SQL Query สำหรับดึงข้อมูลคำร้องพร้อม join กับตาราง student และ title
$sql = "SELECT c.*, s.Stu_fname, s.Stu_lname, t.Title_name 
        FROM comparision c
        LEFT JOIN student s ON c.Stu_id = s.Stu_id
        LEFT JOIN title t ON s.Title_id = t.Title_id
        ORDER BY c.Com_id DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$comparisions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">จัดการคำร้องขอเทียบกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">จัดการคำร้องขอเทียบกิจกรรม</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- แสดงข้อความแจ้งเตือน -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-check"></i> สำเร็จ!</h5>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-ban"></i> เกิดข้อผิดพลาด!</h5>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        รายการคำร้องขอเทียบกิจกรรม
                    </h3>
                </div>
                <div class="card-body">
                    <table id="table1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="5%">ลำดับ</th>
                                <th width="10%">รหัสนักศึกษา</th> 
                                <th width="15%">ชื่อ-สกุล</th>
                                <th width="15%">ชื่อกิจกรรม</th>
                                <th width="10%">จำนวนชั่วโมง</th>
                                <th width="15%">ภาคเรียน/ปีการศึกษา</th>
                                <th width="10%">สถานะ</th>
                                <th width="10%">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        if(!empty($comparisions)):
                            $i = 1;
                            foreach($comparisions as $row):
                                // กำหนด class ของสถานะ
                                $statusClass = '';
                                $statusText = '';
                                switch($row['Com_status']) {
                                    case 'pending':
                                        $statusClass = 'badge badge-warning';
                                        $statusText = 'รอการพิจารณา';
                                        break;
                                    case 'approved':
                                        $statusClass = 'badge badge-success';
                                        $statusText = 'อนุมัติแล้ว';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'badge badge-danger';
                                        $statusText = 'ไม่อนุมัติ';
                                        break;
                                }
                        ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $row['Stu_id']; ?></td>
                                <td><?php echo $row['Title_name'] . $row['Stu_fname'] . ' ' . $row['Stu_lname']; ?></td>
                                <td><?php echo $row['Com_name']; ?></td>
                                <td><?php echo $row['Com_hour']; ?></td>
                                <td><?php echo $row['Com_semester'] . '/' . date('Y', strtotime($row['Com_year'])); ?></td>
                                <td><span class="<?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                <td>
                                    <button type="button" 
                                            class="btn btn-info btn-sm view-request" 
                                            data-toggle="modal" 
                                            data-target="#viewRequestModal"
                                            data-id="<?php echo $row['Com_id']; ?>"
                                            data-student="<?php echo $row['Stu_id']; ?>"
                                            data-name="<?php echo $row['Title_name'].$row['Stu_fname'].' '.$row['Stu_lname']; ?>"
                                            data-activity="<?php echo $row['Com_name']; ?>"
                                            data-hour="<?php echo $row['Com_hour']; ?>"
                                            data-semester="<?php echo $row['Com_semester'].'/'.date('Y', strtotime($row['Com_year'])); ?>"
                                            data-upload="<?php echo $row['Upload'] ?? ''; ?>">
                                        <i class="fas fa-eye"></i> ดูรายละเอียด
                                    </button>
                                </td>
                            </tr>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal แสดงรายละเอียดคำร้อง -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title"><i class="fas fa-file-alt mr-2"></i>รายละเอียดคำร้องขอเทียบกิจกรรม</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="processRequestForm" action="process/process_comparision.php" method="POST">
                <input type="hidden" name="action" value="process">
                <input type="hidden" name="com_id" id="com_id">
                
                <div class="modal-body">
                    <!-- ข้อมูลนักศึกษา -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>รหัสนักศึกษา:</label>
                                <input type="text" id="stu_id" name="stu_id" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อ-สกุล:</label>
                                <input type="text" id="stu_name" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลกิจกรรม -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อกิจกรรม:</label>
                                <input type="text" id="activity_name" name="activity_name" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>จำนวนชั่วโมง:</label>
                                <input type="text" id="activity_hour" name="activity_hour" class="form-control" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ภาคเรียน/ปีการศึกษา:</label>
                                <input type="text" id="semester" class="form-control" readonly>
                                <input type="hidden" id="semester_val" name="semester">
                                <input type="hidden" id="year_val" name="year">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>หลักฐานประกอบ:</label>
                                <div id="upload_file"></div>
                            </div>
                        </div>
                    </div>

                    <!-- ส่วนการพิจารณา -->
                    <div class="form-group">
                        <label>ผลการพิจารณา: <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="">-- เลือกผลการพิจารณา --</option>
                            <option value="approved">อนุมัติ</option>
                            <option value="rejected">ไม่อนุมัติ</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึกผลการพิจารณา</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    // แสดงข้อมูลใน Modal เมื่อคลิกปุ่ม
    $('.view-request').click(function() {
        var button = $(this);
        var semester = button.data('semester').split('/');
        
        $('#com_id').val(button.data('id'));
        $('#stu_id').val(button.data('student'));
        $('#stu_name').val(button.data('name'));
        $('#activity_name').val(button.data('activity'));
        $('#activity_hour').val(button.data('hour'));
        $('#semester').val(button.data('semester'));
        $('#semester_val').val(semester[0]);
        $('#year_val').val(semester[1]);
        
        // แสดงลิงก์ไฟล์แนบ
        var uploadFile = button.data('upload');
        if(uploadFile) {
            $('#upload_file').html(
                '<a href="../uploads/comparision/' + uploadFile + 
                '" target="_blank" class="btn btn-info btn-sm">' +
                '<i class="fas fa-file mr-1"></i>ดูหลักฐาน</a>'
            );
        } else {
            $('#upload_file').html('<p class="text-muted mb-0">ไม่มีไฟล์แนบ</p>');
        }
        
        $('#status').val('');
    });
});
</script>

<?php include 'includes/footer.php'; ?>