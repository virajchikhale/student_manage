<?php
/*
 * Application shell shared by every signed-in page (all roles) plus small UI helpers.
 *   $me = page_header('Students', 'students');   ... page content ...   page_footer();
 * Pass roles to restrict a page:  page_header('People', 'users', 'admin');
 */

function nav_for(string $role): array
{
    $app = fn (string $f) => 'app/' . $f;
    $home = $role === 'admin' ? 'admin/dashboard.php' : 'portal/index.php';
    $items = [
        'Overview' => [['dashboard', 'Dashboard', $home, 'fa-th-large']],
    ];
    if ($role === 'student') {
        $items['My studies'] = [
            ['record',  'My record',  $app('student.php'),  'fa-id-card'],
            ['courses', 'My courses', $app('courses.php'),  'fa-book'],
            ['notices', 'Notices',    $app('notices.php'),  'fa-bullhorn'],
        ];
    } else {
        $items['Academics'] = [
            ['students',   'Students',        $app('students.php'),   'fa-graduation-cap'],
            ['courses',    'Courses',         $app('courses.php'),    'fa-book'],
            ['attendance', 'Attendance',      $app('attendance.php'), 'fa-calendar-check'],
            ['exams',      'Exams & marks',   $app('exams.php'),      'fa-clipboard-list'],
            ['reports',    'Reports',         $app('reports.php'),    'fa-chart-bar'],
        ];
        $items['Campus'] = [
            ['notices', 'Notices', $app('notices.php'), 'fa-bullhorn'],
            ['staff',   'Staff',   $app('staff.php'),   'fa-id-badge'],
        ];
    }
    if ($role === 'admin') {
        $items['Administration'] = [
            ['users',       'People',      'admin/users.php',       'fa-users'],
            ['departments', 'Departments', 'admin/departments.php', 'fa-building'],
            ['addadmin',    'Add admin',   'admin/register.php',    'fa-user-plus'],
        ];
    }
    $items['Account'] = [['profile', 'My profile', $app('profile.php'), 'fa-cog']];
    return $items;
}

function page_header(string $title, string $active, string ...$roles): array
{
    $me   = require_login(...$roles);
    $role = $me['role'];
    $nav  = nav_for($role);
    $name = full_name($me);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo e($title); ?> - Student Management</title>
    <link href="<?php echo e(url('admin/css/font-face.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('admin/vendor/bootstrap-4.1/bootstrap.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('admin/vendor/font-awesome-5/css/fontawesome-all.min.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(url('includes/css/app.css')); ?>" rel="stylesheet">
    <?php echo head_meta(); ?>
    <!-- scripts load here (not at the end) so each page's inline script can use jQuery and App -->
    <script src="<?php echo e(url('admin/vendor/jquery-3.2.1.min.js')); ?>"></script>
    <script src="<?php echo e(url('admin/vendor/bootstrap-4.1/popper.min.js')); ?>"></script>
    <script src="<?php echo e(url('admin/vendor/bootstrap-4.1/bootstrap.min.js')); ?>"></script>
    <script src="<?php echo e(url('includes/js/auth.js')); ?>"></script>
    <script src="<?php echo e(url('includes/js/app.js')); ?>"></script>
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?php echo e(url(ROLES[$role]['home'])); ?>">
            <span class="brand-mark"><i class="fas fa-graduation-cap"></i></span>
            <span>Student<br><small>Management</small></span>
        </a>
        <nav class="side-nav">
            <?php foreach ($nav as $section => $links) { ?>
            <div class="side-title"><?php echo e($section); ?></div>
            <?php foreach ($links as [$key, $label, $href, $icon]) { ?>
            <a class="side-link <?php echo $key === $active ? 'active' : ''; ?>" href="<?php echo e(url($href)); ?>">
                <i class="fas <?php echo $icon; ?>"></i><span><?php echo e($label); ?></span>
            </a>
            <?php } } ?>
        </nav>
        <div class="side-user">
            <span class="avatar"><?php echo e(initials($name)); ?></span>
            <div class="side-user-text"><strong><?php echo e($name); ?></strong><small><?php echo e(ROLES[$role]['label']); ?></small></div>
            <a href="<?php echo e(url('logout.php')); ?>" title="Sign out" class="side-out"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>
    <div class="side-backdrop" id="backdrop"></div>
    <div class="main">
        <header class="topbar">
            <button class="burger" id="burger" aria-label="Menu"><i class="fas fa-bars"></i></button>
            <h1 class="page-title"><?php echo e($title); ?></h1>
            <div class="topbar-right">
                <span class="today d-none d-md-inline"><i class="far fa-calendar-alt"></i> <?php echo date('D, d M Y'); ?></span>
                <a class="btn btn-sm btn-light" href="<?php echo e(url('logout.php')); ?>"><i class="fas fa-sign-out-alt"></i> <span class="d-none d-sm-inline">Sign out</span></a>
            </div>
        </header>
        <main class="content">
<?php
    return $me;
}

function page_footer(): void
{
    ?>
        </main>
    </div>
</div>
<div class="toasts" id="toasts" aria-live="polite"></div>
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm" role="document"><div class="modal-content">
        <div class="modal-body text-center p-4">
            <div class="confirm-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <p class="mb-0" id="confirmText">Are you sure?</p>
        </div>
        <div class="modal-footer justify-content-center border-0 pt-0">
            <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" id="confirmOk">Yes, continue</button>
        </div>
    </div></div>
</div>
</body>
</html>
<?php
}

/* ---------- small UI helpers (all return escaped HTML) ---------- */

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $s = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $s .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $s !== '' ? $s : '?';
}

function avatar(string $name, string $size = ''): string
{
    $hue = abs(crc32($name)) % 360;
    return '<span class="avatar ' . $size . '" style="background:hsl(' . $hue . ',55%,92%);color:hsl(' . $hue . ',50%,32%)">'
         . e(initials($name)) . '</span>';
}

function stat_card(string $icon, string $label, string|int|float $value, string $tone = 'brand', string $sub = ''): string
{
    return '<div class="stat"><span class="stat-icon tone-' . e($tone) . '"><i class="fas ' . e($icon) . '"></i></span>'
         . '<div><div class="stat-value">' . e((string) $value) . '</div><div class="stat-label">' . e($label) . '</div>'
         . ($sub !== '' ? '<div class="stat-sub">' . e($sub) . '</div>' : '') . '</div></div>';
}

function badge(string $text, string $tone = 'secondary'): string
{
    return '<span class="pill pill-' . e($tone) . '">' . e($text) . '</span>';
}

/** Horizontal bar list. $rows = [[label, value, tone?, valueLabel?]]. */
function bars(array $rows, float $max = 0, string $empty = 'Nothing to show yet.'): string
{
    if (!$rows) {
        return '<div class="empty-mini">' . e($empty) . '</div>';
    }
    if ($max <= 0) {
        $max = max(1, ...array_map(fn ($r) => (float) $r[1], $rows));
    }
    $h = '<div class="bars">';
    foreach ($rows as $r) {
        $w = max(0, min(100, (float) $r[1] / $max * 100));
        $tone = $r[2] ?? 'brand';
        $h .= '<div class="bar-row"><div class="bar-label" title="' . e((string) $r[0]) . '">' . e((string) $r[0]) . '</div>'
            . '<div class="bar-track"><div class="bar-fill tone-bg-' . e($tone) . '" style="width:' . $w . '%"></div></div>'
            . '<div class="bar-val">' . e((string) ($r[3] ?? $r[1])) . '</div></div>';
    }
    return $h . '</div>';
}

/** Ring showing a percentage. */
function ring(float $pct, string $label, string $tone = 'brand'): string
{
    $p = max(0, min(100, $pct));
    return '<div class="ring tone-ring-' . e($tone) . '" style="--p:' . $p . '"><div class="ring-in"><b>' . e(rtrim(rtrim(number_format($pct, 1), '0'), '.')) . '%</b><small>' . e($label) . '</small></div></div>';
}

/** Vertical mini columns, e.g. attendance per day. $rows = [[label, pct]]. */
function columns(array $rows, string $empty = 'No data yet.'): string
{
    if (!$rows) {
        return '<div class="empty-mini">' . e($empty) . '</div>';
    }
    $h = '<div class="cols">';
    foreach ($rows as [$label, $v]) {
        $tone = attendance_tone((float) $v);
        $h .= '<div class="col-item" title="' . e($label . ': ' . $v . '%') . '"><div class="col-track"><div class="col-fill tone-bg-' . $tone
            . '" style="height:' . max(3, min(100, (float) $v)) . '%"></div></div><div class="col-label">' . e($label) . '</div></div>';
    }
    return $h . '</div>';
}

function empty_state(string $icon, string $title, string $text = ''): string
{
    return '<div class="empty"><i class="fas ' . e($icon) . '"></i><h5>' . e($title) . '</h5>'
         . ($text !== '' ? '<p>' . e($text) . '</p>' : '') . '</div>';
}

/** Pagination links keeping the other query parameters. */
function pager(int $total, int $page, int $per, array $query = []): string
{
    $pages = (int) ceil($total / $per);
    if ($pages <= 1) {
        return '';
    }
    $link = function (int $p, string $label, bool $active = false, bool $disabled = false) use ($query): string {
        $q = http_build_query(array_merge($query, ['page' => $p]));
        return '<li class="page-item' . ($active ? ' active' : '') . ($disabled ? ' disabled' : '') . '"><a class="page-link" href="?' . e($q) . '">' . $label . '</a></li>';
    };
    $h = '<nav class="pager"><ul class="pagination pagination-sm mb-0">' . $link(max(1, $page - 1), '&laquo;', false, $page <= 1);
    for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
        $h .= $link($i, (string) $i, $i === $page);
    }
    return $h . $link(min($pages, $page + 1), '&raquo;', false, $page >= $pages) . '</ul></nav>';
}

/** <option> list. $rows = [[value, label]]. */
function options(array $rows, string|int|null $selected = null, ?string $placeholder = null): string
{
    $h = $placeholder !== null ? '<option value="">' . e($placeholder) . '</option>' : '';
    foreach ($rows as [$v, $l]) {
        $h .= '<option value="' . e((string) $v) . '"' . ((string) $v === (string) $selected ? ' selected' : '') . '>' . e((string) $l) . '</option>';
    }
    return $h;
}

function notices_for(array $u, int $limit = 50): array
{
    $aud = $u['role'] === 'student' ? ['all', 'students'] : ['all', 'staff'];
    $st = db()->prepare(
        'SELECT * FROM notice WHERE audience IN (?, ?) ORDER BY created_at DESC, id DESC LIMIT ' . (int) $limit
    );
    $st->execute($aud);
    return $st->fetchAll();
}

function notice_list(array $rows): string
{
    if (!$rows) {
        return '<div class="empty-mini">No notices yet.</div>';
    }
    $h = '<div class="notice-list">';
    foreach ($rows as $n) {
        $h .= '<div class="notice"><div class="notice-head"><strong>' . e($n['title']) . '</strong>'
            . badge($n['audience'] === 'all' ? 'Everyone' : ($n['audience'] === 'staff' ? 'Staff' : 'Students'), 'secondary') . '</div>'
            . '<p>' . nl2br(e(mb_strimwidth($n['body'], 0, 160, '…'))) . '</p>'
            . '<small>' . e($n['author_name']) . ' &middot; ' . e(date('d M Y', strtotime($n['created_at']))) . '</small></div>';
    }
    return $h . '</div>';
}
