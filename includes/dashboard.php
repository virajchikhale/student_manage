<?php
/* Dashboard bodies for every role (the page chrome comes from page_header()). */
declare(strict_types=1);

function greeting(): string
{
    $h = (int) date('G');
    return $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
}

function scalar(string $sql, array $args = []): int|float
{
    $st = db()->prepare($sql);
    $st->execute($args);
    return (float) $st->fetchColumn();
}

function hero(string $title, string $text, string $buttons = ''): string
{
    return '<div class="hero"><h2>' . e($title) . '</h2><p>' . e($text) . '</p>' . $buttons . '</div>';
}

function dashboard_staff(array $me): void
{
    $pdo   = db();
    $role  = $me['role'];
    $scope = dept_scope($me);
    $dName = null;
    if ($scope) {
        $st = $pdo->prepare('SELECT name FROM department WHERE id = ?');
        $st->execute([$scope]);
        $dName = $st->fetchColumn();
    }
    $mine = courses_for($me, 'teach');
    $myIds = array_map(fn ($c) => (int) $c['id'], $mine);

    // Attendance scope: teachers see their own courses, HODs their department, the rest everything
    $aWhere = $role === 'teacher' ? 'WHERE c.teacher_id = ?' : ($scope ? 'WHERE c.department_id = ?' : '');
    $aArgs  = $role === 'teacher' ? [(int) $me['id']] : ($scope ? [$scope] : []);
    $st = $pdo->prepare(
        "SELECT a.att_date, COUNT(*) FILTER (WHERE a.status IN ('P','L')) * 100.0 / COUNT(*) AS p
         FROM attendance a JOIN course c ON c.id = a.course_id $aWhere
         GROUP BY a.att_date ORDER BY a.att_date DESC LIMIT 10"
    );
    $st->execute($aArgs);
    $days = array_reverse($st->fetchAll());
    $overall = scalar(
        "SELECT COALESCE(COUNT(*) FILTER (WHERE a.status IN ('P','L')) * 100.0 / NULLIF(COUNT(*), 0), 0)
         FROM attendance a JOIN course c ON c.id = a.course_id $aWhere", $aArgs
    );
    $sWhere = $scope ? 'WHERE department_id = ' . (int) $scope . " AND status = 'active'" : "WHERE status = 'active'";
    $students = (int) scalar("SELECT COUNT(*) FROM student $sWhere");

    $stats = [];
    if ($role === 'teacher') {
        $classSum = array_sum(array_map(fn ($c) => (int) $c['class_size'], $mine));
        $stats = [
            stat_card('fa-book', 'Courses I teach', count($mine), 'brand'),
            stat_card('fa-graduation-cap', 'Students in my classes', $classSum, 'info'),
            stat_card('fa-calendar-check', 'Average attendance', $days ? number_format($overall, 1) . '%' : '—', $days ? attendance_tone($overall) : 'brand'),
            stat_card('fa-clipboard-list', 'Exams to grade', (int) ($myIds ? scalar(
                "SELECT COUNT(*) FROM exam e JOIN course c ON c.id = e.course_id WHERE e.course_id = ANY(?::int[])
                   AND (SELECT COUNT(*) FROM mark m WHERE m.exam_id = e.id) < (SELECT COUNT(*) FROM student s
                        WHERE s.department_id = c.department_id AND s.semester = c.semester AND s.status = 'active')",
                ['{' . implode(',', $myIds) . '}']) : 0), 'warning'),
        ];
    } else {
        $cw = $scope ? 'WHERE department_id = ' . (int) $scope : '';
        $stats = [
            stat_card('fa-graduation-cap', 'Active students', $students, 'brand'),
            stat_card('fa-book', 'Courses', (int) scalar("SELECT COUNT(*) FROM course $cw"), 'info'),
            stat_card('fa-id-badge', 'Teachers', (int) scalar('SELECT COUNT(*) FROM teacher_reg' . ($scope ? ' WHERE department_id = ' . (int) $scope : '')), 'success'),
            stat_card('fa-calendar-check', 'Average attendance', $days ? number_format($overall, 1) . '%' : '—', $days ? attendance_tone($overall) : 'brand'),
        ];
    }

    $btn = '';
    if (in_array($role, ['teacher', 'hod', 'admin', 'principal'], true)) {
        $btn .= '<a class="btn btn-light" href="' . e(url('app/attendance.php')) . '"><i class="fas fa-calendar-check mr-1"></i> Take attendance</a> ';
    }
    if (can_manage($me)) {
        $btn .= '<a class="btn btn-light" href="' . e(url('app/students.php')) . '"><i class="fas fa-user-plus mr-1"></i> Students</a>';
    }
    $sub = $dName ? $dName . ' department' : 'Institution overview';
    echo hero(greeting() . ', ' . ucfirst($me['first_name']) . '!', ROLES[$role]['label'] . ' · ' . $sub, $btn);
    echo '<div class="grid grid-4 keep-2 mb-grid">' . implode('', $stats) . '</div>';
    ?>
<div class="grid grid-7-5 mb-grid">
    <div>
        <div class="card"><div class="card-head"><h5>Attendance trend</h5><small>% present, last sessions</small></div>
            <div class="card-body"><?php echo columns(array_map(fn ($d) => [date('d M', strtotime($d['att_date'])), round((float) $d['p'], 1)], $days), 'Attendance will show here once classes are marked.'); ?></div></div>

        <?php if ($role === 'teacher' || ($role === 'hod' && $mine)) { ?>
        <div class="card"><div class="card-head"><h5><?php echo $role === 'hod' ? 'Department courses' : 'My courses'; ?></h5><a href="<?php echo e(url('app/courses.php?mine=1')); ?>">View all</a></div>
            <div class="card-body"><?php if (!$mine) { echo '<div class="empty-mini">No courses assigned to you yet.</div>'; } else { ?>
            <div class="grid grid-2"><?php foreach (array_slice($mine, 0, 4) as $c) { ?>
                <div class="course-tile"><span class="code"><?php echo e($c['code']); ?></span><h6><?php echo e($c['name']); ?></h6>
                    <div class="meta">Sem <?php echo (int) $c['semester']; ?> &middot; <?php echo (int) $c['class_size']; ?> students</div>
                    <div class="tile-foot"><a class="btn btn-soft btn-sm" href="<?php echo e(url('app/attendance.php?course=' . (int) $c['id'])); ?>">Attendance</a>
                        <a class="btn btn-soft btn-sm" href="<?php echo e(url('app/exams.php?course=' . (int) $c['id'])); ?>">Exams</a></div></div>
            <?php } ?></div><?php } ?></div></div>
        <?php } else {
            $rows = $pdo->query(
                "SELECT d.name, COUNT(s.id) AS n FROM department d LEFT JOIN student s ON s.department_id = d.id AND s.status = 'active'"
                . ($scope ? ' WHERE d.id = ' . (int) $scope : '') . ' GROUP BY d.id ORDER BY d.name'
            )->fetchAll();
            $bySem = $pdo->query("SELECT semester, COUNT(*) AS n FROM student WHERE status = 'active'" . ($scope ? ' AND department_id = ' . (int) $scope : '') . ' GROUP BY semester ORDER BY semester')->fetchAll(); ?>
        <div class="grid grid-2">
            <div class="card"><div class="card-head"><h5>Students by department</h5></div>
                <div class="card-body"><?php echo bars(array_map(fn ($r) => [$r['name'], (int) $r['n']], $rows)); ?></div></div>
            <div class="card"><div class="card-head"><h5>Students by semester</h5></div>
                <div class="card-body"><?php echo bars(array_map(fn ($r) => ['Semester ' . $r['semester'], (int) $r['n'], 'info'], $bySem)); ?></div></div>
        </div>
        <?php } ?>
    </div>
    <div>
        <div class="card"><div class="card-head"><h5>Latest notices</h5><a href="<?php echo e(url('app/notices.php')); ?>">All notices</a></div>
            <div class="card-body"><?php echo notice_list(notices_for($me, 3)); ?></div></div>
        <?php
        require_once __DIR__ . '/reports.php';
        $low = array_slice(report_low_attendance($me), 0, 5); ?>
        <div class="card"><div class="card-head"><h5>Needs attention</h5><a href="<?php echo e(url('app/reports.php?type=low')); ?>">Full report</a></div>
            <div class="card-body"><?php if (!$low) { echo '<div class="empty-mini">No student is below ' . (int) MIN_ATTENDANCE . '% attendance.</div>'; } else { ?>
            <div class="bars"><?php foreach ($low as $r) { ?>
                <div class="bar-row" style="grid-template-columns:1fr 56px"><div><a href="<?php echo e(url('app/student.php?id=' . (int) $r['id'])); ?>"><?php echo e(full_name($r)); ?></a>
                    <small class="d-block text-muted"><?php echo e($r['code']); ?></small></div><div class="bar-val"><?php echo badge($r['pct'] . '%', attendance_tone($r['pct'])); ?></div></div>
            <?php } ?></div><?php } ?></div></div>
    </div>
</div>
<?php
}

function dashboard_student(array $me): void
{
    $att = student_attendance((int) $me['id']);
    $held = $attended = 0;
    foreach ($att as [$h, $a]) { $held += $h; $attended += $a; }
    $overall = pct($attended, $held);
    $results = student_results((int) $me['id']);
    $got = $max = 0.0;
    foreach ($results as $r) { $got += (float) $r['marks']; $max += (float) $r['max_marks']; }
    $avg = pct($got, $max);
    $courses = courses_for($me);
    $btn = '<a class="btn btn-light" href="' . e(url('app/student.php')) . '"><i class="fas fa-id-card mr-1"></i> My record</a>';
    echo hero(greeting() . ', ' . ucfirst($me['first_name']) . '!', 'Roll ' . $me['roll_no'] . ' · Semester ' . $me['semester'], $btn);
    echo '<div class="grid grid-4 keep-2 mb-grid">'
        . stat_card('fa-calendar-check', 'Attendance', $held ? $overall . '%' : '—', $held ? attendance_tone($overall) : 'brand', $held ? "$attended of $held classes" : '')
        . stat_card('fa-star', 'Average marks', $max ? $avg . '%' : '—', $max ? grade_for($avg)[1] : 'brand')
        . stat_card('fa-trophy', 'Grade', $max ? grade_for($avg)[0] : '—', $max ? grade_for($avg)[1] : 'brand')
        . stat_card('fa-book', 'Courses this semester', count($courses), 'info')
        . '</div>'; ?>
<div class="grid grid-7-5">
    <div>
        <div class="card"><div class="card-head"><h5>Attendance by course</h5><small>Minimum <?php echo (int) MIN_ATTENDANCE; ?>%</small></div>
            <div class="card-body"><?php $rows = [];
            foreach ($courses as $c) {
                [$h, $a, $p] = $att[$c['id']] ?? [0, 0, 0.0];
                $rows[] = [$c['code'] . ' · ' . $c['name'], $h ? $p : 0, $h ? attendance_tone($p) : 'info', $h ? $p . '%' : 'n/a'];
            }
            echo bars($rows, 100, 'You have no courses this semester yet.'); ?></div></div>
        <div class="card"><div class="card-head"><h5>Recent results</h5><a href="<?php echo e(url('app/student.php')); ?>">All results</a></div>
            <?php if (!$results) { echo empty_state('fa-clipboard-list', 'No results yet'); } else { ?>
            <div class="table-wrap"><table class="table"><thead><tr><th>Course</th><th>Exam</th><th class="num">Marks</th><th>Grade</th></tr></thead><tbody>
            <?php foreach (array_slice($results, 0, 5) as $r) { $p = pct((float) $r['marks'], (float) $r['max_marks']); ?>
                <tr><td><b><?php echo e($r['code']); ?></b></td><td><?php echo e($r['title']); ?></td>
                    <td class="num"><?php echo rtrim(rtrim(number_format((float) $r['marks'], 2), '0'), '.'); ?> / <?php echo (int) $r['max_marks']; ?></td>
                    <td><?php echo badge(...grade_for($p)); ?></td></tr>
            <?php } ?></tbody></table></div><?php } ?></div>
    </div>
    <div>
        <div class="card"><div class="card-head"><h5>Overall attendance</h5></div>
            <div class="card-body"><?php echo $held ? ring($overall, 'present', attendance_tone($overall)) : '<div class="empty-mini">No classes recorded yet.</div>'; ?></div></div>
        <div class="card"><div class="card-head"><h5>Notices</h5><a href="<?php echo e(url('app/notices.php')); ?>">All notices</a></div>
            <div class="card-body"><?php echo notice_list(notices_for($me, 4)); ?></div></div>
    </div>
</div>
<?php
}
