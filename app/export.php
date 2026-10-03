<?php
// CSV download of the reports (GET, read-only; same access rules as app/reports.php).
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reports.php';
$me = require_login('admin', 'principal', 'hod', 'teacher');

$type = $_GET['type'] ?? '';
$course = null;
if ($type !== 'low') {
    foreach (courses_for($me, 'view') as $c) {
        if ((int) $c['id'] === (int) ($_GET['course'] ?? 0)) { $course = $c; }
    }
    if (!$course) {
        http_response_code(404);
        exit('Course not found');
    }
}
$fname = 'report-' . $type . ($course ? '-' . $course['code'] : '') . '-' . date('Ymd') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]/', '_', $fname) . '"');
$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF");   // BOM so Excel reads UTF-8

/** Cells starting with = + - @ would run as formulas in a spreadsheet. */
$safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
$put = fn (array $row) => fputcsv($out, array_map($safe, $row));

if ($type === 'attendance') {
    $put(['Roll no', 'Name', 'Present', 'Late', 'Absent', 'Sessions', 'Attendance %']);
    foreach (report_attendance($course, parse_date($_GET['from'] ?? ''), parse_date($_GET['to'] ?? '')) as $r) {
        $put([$r['roll_no'], full_name($r), $r['present'], $r['late'], $r['absent'], $r['held'], $r['pct']]);
    }
} elseif ($type === 'results') {
    [$exams, $rows] = report_results($course);
    $put(array_merge(['Roll no', 'Name'], array_map(fn ($x) => $x['title'] . ' (/' . $x['max_marks'] . ')', $exams), ['Total', 'Max', 'Percent', 'Grade']));
    foreach ($rows as $r) {
        $put(array_merge([$r['roll_no'], full_name($r)], array_map(fn ($x) => $r['per'][(int) $x['id']] ?? '', $exams),
            [$r['total'], $r['max'], $r['pct'], $r['grade']]));
    }
} else {
    $put(['Roll no', 'Name', 'Course', 'Attended', 'Sessions', 'Attendance %']);
    foreach (report_low_attendance($me) as $r) {
        $put([$r['roll_no'], full_name($r), $r['code'], $r['attended'], $r['held'], $r['pct']]);
    }
}
fclose($out);
