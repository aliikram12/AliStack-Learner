<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Helpers\Response;

$categoryId = !empty($_GET['category_id']) ? (int)$_GET['category_id'] : null;
$difficulty = !empty($_GET['difficulty']) ? (string)$_GET['difficulty'] : null;
$search = !empty($_GET['q']) ? (string)$_GET['q'] : null;

$courseRepo = new CourseRepository();
$courses = $courseRepo->getAllPublished($categoryId, $difficulty, $search);

Response::success([
    'courses' => $courses,
    'total' => count($courses)
]);
