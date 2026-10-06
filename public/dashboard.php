<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Repositories\CourseRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\NotificationRepository;
use App\Helpers\Sanitizer;

Auth::requireAuth();

$user = Auth::user();
$userId = (int)$user['id'];

$courseRepo = new CourseRepository();
$assessmentRepo = new AssessmentRepository();
$certRepo = new CertificateRepository();
$notifRepo = new NotificationRepository();

// Actual Database Queries
$enrollments = $courseRepo->getStudentEnrollments($userId);
$recentNotes = $courseRepo->getUserNotes($userId, 4);
$bookmarks = $courseRepo->getUserBookmarks($userId);
$certificates = $certRepo->getUserCertificates($userId);
$achievements = $certRepo->getUserAchievements($userId);

// Primary "Continue Learning" course
$continueCourse = !empty($enrollments) ? $enrollments[0] : null;

// Recommended courses (published courses user is not yet enrolled in)
$allCourses = $courseRepo->getAllPublished();
$enrolledCourseIds = array_map(fn($e) => (int)$e['course_id'], $enrollments);
$recommendedCourses = array_filter($allCourses, fn($c) => !in_array((int)$c['id'], $enrolledCourseIds, true));

$pageTitle = 'Student Dashboard';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div class="container" style="padding: 40px 24px 80px;">
    <!-- Welcome Banner -->
    <div style="background: linear-gradient(135deg, #1E293B, #0F172A); border-radius: var(--radius-lg); padding: 36px 40px; color: #FFFFFF; margin-bottom: 36px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap; box-shadow: var(--shadow-lg);">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; background: rgba(37,99,235,0.3); color: #93C5FD; padding: 4px 12px; border-radius: var(--radius-full); margin-bottom: 12px;">
                <i class="bi bi-mortarboard-fill"></i> STUDENT PORTAL
            </div>
            <h1 style="color: #FFFFFF; font-size: 2.2rem; margin-bottom: 8px;">
                Welcome back, <?= Sanitizer::e(explode(' ', $user['full_name'])[0]) ?>!
            </h1>
            <p style="color: #94A3B8; font-size: 15px; margin: 0; max-width: 580px;">
                Track your structured video progress, review personal notes, ask your AI Tutor, and earn verified certificates.
            </p>
        </div>

        <div style="display: flex; gap: 24px; text-align: center;">
            <div style="background: rgba(255,255,255,0.06); padding: 16px 24px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 28px; font-weight: 800; color: #38BDF8;"><?= count($enrollments) ?></div>
                <div style="font-size: 12px; color: #94A3B8; text-transform: uppercase;">Enrolled</div>
            </div>
            <div style="background: rgba(255,255,255,0.06); padding: 16px 24px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 28px; font-weight: 800; color: #4ADE80;"><?= count($certificates) ?></div>
                <div style="font-size: 12px; color: #94A3B8; text-transform: uppercase;">Certificates</div>
            </div>
            <div style="background: rgba(255,255,255,0.06); padding: 16px 24px; border-radius: var(--radius-md); border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 28px; font-weight: 800; color: #FBBF24;"><?= count($achievements) ?></div>
                <div style="font-size: 12px; color: #94A3B8; text-transform: uppercase;">Badges</div>
            </div>
        </div>
    </div>

    <!-- Continue Learning Section -->
    <?php if ($continueCourse): ?>
        <div class="card" style="padding: 28px; margin-bottom: 36px; border-left: 6px solid var(--primary);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div>
                    <span class="badge badge-primary" style="margin-bottom: 8px;">CONTINUE LEARNING</span>
                    <h2 style="font-size: 1.5rem; margin-bottom: 6px;">
                        <?= Sanitizer::e($continueCourse['title']) ?>
                    </h2>
                    <div style="font-size: 13px; color: var(--muted);">
                        Current lesson: <strong><?= Sanitizer::e($continueCourse['last_lesson_title'] ?? 'Lesson 1') ?></strong> &bull;
                        <?= (int)$continueCourse['completed_lessons'] ?> of <?= (int)$continueCourse['total_lessons'] ?> lessons completed (<?= number_format((float)$continueCourse['progress_percent'], 0) ?>%)
                    </div>
                </div>

                <a href="<?= baseUrl('learning.php?course_id=' . $continueCourse['course_id'] . (!empty($continueCourse['last_lesson_id']) ? '&lesson_id=' . $continueCourse['last_lesson_id'] : '')) ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-play-circle-fill"></i> Resume Lesson
                </a>
            </div>

            <div class="course-progress-bar" style="height: 8px; margin-top: 20px;">
                <div class="course-progress-fill" style="width: <?= (float)$continueCourse['progress_percent'] ?>%;"></div>
            </div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 36px;">
        <!-- Left Main Column -->
        <div>
            <!-- Enrolled Courses -->
            <div style="margin-bottom: 40px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <h3 style="font-size: 1.4rem;">My Enrolled Courses</h3>
                    <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">
                        <i class="bi bi-plus-circle"></i> Browse More
                    </a>
                </div>

                <?php if (empty($enrollments)): ?>
                    <div style="text-align: center; padding: 48px 24px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                        <i class="bi bi-collection-play" style="font-size: 40px; color: var(--muted-light); margin-bottom: 12px; display: block;"></i>
                        <h4 style="margin-bottom: 6px;">You haven't enrolled in any courses yet</h4>
                        <p style="font-size: 14px; color: var(--muted); margin-bottom: 16px;">
                            Explore our curated technical curriculum and start watching distraction-free lessons.
                        </p>
                        <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-sm">
                            Explore Technical Catalog
                        </a>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php foreach ($enrollments as $e): ?>
                            <div class="card" style="padding: 20px; display: flex; align-items: center; justify-content: space-between; gap: 20px;">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                                        <h4 style="margin: 0; font-size: 1.1rem;">
                                            <a href="<?= baseUrl('learning.php?course_id=' . $e['course_id']) ?>" style="color: var(--dark);">
                                                <?= Sanitizer::e($e['title']) ?>
                                            </a>
                                        </h4>
                                        <?php if ((float)$e['progress_percent'] >= 100.0): ?>
                                            <span class="badge badge-success"><i class="bi bi-check-all"></i> Completed</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 13px; color: var(--muted); margin-bottom: 10px;">
                                        <?= (int)$e['completed_lessons'] ?> of <?= (int)$e['total_lessons'] ?> lessons &bull; Last studied <?= Sanitizer::timeAgo($e['last_accessed_at']) ?>
                                    </div>
                                    <div class="course-progress-bar" style="max-width: 320px;">
                                        <div class="course-progress-fill" style="width: <?= (float)$e['progress_percent'] ?>%;"></div>
                                    </div>
                                </div>

                                <div style="display: flex; gap: 8px;">
                                    <a href="<?= baseUrl('learning.php?course_id=' . $e['course_id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-play-circle"></i> Learn
                                    </a>
                                    <?php if ((float)$e['progress_percent'] >= 100.0): ?>
                                        <a href="<?= baseUrl('assessment.php?course_id=' . $e['course_id']) ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-patch-question"></i> Assessment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Bookmarked Lessons -->
            <?php if (!empty($bookmarks)): ?>
                <div style="margin-bottom: 40px;">
                    <h3 style="font-size: 1.4rem; margin-bottom: 20px;">Bookmarked Lessons</h3>
                    <div class="card">
                        <?php foreach ($bookmarks as $bm): ?>
                            <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div style="font-weight: 600; font-size: 14px; color: var(--dark);">
                                        <?= Sanitizer::e($bm['lesson_title']) ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--muted);">Course: <?= Sanitizer::e($bm['course_title']) ?></div>
                                </div>
                                <a href="<?= baseUrl('learning.php?course_id=' . $bm['course_id'] . '&lesson_id=' . $bm['lesson_id']) ?>" class="btn btn-outline btn-sm">
                                    <i class="bi bi-play"></i> Watch
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recommended Courses -->
            <?php if (!empty($recommendedCourses)): ?>
                <div>
                    <h3 style="font-size: 1.4rem; margin-bottom: 20px;">Recommended for You</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                        <?php foreach (array_slice($recommendedCourses, 0, 2) as $rc): ?>
                            <div class="course-card">
                                <div class="course-body">
                                    <span class="badge badge-primary" style="margin-bottom: 6px;"><?= ucfirst(Sanitizer::e($rc['difficulty'])) ?></span>
                                    <h4 class="course-title" style="font-size: 1rem;">
                                        <a href="<?= baseUrl('course-details.php?slug=' . urlencode($rc['slug'])) ?>">
                                            <?= Sanitizer::e($rc['title']) ?>
                                        </a>
                                    </h4>
                                    <p class="course-desc" style="font-size: 12px;"><?= Sanitizer::e($rc['short_desc']) ?></p>
                                    <a href="<?= baseUrl('course-details.php?slug=' . urlencode($rc['slug'])) ?>" class="btn btn-outline btn-sm" style="margin-top: 10px;">
                                        View Syllabus
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Sidebar: Badges, Certificates, Notes -->
        <div>
            <!-- Earned Certificates -->
            <div class="card" style="padding: 24px; margin-bottom: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 15px;"><i class="bi bi-award-fill" style="color: var(--success);"></i> Verified Certificates</h4>
                    <span class="badge badge-success"><?= count($certificates) ?></span>
                </div>

                <?php if (empty($certificates)): ?>
                    <p style="font-size: 13px; color: var(--muted); margin: 0;">
                        No certificates earned yet. Complete all lessons of a course and pass the final assessment with 70%+ to earn your certificate.
                    </p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($certificates as $cert): ?>
                            <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 12px;">
                                <div style="font-weight: 600; font-size: 13px; color: var(--dark); margin-bottom: 2px;">
                                    <?= Sanitizer::e($cert['course_title']) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--muted); margin-bottom: 8px;">
                                    Score: <strong><?= number_format((float)$cert['score_percentage'], 1) ?>%</strong> &bull; <?= date('M j, Y', strtotime($cert['issued_at'])) ?>
                                </div>
                                <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-outline btn-sm" style="font-size: 11px; padding: 4px 10px;">
                                    <i class="bi bi-printer"></i> View / Print
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Performance Badges Gallery -->
            <div id="badges" class="card" style="padding: 24px; margin-bottom: 28px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 15px;"><i class="bi bi-patch-check-fill" style="color: var(--secondary);"></i> Performance Badges</h4>
                    <span class="badge badge-secondary"><?= count($achievements) ?></span>
                </div>

                <?php if (empty($achievements)): ?>
                    <p style="font-size: 13px; color: var(--muted); margin: 0;">
                        Earned when you score between 40% and 69.99% on course assessments.
                    </p>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach ($achievements as $ach): ?>
                            <div style="display: flex; align-items: center; gap: 12px; padding: 10px; background: #F8FAFC; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                <div style="font-size: 24px;">
                                    <?php if ($ach['badge_tier'] === 'silver'): ?>
                                        🥈
                                    <?php elseif ($ach['badge_tier'] === 'bronze'): ?>
                                        🥉
                                    <?php else: ?>
                                        🎖️
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <div style="font-weight: 700; font-size: 13px;"><?= Sanitizer::e($ach['badge_title']) ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($ach['course_title']) ?> (<?= number_format((float)$ach['score_percentage'], 1) ?>%)</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Notes -->
            <?php if (!empty($recentNotes)): ?>
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 14px; font-size: 15px;"><i class="bi bi-pencil-square" style="color: var(--primary);"></i> Recent Study Notes</h4>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($recentNotes as $n): ?>
                            <div style="background: #F8FAFC; border-radius: var(--radius-md); padding: 12px; border: 1px solid var(--border-color);">
                                <div style="font-weight: 600; font-size: 12px; color: var(--dark); margin-bottom: 2px;">
                                    <?= Sanitizer::e($n['lesson_title']) ?>
                                </div>
                                <div style="font-size: 12px; color: var(--muted); max-height: 40px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?= Sanitizer::e(strip_tags($n['content'])) ?>
                                </div>
                                <div style="font-size: 11px; color: var(--muted-light); margin-top: 4px;">
                                    <?= Sanitizer::timeAgo($n['updated_at']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
