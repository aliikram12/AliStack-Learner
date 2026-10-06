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

$courses = $courseRepo->getAllAdmin(1, 100);
$selectedCourseId = !empty($_GET['course_id']) ? (int)$_GET['course_id'] : (!empty($courses) ? (int)$courses[0]['id'] : 0);

$currentCourse = $selectedCourseId ? $courseRepo->findById($selectedCourseId) : null;
$lessons = $selectedCourseId ? $courseRepo->getLessons($selectedCourseId, false) : [];

// Handle lesson creation / updating / deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_lesson') {
        $videoInput = trim((string)($_POST['youtube_video_id'] ?? ''));
        $videoId = Sanitizer::extractYouTubeVideoId($videoInput) ?: $videoInput;

        $data = [
            'course_id' => $selectedCourseId,
            'title' => trim((string)($_POST['title'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'youtube_video_id' => $videoId,
            'duration_minutes' => max(1, (int)($_POST['duration_minutes'] ?? 10)),
            'lesson_order' => (int)($_POST['lesson_order'] ?? (count($lessons) + 1)),
            'learning_objectives' => trim((string)($_POST['learning_objectives'] ?? '')),
            'is_published' => !empty($_POST['is_published']) ? 1 : 0
        ];

        $lessonId = $courseRepo->createLesson($data);
        $settingRepo->logAudit(Auth::id(), 'LESSON_CREATE', 'course_lessons', $lessonId, "Added lesson to course ID {$selectedCourseId}");
        $_SESSION['flash_success'] = 'Lesson created successfully.';
        header("Location: " . baseUrl("admin/lessons.php?course_id={$selectedCourseId}"));
        exit;
    } elseif ($action === 'delete_lesson') {
        $lessonId = (int)($_POST['lesson_id'] ?? 0);
        if ($lessonId) {
            $courseRepo->deleteLesson($lessonId);
            $settingRepo->logAudit(Auth::id(), 'LESSON_DELETE', 'course_lessons', $lessonId, "Deleted lesson ID {$lessonId}");
            $_SESSION['flash_success'] = 'Lesson deleted successfully.';
            header("Location: " . baseUrl("admin/lessons.php?course_id={$selectedCourseId}"));
            exit;
        }
    }
}

$pageTitle = 'Manage Course Lessons';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Lessons Management</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Add and organize YouTube video lessons for each course.</p>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
        <select class="form-select" style="width: 280px;" onchange="window.location.href='<?= baseUrl('admin/lessons.php?course_id=') ?>' + this.value;">
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($selectedCourseId === (int)$c['id']) ? 'selected' : '' ?>>
                    <?= Sanitizer::e($c['title']) ?> (<?= (int)$c['lesson_count'] ?> lessons)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($selectedCourseId): ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addLessonModal')">
                <i class="bi bi-plus-circle"></i> Add Lesson
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="table-card">
    <div class="card-header">
        <h3 style="font-size: 1.1rem; margin: 0;">
            Lessons for: <strong><?= Sanitizer::e($currentCourse['title'] ?? 'Course') ?></strong>
        </h3>
        <span style="font-size: 12px; color: var(--muted);"><?= count($lessons) ?> lessons configured</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 60px;">Order</th>
                    <th>Lesson Title</th>
                    <th>YouTube Video ID</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($lessons)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--muted);">No lessons yet. Click "Add Lesson" above to add the first video lesson.</td></tr>
                <?php else: ?>
                    <?php foreach ($lessons as $l): ?>
                        <tr>
                            <td style="font-weight: 700; color: var(--muted);"><?= (int)$l['lesson_order'] ?></td>
                            <td>
                                <div style="font-weight: 600; color: var(--dark);"><?= Sanitizer::e($l['title']) ?></div>
                                <?php if (!empty($l['description'])): ?>
                                    <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::e($l['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="https://www.youtube.com/watch?v=<?= urlencode($l['youtube_video_id']) ?>" target="_blank" style="font-family: monospace; font-size: 12px;">
                                    <i class="bi bi-youtube" style="color: #DC2626;"></i> <?= Sanitizer::e($l['youtube_video_id']) ?>
                                </a>
                            </td>
                            <td><?= (int)$l['duration_minutes'] ?>m</td>
                            <td>
                                <span class="badge <?= $l['is_published'] ? 'badge-success' : 'badge-gray' ?>">
                                    <?= $l['is_published'] ? 'Published' : 'Hidden' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="<?= baseUrl('admin/lessons.php?course_id=' . $selectedCourseId) ?>" style="display: inline;" onsubmit="return confirm('Delete this lesson?');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="action" value="delete_lesson">
                                    <input type="hidden" name="lesson_id" value="<?= $l['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Lesson -->
<div class="modal-backdrop" id="addLessonModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Add Lesson to Course</h3>
            <button type="button" class="modal-close" onclick="closeModal('addLessonModal')">&times;</button>
        </div>
        <form method="POST" action="<?= baseUrl('admin/lessons.php?course_id=' . $selectedCourseId) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="create_lesson">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="lesson_title">Lesson Title</label>
                    <input type="text" id="lesson_title" name="title" class="form-control" required placeholder="e.g. Connecting to Database with PDO">
                </div>

                <div class="form-group">
                    <label class="form-label" for="youtube_video_id">YouTube Video ID or Full URL</label>
                    <input type="text" id="youtube_video_id" name="youtube_video_id" class="form-control" required placeholder="e.g. kEW6f7PILc4 or https://youtu.be/kEW6f7PILc4">
                    <span style="font-size: 11px; color: var(--muted); margin-top: 2px; display: block;">Video ID will be extracted automatically.</span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="duration_minutes">Duration (Minutes)</label>
                        <input type="number" id="duration_minutes" name="duration_minutes" class="form-control" value="15" min="1">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lesson_order">Order Position</label>
                        <input type="number" id="lesson_order" name="lesson_order" class="form-control" value="<?= count($lessons) + 1 ?>" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lesson_desc">Short Description / Outline</label>
                    <textarea id="lesson_desc" name="description" class="form-control" rows="3" placeholder="Overview of what is taught in this video..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_published" value="1" checked>
                        <span>Publish lesson immediately</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('addLessonModal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Add Lesson</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
