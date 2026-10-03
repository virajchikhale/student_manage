<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me = page_header('My profile', 'profile');
$name = full_name($me);
$dept = null;
if (!empty($me['department_id'])) {
    $st = db()->prepare('SELECT name FROM department WHERE id = ?');
    $st->execute([$me['department_id']]);
    $dept = $st->fetchColumn();
}
?>
<div class="card"><div class="card-body profile-head">
    <?php echo avatar($name, 'lg'); ?>
    <div><h3><?php echo e($name); ?></h3>
        <div class="text-muted"><?php echo e($me['email']); ?></div>
        <div class="mt-2"><?php echo badge(ROLES[$me['role']]['label'], 'brand'); ?> <?php echo $dept ? badge($dept, 'secondary') : ''; ?></div></div>
</div></div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-head"><h5>Personal details</h5></div>
        <form class="card-body" data-api="profile" data-action="update" data-ok="Profile updated">
            <div class="form-row">
                <div class="form-group col-md-6"><label>First name</label><input class="form-control" name="first_name" value="<?php echo e($me['first_name']); ?>" maxlength="100" required></div>
                <div class="form-group col-md-6"><label>Last name</label><input class="form-control" name="last_name" value="<?php echo e($me['last_name']); ?>" maxlength="100" required></div>
            </div>
            <div class="form-group"><label>Phone</label><input class="form-control" name="phone" value="<?php echo e($me['phone']); ?>" maxlength="20" required></div>
            <div class="form-group"><label>Email</label><input class="form-control" value="<?php echo e($me['email']); ?>" disabled>
                <span class="form-hint">Your email is your sign-in name. Ask an administrator to change it.</span></div>
            <button class="btn btn-primary" type="submit">Save changes</button>
        </form>
    </div>
    <div class="card">
        <div class="card-head"><h5>Change password</h5></div>
        <form class="card-body" id="pwForm" data-api="profile" data-action="change_password" data-ok="Password changed">
            <div class="form-group"><label>Current password</label><input class="form-control" type="password" name="current" autocomplete="current-password" required></div>
            <div class="form-group"><label>New password</label><input class="form-control" type="password" name="password" id="np" minlength="8" autocomplete="new-password" required>
                <span class="form-hint">At least 8 characters.</span></div>
            <div class="form-group"><label>Confirm new password</label><input class="form-control" type="password" id="cp" autocomplete="new-password" required></div>
            <button class="btn btn-primary" type="submit">Update password</button>
        </form>
    </div>
</div>
<script>
$('#pwForm').on('submit', function (e) {
    if ($('#np').val() !== $('#cp').val()) { e.preventDefault(); e.stopImmediatePropagation(); App.toast('The new passwords do not match.', 'err'); }
});
</script>
<?php page_footer();
