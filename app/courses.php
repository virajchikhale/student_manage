<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header((current_user()['role'] ?? '') === 'student' ? 'My courses' : 'Courses', 'courses');
$pdo = db();
$manage  = can_manage($me);
$courses = courses_for($me);
$mineOnly = $me['role'] === 'teacher' && isset($_GET['mine']);
if ($mineOnly) {
    $courses = array_values(array_filter($courses, fn ($c) => (int) $c['teacher_id'] === (int) $me['id']));
}

$formDepts = $manage ? departments_for($me) : [];
$teachers = [];
if ($manage) {
    $sql = 'SELECT id, first_name, last_name, department_id FROM teacher_reg' . ($me['role'] === 'hod' ? ' WHERE department_id = ' . (int) $me['department_id'] : '') . ' ORDER BY first_name';
    $teachers = $pdo->query($sql)->fetchAll();
}
// Average attendance per course
$attAvg = [];
foreach ($pdo->query("SELECT course_id, COUNT(*) FILTER (WHERE status IN ('P','L')) * 100.0 / COUNT(*) AS p FROM attendance GROUP BY course_id") as $r) {
    $attAvg[(int) $r['course_id']] = (float) $r['p'];
}
$myAtt = $me['role'] === 'student' ? student_attendance((int) $me['id']) : [];
?>
<div class="page-actions">
    <?php if ($me['role'] === 'teacher') { ?>
    <div class="btn-group">
        <a class="btn <?php echo $mineOnly ? 'btn-light' : 'btn-primary'; ?>" href="courses.php">Department</a>
        <a class="btn <?php echo $mineOnly ? 'btn-primary' : 'btn-light'; ?>" href="courses.php?mine=1">Teaching</a>
    </div>
    <?php } ?>
    <span class="spacer"></span>
    <?php if ($manage) { ?><button class="btn btn-primary" id="addCourse"><i class="fas fa-plus mr-1"></i> Add course</button><?php } ?>
</div>

<?php if (!$courses) { ?>
<div class="card"><?php echo empty_state('fa-book', 'No courses yet', $manage ? 'Add a course to get started.' : 'Nothing to show here.'); ?></div>
<?php } else { ?>
<div class="grid grid-3">
<?php foreach ($courses as $c) {
    $teach = can_access_course($me, $c, 'teach'); ?>
    <div class="course-tile">
        <div class="d-flex justify-content-between align-items-start">
            <span class="code"><?php echo e($c['code']); ?></span>
            <span><?php echo badge('Sem ' . $c['semester'], 'brand'); ?> <?php echo badge($c['credits'] . ' cr', 'secondary'); ?></span>
        </div>
        <h6><?php echo e($c['name']); ?></h6>
        <div class="meta"><i class="fas fa-building mr-1"></i><?php echo e($c['dept']); ?></div>
        <div class="meta"><i class="fas fa-id-badge mr-1"></i><?php echo $c['teacher'] ? e($c['teacher']) : '<span class="text-warning">No teacher assigned</span>'; ?></div>
        <?php if ($me['role'] === 'student') {
            [$h, $a, $p] = $myAtt[$c['id']] ?? [0, 0, 0.0]; ?>
            <div class="meta"><i class="fas fa-calendar-check mr-1"></i>My attendance: <?php echo $h ? badge($p . '%', attendance_tone($p)) : '—'; ?></div>
        <?php } else { ?>
            <div class="meta"><i class="fas fa-users mr-1"></i><?php echo (int) $c['class_size']; ?> students
                <?php if (isset($attAvg[$c['id']])) { echo ' &middot; ' . badge(number_format($attAvg[$c['id']], 0) . '% attendance', attendance_tone($attAvg[$c['id']])); } ?></div>
        <?php } ?>
        <div class="tile-foot">
            <?php if ($teach) { ?>
            <a class="btn btn-soft btn-sm" href="attendance.php?course=<?php echo (int) $c['id']; ?>"><i class="fas fa-calendar-check mr-1"></i>Attendance</a>
            <a class="btn btn-soft btn-sm" href="exams.php?course=<?php echo (int) $c['id']; ?>"><i class="fas fa-clipboard-list mr-1"></i>Exams</a>
            <?php } ?>
            <?php if ($manage && can_manage_dept($me, (int) $c['department_id'])) { ?>
            <button class="btn btn-light btn-sm js-edit" data-c="<?php echo e(json_encode($c)); ?>"><i class="far fa-edit"></i></button>
            <button class="btn btn-light btn-sm text-danger" data-api="course" data-action="delete" data-id="<?php echo (int) $c['id']; ?>"
                data-ok="Course deleted" data-confirm="Delete <?php echo e($c['code']); ?>? Its attendance and exams are deleted too."><i class="far fa-trash-alt"></i></button>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</div>
<?php } ?>

<?php if ($manage) { ?>
<div class="modal fade" id="courseModal" tabindex="-1"><div class="modal-dialog modal-dialog-scrollable"><div class="modal-content">
    <form data-api="course" data-action="save">
    <div class="modal-header"><h5 class="modal-title">Add course</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <input type="hidden" name="id">
        <div class="form-row">
            <div class="form-group col-5"><label>Code *</label><input class="form-control" name="code" maxlength="20" placeholder="CS101" required></div>
            <div class="form-group col-7"><label>Name *</label><input class="form-control" name="name" maxlength="150" required></div>
            <div class="form-group col-12"><label>Department *</label>
                <select class="custom-select" name="department_id" id="cDept" required><?php
                    echo options(array_map(fn ($d) => [$d['id'], $d['name']], $formDepts), $me['role'] === 'hod' ? $me['department_id'] : '', count($formDepts) === 1 ? null : 'Choose…'); ?></select></div>
            <div class="form-group col-6"><label>Semester *</label><select class="custom-select" name="semester"><?php echo options(array_map(fn ($i) => [$i, $i], range(1, SEMESTERS)), 1); ?></select></div>
            <div class="form-group col-6"><label>Credits *</label><select class="custom-select" name="credits"><?php echo options(array_map(fn ($i) => [$i, $i], range(1, 6)), 3); ?></select></div>
            <div class="form-group col-12 mb-0"><label>Teacher</label>
                <select class="custom-select" name="teacher_id" id="cTeacher"><option value="">Not assigned</option></select>
                <span class="form-hint">Only teachers of the chosen department can be assigned.</span></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save course</button></div>
    </form>
</div></div></div>
<script>
$(function () {
    var teachers = <?php echo json_encode($teachers, JSON_HEX_TAG | JSON_HEX_AMP); ?>;
    function fillTeachers(dept, selected) {
        var $t = $('#cTeacher').empty().append('<option value="">Not assigned</option>');
        teachers.forEach(function (t) {
            if (String(t.department_id) === String(dept)) {
                $('<option>').val(t.id).text(t.first_name + ' ' + t.last_name).appendTo($t);
            }
        });
        $t.val(selected || '');
    }
    $('#cDept').on('change', function () { fillTeachers(this.value, ''); });
    $('#addCourse').on('click', function () {
        App.openForm('#courseModal', {}, 'Add course');
        fillTeachers($('#cDept').val(), '');
    });
    $('.js-edit').on('click', function () {
        var c = $(this).data('c');
        App.openForm('#courseModal', {id: c.id, code: c.code, name: c.name, department_id: c.department_id, semester: c.semester, credits: c.credits}, 'Edit course');
        fillTeachers(c.department_id, c.teacher_id);
    });
});
</script>
<?php }
page_footer();
