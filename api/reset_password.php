<?php
// Forgot password: {role, email, otp, password}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$role  = role_param();
$email = post('email');
$pass  = $_POST['password'] ?? '';
$pass  = is_string($pass) ? $pass : '';

if (strlen($pass) < 8) {
    fail('Password must be at least 8 characters.');
}
if (!valid_email($email) || !otp_consume('forgot', $role, $email, post('otp'))) {
    fail('Invalid or expired OTP.');
}

$table = ROLES[$role]['table'];
$st = db()->prepare("UPDATE $table SET password = ? WHERE email = ?");
$st->execute([password_hash($pass, PASSWORD_DEFAULT), $email]);

send_mail($email, 'Alert from ' . env('MAIL_FROM_NAME', 'Student Management'),
    'The password for ' . e($email) . ' has been changed. If this was not you, contact your administrator.');
json_out(['ok' => true, 'redirect' => url(ROLES[$role]['login'])]);
