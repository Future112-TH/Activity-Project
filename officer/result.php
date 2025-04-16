<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-chart-bar mr-2"></i>รายงานผลการเข้าร่วมกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">รายงานผลการเข้าร่วมกิจกรรม</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- ปุ่มออกรายงาน -->
            <div class="mb-3">
                <button type="button" class="btn btn-success" onclick="generateExcelReport()">
                    <i class="fas fa-file-excel mr-1"></i> ออกรายงาน Excel
                </button>
                <button type="button" class="btn btn-danger" onclick="generatePDFReport()">
                    <i class="fas fa-file-pdf mr-1"></i> ออกรายงาน PDF
                </button>
            </div>

            <!-- ตารางผลการเข้าร่วมกิจกรรม -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-1"></i>
                        รายชื่อนักศึกษาและข้อมูลการเข้าร่วมกิจกรรม
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="5%">ลำดับ</th>
                                    <th width="10%">รหัสนักศึกษา</th>
                                    <th width="15%">ชื่อ-นามสกุล</th>
                                    <th width="10%">สาขา</th>
                                    <th width="5%">ประเภท</th>
                                    <th width="20%">กิจกรรมล่าสุด</th>
                                    <th width="10%">วันที่เข้าร่วม</th>
                                    <th width="7%">กิจกรรม</th>
                                    <th width="8%">ชั่วโมง</th>
                                    <th width="10%">รายงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>6501103071001</td>
                                    <td>นายสมชาย ใจดี</td>
                                    <td>วิทยาการข้อมูลฯ</td>
                                    <td><span class="badge badge-primary">IT</span></td>
                                    <td>อบรมการใช้งาน Python</td>
                                    <td>01/03/2025</td>
                                    <td>5/8</td>
                                    <td>15/24</td>
                                    <td>
                                        <a href="student_activity_detail.php?id=6501103071001" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-alt"></i> รายงาน
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>2</td>
                                    <td>6501103071002</td>
                                    <td>นางสาวสมหญิง รักเรียน</td>
                                    <td>วิทยาการข้อมูลฯ</td>
                                    <td><span class="badge badge-primary">IT</span></td>
                                    <td>บรรยายพิเศษ AI ในอนาคต</td>
                                    <td>28/02/2025</td>
                                    <td>7/8</td>
                                    <td>21/24</td>
                                    <td>
                                        <a href="student_activity_detail.php?id=6501103071002" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-alt"></i> รายงาน
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>3</td>
                                    <td>6501103071003</td>
                                    <td>นายมานะ ตั้งใจ</td>
                                    <td>วิทยาการข้อมูลฯ</td>
                                    <td><span class="badge badge-secondary">ITR</span></td>
                                    <td>กิจกรรมเข้าค่ายอาสาพัฒนาชุมชน</td>
                                    <td>15/02/2025</td>
                                    <td>6/6</td>
                                    <td>18/18</td>
                                    <td>
                                        <a href="student_activity_detail.php?id=6501103071003" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-alt"></i> รายงาน
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // ไม่มีโค้ดพิเศษอื่นๆ เมื่อหน้าโหลดเสร็จ
});

// ออกรายงาน Excel
function generateExcelReport() {
    alert('กำลังสร้างรายงาน Excel...');
    // ในระบบจริง จะส่งคำขอไปยัง API เพื่อสร้างไฟล์ Excel
}

// ออกรายงาน PDF
function generatePDFReport() {
    alert('กำลังสร้างรายงาน PDF...');
    // ในระบบจริง จะส่งคำขอไปยัง API เพื่อสร้างไฟล์ PDF
}

// แสดงรายงานนักศึกษา
function generateStudentReport(studentId) {
    // สมมติว่านี่คือข้อมูลที่ได้จาก AJAX
    let studentData = {};
    let activities = [];
    
    // สำหรับตัวอย่าง - สร้างข้อมูลตามรหัสนักศึกษา
    if (studentId === '6501103071001') {
        studentData = {
            id: '6501103071001',
            name: 'นายสมชาย ใจดี',
            major: 'วิทยาการข้อมูลและเทคโนโลยีสารสนเทศ',
            type: 'IT (ปกติ)',
            activityCount: 5,
            activityRequired: 8,
            hourCount: 15,
            hourRequired: 24
        };
        
        activities = [
            {id: 1, name: 'อบรมการใช้งาน Python', type: 'กิจกรรมวิชาการ', date: '01/03/2025', hours: 3, status: 'ผ่าน'},
            {id: 2, name: 'งานวันไหว้ครู', type: 'กิจกรรมทั่วไป', date: '15/02/2025', hours: 3, status: 'ผ่าน'},
            {id: 3, name: 'กิจกรรมเข้าค่ายอาสาพัฒนาชุมชน', type: 'กิจกรรมบำเพ็ญประโยชน์', date: '10/02/2025', hours: 6, status: 'ผ่าน'},
            {id: 4, name: 'สัมมนาความปลอดภัยทางไซเบอร์', type: 'กิจกรรมวิชาการ', date: '05/02/2025', hours: 2, status: 'ผ่าน'},
            {id: 5, name: 'กิจกรรมตักบาตรปีใหม่', type: 'กิจกรรมทั่วไป', date: '15/01/2025', hours: 1, status: 'ผ่าน'}
        ];
    } else if (studentId === '6401103071001') {
        studentData = {
            id: '6401103071001',
            name: 'นายวิชัย ขยันเรียน',
            major: 'วิทยาการข้อมูลและเทคโนโลยีสารสนเทศ',
            type: 'IT (ปกติ)',
            activityCount: 8,
            activityRequired: 8,
            hourCount: 24,
            hourRequired: 24
        };
        
        activities = [
            {id: 1, name: 'อบรมการพัฒนาเว็บแอปพลิเคชัน', type: 'กิจกรรมวิชาการ', date: '05/02/2025', hours: 3, status: 'ผ่าน'},
            {id: 2, name: 'อบรมการใช้งาน Git และ GitHub', type: 'กิจกรรมวิชาการ', date: '01/02/2025', hours: 3, status: 'ผ่าน'},
            {id: 3, name: 'กิจกรรมวันลอยกระทง', type: 'กิจกรรมทั่วไป', date: '22/01/2025', hours: 3, status: 'ผ่าน'},
            {id: 4, name: 'ค่ายจิตอาสาพัฒนาชุมชน', type: 'กิจกรรมบำเพ็ญประโยชน์', date: '15/01/2025', hours: 6, status: 'ผ่าน'},
            {id: 5, name: 'กิจกรรมตักบาตรปีใหม่', type: 'กิจกรรมทั่วไป', date: '05/01/2025', hours: 2, status: 'ผ่าน'},
            {id: 6, name: 'อบรมการใช้งาน Cloud Computing', type: 'กิจกรรมวิชาการ', date: '20/12/2024', hours: 3, status: 'ผ่าน'},
            {id: 7, name: 'สัมมนาทิศทางเทคโนโลยีในอนาคต', type: 'กิจกรรมวิชาการ', date: '15/12/2024', hours: 2, status: 'ผ่าน'},
            {id: 8, name: 'อบรมทักษะการนำเสนอผลงาน', type: 'กิจกรรมวิชาการ', date: '10/12/2024', hours: 2, status: 'ผ่าน'}
        ];
    } else {
        // ข้อมูลตัวอย่างสำหรับนักศึกษาอื่นๆ
        studentData = {
            id: studentId,
            name: 'นักศึกษาตัวอย่าง',
            major: 'วิทยาการข้อมูลและเทคโนโลยีสารสนเทศ',
            type: 'IT (ปกติ)',
            activityCount: 4,
            activityRequired: 8,
            hourCount: 12,
            hourRequired: 24
        };
        
        activities = [
{id: 1, name: 'กิจกรรมตัวอย่างที่ 1', type: 'กิจกรรมวิชาการ', date: '01/02/2025', hours: 3, status: 'ผ่าน'},
            {id: 2, name: 'กิจกรรมตัวอย่างที่ 2', type: 'กิจกรรมทั่วไป', date: '15/01/2025', hours: 3, status: 'ผ่าน'},
            {id: 3, name: 'กิจกรรมตัวอย่างที่ 3', type: 'กิจกรรมบำเพ็ญประโยชน์', date: '10/01/2025', hours: 3, status: 'ผ่าน'},
            {id: 4, name: 'กิจกรรมตัวอย่างที่ 4', type: 'กิจกรรมวิชาการ', date: '05/01/2025', hours: 3, status: 'ผ่าน'}
        ];
    }
    
    // แสดงข้อมูลในโมดัล
    $('#student-name-title').text(studentData.name);
    $('#modal-student-id').val(studentData.id);
    $('#modal-student-name').val(studentData.name);
    $('#modal-student-major').val(studentData.major);
    $('#modal-student-type').val(studentData.type);
    
    // ข้อมูลสรุปกิจกรรม
    $('#modal-activity-count').text(studentData.activityCount);
    $('#modal-activity-required').text(studentData.activityRequired);
    $('#modal-hour-count').text(studentData.hourCount);
    $('#modal-hour-required').text(studentData.hourRequired);
    
    // คำนวณเปอร์เซ็นต์ความก้าวหน้า
    const activityProgress = (studentData.activityCount / studentData.activityRequired) * 100;
    const hourProgress = (studentData.hourCount / studentData.hourRequired) * 100;
    
    // กำหนดความกว้างของแถบความก้าวหน้า
    $('#modal-activity-progress').css('width', activityProgress + '%');
    $('#modal-hour-progress').css('width', hourProgress + '%');
    
    // สร้างรายการกิจกรรม
    let activityHTML = '';
    activities.forEach((activity, index) => {
        activityHTML += `
            <tr>
                <td class="text-center">${index + 1}</td>
                <td>${activity.name}</td>
                <td>${activity.type}</td>
                <td>${activity.date}</td>
                <td class="text-center">${activity.hours}</td>
                <td><span class="badge badge-success">${activity.status}</span></td>
            </tr>
        `;
    });
    $('#modal-activities-list').html(activityHTML);
    
    // แสดงโมดัล
    $('#studentReportModal').modal('show');
}

// พิมพ์รายงานนักศึกษา
function printStudentReport() {
    alert('กำลังเตรียมข้อมูลสำหรับการพิมพ์...');
    // ในระบบจริง จะส่งคำขอไปยัง API เพื่อพิมพ์รายงาน
}
</script>

<?php require_once 'includes/tablejs.php' ?>