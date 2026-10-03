<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me = current_user();
$isAdmin = $me && $me['role'] === 'admin';
if (!admin_signup_open()) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Registration</title>
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
                            <form action="" method="post" onsubmit="return false">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>First Name</label>
                                            <input class="au-input au-input--full" type="text" id="first_name" placeholder="First Name" maxlength="100">
                                        </div>
                                        <div class="form-group">
                                            <label>Last Name</label>
                                            <input class="au-input au-input--full" type="text" id="last_name" placeholder="Last Name" maxlength="100">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Phone Number</label>
                                            <input class="au-input au-input--full" type="text" id="phoneno" onchange="checkmobno()" placeholder="Phone Number">
                                        </div>
                                        <div class="form-group">
                                            <label>Email Address</label>
                                            <input type="email" class="au-input au-input--full" id="email" onchange="emailvalid()" placeholder="Email Address" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>OTP <small class="text-muted">(emailed when you enter your email)</small></label>
                                    <input class="au-input au-input--full" type="text" id="otp" inputmode="numeric" maxlength="6" placeholder="Enter OTP">
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Password</label>
                                            <input class="au-input au-input--full" type="password" id="password" onchange="passvalid()" placeholder="Password (min 8)" autocomplete="new-password">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Confirm Password</label>
                                            <input class="au-input au-input--full" type="password" id="cpassword" onchange="passcon()" placeholder="Confirm Password" autocomplete="new-password">
                                        </div>
                                    </div>
                                </div>
                                <button type="button" id="test" onclick="response()" class="au-btn au-btn--block au-btn--green m-b-20">Register</button>
                            </form>
                            <div class="register-link">
                                <p><?php echo $isAdmin ? '<a href="dashboard.php">Back to dashboard</a>' : 'Already have an account? <a href="index.php">Sign In</a>'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendor/jquery-3.2.1.min.js"></script>
    <script src="../includes/js/auth.js"></script>
    <script>Auth.initRegister('admin');</script>
</body>
</html>
