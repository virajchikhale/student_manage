<?php
/* Shared admin chrome. Usage: $me = admin_header('Title', 'dashboard'); ... admin_footer(); */

function admin_header(string $title, string $active): array
{
    $me = require_login('admin');
    $nav = [
        'dashboard'   => ['Dashboard',   'dashboard.php',   'fa-tachometer-alt'],
        'users'       => ['People',      'users.php',       'fa-users'],
        'departments' => ['Departments', 'departments.php', 'fa-building'],
    ];
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo e($title); ?> - Student Management</title>
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    <link href="vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">
    <link href="vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">
    <link href="css/theme.css" rel="stylesheet" media="all">
    <?php echo head_meta(); ?>
    <style>@media (max-width:991px){.menu-sidebar2{position:static;width:auto;height:auto;right:auto}}</style>
</head>
<body>
<div class="page-wrapper">
    <aside class="menu-sidebar2">
        <div class="logo"><a href="dashboard.php"><img src="images/icon/logo-white.png" alt="Student Management"></a></div>
        <div class="menu-sidebar2__content">
            <div class="account2">
                <div class="image img-cir img-120"><img src="images/icon/avatar-big-01.jpg" alt=""></div>
                <h4 class="name"><?php echo e(ucfirst($me['first_name']) . ' ' . ucfirst($me['last_name'])); ?></h4>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Sign out</a>
            </div>
            <nav class="navbar-sidebar2">
                <ul class="list-unstyled navbar__list">
                    <?php foreach ($nav as $key => [$label, $href, $icon]) { ?>
                    <li class="<?php echo $key === $active ? 'active' : ''; ?>">
                        <a href="<?php echo $href; ?>"><i class="fas <?php echo $icon; ?>"></i><?php echo $label; ?></a>
                    </li>
                    <?php } ?>
                    <li><a href="register.php"><i class="fas fa-user-plus"></i>Add admin</a></li>
                </ul>
            </nav>
        </div>
    </aside>
    <div class="page-container2">
        <div class="section__content section__content--p30 m-t-30">
            <div class="container-fluid">
                <h2 class="title-1 m-b-25"><?php echo e($title); ?></h2>
<?php
    return $me;
}

function admin_footer(): void
{
    ?>
            </div>
        </div>
    </div>
</div>
<script src="vendor/jquery-3.2.1.min.js"></script>
<script src="../includes/js/auth.js"></script>
</body>
</html>
<?php
}
