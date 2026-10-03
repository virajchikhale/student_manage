<?php
// Live duplicate check used by the registration forms: {field: email|phone, value} -> {ok, taken}
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$field = post('field');
$value = post('value');
if ($field === 'email' && valid_email($value)) {
    json_out(['ok' => true, 'taken' => identity_taken('email', $value)]);
}
if ($field === 'phone' && valid_phone($value)) {
    json_out(['ok' => true, 'taken' => identity_taken('phone', $value)]);
}
fail('Invalid value');
