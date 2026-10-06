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

if (!Auth::check()) {
    Response::error('Authentication required to interact with AliStack AI Tutor', 401);
}

// CSRF validation
Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$message = trim((string)($data['message'] ?? ''));
if (empty($message)) {
    Response::error('Message cannot be empty', 400);
}

$userId = (int)Auth::id();
$courseId = !empty($data['course_id']) ? (int)$data['course_id'] : null;
$lessonId = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : null;
$conversationId = !empty($data['conversation_id']) ? (int)$data['conversation_id'] : null;
$additionalContext = !empty($data['additional_context']) ? (string)$data['additional_context'] : null;

$aiService = new AiService();
$result = $aiService->askTutor($userId, $message, $courseId, $lessonId, $conversationId, $additionalContext);

if (!$result['success']) {
    Response::error($result['error'], 400, [
        'conversation_id' => $result['conversation_id'] ?? null
    ]);
}

Response::success([
    'conversation_id' => $result['conversation_id'],
    'reply' => $result['reply'],
    'remaining_queries' => $result['remaining_queries'] ?? null
], 'Response received');
