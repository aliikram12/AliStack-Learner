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
$categories = $courseRepo->getCategories();

$id = !empty($_GET['id']) ? (int)$_GET['id'] : 0;
$course = $id ? $courseRepo->findById($id) : null;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();

    $title = trim((string)($_POST['title'] ?? ''));
    $slug = trim((string)($_POST['slug'] ?? ''));
    if (empty($slug)) {
        $slug = Sanitizer::slug($title);
    } else {
        $slug = Sanitizer::slug($slug);
    }

    $playlistUrl = trim((string)($_POST['youtube_playlist_url'] ?? ''));
    $playlistId = Sanitizer::extractYouTubePlaylistId($playlistUrl) ?: trim((string)($_POST['youtube_playlist_id'] ?? ''));

    $data = [
        'category_id' => !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null,
        'title' => $title,
        'slug' => $slug,
        'short_desc' => trim((string)($_POST['short_desc'] ?? '')),
        'full_desc' => trim((string)($_POST['full_desc'] ?? '')),
        'difficulty' => $_POST['difficulty'] ?? 'beginner',
        'language' => trim((string)($_POST['language'] ?? 'English')),
        'estimated_duration' => trim((string)($_POST['estimated_duration'] ?? '4 Hours')),
        'learning_objectives' => trim((string)($_POST['learning_objectives'] ?? '')),
        'prerequisites' => trim((string)($_POST['prerequisites'] ?? '')),
        'instructor_name' => trim((string)($_POST['instructor_name'] ?? 'AliStack Academy')),
        'youtube_playlist_url' => $playlistUrl,
        'youtube_playlist_id' => $playlistId,
        'status' => $_POST['status'] ?? 'published',
        'is_featured' => !empty($_POST['is_featured']) ? 1 : 0
    ];

    if (empty($title) || empty($data['short_desc'])) {
        $error = 'Title and Short Description are required.';
    } else {
        if ($id && $course) {
            $courseRepo->update($id, $data);
            $settingRepo->logAudit(Auth::id(), 'COURSE_UPDATE', 'courses', $id, "Updated course: {$title}");
            $_SESSION['flash_success'] = 'Course updated successfully.';
        } else {
            $id = $courseRepo->create($data);
            $settingRepo->logAudit(Auth::id(), 'COURSE_CREATE', 'courses', $id, "Created new course: {$title}");
            $_SESSION['flash_success'] = 'Course created successfully. You can now add lessons.';
        }
        header('Location: ' . baseUrl('admin/lessons.php?course_id=' . $id));
        exit;
    }
}

$pageTitle = $course ? 'Edit Course: ' . $course['title'] : 'Create New Course';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <a href="<?= baseUrl('admin/courses.php') ?>" style="font-size: 13px; color: var(--muted);"><i class="bi bi-arrow-left"></i> Back to Courses</a>
        <h1 style="font-size: 1.75rem; margin-top: 4px;"><?= $course ? 'Edit Course' : 'Create New Course' ?></h1>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><?= Sanitizer::e($error) ?></div>
    </div>
<?php endif; ?>

<div class="card" style="padding: 32px; max-width: 900px;">
    <form method="POST" action="<?= baseUrl('admin/course-edit.php' . ($id ? '?id=' . $id : '')) ?>">
        <?= Csrf::field() ?>

        <div class="form-group">
            <label class="form-label" for="title">Course Title</label>
            <input type="text" id="title" name="title" class="form-control" required value="<?= Sanitizer::e($course['title'] ?? '') ?>" placeholder="e.g. Modern Full-Stack PHP & MySQL Architecture">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="slug">URL Slug (leave empty to auto-generate)</label>
                <input type="text" id="slug" name="slug" class="form-control" value="<?= Sanitizer::e($course['slug'] ?? '') ?>" placeholder="modern-fullstack-php-mysql">
            </div>

            <div class="form-group">
                <label class="form-label" for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($course['category_id'] ?? null) == $cat['id']) ? 'selected' : '' ?>>
                            <?= Sanitizer::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="difficulty">Difficulty Level</label>
                <select id="difficulty" name="difficulty" class="form-select">
                    <option value="beginner" <?= (($course['difficulty'] ?? '') === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= (($course['difficulty'] ?? '') === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= (($course['difficulty'] ?? '') === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="estimated_duration">Estimated Duration</label>
                <input type="text" id="estimated_duration" name="estimated_duration" class="form-control" value="<?= Sanitizer::e($course['estimated_duration'] ?? '6 Hours') ?>" placeholder="e.g. 8 Hours">
            </div>

            <div class="form-group">
                <label class="form-label" for="instructor_name">Instructor Name</label>
                <input type="text" id="instructor_name" name="instructor_name" class="form-control" value="<?= Sanitizer::e($course['instructor_name'] ?? 'AliStack Academy') ?>">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="youtube_playlist_url">YouTube Playlist URL or Playlist ID</label>
            <input type="text" id="youtube_playlist_url" name="youtube_playlist_url" class="form-control" value="<?= Sanitizer::e($course['youtube_playlist_url'] ?? '') ?>" placeholder="https://www.youtube.com/playlist?list=PL4cUxeGkcC9gksOX3BdCEVDhUXF689Vj">
            <span style="font-size: 11px; color: var(--muted); margin-top: 4px; display: block;">The playlist ID will be extracted and stored automatically for the embedded player.</span>
        </div>

        <div class="form-group">
            <label class="form-label" for="short_desc">Short Description (for catalog cards)</label>
            <textarea id="short_desc" name="short_desc" class="form-control" rows="2" required placeholder="A brief one-sentence or two-sentence description..."><?= Sanitizer::e($course['short_desc'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="full_desc">Full Description & Syllabus Outline</label>
            <textarea id="full_desc" name="full_desc" class="form-control" rows="5" required placeholder="Detailed description of what the course covers..."><?= Sanitizer::e($course['full_desc'] ?? '') ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="learning_objectives">Learning Objectives (one per line)</label>
                <textarea id="learning_objectives" name="learning_objectives" class="form-control" rows="4" placeholder="Master PDO prepared statements&#10;Understand session fixation defenses&#10;Build modular PHP applications"><?= Sanitizer::e($course['learning_objectives'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="prerequisites">Prerequisites</label>
                <textarea id="prerequisites" name="prerequisites" class="form-control" rows="4" placeholder="Basic knowledge of HTML, CSS, and basic programming logic."><?= Sanitizer::e($course['prerequisites'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="display: flex; gap: 24px; align-items: center; margin-bottom: 28px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="status">Publication Status</label>
                <select id="status" name="status" class="form-select" style="width: 180px;">
                    <option value="published" <?= (($course['status'] ?? 'published') === 'published') ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= (($course['status'] ?? '') === 'draft') ? 'selected' : '' ?>>Draft</option>
                    <option value="archived" <?= (($course['status'] ?? '') === 'archived') ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div style="margin-top: 24px;">
                <label class="form-check">
                    <input type="checkbox" name="is_featured" value="1" <?= !empty($course['is_featured']) ? 'checked' : '' ?>>
                    <span>Feature this course on Homepage</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> <?= $course ? 'Save Course Changes' : 'Create Course & Proceed to Lessons' ?>
        </button>
    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
