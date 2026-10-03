<?php
require_once __DIR__ . '/includes/bootstrap.php';
$u = current_user();
if ($u) {
    header('Location: ' . url(ROLES[$u['role']]['home']));
    exit;
}
$regs = ['admin' => 'admin/register.php', 'principal' => 'registration/principal_reg.php',
         'hod' => 'registration/hod_reg.php', 'teacher' => 'registration/teacher_reg.php'];
$blurb = [
    'admin'     => ['fa-cog',  'Set up departments, accounts and verification codes.'],
    'principal' => ['fa-briefcase',  'Oversee students, courses, results and notices.'],
    'hod'       => ['fa-shield-alt', 'Run your department: students, courses and teachers.'],
    'teacher'   => ['fa-id-badge', 'Take attendance and enter marks for your classes.'],
    'student'   => ['fa-graduation-cap', 'See your attendance, results and notices.'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management</title>
    <link href="admin/css/font-face.css" rel="stylesheet">
    <link href="admin/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <link href="admin/vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet">
    <link href="includes/css/app.css" rel="stylesheet">
    <style>
        .landing-hero { background: linear-gradient(120deg, #312e81, #4f46e5 55%, #7c3aed); color: #fff; padding: 64px 0 120px; text-align: center; }
        .landing-hero .mark { width: 64px; height: 64px; border-radius: 18px; display: inline-grid; place-items: center; font-size: 28px;
            background: rgba(255,255,255,.15); margin-bottom: 18px; }
        .landing-hero h1 { color: #fff; font-size: 38px; font-weight: 700; }
        .landing-hero p { color: #c7d2fe; font-size: 16px; max-width: 560px; margin: 8px auto 0; }
        .demo-note { display: inline-block; margin-top: 22px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.25); border-radius: 999px; padding: 6px 16px; font-size: 13px; color: #fff; }
        .demo-note code { color: #fde68a; background: transparent; }
        .portals { margin-top: -70px; padding-bottom: 60px; }
        .portal { text-align: center; padding: 26px 20px; height: 100%; transition: transform .15s, box-shadow .15s; margin: 0; }
        .portal:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(79,70,229,.18); }
        .portal .ico { width: 56px; height: 56px; border-radius: 16px; display: inline-grid; place-items: center; font-size: 22px; margin-bottom: 12px; }
        .portal p { color: var(--muted); font-size: 13px; min-height: 40px; }
    </style>
</head>
<body>
<div class="landing-hero">
    <div class="container">
        <div class="mark"><i class="fas fa-graduation-cap"></i></div>
        <h1>Student Management System</h1>
        <p>Students, courses, attendance, exams and notices &mdash; for every role in your institution.</p>
        <?php if (demo_mode()) { ?>
        <div class="demo-note"><b>Demo mode</b> &mdash; sample data, wiped when the demo stops. Demo password: <code><?php echo e(env('DEMO_PASSWORD', 'demo12345')); ?></code></div>
        <?php } ?>
    </div>
</div>
<div class="container portals">
    <div class="row">
        <?php $tones = ['admin' => 'brand', 'principal' => 'info', 'hod' => 'warning', 'teacher' => 'success', 'student' => 'danger'];
        foreach (ROLES as $key => $r) { ?>
        <div class="col-sm-6 col-lg-4 mb-4">
            <div class="card portal"><div>
                <div class="ico tone-<?php echo $tones[$key]; ?>"><i class="fas <?php echo $blurb[$key][0]; ?>"></i></div>
                <h5><?php echo e($r['label']); ?></h5>
                <p><?php echo e($blurb[$key][1]); ?></p>
                <a class="btn btn-primary btn-sm" href="<?php echo e($r['login']); ?>">Sign in</a>
                <?php if (isset($regs[$key])) { ?><a class="btn btn-light btn-sm" href="<?php echo e($regs[$key]); ?>">Register</a><?php } ?>
            </div></div>
        </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
