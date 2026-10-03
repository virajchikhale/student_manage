<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$me = page_header('Notices', 'notices');
$notices = notices_for($me, 100);
$canPost = is_staff($me);
$labels = ['all' => 'Everyone', 'staff' => 'Staff only', 'students' => 'Students only'];
?>
<?php if ($canPost) { ?>
<div class="page-actions"><span class="spacer"></span>
    <button class="btn btn-primary" id="addNotice"><i class="fas fa-plus mr-1"></i> New notice</button></div>
<?php } ?>

<?php if (!$notices) { ?>
<div class="card"><?php echo empty_state('fa-bullhorn', 'No notices yet', $canPost ? 'Post the first announcement.' : 'Announcements from your institution appear here.'); ?></div>
<?php } else { foreach ($notices as $n) {
    $mine = $n['author_role'] === $me['role'] && (int) $n['author_id'] === (int) $me['id']; ?>
<div class="card notice-card">
    <div class="d-flex justify-content-between align-items-start" style="gap:12px">
        <div><h5><?php echo e($n['title']); ?></h5>
            <div class="meta"><?php echo e($n['author_name']); ?> &middot; <?php echo e(date('d M Y, h:i A', strtotime($n['created_at']))); ?> &middot; <?php echo badge($labels[$n['audience']], 'secondary'); ?></div></div>
        <?php if ($canPost && ($mine || $me['role'] === 'admin')) { ?>
        <button class="btn btn-light btn-icon text-danger" title="Delete" data-api="notice" data-action="delete" data-id="<?php echo (int) $n['id']; ?>"
            data-ok="Notice deleted" data-confirm="Delete this notice?"><i class="far fa-trash-alt"></i></button>
        <?php } ?>
    </div>
    <div class="body"><?php echo e($n['body']); ?></div>
</div>
<?php } } ?>

<?php if ($canPost) { ?>
<div class="modal fade" id="noticeModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form data-api="notice" data-action="save" data-ok="Notice posted">
    <div class="modal-header"><h5 class="modal-title">New notice</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
    <div class="modal-body">
        <div class="form-group"><label>Title *</label><input class="form-control" name="title" maxlength="200" required></div>
        <div class="form-group"><label>Visible to *</label>
            <select class="custom-select" name="audience"><?php echo options(array_map(null, array_keys($labels), array_values($labels)), 'all'); ?></select></div>
        <div class="form-group mb-0"><label>Message *</label><textarea class="form-control" name="body" rows="6" maxlength="5000" required></textarea></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Post notice</button></div>
    </form>
</div></div></div>
<script>$('#addNotice').on('click', function () { App.openForm('#noticeModal', {audience: 'all'}); });</script>
<?php }
page_footer();
