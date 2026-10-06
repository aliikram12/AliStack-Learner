<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Repositories\AssessmentRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$courseRepo = new CourseRepository();
$assessmentRepo = new AssessmentRepository();
$settingRepo = new SettingRepository();

$pdo = getDbConnection();

// Handle update or create assessment settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_assessment') {
        $assessmentId = !empty($_POST['assessment_id']) ? (int)$_POST['assessment_id'] : 0;
        $courseId = (int)$_POST['course_id'];
        $title = trim((string)$_POST['title']);
        $passPercentage = (float)$_POST['pass_percentage'];
        $timeLimit = (int)$_POST['time_limit_minutes'];
        $maxAttempts = (int)$_POST['max_attempts'];
        $isPublished = !empty($_POST['is_published']) ? 1 : 0;

        if ($assessmentId) {
            $stmt = $pdo->prepare("
                UPDATE assessments 
                SET title = ?, pass_percentage = ?, time_limit_minutes = ?, max_attempts = ?, is_published = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$title, $passPercentage, $timeLimit, $maxAttempts, $isPublished, $assessmentId]);
            $settingRepo->logAudit(Auth::id(), 'ASSESSMENT_UPDATE', 'assessments', $assessmentId, "Updated assessment {$title}");
            $_SESSION['flash_success'] = 'Assessment settings updated.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO assessments (course_id, title, pass_percentage, time_limit_minutes, max_attempts, is_published, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$courseId, $title, $passPercentage, $timeLimit, $maxAttempts, $isPublished]);
            $newId = (int)$pdo->lastInsertId();
            $settingRepo->logAudit(Auth::id(), 'ASSESSMENT_CREATE', 'assessments', $newId, "Created assessment {$title}");
            $_SESSION['flash_success'] = 'Assessment created successfully.';
        }
        header('Location: ' . baseUrl('admin/assessments.php'));
        exit;
    }
}

$stmt = $pdo->query("
    SELECT a.*, c.title as course_title,
    (SELECT COUNT(*) FROM assessment_questions aq WHERE aq.assessment_id = a.id) as question_count,
    (SELECT COUNT(*) FROM assessment_attempts aa WHERE aa.assessment_id = a.id) as attempt_count
    FROM assessments a
    JOIN courses c ON c.id = a.course_id
    ORDER BY a.id DESC
");
$assessments = $stmt->fetchAll();
$allCourses = $courseRepo->getAllAdmin(1, 100);

$pageTitle = 'Manage Assessments';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Course Assessments</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Configure final MCQ tests, passing thresholds, and time limits.</p>
    </div>

    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addAssessmentModal')">
        <i class="bi bi-plus-circle"></i> Create Assessment
    </button>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Assessment Title & Course</th>
                    <th>Questions</th>
                    <th>Pass Mark</th>
                    <th>Time Limit</th>
                    <th>Max Attempts</th>
                    <th>Status</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($assessments)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--muted);">No assessments configured yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($assessments as $a): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?= Sanitizer::e($a['title']) ?></div>
                                <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::e($a['course_title']) ?></div>
                            </td>
                            <td>
                                <a href="<?= baseUrl('admin/questions.php?assessment_id=' . $a['id']) ?>" style="font-weight: 600;">
                                    <?= (int)$a['question_count'] ?> Questions &rarr;
                                </a>
                            </td>
                            <td><span class="badge badge-success"><?= number_format((float)$a['pass_percentage'], 0) ?>%</span></td>
                            <td><?= (int)$a['time_limit_minutes'] ?> mins</td>
                            <td><?= (int)$a['max_attempts'] ?></td>
                            <td>
                                <span class="badge <?= $a['is_published'] ? 'badge-success' : 'badge-gray' ?>">
                                    <?= $a['is_published'] ? 'Published' : 'Hidden' ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="<?= baseUrl('admin/questions.php?assessment_id=' . $a['id']) ?>" class="btn btn-primary btn-sm">
                                    <i class="bi bi-list-check"></i> Manage Questions
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Create Assessment -->
<div class="modal-backdrop" id="addAssessmentModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Create Course Assessment</h3>
            <button type="button" class="modal-close" onclick="closeModal('addAssessmentModal')">&times;</button>
        </div>
        <form method="POST" action="<?= baseUrl('admin/assessments.php') ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="save_assessment">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="course_id">Course</label>
                    <select id="course_id" name="course_id" class="form-select" required>
                        <?php foreach ($allCourses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= Sanitizer::e($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="title">Assessment Title</label>
                    <input type="text" id="title" name="title" class="form-control" required placeholder="e.g. Modern Full-Stack PHP Final Assessment">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="pass_percentage">Pass %</label>
                        <input type="number" id="pass_percentage" name="pass_percentage" class="form-control" value="70.00" step="0.1" min="1" max="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="time_limit_minutes">Time (Mins)</label>
                        <input type="number" id="time_limit_minutes" name="time_limit_minutes" class="form-control" value="20" min="1">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="max_attempts">Attempts</label>
                        <input type="number" id="max_attempts" name="max_attempts" class="form-control" value="3" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_published" value="1" checked>
                        <span>Publish assessment immediately</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('addAssessmentModal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Assessment</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
