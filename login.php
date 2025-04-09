<?php 
session_start();
require_once 'config/database.php';
require_once 'config/sweetalert.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    try{
        $username = $_POST['username'];
        $passwd = $_POST['passwd'];
        // เข้ารหัสรหัสผ่านด้วย MD5 แบบง่าย (หรือใช้วิธีเข้ารหัสตามที่ใช้อยู่)
        $hashed_password = md5($passwd);

        // เรียกใช้เมธอดในคลาส User เพื่อตรวจสอบการล็อกอิน
        $result = $user->getUser($username, $hashed_password);

        if(!$result){
            $alert = new SweetAlert('Warning','ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง','warning');
            echo $alert->setRedirectUrl('login.php');
        }else{
            // บันทึกข้อมูลลงใน session
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['status'] = $result['status'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
// ตรวจสอบประเภทผู้ใช้งาน
if (!empty($result['Stu_id'])) {
    // เป็นนักศึกษา
    $_SESSION['status'] = 'student';
    $_SESSION['student_id'] = $result['Stu_id'];
    
    // ดึงข้อมูลนักศึกษาเพิ่มเติม (เพิ่มโค้ดนี้)
    $student = $controller->getStudentById($result['Stu_id']);
    
    if ($student) {
        $_SESSION['fullname'] = $student['Title_name'] . ' ' . $student['Stu_fname'] . ' ' . $student['Stu_lname'];
        $_SESSION['major_id'] = $student['Maj_id'];
        $_SESSION['major_name'] = $student['Maj_name'];
        $_SESSION['faculty_name'] = $student['Fac_name'];
        $_SESSION['plan_id'] = $student['Plan_id'];
        $_SESSION['plan_name'] = $student['Plan_name'];
    } else {
        // กรณีไม่พบข้อมูลนักศึกษา
        $_SESSION['fullname'] = 'นักศึกษา'; // ใส่ค่าเริ่มต้น
    }
    $alert = new SweetAlert('ยินดีต้อนรับ','เข้าสู่ระบบสำหรับนักศึกษา','success');
    echo $alert->setRedirectUrl('student/index.php');
                
            } elseif (!empty($result['Prof_id'])) {
              // เป็นอาจารย์
              $_SESSION['status'] = 'professor';
              $_SESSION['professor_id'] = $result['Prof_id'];
              
              // กำหนดค่าเริ่มต้นแบบปลอดภัย
              $_SESSION['fullname'] = isset($result['Title_name']) && isset($result['Prof_fname']) && isset($result['Prof_lname']) ? 
                                    $result['Title_name'] . ' ' . $result['Prof_fname'] . ' ' . $result['Prof_lname'] : 'อาจารย์';
              $_SESSION['major_id'] = isset($result['Major_id']) ? $result['Major_id'] : '';
              $_SESSION['major_name'] = isset($result['Maj_name']) ? $result['Maj_name'] : '';
              $_SESSION['faculty_name'] = isset($result['Fac_name']) ? $result['Fac_name'] : '';
              
              // ตรวจสอบว่าเป็นผู้ดูแลระบบหรือไม่
              if ($result['status'] === 'admin') {
                    // เป็นผู้ดูแลระบบ
                    $_SESSION['is_admin'] = true;
                    $_SESSION['status'] = 'admin'; // ให้แน่ใจว่ากำหนดค่า status เป็น admin
                    error_log("Admin login successful. Setting is_admin to true. Session: " . print_r($_SESSION, true));
                    $alert = new SweetAlert('ยินดีต้อนรับ','เข้าสู่ระบบสำหรับผู้ดูแลระบบ','success');
                    echo $alert->setRedirectUrl('officer/index.php');
                } else {
                  // เป็นอาจารย์ที่ปรึกษา
                  $alert = new SweetAlert('ยินดีต้อนรับ','เข้าสู่ระบบสำหรับอาจารย์','success');
                  echo $alert->setRedirectUrl('advisor/index.php');
              }
          }
        }
    } catch(PDOException $e){
        echo "Error: ". $e->getMessage();
    }
}
?>

<!doctype html>
<html lang="th" data-bs-theme="auto">

<head>
    <script src="https://getbootstrap.com/docs/5.3/assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="ระบบบันทึกการเข้าร่วมกิจกรรม คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยธนบุรี">
    <title>ระบบบันทึกการเข้าร่วมกิจกรรม</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="https://getbootstrap.com/docs/5.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="img/logo.png" sizes="180x180">
    <link rel="icon" href="img/logo.png" sizes="32x32" type="image/png">
    <link rel="icon" href="img/logo.png" sizes="16x16" type="image/png">
    <meta name="theme-color" content="#712cf9">

    <!-- Custom styles for this template -->
    <link href="https://getbootstrap.com/docs/5.3/examples/sign-in/sign-in.css" rel="stylesheet">

    <style>
    .bd-placeholder-img {
        font-size: 1.125rem;
        text-anchor: middle;
        -webkit-user-select: none;
        -moz-user-select: none;
        user-select: none;
    }

    @media (min-width: 768px) {
        .bd-placeholder-img-lg {
            font-size: 3.5rem;
        }
    }

    .b-example-divider {
        width: 100%;
        height: 3rem;
        background-color: rgba(0, 0, 0, .1);
        border: solid rgba(0, 0, 0, .15);
        border-width: 1px 0;
        box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
    }

    .b-example-vr {
        flex-shrink: 0;
        width: 1.5rem;
        height: 100vh;
    }

    .bi {
        vertical-align: -.125em;
        fill: currentColor;
    }

    .nav-scroller {
        position: relative;
        z-index: 2;
        height: 2.75rem;
        overflow-y: hidden;
    }

    .nav-scroller .nav {
        display: flex;
        flex-wrap: nowrap;
        padding-bottom: 1rem;
        margin-top: -1px;
        overflow-x: auto;
        text-align: center;
        white-space: nowrap;
        -webkit-overflow-scrolling: touch;
    }

    .btn-primary {
        --bs-btn-bg: #0d6efd;
        --bs-btn-color: var(--bs-white);
        --bs-btn-border-color: #0d6efd;
        --bs-btn-hover-color: var(--bs-white);
        --bs-btn-hover-bg: #0b5ed7;
        --bs-btn-hover-border-color: #0b5ed7;
        --bs-btn-focus-shadow-rgb: 49, 132, 253;
        --bs-btn-active-color: var(--bs-btn-hover-color);
        --bs-btn-active-bg: #0a58ca;
        --bs-btn-active-border-color: #0a58ca;
    }

    .bd-mode-toggle {
        z-index: 1500;
    }

    .bd-mode-toggle .dropdown-menu .active .bi {
        display: block !important;
    }

    .logo-img {
        max-width: 150px;
        margin-bottom: 20px;
    }

    .footer-text {
        font-size: 0.9rem;
        margin-top: 30px;
        color: #6c757d;
    }
    </style>
</head>

<body class="d-flex align-items-center py-4 bg-body-tertiary">
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
        <symbol id="check2" viewBox="0 0 16 16">
            <path
                d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
        </symbol>
        <symbol id="circle-half" viewBox="0 0 16 16">
            <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z" />
        </symbol>
        <symbol id="moon-stars-fill" viewBox="0 0 16 16">
            <path
                d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z" />
            <path
                d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z" />
        </symbol>
        <symbol id="sun-fill" viewBox="0 0 16 16">
            <path
                d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z" />
        </symbol>
    </svg>

    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle">
        <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center" id="bd-theme" type="button"
            aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
            <svg class="bi my-1 theme-icon-active" width="1em" height="1em">
                <use href="#circle-half"></use>
            </svg>
            <span class="visually-hidden" id="bd-theme-text">สลับธีม</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light"
                    aria-pressed="false">
                    <svg class="bi me-2 opacity-50" width="1em" height="1em">
                        <use href="#sun-fill"></use>
                    </svg>
                    สว่าง
                    <svg class="bi ms-auto d-none" width="1em" height="1em">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark"
                    aria-pressed="false">
                    <svg class="bi me-2 opacity-50" width="1em" height="1em">
                        <use href="#moon-stars-fill"></use>
                    </svg>
                    มืด
                    <svg class="bi ms-auto d-none" width="1em" height="1em">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto"
                    aria-pressed="true">
                    <svg class="bi me-2 opacity-50" width="1em" height="1em">
                        <use href="#circle-half"></use>
                    </svg>
                    อัตโนมัติ
                    <svg class="bi ms-auto d-none" width="1em" height="1em">
                        <use href="#check2"></use>
                    </svg>
                </button>
            </li>
        </ul>
    </div>

    <main class="form-signin w-100 m-auto">
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post">
            <div class="text-center">
                <img class="logo-img" src="img/logo.png" alt="มหาวิทยาลัยธนบุรี">
            </div>
            <h1 class="h3 mb-3 fw-normal text-center">ระบบบันทึกการเข้าร่วมกิจกรรม</h1>

            <div class="form-floating">
                <input type="text" class="form-control" id="floatingInput" name="username"
                    value="<?php if($_SERVER['REQUEST_METHOD']=='POST') echo $_POST['username']; ?>"
                    placeholder="ชื่อผู้ใช้งาน">
                <label for="floatingInput">ชื่อผู้ใช้งาน</label>
            </div>
            <div class="form-floating my-1">
                <input type="password" class="form-control" id="floatingPassword" name="passwd" placeholder="รหัสผ่าน">
                <label for="floatingPassword">รหัสผ่าน</label>
            </div>

            <div class="form-check text-start my-3">
                <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault"
                    name="rememberMe">
                <label class="form-check-label" for="flexCheckDefault">
                    จดจำรหัสผ่าน
                </label>
            </div>
            <button class="btn btn-primary w-100 py-2" type="submit">เข้าสู่ระบบ</button>
        </form>
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://getbootstrap.com/docs/5.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
</body>

</html>