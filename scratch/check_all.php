<?php
require_once __DIR__ . '/../app/autoload.php';
$db = getDbConnection();

echo "=== COURSES ===" . PHP_EOL;
$stmt = $db->query('SELECT * FROM courses');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== ASSESSMENTS ===" . PHP_EOL;
$stmt = $db->query('SELECT * FROM assessments');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "=== QUESTIONS ===" . PHP_EOL;
$stmt = $db->query('SELECT count(*) as total_q FROM assessment_questions');
print_r($stmt->fetch(PDO::FETCH_ASSOC));
