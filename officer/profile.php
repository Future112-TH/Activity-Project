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

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();
$controller = new Controller($db);

// ดึงข้อมูลผู้ใช้ตาม role
// ใช้ professor_id ถ้ามีใน session มิฉะนั้นใช้ user_id
$user_id = isset($_SESSION['professor_id']) ? $_SESSION['professor_id'] : $_SESSION['user_id'];
$user_role = isset($_SESSION['status']) ? $_SESSION['status'] : 'professor';

// บันทึกข้อมูลเพื่อตรวจสอบ
error_log("Profile page - User ID: " . $user_id . ", Role: " . $user_role);

// กำหนดตัวแปรเพื่อเก็บข้อมูลผู้ใช้
$user_data = null;

// ตรวจสอบประเภทผู้ใช้และดึงข้อมูล
if ($user_role == 'admin' || $user_role == 'professor') {
    // กรณีเป็นอาจารย์หรือผู้ดูแลระบบ
    $user_data = $controller->getProfessorById($user_id);
    
    // ดึงข้อมูลสถานะจากตาราง login
    $login_status = $controller->getUserStatus($_SESSION['user_id']);
    if ($login_status) {
        // ตรวจสอบและปรับสถานะให้เป็นเพียง admin หรือ professor
        $login_status = ($login_status == 'admin') ? 'admin' : 'professor';
        $user_data['LoginStatus'] = $login_status;
    } else {
        // ถ้าไม่พบในตาราง login ให้ใช้สถานะจาก session
        $user_role = ($user_role == 'admin') ? 'admin' : 'professor';
        $user_data['LoginStatus'] = $user_role;
    }
    
    // บันทึกผลลัพธ์เพื่อตรวจสอบ
    error_log("Professor data fetch result: " . ($user_data ? "Found" : "Not found"));
    
    // ถ้าพบข้อมูล ให้ดึงข้อมูลเพิ่มเติม
    if ($user_data) {
        // ดึงข้อมูลคำนำหน้า
        $title = $controller->getTitleById($user_data['Title_id']);
        if ($title) {
            $user_data['Title_name'] = $title['Title_name'];
        }
        
        // ดึงข้อมูลสาขา
        $major = $controller->getMajorById($user_data['Major_id']);
        if ($major) {
            $user_data['Maj_name'] = $major['Maj_name'];
        }
    }
}

// หากไม่พบในฐานข้อมูล แต่มีข้อมูลใน session
if (!$user_data && isset($_SESSION['fullname'])) {
    // แยกชื่อและนามสกุล
    $fullname_parts = explode(' ', $_SESSION['fullname']);
    $title = isset($fullname_parts[0]) ? $fullname_parts[0] : '';
    $firstname = isset($fullname_parts[1]) ? $fullname_parts[1] : '';
    $lastname = '';
    
    for ($i = 2; $i < count($fullname_parts); $i++) {
        $lastname .= $fullname_parts[$i] . ' ';
    }
    $lastname = trim($lastname);
    
    // สร้างข้อมูลผู้ใช้จาก session
    $loginStatus = isset($_SESSION['status']) ? $_SESSION['status'] : 'professor';
    // ตรวจสอบว่าสถานะเป็น admin หรือไม่ ถ้าไม่ใช่ให้เป็น professor
    $loginStatus = ($loginStatus == 'admin') ? 'admin' : 'professor';
    
    $user_data = [
        'Prof_id' => $user_id,
        'Title_name' => $title,
        'Prof_fname' => $firstname,
        'Prof_lname' => $lastname,
        'Phone' => isset($_SESSION['phone']) ? $_SESSION['phone'] : '',
        'Email' => isset($_SESSION['email']) ? $_SESSION['email'] : '',
        'Maj_name' => isset($_SESSION['major_name']) ? $_SESSION['major_name'] : '',
        'Major_id' => isset($_SESSION['major_id']) ? $_SESSION['major_id'] : '',
        'LoginStatus' => $loginStatus
    ];
    
    error_log("Created user_data from session: " . json_encode($user_data, JSON_UNESCAPED_UNICODE));
}

    // ดึงข้อมูลคำนำหน้าทั้งหมด
$titles = $controller->getTitles();

// ดึงข้อมูลสาขาทั้งหมด
$majors = $controller->getMajors();
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-circle mr-2"></i>จัดการข้อมูลส่วนตัว</h1>
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
                    <i class="fas fa-check-circle mr-2"></i><?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <!-- container -->
            <div class="row">
                <div class="col-md-12 mb-3">
                    <a class="btn btn-primary btn-md" 
                       id="edit_profile" 
                       data-toggle="modal" 
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
                            <?php if ($user_data): ?>
                            <div class="row">
                                <!-- รหัสอาจารย์ -->
                                <div class="col-sm-3">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">รหัสประจำตัวอาจารย์</label>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Prof_id']) ? $user_data['Prof_id'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;" 
                                        readonly>
                                </div>
                                <!-- คำนำหน้า -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">คำนำหน้า</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Title_name']) ? $user_data['Title_name'] : ''; ?>"
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
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Prof_fname']) ? $user_data['Prof_fname'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;" 
                                        readonly />
                                </div>
                                <!-- นามสกุล -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">นามสกุล</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Prof_lname']) ? $user_data['Prof_lname'] : ''; ?>"
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
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Phone']) ? $user_data['Phone'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;" 
                                        readonly />
                                </div>
                                
                                <!-- อีเมล -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">อีเมล</label>
                                </div>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Email']) ? $user_data['Email'] : ''; ?>"
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
                                    <input type="text" class="form-control" value="<?php echo isset($user_data['Maj_name']) ? $user_data['Maj_name'] : ''; ?>"
                                        style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;" 
                                        readonly />
                                </div>
                                <!-- สถานะ -->
                                <div class="col-sm-2">
                                    <label class="form-control text-right font-weight-bold"
                                        style="border: none; border-color: transparent">สถานะ</label>
                                </div>
                                <div class="col-sm-4">
                                <input type="text" class="form-control" id="status_display" 
                                    value="<?php 
                                        if (isset($user_data['LoginStatus'])) {
                                            switch($user_data['LoginStatus']) {
                                                case 'admin':
                                                    echo 'ผู้ดูแลระบบ';
                                                    break;
                                                default:
                                                    echo 'อาจารย์';
                                                    break;
                                            }
                                        } else if (isset($user_data['Status'])) {
                                            switch($user_data['Status']) {
                                                case 'admin':
                                                    echo 'ผู้ดูแลระบบ';
                                                    break;
                                                default:
                                                    echo 'อาจารย์';
                                                    break;
                                            }
                                        }
                                    ?>" 
                                    style="border: none; border-color: transparent; border-bottom: #999999 1px dotted;" 
                                    readonly>
                                <!-- ฟิลด์ซ่อนเพื่อส่งค่าสถานะไปยัง process_profile.php -->
                                <input type="hidden" name="status" 
                                    value="<?php 
                                        $status = 'professor'; // ค่าเริ่มต้น
                                        if (isset($user_data['LoginStatus']) && $user_data['LoginStatus'] == 'admin') {
                                            $status = 'admin';
                                        } else if (isset($user_data['Status']) && $user_data['Status'] == 'admin') {
                                            $status = 'admin';
                                        }
                                        echo $status;
                                    ?>">
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i> ไม่พบข้อมูลผู้ใช้
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

<!-- Modal แก้ไขข้อมูลส่วนตัว -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form id="editProfileForm" action="process/process_profile.php" method="post">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editProfileModalLabel"><i class="fas fa-user-edit mr-2"></i>แก้ไขข้อมูลส่วนตัว</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ($user_data): ?>
                    <!-- ซ่อนฟิลด์สำหรับส่งข้อมูลที่จำเป็น แต่ไม่แสดงบนหน้าจอ -->
                    <input type="hidden" id="user_id" name="user_id" value="<?php echo isset($user_data['Prof_id']) ? $user_data['Prof_id'] : $user_id; ?>">
                    <input type="hidden" id="prof_id" name="prof_id" value="<?php echo isset($user_data['Prof_id']) ? $user_data['Prof_id'] : $user_id; ?>">
                    <input type="hidden" id="user_role" name="user_role" value="<?php echo isset($user_data['LoginStatus']) ? $user_data['LoginStatus'] : $user_role; ?>">
                    <input type="hidden" id="status" name="status" value="<?php echo isset($user_data['LoginStatus']) ? $user_data['LoginStatus'] : 
                                                   (isset($user_data['Status']) ? $user_data['Status'] : 'professor'); ?>">
                    
                    <!-- แถวที่ 1: คำนำหน้า -->
                    <div class="form-group">
                        <label for="title">คำนำหน้า <span class="text-danger">*</span></label>
                        <select class="form-control" id="title" name="title" required>
                            <option value="">-- เลือกคำนำหน้า --</option>
                            <?php 
                            // ดึงข้อมูลคำนำหน้าจากฐานข้อมูล
                            if ($titles && $titles->rowCount() > 0) {
                                while ($title = $titles->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = '';
                                    if (isset($user_data['Title_id']) && $title['Title_id'] == $user_data['Title_id']) {
                                        $selected = 'selected';
                                    } 
                                    // ถ้าเป็นค่าจาก session และไม่มีในฐานข้อมูล
                                    elseif (isset($user_data['Title_name']) && $title['Title_name'] == $user_data['Title_name']) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="'.$title['Title_id'].'" '.$selected.'>'.$title['Title_name'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- แถวที่ 2: ชื่อและนามสกุล -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstname">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="firstname" name="firstname" 
                                       value="<?php echo isset($user_data['Prof_fname']) ? $user_data['Prof_fname'] : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastname">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="lastname" name="lastname" 
                                       value="<?php echo isset($user_data['Prof_lname']) ? $user_data['Prof_lname'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- แถวที่ 3: เบอร์โทรศัพท์และอีเมล -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phone">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?php echo isset($user_data['Phone']) ? $user_data['Phone'] : ''; ?>" 
                                       pattern="[0-9]{10}" maxlength="10" required>
                                <small class="form-text text-muted">กรุณากรอกเบอร์โทรศัพท์ 10 หลัก</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">อีเมล <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($user_data['Email']) ? $user_data['Email'] : ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <!-- แถวที่ 4: สาขา -->
                    <div class="form-group">
                        <label for="major">สาขา <span class="text-danger">*</span></label>
                        <select class="form-control" id="major" name="major" required>
                            <option value="">-- เลือกสาขา --</option>
                            <?php 
                            // ดึงข้อมูลสาขาจากฐานข้อมูล
                            if ($majors && $majors->rowCount() > 0) {
                                while ($major = $majors->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = '';
                                    if (isset($user_data['Major_id']) && $major['Maj_id'] == $user_data['Major_id']) {
                                        $selected = 'selected';
                                    }
                                    echo '<option value="'.$major['Maj_id'].'" '.$selected.'>'.$major['Maj_name'].'</option>';
                                }
                            }
                            ?>
                        </select>
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
                    <button type="submit" class="btn btn-primary">
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
        // ตรวจสอบและแสดงค่าที่จะส่งในฟอร์ม (ใช้สำหรับ debug)
        console.log('User ID:', $('#user_id').val());
        console.log('Prof ID:', $('#prof_id').val());
        console.log('Title:', $('#title').val());
        console.log('First Name:', $('#firstname').val());
        console.log('Last Name:', $('#lastname').val());
        console.log('Phone:', $('#phone').val());
        console.log('Email:', $('#email').val());
        console.log('Major:', $('#major').val());
        
        var major = $('#major').val();
        var title = $('#title').val();
        
        // ตรวจสอบว่าได้เลือกสาขาและคำนำหน้าหรือไม่
        if (!major || major === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'กรุณาเลือกสาขา',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }
        
        if (!title || title === '') {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'กรุณาเลือกคำนำหน้า',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }
        
        // เพิ่ม SweetAlert แจ้งเตือนว่ากำลังบันทึกข้อมูล
        Swal.fire({
            title: 'กำลังบันทึกข้อมูล',
            html: 'กรุณารอสักครู่...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
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