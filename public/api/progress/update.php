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

$courseId = !empty($data['course_id']) ? (int)$data['course_id'] : 0;
$lessonId = !empty($data['lesson_id']) ? (int)$data['lesson_id'] : 0;
$position = isset($data['position_seconds']) ? (int)$data['position_seconds'] : 0;
$completed = !empty($data['completed']);

if (!$courseId || !$lessonId) {
    Response::error('course_id and lesson_id are required', 400);
}

$userId = (int)Auth::id();
$courseRepo = new CourseRepository();

// Verify user is enrolled
$enrollment = $courseRepo->getEnrollment($userId, $courseId);
if (!$enrollment) {
    Response::error('User is not enrolled in this course', 403);
}

$courseRepo->saveLessonProgress($userId, $courseId, $lessonId, $completed, $position);

Response::success([
    'lesson_id' => $lessonId,
    'position_seconds' => $position,
    'completed' => $completed
], 'Progress saved');
