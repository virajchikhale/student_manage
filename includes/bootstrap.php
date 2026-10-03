<?php
/*
 * Shared bootstrap: configuration from the environment, PDO connection, hardened
 * session, CSRF, JSON helpers and mail. Every page and API endpoint includes this.
 */
declare(strict_types=1);

function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    return ($v === false || $v === '') ? $default : $v;
}

date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));

function demo_mode(): bool
{
    return in_array(strtolower((string) env('DEMO', 'false')), ['true', '1', 'yes'], true);
}

/** Role => table (the only table names that ever reach SQL), label and landing page. */
const ROLES = [
    'admin'     => ['table' => 'admin_reg',     'label' => 'Admin',     'home' => 'admin/dashboard.php',
                    'login' => 'admin/index.php'],
    'principal' => ['table' => 'principal_reg', 'label' => 'Principal', 'home' => 'portal/index.php',
                    'login' => 'login/principal_reg.php'],
    'hod'       => ['table' => 'hod_reg',       'label' => 'HOD',       'home' => 'portal/index.php',
                    'login' => 'login/hod_login.php'],
    'teacher'   => ['table' => 'teacher_reg',   'label' => 'Teacher',   'home' => 'portal/index.php',
                    'login' => 'login/teacher_reg.php'],
    'student'   => ['table' => 'student',       'label' => 'Student',   'home' => 'portal/index.php',
                    'login' => 'login/student_login.php'],
];

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            env('DB_HOST', 'localhost'),
            env('DB_PORT', '5432'),
            env('DB_NAME', 'student_management')
        );
        $pdo = new PDO($dsn, env('DB_USER', 'postgres'), env('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/** URL path of the application root (works at the docroot or in a sub-folder like /student_manage). */
function app_root(): string
{
    $docroot = rtrim(str_replace('\\', '/', (string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $app     = str_replace('\\', '/', dirname(__DIR__));
    return ($docroot !== '' && str_starts_with($app, $docroot)) ? substr($app, strlen($docroot)) : '';
}

function url(string $path = ''): string
{
    return app_root() . '/' . ltrim($path, '/');
}

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_name('SMSSESSID');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'path' => '/']);
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Put inside <head>: exposes the CSRF token and app root to js/auth.js. */
function head_meta(): string
{
    return '<meta name="csrf-token" content="' . e(csrf_token()) . '">'
         . '<script>window.APP_ROOT=' . json_encode(app_root()) . ';</script>';
}

/* ---------- Auth ---------- */

function current_user(): ?array
{
    $a = $_SESSION['auth'] ?? null;
    if (!$a || !isset(ROLES[$a['role']])) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM ' . ROLES[$a['role']]['table'] . ' WHERE id = ?');
    $st->execute([$a['id']]);
    $u = $st->fetch();
    if (!$u) {                       // account deleted while logged in
        unset($_SESSION['auth']);
        return null;
    }
    $u['role'] = $a['role'];
    return $u;
}

/** For pages: returns the logged-in user or redirects to that role's login page. */
function require_login(string ...$roles): array
{
    $u = current_user();
    if (!$u || ($roles && !in_array($u['role'], $roles, true))) {
        header('Location: ' . url($roles ? ROLES[$roles[0]]['login'] : 'index.php'));
        exit;
    }
    return $u;
}

function login_session(string $role, int $id): void
{
    session_regenerate_id(true);
    $_SESSION['auth'] = ['role' => $role, 'id' => $id];
}

/** Admin sign-up is only open for the very first admin, or to a logged-in admin adding another. */
function admin_signup_open(): bool
{
    $u = current_user();
    if ($u && $u['role'] === 'admin') {
        return true;
    }
    return (int) db()->query('SELECT COUNT(*) FROM admin_reg')->fetchColumn() === 0;
}

/* ---------- JSON API helpers ---------- */

function json_out(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail(string $message, int $status = 400): never
{
    json_out(['ok' => false, 'error' => $message], $status);
}

/** First thing in every API endpoint: POST only, valid CSRF token, uncaught errors become JSON. */
function api_guard(): void
{
    set_exception_handler(function (Throwable $t): void {
        error_log('API error: ' . $t);
        fail('Server error, please try again.', 500);
    });
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        fail('POST required', 405);
    }
    $sent = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrf_token(), (string) $sent)) {
        fail('Session expired, please reload the page.', 403);
    }
}

function post(string $key): string
{
    $v = $_POST[$key] ?? '';
    return is_string($v) ? trim($v) : '';
}

function role_param(string $key = 'role'): string
{
    $r = post($key);
    if (!isset(ROLES[$r])) {
        fail('Unknown role');
    }
    return $r;
}

/** Is this email or phone already used by any role? */
function identity_taken(string $column, string $value): bool
{
    foreach (ROLES as $r) {
        $st = db()->prepare("SELECT 1 FROM {$r['table']} WHERE $column = ? LIMIT 1");
        $st->execute([$value]);
        if ($st->fetchColumn()) {
            return true;
        }
    }
    return false;
}

function valid_email(string $s): bool
{
    return strlen($s) <= 255 && filter_var($s, FILTER_VALIDATE_EMAIL) !== false;
}

function valid_phone(string $s): bool
{
    return (bool) preg_match('/^\+?[0-9][0-9 \-]{6,18}$/', $s);
}

/* ---------- OTP (kept in the session, never sent to the browser except in DEMO) ---------- */

function otp_key(string $purpose, string $role, string $email): string
{
    return $purpose . '|' . $role . '|' . strtolower($email);
}

function otp_issue(string $purpose, string $role, string $email): string
{
    $k = otp_key($purpose, $role, $email);
    $prev = $_SESSION['otp'][$k] ?? null;
    if ($prev && time() - $prev['sent'] < 30) {
        fail('Please wait a few seconds before requesting another OTP.', 429);
    }
    $otp = (string) random_int(100000, 999999);
    $_SESSION['otp'][$k] = [
        'hash'  => hash_hmac('sha256', $otp, csrf_token()),
        'exp'   => time() + 600,
        'tries' => 0,
        'sent'  => time(),
    ];
    return $otp;
}

/** Single-use check: returns true once, then the OTP is gone. */
function otp_consume(string $purpose, string $role, string $email, string $otp): bool
{
    $k = otp_key($purpose, $role, $email);
    $rec = $_SESSION['otp'][$k] ?? null;
    if (!$rec || $rec['exp'] < time() || $rec['tries'] >= 5) {
        unset($_SESSION['otp'][$k]);
        return false;
    }
    if (!hash_equals($rec['hash'], hash_hmac('sha256', $otp, csrf_token()))) {
        $_SESSION['otp'][$k]['tries']++;
        return false;
    }
    unset($_SESSION['otp'][$k]);
    return true;
}

/* ---------- Mail ---------- */

function send_mail(string $to, string $subject, string $html): bool
{
    require_once __DIR__ . '/../email/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../email/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../email/phpmailer/src/SMTP.php';
    try {
        $m = new PHPMailer\PHPMailer\PHPMailer(true);
        $m->isSMTP();
        $m->Host    = env('SMTP_HOST', 'localhost');
        $m->Port    = (int) env('SMTP_PORT', '1025');
        $m->Timeout = 5;
        $secure     = strtolower((string) env('SMTP_SECURE', 'none'));
        $m->SMTPSecure  = $secure === 'ssl' ? 'ssl' : ($secure === 'tls' ? 'tls' : '');
        $m->SMTPAutoTLS = $secure !== 'none';
        if (env('SMTP_USER')) {
            $m->SMTPAuth = true;
            $m->Username = (string) env('SMTP_USER');
            $m->Password = (string) env('SMTP_PASSWORD', '');
        }
        $m->setFrom((string) env('MAIL_FROM', 'no-reply@student.local'), (string) env('MAIL_FROM_NAME', 'Student Management'));
        $m->addAddress($to);
        $m->isHTML(true);
        $m->Subject = $subject;
        $m->Body    = $html;
        $m->send();
        return true;
    } catch (Throwable $t) {
        error_log('Mail to ' . $to . ' failed: ' . $t->getMessage());
        return false;
    }
}

/** Demo logins banner, shown on the login pages when DEMO=true. */
function demo_banner(string $role): string
{
    if (!demo_mode()) {
        return '';
    }
    $pw = e(env('DEMO_PASSWORD', 'demo12345'));
    $map = ['admin' => 'admin@demo.local', 'principal' => 'principal@demo.local',
            'hod' => 'hod.cs@demo.local', 'teacher' => 'teacher.cs1@demo.local', 'student' => 'student.cs1@demo.local'];
    return '<div style="background:#fff3cd;color:#664d03;border:1px solid #ffecb5;border-radius:6px;padding:8px 12px;margin:10px 0;font-size:13px;text-align:left">'
         . '<b>Demo mode</b> &mdash; sign in with <code>' . e($map[$role]) . '</code> / <code>' . $pw . '</code>'
         . '<br>Data is wiped when the demo stops. OTP codes are shown on screen.</div>';
}

require_once __DIR__ . '/domain.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/dashboard.php';
