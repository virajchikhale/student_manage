<?php
require_once __DIR__ . '/../includes/bootstrap.php';
if ((current_user()['role'] ?? '') === 'admin') {
    header('Location: dashboard.php');
    exit;
}
$canSignUp = (int) db()->query('SELECT COUNT(*) FROM admin_reg')->fetchColumn() === 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Login</title>
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link href="vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="css/theme.css" rel="stylesheet" media="all">
    <?php echo head_meta(); ?>
</head>
<body class="animsition">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#"><img src="images/icon/logo.png" alt="Student Management"></a>
                        </div>
                        <div class="login-form">
                            <?php echo demo_banner('admin'); ?>
                            <form action="" method="post">
                                <div class="form-group">
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" type="email" id="email" name="email" placeholder="Email" autocomplete="username" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="au-input au-input--full" type="password" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                                </div>
                                <div id="alert" class="alert alert-danger" role="alert" style="display:none"></div>
                                <div class="login-checkbox">
                                    <label><a href="../forgot_password.php?role=admin">Forgotten Password?</a></label>
                                </div>
                                <button type="submit" id="submit" class="au-btn au-btn--block au-btn--green m-b-20">sign in</button>
                            </form>
                            <?php if ($canSignUp) { ?>
                            <div class="register-link">
                                <p>No admin yet? <a href="register.php">Create the first admin</a></p>
                            </div>
                            <?php } ?>
                            <div class="register-link">
                                <p><a href="../login/principal_reg.php">Principal</a> &middot;
                                   <a href="../login/hod_login.php">HOD</a> &middot;
                                   <a href="../login/teacher_reg.php">Teacher</a> &middot;
                                   <a href="../login/student_login.php">Student</a> login</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="../includes/js/auth.js"></script>
    <script>Auth.initLogin('admin');</script>
</body>
</html>
