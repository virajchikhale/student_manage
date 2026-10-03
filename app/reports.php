<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reports.php';
$me = page_header('Reports', 'reports', 'admin', 'principal', 'hod', 'teacher');

$type    = in_array($_GET['type'] ?? '', ['attendance', 'results', 'low'], true) ? $_GET['type'] : 'attendance';
$courses = courses_for($me, 'view');
$cid     = (int) ($_GET['course'] ?? 0);
$course  = null;
foreach ($courses as $c) {
    if ((int) $c['id'] === $cid) { $course = $c; }
}
$from = parse_date($_GET['from'] ?? '');
$to   = parse_date($_GET['to'] ?? '');
$qs   = http_build_query(array_filter(['type' => $type, 'course' => $cid ?: null, 'from' => $from, 'to' => $to]));
$fmt  = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
?>
<div class="page-actions">
    <div class="btn-group">
        <?php foreach (['attendance' => 'Attendance', 'results' => 'Results', 'low' => 'Low attendance'] as $k => $l) { ?>
        <a class="btn <?php echo $k === $type ? 'btn-primary' : 'btn-light'; ?>" href="?type=<?php echo $k; ?><?php echo $cid ? '&course=' . $cid : ''; ?>"><?php echo $l; ?></a>
        <?php } ?>
    </div>
    <span class="spacer"></span>
    <?php if ($course || $type === 'low') { ?>
    <a class="btn btn-light" href="export.php?<?php echo e($qs); ?>"><i class="fas fa-file-alt mr-1"></i> Export CSV</a>
    <button class="btn btn-light" onclick="window.print()"><i class="fas fa-print mr-1"></i> Print</button>
    <?php } ?>
</div>

<?php if ($type !== 'low') { ?>
<form class="page-actions filters auto-filter" method="get">
    <input type="hidden" name="type" value="<?php echo e($type); ?>">
    <select class="custom-select" name="course" style="min-width:280px"><?php
        echo options(array_map(fn ($c) => [$c['id'], $c['code'] . ' · ' . $c['name'] . ' (Sem ' . $c['semester'] . ')'], $courses), $cid ?: '', 'Choose a course…'); ?></select>
    <?php if ($type === 'attendance') { ?>
    <label class="mb-0 text-muted">From</label><input class="form-control" type="date" name="from" value="<?php echo e((string) $from); ?>">
    <label class="mb-0 text-muted">To</label><input class="form-control" type="date" name="to" value="<?php echo e((string) $to); ?>">
    <?php } ?>
</form>
<?php } ?>

<?php if ($type === 'low') {
    $rows = report_low_attendance($me); ?>
<div class="card">
    <div class="card-head"><h5>Students below <?php echo (int) MIN_ATTENDANCE; ?>% attendance</h5><small><?php echo count($rows); ?> case<?php echo count($rows) === 1 ? '' : 's'; ?></small></div>
    <?php if (!$rows) { echo empty_state('fa-smile', 'Everyone is on track', 'No student is below the minimum attendance.'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><th>Course</th><th class="num">Attended</th><th class="num">Attendance</th></tr></thead>
        <tbody><?php foreach ($rows as $r) { $n = full_name($r); ?>
            <tr><td><div class="person"><?php echo avatar($n, 'sm'); ?><div><a href="student.php?id=<?php echo (int) $r['id']; ?>"><?php echo e($n); ?></a><small><?php echo e($r['roll_no']); ?></small></div></div></td>
                <td><b><?php echo e($r['code']); ?></b> <small class="text-muted"><?php echo e($r['course']); ?></small></td>
                <td class="num"><?php echo (int) $r['attended']; ?> / <?php echo (int) $r['held']; ?></td>
                <td class="num"><?php echo badge($r['pct'] . '%', attendance_tone($r['pct'])); ?></td></tr>
        <?php } ?></tbody>
    </table></div>
    <?php } ?>
</div>

<?php } elseif (!$course) { ?>
<div class="card"><?php echo empty_state('fa-chart-bar', 'Choose a course', 'Pick a course above to see its ' . ($type === 'results' ? 'results.' : 'attendance.')); ?></div>

<?php } elseif ($type === 'attendance') {
    $rows = report_attendance($course, $from, $to);
    $held = $rows ? max(array_column($rows, 'held')) : 0;
    $avg  = $rows ? array_sum(array_column($rows, 'pct')) / count($rows) : 0;
    $low  = count(array_filter($rows, fn ($r) => $r['held'] > 0 && $r['pct'] < MIN_ATTENDANCE)); ?>
<div class="grid grid-3 keep-2 mb-grid">
    <?php echo stat_card('fa-calendar-alt', 'Sessions held', $held, 'brand'); ?>
    <?php echo stat_card('fa-percent', 'Average attendance', $held ? number_format($avg, 1) . '%' : '—', $held ? attendance_tone($avg) : 'brand'); ?>
    <?php echo stat_card('fa-exclamation-triangle', 'Below ' . (int) MIN_ATTENDANCE . '%', $low, $low ? 'danger' : 'success'); ?>
</div>
<div class="card">
    <div class="card-head"><h5><?php echo e($course['code'] . ' · ' . $course['name']); ?></h5>
        <small><?php echo $from || $to ? e(($from ?: 'start') . ' to ' . ($to ?: 'today')) : 'All sessions'; ?></small></div>
    <?php if (!$rows) { echo empty_state('fa-graduation-cap', 'No students in this class'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><th class="num">Present</th><th class="num">Late</th><th class="num">Absent</th><th class="num">Held</th><th style="width:30%">Attendance</th></tr></thead>
        <tbody><?php foreach ($rows as $r) { $n = full_name($r); ?>
            <tr><td><div class="person"><?php echo avatar($n, 'sm'); ?><div><a href="student.php?id=<?php echo (int) $r['id']; ?>"><?php echo e($n); ?></a><small><?php echo e($r['roll_no']); ?></small></div></div></td>
                <td class="num"><?php echo (int) $r['present']; ?></td><td class="num"><?php echo (int) $r['late']; ?></td>
                <td class="num"><?php echo (int) $r['absent']; ?></td><td class="num"><?php echo (int) $r['held']; ?></td>
                <td><?php echo $r['held'] ? bars([['', $r['pct'], attendance_tone($r['pct']), $r['pct'] . '%']], 100) : '<span class="text-muted">—</span>'; ?></td></tr>
        <?php } ?></tbody>
    </table></div>
    <?php } ?>
</div>

<?php } else {
    [$exams, $rows] = report_results($course);
    $graded = array_filter($rows, fn ($r) => $r['max'] > 0);
    $avg = $graded ? array_sum(array_column($graded, 'pct')) / count($graded) : 0;
    $pass = count(array_filter($graded, fn ($r) => $r['pct'] >= PASS_PERCENT)); ?>
<div class="grid grid-3 keep-2 mb-grid">
    <?php echo stat_card('fa-clipboard-list', 'Exams held', count($exams), 'brand'); ?>
    <?php echo stat_card('fa-percent', 'Class average', $graded ? number_format($avg, 1) . '%' : '—', $graded ? grade_for($avg)[1] : 'brand'); ?>
    <?php echo stat_card('fa-check-circle', 'Passing', $graded ? $pass . ' / ' . count($graded) : '—', 'success'); ?>
</div>
<div class="card">
    <div class="card-head"><h5><?php echo e($course['code'] . ' · ' . $course['name']); ?></h5></div>
    <?php if (!$exams) { echo empty_state('fa-clipboard-list', 'No exams yet', 'Create an exam for this course first.'); } else { ?>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Student</th><?php foreach ($exams as $x) { ?><th class="num"><?php echo e($x['title']); ?><br><span style="font-weight:400">/<?php echo (int) $x['max_marks']; ?></span></th><?php } ?><th class="num">Total</th><th class="num">%</th><th>Grade</th></tr></thead>
        <tbody><?php foreach ($rows as $r) { $n = full_name($r); ?>
            <tr><td><div class="person"><?php echo avatar($n, 'sm'); ?><div><a href="student.php?id=<?php echo (int) $r['id']; ?>"><?php echo e($n); ?></a><small><?php echo e($r['roll_no']); ?></small></div></div></td>
                <?php foreach ($exams as $x) { $v = $r['per'][(int) $x['id']]; ?><td class="num"><?php echo $v === null ? '<span class="text-muted">—</span>' : e($fmt($v)); ?></td><?php } ?>
                <td class="num"><?php echo $r['max'] ? e($fmt($r['total'])) . ' / ' . e($fmt($r['max'])) : '—'; ?></td>
                <td class="num"><?php echo $r['max'] ? $r['pct'] . '%' : '—'; ?></td>
                <td><?php echo $r['max'] ? badge($r['grade'], grade_for($r['pct'])[1]) : ''; ?></td></tr>
        <?php } ?></tbody>
    </table></div>
    <?php } ?>
</div>
<?php }
page_footer();
