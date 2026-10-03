<?php
// Admin-only management actions from the dashboard.
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$me = current_user();
if (!$me || $me['role'] !== 'admin') {
    fail('Not allowed', 403);
}
$pdo = db();

switch (post('action')) {
    case 'add_department':
        $name = post('name');
        if ($name === '' || mb_strlen($name) > 255) {
            fail('Enter a department name.');
        }
        try {
            $pdo->prepare('INSERT INTO department (name, status) VALUES (?, 0)')->execute([$name]);
        } catch (PDOException $ex) {
            fail($ex->getCode() === '23505' ? 'That department already exists.' : 'Could not add department.');
        }
        break;

    case 'delete_department':
        $st = $pdo->prepare('SELECT 1 FROM hod_reg WHERE department_id = ?');
        $st->execute([(int) post('id')]);
        if ($st->fetchColumn()) {
            fail('This department has an HOD. Remove the HOD first.');
        }
        $pdo->prepare('DELETE FROM department WHERE id = ?')->execute([(int) post('id')]);
        break;

    case 'new_code':
        $code = strtoupper(bin2hex(random_bytes(4)));
        $pdo->prepare('INSERT INTO details (principal_verification) VALUES (?)')->execute([$code]);
        json_out(['ok' => true, 'code' => $code]);

    case 'delete_code':
        $pdo->prepare('DELETE FROM details WHERE id = ?')->execute([(int) post('id')]);
        break;

    case 'delete_user':
        $role = role_param();
        $id   = (int) post('id');
        if ($role === 'admin' && $id === (int) $me['id']) {
            fail('You cannot delete your own account.');
        }
        if ($role === 'admin' && (int) $pdo->query('SELECT COUNT(*) FROM admin_reg')->fetchColumn() <= 1) {
            fail('At least one admin must remain.');
        }
        $pdo->beginTransaction();
        if ($role === 'hod') {
            $pdo->prepare('UPDATE department SET status = 0 WHERE id = (SELECT department_id FROM hod_reg WHERE id = ?)')->execute([$id]);
        }
        $pdo->prepare('DELETE FROM ' . ROLES[$role]['table'] . ' WHERE id = ?')->execute([$id]);
        $pdo->commit();
        break;

    default:
        fail('Unknown action');
}
json_out(['ok' => true]);
