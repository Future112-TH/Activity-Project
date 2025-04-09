<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">📊 Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Summary Cards -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>1,200</h3>
                            <p>นักศึกษา</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>50</h3>
                            <p>กิจกรรมที่จัด</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-alt"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>4,800</h3>
                            <p>ชั่วโมงกิจกรรมรวม</p>
                        </div>
                        <div class="icon"><i class="fas fa-clock"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>20</h3>
                            <p>อาจารย์ที่ปรึกษา</p>
                        </div>
                        <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">📊 กิจกรรมที่จัดในแต่ละเดือน</h3></div>
                        <div class="card-body"><canvas id="activityChart"></canvas></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">🎯 ประเภทกิจกรรมยอดนิยม</h3></div>
                        <div class="card-body"><canvas id="categoryChart"></canvas></div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Table -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header"><h3 class="card-title">📌 กิจกรรมล่าสุด</h3></div>
                        <div class="card-body">
                            <table id="recentActivityTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ชื่อกิจกรรม</th>
                                        <th>วันที่จัด</th>
                                        <th>ชั่วโมงกิจกรรม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>โครงการปลูกป่า</td>
                                        <td>10 ก.พ. 2025</td>
                                        <td>3 ชั่วโมง</td>
                                    </tr>
                                    <tr>
                                        <td>ค่ายอาสาพัฒนาชนบท</td>
                                        <td>5 ก.พ. 2025</td>
                                        <td>5 ชั่วโมง</td>
                                    </tr>
                                    <tr>
                                        <td>อบรมผู้นำนักศึกษา</td>
                                        <td>1 ก.พ. 2025</td>
                                        <td>4 ชั่วโมง</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // กราฟแท่ง: จำนวนกิจกรรมในแต่ละเดือน
        var ctx1 = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                datasets: [{
                    label: 'จำนวนกิจกรรม',
                    data: [5, 10, 8, 15, 12, 9],
                    backgroundColor: 'rgba(54, 162, 235, 0.7)'
                }]
            }
        });

        // กราฟวงกลม: ประเภทกิจกรรมยอดนิยม
        var ctx2 = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: ['จิตอาสา', 'กีฬา', 'วิชาการ', 'บำเพ็ญประโยชน์'],
                datasets: [{
                    data: [40, 25, 20, 15],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            }
        });

        // DataTable
        $(document).ready(function() {
            $('#recentActivityTable').DataTable();
        });
    </script>
</div>