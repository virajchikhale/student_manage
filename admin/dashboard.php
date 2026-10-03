<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
$me = admin_header('Dashboard', 'dashboard');
dashboard_staff($me);

$pdo   = db();
$people = [];
foreach (['admin_reg' => ['Admins', 'fa-cog', 'secondary'], 'principal_reg' => ['Principals', 'fa-briefcase', 'brand'],
          'hod_reg' => ['HODs', 'fa-shield-alt', 'info'], 'teacher_reg' => ['Teachers', 'fa-id-badge', 'success'],
          'department' => ['Departments', 'fa-building', 'warning']] as $table => [$label, $icon, $tone]) {
    $people[] = stat_card($icon, $label, (int) $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(), $tone);
}
$codes = $pdo->query('SELECT id, principal_verification AS code, created_at FROM details ORDER BY id')->fetchAll();
?>
<div class="grid grid-5 keep-2 mb-grid"><?php echo implode('', $people); ?></div>

<div class="card">
    <div class="card-head"><div><h5>Principal verification codes</h5><small>Give one to a new principal &mdash; they need it to register.</small></div>
        <button class="btn btn-primary btn-sm" id="newCode"><i class="fas fa-plus mr-1"></i> Generate code</button></div>
    <div class="card-body">
        <?php if (!$codes) { echo '<div class="empty-mini">No codes yet.</div>'; } else { ?>
        <div class="d-flex flex-wrap" style="gap:10px">
        <?php foreach ($codes as $c) { ?>
            <span class="secret-box d-inline-flex align-items-center" style="gap:10px;padding:8px 8px 8px 14px"><code style="font-size:15px"><?php echo e($c['code']); ?></code>
                <button class="btn btn-light btn-icon text-danger" title="Delete" data-api="admin" data-action="delete_code" data-id="<?php echo (int) $c['id']; ?>"
                    data-ok="Code deleted" data-confirm="Delete this code?"><i class="far fa-trash-alt"></i></button></span>
        <?php } ?>
        </div>
        <?php } ?>
    </div>
</div>
<script>$('#newCode').on('click', function () { App.run('admin', {action: 'new_code'}, 'Code generated'); });</script>
<?php admin_footer();
