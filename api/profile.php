<?php
// The signed-in user's own account: {action: update | change_password}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me    = api_user();
$table = ROLES[$me['role']]['table'];
$pdo   = db();

switch (post('action')) {
    case 'update':
        $first = post('first_name');
        $last  = post('last_name');
        $phone = post('phone');
        if ($first === '' || $last === '' || mb_strlen($first) > 100 || mb_strlen($last) > 100) {
            fail('Please enter your first and last name.');
        }
        if (!valid_phone($phone)) {
            fail('Please enter a valid phone number.');
        }
        if ($phone !== $me['phone'] && identity_taken('phone', $phone)) {
            fail('This phone number already exists in the system.');
        }
        $pdo->prepare("UPDATE $table SET first_name = ?, last_name = ?, phone = ? WHERE id = ?")
            ->execute([$first, $last, $phone, $me['id']]);
        break;

    case 'change_password':
        $current = $_POST['current'] ?? '';
        $new     = $_POST['password'] ?? '';
        if (!is_string($current) || !is_string($new) || !password_verify($current, $me['password'])) {
            fail('Your current password is not correct.');
        }
        if (strlen($new) < 8) {
            fail('The new password must be at least 8 characters.');
        }
        $pdo->prepare("UPDATE $table SET password = ? WHERE id = ?")->execute([password_hash($new, PASSWORD_DEFAULT), $me['id']]);
        send_mail($me['email'], 'Alert from ' . env('MAIL_FROM_NAME', 'Student Management'),
            'The password for ' . e($me['email']) . ' has been changed. If this was not you, contact your administrator.');
        break;

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
