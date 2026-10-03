<?php
/*
 * One-shot job run by docker compose on every "up" (service "seed"), or by hand:
 *   php database/seed.php
 *   always:     apply database/schema.sql (idempotent) and create ADMIN_EMAIL when no admin exists
 *   DEMO=true:  wipe all people/codes/departments and load the demo data
 */
declare(strict_types=1);

$app = is_file(__DIR__ . '/../includes/bootstrap.php') ? __DIR__ . '/..' : '/var/www/html';
require $app . '/includes/bootstrap.php';

// The database may still be accepting its first connections
for ($i = 0; ; $i++) {
    try {
        $pdo = db();
        break;
    } catch (PDOException $e) {
        if ($i >= 30) {
            fwrite(STDERR, 'Database not reachable: ' . $e->getMessage() . "\n");
            exit(1);
        }
        sleep(2);
    }
}

$schema = @file_get_contents(__DIR__ . '/schema.sql') ?: @file_get_contents('/seed/schema.sql');
if ($schema === false) {
    fwrite(STDERR, "schema.sql not found\n");
    exit(1);
}
$pdo->exec($schema);

$password = (string) env('DEMO_PASSWORD', 'demo12345');
$hash = password_hash($password, PASSWORD_DEFAULT);

function person(PDO $pdo, string $table, string $first, string $last, string $email, string $phone, string $hash,
                ?int $reportTo = null, ?int $dept = null): int
{
    if ($reportTo === null && $dept === null) {
        $st = $pdo->prepare("INSERT INTO $table (first_name, last_name, email, phone, password) VALUES (?,?,?,?,?) RETURNING id");
        $st->execute([$first, $last, $email, $phone, $hash]);
        return (int) $st->fetchColumn();
    }
    $st = $pdo->prepare("INSERT INTO $table (first_name, last_name, email, phone, password, report_to, department_id) VALUES (?,?,?,?,?,?,?) RETURNING id");
    $st->execute([$first, $last, $email, $phone, $hash, $reportTo, $dept]);
    $id = (int) $st->fetchColumn();
    if ($table === 'hod_reg') {
        $pdo->prepare('UPDATE department SET status = 1 WHERE id = ?')->execute([$dept]);
    }
    return $id;
}

/** Demo students, courses, attendance, exams, marks and notices (random but repeatable). */
function seed_academics(PDO $pdo, array $dept, array $teacherIds, string $hash): void
{
    mt_srand(42);
    $names = [
        'cs' => ['Aarav Singh', 'Diya Kapoor', 'Ishaan Verma', 'Kavya Reddy', 'Rohan Gupta', 'Saanvi Menon', 'Tanvi Bhat', 'Yash Malhotra'],
        'it' => ['Aditi Chopra', 'Dev Sharma', 'Ira Banerjee', 'Manav Jain', 'Riya Pillai', 'Veer Khanna'],
        'ec' => ['Ananya Das', 'Harsh Trivedi', 'Mihir Saxena', 'Nisha Rao', 'Pranav Kulkarni', 'Zoya Khan'],
        'me' => ['Karan Bajwa', 'Lakshmi Iyer', 'Omkar Pawar', 'Simran Gill'],
    ];
    $deptOf = ['cs' => 'Computer Science', 'it' => 'Information Technology', 'ec' => 'Electronics', 'me' => 'Mechanical Engineering'];
    $ins = $pdo->prepare('INSERT INTO student (roll_no, first_name, last_name, email, phone, password, gender, dob, address,
        guardian_name, guardian_phone, department_id, semester, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $students = [];   // [deptCode => [[id, semester, quality]]]
    $k = 0;
    foreach ($names as $code => $list) {
        foreach ($list as $i => $full) {
            [$f, $l] = explode(' ', $full);
            $k++;
            $sem = $code === 'cs' && $i >= 6 ? 5 : 3;
            $ins->execute([
                '2024' . strtoupper($code) . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT), $f, $l,
                "student.$code" . ($i + 1) . '@demo.local', '+91 98' . str_pad((string) (7000 + $k), 8, '0', STR_PAD_LEFT), $hash,
                $i % 2 ? 'Female' : 'Male', sprintf('200%d-%02d-%02d', 3 + $i % 2, 1 + $k % 12, 1 + ($k * 7) % 27),
                (10 + $k) . ', Park Road, Pune', 'Mr. ' . $l, '+91 97' . str_pad((string) (6000 + $k), 8, '0', STR_PAD_LEFT),
                (int) $dept[$deptOf[$code]], $sem, $k === 14 ? 'inactive' : 'active',
            ]);
            $students[$code][] = [(int) $pdo->lastInsertId('student_id_seq'), $sem, 0.55 + mt_rand(0, 40) / 100];
        }
    }

    $courses = [
        ['cs', 'CS301', 'Data Structures', 3, 4, 1], ['cs', 'CS302', 'Database Systems', 3, 4, 2],
        ['cs', 'CS303', 'Operating Systems', 3, 3, 3], ['cs', 'CS501', 'Machine Learning', 5, 4, 1],
        ['it', 'IT301', 'Web Technologies', 3, 3, 1], ['it', 'IT302', 'Computer Networks', 3, 4, 2],
        ['ec', 'EC301', 'Digital Electronics', 3, 4, 1], ['ec', 'EC302', 'Signals and Systems', 3, 3, 2],
        ['me', 'ME301', 'Thermodynamics', 3, 4, null],
    ];
    $cIns = $pdo->prepare('INSERT INTO course (code, name, department_id, semester, credits, teacher_id) VALUES (?,?,?,?,?,?) RETURNING id');
    $aIns = $pdo->prepare('INSERT INTO attendance (course_id, student_id, att_date, status, marked_by) VALUES (?,?,?,?,?)');
    $eIns = $pdo->prepare('INSERT INTO exam (course_id, title, exam_date, max_marks) VALUES (?,?,?,?) RETURNING id');
    $mIns = $pdo->prepare('INSERT INTO mark (exam_id, student_id, marks) VALUES (?,?,?)');

    $days = [];
    for ($d = 28; $d >= 1; $d--) {
        $ts = strtotime("-$d days");
        if ((int) date('N', $ts) <= 5) {
            $days[] = date('Y-m-d', $ts);
        }
    }
    $pdo->beginTransaction();
    foreach ($courses as $ci => [$code, $ccode, $cname, $sem, $credits, $tn]) {
        $teacher = $tn ? $teacherIds[$code][$tn] : null;
        $cIns->execute([$ccode, $cname, (int) $dept[$deptOf[$code]], $sem, $credits, $teacher]);
        $cid = (int) $cIns->fetchColumn();
        $class = array_values(array_filter($students[$code], fn ($s) => $s[1] === $sem));
        $mark = $teacher ? 'teacher:' . $teacher : 'seed';
        foreach ($days as $di => $day) {
            if (($di + $ci) % 3 === 2) { continue; }                // each course meets about 2 of 3 weekdays
            foreach ($class as [$sid, , $q]) {
                $r = mt_rand(0, 99) / 100;
                $aIns->execute([$cid, $sid, $day, $r < $q ? ($r < 0.05 ? 'L' : 'P') : 'A', $mark]);
            }
        }
        foreach ([['Unit Test 1', 25, '-21 days', true], ['Mid-term', 50, '-9 days', true], ['Unit Test 2', 25, '-1 days', $ccode !== 'CS301']] as [$title, $max, $when, $graded]) {
            $eIns->execute([$cid, $title, date('Y-m-d', strtotime($when)), $max]);
            $eid = (int) $eIns->fetchColumn();
            if (!$graded) { continue; }
            foreach ($class as [$sid, , $q]) {
                $v = max(0, min($max, round($max * ($q * 0.85 + mt_rand(0, 25) / 100) * 4) / 4));
                $mIns->execute([$eid, $sid, $v]);
            }
        }
    }
    $pdo->commit();

    $nIns = $pdo->prepare('INSERT INTO notice (title, body, audience, author_role, author_id, author_name, created_at) VALUES (?,?,?,?,?,?,?)');
    foreach ([
        ['Welcome to the new semester', "Classes for the new semester are in full swing. Please check your course list and keep your attendance above 75%.", 'all', '-12 days'],
        ['Mid-term results published', "Mid-term marks have been entered for all courses. Students can see their grades on their dashboard.", 'students', '-5 days'],
        ['Staff meeting on Friday', "All teachers and heads of department: staff meeting at 3 PM in the conference room. Agenda: end-of-term schedule.", 'staff', '-2 days'],
        ['Library hours extended', "The library stays open until 8 PM during exam weeks.", 'all', '-1 days'],
    ] as [$t, $b, $aud, $when]) {
        $nIns->execute([$t, $b, $aud, 'principal', 1, 'Priya Sharma (Principal)', date('Y-m-d H:i:s', strtotime($when))]);
    }
}

if (demo_mode()) {
    echo "DEMO=true: resetting and loading demo data\n";
    $pdo->exec('TRUNCATE notice, mark, exam, attendance, course, student, teacher_reg, hod_reg, principal_reg, admin_reg, details, department RESTART IDENTITY CASCADE');
    $pdo->exec("INSERT INTO department (name, status) VALUES
        ('Computer Science', 0), ('Information Technology', 0), ('Electronics', 0), ('Mechanical Engineering', 0)");
    $dept = $pdo->query('SELECT name, id FROM department')->fetchAll(PDO::FETCH_KEY_PAIR);

    person($pdo, 'admin_reg', 'Demo', 'Admin', 'admin@demo.local', '+91 90000 00001', $hash);
    $principal = person($pdo, 'principal_reg', 'Priya', 'Sharma', 'principal@demo.local', '+91 90000 00002', $hash);
    $pdo->exec("INSERT INTO details (principal_verification) VALUES ('DEMO-PRINCIPAL')");

    // Mechanical Engineering is left without an HOD so the HOD sign-up flow can be tried
    $people = [
        ['Computer Science',      'cs', 'Rahul', 'Mehta',  ['Anita Desai', 'Vikram Rao', 'Sneha Kulkarni']],
        ['Information Technology', 'it', 'Neha',  'Joshi',  ['Arjun Nair', 'Pooja Iyer']],
        ['Electronics',            'ec', 'Sanjay', 'Patil', ['Kiran Shah', 'Meera Bose']],
    ];
    $n = 10;
    $teacherIds = [];
    foreach ($people as [$deptName, $code, $hf, $hl, $teachers]) {
        $hod = person($pdo, 'hod_reg', $hf, $hl, "hod.$code@demo.local", '+91 9' . str_pad((string) (++$n), 9, '0', STR_PAD_LEFT), $hash, $principal, (int) $dept[$deptName]);
        foreach ($teachers as $i => $full) {
            [$tf, $tl] = explode(' ', $full);
            $teacherIds[$code][$i + 1] = person($pdo, 'teacher_reg', $tf, $tl, "teacher.$code" . ($i + 1) . '@demo.local', '+91 9' . str_pad((string) (++$n), 9, '0', STR_PAD_LEFT), $hash, $hod, (int) $dept[$deptName]);
        }
    }
    seed_academics($pdo, $dept, $teacherIds, $hash);
    echo "Demo logins (password: $password): admin@demo.local, principal@demo.local, hod.cs@demo.local, teacher.cs1@demo.local, student.cs1@demo.local\n";
} else {
    echo "DEMO is off: no demo data\n";
}

$adminEmail = (string) env('ADMIN_EMAIL', '');
$adminPass  = (string) env('ADMIN_PASSWORD', '');
if ($adminEmail !== '' && $adminPass !== '' && (int) $pdo->query('SELECT COUNT(*) FROM admin_reg')->fetchColumn() === 0) {
    person($pdo, 'admin_reg', 'Site', 'Admin', $adminEmail, '+91 90000 00000', password_hash($adminPass, PASSWORD_DEFAULT));
    echo "Created admin $adminEmail\n";
}
