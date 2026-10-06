<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\AssessmentRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$assessmentRepo = new AssessmentRepository();
$settingRepo = new SettingRepository();
$pdo = getDbConnection();

$assessments = $pdo->query("SELECT a.id, a.title, a.course_id, c.title as course_title FROM assessments a JOIN courses c ON c.id = a.course_id ORDER BY a.id DESC")->fetchAll();
$selectedAssessmentId = !empty($_GET['assessment_id']) ? (int)$_GET['assessment_id'] : (!empty($assessments) ? (int)$assessments[0]['id'] : 0);

$selectedAssessment = $selectedAssessmentId ? $assessmentRepo->findById($selectedAssessmentId) : null;
$questions = $selectedAssessmentId ? $assessmentRepo->getQuestionsAdmin($selectedAssessmentId) : [];

// Handle create / delete question
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_question') {
        $data = [
            'assessment_id' => $selectedAssessmentId,
            'course_id' => (int)$selectedAssessment['course_id'],
            'question_text' => trim((string)$_POST['question_text']),
            'option_a' => trim((string)$_POST['option_a']),
            'option_b' => trim((string)$_POST['option_b']),
            'option_c' => trim((string)$_POST['option_c']),
            'option_d' => trim((string)$_POST['option_d']),
            'correct_option' => strtoupper(trim((string)$_POST['correct_option'])),
            'explanation' => trim((string)($_POST['explanation'] ?? '')),
            'topic' => trim((string)($_POST['topic'] ?? '')),
            'difficulty' => $_POST['difficulty'] ?? 'medium',
            'marks' => max(1, (int)($_POST['marks'] ?? 10))
        ];

        $qId = $assessmentRepo->createQuestion($data);
        $settingRepo->logAudit(Auth::id(), 'QUESTION_CREATE', 'assessment_questions', $qId, "Created question for assessment {$selectedAssessmentId}");
        $_SESSION['flash_success'] = 'Question created successfully.';
        header("Location: " . baseUrl("admin/questions.php?assessment_id={$selectedAssessmentId}"));
        exit;
    } elseif ($action === 'delete_question') {
        $qId = (int)($_POST['question_id'] ?? 0);
        if ($qId) {
            $assessmentRepo->deleteQuestion($qId);
            $settingRepo->logAudit(Auth::id(), 'QUESTION_DELETE', 'assessment_questions', $qId, "Deleted question ID {$qId}");
            $_SESSION['flash_success'] = 'Question deleted successfully.';
            header("Location: " . baseUrl("admin/questions.php?assessment_id={$selectedAssessmentId}"));
            exit;
        }
    }
}

$pageTitle = 'Manage MCQ Questions';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">MCQ Questions Bank</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Author and validate course-specific multiple-choice questions.</p>
    </div>

    <div style="display: flex; gap: 12px; align-items: center;">
        <select class="form-select" style="width: 320px;" onchange="window.location.href='<?= baseUrl('admin/questions.php?assessment_id=') ?>' + this.value;">
            <?php foreach ($assessments as $a): ?>
                <option value="<?= $a['id'] ?>" <?= ($selectedAssessmentId === (int)$a['id']) ? 'selected' : '' ?>>
                    <?= Sanitizer::e($a['title']) ?> (<?= Sanitizer::e($a['course_title']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <?php if ($selectedAssessmentId): ?>
            <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addQuestionModal')">
                <i class="bi bi-plus-circle"></i> Add Question
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="table-card">
    <div class="card-header">
        <h3 style="font-size: 1.1rem; margin: 0;">
            Questions for: <strong><?= Sanitizer::e($selectedAssessment['title'] ?? 'Assessment') ?></strong>
        </h3>
        <span style="font-size: 12px; color: var(--muted);"><?= count($questions) ?> questions active</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question</th>
                    <th>Options</th>
                    <th>Correct</th>
                    <th>Difficulty</th>
                    <th>Marks</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($questions)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--muted);">No questions authored yet. Click "Add Question" above.</td></tr>
                <?php else: ?>
                    <?php foreach ($questions as $idx => $q): ?>
                        <tr>
                            <td><strong><?= $idx + 1 ?></strong></td>
                            <td style="max-width: 320px;">
                                <div style="font-weight: 600; color: var(--dark);"><?= Sanitizer::e($q['question_text']) ?></div>
                                <?php if (!empty($q['explanation'])): ?>
                                    <div style="font-size: 11px; color: var(--muted); margin-top: 4px;"><em>Explanation:</em> <?= Sanitizer::e($q['explanation']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; color: var(--muted); line-height: 1.5;">
                                <div><strong>A:</strong> <?= Sanitizer::e($q['option_a']) ?></div>
                                <div><strong>B:</strong> <?= Sanitizer::e($q['option_b']) ?></div>
                                <div><strong>C:</strong> <?= Sanitizer::e($q['option_c']) ?></div>
                                <div><strong>D:</strong> <?= Sanitizer::e($q['option_d']) ?></div>
                            </td>
                            <td>
                                <span class="badge badge-success" style="font-size: 13px;">
                                    Option <?= Sanitizer::e($q['correct_option']) ?>
                                </span>
                            </td>
                            <td><span class="badge badge-gray"><?= ucfirst(Sanitizer::e($q['difficulty'])) ?></span></td>
                            <td><?= (int)$q['marks'] ?> pts</td>
                            <td style="text-align: right;">
                                <form method="POST" action="<?= baseUrl('admin/questions.php?assessment_id=' . $selectedAssessmentId) ?>" style="display: inline;" onsubmit="return confirm('Delete this question?');">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="action" value="delete_question">
                                    <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
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

<!-- Modal: Add Question -->
<div class="modal-backdrop" id="addQuestionModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <h3 class="modal-title">Add Course MCQ Question</h3>
            <button type="button" class="modal-close" onclick="closeModal('addQuestionModal')">&times;</button>
        </div>
        <form method="POST" action="<?= baseUrl('admin/questions.php?assessment_id=' . $selectedAssessmentId) ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="create_question">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="question_text">Question Prompt</label>
                    <textarea id="question_text" name="question_text" class="form-control" required rows="3" placeholder="Enter the exact question statement..."></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="option_a">Option A</label>
                        <input type="text" id="option_a" name="option_a" class="form-control" required placeholder="Option A text">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_b">Option B</label>
                        <input type="text" id="option_b" name="option_b" class="form-control" required placeholder="Option B text">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_c">Option C</label>
                        <input type="text" id="option_c" name="option_c" class="form-control" required placeholder="Option C text">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="option_d">Option D</label>
                        <input type="text" id="option_d" name="option_d" class="form-control" required placeholder="Option D text">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="correct_option">Correct Answer</label>
                        <select id="correct_option" name="correct_option" class="form-select" required>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="difficulty">Difficulty</label>
                        <select id="difficulty" name="difficulty" class="form-select">
                            <option value="easy">Easy</option>
                            <option value="medium" selected>Medium</option>
                            <option value="hard">Hard</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="marks">Marks</label>
                        <input type="number" id="marks" name="marks" class="form-control" value="10" min="1">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="explanation">Explanation (displayed after grading)</label>
                    <textarea id="explanation" name="explanation" class="form-control" rows="2" placeholder="Explain why this option is correct..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('addQuestionModal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Save Question</button>
            </div>
        </form>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
