<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Helpers\Response;

$slug = $_GET['slug'] ?? null;
$id = !empty($_GET['id']) ? (int)$_GET['id'] : null;

$courseRepo = new CourseRepository();
if ($slug) {
    $course = $courseRepo->findBySlug($slug);
} elseif ($id) {
    $course = $courseRepo->findById($id);
} else {
    Response::error('Course identifier (id or slug) is required', 400);
}

if (!$course) {
    Response::error('Course not found', 404);
}

$lessons = $courseRepo->getLessons((int)$course['id'], true);

Response::success([
    'course' => $course,
    'lessons' => $lessons
]);
