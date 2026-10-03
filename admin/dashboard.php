<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
$me  = admin_header('Dashboard', 'dashboard');
$pdo = db();

$counts = [];
foreach (['admin_reg' => 'Admins', 'principal_reg' => 'Principals', 'hod_reg' => 'HODs',
          'teacher_reg' => 'Teachers', 'department' => 'Departments'] as $table => $label) {
    $counts[$label] = (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
}
$codes = $pdo->query('SELECT id, principal_verification AS code FROM details ORDER BY id')->fetchAll();
?>
<div class="row">
    <?php foreach ($counts as $label => $n) { ?>
    <div class="col-6 col-lg-2 m-b-20">
        <div class="card"><div class="card-body text-center">
            <h2 class="number"><?php echo $n; ?></h2>
            <span class="text-muted"><?php echo e($label); ?></span>
        </div></div>
    </div>
    <?php } ?>
</div>

<div class="card">
    <div class="card-header"><strong>Principal verification codes</strong>
        <small class="text-muted"> &mdash; give one to a new principal; they need it to register</small></div>
    <div class="card-body">
        <ul class="list-unstyled" id="codes">
            <?php foreach ($codes as $c) { ?>
            <li class="m-b-5"><code><?php echo e($c['code']); ?></code>
                <button class="btn btn-sm btn-outline-danger ml-2" onclick="Auth.admin('delete_code', {id: <?php echo (int) $c['id']; ?>})">delete</button></li>
            <?php } ?>
            <?php if (!$codes) { ?><li class="text-muted">No codes yet.</li><?php } ?>
        </ul>
        <button class="btn btn-success btn-sm" onclick="Auth.admin('new_code')">Generate code</button>
    </div>
</div>
<?php admin_footer();
