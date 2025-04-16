<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-alt mr-2"></i>ออกเอกสารใบรับ Transcript</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ออกเอกสารใบรับ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <!-- ปุ่มออกรายงาน -->
            <div class="mb-3">
                <button type="button" class="btn btn-success" onclick="generateBulkTranscripts()">
                    <i class="fas fa-file-pdf mr-1"></i> ออกใบรับรองทั้งหมด
                </button>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-1"></i>
                                รายการนักศึกษา
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="table1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th width="10%">รหัสนักศึกษา</th>
                                            <th width="25%">ชื่อ - นามสกุล</th>
                                            <th width="15%">คณะ</th>
                                            <th width="15%">สาขาวิชา</th>
                                            <th width="10%">ประเภท</th>
                                            <th width="10%">สถานะ</th>
                                            <th width="15%">ออกใบรับ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>6501103071001</td>
                                            <td>นายสมชาย ใจดี</td>
                                            <td>วิทยาศาสตร์และเทคโนโลยี</td>
                                            <td>วิทยาการข้อมูลฯ</td>
                                            <td><span class="badge badge-primary">IT</span></td>
                                            <td><span class="badge badge-warning">รอครบ</span></td>
                                            <td>
                                                <a href="generate_transcript.php?id=6501103071001" class="btn btn-success btn-sm">
                                                    <i class="fas fa-file-pdf"></i> ดาวน์โหลด PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6501103071002</td>
                                            <td>นางสาวสมหญิง รักเรียน</td>
                                            <td>วิทยาศาสตร์และเทคโนโลยี</td>
                                            <td>วิทยาการข้อมูลฯ</td>
                                            <td><span class="badge badge-primary">IT</span></td>
                                            <td><span class="badge badge-success">ครบตามเกณฑ์</span></td>
                                            <td>
                                                <a href="generate_transcript.php?id=6501103071002" class="btn btn-success btn-sm">
                                                    <i class="fas fa-file-pdf"></i> ดาวน์โหลด PDF
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>6501103071003</td>
                                            <td>นายมานะ ตั้งใจ</td>
                                            <td>วิทยาศาสตร์และเทคโนโลยี</td>
                                            <td>วิทยาการข้อมูลฯ</td>
                                            <td><span class="badge badge-secondary">ITR</span></td>
                                            <td><span class="badge badge-success">ครบตามเกณฑ์</span></td>
                                            <td>
                                                <a href="generate_transcript.php?id=6501103071003" class="btn btn-success btn-sm">
                                                    <i class="fas fa-file-pdf"></i> ดาวน์โหลด PDF
                                                </a>
                                            </td>
                                        </tr>
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

<script>
// ออกใบรับรองทั้งหมด
function generateBulkTranscripts() {
    alert('กำลังสร้างใบรับรองทั้งหมดสำหรับนักศึกษาที่มีคุณสมบัติครบตามเกณฑ์...');
    // ในระบบจริง จะส่งคำขอไปยัง API เพื่อสร้างใบรับรองทั้งหมด
}
</script>

<?php require_once 'includes/tablejs.php' ?>