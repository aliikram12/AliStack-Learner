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

<!-- Course Header Hero -->
<div style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px; align-items: center;">
            <div>
                <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                    <span class="badge badge-primary"><?= Sanitizer::e($course['category_name'] ?? 'Course') ?></span>
                    <span class="badge badge-secondary"><?= ucfirst(Sanitizer::e($course['difficulty'])) ?></span>
                    <span class="badge badge-gray"><i class="bi bi-clock"></i> <?= Sanitizer::e($course['estimated_duration']) ?></span>
                </div>

                <h1 style="font-size: 2.5rem; margin-bottom: 16px; line-height: 1.25;"><?= Sanitizer::e($course['title']) ?></h1>
                <p style="font-size: 1.15rem; color: var(--muted); margin-bottom: 24px; line-height: 1.6;">
                    <?= Sanitizer::e($course['short_desc']) ?>
                </p>

                <div style="display: flex; align-items: center; gap: 20px; font-size: 14px; color: var(--muted); margin-bottom: 28px;">
                    <div><i class="bi bi-person-circle"></i> Instructor: <strong><?= Sanitizer::e($course['instructor_name']) ?></strong></div>
                    <div><i class="bi bi-translate"></i> Language: <strong><?= Sanitizer::e($course['language']) ?></strong></div>
                    <div><i class="bi bi-collection-play"></i> Lessons: <strong><?= count($lessons) ?></strong></div>
                </div>

                <div>
                    <?php if ($isEnrolled): ?>
                        <a href="<?= baseUrl('learning.php?course_id=' . $course['id']) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-play-circle-fill"></i> Continue Learning (<?= number_format((float)$enrollment['progress_percent'], 0) ?>%)
                        </a>
                    <?php elseif (Auth::check()): ?>
                        <form method="POST" action="<?= baseUrl('api/courses/enroll.php') ?>" style="display: inline-block;">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-mortarboard-fill"></i> Enroll in Course (Free)
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="<?= baseUrl('login.php?redirect=' . urlencode('course-details.php?slug=' . $course['slug'])) ?>" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Log In to Enroll
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Thumbnail Card -->
            <div>
                <div class="card" style="overflow: hidden; box-shadow: var(--shadow-xl);">
                    <div style="aspect-ratio: 16/9; background: #0F172A; display: flex; align-items: center; justify-content: center; position: relative;">
                        <i class="bi bi-play-circle-fill" style="font-size: 64px; color: var(--primary);"></i>
                    </div>
                    <div style="padding: 24px;">
                        <h4 style="margin-bottom: 12px; font-size: 15px;">Included in this course:</h4>
                        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: var(--muted);">
                            <li><i class="bi bi-check-circle-fill" style="color: var(--success); margin-right: 8px;"></i> <?= count($lessons) ?> Distraction-Free Video Lessons</li>
                            <li><i class="bi bi-check-circle-fill" style="color: var(--success); margin-right: 8px;"></i> 24/7 Contextual AliStack AI Tutor Support</li>
                            <li><i class="bi bi-check-circle-fill" style="color: var(--success); margin-right: 8px;"></i> Interactive Personal Notes Autosave</li>
                            <li><i class="bi bi-check-circle-fill" style="color: var(--success); margin-right: 8px;"></i> Comprehensive Graded MCQ Assessment</li>
                            <li><i class="bi bi-check-circle-fill" style="color: var(--success); margin-right: 8px;"></i> Verifiable AliStack Certificate upon completion</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Syllabus & Objectives -->
<div class="container" style="padding: 64px 24px;">
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 48px;">
        <div>
            <!-- Overview -->
            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 16px;">Course Overview</h3>
                <div style="line-height: 1.7; font-size: 15px; color: var(--dark-light);">
                    <?= nl2br(Sanitizer::e($course['full_desc'])) ?>
                </div>
            </div>

            <!-- Learning Objectives -->
            <?php if (!empty($course['learning_objectives'])): ?>
                <div style="margin-bottom: 40px; background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 28px;">
                    <h3 style="margin-bottom: 16px;">What You Will Learn</h3>
                    <ul style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; list-style: none; padding: 0;">
                        <?php 
                        $objectives = explode("\n", $course['learning_objectives']);
                        foreach ($objectives as $obj):
                            if (trim($obj) === '') continue;
                        ?>
                            <li style="display: flex; align-items: flex-start; gap: 10px; font-size: 14px;">
                                <i class="bi bi-check2-circle" style="color: var(--primary); font-size: 18px; flex-shrink: 0;"></i>
                                <span><?= Sanitizer::e(trim($obj)) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Course Curriculum / Lessons -->
            <div style="margin-bottom: 40px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h3>Course Curriculum</h3>
                    <span style="font-size: 13px; color: var(--muted);"><?= count($lessons) ?> lessons</span>
                </div>

                <div class="card">
                    <?php if (empty($lessons)): ?>
                        <div style="padding: 24px; text-align: center; color: var(--muted);">No published lessons available yet.</div>
                    <?php else: ?>
                        <?php foreach ($lessons as $index => $lesson): ?>
                            <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #F1F5F9; color: var(--muted); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700;">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; font-size: 14px; color: var(--dark);"><?= Sanitizer::e($lesson['title']) ?></div>
                                        <?php if (!empty($lesson['description'])): ?>
                                            <div style="font-size: 12px; color: var(--muted); margin-top: 2px; max-width: 500px;"><?= Sanitizer::e($lesson['description']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div style="font-size: 12px; color: var(--muted); display: flex; align-items: center; gap: 6px;">
                                    <i class="bi bi-play-circle"></i> <?= (int)$lesson['duration_minutes'] ?>m
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Requirements & Assessment -->
        <div>
            <?php if (!empty($course['prerequisites'])): ?>
                <div class="card" style="padding: 24px; margin-bottom: 24px;">
                    <h4 style="margin-bottom: 12px; font-size: 15px;">Prerequisites</h4>
                    <p style="font-size: 13px; margin: 0; color: var(--muted); line-height: 1.6;">
                        <?= nl2br(Sanitizer::e($course['prerequisites'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="card" style="padding: 24px; background: linear-gradient(180deg, #FFFFFF, #F8FAFC);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h4 style="margin: 0; font-size: 15px;">Final Assessment</h4>
                </div>
                <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 16px;">
                    Upon completing all required lessons, you will unlock the final MCQ assessment. Passing with 70%+ awards you the official verified AliStack Course Certificate.
                </p>
                <div style="border-top: 1px solid var(--border-color); padding-top: 14px; font-size: 12px; color: var(--muted); display: flex; flex-direction: column; gap: 6px;">
                    <div><i class="bi bi-check2"></i> Pass threshold: <strong>70%</strong></div>
                    <div><i class="bi bi-check2"></i> Silver badge: <strong>60% - 69.99%</strong></div>
                    <div><i class="bi bi-check2"></i> Bronze badge: <strong>50% - 59.99%</strong></div>
                    <div><i class="bi bi-check2"></i> Starter badge: <strong>40% - 49.99%</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
