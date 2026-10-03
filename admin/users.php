<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
$me = admin_header('People', 'users');

$groups = [
    'admin'     => ['fa-cog',  "SELECT id, first_name, last_name, email, phone, NULL AS dept, NULL AS boss FROM admin_reg ORDER BY id"],
    'principal' => ['fa-briefcase',  "SELECT id, first_name, last_name, email, phone, NULL AS dept, NULL AS boss FROM principal_reg ORDER BY id"],
    'hod'       => ['fa-shield-alt', "SELECT h.id, h.first_name, h.last_name, h.email, h.phone, d.name AS dept,
                           NULLIF(TRIM(CONCAT(p.first_name, ' ', p.last_name)), '') AS boss
                    FROM hod_reg h LEFT JOIN department d ON d.id = h.department_id
                    LEFT JOIN principal_reg p ON p.id = h.report_to ORDER BY h.id"],
    'teacher'   => ['fa-id-badge', "SELECT t.id, t.first_name, t.last_name, t.email, t.phone, d.name AS dept,
                           NULLIF(TRIM(CONCAT(h.first_name, ' ', h.last_name)), '') AS boss
                    FROM teacher_reg t LEFT JOIN department d ON d.id = t.department_id
                    LEFT JOIN hod_reg h ON h.id = t.report_to ORDER BY t.id"],
];
?>
<p class="lead-text">Everyone who can sign in to the system. Students are managed under <a href="<?php echo e(url('app/students.php')); ?>">Students</a>.</p>
<?php foreach ($groups as $role => [$icon, $sql]) {
    $rows = db()->query($sql)->fetchAll(); ?>
<div class="card">
    <div class="card-head"><h5><i class="fas <?php echo $icon; ?> mr-2 text-muted"></i><?php echo e(ROLES[$role]['label']); ?>s</h5><small><?php echo count($rows); ?></small></div>
    <?php if (!$rows) { echo empty_state($icon, 'None yet'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Name</th><th>Phone</th><th>Department</th><th>Reports to</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r) { $name = full_name($r); ?>
            <tr>
                <td><div class="person"><?php echo avatar($name); ?><div><?php echo e($name); ?><small><?php echo e($r['email']); ?></small></div></div></td>
                <td><?php echo e($r['phone']); ?></td>
                <td><?php echo e($r['dept'] ?? '—'); ?></td>
                <td><?php echo e(trim((string) ($r['boss'] ?? '')) ?: '—'); ?></td>
                <td class="row-actions"><?php if (!($role === 'admin' && (int) $r['id'] === (int) $me['id'])) { ?>
                    <button class="btn btn-light btn-icon text-danger" title="Delete" data-api="admin" data-action="delete_user"
                        data-payload="<?php echo e(json_encode(['role' => $role])); ?>" data-id="<?php echo (int) $r['id']; ?>"
                        data-ok="Account deleted" data-confirm="Delete the account of <?php echo e($name); ?>?"><i class="far fa-trash-alt"></i></button>
                <?php } else { echo badge('You', 'brand'); } ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table></div>
    <?php } ?>
</div>
<?php }
admin_footer();
