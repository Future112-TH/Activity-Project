<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลคณะทั้งหมดสำหรับแสดงใน dropdown
$faculties = $controller->getFaculties();

// ดึงข้อมูลสาขาวิชาทั้งหมด
$majors = $controller->getMajors();
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-graduation-cap mr-2"></i>สาขาวิชา</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">สาขาวิชา</li>
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
                    <!-- ตารางข้อมูลสาขา -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการสาขาวิชา
                            </h3>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#addMajorModal">
                                <i class="fas fa-plus-circle mr-1"></i> เพิ่มสาขาวิชา
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="15%">รหัสสาขา</th>
                                            <th width="40%">ชื่อสาขาวิชา</th>
                                            <th width="30%">คณะ</th>
                                            <th width="15%">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($majors && $majors->rowCount() > 0) {
                                            while($row = $majors->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['Maj_id']; ?></td>
                                            <td><?php echo $row['Maj_name']; ?></td>
                                            <td><?php echo $row['Fac_name']; ?></td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm"
                                                   onclick="editMajor('<?php echo htmlspecialchars($row['Maj_id']); ?>', '<?php echo htmlspecialchars($row['Maj_name']); ?>', '<?php echo htmlspecialchars($row['Fac_id']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="process/process_major.php?action=delete&id=<?php echo $row['Maj_id']; ?>"
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('คุณต้องการลบสาขาวิชารหัส <?php echo $row['Maj_id']; ?> ใช่หรือไม่?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="4" class="text-center">ไม่พบข้อมูล</td>
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

    <!-- Modal: เพิ่มสาขาวิชา -->
    <div class="modal fade" id="addMajorModal" tabindex="-1" aria-labelledby="addMajorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_major.php" method="post" id="addMajorForm">
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มสาขาวิชา
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="major_id">รหัสสาขาวิชา <span class="text-danger">*</span></label>
                            <input type="text" name="major_id" id="major_id" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="major_name">ชื่อสาขาวิชา <span class="text-danger">*</span></label>
                            <input type="text" name="major_name" id="major_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="faculty_id">คณะ <span class="text-danger">*</span></label>
                            <select name="faculty_id" id="faculty_id" class="form-control" required>
                                <option value="">-- เลือกคณะ --</option>
                                <?php 
                                if($faculties && $faculties->rowCount() > 0) {
                                    while($faculty = $faculties->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="'.$faculty['Fac_id'].'">'.$faculty['Fac_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
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

    <!-- Modal: แก้ไขสาขาวิชา -->
    <div class="modal fade" id="editMajorModal" tabindex="-1" aria-labelledby="editMajorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_major.php" method="post" id="editMajorForm">
                <input type="hidden" name="action" value="update">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขสาขาวิชา
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_major_id">รหัสสาขาวิชา</label>
                            <input type="text" name="edit_major_id" id="edit_major_id" class="form-control" readonly>
                            <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสสาขาได้</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_major_name">ชื่อสาขาวิชา <span class="text-danger">*</span></label>
                            <input type="text" name="edit_major_name" id="edit_major_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_faculty_id">คณะ <span class="text-danger">*</span></label>
                            <select name="edit_faculty_id" id="edit_faculty_id" class="form-control" required>
                                <option value="">-- เลือกคณะ --</option>
                                <?php 
                                // คืนค่าตัวชี้ตำแหน่งของผลลัพธ์ไปที่ตำแหน่งแรก
                                if($faculties) {
                                    $faculties->execute();
                                    while($faculty = $faculties->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<option value="'.$faculty['Fac_id'].'">'.$faculty['Fac_name'].'</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> ยกเลิก
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save mr-1"></i> บันทึกการแก้ไข
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMajor(id, name, facId) {
    // กำหนดค่าให้กับฟิลด์ในฟอร์มแก้ไข
    document.getElementById('edit_major_id').value = id;
    document.getElementById('edit_major_name').value = name;
    document.getElementById('edit_faculty_id').value = facId;
    
    // เปิด modal
    $('#editMajorModal').modal('show');
}

// เพิ่มการตรวจสอบฟอร์มก่อนส่ง
$('#editMajorForm').on('submit', function(e) {
    // ตรวจสอบว่ามีค่าใน edit_major_id หรือไม่
    const majorId = $('#edit_major_id').val();
    
    if (!majorId) {
        e.preventDefault();
        alert('ไม่พบรหัสสาขาวิชา กรุณาลองใหม่อีกครั้ง');
        return false;
    }
});
</script>

<?php require_once 'includes/tablejs.php' ?>