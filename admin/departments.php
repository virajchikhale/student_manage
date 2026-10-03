<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/admin_includes/layout.php';
admin_header('Departments', 'departments');

$rows = db()->query(
    "SELECT d.id, d.name, NULLIF(TRIM(CONCAT(h.first_name, ' ', h.last_name)), '') AS hod,
            (SELECT COUNT(*) FROM teacher_reg t WHERE t.department_id = d.id) AS teachers,
            (SELECT COUNT(*) FROM student s WHERE s.department_id = d.id) AS students,
            (SELECT COUNT(*) FROM course c WHERE c.department_id = d.id) AS courses
     FROM department d LEFT JOIN hod_reg h ON h.department_id = d.id ORDER BY d.name"
)->fetchAll();
?>
<div class="card"><div class="card-body">
    <form class="form-inline" data-api="admin" data-action="add_department" data-ok="Department added">
        <input class="form-control mr-2 mb-2 mb-sm-0" name="name" placeholder="New department name" maxlength="255" required style="min-width:260px">
        <button class="btn btn-primary" type="submit"><i class="fas fa-plus mr-1"></i> Add department</button>
    </form>
</div></div>
<div class="card">
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Department</th><th>HOD</th><th class="num">Teachers</th><th class="num">Students</th><th class="num">Courses</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r) { ?>
            <tr>
                <td><b><?php echo e($r['name']); ?></b></td>
                <td><?php echo $r['hod'] ? e($r['hod']) : '<span class="text-muted">none</span>'; ?></td>
                <td class="num"><?php echo (int) $r['teachers']; ?></td>
                <td class="num"><?php echo (int) $r['students']; ?></td>
                <td class="num"><?php echo (int) $r['courses']; ?></td>
                <td class="row-actions"><?php if (!$r['hod']) { ?>
                    <button class="btn btn-light btn-icon text-danger" title="Delete" data-api="admin" data-action="delete_department"
                        data-id="<?php echo (int) $r['id']; ?>" data-ok="Department deleted"
                        data-confirm="Delete <?php echo e($r['name']); ?>? Its courses are deleted too."><i class="far fa-trash-alt"></i></button>
                <?php } ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table></div>
</div>
<?php admin_footer();
