<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Services\AiService;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::check() || !in_array(Auth::user()['role'] ?? '', ['admin', 'super_admin'], true)) {
    Response::error('Administrative authorization required', 403);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$courseId = !empty($data['course_id']) ? (int)$data['course_id'] : 0;
if (!$courseId) {
    Response::error('Course ID is required', 400);
}

$count = !empty($data['count']) ? max(1, min(15, (int)$data['count'])) : 5;
$difficulty = !empty($data['difficulty']) ? (string)$data['difficulty'] : 'medium';
$topic = !empty($data['topic']) ? trim((string)$data['topic']) : null;

$aiService = new AiService();
$result = $aiService->generateAssessmentQuestions($courseId, $count, $difficulty, $topic);

if (!$result['success']) {
    Response::error($result['error'] ?? 'Failed to generate questions', 500);
}

Response::success($result, 'MCQ Questions successfully generated with AI');
