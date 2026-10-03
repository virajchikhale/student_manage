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

if (demo_mode()) {
    echo "DEMO=true: resetting and loading demo data\n";
    $pdo->exec('TRUNCATE teacher_reg, hod_reg, principal_reg, admin_reg, details, department RESTART IDENTITY CASCADE');
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
    foreach ($people as [$deptName, $code, $hf, $hl, $teachers]) {
        $hod = person($pdo, 'hod_reg', $hf, $hl, "hod.$code@demo.local", '+91 9' . str_pad((string) (++$n), 9, '0', STR_PAD_LEFT), $hash, $principal, (int) $dept[$deptName]);
        foreach ($teachers as $i => $full) {
            [$tf, $tl] = explode(' ', $full);
            person($pdo, 'teacher_reg', $tf, $tl, "teacher.$code" . ($i + 1) . '@demo.local', '+91 9' . str_pad((string) (++$n), 9, '0', STR_PAD_LEFT), $hash, $hod, (int) $dept[$deptName]);
        }
    }
    echo "Demo logins (password: $password): admin@demo.local, principal@demo.local, hod.cs@demo.local, teacher.cs1@demo.local\n";
} else {
    echo "DEMO is off: no demo data\n";
}

$adminEmail = (string) env('ADMIN_EMAIL', '');
$adminPass  = (string) env('ADMIN_PASSWORD', '');
if ($adminEmail !== '' && $adminPass !== '' && (int) $pdo->query('SELECT COUNT(*) FROM admin_reg')->fetchColumn() === 0) {
    person($pdo, 'admin_reg', 'Site', 'Admin', $adminEmail, '+91 90000 00000', password_hash($adminPass, PASSWORD_DEFAULT));
    echo "Created admin $adminEmail\n";
}
