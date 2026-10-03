<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Staff', 'staff', 'admin', 'principal', 'hod', 'teacher');
$pdo = db();
$scope = dept_scope($me);

function staff_rows(string $table, ?int $dept): array
{
    $st = db()->prepare(
        "SELECT x.id, x.first_name, x.last_name, x.email, x.phone, d.name AS dept FROM $table x
         LEFT JOIN department d ON d.id = x.department_id" . ($dept === null ? '' : ' WHERE x.department_id = ?')
        . ' ORDER BY d.name, x.first_name'
    );
    $st->execute($dept === null ? [] : [$dept]);
    return $st->fetchAll();
}

$groups = [];
if (institution_wide($me)) {
    $groups['Principal'] = $pdo->query('SELECT id, first_name, last_name, email, phone, NULL AS dept FROM principal_reg ORDER BY first_name')->fetchAll();
}
$groups['Heads of department'] = staff_rows('hod_reg', $scope);
$groups['Teachers'] = staff_rows('teacher_reg', $scope);
$icons = ['Principal' => 'fa-briefcase', 'Heads of department' => 'fa-shield-alt', 'Teachers' => 'fa-id-badge'];
foreach ($groups as $title => $rows) { ?>
<div class="card">
    <div class="card-head"><h5><i class="fas <?php echo $icons[$title]; ?> mr-2 text-muted"></i><?php echo e($title); ?></h5><small><?php echo count($rows); ?></small></div>
    <?php if (!$rows) { echo empty_state('fa-users', 'Nobody here yet'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Department</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r) { $name = full_name($r); ?>
            <tr><td><div class="person"><?php echo avatar($name); ?><span><?php echo e($name); ?></span></div></td>
                <td><a href="mailto:<?php echo e($r['email']); ?>"><?php echo e($r['email']); ?></a></td>
                <td><?php echo e($r['phone']); ?></td>
                <td><?php echo e($r['dept'] ?? '—'); ?></td></tr>
        <?php } ?>
        </tbody>
    </table></div>
    <?php } ?>
</div>
<?php }
page_footer();
