<?php
// Exams and marks: {action: save_exam | delete_exam | save_marks}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me  = api_user('admin', 'principal', 'hod', 'teacher');
$pdo = db();

function load_exam(int $id, array $me): array
{
    $st = db()->prepare('SELECT * FROM exam WHERE id = ?');
    $st->execute([$id]);
    $exam = $st->fetch() ?: fail('Exam not found.', 404);
    $course = fetch_course((int) $exam['course_id']) ?: fail('Course not found.', 404);
    if (!can_access_course($me, $course, 'teach')) {
        fail('You are not allowed to change this exam.', 403);
    }
    return [$exam, $course];
}

switch (post('action')) {
    case 'save_exam':
        $id    = (int) post('id');
        $title = post('title');
        $max   = (int) post('max_marks');
        $date  = post_date('exam_date');
        if ($title === '' || mb_strlen($title) > 120) {
            fail('Please enter the exam title.');
        }
        if ($max < 1 || $max > 1000) {
            fail('Maximum marks must be between 1 and 1000.');
        }
        if ($id) {
            [$exam, $course] = load_exam($id, $me);
            $below = $pdo->prepare('SELECT COUNT(*) FROM mark WHERE exam_id = ? AND marks > ?');
            $below->execute([$id, $max]);
            if ($below->fetchColumn() > 0) {
                fail('Some students already have more marks than that maximum.');
            }
            $pdo->prepare('UPDATE exam SET title=?, exam_date=?, max_marks=? WHERE id=?')->execute([$title, $date, $max, $id]);
            json_out(['ok' => true, 'id' => $id]);
        }
        $course = fetch_course((int) post('course_id')) ?: fail('Please choose a course.');
        if (!can_access_course($me, $course, 'teach')) {
            fail('You are not allowed to add exams to this course.', 403);
        }
        $st = $pdo->prepare('INSERT INTO exam (course_id, title, exam_date, max_marks) VALUES (?,?,?,?) RETURNING id');
        $st->execute([$course['id'], $title, $date, $max]);
        json_out(['ok' => true, 'id' => (int) $st->fetchColumn()]);

    case 'delete_exam':
        [$exam] = load_exam((int) post('id'), $me);
        $pdo->prepare('DELETE FROM exam WHERE id = ?')->execute([$exam['id']]);
        break;

    case 'save_marks':
        [$exam, $course] = load_exam((int) post('exam_id'), $me);
        $valid = array_map('intval', array_column(roster($course), 'id'));
        $ins = $pdo->prepare('INSERT INTO mark (exam_id, student_id, marks) VALUES (?,?,?)
                              ON CONFLICT (exam_id, student_id) DO UPDATE SET marks = EXCLUDED.marks');
        $del = $pdo->prepare('DELETE FROM mark WHERE exam_id = ? AND student_id = ?');
        $pdo->beginTransaction();
        $saved = 0;
        foreach (post_map('marks') as $sid => $v) {
            if (!in_array((int) $sid, $valid, true) || !is_string($v)) {
                continue;
            }
            $v = trim($v);
            if ($v === '') {
                $del->execute([$exam['id'], (int) $sid]);
                continue;
            }
            if (!is_numeric($v) || (float) $v < 0 || (float) $v > (float) $exam['max_marks']) {
                $pdo->rollBack();
                fail('Marks must be between 0 and ' . $exam['max_marks'] . '.');
            }
            $ins->execute([$exam['id'], (int) $sid, round((float) $v, 2)]);
            $saved++;
        }
        $pdo->commit();
        json_out(['ok' => true, 'saved' => $saved]);

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
