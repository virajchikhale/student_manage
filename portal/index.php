<?php
// Landing page after sign-in for principals, HODs, teachers and students.
require_once __DIR__ . '/../includes/bootstrap.php';
$u0 = current_user();
if ($u0 && $u0['role'] === 'admin') {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}
$me = page_header('Dashboard', 'dashboard', 'principal', 'hod', 'teacher', 'student');
if ($me['role'] === 'student') {
    dashboard_student($me);
} else {
    dashboard_staff($me);
}
page_footer();
