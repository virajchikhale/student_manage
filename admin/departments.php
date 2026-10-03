<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
admin_header('Departments', 'departments');

$rows = db()->query(
    "SELECT d.id, d.name, CONCAT(h.first_name, ' ', h.last_name) AS hod,
            (SELECT COUNT(*) FROM teacher_reg t WHERE t.department_id = d.id) AS teachers
     FROM department d LEFT JOIN hod_reg h ON h.department_id = d.id ORDER BY d.name"
)->fetchAll();
?>
<div class="card m-b-20"><div class="card-body">
    <form class="form-inline" onsubmit="Auth.admin('add_department', {name: $('#dname').val()}); return false;">
        <input class="form-control mr-2" id="dname" placeholder="New department name" maxlength="255" required>
        <button class="btn btn-success">Add department</button>
    </form>
</div></div>
<div class="table-responsive"><table class="table table-bordered bg-white">
    <thead><tr><th>Department</th><th>HOD</th><th>Teachers</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r) { ?>
        <tr>
            <td><?php echo e($r['name']); ?></td>
            <td><?php echo $r['hod'] ? e($r['hod']) : '<span class="text-muted">none</span>'; ?></td>
            <td><?php echo (int) $r['teachers']; ?></td>
            <td><?php if (!$r['hod']) { ?>
                <button class="btn btn-sm btn-outline-danger" onclick="confirm('Delete this department?') && Auth.admin('delete_department', {id: <?php echo (int) $r['id']; ?>})">delete</button>
            <?php } ?></td>
        </tr>
    <?php } ?>
    </tbody>
</table></div>
<?php admin_footer();
