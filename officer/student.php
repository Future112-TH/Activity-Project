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
$students = $controller->getStudents();    // นักศึกษาทั้งหมด
$titles = $controller->getTitles();        // คำนำหน้าทั้งหมด
$majors = $controller->getMajors();        // สาขาทั้งหมด
$professors = $controller->getProfessors(); // อาจารย์ทั้งหมด
$plans = $controller->getStudentPlans();   // แผนการเรียนทั้งหมด
$curriculums = $controller->getCurriculum(); // หลักสูตรทั้งหมด

// ดึงข้อมูลนักศึกษาที่ต้องการแก้ไข (ถ้ามี)
$editStudent = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editStudent = $controller->getStudentById($_GET['edit']);
}

// ดึงข้อมูลนักศึกษาที่ต้องการดู (ถ้ามี)
$viewStudent = null;
$studentActivities = null;
if (isset($_GET['view']) && !empty($_GET['view'])) {
    $viewStudent = $controller->getStudentById($_GET['view']);
    if ($viewStudent) {
        $studentActivities = $controller->getStudentActivities($viewStudent['Stu_id']);
    }
}
?>

<div class="content-wrapper">
    <!-- ส่วนหัวของหน้า -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-graduate mr-2"></i>จัดการข้อมูลนักศึกษา</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ข้อมูลนักศึกษา</li>
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

            <!-- ตารางแสดงข้อมูลนักศึกษา -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        รายชื่อนักศึกษา
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success float-right mr-2" data-toggle="modal"
                            data-target="#importStudentModal">
                            <i class="fas fa-file-import mr-1"></i> นำเข้าข้อมูล
                        </button>
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                            data-target="#addStudentModal">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มนักศึกษา
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table1" class="table table-bordered table-striped">
                            <thead>
                                <tr class="bg-light">
                                    <th width="11%">รหัสนักศึกษา</th>
                                    <th width="5%">คำนำหน้า</th>
                                    <th width="13%">ชื่อ</th>
                                    <th width="13%">นามสกุล</th>
                                    <th width="13%">สาขาวิชา</th>
                                    <th width="15%">หลักสูตร</th>
                                    <th width="10%">แผนการเรียน</th>
                                    <th width="10%">อาจารย์ที่ปรึกษา</th>
                                    <th width="10%" class="text-center">การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if($students && $students->rowCount() > 0): ?>
                                <?php while($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td><?php echo $row['Stu_id']; ?></td>
                                    <td><?php echo $row['Title_name']; ?></td>
                                    <td><?php echo $row['Stu_fname']; ?></td>
                                    <td><?php echo $row['Stu_lname']; ?></td>
                                    <td><?php echo $row['Maj_name']; ?></td>
                                    <td>
                                        <?php 
                                        $curriculumInfo = $controller->getCurriculumById($row['Curri_id']);
                                        echo $curriculumInfo ? $curriculumInfo['Curri_t'] : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            <?php 
                                                echo $row['Abbre'] ?: 'ไม่ระบุ'; 
                                                echo "<!-- Plan_id: " . $row['Plan_id'] . " -->";
                                            ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['Prof_fname'] . ' ' . $row['Prof_lname']; ?></td>
                                    <td class="text-center">
                                        <a href="index.php?menu=7&view=<?php echo $row['Stu_id']; ?>"
                                            class="btn btn-info btn-sm" title="ดูข้อมูล">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="index.php?menu=7&edit=<?php echo $row['Stu_id']; ?>"
                                            class="btn btn-warning btn-sm" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="post" action="process/process_student.php" style="display:inline;"
                                            id="deleteForm<?php echo $row['Stu_id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $row['Stu_id']; ?>">
                                            <button type="button" class="btn btn-danger btn-sm"
                                                onclick="confirmDelete('<?php echo $row['Stu_id']; ?>')" title="ลบ">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center">ไม่พบข้อมูล</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal: เพิ่มนักศึกษา -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" aria-labelledby="addStudentLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="process/process_student.php" method="post" id="addStudentForm" class="needs-validation"
                novalidate>
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มนักศึกษา
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_id">รหัสนักศึกษา <span class="text-danger">*</span></label>
                                    <input type="text" name="student_id" id="student_id" class="form-control" required
                                        maxlength="13" placeholder="รหัสนักศึกษา 13 หลัก">
                                    <small class="form-text text-muted">ตัวอย่าง: 6501103071001</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title_id">คำนำหน้า <span class="text-danger">*</span></label>
                                    <select name="title_id" id="title_id" class="form-control" required>
                                        <option value="">เลือกคำนำหน้า</option>
                                        <?php if($titles && $titles->rowCount() > 0): ?>
                                        <?php while($title = $titles->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $title['Title_id']; ?>">
                                            <?php echo $title['Title_name']; ?></option>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" name="student_fname" id="student_fname" class="form-control"
                                        required placeholder="ชื่อ">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="student_lname" id="student_lname" class="form-control"
                                        required placeholder="นามสกุล">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_phone">เบอร์โทรศัพท์</label>
                                    <input type="text" name="student_phone" id="student_phone" class="form-control"
                                        maxlength="10" placeholder="เบอร์โทรศัพท์">
                                    <small class="form-text text-muted">ตัวอย่าง: 0812345678</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="student_email">อีเมล</label>
                                    <input type="email" name="student_email" id="student_email" class="form-control"
                                        placeholder="อีเมล">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="birthdate">วันเกิด</label>
                                    <input type="date" name="birthdate" id="birthdate" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="religion">ศาสนา</label>
                                    <input type="text" name="religion" id="religion" class="form-control"
                                        placeholder="ระบุศาสนา">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nationality">สัญชาติ</label>
                                    <input type="text" name="nationality" id="nationality" class="form-control"
                                        value="ไทย">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="curriculum_id">หลักสูตร <span class="text-danger">*</span></label>
                                    <select name="curriculum_id" id="curriculum_id" class="form-control" required>
                                        <option value="">เลือกหลักสูตร</option>
                                        <?php if($curriculums && $curriculums->rowCount() > 0): ?>
                                        <?php while($curriculum = $curriculums->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $curriculum['Curri_id']; ?>"
                                            data-major="<?php echo $curriculum['Maj_id']; ?>">
                                            <?php echo $curriculum['Curri_t']; ?></option>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="plan_id">แผนการเรียน <span class="text-danger">*</span></label>
                                    <select name="plan_id" id="plan_id" class="form-control" required>
                                        <option value="">เลือกแผนการเรียน</option>
                                        <?php if($plans && $plans->rowCount() > 0): ?>
                                        <?php while($plan = $plans->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $plan['Plan_id']; ?>">
                                            <?php echo $plan['Abbre']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="major_id">สาขาวิชา <span class="text-danger">*</span></label>
                                    <select name="major_id" id="major_id" class="form-control" required>
                                        <option value="">เลือกสาขา</option>
                                        <?php if($majors && $majors->rowCount() > 0): ?>
                                        <?php while($major = $majors->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $major['Maj_id']; ?>">
                                            <?php echo $major['Maj_name']; ?></option>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="prof_id">อาจารย์ที่ปรึกษา <span class="text-danger">*</span></label>
                                    <select name="prof_id" id="prof_id" class="form-control" required>
                                        <option value="">เลือกอาจารย์ที่ปรึกษา</option>
                                        <?php if($professors && $professors->rowCount() > 0): ?>
                                        <?php while($prof = $professors->fetch(PDO::FETCH_ASSOC)): ?>
                                        <option value="<?php echo $prof['Prof_id']; ?>"
                                            data-major="<?php echo $prof['Major_id']; ?>">
                                            <?php echo $prof['Title_name'] . ' ' . $prof['Prof_fname'] . ' ' . $prof['Prof_lname']; ?>
                                        </option>
                                        <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
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

    <!-- Modal: แก้ไขนักศึกษา -->
    <?php if($editStudent): ?>
    <div class="modal fade show" id="editStudentModal" tabindex="-1" aria-labelledby="editStudentLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl">
            <form action="process/process_student.php" method="post" id="editStudentForm" class="needs-validation"
                novalidate>
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="student_id" value="<?php echo $editStudent['Stu_id']; ?>">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขข้อมูลนักศึกษา
                        </h5>
                        <a href="index.php?menu=7" class="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_student_code">รหัสนักศึกษา</label>
                                    <input type="text" name="edit_student_code" id="edit_student_code"
                                        class="form-control bg-light" value="<?php echo $editStudent['Stu_id']; ?>"
                                        readonly>
                                    <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสนักศึกษาได้</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_title_id">คำนำหน้า <span class="text-danger">*</span></label>
                                    <select name="edit_title_id" id="edit_title_id" class="form-control" required>
                                        <option value="">เลือกคำนำหน้า</option>
                                        <?php 
                                            // รีเซ็ต pointer
                                            if ($titles instanceof PDOStatement) {
                                                $titles->execute();
                                            }
                                            if($titles && $titles->rowCount() > 0): 
                                                while($title = $titles->fetch(PDO::FETCH_ASSOC)): 
                                                    $selected = ($editStudent['Title_id'] == $title['Title_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $title['Title_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $title['Title_name']; ?></option>
                                        <?php 
                                                endwhile; 
                                            endif; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_student_fname">ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_student_fname" id="edit_student_fname"
                                        class="form-control" value="<?php echo $editStudent['Stu_fname']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_student_lname">นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" name="edit_student_lname" id="edit_student_lname"
                                        class="form-control" value="<?php echo $editStudent['Stu_lname']; ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_student_phone">เบอร์โทรศัพท์</label>
                                    <input type="text" name="edit_student_phone" id="edit_student_phone"
                                        class="form-control" value="<?php echo $editStudent['Stu_phone']; ?>"
                                        maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_student_email">อีเมล</label>
                                    <input type="email" name="edit_student_email" id="edit_student_email"
                                        class="form-control" value="<?php echo $editStudent['Stu_email']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_birthdate">วันเกิด</label>
                                    <input type="date" name="edit_birthdate" id="edit_birthdate" class="form-control"
                                        value="<?php echo $editStudent['Birthdate']; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_religion">ศาสนา</label>
                                    <input type="text" name="edit_religion" id="edit_religion" class="form-control"
                                        value="<?php echo $editStudent['Religion']; ?>" placeholder="ระบุศาสนา">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="edit_nationality">สัญชาติ</label>
                                    <input type="text" name="edit_nationality" id="edit_nationality"
                                        class="form-control" value="<?php echo $editStudent['Nationality']; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_curriculum_id">หลักสูตร <span class="text-danger">*</span></label>
                                    <select name="edit_curriculum_id" id="edit_curriculum_id" class="form-control"
                                        required>
                                        <option value="">เลือกหลักสูตร</option>
                                        <?php 
                                            // รีเซ็ต pointer
                                            if ($curriculums instanceof PDOStatement) {
                                                $curriculums->execute();
                                            }
                                            if($curriculums && $curriculums->rowCount() > 0): 
                                                while($curriculum = $curriculums->fetch(PDO::FETCH_ASSOC)):
                                                    $selected = ($editStudent['Curri_id'] == $curriculum['Curri_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $curriculum['Curri_id']; ?>" <?php echo $selected; ?>
                                            data-major="<?php echo $curriculum['Maj_id']; ?>">
                                            <?php echo $curriculum['Curri_t']; ?></option>
                                        <?php 
                                                endwhile; 
                                            endif; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_plan_id">แผนการเรียน <span class="text-danger">*</span></label>
                                    <select name="edit_plan_id" id="edit_plan_id" class="form-control" required>
                                        <option value="">เลือกแผนการเรียน</option>
                                        <?php 
                                            // รีเซ็ต pointer
                                            if ($plans instanceof PDOStatement) {
                                                $plans->execute();
                                            }
                                            if($plans && $plans->rowCount() > 0): 
                                                while($plan = $plans->fetch(PDO::FETCH_ASSOC)): 
                                                    $selected = ($editStudent['Plan_id'] == $plan['Plan_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $plan['Plan_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $plan['Abbre']; ?>
                                        </option>
                                        <?php 
                                                endwhile; 
                                            endif; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_major_id">สาขาวิชา <span class="text-danger">*</span></label>
                                    <select name="edit_major_id" id="edit_major_id" class="form-control" required>
                                        <option value="">เลือกสาขา</option>
                                        <?php 
                                            // รีเซ็ต pointer
                                            if ($majors instanceof PDOStatement) {
                                                $majors->execute();
                                            }
                                            if($majors && $majors->rowCount() > 0): 
                                                while($major = $majors->fetch(PDO::FETCH_ASSOC)): 
                                                    $selected = ($editStudent['Maj_id'] == $major['Maj_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $major['Maj_id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $major['Maj_name']; ?></option>
                                        <?php 
                                                endwhile; 
                                            endif; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_prof_id">อาจารย์ที่ปรึกษา <span
                                            class="text-danger">*</span></label>
                                    <select name="edit_prof_id" id="edit_prof_id" class="form-control" required>
                                        <option value="">เลือกอาจารย์ที่ปรึกษา</option>
                                        <?php 
                                            // รีเซ็ต pointer
                                            if ($professors instanceof PDOStatement) {
                                                $professors->execute();
                                            }
                                            if($professors && $professors->rowCount() > 0): 
                                                while($prof = $professors->fetch(PDO::FETCH_ASSOC)): 
                                                    $selected = ($editStudent['Prof_id'] == $prof['Prof_id']) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo $prof['Prof_id']; ?>" <?php echo $selected; ?>
                                            data-major="<?php echo $prof['Major_id']; ?>">
                                            <?php echo $prof['Title_name'] . ' ' . $prof['Prof_fname'] . ' ' . $prof['Prof_lname']; ?>
                                        </option>
                                        <?php 
                                                endwhile; 
                                            endif; 
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="index.php?menu=7" class="btn btn-secondary">
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

    <!-- Modal: นำเข้าข้อมูลนักศึกษา -->
    <div class="modal fade" id="importStudentModal" tabindex="-1" aria-labelledby="importStudentLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form action="process/process_student.php" method="post" id="importStudentForm"
                enctype="multipart/form-data">
                <input type="hidden" name="action" value="import">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-file-import mr-1"></i> นำเข้าข้อมูลนักศึกษา
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="import_file">เลือกไฟล์ Excel (.xlsx, .xls) <span
                                            class="text-danger">*</span></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="import_file" name="import_file"
                                            accept=".xlsx, .xls" required>
                                        <label class="custom-file-label" for="import_file">เลือกไฟล์...</label>
                                    </div>
                                    <small class="form-text text-muted">รองรับไฟล์นามสกุล .xlsx, .xls เท่านั้น</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="import_mode">โหมดการนำเข้า <span class="text-danger">*</span></label>
                                    <select name="import_mode" id="import_mode" class="form-control" required>
                                        <option value="both" selected>เพิ่มข้อมูลใหม่และอัปเดตข้อมูลเดิม</option>
                                        <option value="add">เพิ่มข้อมูลใหม่เท่านั้น</option>
                                        <option value="update">อัปเดตข้อมูลเดิมเท่านั้น</option>
                                    </select>
                                    <small
                                        class="form-text text-muted">เลือกวิธีการจัดการกับข้อมูลนักศึกษาที่มีอยู่แล้ว</small>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-primary mb-3">
                            <div class="card-header">
                                <h3 class="card-title">คำแนะนำการนำเข้าข้อมูล</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <p><strong>คอลัมน์ที่จำเป็น:</strong></p>
                                <ol>
                                    <li><strong>รหัสนักศึกษา</strong> - ต้องมี 13 หลัก</li>
                                    <li><strong>คำนำหน้า</strong> - ต้องตรงกับข้อมูลในระบบ เช่น นาย, นางสาว</li>
                                    <li><strong>ชื่อ</strong></li>
                                    <li><strong>นามสกุล</strong></li>
                                    <li><strong>สาขาวิชา</strong> - ต้องตรงกับชื่อสาขาในระบบ</li>
                                    <li><strong>แผนการเรียน</strong> - ต้องตรงกับชื่อแผนการเรียนหรือชื่อย่อในระบบ</li>
                                    <li><strong>อาจารย์ที่ปรึกษา</strong> - ระบุเป็นรหัสอาจารย์ หรือชื่อ-นามสกุลอาจารย์</li>
                                </ol>
                                <p><strong>คอลัมน์ที่เพิ่มเติมได้ (ไม่บังคับ):</strong></p>
                                <ul>
                                    <li><strong>เบอร์โทรศัพท์</strong> - ถ้าระบุต้องเป็นตัวเลข 10 หลัก</li>
                                    <li><strong>อีเมล</strong></li>
                                    <li><strong>วันเกิด</strong> - รูปแบบ yyyy-mm-dd หรือ dd/mm/yyyy</li>
                                    <li><strong>ศาสนา</strong></li>
                                    <li><strong>สัญชาติ</strong> - ค่าเริ่มต้นคือ "ไทย"</li>
                                    <li><strong>หลักสูตร</strong> - ต้องตรงกับชื่อหลักสูตรในระบบ</li>
                                    <li><strong>สถานะ</strong> - 1=ลาออก, 2=ปกติ, 3=จบการศึกษา, 4=พ้นสภาพ</li>
                                </ul>
                                <p class="text-info"><i class="fas fa-info-circle"></i> <strong>หมายเหตุ:</strong>
                                    แถวแรกของไฟล์ Excel ต้องเป็นชื่อคอลัมน์ที่ตรงกับรายการข้างต้น</p>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <div class="d-flex">
                                <div class="mr-2">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">ต้องการไฟล์แม่แบบ?</h5>
                                    <p>คุณสามารถดาวน์โหลด <a href="templates/student_template.xlsx"
                                            class="alert-link">ไฟล์ตัวอย่าง</a> เพื่อดูรูปแบบที่ถูกต้อง
                                        หรือใช้เป็นแม่แบบสำหรับการนำเข้าข้อมูล</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-file-import mr-1"></i> นำเข้าข้อมูล
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: แสดงรายละเอียดข้อผิดพลาดการนำเข้า -->
    <div class="modal fade" id="importErrorModal" tabindex="-1" aria-labelledby="importErrorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle mr-1"></i> รายละเอียดข้อผิดพลาดการนำเข้า
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle mr-1"></i> พบข้อผิดพลาดในการนำเข้าข้อมูล
                        กรุณาตรวจสอบและแก้ไขข้อมูลในไฟล์ Excel ของคุณ
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th width="10%">ลำดับ</th>
                                    <th width="90%">ข้อความผิดพลาด</th>
                                </tr>
                            </thead>
                            <tbody id="error-details-body">
                                <!-- ข้อมูลจะถูกเพิ่มโดย JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: ดูข้อมูลนักศึกษา -->
    <?php if($viewStudent): ?>
    <div class="modal fade show" id="viewStudentModal" tabindex="-1" aria-labelledby="viewStudentLabel"
        style="display: block; padding-right: 17px;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user mr-1"></i> ข้อมูลนักศึกษา
                    </h5>
                    <a href="index.php?menu=7" class="close text-white" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- ข้อมูลส่วนตัว -->
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">ข้อมูลส่วนตัว</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="30%" class="font-weight-bold">รหัสนักศึกษา:</td>
                                                    <td width="70%"><?php echo $viewStudent['Stu_id']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">ชื่อ-นามสกุล:</td>
                                                    <td><?php echo $viewStudent['Title_name'] . ' ' . $viewStudent['Stu_fname'] . ' ' . $viewStudent['Stu_lname']; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">เบอร์โทรศัพท์:</td>
                                                    <td><?php echo !empty($viewStudent['Stu_phone']) ? $viewStudent['Stu_phone'] : '-'; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">วันเกิด:</td>
                                                    <td><?php echo !empty($viewStudent['Birthdate']) ? date('d/m/Y', strtotime($viewStudent['Birthdate'])) : '-'; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">อาจารย์ที่ปรึกษา:</td>
                                                    <td>
                                                        <?php 
                                                            // ดึงข้อมูลอาจารย์ที่ปรึกษาเพิ่มเติมถ้ายังไม่มีในอาร์เรย์ $viewStudent
                                                            if (!isset($viewStudent['Prof_title']) && isset($viewStudent['Prof_id'])) {
                                                                $advisorInfo = $controller->getProfessorById($viewStudent['Prof_id']);
                                                                if ($advisorInfo) {
                                                                    echo $advisorInfo['Title_name'] . ' ' . $advisorInfo['Prof_fname'] . ' ' . $advisorInfo['Prof_lname'];
                                                                } else {
                                                                    echo '-';
                                                                }
                                                            } else {
                                                                echo (isset($viewStudent['Prof_title']) ? $viewStudent['Prof_title'] : '') . ' ' . 
                                                                    (isset($viewStudent['Prof_fname']) ? $viewStudent['Prof_fname'] : '') . ' ' . 
                                                                    (isset($viewStudent['Prof_lname']) ? $viewStudent['Prof_lname'] : '');
                                                            }
                                                        ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="30%" class="font-weight-bold">อีเมล:</td>
                                                    <td width="70%">
                                                        <?php echo !empty($viewStudent['Stu_email']) ? $viewStudent['Stu_email'] : '-'; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">ศาสนา/สัญชาติ:</td>
                                                    <td><?php echo $viewStudent['Religion'] . '/' . $viewStudent['Nationality']; ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">หลักสูตร:</td>
                                                    <td>
                                                        <?php 
                                                            // ดึงข้อมูลหลักสูตรเพิ่มเติม
                                                            $curriculumInfo = $controller->getCurriculumById($viewStudent['Curri_id']);
                                                            echo $curriculumInfo ? $curriculumInfo['Curri_t'] : '-';
                                                        ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">แผนการเรียน:</td>
                                                    <td><?php echo $viewStudent['Abbre']; ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">สาขาวิชา:</td>
                                                    <td><?php echo $viewStudent['Maj_name']; ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <!-- กิจกรรมที่เข้าร่วม -->
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">กิจกรรมที่เข้าร่วม</h3>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-striped mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" width="5%">ลำดับ</th>
                                                    <th width="35%">ชื่อกิจกรรม</th>
                                                    <th width="15%">ประเภท</th>
                                                    <th class="text-center" width="10%">ชั่วโมง</th>
                                                    <th width="20%">วันที่</th>
                                                    <th width="15%">สถานะ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if($studentActivities && $studentActivities->rowCount() > 0): 
                                                    $i = 1;
                                                    while($activity = $studentActivities->fetch(PDO::FETCH_ASSOC)):
                                                ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $i++; ?></td>
                                                    <td><?php echo $activity['Act_name']; ?></td>
                                                    <td><?php echo $activity['ActType_Name']; ?></td>
                                                    <td class="text-center"><?php echo $activity['Act_hour']; ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($activity['Act_start_date'])); ?>
                                                    </td>
                                                    <td><span class="badge badge-success">เข้าร่วมแล้ว</span>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endwhile;
                                                  else:
                                                ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">ไม่พบข้อมูลการเข้าร่วมกิจกรรม
                                                    </td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <!-- สรุปการเข้าร่วมกิจกรรม -->
                            <div class="card card-outline card-success">
                                <div class="card-header">
                                    <h3 class="card-title">สรุปการเข้าร่วมกิจกรรม</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span
                                                        class="info-box-text text-center text-muted">กิจกรรมวิชาการ</span>
                                                    <span class="info-box-number text-center text-muted mb-0">
                                                        <?php 
                                                            $academicActivities = 0;
                                                            $totalAcademic = 4; // จำนวนกิจกรรมที่ต้องเข้าร่วมตามแผน
                                                            if($studentActivities) {
                                                                // ตรวจสอบว่ามีการ query ไปแล้วหรือไม่
                                                                if($studentActivities instanceof PDOStatement) {
                                                                    $studentActivities->execute();
                                                                }
                                                                while($activity = $studentActivities->fetch(PDO::FETCH_ASSOC)) {
                                                                    if(isset($activity['ActType_Name']) && $activity['ActType_Name'] == 'วิชาการ') {
                                                                        $academicActivities++;
                                                                    }
                                                                }
                                                            }
                                                            echo $academicActivities . '/' . $totalAcademic;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span
                                                        class="info-box-text text-center text-muted">กิจกรรมทั่วไป</span>
                                                    <span class="info-box-number text-center text-muted mb-0">
                                                        <?php 
                                                            $generalActivities = 0;
                                                            $totalGeneral = 4; // จำนวนกิจกรรมที่ต้องเข้าร่วมตามแผน
                                                            if($studentActivities) {
                                                                // ตรวจสอบว่ามีการ query ไปแล้วหรือไม่
                                                                if($studentActivities instanceof PDOStatement) {
                                                                    $studentActivities->execute();
                                                                }
                                                                while($activity = $studentActivities->fetch(PDO::FETCH_ASSOC)) {
                                                                    if(isset($activity['ActType_Name']) && $activity['ActType_Name'] == 'ทั่วไป') {
                                                                        $generalActivities++;
                                                                    }
                                                                }
                                                            }
                                                            echo $generalActivities . '/' . $totalGeneral;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span
                                                        class="info-box-text text-center text-muted">ชั่วโมงวิชาการ</span>
                                                    <span class="info-box-number text-center text-muted mb-0">
                                                        <?php 
                                                            $academicHours = 0;
                                                            $totalAcademicHours = 12; // จำนวนชั่วโมงที่ต้องเข้าร่วมตามแผน
                                                            if($studentActivities) {
                                                                // ตรวจสอบว่ามีการ query ไปแล้วหรือไม่
                                                                if($studentActivities instanceof PDOStatement) {
                                                                    $studentActivities->execute();
                                                                }
                                                                while($activity = $studentActivities->fetch(PDO::FETCH_ASSOC)) {
                                                                    if(isset($activity['ActType_Name']) && $activity['ActType_Name'] == 'วิชาการ') {
                                                                        $academicHours += intval($activity['Act_hour']);
                                                                    }
                                                                }
                                                            }
                                                            echo $academicHours . '/' . $totalAcademicHours;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-light">
                                                <div class="info-box-content">
                                                    <span
                                                        class="info-box-text text-center text-muted">ชั่วโมงทั่วไป</span>
                                                    <span class="info-box-number text-center text-muted mb-0">
                                                        <?php 
                                                            $generalHours = 0;
                                                            $totalGeneralHours = 12; // จำนวนชั่วโมงที่ต้องเข้าร่วมตามแผน
                                                            if($studentActivities) {
                                                                // ตรวจสอบว่ามีการ query ไปแล้วหรือไม่
                                                                if($studentActivities instanceof PDOStatement) {
                                                                    $studentActivities->execute();
                                                                }
                                                                while($activity = $studentActivities->fetch(PDO::FETCH_ASSOC)) {
                                                                    if(isset($activity['ActType_Name']) && $activity['ActType_Name'] == 'ทั่วไป') {
                                                                        $generalHours += intval($activity['Act_hour']);
                                                                    }
                                                                }
                                                            }
                                                            echo $generalHours . '/' . $totalGeneralHours;
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php?menu=7" class="btn btn-secondary">
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
    console.log("Document is ready!"); // เพื่อตรวจสอบว่า JavaScript ทำงาน
    console.log("jQuery loaded: ", typeof jQuery !== 'undefined');
    console.log("bsCustomFileInput loaded: ", typeof bsCustomFileInput !== 'undefined');
    
    // ตรวจสอบว่า bsCustomFileInput ถูกโหลดหรือไม่
    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
        console.log("bsCustomFileInput initialized");
    } else {
        console.error("bsCustomFileInput library is not loaded");
    }

    // ตรวจสอบว่า element ถูกพบหรือไม่
    console.log("import_file element found: ", $('#import_file').length);
    console.log("custom-file-label element found: ", $('#import_file').next('.custom-file-label').length);

    // ตรวจสอบรหัสนักศึกษาเมื่อกรอก
    $('#student_id').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 13);
    });

    // ตรวจสอบเบอร์โทรศัพท์
    $('#student_phone, #edit_student_phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
    });

    // จัดการการแสดงชื่อไฟล์เมื่อมีการเลือกไฟล์
    $('#import_file').on('change', function() {
        const file = this.files[0];
        const fileName = file ? file.name : 'เลือกไฟล์...';
        const fileExt = fileName.split('.').pop().toLowerCase();
        
        // ตรวจสอบนามสกุลไฟล์
        if (file && (fileExt === 'xlsx' || fileExt === 'xls')) {
            // แสดงชื่อไฟล์ที่เลือก
            $(this).next('.custom-file-label').html(fileName);
        } else if (file) {
            // ถ้าไฟล์ไม่ถูกต้อง
            Swal.fire({
                icon: 'error',
                title: 'ไฟล์ไม่ถูกต้อง',
                text: 'โปรดเลือกไฟล์ Excel (.xlsx, .xls) เท่านั้น',
                confirmButtonText: 'เข้าใจแล้ว'
            });
            // รีเซ็ตการเลือกไฟล์
            $(this).val('');
            $(this).next('.custom-file-label').html('เลือกไฟล์...');
        }
    });

    // เพิ่มการตรวจสอบก่อนส่งฟอร์ม
    $('#importStudentForm').on('submit', function(e) {
        const fileInput = $('#import_file');
        
        if (!fileInput.val()) {
            e.preventDefault();
            
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาเลือกไฟล์',
                    text: 'โปรดเลือกไฟล์ Excel สำหรับนำเข้าข้อมูล',
                    confirmButtonText: 'เข้าใจแล้ว'
                });
            } else {
                alert('กรุณาเลือกไฟล์ Excel สำหรับนำเข้าข้อมูล');
            }
            
            return false;
        }
        
        // แสดง loading ระหว่างอัปโหลด
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'กำลังนำเข้าข้อมูล...',
                html: 'โปรดรอสักครู่',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
        
        return true;
    });

    // เพิ่มการตรวจสอบฟอร์มแบบ Bootstrap 4
    (function() {
        'use strict';
        var forms = document.getElementsByClassName('needs-validation');
        Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();

    // เมื่อเลือกหลักสูตร ให้ตั้งค่าสาขาอัตโนมัติ
    $('#curriculum_id').on('change', function() {
        const curriculumId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const majorId = selectedOption.data('major');

        if (majorId) {
            // ตั้งค่าสาขาอัตโนมัติตามหลักสูตรที่เลือก
            $('#major_id').val(majorId).trigger('change');
        }
    });

    // เมื่อเลือกหลักสูตรในฟอร์มแก้ไข
    $('#edit_curriculum_id').on('change', function() {
        const curriculumId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const majorId = selectedOption.data('major');

        if (majorId) {
            // ตั้งค่าสาขาอัตโนมัติตามหลักสูตรที่เลือก
            $('#edit_major_id').val(majorId).trigger('change');
        }
    });

    // เมื่อเลือกสาขา ให้กรองอาจารย์ตามสาขา
    $('#major_id').on('change', function() {
        const majorId = $(this).val();

        if (majorId) {
            // กรองอาจารย์ตามสาขา
            $('#prof_id option').each(function() {
                const profMajorId = $(this).data('major');
                if (profMajorId && profMajorId != majorId) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });

            // ล้างการเลือกอาจารย์ ถ้าไม่อยู่ในสาขาที่เลือก
            const currentProf = $('#prof_id').val();
            const currentProfOption = $('#prof_id option[value="' + currentProf + '"]');
            const currentProfMajor = currentProfOption.data('major');

            if (currentProfMajor && currentProfMajor != majorId) {
                $('#prof_id').val('');
            }
        } else {
            // แสดงอาจารย์ทั้งหมด
            $('#prof_id option').show();
        }
    });

    // เมื่อเลือกสาขาในฟอร์มแก้ไข
    $('#edit_major_id').on('change', function() {
        const majorId = $(this).val();

        if (majorId) {
            // กรองอาจารย์ตามสาขา
            $('#edit_prof_id option').each(function() {
                const profMajorId = $(this).data('major');
                if (profMajorId && profMajorId != majorId) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });

            // ล้างการเลือกอาจารย์ ถ้าไม่อยู่ในสาขาที่เลือก
            const currentProf = $('#edit_prof_id').val();
            const currentProfOption = $('#edit_prof_id option[value="' + currentProf + '"]');
            const currentProfMajor = currentProfOption.data('major');

            if (currentProfMajor && currentProfMajor != majorId) {
                $('#edit_prof_id').val('');
            }
        } else {
            // แสดงอาจารย์ทั้งหมด
            $('#edit_prof_id option').show();
        }
    });

    // เรียกใช้ฟังก์ชันดึงข้อมูลอาจารย์ตามสาขาเมื่อโหลดหน้า (กรณีแก้ไขนักศึกษา)
    if ($('#edit_major_id').length > 0) {
        $('#edit_major_id').trigger('change');
    }

    // แสดงข้อความผิดพลาดการนำเข้า (ถ้ามี)
    <?php if(isset($_SESSION['error_details']) && !empty($_SESSION['error_details'])): ?>
    
    // สร้างตารางแสดงข้อผิดพลาด
    let errorHtml = '';
    const errorDetails = <?php echo json_encode($_SESSION['error_details']); ?>;
    
    errorDetails.forEach((error, index) => {
        errorHtml += `<tr>
            <td class="text-center">${index + 1}</td>
            <td>${error}</td>
        </tr>`;
    });
    
    $('#error-details-body').html(errorHtml);
    $('#importErrorModal').modal('show');
    
    <?php unset($_SESSION['error_details']); ?>
    <?php endif; ?>
});

// ฟังก์ชันสำหรับการรีเซ็ตฟอร์มอัปโหลด
function resetImportForm() {
    $('#import_file').val('');
    $('#import_file').next('.custom-file-label').html('เลือกไฟล์...');
    $('#import_mode').val('both');
}

// เมื่อปิด modal ให้รีเซ็ตฟอร์ม
$('#importStudentModal').on('hidden.bs.modal', function () {
    resetImportForm();
});

// ฟังก์ชันสำหรับยืนยันการลบ
function confirmDelete(id) {
    if (confirm('คุณต้องการลบนักศึกษารหัส ' + id + ' ใช่หรือไม่?')) {
        document.getElementById('deleteForm' + id).submit();
    }
}

// เพิ่มฟังก์ชันสำหรับดาวน์โหลดเทมเพลต
function downloadTemplateFile() {
    window.location.href = 'templates/student_template.xlsx';
}
</script>

<?php require_once 'includes/tablejs.php'; ?>