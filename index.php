<?php
require_once __DIR__ . '/includes/bootstrap.php';
$regs = ['admin' => 'admin/register.php', 'principal' => 'registration/principal_reg.php',
         'hod' => 'registration/hod_reg.php', 'teacher' => 'registration/teacher_reg.php'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Management</title>
    <link href="admin/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f1f3f6}.card{border:0;box-shadow:0 4px 24px rgba(0,0,0,.08)}</style>
</head>
<body>
<div class="container py-5" style="max-width:760px">
    <h1 class="mb-1">Student Management System</h1>
    <p class="text-muted">Choose your portal to sign in.</p>
    <?php if (demo_mode()) { ?>
    <div class="alert alert-warning"><b>Demo mode</b> &mdash; sample data, wiped when the demo stops. Demo password: <code><?php echo e(env('DEMO_PASSWORD', 'demo12345')); ?></code></div>
    <?php } ?>
    <div class="row">
        <?php foreach (ROLES as $key => $r) { ?>
        <div class="col-sm-6 mb-4">
            <div class="card"><div class="card-body">
                <h5><?php echo e($r['label']); ?></h5>
                <a class="btn btn-success btn-sm" href="<?php echo e($r['login']); ?>">Sign in</a>
                <?php if ($key !== 'admin') { ?><a class="btn btn-outline-secondary btn-sm" href="<?php echo e($regs[$key]); ?>">Register</a><?php } ?>
            </div></div>
        </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
