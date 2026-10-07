<?php
require_once __DIR__ . '/../app/autoload.php';
$pdo = getDbConnection();
$cols = $pdo->query("DESCRIBE discussion_replies")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($cols, JSON_PRETTY_PRINT);
