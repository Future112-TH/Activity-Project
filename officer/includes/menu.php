<?php
if(isset($_GET['menu'])){
    $menu = $_GET['menu'];
}else{
    $menu = 2;
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
                <!-- <li class="nav-item">
                    <a href="index.php?menu=1" class="nav-link <?php if($menu==1){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-users-cog"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li> -->

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

                <li
                    class="nav-item <?php if($menu==4 || $menu==5 || $menu==6 || $menu==7 || $menu==8 || $menu==9){echo 'menu-open';} ?>">
                    <a href="#"
                        class="nav-link <?php if($menu==4 || $menu==5 || $menu==6 || $menu==7 || $menu==8 || $menu==9){echo 'active';} ?>">
                        <!-- <i class="nav-icon fas fa-tachometer-alt"></i> -->
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>
                            จัดการข้อมูลพื้นฐาน
                            <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?menu=4" class="nav-link <?php if($menu==4){echo 'active'; } ?>">
                                <p> </p><i class="nav-icon far fa-circle"></i>
                                <p>
                                    คณะหรือหน่วยงาน
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?menu=5" class="nav-link <?php if($menu==5){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    สาขาวิชา
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?menu=6" class="nav-link <?php if($menu==6){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    อาจารย์ที่ปรึกษา
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?menu=7" class="nav-link <?php if($menu==7){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    ข้อมูลนักศึกษา
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?menu=8" class="nav-link <?php if($menu==8){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    หลักเกณฑ์กิจกรรม
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?menu=9" class="nav-link <?php if($menu==9){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    ประเภทกิจกรรม
                                </p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="index.php?menu=10" class="nav-link <?php if($menu==10){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-calendar-alt mr-2"></i>
                        <p>
                            ข้อมูลกิจกรรม
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="index.php?menu=11" class="nav-link <?php if($menu==11){echo 'active'; } ?>">
                        <i class="nav-icon fas fa-exchange-alt mr-2"></i>
                        <p>
                            เทียบชั่วโมงกิจกรรม
                        </p>
                    </a>
                </li>
                <li class="nav-item <?php if($menu==12 || $menu==13){echo 'menu-open';} ?>">
                    <a href="#" class="nav-link <?php if($menu==12 || $menu==13){echo 'active';} ?>">
                        <i class="nav-icon fas fa-print"></i>
                        <p>
                            ออกเอกสารต่างๆ
                        <i class="right fas fa-angle-left"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="index.php?menu=12" class="nav-link <?php if($menu==12){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    รายงานผลการเข้าร่วม
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="index.php?menu=13" class="nav-link <?php if($menu==13){echo 'active'; } ?>">
                                <i class="nav-icon far fa-circle"></i>
                                <p>
                                    เอกสารใบรับ
                                </p>
                            </a>
                        </li>
                    </ul>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link"> 
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>
                            Logout
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>