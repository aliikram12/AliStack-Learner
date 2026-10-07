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

// Compute real metric counts
$inProgressCount = 0;
$completedCount = 0;
$totalHoursEstimated = 0.0;

foreach ($enrollments as $e) {
    if ((float)$e['progress_percent'] >= 100.0) {
        $completedCount++;
    } else {
        $inProgressCount++;
    }
    // Estimate hours from completed lessons
    $totalHoursEstimated += ((int)$e['completed_lessons'] * 15) / 60.0;
}

// Primary "Continue Learning" course
$continueCourse = !empty($enrollments) ? $enrollments[0] : null;

// Recommended courses (published courses user is not yet enrolled in)
$allCourses = $courseRepo->getAllPublished();
$enrolledCourseIds = array_map(fn($e) => (int)$e['course_id'], $enrollments);
$recommendedCourses = array_values(array_filter($allCourses, fn($c) => !in_array((int)$c['id'], $enrolledCourseIds, true)));

// Dynamic Time-of-Day Greeting
$hour = (int)date('H');
if ($hour < 12) {
    $timeGreeting = 'Good morning';
} elseif ($hour < 17) {
    $timeGreeting = 'Good afternoon';
} else {
    $timeGreeting = 'Good evening';
}

$firstName = Sanitizer::e(explode(' ', $user['full_name'])[0]);

$pageTitle = 'Student Dashboard';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div class="container" style="padding: 40px 24px 80px;">
    <!-- Top Greeting Section -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 24px; margin-bottom: 36px;" class="hero-anim">
        <div>
            <div style="font-size: 14px; font-weight: 600; color: var(--primary); text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 4px;">
                <?= $timeGreeting ?>, <?= $firstName ?>
            </div>
            <h1 style="font-size: 2.25rem; font-weight: 800; color: var(--text-dark); margin-bottom: 8px;">
                Ready to continue learning?
            </h1>
            <p style="font-size: 15px; color: var(--text-muted); max-width: 580px; margin: 0;">
                Pick up where you left off or explore something new in your distraction-reduced classroom.
            </p>
        </div>

        <div style="display: flex; gap: 12px; align-items: center;">
            <?php if ($continueCourse): ?>
                <a href="<?= baseUrl('learning.php?course_id=' . $continueCourse['course_id'] . (!empty($continueCourse['last_lesson_id']) ? '&lesson_id=' . $continueCourse['last_lesson_id'] : '')) ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-play-circle-fill"></i> Continue Learning
                </a>
            <?php else: ?>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-collection-play-fill"></i> Start First Course
                </a>
            <?php endif; ?>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-lg">
                <i class="bi bi-compass"></i> Explore Courses
            </a>
        </div>
    </div>

    <!-- Learning Overview Stats Cards -->
    <div style="margin-bottom: 40px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin-bottom: 18px;">Learning Overview</h2>
        <div class="grid grid-4">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $inProgressCount ?></div>
                    <div class="stat-label">Courses in Progress</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-emerald">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="stat-value"><?= $completedCount ?></div>
                    <div class="stat-label">Courses Completed</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="stat-value"><?= number_format($totalHoursEstimated, 1) ?>h</div>
                    <div class="stat-label">Estimated Study Time</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon stat-icon-amber">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <div class="stat-value"><?= count($certificates) ?></div>
                    <div class="stat-label">Verified Certificates</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Continue Learning Section -->
    <?php if ($continueCourse): ?>
        <div style="margin-bottom: 44px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin: 0;">Continue Learning</h2>
                <a href="<?= baseUrl('learning.php?course_id=' . $continueCourse['course_id']) ?>" style="font-size: 13px; font-weight: 600;">
                    Reopen Classroom <i class="bi bi-arrow-right"></i>
                </a>
            </div>

            <div class="continue-learning-card">
                <div class="continue-thumb">
                    <?php if (!empty($continueCourse['thumbnail'])): ?>
                        <img src="<?= baseUrl('assets/images/' . $continueCourse['thumbnail']) ?>" alt="Thumbnail" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=400&q=80';">
                    <?php else: ?>
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #1E293B; color: #94A3B8;">
                            <i class="bi bi-play-circle" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                        <span class="badge badge-primary">IN PROGRESS</span>
                        <span style="font-size: 12px; color: var(--text-muted);">&bull;</span>
                        <span style="font-size: 12px; color: var(--text-muted);"><?= (int)$continueCourse['completed_lessons'] ?> / <?= (int)$continueCourse['total_lessons'] ?> Lessons</span>
                    </div>
                    
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; line-height: 1.3;">
                        <?= Sanitizer::e($continueCourse['title']) ?>
                    </h3>
                    
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">
                        Last lesson: <strong style="color: var(--text-dark);"><?= Sanitizer::e($continueCourse['last_lesson_title'] ?? 'Lesson 1') ?></strong>
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="progress-bar" style="flex: 1; height: 8px;">
                            <div class="progress-fill" style="width: <?= (float)$continueCourse['progress_percent'] ?>%;"></div>
                        </div>
                        <span style="font-size: 13px; font-weight: 700; color: var(--text-dark);"><?= number_format((float)$continueCourse['progress_percent'], 0) ?>%</span>
                    </div>
                </div>

                <div>
                    <a href="<?= baseUrl('learning.php?course_id=' . $continueCourse['course_id'] . (!empty($continueCourse['last_lesson_id']) ? '&lesson_id=' . $continueCourse['last_lesson_id'] : '')) ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.3);">
                        <i class="bi bi-play-fill"></i> Continue Learning
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Columns -->
    <div class="dashboard-layout">
        <!-- Left Main Column: My Courses -->
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin: 0;">My Enrolled Courses</h2>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">
                    <i class="bi bi-plus"></i> Browse Catalog
                </a>
            </div>

            <?php if (empty($enrollments)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="bi bi-collection-play"></i>
                    </div>
                    <div class="empty-state-title">Your learning journey starts here</div>
                    <div class="empty-state-desc">
                        Explore our structured technical courses without recommendation feeds or distractions.
                    </div>
                    <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary">
                        <i class="bi bi-compass"></i> Explore Courses
                    </a>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach ($enrollments as $e): ?>
                        <div class="card card-hover" style="padding: 20px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                                <div style="flex: 1; min-width: 260px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                        <span class="badge <?= ((float)$e['progress_percent'] >= 100.0) ? 'badge-success' : 'badge-neutral' ?>">
                                            <?= ((float)$e['progress_percent'] >= 100.0) ? 'COMPLETED' : 'IN PROGRESS' ?>
                                        </span>
                                        <span style="font-size: 12px; color: var(--text-muted);">&bull;</span>
                                        <span style="font-size: 12px; color: var(--text-muted);"><?= (int)$e['completed_lessons'] ?> of <?= (int)$e['total_lessons'] ?> lessons</span>
                                    </div>

                                    <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: 8px;">
                                        <a href="<?= baseUrl('learning.php?course_id=' . $e['course_id']) ?>" style="color: var(--text-dark);">
                                            <?= Sanitizer::e($e['title']) ?>
                                        </a>
                                    </h3>

                                    <div style="display: flex; align-items: center; gap: 12px; max-width: 380px;">
                                        <div class="progress-bar" style="flex: 1; height: 6px;">
                                            <div class="progress-fill" style="width: <?= (float)$e['progress_percent'] ?>%;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);"><?= number_format((float)$e['progress_percent'], 0) ?>%</span>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <a href="<?= baseUrl('learning.php?course_id=' . $e['course_id']) ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-play-circle-fill"></i> Learn
                                    </a>
                                    <?php if ((float)$e['progress_percent'] >= 100.0): ?>
                                        <a href="<?= baseUrl('assessment.php?course_id=' . $e['course_id']) ?>" class="btn btn-secondary btn-sm">
                                            <i class="bi bi-patch-question-fill"></i> Assessment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Recommended Next Courses -->
            <?php if (!empty($recommendedCourses)): ?>
                <div style="margin-top: 48px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); margin: 0;">Recommended For You</h2>
                        <a href="<?= baseUrl('courses.php') ?>" style="font-size: 13px; font-weight: 600;">View All</a>
                    </div>

                    <div class="grid grid-2">
                        <?php foreach (array_slice($recommendedCourses, 0, 2) as $rc): ?>
                            <div class="course-card">
                                <div class="course-thumbnail-wrap">
                                    <?php if (!empty($rc['thumbnail'])): ?>
                                        <img src="<?= baseUrl('assets/images/' . $rc['thumbnail']) ?>" alt="Thumbnail" class="course-thumbnail-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&q=80';">
                                    <?php else: ?>
                                        <div style="width:100%; height:100%; background:#1E293B; display:flex; align-items:center; justify-content:center; color:#94A3B8;">
                                            <i class="bi bi-play-circle" style="font-size:2rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="badge badge-primary course-difficulty-badge"><?= ucfirst($rc['difficulty'] ?? 'Beginner') ?></span>
                                </div>
                                <div class="course-card-content">
                                    <div class="course-card-category"><?= Sanitizer::e($rc['category_name'] ?? 'General') ?></div>
                                    <h4 class="course-card-title"><?= Sanitizer::e($rc['title']) ?></h4>
                                    <p class="course-card-desc"><?= Sanitizer::e($rc['short_desc']) ?></p>
                                    <div class="course-card-meta">
                                        <div class="course-meta-item"><i class="bi bi-collection-play"></i> <?= (int)$rc['lesson_count'] ?> Lessons</div>
                                        <a href="<?= baseUrl('course-details.php?slug=' . $rc['slug']) ?>" class="btn btn-outline btn-sm">Details</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Side: Achievements, Bookmarks & Notes -->
        <div style="display: flex; flex-direction: column; gap: 28px;">
            <!-- Achievements & Certs Summary Card -->
            <div class="card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">My Achievements</h3>
                    <a href="<?= baseUrl('certificates.php') ?>" style="font-size: 12px; font-weight: 600;">View All</a>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <?php if (empty($certificates) && empty($achievements)): ?>
                        <div style="text-align: center; padding: 20px 10px; color: var(--text-muted); font-size: 13px;">
                            <i class="bi bi-award" style="font-size: 1.8rem; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                            Pass your course assessments to earn certificates and badges.
                        </div>
                    <?php else: ?>
                        <?php foreach (array_slice($certificates, 0, 2) as $cert): ?>
                            <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: #DCFCE7; color: #16A34A; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                    <i class="bi bi-award-fill"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= Sanitizer::e($cert['course_title']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);">Verified Certificate &bull; Score: <?= number_format((float)$cert['score_percentage'], 0) ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php foreach (array_slice($achievements, 0, 2) as $ach): ?>
                            <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; display: flex; align-items: center; gap: 12px;">
                                <div style="width: 36px; height: 36px; background: #FEF3C7; color: #D97706; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                                    <i class="bi bi-shield-fill-check"></i>
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--text-dark);"><?= Sanitizer::e($ach['badge_title']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= Sanitizer::e($ach['course_title']) ?> (<?= number_format((float)$ach['score_achieved'], 0) ?>%)</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Saved Bookmarks -->
            <div class="card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">Bookmarked Lessons</h3>
                    <a href="<?= baseUrl('bookmarks.php') ?>" style="font-size: 12px; font-weight: 600;">View All (<?= count($bookmarks) ?>)</a>
                </div>

                <?php if (empty($bookmarks)): ?>
                    <div style="text-align: center; padding: 18px; color: var(--text-muted); font-size: 13px;">
                        <i class="bi bi-bookmark" style="font-size: 1.6rem; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                        Bookmark key lessons while watching to revise them quickly.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php foreach (array_slice($bookmarks, 0, 3) as $bm): ?>
                            <a href="<?= baseUrl('learning.php?course_id=' . $bm['course_id'] . '&lesson_id=' . $bm['lesson_id']) ?>" style="display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: var(--bg-main); border-radius: var(--radius-md); text-decoration: none; color: var(--text-dark); border: 1px solid var(--border); transition: all var(--transition-fast);">
                                <i class="bi bi-bookmark-fill" style="color: var(--warning); font-size: 14px;"></i>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= Sanitizer::e($bm['lesson_title']) ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?= Sanitizer::e($bm['course_title']) ?></div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Notes -->
            <div class="card" style="padding: 22px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0;">Recent Study Notes</h3>
                    <a href="<?= baseUrl('notes.php') ?>" style="font-size: 12px; font-weight: 600;">View All (<?= count($recentNotes) ?>)</a>
                </div>

                <?php if (empty($recentNotes)): ?>
                    <div style="text-align: center; padding: 18px; color: var(--text-muted); font-size: 13px;">
                        <i class="bi bi-journal-text" style="font-size: 1.6rem; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                        Your personal lesson notes will autosave and appear here.
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <?php foreach ($recentNotes as $n): ?>
                            <div style="padding: 12px; background: var(--bg-main); border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <span style="font-weight: 700; font-size: 12px; color: var(--text-dark);"><?= Sanitizer::e($n['lesson_title']) ?></span>
                                    <span style="font-size: 10.5px; color: var(--text-light);"><?= Sanitizer::timeAgo($n['updated_at']) ?></span>
                                </div>
                                <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.4; margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= Sanitizer::e($n['content']) ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
