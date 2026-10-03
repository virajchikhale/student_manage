<?php
// Landing page for principals, HODs and teachers after login.
require_once __DIR__ . '/../includes/bootstrap.php';
$me  = require_login('principal', 'hod', 'teacher');
$pdo = db();
$role = $me['role'];

function table_of(PDO $pdo, string $sql, array $args, array $cols): string
{
    $st = $pdo->prepare($sql);
    $st->execute($args);
    $rows = $st->fetchAll();
    $h = '<div class="table-responsive"><table class="table table-bordered table-sm bg-white"><thead><tr>';
    foreach ($cols as $label) { $h .= '<th>' . e($label) . '</th>'; }
    $h .= '</tr></thead><tbody>';
    foreach ($rows as $r) {
        $h .= '<tr>';
        foreach (array_keys($cols) as $k) { $h .= '<td>' . e((string) $r[$k]) . '</td>'; }
        $h .= '</tr>';
    }
    return $h . ($rows ? '' : '<tr><td colspan="' . count($cols) . '" class="text-muted">None yet.</td></tr>') . '</tbody></table></div>';
}

$dept = null;
if ($me['department_id'] ?? null) {
    $st = $pdo->prepare('SELECT name FROM department WHERE id = ?');
    $st->execute([$me['department_id']]);
    $dept = $st->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e(ROLES[$role]['label']); ?> Portal</title>
    <link href="../admin/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet">
    <style>body{background:#f1f3f6}</style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
    <span class="navbar-brand">Student Management &middot; <?php echo e(ROLES[$role]['label']); ?></span>
    <a class="btn btn-outline-light btn-sm" href="../logout.php">Sign out</a>
</nav>
<div class="container py-4">
    <h3>Welcome, <?php echo e($me['first_name'] . ' ' . $me['last_name']); ?></h3>
    <p class="text-muted"><?php echo e($me['email']); ?> &middot; <?php echo e($me['phone']); ?><?php echo $dept ? ' &middot; ' . e($dept) . ' department' : ''; ?></p>

<?php if ($role === 'principal') { ?>
    <h5>Heads of department reporting to you</h5>
    <?php echo table_of($pdo,
        "SELECT h.first_name, h.last_name, h.email, d.name AS dept FROM hod_reg h
         LEFT JOIN department d ON d.id = h.department_id WHERE h.report_to = ? ORDER BY h.first_name",
        [$me['id']], ['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'dept' => 'Department']); ?>
<?php } elseif ($role === 'hod') { ?>
    <h5>Teachers in your department</h5>
    <?php echo table_of($pdo,
        "SELECT first_name, last_name, email, phone FROM teacher_reg WHERE report_to = ? ORDER BY first_name",
        [$me['id']], ['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'phone' => 'Phone']); ?>
<?php } else { ?>
    <h5>Your head of department</h5>
    <?php echo table_of($pdo,
        "SELECT first_name, last_name, email, phone FROM hod_reg WHERE id = ?",
        [$me['report_to'] ?? 0], ['first_name' => 'First name', 'last_name' => 'Last name', 'email' => 'Email', 'phone' => 'Phone']); ?>
<?php } ?>
</div>
</body>
</html>
