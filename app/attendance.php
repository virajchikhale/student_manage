<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Attendance', 'attendance', 'admin', 'principal', 'hod', 'teacher');
$pdo = db();

$courses = courses_for($me, 'teach');
$cid     = (int) ($_GET['course'] ?? 0);
$course  = null;
foreach ($courses as $c) {
    if ((int) $c['id'] === $cid) { $course = $c; }
}
$date = parse_date($_GET['date'] ?? '') ?? date('Y-m-d');
if ($date > date('Y-m-d')) { $date = date('Y-m-d'); }

$students = [];
$marked = [];
$sessions = [];
if ($course) {
    $students = roster($course);
    $st = $pdo->prepare('SELECT student_id, status FROM attendance WHERE course_id = ? AND att_date = ?');
    $st->execute([$course['id'], $date]);
    $marked = $st->fetchAll(PDO::FETCH_KEY_PAIR);
    $st = $pdo->prepare(
        "SELECT att_date, COUNT(*) AS n, COUNT(*) FILTER (WHERE status IN ('P','L')) AS ok
         FROM attendance WHERE course_id = ? GROUP BY att_date ORDER BY att_date DESC LIMIT 14"
    );
    $st->execute([$course['id']]);
    $sessions = array_reverse($st->fetchAll());
}
?>
<form class="page-actions filters auto-filter" method="get">
    <select class="custom-select" name="course" style="min-width:280px"><?php
        echo options(array_map(fn ($c) => [$c['id'], $c['code'] . ' · ' . $c['name'] . ' (Sem ' . $c['semester'] . ')'], $courses), $cid ?: '', 'Choose a course…'); ?></select>
    <input class="form-control" type="date" name="date" value="<?php echo e($date); ?>" max="<?php echo date('Y-m-d'); ?>">
</form>

<?php if (!$courses) { ?>
<div class="card"><?php echo empty_state('fa-calendar-check', 'No courses to mark',
    $me['role'] === 'teacher' ? 'You are not assigned to any course yet. Ask your HOD.' : 'Create a course first.'); ?></div>
<?php } elseif (!$course) { ?>
<div class="card"><?php echo empty_state('fa-hand-pointer', 'Pick a course', 'Choose a course and a date to take attendance.'); ?></div>
<?php } else { ?>
<div class="grid grid-7-5">
<div class="card" style="align-self:start">
    <div class="card-head"><div><h5><?php echo e($course['code'] . ' · ' . $course['name']); ?></h5>
        <small><?php echo e(date('l, d M Y', strtotime($date))); ?> &middot; <?php echo count($students); ?> students<?php echo $marked ? ' &middot; already marked, saving updates it' : ''; ?></small></div>
        <div>
            <button type="button" class="btn btn-light btn-sm" data-all="P">All present</button>
            <button type="button" class="btn btn-light btn-sm" data-all="A">All absent</button>
        </div>
    </div>
    <?php if (!$students) { echo empty_state('fa-graduation-cap', 'No students in this class', 'Active students of ' . $course['dept'] . ' semester ' . $course['semester'] . ' appear here.'); } else { ?>
    <form id="attForm">
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><th>Roll no</th><th class="text-right">Status</th></tr></thead>
        <tbody>
        <?php foreach ($students as $s) { $cur = $marked[$s['id']] ?? 'P'; $name = full_name($s); ?>
            <tr><td><div class="person"><?php echo avatar($name, 'sm'); ?><span><?php echo e($name); ?></span></div></td>
                <td><code><?php echo e($s['roll_no']); ?></code></td>
                <td class="text-right"><div class="seg">
                    <?php foreach (['P' => 'Present', 'L' => 'Late', 'A' => 'Absent'] as $v => $l) { $rid = 'r' . $s['id'] . $v; ?>
                    <input type="radio" name="records[<?php echo (int) $s['id']; ?>]" value="<?php echo $v; ?>" id="<?php echo $rid; ?>" <?php echo $cur === $v ? 'checked' : ''; ?>><label for="<?php echo $rid; ?>"><?php echo $l; ?></label>
                    <?php } ?></div></td></tr>
        <?php } ?>
        </tbody>
    </table></div>
    <div class="sticky-save">
        <div class="tally"><span>Present <b id="tP">0</b></span><span>Late <b id="tL">0</b></span><span>Absent <b id="tA">0</b></span></div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-save mr-1"></i> Save attendance</button>
    </div>
    </form>
    <?php } ?>
</div>
<div class="card" style="align-self:start"><div class="card-head"><h5>Recent sessions</h5><small>Click a bar to open that day</small></div>
    <div class="card-body">
        <?php if (!$sessions) { echo '<div class="empty-mini">No sessions recorded yet.</div>'; } else { ?>
        <div class="cols">
        <?php foreach ($sessions as $x) { $p = pct((int) $x['ok'], (int) $x['n']); ?>
            <a class="col-item" href="?course=<?php echo (int) $course['id']; ?>&date=<?php echo e($x['att_date']); ?>" title="<?php echo e($x['att_date'] . ': ' . $p . '% present'); ?>" style="text-decoration:none">
                <div class="col-track"><div class="col-fill tone-bg-<?php echo attendance_tone($p); ?>" style="height:<?php echo max(3, $p); ?>%"></div></div>
                <div class="col-label"><?php echo e(date('j/n', strtotime($x['att_date']))); ?></div></a>
        <?php } ?>
        </div>
        <?php } ?>
    </div></div>
</div>
<script>
$(function () {
    function tally() {
        ['P', 'L', 'A'].forEach(function (v) { $('#t' + v).text($('#attForm input[value=' + v + ']:checked').length); });
    }
    tally();
    $('#attForm').on('change', 'input', tally);
    $('[data-all]').on('click', function () {
        $('#attForm input[value=' + $(this).data('all') + ']').prop('checked', true); tally();
    });
    $('#attForm').on('submit', function (e) {
        e.preventDefault();
        var records = {};
        $.each($(this).serializeArray(), function (_, p) {
            var m = p.name.match(/^records\[(\d+)\]$/);
            if (m) { records[m[1]] = p.value; }
        });
        App.api('attendance', {course_id: <?php echo (int) $course['id']; ?>, date: <?php echo json_encode($date); ?>, records: records})
            .done(function (res) { App.flash('Attendance saved for ' + res.saved + ' students'); location.reload(); })
            .fail(App.fail);
    });
});
</script>
<?php }
page_footer();
