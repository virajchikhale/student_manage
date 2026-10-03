<?php
/*
 * Academic domain helpers shared by pages and API endpoints: who may see / change what,
 * grades and attendance maths. Loaded by bootstrap.php.
 */
declare(strict_types=1);

const SEMESTERS = 8;
const PASS_PERCENT = 40.0;
const MIN_ATTENDANCE = 75.0;

function is_staff(array $u): bool
{
    return $u['role'] !== 'student';
}

/** Admin and principal see the whole institution; HOD and teacher only their own department. */
function institution_wide(array $u): bool
{
    return in_array($u['role'], ['admin', 'principal'], true);
}

/** Department a user is limited to (null = no limit). Students are limited to their own record elsewhere. */
function dept_scope(array $u): ?int
{
    return institution_wide($u) ? null : (int) ($u['department_id'] ?? 0);
}

/** Enrol, edit and remove students and courses. */
function can_manage(array $u): bool
{
    return in_array($u['role'], ['admin', 'principal', 'hod'], true);
}

/** May this user create / edit / delete things that belong to the given department? */
function can_manage_dept(array $u, ?int $deptId): bool
{
    if (!can_manage($u)) {
        return false;
    }
    return $u['role'] !== 'hod' || ((int) $u['department_id'] === (int) $deptId && $deptId !== null);
}

function can_view_student(array $u, array $s): bool
{
    if ($u['role'] === 'student') {
        return (int) $u['id'] === (int) $s['id'];
    }
    $scope = dept_scope($u);
    return $scope === null || $scope === (int) $s['department_id'];
}

/** mode 'view' = see it, 'teach' = take attendance / enter marks. */
function can_access_course(array $u, array $c, string $mode = 'view'): bool
{
    switch ($u['role']) {
        case 'admin':
        case 'principal':
            return true;
        case 'hod':
            return (int) $u['department_id'] === (int) $c['department_id'];
        case 'teacher':
            return $mode === 'teach'
                ? (int) $c['teacher_id'] === (int) $u['id']
                : (int) $u['department_id'] === (int) $c['department_id'];
        case 'student':
            return $mode === 'view'
                && (int) $u['department_id'] === (int) $c['department_id']
                && (int) $u['semester'] === (int) $c['semester'];
    }
    return false;
}

function fetch_course(int $id): ?array
{
    $st = db()->prepare(
        "SELECT c.*, d.name AS dept, NULLIF(TRIM(CONCAT(t.first_name, ' ', t.last_name)), '') AS teacher
         FROM course c JOIN department d ON d.id = c.department_id
         LEFT JOIN teacher_reg t ON t.id = c.teacher_id WHERE c.id = ?"
    );
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Courses the user may see ('view') or run ('teach'), with department, teacher and class size. */
function courses_for(array $u, string $mode = 'view'): array
{
    $where = [];
    $args  = [];
    switch ($u['role']) {
        case 'hod':
            $where[] = 'c.department_id = ?';
            $args[]  = (int) $u['department_id'];
            break;
        case 'teacher':
            if ($mode === 'teach') {
                $where[] = 'c.teacher_id = ?';
                $args[]  = (int) $u['id'];
            } else {
                $where[] = 'c.department_id = ?';
                $args[]  = (int) $u['department_id'];
            }
            break;
        case 'student':
            $where[] = 'c.department_id = ? AND c.semester = ?';
            array_push($args, (int) $u['department_id'], (int) $u['semester']);
            break;
    }
    $sql = "SELECT c.*, d.name AS dept, NULLIF(TRIM(CONCAT(t.first_name, ' ', t.last_name)), '') AS teacher,
                   (SELECT COUNT(*) FROM student s WHERE s.department_id = c.department_id
                      AND s.semester = c.semester AND s.status = 'active') AS class_size
            FROM course c JOIN department d ON d.id = c.department_id
            LEFT JOIN teacher_reg t ON t.id = c.teacher_id"
         . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
         . ' ORDER BY d.name, c.semester, c.code';
    $st = db()->prepare($sql);
    $st->execute($args);
    return $st->fetchAll();
}

/** Active students that sit in a course. */
function roster(array $course): array
{
    $st = db()->prepare(
        "SELECT id, roll_no, first_name, last_name FROM student
         WHERE department_id = ? AND semester = ? AND status = 'active' ORDER BY roll_no"
    );
    $st->execute([$course['department_id'], $course['semester']]);
    return $st->fetchAll();
}

/** Departments the user may pick when creating students / courses. */
function departments_for(array $u): array
{
    if ($u['role'] === 'hod') {
        $st = db()->prepare('SELECT id, name FROM department WHERE id = ?');
        $st->execute([(int) $u['department_id']]);
        return $st->fetchAll();
    }
    return db()->query('SELECT id, name FROM department ORDER BY name')->fetchAll();
}

function full_name(array $r): string
{
    return trim(ucfirst((string) $r['first_name']) . ' ' . ucfirst((string) $r['last_name']));
}

/* ---------- maths ---------- */

function grade_for(float $pct): array
{
    // [letter, css tone]
    return match (true) {
        $pct >= 90 => ['A+', 'success'],
        $pct >= 80 => ['A',  'success'],
        $pct >= 70 => ['B+', 'info'],
        $pct >= 60 => ['B',  'info'],
        $pct >= 50 => ['C',  'warning'],
        $pct >= PASS_PERCENT => ['D', 'warning'],
        default    => ['F',  'danger'],
    };
}

function pct(float|int $part, float|int $whole): float
{
    return $whole > 0 ? round($part * 100 / $whole, 1) : 0.0;
}

function attendance_tone(float $pct): string
{
    return $pct >= MIN_ATTENDANCE ? 'success' : ($pct >= 60 ? 'warning' : 'danger');
}

/** Per course attendance of one student: [course_id => [held, attended, pct]]. */
function student_attendance(int $studentId): array
{
    $st = db()->prepare(
        "SELECT course_id, COUNT(*) AS held, COUNT(*) FILTER (WHERE status IN ('P','L')) AS attended
         FROM attendance WHERE student_id = ? GROUP BY course_id"
    );
    $st->execute([$studentId]);
    $out = [];
    foreach ($st->fetchAll() as $r) {
        $out[(int) $r['course_id']] = [(int) $r['held'], (int) $r['attended'], pct((int) $r['attended'], (int) $r['held'])];
    }
    return $out;
}

/** Exam results of one student, newest first. */
function student_results(int $studentId): array
{
    $st = db()->prepare(
        "SELECT e.id, e.title, e.exam_date, e.max_marks, c.code, c.name AS course, m.marks
         FROM mark m JOIN exam e ON e.id = m.exam_id JOIN course c ON c.id = e.course_id
         WHERE m.student_id = ? ORDER BY e.exam_date DESC, e.id DESC"
    );
    $st->execute([$studentId]);
    return $st->fetchAll();
}

function who(array $u): string
{
    return $u['role'] . ':' . $u['id'];
}

/** Date from user input, or null. */
function parse_date(string $s): ?string
{
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return ($d && $d->format('Y-m-d') === $s) ? $s : null;
}

function random_password(int $len = 10): string
{
    $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $out .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $out;
}

/** API endpoints: the signed-in user, optionally limited to some roles (401 / 403 otherwise). */
function api_user(string ...$roles): array
{
    $u = current_user();
    if (!$u) {
        fail('Your session has expired. Please sign in again.', 401);
    }
    if ($roles && !in_array($u['role'], $roles, true)) {
        fail('You are not allowed to do that.', 403);
    }
    return $u;
}

/** Array posted as name[key]=value (empty array when absent). */
function post_map(string $key): array
{
    $v = $_POST[$key] ?? [];
    return is_array($v) ? $v : [];
}

function post_date(string $key, bool $required = true): ?string
{
    $v = post($key);
    if ($v === '' && !$required) {
        return null;
    }
    $d = parse_date($v);
    if ($d === null) {
        fail('Please enter a valid date.');
    }
    return $d;
}
