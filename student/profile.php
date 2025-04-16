<?php

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // ถ้าไม่มีการล็อกอิน ให้เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
    header('Location: login.php');
    exit();
}

// สร้างการเชื่อมต่อกับฐานข้อมูล
include_once '../config/database.php';
include_once '../config/controller.php';

$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

// ใช้ student_id จาก session แทน user_id
$student_id = isset($_SESSION['student_id']) ? $_SESSION['student_id'] : $_SESSION['user_id'];
$user_role = $_SESSION['status'];

// ดึงข้อมูลนักศึกษาจากฐานข้อมูล
$user_data = null;
if ($user_role == 'student') {
    $user_data = $controller->getStudentById($student_id);
    
    // ถ้าไม่พบข้อมูล แต่มีข้อมูลใน session ให้สร้างข้อมูลจาก session
    if (!$user_data && isset($_SESSION['fullname'])) {
        // แยกชื่อและนามสกุล
        $fullname_parts = explode(' ', $_SESSION['fullname']);
        $title = $fullname_parts[0]; // นาย/นาง/นางสาว
        $firstname = isset($fullname_parts[1]) ? $fullname_parts[1] : '';
        $lastname = '';
        for ($i = 2; $i < count($fullname_parts); $i++) {
            $lastname .= $fullname_parts[$i] . ' ';
        }
        $lastname = trim($lastname);
        
        // สร้างข้อมูลผู้ใช้จาก session
        $user_data = [
            'Stu_id' => $student_id,
            'Stu_fname' => $firstname,
            'Stu_lname' => $lastname,
            'Title_name' => $title,
            'Stu_phone' => '', // เราไม่มีข้อมูลนี้ใน session
            'Stu_email' => '', // เราไม่มีข้อมูลนี้ใน session
            'Maj_id' => $_SESSION['major_id'],
            'Maj_name' => $_SESSION['major_name'],
            'Fac_name' => $_SESSION['faculty_name'],
            'Plan_id' => $_SESSION['plan_id'],
            'Plan_name' => $_SESSION['plan_name']
        ];
    }
} else {
    // กรณีเป็นอาจารย์หรือผู้ดูแลระบบ
    $user_data = $controller->getProfessorById($_SESSION['user_id']);
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user mr-2"></i>จัดการข้อมูลส่วนตัว</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ข้อมูลส่วนตัว</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- แสดงข้อความแจ้งเตือนถ้ามี -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <!-- container -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <a class="btn btn-outline-warning btn-md" id="edit_profile" data-toggle="modal"
                        data-target="#editProfileModal">
                        <i class="far fa-edit mr-1"></i>
                        แก้ไขข้อมูลส่วนตัว
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <!-- ข้อมูลส่วนตัว -->
                    <div class="card card-outline card-primary">
                        <div class="card-header bg-primary">
                            <h5 class="card-title text-white"><i class="fas fa-user mr-2"></i>ข้อมูลส่วนตัว</h5>
                        </div>
                        <div class="card-body">
                            <?php if($user_data): ?>
                            <div class="row">
                                <!-- รหัสประจำตัว -->
                                <div class="col-sm-3">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">
                                        <?php echo ($user_role == 'student') ? 'รหัสประจำตัวนักศึกษา' : 'รหัสประจำตัวอาจารย์'; ?>
                                    </label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control"
                                        value="<?php echo ($user_role == 'student') ? $student_id : $_SESSION['user_id']; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly>
                                </div>
                                <!-- คำนำหน้า -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">คำนำหน้า</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control"
                                        value="<?php echo isset($user_data['Title_name']) ? $user_data['Title_name'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                            </div>

                            <div class="row">
                                <!-- ชื่อ -->
                                <div class="col-sm-3">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">ชื่อ</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control"
                                        value="<?php echo ($user_role == 'student') ? $user_data['Stu_fname'] : (isset($user_data['Prof_fname']) ? $user_data['Prof_fname'] : ''); ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                                <!-- นามสกุล -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">นามสกุล</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control"
                                        value="<?php echo ($user_role == 'student') ? $user_data['Stu_lname'] : (isset($user_data['Prof_lname']) ? $user_data['Prof_lname'] : ''); ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                            </div>

                            <div class="row">
                                <!-- เบอร์โทร -->
                                <div class="col-sm-3">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">เบอร์โทร</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control"
                                        value="<?php echo ($user_role == 'student') ? (isset($user_data['Stu_phone']) ? $user_data['Stu_phone'] : '') : (isset($user_data['Phone']) ? $user_data['Phone'] : ''); ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>

                                <!-- อีเมล -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">อีเมล</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control"
                                        value="<?php echo ($user_role == 'student') ? (isset($user_data['Stu_email']) ? $user_data['Stu_email'] : '') : (isset($user_data['Email']) ? $user_data['Email'] : ''); ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                            </div>

                            <div class="row">
                                <!-- สาขา -->
                                <div class="col-sm-3">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">สาขา</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control"
                                        value="<?php echo isset($user_data['Maj_name']) ? $user_data['Maj_name'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                                <!-- สถานะ -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">สถานะ</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="<?php 
                                            if ($user_role == 'admin') {
                                                echo 'ผู้ดูแลระบบ';
                                            } elseif ($user_role == 'teacher') {
                                                echo 'อาจารย์';
                                            } elseif ($user_role == 'advisor') {
                                                echo 'อาจารย์ที่ปรึกษา';
                                            } elseif ($user_role == 'student') {
                                                echo 'นักศึกษา';
                                            } else {
                                                echo $user_role;
                                            }
                                        ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;"
                                        readonly />
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i> ไม่พบข้อมูลผู้ใช้ในฐานข้อมูล
                                แต่มีข้อมูลจาก session
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- container -->
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

<!-- Modal ฟอร์มแก้ไขข้อมูล -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form id="editProfileForm" action="process/process_update.php" method="post">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="editProfileModalLabel"><i
                            class="fas fa-user-edit mr-2"></i>แก้ไขข้อมูลส่วนตัว</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if($user_data): ?>
                    <input type="hidden" name="user_role" value="<?php echo $user_role; ?>">
                    <input type="hidden" name="user_id"
                        value="<?php echo ($user_role == 'student') ? $student_id : $_SESSION['user_id']; ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">คำนำหน้า <span class="text-danger">*</span></label>
                                <select class="form-control" id="title" name="title" required>
                                    <?php 
                                    // ดึงข้อมูลคำนำหน้าจากฐานข้อมูล
                                    $titles = $controller->getTitles();
                                    if ($titles && $titles->rowCount() > 0) {
                                        while ($title = $titles->fetch(PDO::FETCH_ASSOC)) {
                                            $selected = '';
                                            // ถ้าเป็นนักศึกษา ตรวจสอบกับ Title_id
                                            if (isset($user_data['Title_id']) && $title['Title_id'] == $user_data['Title_id']) {
                                                $selected = 'selected';
                                            } 
                                            // ถ้าเป็นค่าจาก session และไม่มีในฐานข้อมูล
                                            elseif (isset($user_data['Title_name']) && $title['Title_name'] == $user_data['Title_name']) {
                                                $selected = 'selected';
                                            }
                                            echo '<option value="'.$title['Title_id'].'" '.$selected.'>'.$title['Title_name'].'</option>';
                                        }
                                    } else {
                                        // ถ้าไม่สามารถดึงจากฐานข้อมูลได้ ให้แสดงค่าทั่วไป
                                        ?>
                                    <option value="1"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'นาย') ? 'selected' : ''; ?>>
                                        นาย</option>
                                    <option value="2"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'นาง') ? 'selected' : ''; ?>>
                                        นาง</option>
                                    <option value="3"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'นางสาว') ? 'selected' : ''; ?>>
                                        นางสาว</option>
                                    <option value="4"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'ดร.') ? 'selected' : ''; ?>>
                                        ดร.</option>
                                    <option value="5"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'ผศ.') ? 'selected' : ''; ?>>
                                        ผศ.</option>
                                    <option value="6"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'ผศ.ดร.') ? 'selected' : ''; ?>>
                                        ผศ.ดร.</option>
                                    <option value="7"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'รศ.') ? 'selected' : ''; ?>>
                                        รศ.</option>
                                    <option value="8"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'รศ.ดร.') ? 'selected' : ''; ?>>
                                        รศ.ดร.</option>
                                    <option value="9"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'ศ.') ? 'selected' : ''; ?>>
                                        ศ.</option>
                                    <option value="10"
                                        <?php echo (isset($user_data['Title_name']) && $user_data['Title_name'] == 'ศ.ดร.') ? 'selected' : ''; ?>>
                                        ศ.ดร.</option>
                                    <?php
            }
            ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>รหัสประจำตัว</label>
                                <input type="text" class="form-control bg-light"
                                    value="<?php echo ($user_role == 'student') ? $student_id : $_SESSION['user_id']; ?>"
                                    readonly>
                                <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสประจำตัวได้</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstname" name="firstname"
                                    value="<?php echo ($user_role == 'student') ? $user_data['Stu_fname'] : (isset($user_data['Prof_fname']) ? $user_data['Prof_fname'] : ''); ?>"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastname" name="lastname"
                                    value="<?php echo ($user_role == 'student') ? $user_data['Stu_lname'] : (isset($user_data['Prof_lname']) ? $user_data['Prof_lname'] : ''); ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="<?php echo ($user_role == 'student') ? (isset($user_data['Stu_phone']) ? $user_data['Stu_phone'] : '') : (isset($user_data['Phone']) ? $user_data['Phone'] : ''); ?>"
                                    pattern="[0-9]{10}" maxlength="10" required>
                                <small class="form-text text-muted">กรุณากรอกเบอร์โทรศัพท์ 10 หลัก</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo ($user_role == 'student') ? (isset($user_data['Stu_email']) ? $user_data['Stu_email'] : '') : (isset($user_data['Email']) ? $user_data['Email'] : ''); ?>"
                                    required>
                            </div>
                        </div>
                    </div>

                    <!-- แสดงสาขาและสถานะเฉพาะแบบอ่านอย่างเดียว -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>สาขา</label>
                                <input type="text" class="form-control bg-light"
                                    value="<?php echo isset($user_data['Maj_name']) ? $user_data['Maj_name'] : $_SESSION['major_name']; ?>"
                                    readonly>
                                <input type="hidden" name="major"
                                    value="<?php echo isset($user_data['Maj_id']) ? $user_data['Maj_id'] : $_SESSION['major_id']; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>สถานะ</label>
                                <input type="text" class="form-control bg-light" value="<?php 
                                        if ($user_role == 'admin') {
                                            echo 'ผู้ดูแลระบบ';
                                        } elseif ($user_role == 'teacher') {
                                            echo 'อาจารย์';
                                        } elseif ($user_role == 'advisor') {
                                            echo 'อาจารย์ที่ปรึกษา';
                                        } elseif ($user_role == 'student') {
                                            echo 'นักศึกษา';
                                        } else {
                                            echo $user_role;
                                        }
                                    ?>" readonly>
                                <input type="hidden" name="status" value="<?php echo $user_role; ?>">
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-2"></i> ไม่พบข้อมูลผู้ใช้
                    </div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i>ยกเลิก
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i>บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    // ตรวจสอบฟอร์มข้อมูลส่วนตัว
    $('#editProfileForm').on('submit', function(e) {
        // สามารถเพิ่มการตรวจสอบอื่นๆ ได้ในอนาคต
        return true;
    });

    // ตรวจสอบรูปแบบเบอร์โทรศัพท์
    $('#phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
});
</script>