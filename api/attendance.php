<?php
// Save one class session: {course_id, date, records[student_id] = P|A|L}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me  = api_user('admin', 'principal', 'hod', 'teacher');
$pdo = db();

$course = fetch_course((int) post('course_id')) ?: fail('Course not found.', 404);
if (!can_access_course($me, $course, 'teach')) {
    fail('You are not allowed to take attendance for this course.', 403);
}
$date = post_date('date');
if ($date > date('Y-m-d')) {
    fail('You cannot mark attendance for a future date.');
}
$records = post_map('records');
if (!$records) {
    fail('There are no students to mark.');
}
$valid = array_column(roster($course), 'id');
$st = $pdo->prepare(
    'INSERT INTO attendance (course_id, student_id, att_date, status, marked_by) VALUES (?,?,?,?,?)
     ON CONFLICT (course_id, student_id, att_date) DO UPDATE SET status = EXCLUDED.status, marked_by = EXCLUDED.marked_by'
);
$pdo->beginTransaction();
$n = 0;
foreach ($records as $sid => $status) {
    if (!in_array((int) $sid, array_map('intval', $valid), true) || !in_array($status, ['P', 'A', 'L'], true)) {
        continue;
    }
    $st->execute([$course['id'], (int) $sid, $date, $status, who($me)]);
    $n++;
}
$pdo->commit();
json_out(['ok' => true, 'saved' => $n]);
