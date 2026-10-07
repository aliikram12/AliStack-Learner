<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Auth;
use App\Helpers\Csrf;

$slug = trim((string)($_GET['slug'] ?? ''));
$id = !empty($_GET['id']) ? (int)$_GET['id'] : null;

$courseRepo = new CourseRepository();
if ($slug) {
    $course = $courseRepo->findBySlug($slug);
} elseif ($id) {
    $course = $courseRepo->findById($id);
} else {
    header('Location: ' . baseUrl('courses.php'));
    exit;
}

if (!$course || $course['status'] !== 'published') {
    http_response_code(404);
    die('Course not found or currently unavailable.');
}

$lessons = $courseRepo->getLessons((int)$course['id'], true);

$isEnrolled = false;
$enrollment = null;
if (Auth::check()) {
    $enrollment = $courseRepo->getEnrollment((int)Auth::id(), (int)$course['id']);
    $isEnrolled = ($enrollment !== null);
}

$pageTitle = $course['title'];
$pageDesc = $course['short_desc'];

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Breadcrumbs Bar -->
<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 14px 0;">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?= baseUrl('/') ?>"><i class="bi bi-house"></i> Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?= baseUrl('courses.php') ?>">Courses</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-current"><?= Sanitizer::e($course['title']) ?></span>
        </nav>
    </div>
</div>

<!-- Course Header Hero -->
<div style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); border-bottom: 1px solid var(--border); padding: 56px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px; align-items: center;">
            <div>
                <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
                    <span class="badge badge-primary"><?= Sanitizer::e($course['category_name'] ?? 'Technical Course') ?></span>
                    <span class="badge badge-secondary"><?= ucfirst(Sanitizer::e($course['difficulty'])) ?></span>
                    <span class="badge badge-neutral"><i class="bi bi-clock"></i> <?= Sanitizer::e($course['estimated_duration']) ?></span>
                </div>

                <h1 style="font-size: 2.4rem; font-weight: 800; margin-bottom: 16px; line-height: 1.25; color: var(--text-dark);">
                    <?= Sanitizer::e($course['title']) ?>
                </h1>
                
                <p style="font-size: 1.15rem; color: var(--text-muted); margin-bottom: 24px; line-height: 1.6;">
                    <?= Sanitizer::e($course['short_desc']) ?>
                </p>

                <div style="display: flex; align-items: center; gap: 24px; font-size: 13.5px; color: var(--text-muted); margin-bottom: 32px; flex-wrap: wrap;">
                    <div><i class="bi bi-person-circle" style="color: var(--primary);"></i> Instructor: <strong style="color: var(--text-dark);"><?= Sanitizer::e($course['instructor_name']) ?></strong></div>
                    <div><i class="bi bi-translate" style="color: var(--primary);"></i> Language: <strong style="color: var(--text-dark);"><?= Sanitizer::e($course['language']) ?></strong></div>
                    <div><i class="bi bi-collection-play" style="color: var(--primary);"></i> Curriculum: <strong style="color: var(--text-dark);"><?= count($lessons) ?> Lessons</strong></div>
                </div>

                <div>
                    <?php if ($isEnrolled): ?>
                        <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
                            <a href="<?= baseUrl('learning.php?course_id=' . $course['id']) ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-play-circle-fill"></i> Continue Learning
                            </a>
                            <div style="font-size: 13px; color: var(--text-muted);">
                                Your progress: <strong><?= number_format((float)$enrollment['progress_percent'], 0) ?>%</strong>
                            </div>
                        </div>
                    <?php elseif (Auth::check()): ?>
                        <form method="POST" action="<?= baseUrl('api/courses/enroll.php') ?>" style="display: inline-block;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.3);">
                                <i class="bi bi-mortarboard-fill"></i> Start Learning Now (Free)
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= baseUrl('login.php?redirect=' . urlencode('course-details.php?slug=' . $course['slug'])) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Log In to Start Learning
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Visual Overview Card -->
            <div>
                <div class="card" style="box-shadow: var(--shadow-xl); overflow: hidden;">
                    <div class="course-thumbnail-wrap" style="aspect-ratio: 16/9;">
                        <?php if (!empty($course['thumbnail'])): ?>
                            <img src="<?= baseUrl('assets/images/' . $course['thumbnail']) ?>" alt="Thumbnail" class="course-thumbnail-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&q=80';">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: #0F172A; display: flex; align-items: center; justify-content: center; color: #FFFFFF;">
                                <i class="bi bi-play-circle-fill" style="font-size: 56px; color: var(--primary);"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="padding: 24px;">
                        <h4 style="margin-bottom: 14px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-dark);">Included in this course:</h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 11px; font-size: 13.5px; color: var(--text-main);">
                            <li style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 16px;"></i>
                                <span><?= count($lessons) ?> Distraction-Free Video Lessons</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 16px;"></i>
                                <span>24/7 Context-Aware AI Learning Tutor</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 16px;"></i>
                                <span>Personal Notes with Real-Time Autosave</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 16px;"></i>
                                <span>Comprehensive Course Assessment</span>
                            </li>
                            <li style="display: flex; align-items: center; gap: 10px;">
                                <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 16px;"></i>
                                <span>Verified AliStack Certificate (70%+ Score)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Syllabus & Objectives -->
<div class="container" style="padding: 64px 24px 80px;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px;">
        <div>
            <!-- What You'll Learn -->
            <?php if (!empty($course['learning_objectives'])): ?>
                <div style="margin-bottom: 40px; background: #FFFFFF; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-card);">
                    <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 20px;">What You Will Learn</h2>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <?php 
                        $objectives = explode("\n", $course['learning_objectives']);
                        foreach ($objectives as $obj):
                            if (trim($obj) === '') continue;
                        ?>
                            <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 14px; line-height: 1.5;">
                                <div style="width: 24px; height: 24px; border-radius: 50%; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;">
                                    <i class="bi bi-check2"></i>
                                </div>
                                <span style="color: var(--text-dark);"><?= Sanitizer::e(trim($obj)) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Course Full Description -->
            <div style="margin-bottom: 48px;">
                <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 16px;">Course Overview</h2>
                <div style="line-height: 1.8; font-size: 15px; color: var(--text-main);">
                    <?= nl2br(Sanitizer::e($course['full_desc'])) ?>
                </div>
            </div>

            <!-- Course Curriculum / Lessons -->
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <div>
                        <h2 style="font-size: 1.35rem; font-weight: 700; margin-bottom: 4px;">Course Content</h2>
                        <div style="font-size: 13px; color: var(--text-muted);"><?= count($lessons) ?> lessons &bull; Total duration: <?= Sanitizer::e($course['estimated_duration']) ?></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php foreach ($lessons as $index => $lesson): ?>
                        <div class="card" style="padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px;">
                            <div style="display: flex; align-items: center; gap: 16px; flex: 1;">
                                <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; color: var(--text-muted); flex-shrink: 0;">
                                    <?= $index + 1 ?>
                                </div>
                                <div>
                                    <div style="font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 2px;">
                                        <?= Sanitizer::e($lesson['title']) ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--text-muted);">
                                        <i class="bi bi-clock"></i> <?= (int)$lesson['duration_minutes'] ?> minutes
                                    </div>
                                </div>
                            </div>

                            <a href="<?= baseUrl('learning.php?course_id=' . $course['id'] . '&lesson_id=' . $lesson['id']) ?>" class="btn btn-outline btn-sm">
                                <i class="bi bi-play-circle"></i> Play Lesson
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Requirements & Info -->
        <div>
            <!-- Prerequisites Card -->
            <?php if (!empty($course['prerequisites'])): ?>
                <div class="card" style="padding: 24px; margin-bottom: 24px;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px;"><i class="bi bi-info-circle text-primary"></i> Prerequisites</h3>
                    <p style="font-size: 14px; color: var(--text-muted); line-height: 1.6; margin: 0;">
                        <?= nl2br(Sanitizer::e($course['prerequisites'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Instructor Card -->
            <div class="card" style="padding: 24px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 14px;"><i class="bi bi-person-workspace text-primary"></i> Instructor</h3>
                <div style="display: flex; align-items: center; gap: 14px;">
                    <div class="avatar" style="width: 48px; height: 48px; font-size: 1.1rem;">
                        <?= strtoupper(substr($course['instructor_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-size: 15px; font-weight: 700; color: var(--text-dark);"><?= Sanitizer::e($course['instructor_name']) ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);">AliStack Certified Educator</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
