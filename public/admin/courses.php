<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$courseRepo = new CourseRepository();
$settingRepo = new SettingRepository();

// Handle course delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    Csrf::checkOrAbort();
    $courseId = (int)($_POST['course_id'] ?? 0);
    if ($courseId) {
        $courseRepo->delete($courseId);
        $settingRepo->logAudit(Auth::id(), 'COURSE_DELETE', 'courses', $courseId, "Deleted course ID {$courseId}");
        $_SESSION['flash_success'] = 'Course deleted successfully.';
        header('Location: ' . baseUrl('admin/courses.php'));
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$status = !empty($_GET['status']) ? (string)$_GET['status'] : null;
$search = !empty($_GET['q']) ? (string)$_GET['q'] : null;

$courses = $courseRepo->getAllAdmin($page, 20, $status, $search);
$total = $courseRepo->countAllAdmin($status, $search);

$pageTitle = 'Manage Courses';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Courses Management</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Configure YouTube playlists, syllabus structure, and visibility.</p>
    </div>

    <a href="<?= baseUrl('admin/course-edit.php') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-circle"></i> Create New Course
    </a>
</div>

<div class="table-card">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; gap: 12px; align-items: center; justify-content: space-between;">
        <form method="GET" action="<?= baseUrl('admin/courses.php') ?>" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" value="<?= Sanitizer::e($search ?? '') ?>" placeholder="Search courses..." class="form-control" style="width: 240px; font-size: 13px; padding: 6px 12px;">
            <select name="status" class="form-select" style="width: 140px; font-size: 13px; padding: 6px 12px;" onchange="this.form.submit()">
                <option value="">All Statuses</option>
                <option value="published" <?= ($status === 'published') ? 'selected' : '' ?>>Published</option>
                <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                <option value="archived" <?= ($status === 'archived') ? 'selected' : '' ?>>Archived</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        </form>

        <span style="font-size: 12px; color: var(--muted);"><?= $total ?> total courses</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title & Category</th>
                    <th>Difficulty</th>
                    <th>Lessons</th>
                    <th>Enrolled</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--muted);">No courses found.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?= Sanitizer::e($c['title']) ?></div>
                                <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($c['category_name'] ?? 'General') ?> &bull; Instructor: <?= Sanitizer::e($c['instructor_name']) ?></div>
                            </td>
                            <td><span class="badge badge-gray"><?= ucfirst(Sanitizer::e($c['difficulty'])) ?></span></td>
                            <td>
                                <a href="<?= baseUrl('admin/lessons.php?course_id=' . $c['id']) ?>" style="font-weight: 600;">
                                    <?= (int)$c['lesson_count'] ?> lessons &rarr;
                                </a>
                            </td>
                            <td><?= (int)$c['student_count'] ?></td>
                            <td>
                                <span class="badge <?= ($c['status'] === 'published') ? 'badge-success' : 'badge-warning' ?>">
                                    <?= strtoupper(Sanitizer::e($c['status'])) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" target="_blank" class="btn btn-outline btn-sm" title="Preview Public Page">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="<?= baseUrl('admin/course-edit.php?id=' . $c['id']) ?>" class="btn btn-outline btn-sm" title="Edit Course">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" action="<?= baseUrl('admin/courses.php') ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this course and all its lessons?');">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Delete Course">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
