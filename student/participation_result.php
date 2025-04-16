<?php
// ตรวจสอบว่ามีการล็อกอินหรือไม่
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// กรณีข้อมูลถูกดึงจาก process_participation.php
$student = isset($_SESSION['student_data']) ? $_SESSION['student_data'] : null;
$activities = isset($_SESSION['activities_data']) ? $_SESSION['activities_data'] : [];
$approved_comparisions = isset($_SESSION['approved_comparisions']) ? $_SESSION['approved_comparisions'] : [];
$total_hours = isset($_SESSION['total_hours']) ? $_SESSION['total_hours'] : 0;
$student_id = isset($_SESSION['real_student_id']) ? $_SESSION['real_student_id'] : $_SESSION['user_id'];

// กรณีที่ไม่ได้มาจาก process ให้ดึงข้อมูลโดยตรง
if (empty($student) || empty($activities)) {
    // สร้างการเชื่อมต่อฐานข้อมูลใหม่
    $database = new Database();
    $db = $database->connect();
    
    // สร้างอ็อบเจกต์ Controller ใหม่
    $controller = new Controller($db);
    
    // รับรหัสนักศึกษาจาก session
    $user_id = $_SESSION['user_id'];
    
    // ตรวจสอบว่ารหัสผู้ใช้เป็น username จากตาราง login
    $sql_login = "SELECT * FROM login WHERE user_id = :user_id";
    $stmt_login = $db->prepare($sql_login);
    $stmt_login->bindParam(':user_id', $user_id);
    $stmt_login->execute();
    $login_data = $stmt_login->fetch(PDO::FETCH_ASSOC);
    
    // หากเป็น user จากตาราง login ที่มีการเชื่อมโยงกับนักศึกษา
    if ($login_data && !empty($login_data['Stu_id'])) {
        $student_id = $login_data['Stu_id'];
    } else {
        // ถ้าไม่มีข้อมูลในตาราง login หรือไม่ได้เชื่อมโยงกับนักศึกษา ให้ใช้ user_id เป็น student_id
        $student_id = $user_id;
    }
    
    // ดึงข้อมูลนักศึกษา
    $student = $controller->getStudentById($student_id);
    
    // สร้างคำสั่ง SQL เพื่อเรียกดูข้อมูลการเข้าร่วมกิจกรรม
    $sql = "SELECT p.*, a.Act_name, a.Act_start_date, a.Act_stop_date, a.ActSemester, a.ActYear, at.ActType_Name 
            FROM participation p 
            LEFT JOIN activity a ON p.Act_id = a.Act_id 
            LEFT JOIN activity_type at ON a.ActType_id = at.ActType_id 
            WHERE p.Stu_id = :stu_id 
            ORDER BY a.Act_start_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':stu_id', $student_id);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // ดึงข้อมูลการเทียบโอนกิจกรรมที่อนุมัติแล้ว
    if (method_exists($controller, 'getApprovedComparisionsByStudentId')) {
        $approved_comparisions = $controller->getApprovedComparisionsByStudentId($student_id);
    } else {
        // ถ้าไม่มีเมธอดนี้ ให้ดึงข้อมูลโดยตรงจาก SQL
        $comp_sql = "SELECT * FROM comparision WHERE Stu_id = :stu_id AND Status = 'approved' ORDER BY RequestDate DESC";
        $comp_stmt = $db->prepare($comp_sql);
        $comp_stmt->bindParam(':stu_id', $student_id);
        $comp_stmt->execute();
        $approved_comparisions = $comp_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // คำนวณจำนวนชั่วโมงกิจกรรมทั้งหมด
    $total_hours = 0;
    foreach ($activities as $activity) {
        $total_hours += $activity['Act_hour'];
    }
    
    // เพิ่มชั่วโมงจากการเทียบโอนกิจกรรม
    foreach ($approved_comparisions as $comparision) {
        $total_hours += $comparision['Act_hour'];
    }
}

// เพิ่มการจัดกลุ่มข้อมูลตามภาคเรียน (สำหรับกิจกรรมที่เข้าร่วม)
$activities_by_semester = [];
if (!empty($activities)) {
    foreach ($activities as $activity) {
        $semester_key = $activity['ActSemester']; // ใช้เฉพาะภาคเรียน ไม่รวมปี
        if (!isset($activities_by_semester[$semester_key])) {
            $activities_by_semester[$semester_key] = [];
        }
        $activities_by_semester[$semester_key][] = $activity;
    }
    
    // เรียงลำดับภาคเรียนจากมากไปน้อย
    krsort($activities_by_semester);
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">ตรวจสอบผลการเข้าร่วมกิจกรรม</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ตรวจสอบผลการเข้าร่วมกิจกรรม</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- แสดงข้อความแจ้งเตือน -->
            <?php if(isset($_SESSION['success'])) { ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-check"></i> สำเร็จ!</h5>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
            <?php } ?>

            <?php if(isset($_SESSION['error'])) { ?>
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h5><i class="icon fas fa-ban"></i> ผิดพลาด!</h5>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
            <?php } ?>

            <div class="row">
                <div class="col-lg-12">
                    <!-- ข้อมูลสรุปกิจกรรม -->
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">สรุปการเข้าร่วมกิจกรรม</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>รหัสนักศึกษา:</strong> <?php echo $student_id; ?></p>
                                    <p><strong>ชื่อ-นามสกุล:</strong>
                                        <?php 
                                        if ($student) {
                                            echo $student['Title_name'] . ' ' . $student['Stu_fname'] . ' ' . $student['Stu_lname']; 
                                        } else {
                                            // ถ้าไม่พบข้อมูลนักศึกษา แสดงข้อมูลจาก session แทน
                                            if (isset($_SESSION['fullname'])) {
                                                echo $_SESSION['fullname'];
                                            } else {
                                                echo "ไม่พบข้อมูลนักศึกษา (รหัส: $student_id)";
                                            }
                                        }
                                        ?>
                                    </p>
                                    <p><strong>สาขาวิชา:</strong>
                                        <?php 
                                        if ($student) {
                                            echo $student['Maj_name']; 
                                        } else {
                                            echo "ไม่พบข้อมูล";
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- ลบส่วนการแสดงผลทั้ง 4 กรอบออกแล้ว -->
                            </div>
                        </div>
                    </div>

                    <!-- ตารางกิจกรรมที่ขอเทียบโอน (ย้ายขึ้นมาอยู่ข้างบน) -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">กิจกรรมที่ขอเทียบโอน (อนุมัติแล้ว)</h3>
                        </div>
                        <div class="card-body">
                            <table id="table2" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>ประเภทการขอเทียบ</th>
                                        <th>รายละเอียด</th>
                                        <th>ภาคเรียน/ปีการศึกษา</th>
                                        <th>จำนวนชั่วโมง</th>
                                        <th>วันที่อนุมัติ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($approved_comparisions)) : ?>
                                    <?php $i = 1; foreach ($approved_comparisions as $comparision) : ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td>
                                            <?php 
                                                    switch ($comparision['RequestType']) {
                                                        case 'position':
                                                            echo 'ตำแหน่งในองค์กร';
                                                            break;
                                                        case 'award':
                                                            echo 'รางวัล/การแข่งขัน';
                                                            break;
                                                        case 'helper':
                                                            echo 'ผู้ช่วยงาน';
                                                            break;
                                                        default:
                                                            echo $comparision['RequestType'];
                                                    }
                                                    ?>
                                        </td>
                                        <td><?php echo $comparision['RequestDetail']; ?></td>
                                        <td><?php echo $comparision['ActSemester'] . '/' . date('Y', strtotime($comparision['ActYear'])); ?>
                                        </td>
                                        <td><?php echo $comparision['Act_hour']; ?></td>
                                        <td><?php echo !empty($comparision['ApprovedDate']) ? date('d/m/Y', strtotime($comparision['ApprovedDate'])) : '-'; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">ไม่พบข้อมูลการเทียบโอนกิจกรรมที่อนุมัติแล้ว
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ตารางกิจกรรมที่เข้าร่วม แยกตามภาคเรียน -->
                    <?php if (!empty($activities_by_semester)) : ?>
                    <?php foreach ($activities_by_semester as $semester => $sem_activities) : ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">กิจกรรมที่เข้าร่วม (ภาคเรียน <?php echo $semester; ?>)</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>รหัสกิจกรรม</th>
                                        <th>ชื่อกิจกรรม</th>
                                        <th>ประเภทกิจกรรม</th>
                                        <th>ปีการศึกษา</th>
                                        <th>วันที่จัดกิจกรรม</th>
                                        <th>จำนวนชั่วโมง</th>
                                        <th>เช็คอิน</th>
                                        <th>เช็คเอาท์</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($sem_activities as $activity) : ?>
                                    <tr>
                                        <td><?php echo $i++; ?></td>
                                        <td><?php echo $activity['Act_id']; ?></td>
                                        <td><?php echo $activity['Act_name']; ?></td>
                                        <td><?php echo $activity['ActType_Name']; ?></td>
                                        <td><?php echo date('Y', strtotime($activity['ActYear'])); ?></td>
                                        <td>
                                            <?php 
                                                    $start_date = date('d/m/Y', strtotime($activity['Act_start_date']));
                                                    $stop_date = date('d/m/Y', strtotime($activity['Act_stop_date']));
                                                    echo $start_date;
                                                    if ($start_date != $stop_date) {
                                                        echo " - " . $stop_date;
                                                    }
                                                ?>
                                        </td>
                                        <td><?php echo $activity['Act_hour']; ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($activity['CheckIn'])); ?></td>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($activity['CheckOut'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">กิจกรรมที่เข้าร่วม</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-center">ไม่พบข้อมูลการเข้าร่วมกิจกรรม</p>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
                <!-- /.col-md-12 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>