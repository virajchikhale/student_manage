<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Students', 'students', 'admin', 'principal', 'hod', 'teacher');
$pdo = db();
$manage = can_manage($me);

$q      = trim($_GET['q'] ?? '');
$fdept  = dept_scope($me) ?? (int) ($_GET['dept'] ?? 0);
$fsem   = (int) ($_GET['sem'] ?? 0);
$fstat  = in_array($_GET['status'] ?? '', ['active', 'inactive', 'graduated'], true) ? $_GET['status'] : '';
$page   = max(1, (int) ($_GET['page'] ?? 1));
$per    = 12;

$where = [];
$args  = [];
if ($q !== '') {
    $where[] = "(s.first_name ILIKE ? OR s.last_name ILIKE ? OR CONCAT(s.first_name, ' ', s.last_name) ILIKE ? OR s.email ILIKE ? OR s.roll_no ILIKE ?)";
    array_push($args, "%$q%", "%$q%", "%$q%", "%$q%", "%$q%");
}
if ($fdept) { $where[] = 's.department_id = ?'; $args[] = $fdept; }
if ($fsem)  { $where[] = 's.semester = ?';      $args[] = $fsem; }
if ($fstat) { $where[] = 's.status = ?';        $args[] = $fstat; }
$w = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$st = $pdo->prepare("SELECT COUNT(*) FROM student s $w");
$st->execute($args);
$total = (int) $st->fetchColumn();

$st = $pdo->prepare(
    "SELECT s.*, d.name AS dept,
            (SELECT COUNT(*) FILTER (WHERE a.status IN ('P','L')) * 100.0 / NULLIF(COUNT(*), 0)
               FROM attendance a WHERE a.student_id = s.id) AS att
     FROM student s LEFT JOIN department d ON d.id = s.department_id $w
     ORDER BY s.roll_no LIMIT $per OFFSET " . (($page - 1) * $per)
);
$st->execute($args);
$rows = $st->fetchAll();

$allDepts = institution_wide($me) ? $pdo->query('SELECT id, name FROM department ORDER BY name')->fetchAll() : [];
$formDepts = departments_for($me);
$statusTone = ['active' => 'success', 'inactive' => 'secondary', 'graduated' => 'info'];
$query = array_filter(['q' => $q, 'dept' => $_GET['dept'] ?? '', 'sem' => $fsem ?: '', 'status' => $fstat]);

// Student to edit (from the profile page)
$editRow = null;
if ($manage && isset($_GET['edit'])) {
    $es = $pdo->prepare('SELECT * FROM student WHERE id = ?');
    $es->execute([(int) $_GET['edit']]);
    $r = $es->fetch();
    if ($r && can_manage_dept($me, (int) $r['department_id'])) {
        unset($r['password']);
        $editRow = $r;
    }
}
?>
<form class="page-actions filters auto-filter" method="get">
    <div class="search"><i class="fas fa-search"></i>
        <input class="form-control" name="q" value="<?php echo e($q); ?>" placeholder="Search name, email or roll no"></div>
    <?php if ($allDepts) { ?>
    <select class="custom-select" name="dept"><?php echo options(array_map(fn ($d) => [$d['id'], $d['name']], $allDepts), $fdept ?: '', 'All departments'); ?></select>
    <?php } ?>
    <select class="custom-select" name="sem"><?php
        echo options(array_map(fn ($i) => [$i, "Semester $i"], range(1, SEMESTERS)), $fsem ?: '', 'All semesters'); ?></select>
    <select class="custom-select" name="status"><?php
        echo options([['active', 'Active'], ['inactive', 'Inactive'], ['graduated', 'Graduated']], $fstat, 'Any status'); ?></select>
    <button class="d-none" type="submit">Search</button>
    <?php if ($q !== '' || $fsem || $fstat || ($allDepts && $fdept)) { ?><a class="btn btn-link" href="students.php">Clear</a><?php } ?>
    <span class="spacer"></span>
    <?php if ($manage) { ?>
    <button class="btn btn-primary" type="button" id="addStudent"><i class="fas fa-plus mr-1"></i> Add student</button>
    <?php } ?>
</form>

<div class="card">
    <?php if (!$rows) { echo empty_state('fa-graduation-cap', 'No students found',
        $manage ? 'Add your first student, or change the filters.' : 'Try different filters.'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><th>Roll no</th><th>Department</th><th>Sem</th><th>Attendance</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r) { $name = full_name($r); ?>
            <tr>
                <td><div class="person"><?php echo avatar($name); ?><div>
                    <a href="student.php?id=<?php echo (int) $r['id']; ?>"><?php echo e($name); ?></a>
                    <small><?php echo e($r['email']); ?></small></div></div></td>
                <td><code><?php echo e($r['roll_no']); ?></code></td>
                <td><?php echo e($r['dept'] ?? '—'); ?></td>
                <td><?php echo (int) $r['semester']; ?></td>
                <td><?php echo $r['att'] === null ? '<span class="text-muted">—</span>'
                    : badge(number_format((float) $r['att'], 0) . '%', attendance_tone((float) $r['att'])); ?></td>
                <td><?php echo badge(ucfirst($r['status']), $statusTone[$r['status']]); ?></td>
                <td class="row-actions">
                    <a class="btn btn-light btn-icon" title="View" href="student.php?id=<?php echo (int) $r['id']; ?>"><i class="fas fa-eye"></i></a>
                    <?php if ($manage && can_manage_dept($me, (int) $r['department_id'])) {
                        $js = $r; unset($js['password']); ?>
                    <button class="btn btn-light btn-icon js-edit" title="Edit" data-s="<?php echo e(json_encode($js)); ?>"><i class="far fa-edit"></i></button>
                    <button class="btn btn-light btn-icon text-danger" title="Delete" data-api="student" data-action="delete"
                        data-id="<?php echo (int) $r['id']; ?>" data-ok="Student deleted"
                        data-confirm="Delete <?php echo e($name); ?>? Their attendance and marks are deleted too."><i class="far fa-trash-alt"></i></button>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table></div>
    <div class="card-foot"><span>Showing <?php echo count($rows); ?> of <?php echo $total; ?> student<?php echo $total === 1 ? '' : 's'; ?></span>
        <?php echo pager($total, $page, $per, $query); ?></div>
    <?php } ?>
</div>

<?php if ($manage) { ?>
<div class="modal fade" id="studentModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <form data-api="student" data-action="save" data-reload="false">
    <div class="modal-header"><h5 class="modal-title">Add student</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <input type="hidden" name="id">
        <div class="form-row">
            <div class="form-group col-md-6"><label>First name *</label><input class="form-control" name="first_name" maxlength="100" required></div>
            <div class="form-group col-md-6"><label>Last name *</label><input class="form-control" name="last_name" maxlength="100" required></div>
            <div class="form-group col-md-6"><label>Email *</label><input class="form-control" type="email" name="email" maxlength="255" required></div>
            <div class="form-group col-md-6"><label>Phone *</label><input class="form-control" name="phone" maxlength="20" required></div>
            <div class="form-group col-md-4"><label>Roll no</label><input class="form-control" name="roll_no" maxlength="30" placeholder="Auto-generated"></div>
            <div class="form-group col-md-4"><label>Department *</label>
                <select class="custom-select" name="department_id" required><?php
                    echo options(array_map(fn ($d) => [$d['id'], $d['name']], $formDepts), $me['role'] === 'hod' ? $me['department_id'] : '', count($formDepts) === 1 ? null : 'Choose…'); ?></select></div>
            <div class="form-group col-md-2"><label>Semester *</label>
                <select class="custom-select" name="semester"><?php echo options(array_map(fn ($i) => [$i, $i], range(1, SEMESTERS)), 1); ?></select></div>
            <div class="form-group col-md-2"><label>Status</label>
                <select class="custom-select" name="status"><?php echo options([['active', 'Active'], ['inactive', 'Inactive'], ['graduated', 'Graduated']], 'active'); ?></select></div>
            <div class="form-group col-md-4"><label>Gender</label>
                <select class="custom-select" name="gender"><?php echo options([['Male', 'Male'], ['Female', 'Female'], ['Other', 'Other']], null, '—'); ?></select></div>
            <div class="form-group col-md-4"><label>Date of birth</label><input class="form-control" type="date" name="dob" max="<?php echo date('Y-m-d'); ?>"></div>
            <div class="form-group col-md-4"></div>
            <div class="form-group col-md-6"><label>Guardian name</label><input class="form-control" name="guardian_name" maxlength="150"></div>
            <div class="form-group col-md-6"><label>Guardian phone</label><input class="form-control" name="guardian_phone" maxlength="20"></div>
            <div class="form-group col-12 mb-0"><label>Address</label><textarea class="form-control" name="address" rows="2"></textarea></div>
        </div>
        <p class="form-hint mt-3 mb-0" id="newHint">A sign-in password is generated and emailed to the student. You will also see it once after saving.</p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save student</button></div>
    </form>
</div></div></div>

<div class="modal fade" id="credModal" tabindex="-1" data-backdrop="static"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
    <div class="modal-body text-center p-4">
        <div class="confirm-icon" style="background:#e8f8ee;color:#16a34a"><i class="fas fa-check"></i></div>
        <h5>Student saved</h5>
        <p class="text-muted mb-2" id="credMail"></p>
        <div class="secret-box mb-2"><small class="d-block text-muted">Temporary password</small><code id="credPass"></code></div>
        <small class="text-muted">Shown only once. The student can change it from <i>My profile</i>.</small>
    </div>
    <div class="modal-footer justify-content-center border-0 pt-0"><button class="btn btn-primary" id="credDone">Done</button></div>
</div></div></div>

<script>
$(function () {
    var deptFixed = <?php echo json_encode($me['role'] === 'hod'); ?>;
    $('#addStudent').on('click', function () { $('#newHint').show(); App.openForm('#studentModal', {}, 'Add student'); });
    $('.js-edit').on('click', function () { openEdit($(this).data('s')); });
    function openEdit(s) {
        $('#newHint').hide();
        App.openForm('#studentModal', {
            id: s.id, first_name: s.first_name, last_name: s.last_name, email: s.email, phone: s.phone, roll_no: s.roll_no,
            department_id: s.department_id, semester: s.semester, status: s.status, gender: s.gender || '', dob: s.dob || '',
            guardian_name: s.guardian_name || '', guardian_phone: s.guardian_phone || '', address: s.address || ''
        }, 'Edit student');
    }
    <?php if ($editRow) { ?>openEdit(<?php echo json_encode($editRow, JSON_HEX_TAG | JSON_HEX_AMP); ?>);<?php } ?>
    $('#studentModal form').on('app:ok', function (e, res) {
        if (res.password) {
            $('#studentModal').modal('hide');
            $('#credPass').text(res.password);
            $('#credMail').text(res.emailed ? 'Login details were emailed to the student.' : 'Email could not be sent, so give this password to the student.');
            $('#credModal').modal('show');
        } else {
            App.flash('Student updated');
            location.reload();
        }
    });
    $('#credDone').on('click', function () { App.flash('Student added'); location.reload(); });
});
</script>
<?php }
page_footer();
