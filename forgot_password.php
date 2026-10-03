<?php
require_once __DIR__ . '/includes/bootstrap.php';
$role = $_GET['role'] ?? 'admin';
if (!isset(ROLES[$role])) {
    $role = 'admin';
}
$label = ROLES[$role]['label'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($label); ?> - Forgot Password</title>
    <link href="admin/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <?php echo head_meta(); ?>
    <style>
        body { background: #f1f3f6; }
        .card { max-width: 420px; margin: 8vh auto; border: 0; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
        #otp_box, #pass_box { display: none; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-body p-4">
        <h4 class="mb-1"><?php echo e($label); ?> password reset</h4>
        <p class="text-muted small">Enter your email and we will send you a one-time password.</p>
        <?php echo demo_banner($role); ?>
        <div class="form-group">
            <label for="email">Email address</label>
            <input class="form-control" type="email" id="email" placeholder="Email" autocomplete="email">
        </div>
        <button type="button" class="btn btn-success btn-block mb-3" id="send_otp">Send OTP</button>
        <div id="otp_box" class="form-group">
            <label for="otpin">OTP</label>
            <input class="form-control" type="text" id="otpin" inputmode="numeric" maxlength="6" placeholder="6-digit OTP" autocomplete="one-time-code">
        </div>
        <div id="pass_box">
            <div class="form-group">
                <label for="password">New password</label>
                <input class="form-control" type="password" id="password" minlength="8" autocomplete="new-password">
            </div>
            <div class="form-group">
                <label for="cpassword">Confirm password</label>
                <input class="form-control" type="password" id="cpassword" minlength="8" autocomplete="new-password">
            </div>
            <button type="button" class="btn btn-primary btn-block" id="reset">Change password</button>
        </div>
        <p class="text-center mt-3 mb-0 small"><a href="<?php echo e(url(ROLES[$role]['login'])); ?>">Back to sign in</a></p>
    </div>
</div>
<script src="includes/js/jquery-3.3.1.min.js"></script>
<script src="includes/js/auth.js"></script>
<script>Auth.initForgot(<?php echo json_encode($role); ?>);</script>
</body>
</html>
