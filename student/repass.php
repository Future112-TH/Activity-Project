<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-key mr-2"></i>แก้ไขรหัสผ่าน</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php?menu=1">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">แก้ไขรหัสผ่าน</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- แสดงข้อความแจ้งเตือนถ้ามี -->
            <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-6 mx-auto">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">แก้ไขรหัสผ่าน</h3>
                        </div>
                        <!-- /.card-header -->
                        
                        <!-- form start -->
                        <form id="changePasswordForm" action="process/process_repass.php" method="post">
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="current_password">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="form-group">
                                    <label for="new_password">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <small class="form-text text-muted">รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร</small>
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                            <!-- /.card-body -->

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">เปลี่ยนรหัสผ่าน</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>

<script>
$(document).ready(function() {
    // ตรวจสอบความถูกต้องของฟอร์ม
    $('#changePasswordForm').on('submit', function(e) {
        const newPassword = $('#new_password').val();
        const confirmPassword = $('#confirm_password').val();
        
        // ตรวจสอบความยาวของรหัสผ่าน
        if (newPassword.length < 8) {
            e.preventDefault();
            alert('รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร');
            return false;
        }
        
        // ตรวจสอบว่ารหัสผ่านใหม่และการยืนยันตรงกัน
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('รหัสผ่านใหม่และการยืนยันรหัสผ่านไม่ตรงกัน');
            return false;
        }
        
        return true;
    });
});
</script>