<?php
require_once __DIR__ . '/../app/autoload.php';
$pdo = getDbConnection();
$users = $pdo->query("SELECT id, full_name, email, role FROM users")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users, JSON_PRETTY_PRINT);
