<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();
$stmt = $pdo->query('DESCRIBE course_categories');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
