<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-exchange-alt mr-2"></i>เทียบชั่วโมงกิจกรรม</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">เทียบชั่วโมงกิจกรรม</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- แท็บแสดงประเภทคำร้อง -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list mr-1"></i>
                        รายการคำร้องขอเทียบชั่วโมงกิจกรรม
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="filter_status" class="form-control">
                                    <option value="">-- ทั้งหมด --</option>
                                    <option value="pending">รอการพิจารณา</option>
                                    <option value="approved">อนุมัติแล้ว</option>
                                    <option value="rejected">ไม่อนุมัติ</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select id="filter_type" class="form-control">
                                    <option value="">-- ประเภทการเทียบ --</option>
                                    <option value="position">ตำแหน่งกรรมการนักศึกษา</option>
                                    <option value="award">รางวัล/การแข่งขัน</option>
                                    <option value="helper">ช่วยงานกิจกรรม</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="ค้นหา: รหัส, ชื่อ-สกุล">
                                <div class="input-group-append">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-search"></i> ค้นหา
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table1" class="table table-bordered">
                            <thead>
                                <tr class="bg-light">
                                    <th width="5%">ลำดับ</th>
                                    <th width="10%">วันที่ส่งคำร้อง</th>
                                    <th width="15%">รหัสนักศึกษา</th>
                                    <th width="20%">ชื่อ-สกุล</th>
                                    <th width="10%">ประเภท</th>
                                    <th width="25%">รายการเทียบ</th>
                                    <th width="15%">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center">1</td>
                                    <td>05/03/2025</td>
                                    <td>6501103071001</td>
                                    <td>นายสมชาย ใจดี</td>
                                    <td><span class="badge badge-primary">IT</span></td>
                                    <td>นายกสโมสรนักศึกษา</td>
                                    <td>
                                        <button class="btn btn-info btn-sm view-request" data-id="1" data-toggle="modal"
                                            data-target="#viewRequestModal">
                                            <i class="fas fa-eye"></i> ดูคำร้อง
                                        </button>
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

<style>
.activity-item {
    cursor: pointer;
}

.activity-item:hover {
    background-color: #f8f9fa;
}

.selected-activity {
    background-color: #e8f4f8;
    border-left: 3px solid #17a2b8;
}
</style>

<!-- Modal ดูรายละเอียดคำร้อง -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog" aria-labelledby="viewRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="viewRequestModalLabel">
                    <i class="fas fa-file-alt mr-2"></i> รายละเอียดคำร้องขอเทียบชั่วโมงกิจกรรม
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- รายละเอียดคำร้อง -->
                        <div class="card card-outline card-info mb-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-user-graduate mr-2"></i>ข้อมูลนักศึกษา</h3>
                                <span class="badge badge-pill badge-primary float-right mt-1" id="request-date">วันที่ส่งคำร้อง: 05/03/2025</span>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>รหัสนักศึกษา:</label>
                                            <input type="text" class="form-control" id="student-id" value="6501103071001" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ชื่อ-สกุล:</label>
                                            <input type="text" class="form-control" id="student-name" value="นายสมชาย ใจดี" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>หลักสูตร:</label>
                                            <input type="text" class="form-control" id="student-plan" value="IT (ปกติ 4 ปี)" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>สาขาวิชา:</label>
                                            <input type="text" class="form-control" id="student-major" value="วิทยาการข้อมูลและเทคโนโลยีสารสนเทศ" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>อาจารย์ที่ปรึกษา:</label>
                                            <input type="text" class="form-control" id="student-advisor" value="อาจารย์สมศรี" readonly>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-success" id="student-progress-bar" role="progressbar" style="width: 75%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <small class="text-muted">ความก้าวหน้าของนักศึกษา:</small>
                                    <span class="badge badge-success" id="student-progress">6/8 กิจกรรม, 18/24 ชั่วโมง</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- ฝั่งซ้าย: แสดงรายการกิจกรรมที่นักศึกษาเคยเข้าร่วม -->
                    <div class="col-md-6">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-clipboard-list mr-2"></i>กิจกรรมที่เคยเข้าร่วม</h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="bg-light">
                                                <th width="45%">ชื่อกิจกรรม</th>
                                                <th width="20%">วันที่</th>
                                                <th width="15%">ชั่วโมง</th>
                                                <th width="20%">ประเภท</th>
                                            </tr>
                                        </thead>
                                                                                    <tbody id="participated-activities-list">
                                            <!-- รายการกิจกรรมที่เคยเข้าร่วมจะถูกเพิ่มโดย JavaScript -->
                                            <tr class="activity-item selected-activity" data-activity-id="201">
                                                <td>อบรมการเขียนโปรแกรม Python</td>
                                                <td>05/01/2025</td>
                                                <td class="text-center">6</td>
                                                <td><span class="badge badge-info">วิชาการ</span></td>
                                            </tr>
                                            <tr class="activity-item" data-activity-id="202">
                                                <td>กิจกรรมวันไหว้ครู</td>
                                                <td>18/07/2024</td>
                                                <td class="text-center">3</td>
                                                <td><span class="badge badge-primary">ทั่วไป</span></td>
                                            </tr>
                                            <tr class="activity-item" data-activity-id="203">
                                                <td>กิจกรรมกีฬาสีภายใน</td>
                                                <td>15/08/2024</td>
                                                <td class="text-center">6</td>
                                                <td><span class="badge badge-primary">ทั่วไป</span></td>
                                            </tr>
                                            <tr class="activity-item" data-activity-id="204">
                                                <td>อบรมการใช้งาน Git และ GitHub</td>
                                                <td>05/09/2024</td>
                                                <td class="text-center">3</td>
                                                <td><span class="badge badge-info">วิชาการ</span></td>
                                            </tr>
                                            <tr class="activity-item" data-activity-id="205">
                                                <td>กิจกรรมบำเพ็ญประโยชน์ ปลูกป่าชายเลน</td>
                                                <td>15/11/2024</td>
                                                <td class="text-center">6</td>
                                                <td><span class="badge badge-primary">ทั่วไป</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer bg-light">
                                <h5 class="text-info mb-3"><i class="fas fa-info-circle mr-2"></i>รายละเอียดกิจกรรมที่เลือก</h5>
                                <div class="form-group">
                                    <label>รหัสกิจกรรม:</label>
                                    <input type="text" class="form-control" id="selected-activity-id" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>ชื่อกิจกรรม:</label>
                                    <input type="text" class="form-control" id="selected-activity-name" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label>ประเภทกิจกรรม:</label>
                                    <input type="text" class="form-control" id="selected-activity-type" readonly>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>วันที่จัดกิจกรรม:</label>
                                            <input type="date" class="form-control" id="selected-activity-date" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>จำนวนชั่วโมง:</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="selected-activity-hours" readonly>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">ชั่วโมง</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ภาคการศึกษา:</label>
                                            <input type="text" class="form-control" id="selected-activity-semester" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ปีการศึกษา:</label>
                                            <input type="text" class="form-control" id="selected-activity-year" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ฝั่งขวา: เลือกเกณฑ์การเทียบ -->
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-sliders-h mr-2"></i>เกณฑ์การเทียบชั่วโมง</h3>
                                <div class="float-right">
                                    <span class="badge badge-primary" id="student-plan-badge">IT (ปกติ 4 ปี)</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <form id="equivalence-form">
                                    <div class="form-group">
                                        <label for="equivalence-criteria">เลือกเกณฑ์การเทียบชั่วโมง <span class="text-danger">*</span></label>
                                        <select class="form-control" id="equivalence-criteria" required>
                                            <option value="">-- เลือกเกณฑ์การเทียบ --</option>
                                            <!-- ตัวเลือกจะถูกดึงจากฐานข้อมูลด้วย AJAX -->
                                        </select>
                                        <small class="form-text text-info">เกณฑ์การเทียบเป็นไปตามหลักสูตรของนักศึกษา</small>
                                    </div>

                                    <hr>
                                    <h5 class="text-primary mb-3"><i class="fas fa-calculator mr-2"></i>ผลการเทียบชั่วโมง</h5>
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="equivalent-activities">จำนวนกิจกรรมที่เทียบได้:</label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" id="equivalent-activities" value="0" readonly>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">กิจกรรม</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="equivalent-hours">จำนวนชั่วโมงที่เทียบได้:</label>
                                                        <div class="input-group">
                                                            <input type="number" class="form-control" id="equivalent-hours" value="0" readonly>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">ชั่วโมง</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group mb-0">
                                                <label for="activity-category">เทียบเป็นประเภทกิจกรรม:</label>
                                                <select class="form-control" id="activity-category">
                                                    <option value="general" selected>กิจกรรมทั่วไป (ข้อ 3.2)</option>
                                                    <option value="academic">กิจกรรมวิชาการ (ข้อ 3.1)</option>
                                                </select>
                                                <small class="form-text text-muted mt-2">ตามข้อ 3.2, 4.2, 5.2 และ 6.2 ของประกาศฯ</small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ส่วนสรุปผลและสถานะการพิจารณา -->
                                    <div class="card mt-3 bg-light">
                                        <div class="card-body">
                                            <div class="form-group">
                                                <label for="review-status">ผลการพิจารณา: <span class="text-danger">*</span></label>
                                                <select class="form-control" id="review-status" required>
                                                    <option value="">-- เลือกผลการพิจารณา --</option>
                                                    <option value="approved" selected>อนุมัติ</option>
                                                    <option value="rejected">ไม่อนุมัติ</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> ยกเลิก
                </button>
                <button type="button" class="btn btn-success" onclick="approveRequest()">
                    <i class="fas fa-check-circle mr-1"></i> บันทึกผลการพิจารณา
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.activity-item {
    cursor: pointer;
}
.activity-item:hover {
    background-color: #f8f9fa;
}
.selected-activity {
    background-color: #e8f4f8;
    border-left: 3px solid #17a2b8;
}
</style>

<script>
$(document).ready(function() {
    // คลิกเลือกกิจกรรม
    $(document).on('click', '.activity-item', function() {
        // ลบ class selected-activity จากทุกแถว
        $('.activity-item').removeClass('selected-activity');
        
        // เพิ่ม class selected-activity ให้แถวที่คลิก
        $(this).addClass('selected-activity');
        
        // โหลดข้อมูลกิจกรรมที่เลือก
        const activityId = $(this).data('activity-id');
        loadSelectedActivityDetails(activityId);
    });
    
    // ค้นหากิจกรรม
    $('#search-activity').on('keyup', function() {
        const searchText = $(this).val().toLowerCase();
        
        $('#participated-activities-list tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
        });
    });
    
    // อัพเดทค่าการเทียบตามเกณฑ์ที่เลือก
    updateEquivalenceValues();
    
    // โหลดข้อมูลความก้าวหน้าของนักศึกษา
    loadStudentProgress(plan);
}

// โหลดข้อมูลความก้าวหน้าของนักศึกษา
function loadStudentProgress(plan) {
    // ข้อมูลตัวอย่าง (ในระบบจริงควรใช้ AJAX)
    const progressData = {
        "IT": { current_act: 5, total_act: 8, current_hour: 24, total_hour: 24 },
        "ITR": { current_act: 4, total_act: 6, current_hour: 12, total_hour: 18 },
        "IT4S": { current_act: 3, total_act: 4, current_hour: 9, total_hour: 12 },
        "ITS": { current_act: 2, total_act: 3, current_hour: 6, total_hour: 9 }
    };
    
    const data = progressData[plan] || progressData["IT"];
    
    // คำนวณเปอร์เซ็นต์ความก้าวหน้า
    const percentage = Math.round((data.current_hour / data.total_hour) * 100);
    
    // แสดงข้อมูลความก้าวหน้า
    $('#student-progress').text(`${data.current_act}/${data.total_act} กิจกรรม, ${data.current_hour}/${data.total_hour} ชั่วโมง`);
    $('#student-progress-bar').css('width', `${percentage}%`).attr('aria-valuenow', percentage);
    
    // เปลี่ยนสีตามเปอร์เซ็นต์
    if (percentage >= 100) {
        $('#student-progress-bar').removeClass('bg-warning bg-danger').addClass('bg-success');
        $('#student-progress').removeClass('badge-warning badge-danger').addClass('badge-success');
    } else if (percentage >= 50) {
        $('#student-progress-bar').removeClass('bg-success bg-danger').addClass('bg-warning');
        $('#student-progress').removeClass('badge-success badge-danger').addClass('badge-warning');
    } else {
        $('#student-progress-bar').removeClass('bg-success bg-warning').addClass('bg-danger');
        $('#student-progress').removeClass('badge-success badge-warning').addClass('badge-danger');
    }
}

// อัพเดทค่าการเทียบ เมื่อเปลี่ยนเกณฑ์
function updateEquivalenceValues(activity = null) {
    // ดึงค่าชั่วโมงจริงของกิจกรรมที่เลือก
    const activityHours = parseInt($('#selected-activity-hours').val()) || 0;
    
    const criteriaId = $('#equivalence-criteria').val();
    if (!criteriaId) {
        $('#equivalent-activities').val(0);
        $('#equivalent-hours').val(0);
        return;
    }
    
    const selectedOption = $(`#equivalence-criteria option[value="${criteriaId}"]`);
    let activities = parseInt(selectedOption.data('amount')) || 0;
    let hours = parseInt(selectedOption.data('hour')) || 0;
    
    // กรณีช่วยกิจกรรม ใช้ชั่วโมงจริง
    if (selectedOption.text().toLowerCase().includes('ช่วยกิจกรรม')) {
        hours = activityHours;
    }
    
    $('#equivalent-activities').val(activities);
    $('#equivalent-hours').val(hours);
    
    // ปรับตัวเลือกประเภทกิจกรรมตามกิจกรรมที่เลือก
    const activityCategory = $('#selected-activity-type').val().toLowerCase().includes('วิชาการ') ? 'academic' : 'general';
    $('#activity-category').val(activityCategory);
}

// โหลดข้อมูลคำร้อง
function loadRequestDetails(requestId) {
    // ในระบบจริงควรใช้ AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
    // ตัวอย่างข้อมูลสำหรับการพัฒนา
    let requestData = {
        studentId: '6501103071001',
        studentName: 'นายสมชาย ใจดี',
        studentPlan: 'IT (ปกติ 4 ปี)',
        planCode: 'IT',
        studentMajor: 'วิทยาการข้อมูลและเทคโนโลยีสารสนเทศ',
        studentAdvisor: 'อาจารย์สมศรี',
        requestDate: '05/03/2025',
        // ข้อมูลกิจกรรมที่เคยเข้าร่วม
        participatedActivities: [
            {
                id: 201,
                name: 'อบรมการเขียนโปรแกรม Python',
                type: 'กิจกรรมวิชาการ',
                typeId: 1,
                date: '2025-01-05',
                hours: 6,
                semester: '2/2567',
                year: '2567',
                activityCategory: 'academic',
                status: 'เข้าร่วมแล้ว'
            },
            {
                id: 202,
                name: 'กิจกรรมวันไหว้ครู',
                type: 'กิจกรรมทั่วไป',
                typeId: 2,
                date: '2024-07-18',
                hours: 3,
                semester: '1/2567',
                year: '2567',
                activityCategory: 'general',
                status: 'เข้าร่วมแล้ว'
            },
            {
                id: 203,
                name: 'กิจกรรมกีฬาสีภายใน',
                type: 'กิจกรรมทั่วไป',
                typeId: 2,
                date: '2024-08-15',
                hours: 6,
                semester: '1/2567',
                year: '2567',
                activityCategory: 'general',
                status: 'เข้าร่วมแล้ว'
            },
            {
                id: 204,
                name: 'อบรมการใช้งาน Git และ GitHub',
                type: 'กิจกรรมวิชาการ',
                typeId: 1,
                date: '2024-09-05',
                hours: 3,
                semester: '1/2567',
                year: '2567',
                activityCategory: 'academic',
                status: 'เข้าร่วมแล้ว'
            },
            {
                id: 205,
                name: 'กิจกรรมบำเพ็ญประโยชน์ ปลูกป่าชายเลน',
                type: 'กิจกรรมทั่วไป',
                typeId: 2,
                date: '2024-11-15',
                hours: 6,
                semester: '2/2567',
                year: '2567',
                activityCategory: 'general',
                status: 'เข้าร่วมแล้ว'
            }
        ]
    };

    // กำหนดค่าข้อมูลนักศึกษาลงในฟอร์ม
    $('#request-date').text('วันที่ส่งคำร้อง: ' + requestData.requestDate);
    $('#student-id').val(requestData.studentId);
    $('#student-name').val(requestData.studentName);
    $('#student-plan').val(requestData.studentPlan);
    $('#student-major').val(requestData.studentMajor);
    $('#student-advisor').val(requestData.studentAdvisor);
    
    // โหลดเกณฑ์การเทียบชั่วโมงตามหลักสูตรของนักศึกษา
    loadCriteriaByPlan(requestData.planCode);
    
    // โหลดข้อมูลกิจกรรมที่เคยเข้าร่วม (ในกรณีจริงเราจะดึงข้อมูลจากฐานข้อมูล)
    // ในที่นี้เราได้กำหนดข้อมูลตัวอย่างไว้แล้วในโค้ด HTML
    
    // โหลดข้อมูลกิจกรรมแรกโดยอัตโนมัติ
    const firstActivityId = $('.activity-item:first').data('activity-id');
    loadSelectedActivityDetails(firstActivityId);
}

// บันทึกผลการพิจารณา
function approveRequest() {
    const studentId = $('#student-id').val();
    const studentName = $('#student-name').val();
    const activityName = $('#selected-activity-name').val();
    const criteria = $('#equivalence-criteria').val();
    const category = $('#activity-category').val();
    const status = $('#review-status').val();
    
    if (!criteria) {
        alert('กรุณาเลือกเกณฑ์การเทียบชั่วโมง');
        return;
    }
    
    if (!status) {
        alert('กรุณาเลือกผลการพิจารณา');
        return;
    }
    
    // ข้อความแจ้งผลการบันทึก
    let message = status === 'approved' ? 'อนุมัติ' : 'ไม่อนุมัติ';
    let activities = $('#equivalent-activities').val();
    let hours = $('#equivalent-hours').val();
    let categoryText = category === 'academic' ? 'กิจกรรมวิชาการ (ข้อ 3.1)' : 'กิจกรรมทั่วไป (ข้อ 3.2)';
    
    alert(`บันทึกผลการพิจารณาเรียบร้อยแล้ว\n\nนักศึกษา: ${studentName}\nกิจกรรม: ${activityName}\nผลการพิจารณา: ${message}\nเทียบเป็น: ${activities} กิจกรรม ${hours} ชั่วโมง\nประเภทกิจกรรม: ${categoryText}`);
    
    // ปิดโมดัล
    $('#viewRequestModal').modal('hide');
}

// Event handler เมื่อเปลี่ยนเกณฑ์การเทียบ
$(document).on('change', '#equivalence-criteria', function() {
    updateEquivalenceValues();
});

// โหลดข้อมูลกิจกรรมที่เลือก
function loadSelectedActivityDetails(activityId) {
    // ในระบบจริงควรใช้ AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
    // ตัวอย่างข้อมูลสำหรับการพัฒนา
    let activityData;
    
    if (activityId === 201) {
        activityData = {
            id: 201,
            name: 'อบรมการเขียนโปรแกรม Python',
            type: 'กิจกรรมวิชาการ',
            typeId: 1,
            date: '2025-01-05',
            hours: 6,
            semester: '2/2567',
            year: '2567',
            activityCategory: 'academic'
        };
    } else if (activityId === 202) {
        activityData = {
            id: 202,
            name: 'กิจกรรมวันไหว้ครู',
            type: 'กิจกรรมทั่วไป',
            typeId: 2,
            date: '2024-07-18',
            hours: 3,
            semester: '1/2567',
            year: '2567',
            activityCategory: 'general'
        };
    } else if (activityId === 203) {
        activityData = {
            id: 203,
            name: 'กิจกรรมกีฬาสีภายใน',
            type: 'กิจกรรมทั่วไป',
            typeId: 2,
            date: '2024-08-15',
            hours: 6,
            semester: '1/2567',
            year: '2567',
            activityCategory: 'general'
        };
    } else if (activityId === 204) {
        activityData = {
            id: 204,
            name: 'อบรมการใช้งาน Git และ GitHub',
            type: 'กิจกรรมวิชาการ',
            typeId: 1,
            date: '2024-09-05',
            hours: 3,
            semester: '1/2567',
            year: '2567',
            activityCategory: 'academic'
        };
    } else if (activityId === 205) {
        activityData = {
            id: 205,
            name: 'กิจกรรมบำเพ็ญประโยชน์ ปลูกป่าชายเลน',
            type: 'กิจกรรมทั่วไป',
            typeId: 2,
            date: '2024-11-15',
            hours: 6,
            semester: '2/2567',
            year: '2567',
            activityCategory: 'general'
        };
    }
    
    if (activityData) {
        // แสดงข้อมูลกิจกรรมที่เลือก
        $('#selected-activity-id').val(activityData.id);
        $('#selected-activity-name').val(activityData.name);
        $('#selected-activity-type').val(activityData.type);
        $('#selected-activity-date').val(activityData.date);
        $('#selected-activity-hours').val(activityData.hours);
        $('#selected-activity-semester').val(activityData.semester);
        $('#selected-activity-year').val(activityData.year);
        
        // ปรับค่าในฟอร์มเทียบชั่วโมง
        $('#activity-category').val(activityData.activityCategory);
        
        // เราจะเลือกเกณฑ์ที่เหมาะสมกับกิจกรรมนี้
        // หากเป็นกิจกรรมวิชาการ จะเลือกเกณฑ์ ช่วยกิจกรรม
        if (activityData.typeId === 1) { // กิจกรรมวิชาการ
            $('#equivalence-criteria').val(6); // เกณฑ์ช่วยกิจกรรม
        } else {
            $('#equivalence-criteria').val(6); // เกณฑ์ช่วยกิจกรรม
        }
        
        // อัพเดทค่าการเทียบ
        updateEquivalenceValues(activityData);
    }
}

// โหลดเกณฑ์การเทียบชั่วโมงตามหลักสูตรของนักศึกษา
function loadCriteriaByPlan(planId, activityType) {
    // ในระบบจริงควรใช้ AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
    // ตัวอย่างข้อมูลสำหรับการพัฒนา
    const criteriaData = {
        "IT": [ // IT (ปกติ 4 ปี)
            { id: 1, name: "นายกสโมสรนักศึกษา", act_amount: 2, act_hour: 12 },
            { id: 2, name: "คณะกรรมการนักศึกษา", act_amount: 1, act_hour: 8 },
            { id: 3, name: "รางวัลจากการประกวดผลงาน", act_amount: 1, act_hour: 3 },
            { id: 4, name: "รางวัลชนะเลิศระดับประเทศ", act_amount: 2, act_hour: 6 },
            { id: 5, name: "รางวัลชนะเลิศระดับนานาชาติ", act_amount: 4, act_hour: 12 },
            { id: 6, name: "ช่วยกิจกรรม", act_amount: 1, act_hour: 0 } // ตามชั่วโมงจริง
        ],
        "ITR": [ // ITR (ปกติ เทียบโอน)
            { id: 7, name: "นายกสโมสรนักศึกษา", act_amount: 2, act_hour: 12 },
            { id: 8, name: "คณะกรรมการนักศึกษา", act_amount: 1, act_hour: 8 },
            { id: 9, name: "รางวัลจากการประกวดผลงาน", act_amount: 1, act_hour: 3 },
            { id: 10, name: "รางวัลชนะเลิศระดับประเทศ", act_amount: 2, act_hour: 6 },
            { id: 11, name: "ช่วยกิจกรรม", act_amount: 1, act_hour: 0 } // ตามชั่วโมงจริง
        ],
        "IT4S": [ // IT4S (อาทิตย์ 4 ปี)
            { id: 12, name: "นายกสโมสรนักศึกษา", act_amount: 2, act_hour: 12 },
            { id: 13, name: "คณะกรรมการนักศึกษา", act_amount: 1, act_hour: 8 },
            { id: 14, name: "รางวัลจากการประกวดผลงาน", act_amount: 1, act_hour: 3 },
            { id: 15, name: "ช่วยกิจกรรม", act_amount: 1, act_hour: 0 } // ตามชั่วโมงจริง
        ],
        "ITS": [ // ITS (อาทิตย์ เทียบโอน)
            { id: 16, name: "นายกสโมสรนักศึกษา", act_amount: 2, act_hour: 9 },
            { id: 17, name: "คณะกรรมการนักศึกษา", act_amount: 1, act_hour: 6 },
            { id: 18, name: "รางวัลจากการประกวดผลงาน", act_amount: 1, act_hour: 3 },
            { id: 19, name: "ช่วยกิจกรรม", act_amount: 1, act_hour: 0 } // ตามชั่วโมงจริง
        ]
    };

    // ดึงหลักสูตรจากชื่อหลักสูตร
    let plan = "IT"; // ค่าเริ่มต้น
    if (planId && typeof planId === 'string') {
        if (planId.includes("ITR")) plan = "ITR";
        else if (planId.includes("IT4S")) plan = "IT4S";
        else if (planId.includes("ITS")) plan = "ITS";
    }

    // แสดง badge หลักสูตร
    $('#student-plan-badge').text(planId || 'IT (ปกติ 4 ปี)');

    // ดึงข้อมูลเกณฑ์ตามหลักสูตร
    const criteria = criteriaData[plan] || criteriaData["IT"];
    
    // เพิ่มตัวเลือกลงใน select
    const criteriaSelect = $('#equivalence-criteria');
    criteriaSelect.empty();
    criteriaSelect.append('<option value="">-- เลือกเกณฑ์การเทียบ --</option>');
    
    criteria.forEach(crit => {
        let hourText = crit.act_hour > 0 ? `${crit.act_hour} ชั่วโมง` : "ตามชั่วโมงจริง";
        let optionText = `${crit.name} (${crit.act_amount} กิจกรรม, ${hourText})`;
        let selected = '';
        
        // ถ้ามีประเภทกิจกรรมส่งมา ให้เลือกตามประเภท
        if (activityType) {
            if (crit.name.toLowerCase().includes(activityType.toLowerCase())) {
                selected = 'selected';
            }
        }
        
        criteriaSelect.append(`<option value="${crit.id}" data-amount="${crit.act_amount}" data-hour="${crit.act_hour}" ${selected}>${optionText}</option>`);
    });
</script>

<?php require_once 'includes/tablejs.php' ?>