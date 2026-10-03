<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Exams & marks', 'exams', 'admin', 'principal', 'hod', 'teacher');
$pdo = db();

$courses = courses_for($me, 'view');
$teach   = array_values(array_filter($courses, fn ($c) => can_access_course($me, $c, 'teach')));
$cid     = (int) ($_GET['course'] ?? 0);

$ids = array_map(fn ($c) => (int) $c['id'], $courses);
if ($cid && in_array($cid, $ids, true)) { $ids = [$cid]; }
$exams = [];
if ($ids) {
    $st = $pdo->prepare(
        "SELECT e.*, c.code, c.name AS course, c.department_id, c.semester, c.teacher_id,
                (SELECT COUNT(*) FROM mark m WHERE m.exam_id = e.id) AS graded,
                (SELECT AVG(m.marks) * 100.0 / e.max_marks FROM mark m WHERE m.exam_id = e.id) AS avgp,
                (SELECT COUNT(*) FROM student s WHERE s.department_id = c.department_id AND s.semester = c.semester AND s.status = 'active') AS class_size
         FROM exam e JOIN course c ON c.id = e.course_id WHERE e.course_id = ANY(?::int[])
         ORDER BY e.exam_date DESC, e.id DESC"
    );
    $st->execute(['{' . implode(',', $ids) . '}']);
    $exams = $st->fetchAll();
}
?>
<form class="page-actions filters auto-filter" method="get">
    <select class="custom-select" name="course" style="min-width:280px"><?php
        echo options(array_map(fn ($c) => [$c['id'], $c['code'] . ' · ' . $c['name']], $courses), $cid ?: '', 'All courses'); ?></select>
    <span class="spacer"></span>
    <?php if ($teach) { ?><button type="button" class="btn btn-primary" id="addExam"><i class="fas fa-plus mr-1"></i> New exam</button><?php } ?>
</form>

<div class="card">
<?php if (!$exams) { echo empty_state('fa-clipboard-list', 'No exams yet', $teach ? 'Create an exam, then enter the marks.' : 'Nothing to show here.'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Exam</th><th>Course</th><th>Date</th><th class="num">Max</th><th>Graded</th><th>Class average</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($exams as $x) { $can = can_access_course($me, $x, 'teach'); ?>
            <tr><td><b><?php echo e($x['title']); ?></b></td>
                <td><?php echo e($x['code']); ?><br><small class="text-muted"><?php echo e($x['course']); ?></small></td>
                <td><?php echo e(date('d M Y', strtotime($x['exam_date']))); ?></td>
                <td class="num"><?php echo (int) $x['max_marks']; ?></td>
                <td><?php echo (int) $x['graded']; ?> / <?php echo (int) $x['class_size']; ?></td>
                <td><?php echo $x['avgp'] === null ? '<span class="text-muted">—</span>' : badge(number_format((float) $x['avgp'], 1) . '%', grade_for((float) $x['avgp'])[1]); ?></td>
                <td class="row-actions">
                    <?php if ($can) { ?>
                    <a class="btn btn-soft btn-sm" href="marks.php?exam=<?php echo (int) $x['id']; ?>"><i class="fas fa-pencil-alt mr-1"></i>Enter marks</a>
                    <button class="btn btn-light btn-icon js-edit" data-x="<?php echo e(json_encode($x)); ?>"><i class="far fa-edit"></i></button>
                    <button class="btn btn-light btn-icon text-danger" data-api="marks" data-action="delete_exam" data-id="<?php echo (int) $x['id']; ?>"
                        data-ok="Exam deleted" data-confirm="Delete this exam and all its marks?"><i class="far fa-trash-alt"></i></button>
                    <?php } else { ?><a class="btn btn-light btn-sm" href="marks.php?exam=<?php echo (int) $x['id']; ?>">View</a><?php } ?>
                </td></tr>
        <?php } ?>
        </tbody>
    </table></div>
<?php } ?>
</div>

<?php if ($teach) { ?>
<div class="modal fade" id="examModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-api="marks" data-action="save_exam">
    <div class="modal-header"><h5 class="modal-title">New exam</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <input type="hidden" name="id">
        <div class="form-group" id="examCourse"><label>Course *</label>
            <select class="custom-select" name="course_id"><?php echo options(array_map(fn ($c) => [$c['id'], $c['code'] . ' · ' . $c['name']], $teach), $cid ?: '', 'Choose…'); ?></select></div>
        <div class="form-group"><label>Title *</label><input class="form-control" name="title" maxlength="120" placeholder="Mid-term, Unit test 1…" required></div>
        <div class="form-row">
            <div class="form-group col-7 mb-0"><label>Date *</label><input class="form-control" type="date" name="exam_date" value="<?php echo date('Y-m-d'); ?>" required></div>
            <div class="form-group col-5 mb-0"><label>Max marks *</label><input class="form-control" type="number" name="max_marks" min="1" max="1000" value="100" required></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save exam</button></div>
    </form>
</div></div></div>
<script>
$(function () {
    $('#addExam').on('click', function () { $('#examCourse').show(); App.openForm('#examModal', {max_marks: 100, exam_date: <?php echo json_encode(date('Y-m-d')); ?>, course_id: <?php echo $cid ?: "''"; ?>}, 'New exam'); });
    $('.js-edit').on('click', function () {
        var x = $(this).data('x');
        $('#examCourse').hide();
        App.openForm('#examModal', {id: x.id, title: x.title, exam_date: x.exam_date, max_marks: x.max_marks}, 'Edit exam');
    });
});
</script>
<?php }
page_footer();
