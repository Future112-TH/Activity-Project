<?php
// เรียกใช้ไฟล์เชื่อมต่อฐานข้อมูลและคลาส Controller
include_once '../config/database.php';
include_once '../config/controller.php';

// สร้างการเชื่อมต่อกับฐานข้อมูล
$database = new Database();
$db = $database->connect();

// สร้างอ็อบเจกต์ Controller
$controller = new Controller($db);

// ดึงข้อมูลอาจารย์ที่ปรึกษา (ใช้ session ที่เก็บรหัสอาจารย์)
$profId = $_SESSION['prof_id'] ?? '30701'; // ตัวอย่างรหัสอาจารย์ (ใช้ session จริงในการพัฒนา)

// ดึงข้อมูลนักศึกษาที่อยู่ในที่ปรึกษา
$students = $controller->getAdvisoryStudents($profId);

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
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-graduate mr-2"></i>นักศึกษาในที่ปรึกษา</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">นักศึกษาในที่ปรึกษา</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <!-- ตารางข้อมูลนักศึกษา -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายชื่อนักศึกษาในที่ปรึกษา
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr class="bg-light">
                                            <th width="12%">รหัสนักศึกษา</th>
                                            <th width="6%">คำนำหน้า</th>
                                            <th width="20%">ชื่อ</th>
                                            <th width="20%">นามสกุล</th>
                                            <th width="22%">สาขาวิชา</th>
                                            <th width="10%">หลักสูตร</th>
                                            <th width="10%" class="text-center">รายละเอียด</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if($students && $students->rowCount() > 0): ?>
                                        <?php while($row = $students->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?php echo $row['Stu_id']; ?></td>
                                            <td><?php echo $row['Title_name'] ?? '-'; ?></td>
                                            <td><?php echo $row['Stu_fname']; ?></td>
                                            <td><?php echo $row['Stu_lname']; ?></td>
                                            <td><?php echo $row['Maj_name'] ?? '-'; ?></td>
                                            <td>
                                                <span
                                                    class="badge badge-primary"><?php echo $row['Plan_name'] ?? '-'; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="index.php?menu=4&view=<?php echo $row['Stu_id']; ?>"
                                                    class="btn btn-info btn-sm" title="ดูข้อมูล">
                                                    <i class="fas fa-eye"></i> ดูข้อมูล
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">ไม่พบข้อมูลนักศึกษาในที่ปรึกษา</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
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
                    <a href="index.php?menu=4" class="close text-white" aria-label="Close">
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
                                                    <td><?php echo (!empty($viewStudent['Religion']) ? $viewStudent['Religion'] : '-') . '/' . 
                                                        (!empty($viewStudent['Nationality']) ? $viewStudent['Nationality'] : '-'); ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="font-weight-bold">หลักสูตร/สาขา:</td>
                                                    <td><?php echo $viewStudent['Plan_name'] . ' / ' . $viewStudent['Maj_name']; ?>
                                                    </td>
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
                </div>
                <div class="modal-footer">
                    <a href="index.php?menu=4" class="btn btn-secondary">
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