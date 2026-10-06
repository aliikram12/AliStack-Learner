<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Services\AssessmentService;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::check()) {
    Response::error('Authentication required', 401);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$assessmentId = !empty($data['assessment_id']) ? (int)$data['assessment_id'] : 0;
if (!$assessmentId) {
    Response::error('assessment_id is required', 400);
}

$userId = (int)Auth::id();
$service = new AssessmentService();
$result = $service->startAttempt($userId, $assessmentId);

if (!$result['success']) {
    Response::error($result['error'], 403);
}

// Ensure correct options and explanations are stripped from client delivery during the active test!
$safeQuestions = array_map(function($q) {
    return [
        'id' => (int)$q['id'],
        'question_text' => $q['question_text'],
        'option_a' => $q['option_a'],
        'option_b' => $q['option_b'],
        'option_c' => $q['option_c'],
        'option_d' => $q['option_d'],
        'topic' => $q['topic'] ?? null,
        'difficulty' => $q['difficulty'] ?? 'medium',
        'marks' => (int)$q['marks']
    ];
}, $result['questions']);

Response::success([
    'attempt_id' => $result['attempt']['id'],
    'time_limit_minutes' => (int)$result['attempt']['time_limit_minutes'],
    'questions' => $safeQuestions,
    'total_questions' => count($safeQuestions),
    'is_resumed' => $result['is_resumed']
], 'Assessment attempt started');
