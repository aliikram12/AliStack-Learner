<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CourseRepository;
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

$lessonId = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : 0;

if (!$lessonId) {
    Response::error('lesson_id is required', 400);
}

$userId = (int)Auth::id();
$courseRepo = new CourseRepository();
$deleted = $courseRepo->deleteLessonNote($userId, $lessonId);

if ($deleted) {
    Response::success(['lesson_id' => $lessonId], 'Note deleted successfully');
} else {
    Response::error('Note could not be deleted or does not exist', 404);
}
