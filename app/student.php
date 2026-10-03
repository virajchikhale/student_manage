<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = page_header('Student record', (current_user()['role'] ?? '') === 'student' ? 'record' : 'students');
$pdo = db();

$id = $me['role'] === 'student' ? (int) $me['id'] : (int) ($_GET['id'] ?? 0);
$st = $pdo->prepare('SELECT s.*, d.name AS dept FROM student s LEFT JOIN department d ON d.id = s.department_id WHERE s.id = ?');
$st->execute([$id]);
$s = $st->fetch();
if (!$s || !can_view_student($me, $s)) {
    echo empty_state('fa-user-times', 'Student not found', 'The student does not exist or you do not have access.');
    page_footer();
    exit;
}
$name = full_name($s);
$mine = $me['role'] === 'student';

// Attendance per course of the student's current class
$att = student_attendance((int) $s['id']);
$cs = $pdo->prepare('SELECT c.id, c.code, c.name FROM course c WHERE c.id = ANY(?::int[]) OR (c.department_id = ? AND c.semester = ?) ORDER BY c.code');
$cs->execute(['{' . implode(',', array_keys($att) ?: [0]) . '}', $s['department_id'], $s['semester']]);
$courses = $cs->fetchAll();
$held = $attended = 0;
foreach ($att as [$h, $a]) { $held += $h; $attended += $a; }
$overall = pct($attended, $held);

$results = student_results((int) $s['id']);
$gotSum = $maxSum = 0;
foreach ($results as $r) { $gotSum += (float) $r['marks']; $maxSum += (float) $r['max_marks']; }
$avg = pct($gotSum, $maxSum);
[$grade, $gtone] = grade_for($avg);
?>
<div class="page-actions">
    <?php if (!$mine) { ?><a class="btn btn-light" href="students.php"><i class="fas fa-arrow-left mr-1"></i> All students</a><?php } ?>
    <span class="spacer"></span>
    <?php if (!$mine && can_manage_dept($me, (int) $s['department_id'])) { ?>
    <a class="btn btn-light" href="students.php?edit=<?php echo (int) $s['id']; ?>"><i class="far fa-edit mr-1"></i> Edit</a>
    <button class="btn btn-light" data-confirm="Generate a new password for <?php echo e($name); ?>?" id="resetPw"><i class="fas fa-key mr-1"></i> Reset password</button>
    <?php } ?>
    <button class="btn btn-light" onclick="window.print()"><i class="fas fa-print mr-1"></i> Print</button>
</div>

<div class="card"><div class="card-body profile-head">
    <?php echo avatar($name, 'lg'); ?>
    <div style="flex:1;min-width:220px">
        <h3><?php echo e($name); ?></h3>
        <div class="text-muted mb-2"><?php echo e($s['dept'] ?? 'No department'); ?> &middot; Semester <?php echo (int) $s['semester']; ?> &middot; Roll <code><?php echo e($s['roll_no']); ?></code></div>
        <?php echo badge(ucfirst($s['status']), ['active' => 'success', 'inactive' => 'secondary', 'graduated' => 'info'][$s['status']]); ?>
    </div>
    <div class="grid grid-3" style="min-width:min(100%,430px)">
        <?php echo stat_card('fa-calendar-check', 'Attendance', $held ? $overall . '%' : '—', $held ? attendance_tone($overall) : 'brand'); ?>
        <?php echo stat_card('fa-star', 'Average marks', $maxSum ? $avg . '%' : '—', $maxSum ? $gtone : 'brand'); ?>
        <?php echo stat_card('fa-trophy', 'Grade', $maxSum ? $grade : '—', $maxSum ? $gtone : 'brand'); ?>
    </div>
</div></div>

<div class="grid grid-7-5 mb-grid">
    <div>
        <div class="card"><div class="card-head"><h5>Exam results</h5><small><?php echo count($results); ?> result<?php echo count($results) === 1 ? '' : 's'; ?></small></div>
        <?php if (!$results) { echo empty_state('fa-clipboard-list', 'No results yet'); } else { ?>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Course</th><th>Exam</th><th>Date</th><th class="num">Marks</th><th class="num">%</th><th>Grade</th></tr></thead>
            <tbody>
            <?php foreach ($results as $r) { $p = pct((float) $r['marks'], (float) $r['max_marks']); [$g, $gt] = grade_for($p); ?>
                <tr><td><b><?php echo e($r['code']); ?></b><br><small class="text-muted"><?php echo e($r['course']); ?></small></td>
                    <td><?php echo e($r['title']); ?></td>
                    <td><?php echo e(date('d M Y', strtotime($r['exam_date']))); ?></td>
                    <td class="num"><?php echo rtrim(rtrim(number_format((float) $r['marks'], 2), '0'), '.'); ?> / <?php echo (int) $r['max_marks']; ?></td>
                    <td class="num"><?php echo $p; ?>%</td>
                    <td><?php echo badge($g, $gt); ?></td></tr>
            <?php } ?>
            </tbody>
        </table></div>
        <?php } ?></div>

        <div class="card"><div class="card-head"><h5>Attendance by course</h5><small>Minimum required: <?php echo (int) MIN_ATTENDANCE; ?>%</small></div>
        <div class="card-body">
            <?php $rows = [];
            foreach ($courses as $c) {
                [$h, $a, $p] = $att[$c['id']] ?? [0, 0, 0.0];
                if ($h) { $rows[] = [$c['code'] . ' · ' . $c['name'], $p, attendance_tone($p), $p . '% (' . $a . '/' . $h . ')']; }
            }
            echo bars($rows, 100, 'No classes recorded yet.'); ?>
        </div></div>
    </div>
    <div>
        <div class="card"><div class="card-head"><h5>Details</h5></div><div class="card-body">
            <dl class="kv">
                <dt>Email</dt><dd><?php echo e($s['email']); ?></dd>
                <dt>Phone</dt><dd><?php echo e($s['phone']); ?></dd>
                <dt>Gender</dt><dd><?php echo e($s['gender'] ?: '—'); ?></dd>
                <dt>Date of birth</dt><dd><?php echo $s['dob'] ? e(date('d M Y', strtotime($s['dob']))) : '—'; ?></dd>
                <dt>Admitted</dt><dd><?php echo e(date('d M Y', strtotime($s['admitted_on']))); ?></dd>
                <dt>Guardian</dt><dd><?php echo e($s['guardian_name'] ?: '—'); ?><?php echo $s['guardian_phone'] ? '<br><small class="text-muted">' . e($s['guardian_phone']) . '</small>' : ''; ?></dd>
                <dt>Address</dt><dd><?php echo e($s['address'] ?: '—'); ?></dd>
            </dl>
        </div></div>
    </div>
</div>

<div class="modal fade" id="credModal" tabindex="-1" data-backdrop="static"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content">
    <div class="modal-body text-center p-4">
        <div class="confirm-icon" style="background:#e8f8ee;color:#16a34a"><i class="fas fa-key"></i></div>
        <h5>Password reset</h5>
        <p class="text-muted mb-2" id="credMail"></p>
        <div class="secret-box mb-2"><small class="d-block text-muted">New password</small><code id="credPass"></code></div>
    </div>
    <div class="modal-footer justify-content-center border-0 pt-0"><button class="btn btn-primary" data-dismiss="modal">Done</button></div>
</div></div></div>
<script>
$('#resetPw').on('click', function () {
    App.confirm($(this).data('confirm'), function () {
        App.api('student', {action: 'reset_password', id: <?php echo (int) $s['id']; ?>}).done(function (res) {
            $('#credPass').text(res.password);
            $('#credMail').text(res.emailed ? 'The new password was emailed to the student.' : 'Email could not be sent, so give this password to the student.');
            $('#credModal').modal('show');
        }).fail(App.fail);
    });
});
</script>
<?php page_footer();
