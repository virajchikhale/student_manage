<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Enter marks', 'exams', 'admin', 'principal', 'hod', 'teacher');
$pdo = db();

$st = $pdo->prepare('SELECT * FROM exam WHERE id = ?');
$st->execute([(int) ($_GET['exam'] ?? 0)]);
$exam   = $st->fetch();
$course = $exam ? fetch_course((int) $exam['course_id']) : null;
if (!$exam || !$course || !can_access_course($me, $course, 'view')) {
    echo empty_state('fa-clipboard-list', 'Exam not found');
    page_footer();
    exit;
}
$edit     = can_access_course($me, $course, 'teach');
$students = roster($course);
$st = $pdo->prepare('SELECT student_id, marks FROM mark WHERE exam_id = ?');
$st->execute([$exam['id']]);
$got = $st->fetchAll(PDO::FETCH_KEY_PAIR);
$max = (float) $exam['max_marks'];

$vals = array_map('floatval', $got);
$n = count($vals);
$avg = $n ? array_sum($vals) / $n : 0;
$passed = count(array_filter($vals, fn ($v) => pct($v, $max) >= PASS_PERCENT));
$fmt = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
?>
<div class="page-actions">
    <a class="btn btn-light" href="exams.php?course=<?php echo (int) $course['id']; ?>"><i class="fas fa-arrow-left mr-1"></i> Exams</a>
    <span class="spacer"></span>
</div>
<div class="grid grid-4 keep-2 mb-grid">
    <?php echo stat_card('fa-book', $exam['title'], $course['code'], 'brand', $course['name']); ?>
    <?php echo stat_card('fa-users', 'Marks entered', $n . ' / ' . count($students), 'info'); ?>
    <?php echo stat_card('fa-chart-line', 'Class average', $n ? $fmt($avg) . ' / ' . $fmt($max) : '—', $n ? grade_for(pct($avg, $max))[1] : 'brand', $n ? pct($avg, $max) . '%' : ''); ?>
    <?php echo stat_card('fa-check-circle', 'Passed', $n ? $passed . ' / ' . $n : '—', 'success', 'Pass mark ' . (int) PASS_PERCENT . '%'); ?>
</div>
<div class="card">
    <div class="card-head"><div><h5><?php echo e($exam['title']); ?> &middot; max <?php echo $fmt($max); ?></h5>
        <small><?php echo e(date('d M Y', strtotime($exam['exam_date']))); ?> &middot; leave a field empty if the student did not sit the exam</small></div></div>
    <?php if (!$students) { echo empty_state('fa-graduation-cap', 'No students in this class'); } else { ?>
    <form id="markForm">
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><th>Roll no</th><th class="text-right">Marks (/<?php echo $fmt($max); ?>)</th><th class="num">%</th><th>Grade</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s) { $v = $got[$s['id']] ?? null; $name = full_name($s); ?>
            <tr><td><div class="person"><?php echo avatar($name, 'sm'); ?><span><?php echo e($name); ?></span></div></td>
                <td><code><?php echo e($s['roll_no']); ?></code></td>
                <td class="text-right"><?php if ($edit) { ?>
                    <input class="form-control mark-input d-inline-block js-mark" type="number" step="any" min="0" max="<?php echo $fmt($max); ?>"
                        name="marks[<?php echo (int) $s['id']; ?>]" value="<?php echo $v === null ? '' : e($fmt($v)); ?>">
                    <?php } else { echo $v === null ? '—' : e($fmt($v)); } ?></td>
                <td class="num js-pct"><?php echo $v === null ? '' : pct((float) $v, $max) . '%'; ?></td>
                <td class="js-grade"><?php echo $v === null ? '' : badge(...grade_for(pct((float) $v, $max))); ?></td></tr>
        <?php } ?>
        </tbody>
    </table></div>
    <?php if ($edit) { ?>
    <div class="sticky-save"><span class="text-muted">Marks outside 0&ndash;<?php echo $fmt($max); ?> are rejected.</span>
        <button class="btn btn-primary" type="submit"><i class="fas fa-save mr-1"></i> Save marks</button></div>
    <?php } ?>
    </form>
    <?php } ?>
</div>
<?php if ($edit) { ?>
<script>
$(function () {
    var max = <?php echo json_encode($max); ?>, pass = <?php echo json_encode(PASS_PERCENT); ?>;
    function grade(p) { return p >= 90 ? ['A+', 'success'] : p >= 80 ? ['A', 'success'] : p >= 70 ? ['B+', 'info'] : p >= 60 ? ['B', 'info'] : p >= 50 ? ['C', 'warning'] : p >= pass ? ['D', 'warning'] : ['F', 'danger']; }
    $('.js-mark').on('input', function () {
        var $tr = $(this).closest('tr'), v = parseFloat(this.value);
        if (isNaN(v)) { $tr.find('.js-pct,.js-grade').empty(); return; }
        var p = Math.round(v * 1000 / max) / 10, g = grade(p);
        $tr.find('.js-pct').text(p + '%');
        $tr.find('.js-grade').html('<span class="pill pill-' + g[1] + '">' + g[0] + '</span>');
    });
    $('#markForm').on('submit', function (e) {
        e.preventDefault();
        var marks = {};
        $('.js-mark').each(function () { marks[this.name.match(/\d+/)[0]] = this.value; });
        App.api('marks', {action: 'save_marks', exam_id: <?php echo (int) $exam['id']; ?>, marks: marks})
            .done(function (res) { App.flash('Marks saved'); location.reload(); }).fail(App.fail);
    });
});
</script>
<?php }
page_footer();
