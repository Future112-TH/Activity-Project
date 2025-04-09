<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลที่จำเป็น
$professors = $controller->getProfessors();  // อาจารย์ทั้งหมด
$titles = $controller->getTitles();          // คำนำหน้าทั้งหมด
$majors = $controller->getMajors();          // สาขาทั้งหมด

// ดึงข้อมูลอาจารย์ที่ต้องการแก้ไข (ถ้ามี)
$editProfessor = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editProfessor = $controller->getProfessorById($_GET['edit']);
}

// ดึงข้อมูลอาจารย์ที่ต้องการดู (ถ้ามี)
$viewProfessor = null;
$advisoryStudents = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $viewProfessor = $controller->getProfessorById($_GET['view']);
    if ($viewProfessor) {
        $advisoryStudents = $controller->getAdvisoryStudents($viewProfessor['Prof_id']);
    }
}
?>

<div class="content-wrapper">
    <!-- ส่วนหัวของหน้า -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-chalkboard-teacher mr-2"></i>อาจารย์ที่ปรึกษา</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">อาจารย์ที่ปรึกษา</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- เนื้อหาหลัก -->
    <section class="content">
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

            <!-- ตารางแสดงข้อมูลอาจารย์ที่ปรึกษา -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        รายการอาจารย์ที่ปรึกษา
                    </h3>
                    <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                        data-target="#addAdvisorModal">
                        <i class="fas fa-plus-circle mr-1"></i> เพิ่มอาจารย์ที่ปรึกษา
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table1" class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-light">
                                    <th width="12%">รหัสอาจารย์</th>
                                    <th width="10%">คำนำหน้า</th>
                                    <th width="22%">ชื่อ</th>
                                    <th width="22%">นามสกุล</th>
                                    <th width="22%">สาขาวิชา</th>
                                    <th width="12%" class="text-center">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($professors && $professors->rowCount() > 0): ?>
                                <?php while($row = $professors->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['Prof_id']; ?></td>
                                    <td><?php echo isset($row['Title_name']) ? $row['Title_name'] : ''; ?></td>
                                    <td><?php echo $row['Prof_fname']; ?></td>
                                    <td><?php echo $row['Prof_lname']; ?></td>
                                    <td><?php echo $row['Maj_name']; ?></td>
                                    <td>
                                        <div>
                                            <a href="index.php?menu=6&view=<?php echo $row['Prof_id']; ?>"
                                                class="btn btn-info btn-sm"
                                                title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="index.php?menu=6&edit=<?php echo $row['Prof_id']; ?>"
                                                class="btn btn-warning btn-sm"
                                                title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="post" action="process/process_advisor.php"
                                                style="display: inline-block;"
                                                id="deleteForm<?php echo $row['Prof_id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?php echo $row['Prof_id']; ?>">
                                                <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="confirmDelete(<?php echo $row['Prof_id']; ?>)"
                                                    title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">ไม่พบข้อมูล</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal: แก้ไขอาจารย์ที่ปรึกษา -->
    <?php if($editProfessor): ?>
    <div class="modal fade show" id="editAdvisorModal" tabindex="-1" aria-labelledby="editAdvisorLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl   ">
            <form action="process/process_advisor.php" method="post" id="editAdvisorForm" class="needs-validation"
                novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="edit_prof_id" value="<?php echo $editProfessor['Prof_id']; ?>">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขข้อมูลอาจารย์ที่ปรึกษา
                        </h5>
                        <a href="index.php?menu=6" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                    <div class="modal-body">
                        <!-- ข้อมูลส่วนที่ 1: ข้อมูลพื้นฐาน -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_prof_id">รหัสอาจารย์</label>
                                    <input type="text" class="form-control"
                                        value="<?php echo $editProfessor['Prof_id']; ?>" readonly>
                                    <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสอาจารย์ได้</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_title_id">คำนำหน้า <span class="text-danger">*</span></label>
                                    <select name="edit_title_id" id="edit_title_id" class="form-control" required>
                                        <option value="">เลือกคำนำหน้า</option>
                                        <?php 
                                        if($titles && $titles->rowCount() > 0) {
                                            // รีเซ็ต pointer
                                            if ($titles instanceof PDOStatement) {
                                                $titles->execute();
                                            }
                                            
                                            while($title = $titles->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = ($editProfessor['Title_id'] == $title['Title_id']) ? 'selected' : '';
                                                echo '<option value="' . $title['Title_id'] . '" ' . $selected . '>' . $title['Title_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกคำนำหน้า</div>
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 2: ชื่อ-นามสกุล -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_prof_fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_prof_fname" id="edit_prof_fname" class="form-control"
                                        required value="<?php echo $editProfessor['Prof_fname']; ?>">
                                    <div class="invalid-feedback">กรุณากรอกชื่อ</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_prof_lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_prof_lname" id="edit_prof_lname" class="form-control"
                                        required value="<?php echo $editProfessor['Prof_lname']; ?>">
                                    <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 3: ข้อมูลติดต่อ -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_phone">เบอร์โทรศัพท์</label>
                                    <input type="text" name="edit_phone" id="edit_phone" class="form-control"
                                        maxlength="10" pattern="[0-9]{10}"
                                        value="<?php echo $editProfessor['Phone']; ?>">
                                    <small class="form-text text-muted">กรอกตัวเลข 10 หลัก</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_email">อีเมล</label>
                                    <input type="email" name="edit_email" id="edit_email" class="form-control"
                                        value="<?php echo $editProfessor['Email']; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 4: สาขา -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_major_id">สาขาวิชา <span class="text-danger">*</span></label>
                                    <select name="edit_major_id" id="edit_major_id" class="form-control" required>
                                        <option value="">เลือกสาขาวิชา</option>
                                        <?php 
                                        if($majors && $majors->rowCount() > 0) {
                                            // รีเซ็ต pointer
                                            if ($majors instanceof PDOStatement) {
                                                $majors->execute();
                                            }
                                            
                                            while($major = $majors->fetch(PDO::FETCH_ASSOC)) {
                                                $selected = ($editProfessor['Major_id'] == $major['Maj_id']) ? 'selected' : '';
                                                echo '<option value="' . $major['Maj_id'] . '" ' . $selected . '>' . $major['Maj_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกสาขาวิชา</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="index.php?menu=6" class="btn btn-secondary">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> บันทึกการแก้ไข
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>

    <!-- Modal: เพิ่มอาจารย์ที่ปรึกษา -->
    <div class="modal fade" id="addAdvisorModal" tabindex="-1" aria-labelledby="addAdvisorLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="process/process_advisor.php" method="post" id="addAdvisorForm" class="needs-validation"
                novalidate>
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มอาจารย์ที่ปรึกษา
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- ข้อมูลส่วนที่ 1: ข้อมูลพื้นฐาน -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prof_id">รหัสอาจารย์ <span class="text-danger">*</span></label>
                                    <input type="text" name="prof_id" id="prof_id" class="form-control" required
                                        maxlength="5" pattern="[0-9]{5}" placeholder="รหัสอาจารย์ 5 หลัก">
                                    <div class="invalid-feedback">กรุณากรอกรหัสอาจารย์ 5 หลัก</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title_id">คำนำหน้า <span class="text-danger">*</span></label>
                                    <select name="title_id" id="title_id" class="form-control" required>
                                        <option value="">เลือกคำนำหน้า</option>
                                        <?php 
                                        if($titles && $titles->rowCount() > 0) {
                                            // รีเซ็ต pointer
                                            if ($titles instanceof PDOStatement) {
                                                $titles->execute();
                                            }
                                            
                                            while($title = $titles->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $title['Title_id'] . '">' . $title['Title_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกคำนำหน้า</div>
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 2: ชื่อ-นามสกุล -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prof_fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" name="prof_fname" id="prof_fname" class="form-control" required
                                        placeholder="ชื่อ">
                                    <div class="invalid-feedback">กรุณากรอกชื่อ</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prof_lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="prof_lname" id="prof_lname" class="form-control" required
                                        placeholder="นามสกุล">
                                    <div class="invalid-feedback">กรุณากรอกนามสกุล</div>
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 3: ข้อมูลติดต่อ -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">เบอร์โทรศัพท์</label>
                                    <input type="text" name="phone" id="phone" class="form-control" maxlength="10"
                                        pattern="[0-9]{10}" placeholder="เบอร์โทรศัพท์ 10 หลัก">
                                    <small class="form-text text-muted">กรอกตัวเลข 10 หลัก</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">อีเมล</label>
                                    <input type="email" name="email" id="email" class="form-control"
                                        placeholder="อีเมล">
                                </div>
                            </div>
                        </div>

                        <!-- ข้อมูลส่วนที่ 4: สาขา -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="major_id">สาขาวิชา <span class="text-danger">*</span></label>
                                    <select name="major_id" id="major_id" class="form-control" required>
                                        <option value="">เลือกสาขาวิชา</option>
                                        <?php 
                                        if($majors && $majors->rowCount() > 0) {
                                            // รีเซ็ต pointer
                                            if ($majors instanceof PDOStatement) {
                                                $majors->execute();
                                            }
                                            
                                            while($major = $majors->fetch(PDO::FETCH_ASSOC)) {
                                                echo '<option value="' . $major['Maj_id'] . '">' . $major['Maj_name'] . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">กรุณาเลือกสาขาวิชา</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> บันทึก
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal สำหรับดูข้อมูลอาจารย์ที่ปรึกษา -->
    <?php if($viewProfessor): ?>
    <div class="modal fade show" id="viewAdvisorModal" tabindex="-1" aria-labelledby="viewAdvisorModalLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-eye mr-1"></i> ข้อมูลอาจารย์ที่ปรึกษา
                    </h5>
                    <a href="index.php?menu=6" class="close text-white" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <!-- ข้อมูลอาจารย์ -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-user mr-1"></i> ข้อมูลส่วนตัว
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>รหัสอาจารย์:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php echo $viewProfessor['Prof_id']; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>ชื่อ-นามสกุล:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php echo isset($viewProfessor['Title_name']) ? $viewProfessor['Title_name'] : ''; ?>
                                    <?php echo $viewProfessor['Prof_fname']; ?>
                                    <?php echo $viewProfessor['Prof_lname']; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>เบอร์โทรศัพท์:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php echo !empty($viewProfessor['Phone']) ? $viewProfessor['Phone'] : '-'; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>อีเมล:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php echo !empty($viewProfessor['Email']) ? $viewProfessor['Email'] : '-'; ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>สาขาวิชา:</strong>
                                </div>
                                <div class="col-md-8">
                                    <?php echo isset($viewProfessor['Maj_name']) ? $viewProfessor['Maj_name'] : '-'; ?>
                                </div>
                            </div>
                            <hr>
                        </div>
                    </div>

                    <!-- ข้อมูลนักศึกษาในที่ปรึกษา -->
                    <div class="card card-info card-outline mt-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-users mr-1"></i> นักศึกษาในที่ปรึกษา
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if($advisoryStudents && $advisoryStudents->rowCount() > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="15%">รหัสนักศึกษา</th>
                                            <th width="20%">ชื่อ</th>
                                            <th width="20%">นามสกุล</th>
                                            <th width="15%">เบอร์โทร</th>
                                            <th width="30%">สาขาวิชา</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($student = $advisoryStudents->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo $student['Stu_id']; ?></td>
                                            <td><?php echo $student['Stu_fname']; ?></td>
                                            <td><?php echo $student['Stu_lname']; ?></td>
                                            <td><?php echo isset($student['Stu_phone']) ? $student['Stu_phone'] : '-'; ?>
                                            </td>
                                            <td><?php echo isset($student['Maj_name']) ? $student['Maj_name'] : '-'; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i> ไม่พบข้อมูลนักศึกษาในที่ปรึกษา
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?menu=6" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> ปิด
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    <?php endif; ?>
</div>

<script>
// รอให้เอกสาร HTML โหลดเสร็จก่อน
$(document).ready(function() {
    // ตรวจสอบการกรอกรหัสอาจารย์
    $('#prof_id').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 5) {
            this.value = this.value.slice(0, 5);
        }
    });

    // ตรวจสอบการกรอกเบอร์โทร
    $('#phone, #edit_phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });

    // เพิ่มการตรวจสอบฟอร์มแบบ Bootstrap 4
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // ดึงฟอร์มที่ต้องการตรวจสอบ
            var forms = document.getElementsByClassName('needs-validation');

            // วนลูปเพื่อป้องกันการส่งฟอร์มและตรวจสอบ
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
});

// ฟังก์ชันสำหรับยืนยันการลบ
function confirmDelete(id) {
    if (confirm('คุณต้องการลบอาจารย์ที่ปรึกษารหัส ' + id + ' ใช่หรือไม่?')) {
        document.getElementById('deleteForm' + id).submit();
    }
}
</script>

<?php require_once 'includes/tablejs.php'; ?>