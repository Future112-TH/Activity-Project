<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลกิจกรรมทั้งหมด
$activities = $controller->getActivities();

// ดึงข้อมูลประเภทกิจกรรมทั้งหมด
$activityTypes = $controller->getActivityTypes();

// ดึงข้อมูลสาขาทั้งหมด
$majors = $controller->getMajors();

// ดึงข้อมูลกิจกรรมที่ต้องการแก้ไข (ถ้ามี)
$editActivity = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editActivity = $controller->getActivityById($_GET['edit']);
}

// ดึงข้อมูลกิจกรรมที่ต้องการดู (ถ้ามี)
$viewActivity = null;
$participants = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $viewActivity = $controller->getActivityById($_GET['view']);
    if ($viewActivity) {
        $participants = $controller->getActivityParticipants($viewActivity['Act_id']);
    }
}
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-calendar-alt mr-2"></i>ข้อมูลกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ข้อมูลกิจกรรม</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <!-- แสดงข้อความสำเร็จหรือข้อผิดพลาด -->
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
                <h5><i class="icon fas fa-ban"></i> ผิดพลาด!</h5>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการกิจกรรม
                            </h3>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                                data-target="#addActivityModal">
                                <i class="fas fa-plus-circle mr-1"></i> เพิ่มกิจกรรม
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="5%">รหัส</th>
                                            <th width="25%">ชื่อกิจกรรม</th>
                                            <th width="10%">ประเภท</th>
                                            <th width="10%">จำนวนชั่วโมง</th>
                                            <th width="12%">วันที่เริ่ม</th>
                                            <th width="12%">วันที่สิ้นสุด</th>
                                            <th width="8%">ภาคเรียน</th>
                                            <th width="8%">สถานะ</th>
                                            <th width="10%">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($activities && $activities->rowCount() > 0) {
                                            while($row = $activities->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['Act_id']; ?></td>
                                            <td><?php echo $row['Act_name']; ?></td>
                                            <td><?php echo $row['ActType_Name']; ?></td>
                                            <td><?php echo $row['Act_hour']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['Act_start_date'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row['Act_stop_date'])); ?></td>
                                            <td><?php echo $row['ActSemester']; ?></td>
                                            <td>
                                                <?php 
                                                $statusClass = '';
                                                switch($row['ActStatus']) {
                                                    case 'รอดำเนินการ': $statusClass = 'secondary'; break;
                                                    case 'ดำเนินการ': $statusClass = 'success'; break;
                                                    case 'เสร็จสิ้น': $statusClass = 'info'; break;
                                                    case 'ยกเลิก': $statusClass = 'danger'; break;
                                                    default: $statusClass = 'secondary'; break;
                                                }
                                                ?>
                                                <span
                                                    class="badge badge-<?php echo $statusClass; ?>"><?php echo $row['ActStatus']; ?></span>
                                            </td>
                                            <td>
                                                <a href="index.php?menu=10&view=<?php echo $row['Act_id']; ?>"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="index.php?menu=10&edit=<?php echo $row['Act_id']; ?>"
                                                    class="btn btn-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="process/process_activity.php?action=delete&id=<?php echo $row['Act_id']; ?>"
                                                    class="btn btn-danger btn-sm delete-activity"
                                                    onclick="return confirm('คุณต้องการลบกิจกรรมรหัส <?php echo $row['Act_id']; ?> ใช่หรือไม่?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
                                        </tr>
                                        <?php 
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
    </div>

    <!-- Modal: เพิ่มกิจกรรม -->
    <div class="modal fade" id="addActivityModal" tabindex="-1" aria-labelledby="addActivityLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="process/process_activity.php" method="post" id="addActivityForm">
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มกิจกรรม
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="act_name">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                                    <input type="text" name="act_name" id="act_name" class="form-control" required
                                        placeholder="ชื่อกิจกรรม">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="act_hour">จำนวนชั่วโมง <span class="text-danger">*</span></label>
                                    <input type="number" name="act_hour" id="act_hour" class="form-control" required
                                        min="1" value="1">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="act_start_date">วันที่เริ่มกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="act_start_date" id="act_start_date" class="form-control"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="act_stop_date">วันที่สิ้นสุดกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="act_stop_date" id="act_stop_date" class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- แยกฟิลด์ภาคเรียนและปีการศึกษา -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="act_term">ภาคเรียน <span class="text-danger">*</span></label>
                                    <select name="act_term" id="act_term" class="form-control" required>
                                        <option value="">เลือกภาคเรียน</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3">3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="act_year_number">ปีการศึกษา <span class="text-danger">*</span></label>
                                    <select name="act_year_number" id="act_year_number" class="form-control" required>
                                        <option value="">เลือกปีการศึกษา</option>
                                        <?php 
                                        $currentYear = date('Y') + 543; // เปลี่ยนเป็นปี พ.ศ.
                                        // แสดงตัวเลือกปีปัจจุบันและย้อนหลัง 5 ปี
                                        for ($i = 0; $i < 6; $i++) {
                                            $yearOption = $currentYear - $i;
                                            echo "<option value=\"$yearOption\">$yearOption</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="act_status">สถานะกิจกรรม <span class="text-danger">*</span></label>
                                    <select name="act_status" id="act_status" class="form-control" required>
                                        <option value="">เลือกสถานะ</option>
                                        <option value="รอดำเนินการ">รอดำเนินการ</option>
                                        <option value="ดำเนินการ">ดำเนินการ</option>
                                        <option value="เสร็จสิ้น">เสร็จสิ้น</option>
                                        <option value="ยกเลิก">ยกเลิก</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="act_year">ปีที่จัด <span class="text-danger">*</span></label>
                                    <select name="act_year" id="act_year" class="form-control" required>
                                        <option value="">เลือกปีที่จัด</option>
                                        <?php 
                                        $currentYear = date('Y');
                                        for ($i = 0; $i < 6; $i++) {
                                            $yearOption = $currentYear - $i;
                                            echo "<option value=\"$yearOption-01-01\">$yearOption</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <!-- เพิ่มฟิลด์ซ่อนสำหรับเก็บค่ารวมภาคเรียนและปีการศึกษา -->
                            <input type="hidden" name="act_semester" id="act_semester" value="">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="act_type_id">ประเภทกิจกรรม <span class="text-danger">*</span></label>
                                    <select name="act_type_id" id="act_type_id" class="form-control" required>
                                        <option value="">เลือกประเภทกิจกรรม</option>
                                        <?php 
                                        if($activityTypes && $activityTypes->rowCount() > 0) {
                                            while($type = $activityTypes->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $type['ActType_id'] . '">' . $type['ActType_Name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="maj_id">สาขาวิชาที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                    <select name="maj_id" id="maj_id" class="form-control" required>
                                        <option value="">เลือกสาขาวิชา</option>
                                        <?php 
                                        if($majors && $majors->rowCount() > 0) {
                                            while($major = $majors->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $major['Maj_id'] . '">' . $major['Maj_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-primary" onclick="combineTermAndYearBeforeSubmit()">
                            <i class="fas fa-save mr-1"></i> บันทึก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: แก้ไขกิจกรรม -->
    <?php if($editActivity): 
        // แยกภาคเรียนและปีการศึกษาจากค่าที่จัดเก็บในฐานข้อมูล (เช่น 1/2567)
        $semesterParts = explode('/', $editActivity['ActSemester']);
        $editTerm = isset($semesterParts[0]) ? $semesterParts[0] : '';
        $editYear = isset($semesterParts[1]) ? $semesterParts[1] : '';
    ?>
    <div class="modal fade show" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl">
            <form action="process/process_activity.php" method="post" id="editActivityForm">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="edit_act_id" value="<?php echo $editActivity['Act_id']; ?>">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขข้อมูลกิจกรรม
                        </h5>
                        <a href="index.php?menu=10" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="edit_act_name">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_act_name" id="edit_act_name" class="form-control"
                                        required value="<?php echo $editActivity['Act_name']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_act_hour">จำนวนชั่วโมง <span class="text-danger">*</span></label>
                                    <input type="number" name="edit_act_hour" id="edit_act_hour" class="form-control"
                                        required min="1" value="<?php echo $editActivity['Act_hour']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_act_start_date">วันที่เริ่มกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="edit_act_start_date" id="edit_act_start_date"
                                        class="form-control" required
                                        value="<?php echo $editActivity['Act_start_date']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_act_stop_date">วันที่สิ้นสุดกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <input type="date" name="edit_act_stop_date" id="edit_act_stop_date"
                                        class="form-control" required
                                        value="<?php echo $editActivity['Act_stop_date']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <!-- แยกฟิลด์ภาคเรียนและปีการศึกษาสำหรับฟอร์มแก้ไข -->
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_act_term">ภาคเรียน <span class="text-danger">*</span></label>
                                    <select name="edit_act_term" id="edit_act_term" class="form-control" required>
                                        <option value="">เลือกภาคเรียน</option>
                                        <option value="1" <?php echo ($editTerm == '1') ? 'selected' : ''; ?>>1</option>
                                        <option value="2" <?php echo ($editTerm == '2') ? 'selected' : ''; ?>>2</option>
                                        <option value="3" <?php echo ($editTerm == '3') ? 'selected' : ''; ?>>3</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_act_year_number">ปีการศึกษา <span
                                            class="text-danger">*</span></label>
                                    <select name="edit_act_year_number" id="edit_act_year_number" class="form-control"
                                        required>
                                        <option value="">เลือกปีการศึกษา</option>
                                        <?php 
                                        $currentYear = date('Y') + 543; // เปลี่ยนเป็นปี พ.ศ.
                                        // แสดงตัวเลือกปีปัจจุบันและย้อนหลัง 5 ปี
                                        for ($i = 0; $i < 6; $i++) {
                                            $yearOption = $currentYear - $i;
                                            $selected = ($editYear == $yearOption) ? 'selected' : '';
                                            echo "<option value=\"$yearOption\" $selected>$yearOption</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_act_status">สถานะกิจกรรม <span class="text-danger">*</span></label>
                                    <select name="edit_act_status" id="edit_act_status" class="form-control" required>
                                        <option value="">เลือกสถานะ</option>
                                        <option value="รอดำเนินการ"
                                            <?php echo ($editActivity['ActStatus'] == 'รอดำเนินการ') ? 'selected' : ''; ?>>
                                            รอดำเนินการ</option>
                                        <option value="ดำเนินการ"
                                            <?php echo ($editActivity['ActStatus'] == 'ดำเนินการ') ? 'selected' : ''; ?>>
                                            ดำเนินการ</option>
                                        <option value="เสร็จสิ้น"
                                            <?php echo ($editActivity['ActStatus'] == 'เสร็จสิ้น') ? 'selected' : ''; ?>>
                                            เสร็จสิ้น</option>
                                        <option value="ยกเลิก"
                                            <?php echo ($editActivity['ActStatus'] == 'ยกเลิก') ? 'selected' : ''; ?>>
                                            ยกเลิก</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_act_year">ปีที่จัด <span class="text-danger">*</span></label>
                                    <input type="date" name="edit_act_year" id="edit_act_year" class="form-control"
                                        required value="<?php echo $editActivity['ActYear']; ?>">
                                </div>
                            </div>
                            <!-- เพิ่มฟิลด์ซ่อนสำหรับเก็บค่ารวมภาคเรียนและปีการศึกษา -->
                            <input type="hidden" name="edit_act_semester" id="edit_act_semester"
                                value="<?php echo $editActivity['ActSemester']; ?>">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_act_type_id">ประเภทกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <select name="edit_act_type_id" id="edit_act_type_id" class="form-control" required>
                                        <option value="">เลือกประเภทกิจกรรม</option>
                                        <?php 
                                        $activityTypes = $controller->getActivityTypes(); // รีเซ็ต pointer
                                        if($activityTypes && $activityTypes->rowCount() > 0) {
                                            while($type = $activityTypes->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = ($editActivity['ActType_id'] == $type['ActType_id']) ? 'selected' : '';
                                                echo '<option value="' . $type['ActType_id'] . '" ' . $selected . '>' . $type['ActType_Name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_maj_id">สาขาวิชาที่จัดกิจกรรม <span
                                            class="text-danger">*</span></label>
                                    <select name="edit_maj_id" id="edit_maj_id" class="form-control" required>
                                        <option value="">เลือกสาขาวิชา</option>
                                        <?php 
                                        $majors = $controller->getMajors(); // รีเซ็ต pointer
                                        if($majors && $majors->rowCount() > 0) {
                                            while($major = $majors->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = ($editActivity['Maj_id'] == $major['Maj_id']) ? 'selected' : '';
                                                echo '<option value="' . $major['Maj_id'] . '" ' . $selected . '>' . $major['Maj_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="index.php?menu=10" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-warning" onclick="combineEditTermAndYearBeforeSubmit()">
                            <i class="fas fa-save mr-1"></i> บันทึกการแก้ไข
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Modal: ดูข้อมูลกิจกรรม (แสดงถ้ามีการกดปุ่มดูข้อมูล) -->
    <?php if($viewActivity): ?>
    <div class="modal fade show" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <!-- ส่วนต่อเนื่องของ Modal: ดูข้อมูลกิจกรรม -->
                    <h5 class="modal-title text-white">
                        <i class="fas fa-calendar-alt mr-1"></i> รายละเอียดกิจกรรม
                    </h5>
                    <a href="index.php?menu=10" class="close text-white" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card card-primary card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">ข้อมูลพื้นฐานกิจกรรม</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">ชื่อกิจกรรม:</div>
                                        <div class="col-md-8"><?php echo $viewActivity['Act_name']; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">ประเภทกิจกรรม:</div>
                                        <div class="col-md-8"><?php echo $viewActivity['ActType_Name']; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">สาขาวิชา:</div>
                                        <div class="col-md-8"><?php echo $viewActivity['Maj_name']; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-4 font-weight-bold">จำนวนชั่วโมง:</div>
                                        <div class="col-md-8"><?php echo $viewActivity['Act_hour']; ?> ชั่วโมง</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-warning card-outline">
                                <div class="card-header">
                                    <h3 class="card-title">ระยะเวลากิจกรรม</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-md-5 font-weight-bold">วันที่เริ่ม:</div>
                                        <div class="col-md-7">
                                            <?php echo date('d/m/Y', strtotime($viewActivity['Act_start_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-5 font-weight-bold">วันที่สิ้นสุด:</div>
                                        <div class="col-md-7">
                                            <?php echo date('d/m/Y', strtotime($viewActivity['Act_stop_date'])); ?>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-5 font-weight-bold">ภาคเรียน:</div>
                                        <div class="col-md-7"><?php echo $viewActivity['ActSemester']; ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-5 font-weight-bold">ปีที่จัด:</div>
                                        <div class="col-md-7">
                                            <?php echo date('Y', strtotime($viewActivity['ActYear'])); ?></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-5 font-weight-bold">สถานะ:</div>
                                        <div class="col-md-7">
                                            <?php 
                                            $statusClass = '';
                                            switch($viewActivity['ActStatus']) {
                                                case 'รอดำเนินการ': $statusClass = 'secondary'; break;
                                                case 'ดำเนินการ': $statusClass = 'success'; break;
                                                case 'เสร็จสิ้น': $statusClass = 'info'; break;
                                                case 'ยกเลิก': $statusClass = 'danger'; break;
                                                default: $statusClass = 'secondary'; break;
                                            }
                                            ?>
                                            <span
                                                class="badge badge-<?php echo $statusClass; ?>"><?php echo $viewActivity['ActStatus']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">รายชื่อนักศึกษาที่เข้าร่วมกิจกรรม</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th width="10%">รหัสนักศึกษา</th>
                                                <th width="25%">ชื่อ-สกุล</th>
                                                <th width="20%">สาขาวิชา</th>
                                                <th width="10%">ประเภท</th>
                                                <th width="10%">ชั่วโมง</th>
                                                <th width="15%">เช็คชื่อ</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalHours = 0;
                                            $participantCount = 0;
                                            
                                            if($participants && $participants->rowCount() > 0) {
                                                while($student = $participants->fetch(PDO::FETCH_ASSOC)) {
                                                    $participantCount++;
                                                    $totalHours += $student['Act_hour'];
                                            ?>
                                            <tr>
                                                <td><?php echo $student['Stu_id']; ?></td>
                                                <td><?php echo $student['Stu_fname'] . ' ' . $student['Stu_lname']; ?>
                                                </td>
                                                <td><?php echo $student['Maj_name']; ?></td>
                                                <td><span class="badge badge-primary">IT</span></td>
                                                <td><?php echo $student['Act_hour']; ?></td>
                                                <td><span
                                                        class="badge badge-success"><?php echo $student['CheckIn']; ?></span>
                                                </td>
                                            </tr>
                                            <?php 
                                                }
                                            } else {
                                            ?>
                                            <tr>
                                                <td colspan="6" class="text-center">ไม่พบข้อมูลผู้เข้าร่วมกิจกรรม</td>
                                            </tr>
                                            <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="card-footer text-center">
                                    <small class="text-muted">
                                        จำนวนนักศึกษาทั้งหมด: <?php echo $participantCount; ?> คน | รวมชั่วโมงกิจกรรม:
                                        <?php echo $totalHours; ?> ชั่วโมง
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?menu=10" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> ปิด
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>
</div>
<?php require_once 'includes/tablejs.php' ?>

<script type="text/javascript">
// JavaScript รวมสำหรับการจัดการฟอร์มกิจกรรม
$(document).ready(function() {
    console.log("Activity page and script loaded");

    // ===== ฟังก์ชันสำหรับการรวมค่าภาคเรียนและปีการศึกษา =====

    // สำหรับฟอร์มเพิ่มกิจกรรม
    window.combineTermAndYearBeforeSubmit = function() {
        const term = $('#act_term').val();
        const year = $('#act_year_number').val();

        if (term && year) {
            const semester = term + '/' + year;
            $('#act_semester').val(semester);
            console.log("Add form - Combined semester: " + semester);
            return true;
        } else {
            console.error("Add form - Term or Year is missing - Term: " + term + ", Year: " + year);
            return false;
        }
    };

    // สำหรับฟอร์มแก้ไขกิจกรรม
    window.combineEditTermAndYearBeforeSubmit = function() {
        const term = $('#edit_act_term').val();
        const year = $('#edit_act_year_number').val();

        if (term && year) {
            const semester = term + '/' + year;
            $('#edit_act_semester').val(semester);
            console.log("Edit form - Combined semester: " + semester);
            return true;
        } else {
            console.error("Edit form - Term or Year is missing - Term: " + term + ", Year: " + year);
            return false;
        }
    };

    // ===== การจัดการฟอร์มเพิ่มกิจกรรม =====

    // เมื่อเปิดโมดัลเพิ่มกิจกรรม ตั้งค่าเริ่มต้น
    $('#addActivityModal').on('shown.bs.modal', function() {
        console.log("Add modal shown");

        // ตั้งค่าวันที่ปัจจุบัน
        const today = new Date();
        const formattedDate = today.toISOString().substr(0, 10);
        $('#act_start_date, #act_stop_date').val(formattedDate);

        // ตั้งค่าปีที่จัด (ค.ศ.)
        $('#act_year').val(today.getFullYear() + "-01-01");

        // ตั้งค่าปีการศึกษาปัจจุบัน (พ.ศ.)
        const currentThaiYear = today.getFullYear() + 543;
        $('#act_year_number').val(currentThaiYear);

        // ตั้งค่าภาคเรียนตามเดือนปัจจุบัน
        const currentMonth = today.getMonth() + 1;
        let currentTerm = "1"; // ค่าเริ่มต้น

        if (currentMonth >= 6 && currentMonth <= 10) {
            currentTerm = "1";
        } else if (currentMonth >= 11 || currentMonth <= 3) {
            currentTerm = "2";
        } else {
            currentTerm = "3";
        }

        $('#act_term').val(currentTerm);

        // สร้างค่า act_semester จากภาคเรียนและปีการศึกษา
        combineTermAndYearBeforeSubmit();
        console.log("Add form - Initial values set - Term: " + currentTerm + ", Year: " +
            currentThaiYear);
    });

    // เมื่อเลือกวันที่เริ่ม ให้กำหนดวันที่สิ้นสุดเป็นวันเดียวกัน
    $('#act_start_date').on('change', function() {
        $('#act_stop_date').val($(this).val());
    });

    // รวมค่าเมื่อมีการเปลี่ยนแปลงในฟอร์มเพิ่ม
    $('#act_term, #act_year_number').on('change', function() {
        console.log("Add form - " + $(this).attr('id') + " changed to " + $(this).val());
        combineTermAndYearBeforeSubmit();
    });

    // ตรวจสอบฟอร์มเพิ่มกิจกรรมก่อนส่ง
    $('#addActivityForm').on('submit', function(e) {
        console.log("Add form submitted");

        // รวมค่าภาคเรียนและปีการศึกษาอีกครั้ง
        if (!combineTermAndYearBeforeSubmit()) {
            e.preventDefault();
            alert('กรุณาเลือกภาคเรียนและปีการศึกษาให้ครบถ้วน');
            return false;
        }

        // ตรวจสอบค่า Semester
        const actSemester = $('#act_semester').val();
        console.log("Add form - Submitting with semester value: " + actSemester);

        if (!actSemester) {
            console.error("Add form - Semester value is empty!");
            e.preventDefault();
            alert('ค่าภาคเรียน/ปีการศึกษารวมกันว่างเปล่า กรุณาเลือกภาคเรียนและปีการศึกษาอีกครั้ง');
            return false;
        }

        // ตรวจสอบข้อมูลที่จำเป็น
        const actName = $('#act_name').val();
        const actHour = $('#act_hour').val();
        const actStartDate = $('#act_start_date').val();
        const actStopDate = $('#act_stop_date').val();
        const actStatus = $('#act_status').val();
        const actYear = $('#act_year').val();
        const actTypeId = $('#act_type_id').val();
        const majId = $('#maj_id').val();

        if (!actName || !actHour || !actStartDate || !actStopDate ||
            !actSemester || !actStatus || !actYear || !actTypeId || !majId) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลในช่องที่มีเครื่องหมาย * ให้ครบถ้วน');
            return false;
        }

        // ตรวจสอบวันที่
        const startDate = new Date(actStartDate);
        const stopDate = new Date(actStopDate);

        if (stopDate < startDate) {
            e.preventDefault();
            alert('วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม');
            return false;
        }

        return true;
    });

    // ===== การจัดการฟอร์มแก้ไขกิจกรรม =====

    // ตรวจสอบว่ามีฟอร์มแก้ไขเปิดอยู่หรือไม่
    if ($('#editActivityModal').length > 0 && $('#editActivityModal').is(':visible')) {
        console.log("Edit form is visible on page load");

        // รวมค่าเมื่อโหลดหน้า
        combineEditTermAndYearBeforeSubmit();
    }

    // เมื่อเปิดโมดัลแก้ไข
    $('#editActivityModal').on('shown.bs.modal', function() {
        console.log("Edit modal shown");

        // รวมค่าภาคเรียนและปีการศึกษา
        combineEditTermAndYearBeforeSubmit();
    });

    // รวมค่าเมื่อมีการเปลี่ยนแปลงในฟอร์มแก้ไข
    $('#edit_act_term, #edit_act_year_number').on('change', function() {
        console.log("Edit form - " + $(this).attr('id') + " changed to " + $(this).val());
        combineEditTermAndYearBeforeSubmit();
    });

    // ตรวจสอบฟอร์มแก้ไขกิจกรรมก่อนส่ง
    $('#editActivityForm').on('submit', function(e) {
        console.log("Edit form submitted");

        // รวมค่าภาคเรียนและปีการศึกษาอีกครั้ง
        if (!combineEditTermAndYearBeforeSubmit()) {
            e.preventDefault();
            alert('กรุณาเลือกภาคเรียนและปีการศึกษาให้ครบถ้วน');
            return false;
        }

        // ตรวจสอบค่า Semester
        const actSemester = $('#edit_act_semester').val();
        console.log("Edit form - Submitting with semester value: " + actSemester);

        if (!actSemester) {
            console.error("Edit form - Semester value is empty!");
            e.preventDefault();
            alert('ค่าภาคเรียน/ปีการศึกษารวมกันว่างเปล่า กรุณาเลือกภาคเรียนและปีการศึกษาอีกครั้ง');
            return false;
        }

        // ตรวจสอบข้อมูลที่จำเป็น
        const actName = $('#edit_act_name').val();
        const actHour = $('#edit_act_hour').val();
        const actStartDate = $('#edit_act_start_date').val();
        const actStopDate = $('#edit_act_stop_date').val();
        const actStatus = $('#edit_act_status').val();
        const actYear = $('#edit_act_year').val();
        const actTypeId = $('#edit_act_type_id').val();
        const majId = $('#edit_maj_id').val();

        if (!actName || !actHour || !actStartDate || !actStopDate ||
            !actSemester || !actStatus || !actYear || !actTypeId || !majId) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลให้ครบถ้วน');
            return false;
        }

        // ตรวจสอบวันที่
        const startDate = new Date(actStartDate);
        const stopDate = new Date(actStopDate);

        if (stopDate < startDate) {
            e.preventDefault();
            alert('วันที่สิ้นสุดกิจกรรมต้องไม่น้อยกว่าวันที่เริ่มกิจกรรม');
            return false;
        }

        return true;
    });
});
</script>

<?php require_once 'includes/tablejs.php' ?>