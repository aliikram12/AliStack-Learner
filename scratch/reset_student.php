<?php
require_once __DIR__ . '/../app/autoload.php';
$pdo = getDbConnection();
$hash = password_hash('Student@AliStack2026!', PASSWORD_BCRYPT);
$pdo->prepare("UPDATE users SET password_hash = ? WHERE id = 2")->execute([$hash]);
echo "Student 2 password set to Student@AliStack2026!\n";
