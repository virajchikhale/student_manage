<?php
// Students: {action: save | delete | reset_password}. Admin, principal and HOD (own department) only.
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me  = api_user('admin', 'principal', 'hod');
$pdo = db();

function load_student(int $id): array
{
    $st = db()->prepare('SELECT * FROM student WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: fail('Student not found.', 404);
}

function new_roll_no(PDO $pdo): string
{
    $n = (int) $pdo->query('SELECT COUNT(*) FROM student')->fetchColumn();
    do {
        $roll = date('Y') . str_pad((string) ++$n, 4, '0', STR_PAD_LEFT);
        $st = $pdo->prepare('SELECT 1 FROM student WHERE roll_no = ?');
        $st->execute([$roll]);
    } while ($st->fetchColumn());
    return $roll;
}

function mail_credentials(array $s, string $password, bool $reset): bool
{
    $app = e(env('MAIL_FROM_NAME', 'Student Management'));
    return send_mail($s['email'], ($reset ? 'Your new password - ' : 'Welcome - ') . env('MAIL_FROM_NAME', 'Student Management'),
        'Hello ' . e($s['first_name']) . ',<br><br>' . ($reset ? 'Your password was reset.' : 'You have been enrolled in ' . $app . '.')
        . '<br>Sign in with your email <b>' . e($s['email']) . '</b> and this password: <b>' . e($password)
        . '</b><br>Please change it from <i>My profile</i> after signing in.');
}

switch (post('action')) {
    case 'save':
        $id    = (int) post('id');
        $old   = $id ? load_student($id) : null;
        $first = post('first_name');
        $last  = post('last_name');
        $email = post('email');
        $phone = post('phone');
        $dept  = $me['role'] === 'hod' ? (int) $me['department_id'] : (int) post('department_id');
        $sem   = (int) post('semester');
        $status = post('status') ?: 'active';

        if ($first === '' || $last === '' || mb_strlen($first) > 100 || mb_strlen($last) > 100) {
            fail('Please enter the first and last name.');
        }
        if (!valid_email($email)) {
            fail('Please enter a valid email address.');
        }
        if (!valid_phone($phone)) {
            fail('Please enter a valid phone number.');
        }
        $guardianPhone = post('guardian_phone');
        if ($guardianPhone !== '' && !valid_phone($guardianPhone)) {
            fail('Please enter a valid guardian phone number.');
        }
        $st = $pdo->prepare('SELECT 1 FROM department WHERE id = ?');
        $st->execute([$dept]);
        if (!$st->fetchColumn()) {
            fail('Please choose a department.');
        }
        if (!can_manage_dept($me, $dept) || ($old && !can_manage_dept($me, (int) $old['department_id']))) {
            fail('You can only manage students of your own department.', 403);
        }
        if ($sem < 1 || $sem > 12) {
            fail('Please choose a semester.');
        }
        if (!in_array($status, ['active', 'inactive', 'graduated'], true)) {
            fail('Invalid status.');
        }
        $gender = post('gender');
        $gender = in_array($gender, ['Male', 'Female', 'Other'], true) ? $gender : null;
        $dob    = post_date('dob', false);
        $roll   = strtoupper(post('roll_no'));
        if ($roll !== '' && !preg_match('/^[A-Z0-9][A-Z0-9\-\/]{0,29}$/', $roll)) {
            fail('Roll number may only contain letters, digits, - and /.');
        }
        if ((!$old || strcasecmp($old['email'], $email) !== 0) && identity_taken('email', $email)) {
            fail('This email already exists in the system.');
        }
        if ((!$old || $old['phone'] !== $phone) && identity_taken('phone', $phone)) {
            fail('This phone number already exists in the system.');
        }
        $fields = [$first, $last, $email, $phone, $gender, $dob, post('address') ?: null, post('guardian_name') ?: null,
                   $guardianPhone ?: null, $dept, $sem, $status];
        try {
            if ($old) {
                if ($roll === '') {
                    $roll = $old['roll_no'];
                }
                $pdo->prepare('UPDATE student SET first_name=?, last_name=?, email=?, phone=?, gender=?, dob=?, address=?,
                               guardian_name=?, guardian_phone=?, department_id=?, semester=?, status=?, roll_no=? WHERE id=?')
                    ->execute([...$fields, $roll, $id]);
                json_out(['ok' => true, 'id' => $id]);
            }
            $roll = $roll !== '' ? $roll : new_roll_no($pdo);
            $password = random_password();
            $st = $pdo->prepare('INSERT INTO student (first_name, last_name, email, phone, gender, dob, address,
                guardian_name, guardian_phone, department_id, semester, status, roll_no, password)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?) RETURNING id');
            $st->execute([...$fields, $roll, password_hash($password, PASSWORD_DEFAULT)]);
            $newId = (int) $st->fetchColumn();
        } catch (PDOException $ex) {
            fail($ex->getCode() === '23505' ? 'That roll number, email or phone is already used.' : 'Could not save the student.');
        }
        $mailed = mail_credentials(['first_name' => $first, 'email' => $email], $password, false);
        json_out(['ok' => true, 'id' => $newId, 'password' => $password, 'roll_no' => $roll, 'emailed' => $mailed]);

    case 'delete':
        $s = load_student((int) post('id'));
        if (!can_manage_dept($me, (int) $s['department_id'])) {
            fail('You can only manage students of your own department.', 403);
        }
        $pdo->prepare('DELETE FROM student WHERE id = ?')->execute([$s['id']]);
        break;

    case 'reset_password':
        $s = load_student((int) post('id'));
        if (!can_manage_dept($me, (int) $s['department_id'])) {
            fail('You can only manage students of your own department.', 403);
        }
        $password = random_password();
        $pdo->prepare('UPDATE student SET password = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $s['id']]);
        json_out(['ok' => true, 'password' => $password, 'emailed' => mail_credentials($s, $password, true)]);

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
