<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\SettingRepository;
use App\Repositories\UserRepository;
use App\Repositories\AssessmentRepository;
use App\Helpers\Sanitizer;

$settingRepo = new SettingRepository();
$userRepo = new UserRepository();
$assessmentRepo = new AssessmentRepository();

$stats = $settingRepo->getPlatformStats();
$recentUsers = $userRepo->getAll(1, 5);
$recentAttempts = $assessmentRepo->getAllAttemptsAdmin(1, 5);

$pageTitle = 'Dashboard Overview';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Platform Overview</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Live database metrics and recent platform activity.</p>
    </div>

    <div style="display: flex; gap: 8px;">
        <a href="<?= baseUrl('admin/course-edit.php') ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Create Course
        </a>
    </div>
</div>

<!-- Metrics Cards Grid -->
<div class="admin-stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_students'] ?></div>
            <div class="stat-label">Registered Students</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon purple"><i class="bi bi-collection-play-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['published_courses'] ?></div>
            <div class="stat-label">Published Courses</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-mortarboard-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['active_enrollments'] ?></div>
            <div class="stat-label">Active Enrollments</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-patch-check-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['certificates_issued'] ?></div>
            <div class="stat-label">Certificates Issued</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-award-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['badges_awarded'] ?></div>
            <div class="stat-label">Badges Awarded</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-clipboard2-check-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['assessment_attempts'] ?></div>
            <div class="stat-label">Tests Completed</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-person-plus-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['pending_group_requests'] ?></div>
            <div class="stat-label">Pending Join Requests</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-play-btn-fill"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?= $stats['total_lessons'] ?></div>
            <div class="stat-label">Total Video Lessons</div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 28px;">
    <!-- Recent Users -->
    <div class="table-card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; margin: 0;">Recent Student Registrations</h3>
            <a href="<?= baseUrl('admin/students.php') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentUsers)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--muted);">No registrations yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $u): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?= Sanitizer::e($u['full_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($u['email']) ?></div>
                                </td>
                                <td><span class="badge badge-gray"><?= strtoupper(Sanitizer::e($u['role'])) ?></span></td>
                                <td><span class="badge badge-success"><?= strtoupper(Sanitizer::e($u['status'])) ?></span></td>
                                <td style="font-size: 12px; color: var(--muted);"><?= Sanitizer::timeAgo($u['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Assessment Attempts -->
    <div class="table-card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; margin: 0;">Recent Assessment Activity</h3>
            <a href="<?= baseUrl('admin/assessments.php') ?>" class="btn btn-outline btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Score</th>
                        <th>Award</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentAttempts)): ?>
                        <tr><td colspan="4" style="text-align: center; color: var(--muted);">No completed attempts yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentAttempts as $att): ?>
                            <tr>
                                <td>
                                    <div style="font-weight: 600;"><?= Sanitizer::e($att['student_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($att['student_email']) ?></div>
                                </td>
                                <td style="font-size: 12px;"><?= Sanitizer::e($att['course_title']) ?></td>
                                <td style="font-weight: 700;"><?= number_format((float)$att['percentage'], 1) ?>%</td>
                                <td>
                                    <?php if ($att['qualifying_achievement'] === 'certificate'): ?>
                                        <span class="badge badge-success">Certificate</span>
                                    <?php elseif ($att['qualifying_achievement'] === 'silver_badge'): ?>
                                        <span class="badge badge-tier-silver">Silver</span>
                                    <?php elseif ($att['qualifying_achievement'] === 'bronze_badge'): ?>
                                        <span class="badge badge-tier-bronze">Bronze</span>
                                    <?php elseif ($att['qualifying_achievement'] === 'starter_badge'): ?>
                                        <span class="badge badge-tier-starter">Starter</span>
                                    <?php else: ?>
                                        <span class="badge badge-gray">None</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
