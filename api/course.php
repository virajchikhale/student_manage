<?php
// Courses: {action: save | delete}. Admin, principal and HOD (own department).
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me  = api_user('admin', 'principal', 'hod');
$pdo = db();

switch (post('action')) {
    case 'save':
        $id   = (int) post('id');
        $old  = $id ? (fetch_course($id) ?: fail('Course not found.', 404)) : null;
        $code = strtoupper(post('code'));
        $name = post('name');
        $dept = $me['role'] === 'hod' ? (int) $me['department_id'] : (int) post('department_id');
        $sem  = (int) post('semester');
        $cred = (int) post('credits');
        $teacher = (int) post('teacher_id') ?: null;

        if (!preg_match('/^[A-Z0-9][A-Z0-9\-]{1,19}$/', $code)) {
            fail('Enter a course code of 2-20 letters / digits, e.g. CS101.');
        }
        if ($name === '' || mb_strlen($name) > 150) {
            fail('Please enter the course name.');
        }
        $st = $pdo->prepare('SELECT 1 FROM department WHERE id = ?');
        $st->execute([$dept]);
        if (!$st->fetchColumn()) {
            fail('Please choose a department.');
        }
        if (!can_manage_dept($me, $dept) || ($old && !can_manage_dept($me, (int) $old['department_id']))) {
            fail('You can only manage courses of your own department.', 403);
        }
        if ($sem < 1 || $sem > 12 || $cred < 1 || $cred > 10) {
            fail('Please check the semester and credits.');
        }
        if ($teacher) {
            $st = $pdo->prepare('SELECT 1 FROM teacher_reg WHERE id = ? AND department_id = ?');
            $st->execute([$teacher, $dept]);
            if (!$st->fetchColumn()) {
                fail('The teacher must belong to the same department.');
            }
        }
        try {
            if ($old) {
                $pdo->prepare('UPDATE course SET code=?, name=?, department_id=?, semester=?, credits=?, teacher_id=? WHERE id=?')
                    ->execute([$code, $name, $dept, $sem, $cred, $teacher, $id]);
            } else {
                $pdo->prepare('INSERT INTO course (code, name, department_id, semester, credits, teacher_id) VALUES (?,?,?,?,?,?)')
                    ->execute([$code, $name, $dept, $sem, $cred, $teacher]);
            }
        } catch (PDOException $ex) {
            fail($ex->getCode() === '23505' ? 'That course code already exists.' : 'Could not save the course.');
        }
        break;

    case 'delete':
        $c = fetch_course((int) post('id')) ?: fail('Course not found.', 404);
        if (!can_manage_dept($me, (int) $c['department_id'])) {
            fail('You can only manage courses of your own department.', 403);
        }
        $pdo->prepare('DELETE FROM course WHERE id = ?')->execute([$c['id']]);
        break;

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
