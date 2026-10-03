<?php
// Sends a one-time password by email. {role, email, purpose: reg|forgot}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$role    = role_param();
$email   = post('email');
$purpose = post('purpose');
if (!in_array($purpose, ['reg', 'forgot'], true)) {
    fail('Invalid request');
}
if (!valid_email($email)) {
    fail('Please enter a valid email address.');
}

// Each request costs an email, so cap them per address and per client (before the existence check,
// so the limit itself reveals nothing about which emails are registered)
rate_limit_or_fail('otp_email', $purpose . '|' . $role . '|' . $email, 5, 3600, 'Too many OTP requests for this email. Please try again in an hour.');
rate_limit_or_fail('otp_ip', client_ip(), 30, 3600, 'Too many OTP requests. Please try again later.');

$table = ROLES[$role]['table'];
$st = db()->prepare("SELECT 1 FROM $table WHERE email = ?");
$st->execute([$email]);
$exists = (bool) $st->fetchColumn();

if ($purpose === 'reg') {
    if ($role === 'admin' && !admin_signup_open()) {
        fail('Admin registration is closed.', 403);
    }
    if (identity_taken('email', $email)) {
        fail('This email already exists in the system.');
    }
} elseif (!$exists) {
    // Do not reveal which emails are registered
    json_out(['ok' => true]);
}

$otp = otp_issue($purpose, $role, $email);
$body = $purpose === 'reg'
    ? "Dear User, your OTP for sign-up confirmation is <b><u>$otp</u></b>. It is valid for 10 minutes."
    : "Dear User, your OTP for password change is <b><u>$otp</u></b>. It is valid for 10 minutes.";
$sent = send_mail($email, 'OTP for ' . ROLES[$role]['label'] . ' Confirmation', $body);

$out = ['ok' => true];
if (demo_mode()) {
    $out['demo_otp'] = $otp;       // public demo: visitors have no inbox to read
} elseif (!$sent) {
    unset($_SESSION['otp'][otp_key($purpose, $role, $email)]);
    fail('Could not send the email. Please try again later.', 502);
}
json_out($out);
