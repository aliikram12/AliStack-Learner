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

$attemptId = !empty($data['attempt_id']) ? (int)$data['attempt_id'] : 0;
$answers = is_array($data['answers'] ?? null) ? $data['answers'] : [];

if (!$attemptId) {
    Response::error('attempt_id is required', 400);
}

$userId = (int)Auth::id();
$service = new AssessmentService();
$result = $service->submitAssessment($userId, $attemptId, $answers);

if (!$result['success']) {
    Response::error($result['error'], 400);
}

Response::success([
    'result' => $result['result'],
    'certificate' => $result['certificate'],
    'redirect_url' => baseUrl($result['redirect_url'])
], 'Assessment graded and recorded successfully');
