<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();
echo "=== DISCUSSION_GROUPS ===\n";
print_r($pdo->query('DESCRIBE discussion_groups')->fetchAll(PDO::FETCH_ASSOC));
echo "=== ASSESSMENTS ===\n";
print_r($pdo->query('DESCRIBE assessments')->fetchAll(PDO::FETCH_ASSOC));
