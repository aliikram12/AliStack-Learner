<?php
require_once __DIR__ . '/../app/autoload.php';

use App\Services\AiService;

$ai = new AiService();
echo "Testing generateAssessmentQuestions for Course 4 (Web Dev)...\n";
$result = $ai->generateAssessmentQuestions(4, 3, 'medium', 'HTML5 and CSS Flexbox');
print_r($result);
