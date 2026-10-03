<?php
/* Report queries shared by app/reports.php (screen) and app/export.php (CSV). */
declare(strict_types=1);

/** One row per student of the course: sessions held / attended / pct within the date range. */
function report_attendance(array $course, ?string $from, ?string $to): array
{
    $args = [$course['id']];
    $range = '';
    if ($from) { $range .= ' AND a.att_date >= ?'; $args[] = $from; }
    if ($to)   { $range .= ' AND a.att_date <= ?'; $args[] = $to; }
    $st = db()->prepare(
        "SELECT s.id, s.roll_no, s.first_name, s.last_name,
                COUNT(a.id) AS held,
                COUNT(a.id) FILTER (WHERE a.status = 'P') AS present,
                COUNT(a.id) FILTER (WHERE a.status = 'L') AS late,
                COUNT(a.id) FILTER (WHERE a.status = 'A') AS absent
         FROM student s LEFT JOIN attendance a ON a.student_id = s.id AND a.course_id = ? $range
         WHERE s.department_id = ? AND s.semester = ? AND s.status = 'active'
         GROUP BY s.id ORDER BY s.roll_no"
    );
    // placeholders appear in this order: course (JOIN), range, department, semester
    array_push($args, $course['department_id'], $course['semester']);
    $st->execute($args);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $r['attended'] = (int) $r['present'] + (int) $r['late'];
        $r['pct']      = pct($r['attended'], (int) $r['held']);
    }
    return $rows;
}

/** [exams, rows]: every student with marks per exam, total, percentage and grade. */
function report_results(array $course): array
{
    $st = db()->prepare('SELECT id, title, max_marks FROM exam WHERE course_id = ? ORDER BY exam_date, id');
    $st->execute([$course['id']]);
    $exams = $st->fetchAll();

    $st = db()->prepare('SELECT m.exam_id, m.student_id, m.marks FROM mark m JOIN exam e ON e.id = m.exam_id WHERE e.course_id = ?');
    $st->execute([$course['id']]);
    $marks = [];
    foreach ($st->fetchAll() as $m) {
        $marks[(int) $m['student_id']][(int) $m['exam_id']] = (float) $m['marks'];
    }
    $rows = [];
    foreach (roster($course) as $s) {
        $got = $max = 0.0;
        $per = [];
        foreach ($exams as $e) {
            $v = $marks[(int) $s['id']][(int) $e['id']] ?? null;
            $per[(int) $e['id']] = $v;
            if ($v !== null) {
                $got += $v;
                $max += (float) $e['max_marks'];
            }
        }
        $p = pct($got, $max);
        $s['per'] = $per;
        $s['total'] = $got;
        $s['max'] = $max;
        $s['pct'] = $p;
        $s['grade'] = $max > 0 ? grade_for($p)[0] : '—';
        $rows[] = $s;
    }
    return [$exams, $rows];
}

/** Students below the attendance minimum in any course the user can see. */
function report_low_attendance(array $u): array
{
    $ids = array_map(fn ($c) => (int) $c['id'], courses_for($u, 'view'));
    if (!$ids) {
        return [];
    }
    $st = db()->prepare(
        "SELECT s.id, s.roll_no, s.first_name, s.last_name, c.code, c.name AS course,
                COUNT(*) AS held, COUNT(*) FILTER (WHERE a.status IN ('P','L')) AS attended
         FROM attendance a JOIN student s ON s.id = a.student_id JOIN course c ON c.id = a.course_id
         WHERE a.course_id = ANY(?::int[]) AND s.status = 'active'
         GROUP BY s.id, c.id
         HAVING COUNT(*) FILTER (WHERE a.status IN ('P','L')) * 100.0 / COUNT(*) < ?
         ORDER BY COUNT(*) FILTER (WHERE a.status IN ('P','L')) * 100.0 / COUNT(*), s.roll_no"
    );
    $st->execute(['{' . implode(',', $ids) . '}', MIN_ATTENDANCE]);
    $rows = $st->fetchAll();
    foreach ($rows as &$r) {
        $r['pct'] = pct((int) $r['attended'], (int) $r['held']);
    }
    return $rows;
}
