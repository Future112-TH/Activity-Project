<?php
if(isset($_GET['menu'])){
    $menu = $_GET['menu'];
}else{
    $menu = 1;
}

?>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php?menu=1" class="brand-link text-center">
        <span class="brand-text font-weight-light">TRU Activity System</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                <li class="nav-item">
                    <a href="index.php?menu=1" class="nav-link <?php if($menu==1){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="index.php?menu=2" class="nav-link <?php if($menu==2){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            จัดการข้อมูลส่วนตัว
                        </p>
                    </a>
                </li>

                <!-- <li class="nav-item">
                    <a href="index.php?menu=1" class="nav-link <?php if($menu==3){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-key"></i>
                        <p>
                            แก้ไขรหัสผ่าน
                        </p>
                    </a>
                </li> -->

                <li class="nav-item">
                    <a href="index.php?menu=4" class="nav-link <?php if($menu==4){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-user-graduate"></i>
                        <p>
                            ข้อมูลนักศึกษา
                        </p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="logout.php" class="nav-link"> 
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Log out
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>