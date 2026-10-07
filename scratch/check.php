<?php
require_once __DIR__ . '/../app/autoload.php';
$db = getDbConnection();

echo "=== USERS ===" . PHP_EOL;
$stmt = $db->query('SELECT id, full_name, email, role, status FROM users');
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "ID {$u['id']} | {$u['full_name']} | {$u['email']} | {$u['role']} | {$u['status']}" . PHP_EOL;
}
