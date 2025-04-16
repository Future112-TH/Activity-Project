<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลประเภทกิจกรรมทั้งหมด
$result = $controller->getActivityTypes();
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-tags mr-2"></i>ประเภทกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ประเภทกิจกรรม</li>
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
                    <!-- ตารางข้อมูลประเภทกิจกรรม -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการประเภทกิจกรรม
                            </h3>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                                data-target="#addActivityTypeModal">
                                <i class="fas fa-plus-circle mr-1"></i> เพิ่มประเภทกิจกรรม
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="15%">รหัสประเภท</th>
                                            <th width="70%">ชื่อประเภทกิจกรรม</th>
                                            <th width="15%">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($result && $result->rowCount() > 0) {
                                            while($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['ActType_id']; ?></td>
                                            <td><?php echo $row['ActType_Name']; ?></td>
                                            <td>
                                                <a href="#" class="btn btn-warning btn-sm"
                                                    onclick="editActivityType('<?php echo htmlspecialchars($row['ActType_id']); ?>', '<?php echo htmlspecialchars($row['ActType_Name']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="process/process_activity_type.php?action=delete&id=<?php echo $row['ActType_id']; ?>"
                                                    class="btn btn-danger btn-sm"
                                                    onclick="return confirm('คุณต้องการลบประเภทกิจกรรมรหัส <?php echo $row['ActType_id']; ?> ใช่หรือไม่?');">
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

    <!-- Modal: เพิ่มประเภทกิจกรรม -->
    <div class="modal fade" id="addActivityTypeModal" tabindex="-1" aria-labelledby="addActivityTypeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_activity_type.php" method="post" id="addActivityTypeForm">
                <input type="hidden" name="action" value="add">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title">
                            <i class="fas fa-plus-circle mr-1"></i> เพิ่มประเภทกิจกรรม
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="activity_type_id">รหัสประเภทกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" name="activity_type_id" id="activity_type_id" class="form-control"
                                required>
                            <small class="form-text text-muted">กรอกรหัสประเภทกิจกรรมเป็นตัวเลข เช่น 1, 2, 3,
                                ...</small>
                        </div>
                        <div class="form-group">
                            <label for="activity_type_name">ชื่อประเภทกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" name="activity_type_name" id="activity_type_name" class="form-control"
                                required>
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

    <!-- Modal: แก้ไขประเภทกิจกรรม -->
    <div class="modal fade" id="editActivityTypeModal" tabindex="-1" aria-labelledby="editActivityTypeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form action="process/process_activity_type.php" method="post"
                id="editActivityTypeForm">
                <input type="hidden" name="action" value="update">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-1"></i> แก้ไขประเภทกิจกรรม
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="edit_activity_type_id">รหัสประเภทกิจกรรม</label>
                            <input type="text" name="edit_activity_type_id" id="edit_activity_type_id"
                                class="form-control" readonly>
                            <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสประเภทได้</small>
                        </div>
                        <div class="form-group">
                            <label for="edit_activity_type_name">ชื่อประเภทกิจกรรม <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="edit_activity_type_name" id="edit_activity_type_name"
                                class="form-control" required>
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
function editActivityType(id, name) {
    // กำหนดค่าให้กับฟิลด์
    document.getElementById('edit_activity_type_id').value = id;
    document.getElementById('edit_activity_type_name').value = name;
    
    // เปิด modal
    $('#editActivityTypeModal').modal('show');
}

$(document).ready(function() {
    // เพิ่มการตรวจสอบฟอร์มก่อนส่ง
    $('#editActivityTypeForm').on('submit', function(e) {
        // ตรวจสอบว่ามีค่าใน edit_activity_type_id หรือไม่
        const activityTypeId = $('#edit_activity_type_id').val();
        
        if (!activityTypeId) {
            e.preventDefault();
            alert('ไม่พบรหัสประเภทกิจกรรม กรุณาลองใหม่อีกครั้ง');
            return false;
        }
    });

    // ตรวจสอบรหัสประเภทกิจกรรมเมื่อกรอก
    $('#activity_type_id').on('input', function() {
        // อนุญาตให้ใช้เฉพาะตัวเลขเท่านั้น
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>

<?php require_once 'includes/tablejs.php' ?>