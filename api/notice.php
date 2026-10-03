<?php
// Notices: {action: save | delete}. Any staff member posts; authors (and admins) delete.
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();
$me  = api_user('admin', 'principal', 'hod', 'teacher');
$pdo = db();

switch (post('action')) {
    case 'save':
        $title = post('title');
        $body  = post('body');
        $aud   = post('audience');
        if ($title === '' || mb_strlen($title) > 200) {
            fail('Please enter a title (up to 200 characters).');
        }
        if ($body === '' || mb_strlen($body) > 5000) {
            fail('Please write the notice (up to 5000 characters).');
        }
        if (!in_array($aud, ['all', 'staff', 'students'], true)) {
            fail('Please choose who should see this notice.');
        }
        $pdo->prepare('INSERT INTO notice (title, body, audience, author_role, author_id, author_name) VALUES (?,?,?,?,?,?)')
            ->execute([$title, $body, $aud, $me['role'], $me['id'], full_name($me) . ' (' . ROLES[$me['role']]['label'] . ')']);
        break;

    case 'delete':
        $st = $pdo->prepare('SELECT * FROM notice WHERE id = ?');
        $st->execute([(int) post('id')]);
        $n = $st->fetch() ?: fail('Notice not found.', 404);
        if ($me['role'] !== 'admin' && !($n['author_role'] === $me['role'] && (int) $n['author_id'] === (int) $me['id'])) {
            fail('You can only delete your own notices.', 403);
        }
        $pdo->prepare('DELETE FROM notice WHERE id = ?')->execute([$n['id']]);
        break;

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
