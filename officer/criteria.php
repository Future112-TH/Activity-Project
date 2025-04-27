<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลหลักเกณฑ์ทั้งหมด
$criteria = $controller->getCriteria();

// ดึงข้อมูลหลักสูตรทั้งหมด
$curriculum = $controller->getCurriculum();

// ดึงข้อมูลแผนการศึกษาทั้งหมด
$plans = $controller->getStudentPlans();

// ดึงข้อมูลประเภทกิจกรรม
$activityTypes = $controller->getActivityTypes();

// ดึงข้อมูลเกณฑ์ที่ต้องการแก้ไข (ถ้ามี)
$editCriteria = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $editCriteria = $controller->getCriteriaById($_GET['edit']);
}
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-clipboard-check mr-2"></i>หลักเกณฑ์การเข้าร่วมกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">หลักเกณฑ์การเข้าร่วมกิจกรรม</li>
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
                    <!-- ตารางข้อมูลหลักเกณฑ์ -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการหลักเกณฑ์
                            </h3>
                            <button type="button" class="btn btn-primary float-right" data-toggle="modal"
                                data-target="#addCriteriaModal">
                                <i class="fas fa-plus-circle mr-1"></i> เพิ่มหลักเกณฑ์
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th style="width: 8%">รหัสเกณฑ์</th>
                                            <th style="width: 25%">ชื่อเกณฑ์</th>
                                            <th style="width: 20%">หลักสูตร</th>
                                            <th style="width: 15%">แผนการศึกษา</th>
                                            <th style="width: 12%">ประเภทกิจกรรม</th>
                                            <th style="width: 7%">จำนวนชั่วโมง</th>
                                            <th style="width: 7%">จำนวนกิจกรรม</th>
                                            <th style="width: 6%">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($criteria && $criteria->rowCount() > 0) {
                                            while($row = $criteria->fetch(PDO::FETCH_ASSOC)) {
                                        ?>
                                        <tr>
                                            <td><?php echo $row['Crit_id']; ?></td>
                                            <td><?php echo $row['Crit_name']; ?></td>
                                            <td><?php echo $row['Curri_tname']; ?></td>
                                            <td><?php echo $row['Abbre']; ?></td>
                                            <td><?php echo $row['ActType_Name']; ?></td>
                                            <td class="text-center"><?php echo $row['Act_hour']; ?></td>
                                            <td class="text-center"><?php echo $row['Act_amount']; ?></td>
                                            <td class="text-center">
                                                <div class="btn-group">
                                                    <a href="index.php?menu=8&edit=<?php echo $row['Crit_id']; ?>"
                                                        class="btn btn-warning btn-sm" title="แก้ไข">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="process/process_criteria.php?action=delete&id=<?php echo $row['Crit_id']; ?>"
                                                        class="btn btn-danger btn-sm" title="ลบ"
                                                        onclick="return confirm('คุณต้องการลบหลักเกณฑ์รหัส <?php echo $row['Crit_id']; ?> ใช่หรือไม่?')">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php 
                                            }
                                        } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center">ไม่พบข้อมูล</td>
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
</div>

<!-- Modal: เพิ่มหลักเกณฑ์ -->
<div class="modal fade" id="addCriteriaModal" tabindex="-1" role="dialog" aria-labelledby="addCriteriaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="addCriteriaModalLabel"><i
                        class="fas fa-plus-circle mr-2"></i>เพิ่มหลักเกณฑ์ใหม่</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCriteriaForm" action="process/process_criteria.php" method="post">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="crit_name">ชื่อหลักเกณฑ์ <span class="text-danger">*</span></label>
                        <input type="text" name="crit_name" id="crit_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="curri_id">หลักสูตร <span class="text-danger">*</span></label>
                        <select name="curri_id" id="curri_id" class="form-control" required>
                            <option value="">-- เลือกหลักสูตร --</option>
                            <?php
                            if($curriculum && $curriculum->rowCount() > 0) {
                                while($curri = $curriculum->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $curri['Curri_id'] . '">' . $curri['Curri_tname'] . ' (' . $curri['Curri_t'] . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="plan_id">แผนการศึกษา <span class="text-danger">*</span></label>
                        <select name="plan_id" id="plan_id" class="form-control" required>
                            <option value="">-- เลือกแผนการศึกษา --</option>
                            <?php
                            if($plans && $plans->rowCount() > 0) {
                                while($plan = $plans->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $plan['Plan_id'] . '">' . $plan['Abbre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="act_type_id">ประเภทกิจกรรม <span class="text-danger">*</span></label>
                        <select name="act_type_id" id="act_type_id" class="form-control" required>
                            <option value="">-- เลือกประเภทกิจกรรม --</option>
                            <?php
                            if($activityTypes && $activityTypes->rowCount() > 0) {
                                while($actType = $activityTypes->fetch(PDO::FETCH_ASSOC)) {
                                    echo '<option value="' . $actType['ActType_id'] . '">' . $actType['ActType_Name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="act_hour">จำนวนชั่วโมง <span class="text-danger">*</span></label>
                        <input type="number" name="act_hour" id="act_hour" class="form-control" min="1" value="1"
                            required>
                        <small class="form-text text-muted">จำนวนชั่วโมงที่ต้องเข้าร่วมตามเกณฑ์</small>
                    </div>
                    <div class="form-group">
                        <label for="act_amount">จำนวนกิจกรรม <span class="text-danger">*</span></label>
                        <input type="number" name="act_amount" id="act_amount" class="form-control" min="1" value="1"
                            required>
                        <small class="form-text text-muted">จำนวนกิจกรรมที่ต้องเข้าร่วมตามเกณฑ์</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: แก้ไขหลักเกณฑ์ -->
<?php if($editCriteria): ?>
<div class="modal fade" id="editCriteriaModal" tabindex="-1" role="dialog" aria-labelledby="editCriteriaModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="editCriteriaModalLabel"><i class="fas fa-edit mr-2"></i>แก้ไขหลักเกณฑ์</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCriteriaForm" action="process/process_criteria.php" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="edit_crit_id" value="<?php echo $editCriteria['Crit_id']; ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label>รหัสหลักเกณฑ์</label>
                        <p class="form-control-static font-weight-bold"><?php echo $editCriteria['Crit_id']; ?></p>
                        <small class="form-text text-muted">ไม่สามารถแก้ไขรหัสหลักเกณฑ์ได้</small>
                    </div>
                    <div class="form-group">
                        <label for="edit_crit_name">ชื่อหลักเกณฑ์ <span class="text-danger">*</span></label>
                        <input type="text" name="edit_crit_name" id="edit_crit_name" class="form-control"
                            value="<?php echo $editCriteria['Crit_name']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_curri_id">หลักสูตร <span class="text-danger">*</span></label>
                        <select name="edit_curri_id" id="edit_curri_id" class="form-control" required>
                            <option value="">-- เลือกหลักสูตร --</option>
                            <?php
                            if($curriculum && $curriculum->rowCount() > 0) {
                                // รีเซ็ต pointer
                                $curriculum->execute();
                                
                                while($curri = $curriculum->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($editCriteria['Curri_id'] == $curri['Curri_id']) ? 'selected' : '';
                                    echo '<option value="' . $curri['Curri_id'] . '" ' . $selected . '>' . 
                                         $curri['Curri_tname'] . ' (' . $curri['Curri_t'] . ')</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_plan_id">แผนการศึกษา <span class="text-danger">*</span></label>
                        <select name="edit_plan_id" id="edit_plan_id" class="form-control" required>
                            <option value="">-- เลือกแผนการศึกษา --</option>
                            <?php
                            if($plans && $plans->rowCount() > 0) {
                                // รีเซ็ต pointer
                                $plans->execute();
                                
                                while($plan = $plans->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($editCriteria['Plan_id'] == $plan['Plan_id']) ? 'selected' : '';
                                    echo '<option value="' . $plan['Plan_id'] . '" ' . $selected . '>' . $plan['Abbre'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_act_type_id">ประเภทกิจกรรม <span class="text-danger">*</span></label>
                        <select name="edit_act_type_id" id="edit_act_type_id" class="form-control" required>
                            <option value="">-- เลือกประเภทกิจกรรม --</option>
                            <?php
                            if($activityTypes && $activityTypes->rowCount() > 0) {
                                // รีเซ็ต pointer
                                $activityTypes->execute();
                                
                                while($actType = $activityTypes->fetch(PDO::FETCH_ASSOC)) {
                                    $selected = ($editCriteria['ActType_id'] == $actType['ActType_id']) ? 'selected' : '';
                                    echo '<option value="' . $actType['ActType_id'] . '" ' . $selected . '>' . 
                                         $actType['ActType_Name'] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_act_hour">จำนวนชั่วโมง <span class="text-danger">*</span></label>
                        <input type="number" name="edit_act_hour" id="edit_act_hour" class="form-control" min="1"
                            value="<?php echo $editCriteria['Act_hour']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_act_amount">จำนวนกิจกรรม <span class="text-danger">*</span></label>
                        <input type="number" name="edit_act_amount" id="edit_act_amount" class="form-control" min="1"
                            value="<?php echo $editCriteria['Act_amount']; ?>" required>
                        <small class="form-text text-muted">จำนวนกิจกรรมที่ต้องเข้าร่วมตามเกณฑ์</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-warning">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// เปิด Modal แก้ไขเมื่อมีการเรียกหน้านี้พร้อมพารามิเตอร์ edit
$(document).ready(function() {
    $('#editCriteriaModal').modal('show');
});
</script>
<?php endif; ?>

<script>
$(document).ready(function() {
    // การตรวจสอบฟอร์มก่อนส่ง
    $('#addCriteriaForm').on('submit', function(e) {
        // ตรวจสอบช่องที่จำเป็น
        const critName = $('#crit_name').val();
        const curriId = $('#curri_id').val();
        const planId = $('#plan_id').val();
        const actTypeId = $('#act_type_id').val();
        const actHour = $('#act_hour').val();
        const actAmount = $('#act_amount').val();

        if (!critName || !curriId || !planId || !actTypeId || !actHour || !actAmount) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
            return false;
        }

        return true;
    });

    // การตรวจสอบฟอร์มแก้ไขก่อนส่ง
    $('#editCriteriaForm').on('submit', function(e) {
        // ตรวจสอบช่องที่จำเป็น
        const critName = $('#edit_crit_name').val();
        const curriId = $('#edit_curri_id').val();
        const planId = $('#edit_plan_id').val();
        const actTypeId = $('#edit_act_type_id').val();
        const actHour = $('#edit_act_hour').val();
        const actAmount = $('#edit_act_amount').val();

        if (!critName || !curriId || !planId || !actTypeId || !actHour || !actAmount) {
            e.preventDefault();
            alert('กรุณากรอกข้อมูลให้ครบทุกช่อง');
            return false;
        }

        return true;
    });
});
</script>

<?php require_once 'includes/tablejs.php' ?>