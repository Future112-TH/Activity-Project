<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลคณะทั้งหมด
$result = $controller->getFaculties();
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-university mr-2"></i>คณะหรือหน่วยงาน</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">คณะหรือหน่วยงาน</li>
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
                    <!-- ตารางข้อมูลคณะ -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการคณะหรือหน่วยงาน
                            </h3>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                                data-target="#addFacultyModal">
                                <i class="fas fa-plus-circle mr-1"></i> เพิ่มคณะหรือหน่วยงาน
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="15%">รหัสคณะ</th>
                                            <th width="70%">ชื่อคณะหรือหน่วยงาน</th>
                                            <th width="15%">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($result && $result->rowCount() > 0) {
                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['Fac_id']; ?></td>
                                            <td><?php echo $row['Fac_name']; ?></td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm"
                                                    onclick="editFaculty('<?php echo htmlspecialchars($row['Fac_id']); ?>', '<?php echo htmlspecialchars($row['Fac_name']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="process/process_faculty.php?action=delete&id=<?php echo $row['Fac_id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('คุณต้องการลบคณะรหัส <?php echo $row['Fac_id']; ?> ใช่หรือไม่?');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                            ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">ไม่พบข้อมูล</td>
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

    <!-- Modal: เพิ่มคณะ -->
    <div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_faculty.php" method="post" id="addFacultyForm">
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มคณะหรือหน่วยงาน
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="faculty_id">รหัสคณะ <span class="text-danger">*</span></label>
                            <input type="text" name="faculty_id" id="faculty_id" class="form-control" required>
                            <small class="form-text text-muted">รหัสต้องเป็นตัวเลข 3 หลัก เช่น 001, 002, ...</small>
                        </div>
                        <div class="form-group">
                            <label for="faculty_name">ชื่อคณะหรือหน่วยงาน <span class="text-danger">*</span></label>
                            <input type="text" name="faculty_name" id="faculty_name" class="form-control" required>
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

    <!-- Modal: แก้ไขคณะ -->
    <div class="modal fade" id="editFacultyModal" tabindex="-1" aria-labelledby="editFacultyLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_faculty.php" method="post" id="editFacultyForm">
                <input type="hidden" name="action" value="update">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขคณะหรือหน่วยงาน
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_faculty_id">รหัสคณะ</label>
                            <input type="text" name="edit_faculty_id" id="edit_faculty_id" class="form-control"
                                readonly>
                            <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสคณะได้</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_faculty_name">ชื่อคณะหรือหน่วยงาน <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="edit_faculty_name" id="edit_faculty_name" class="form-control"
                                required>
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
function editFaculty(id, name) {
    // กำหนดค่าให้กับฟิลด์
    document.getElementById('edit_faculty_id').value = id;
    document.getElementById('edit_faculty_name').value = name;
    
    // เปิด modal
    $('#editFacultyModal').modal('show');
}

// เพิ่มการตรวจสอบฟอร์มก่อนส่ง
$('#editFacultyForm').on('submit', function(e) {
    // ตรวจสอบว่ามีค่าใน edit_faculty_id หรือไม่
    const facultyId = $('#edit_faculty_id').val();
    
    if (!facultyId) {
        e.preventDefault();
        alert('ไม่พบรหัสคณะ กรุณาลองใหม่อีกครั้ง');
        return false;
    }
});
</script>

<?php require_once 'includes/tablejs.php' ?>