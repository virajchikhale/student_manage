<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
$me = admin_header('People', 'users');

$groups = [
    'admin'     => "SELECT id, first_name, last_name, email, phone, NULL AS dept, NULL AS boss FROM admin_reg ORDER BY id",
    'principal' => "SELECT id, first_name, last_name, email, phone, NULL AS dept, NULL AS boss FROM principal_reg ORDER BY id",
    'hod'       => "SELECT h.id, h.first_name, h.last_name, h.email, h.phone, d.name AS dept,
                           CONCAT(p.first_name, ' ', p.last_name) AS boss
                    FROM hod_reg h LEFT JOIN department d ON d.id = h.department_id
                    LEFT JOIN principal_reg p ON p.id = h.report_to ORDER BY h.id",
    'teacher'   => "SELECT t.id, t.first_name, t.last_name, t.email, t.phone, d.name AS dept,
                           CONCAT(h.first_name, ' ', h.last_name) AS boss
                    FROM teacher_reg t LEFT JOIN department d ON d.id = t.department_id
                    LEFT JOIN hod_reg h ON h.id = t.report_to ORDER BY t.id",
];
foreach ($groups as $role => $sql) {
    $rows = db()->query($sql)->fetchAll(); ?>
<h4 class="m-t-20 m-b-10"><?php echo e(ROLES[$role]['label']); ?>s (<?php echo count($rows); ?>)</h4>
<div class="table-responsive"><table class="table table-bordered table-sm bg-white">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Department</th><th>Reports to</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) { ?>
        <tr>
            <td><?php echo e($r['first_name'] . ' ' . $r['last_name']); ?></td>
            <td><?php echo e($r['email']); ?></td>
            <td><?php echo e($r['phone']); ?></td>
            <td><?php echo e($r['dept'] ?? ''); ?></td>
            <td><?php echo e($r['boss'] ?? ''); ?></td>
            <td><?php if (!($role === 'admin' && (int) $r['id'] === (int) $me['id'])) { ?>
                <button class="btn btn-sm btn-outline-danger"
                    onclick="confirm('Delete this account?') && Auth.admin('delete_user', {role: '<?php echo $role; ?>', id: <?php echo (int) $r['id']; ?>})">delete</button>
            <?php } ?></td>
        </tr>
    <?php } ?>
    <?php if (!$rows) { ?><tr><td colspan="6" class="text-muted">None yet.</td></tr><?php } ?>
    </tbody>
</table></div>
<?php }
admin_footer();
