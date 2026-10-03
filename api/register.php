<?php
// Creates an account after the emailed OTP is verified server-side.
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$role  = role_param();
if ($role === 'student') {
    fail('Students are enrolled by the staff. Ask your department.', 403);
}
$first = post('fname');
$last  = post('lname');
$email = post('email');
$phone = post('phoneno');
$pass  = $_POST['password'] ?? '';
$pass  = is_string($pass) ? $pass : '';

if ($first === '' || $last === '' || mb_strlen($first) > 100 || mb_strlen($last) > 100) {
    fail('Please enter your first and last name.');
}
if (!valid_email($email)) {
    fail('Please enter a valid email address.');
}
if (!valid_phone($phone)) {
    fail('Please enter a valid phone number.');
}
if (strlen($pass) < 8) {
    fail('Password must be at least 8 characters.');
}
if ($role === 'admin' && !admin_signup_open()) {
    fail('Admin registration is closed.', 403);
}

$pdo = db();
$deptId = null;
$reportTo = null;

if ($role === 'principal') {
    $st = $pdo->prepare('SELECT 1 FROM details WHERE principal_verification = ?');
    $st->execute([post('code')]);
    if (!$st->fetchColumn()) {
        fail('Please enter a valid admin verification code.');
    }
}
if ($role === 'hod' || $role === 'teacher') {
    $deptId = (int) post('department');
    $st = $pdo->prepare('SELECT status FROM department WHERE id = ?');
    $st->execute([$deptId]);
    $dept = $st->fetch();
    if (!$dept) {
        fail('Please select a department.');
    }
}
if ($role === 'hod') {
    if ($dept['status'] != 0) {
        fail('That department already has an HOD.');
    }
    $reportTo = (int) post('report_to');
    $st = $pdo->prepare('SELECT 1 FROM principal_reg WHERE id = ?');
    $st->execute([$reportTo]);
    if (!$st->fetchColumn()) {
        fail('Please select who you report to.');
    }
}
if ($role === 'teacher') {
    $st = $pdo->prepare('SELECT id FROM hod_reg WHERE department_id = ?');
    $st->execute([$deptId]);
    $reportTo = $st->fetchColumn();
    if (!$reportTo) {
        fail('That department has no HOD yet. Please choose another department.');
    }
}

if (identity_taken('email', $email)) {
    fail('This email already exists in the system.');
}
if (identity_taken('phone', $phone)) {
    fail('This phone number already exists in the system.');
}
if (!otp_consume('reg', $role, $email, post('otp'))) {
    fail('Invalid or expired OTP.');
}

$table = ROLES[$role]['table'];
$hash  = password_hash($pass, PASSWORD_DEFAULT);
try {
    $pdo->beginTransaction();
    if ($role === 'hod' || $role === 'teacher') {
        $pdo->prepare("INSERT INTO $table (first_name, last_name, email, phone, password, report_to, department_id) VALUES (?,?,?,?,?,?,?)")
            ->execute([$first, $last, $email, $phone, $hash, $reportTo, $deptId]);
        if ($role === 'hod') {
            $pdo->prepare('UPDATE department SET status = 1 WHERE id = ?')->execute([$deptId]);
        }
    } else {
        $pdo->prepare("INSERT INTO $table (first_name, last_name, email, phone, password) VALUES (?,?,?,?,?)")
            ->execute([$first, $last, $email, $phone, $hash]);
    }
    $pdo->commit();
} catch (PDOException $ex) {
    $pdo->rollBack();
    if ($ex->getCode() === '23505') {      // lost a race on a unique key
        fail('This email, phone number or department is already taken.');
    }
    throw $ex;
}

send_mail($email, 'Welcome to ' . env('MAIL_FROM_NAME', 'Student Management'),
    'Thank you for registering as ' . ROLES[$role]['label'] . '. You can now sign in.');
json_out(['ok' => true, 'redirect' => url(ROLES[$role]['login'])]);
