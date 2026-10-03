<?php
// {role, email, password} -> starts the session
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$role  = role_param();
$email = post('email');
$pass  = $_POST['password'] ?? '';
$pass  = is_string($pass) ? $pass : '';

if (($_SESSION['login_lock'] ?? 0) > time()) {
    fail('Too many attempts. Please wait a little and try again.', 429);
}

$table = ROLES[$role]['table'];
$st = db()->prepare("SELECT id, password FROM $table WHERE email = ?");
$st->execute([$email]);
$u = $st->fetch();

$ok = false;
if ($u) {
    if (password_verify($pass, $u['password'])) {
        $ok = true;
        if (password_needs_rehash($u['password'], PASSWORD_DEFAULT)) {
            db()->prepare("UPDATE $table SET password = ? WHERE id = ?")
                ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
        }
    } elseif (strlen($u['password']) === 32 && hash_equals($u['password'], md5($pass))) {
        // Account created by the old version (unsalted MD5): accept once and upgrade the hash
        $ok = true;
        db()->prepare("UPDATE $table SET password = ? WHERE id = ?")
            ->execute([password_hash($pass, PASSWORD_DEFAULT), $u['id']]);
    }
} else {
    password_verify($pass, '$2y$10$usesomesillystringforsaltsalt.0123456789abcdefghijklmn'); // equalise timing
}

if (!$ok) {
    $_SESSION['login_fails'] = ($_SESSION['login_fails'] ?? 0) + 1;
    if ($_SESSION['login_fails'] >= 5) {
        $_SESSION['login_fails'] = 0;
        $_SESSION['login_lock']  = time() + 30;
    }
    fail('Incorrect email or password.', 401);
}

unset($_SESSION['login_fails'], $_SESSION['login_lock']);
login_session($role, (int) $u['id']);
json_out(['ok' => true, 'redirect' => url(ROLES[$role]['home'])]);
