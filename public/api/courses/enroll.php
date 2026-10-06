<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Repositories\NotificationRepository;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::check()) {
    Response::error('Authentication required to enroll in courses', 401);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$courseId = !empty($data['course_id']) ? (int)$data['course_id'] : 0;
if (!$courseId) {
    Response::error('Invalid course ID', 400);
}

$courseRepo = new CourseRepository();
$course = $courseRepo->findById($courseId);
if (!$course || $course['status'] !== 'published') {
    Response::error('Course not available for enrollment', 404);
}

$userId = (int)Auth::id();
$courseRepo->enroll($userId, $courseId);

// Send in-app notification
$notifRepo = new NotificationRepository();
$notifRepo->create(
    $userId,
    "Enrolled: {$course['title']}",
    "Welcome to the course! Start watching the first lesson and practice your skills.",
    "learning.php?course_id={$courseId}",
    "course"
);

Response::success([
    'course_id' => $courseId,
    'redirect_url' => baseUrl("learning.php?course_id={$courseId}")
], 'Successfully enrolled in course');
