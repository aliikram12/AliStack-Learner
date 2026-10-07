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
            <button type="button" class="btn btn-sm" onclick="openAiGeneratorModal()" style="background: linear-gradient(135deg, #7C3AED 0%, #2563EB 100%); color: #FFFFFF; font-weight: 700; border: none; box-shadow: 0 4px 14px rgba(124,58,237,0.35); padding: 8px 16px;">
                <i class="bi bi-stars"></i> ✨ AI Generate MCQs
            </button>
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

<!-- Modal: AI MCQ Generator -->
<div id="aiGeneratorModal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog" style="max-width: 800px; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-content" style="background: var(--bg-surface); border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; border: 1px solid var(--border);">
            
            <div style="padding: 20px 28px; background: linear-gradient(135deg, rgba(124,58,237,0.06), rgba(37,99,235,0.06)); border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #7C3AED, #2563EB); display: flex; align-items: center; justify-content: center; color: #FFFFFF; font-size: 20px; box-shadow: 0 4px 12px rgba(124,58,237,0.3);">
                        <i class="bi bi-stars"></i>
                    </div>
                    <div>
                        <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin: 0;">AI MCQ Question Generator</h3>
                        <p style="font-size: 12.5px; color: var(--muted); margin: 0;">Automated exam question authoring powered by AliStack AI Engine</p>
                    </div>
                </div>
                <button type="button" onclick="closeModal('aiGeneratorModal')" style="background: none; border: none; font-size: 24px; cursor: pointer; color: var(--muted); line-height: 1;">&times;</button>
            </div>

            <div style="padding: 24px 28px; overflow-y: auto; flex: 1;">
                <!-- Step 1: Configuration form -->
                <div id="aiConfigSection">
                    <div style="background: var(--bg-subtle); padding: 14px 18px; border-radius: var(--radius-md); margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: gap: 12px;">
                        <div>
                            <span style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Target Assessment:</span>
                            <div style="font-weight: 700; color: var(--dark); font-size: 15px;"><?= Sanitizer::e($selectedAssessment['title'] ?? 'Selected Assessment') ?></div>
                        </div>
                        <span class="badge badge-primary"><?= Sanitizer::e($selectedAssessment['course_title'] ?? 'Active Course') ?></span>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                        <div>
                            <label class="form-label" for="aiTopic" style="font-weight: 600; font-size: 13px;">Focus Topic (Optional)</label>
                            <input type="text" id="aiTopic" class="form-control" placeholder="e.g. HTML5 Forms, CSS Flexbox, DOM Manipulation, Async/Await...">
                            <small style="font-size: 11px; color: var(--muted);">Leave blank to cover all course lessons automatically.</small>
                        </div>
                        <div>
                            <label class="form-label" for="aiCount" style="font-weight: 600; font-size: 13px;">Question Count</label>
                            <select id="aiCount" class="form-select">
                                <option value="3">3 Questions</option>
                                <option value="5" selected>5 Questions</option>
                                <option value="8">8 Questions</option>
                                <option value="10">10 Questions</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" for="aiDifficulty" style="font-weight: 600; font-size: 13px;">Difficulty</label>
                            <select id="aiDifficulty" class="form-select">
                                <option value="easy">Easy</option>
                                <option value="medium" selected>Medium</option>
                                <option value="hard">Hard</option>
                            </select>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <button type="button" id="btnRunAiGen" class="btn btn-primary" onclick="generateAiQuestions()" style="background: linear-gradient(135deg, #7C3AED, #2563EB); border: none; padding: 10px 24px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.3);">
                            <i class="bi bi-cpu"></i> Generate Questions with AI
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="aiLoadingSection" style="display: none; text-align: center; padding: 48px 20px;">
                    <div class="spinner" style="width: 48px; height: 48px; border-width: 4px; border-color: rgba(124,58,237,0.2); border-top-color: var(--secondary); margin: 0 auto 20px;"></div>
                    <h4 style="font-size: 1.15rem; font-weight: 800; color: var(--dark); margin-bottom: 8px;">AI Engine is Synthesizing MCQs...</h4>
                    <p style="font-size: 13.5px; color: var(--muted); max-width: 480px; margin: 0 auto;">Analyzing course lessons, extracting technical competencies, constructing distractor choices, and drafting educational explanations.</p>
                </div>

                <!-- Step 2: Generated Questions Preview -->
                <div id="aiPreviewSection" style="display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                        <div>
                            <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800; color: var(--dark);">Generated Questions Preview</h4>
                            <span id="aiSourceBadge" class="badge badge-success" style="font-size: 11px;">AI Generated</span>
                        </div>
                        <button type="button" class="btn btn-outline btn-xs" onclick="resetAiGenForm()">
                            <i class="bi bi-arrow-repeat"></i> Regenerate
                        </button>
                    </div>

                    <div id="aiNoticeAlert" style="display: none; padding: 12px 16px; background: rgba(37,99,235,0.08); border-left: 4px solid var(--primary); border-radius: 6px; font-size: 13px; color: var(--dark); margin-bottom: 20px;"></div>

                    <div id="aiQuestionsList" style="display: flex; flex-direction: column; gap: 16px;"></div>
                </div>
            </div>

            <div class="modal-footer" id="aiModalFooter" style="padding: 16px 28px; background: var(--bg-subtle); border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('aiGeneratorModal')">Close</button>
                <button type="button" id="btnSaveAiQuestions" class="btn btn-primary btn-sm" style="display: none;" onclick="saveAllGeneratedQuestions()">
                    <i class="bi bi-check2-circle"></i> Save All to Question Bank (<span id="saveCountBadge">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var generatedQuestionsData = [];
var currentCourseId = <?= (int)($selectedAssessment['course_id'] ?? 0) ?>;
var currentAssessmentId = <?= (int)$selectedAssessmentId ?>;

function openAiGeneratorModal() {
    document.getElementById('aiConfigSection').style.display = 'block';
    document.getElementById('aiLoadingSection').style.display = 'none';
    document.getElementById('aiPreviewSection').style.display = 'none';
    document.getElementById('btnSaveAiQuestions').style.display = 'none';
    document.getElementById('aiGeneratorModal').style.display = 'flex';
}

function resetAiGenForm() {
    document.getElementById('aiConfigSection').style.display = 'block';
    document.getElementById('aiPreviewSection').style.display = 'none';
    document.getElementById('btnSaveAiQuestions').style.display = 'none';
}

function generateAiQuestions() {
    var topic = document.getElementById('aiTopic').value.trim();
    var count = document.getElementById('aiCount').value;
    var difficulty = document.getElementById('aiDifficulty').value;

    document.getElementById('aiConfigSection').style.display = 'none';
    document.getElementById('aiLoadingSection').style.display = 'block';
    document.getElementById('aiPreviewSection').style.display = 'none';

    var payload = {
        course_id: currentCourseId,
        assessment_id: currentAssessmentId,
        count: parseInt(count, 10),
        difficulty: difficulty,
        topic: topic
    };

    fetch(window.getApiUrl('api/admin/generate-questions.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.getCsrfToken()
        },
        body: JSON.stringify(payload)
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        document.getElementById('aiLoadingSection').style.display = 'none';
        if (!json.success || !json.data || !json.data.questions) {
            alert(json.message || 'Failed to generate questions. Please try again.');
            document.getElementById('aiConfigSection').style.display = 'block';
            return;
        }

        generatedQuestionsData = json.data.questions;
        renderQuestionsPreview(generatedQuestionsData, json.data.source, json.data.note);
    })
    .catch(function(err) {
        document.getElementById('aiLoadingSection').style.display = 'none';
        document.getElementById('aiConfigSection').style.display = 'block';
        alert('Network error while generating questions: ' + err.message);
    });
}

function renderQuestionsPreview(questions, source, note) {
    var container = document.getElementById('aiQuestionsList');
    container.innerHTML = '';

    if (note) {
        var noticeEl = document.getElementById('aiNoticeAlert');
        noticeEl.textContent = note;
        noticeEl.style.display = 'block';
    } else {
        document.getElementById('aiNoticeAlert').style.display = 'none';
    }

    var badge = document.getElementById('aiSourceBadge');
    if (source === 'ai_live') {
        badge.className = 'badge badge-primary';
        badge.innerHTML = '<i class="bi bi-stars"></i> Live AI Generated';
    } else {
        badge.className = 'badge badge-secondary';
        badge.innerHTML = '<i class="bi bi-mortarboard-fill"></i> Curriculum Engine';
    }

    questions.forEach(function(q, index) {
        var card = document.createElement('div');
        card.style.cssText = 'background: var(--bg-surface); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-xs);';

        var optionsHtml = ['A', 'B', 'C', 'D'].map(function(opt) {
            var key = 'option_' + opt.toLowerCase();
            var isCorrect = (q.correct_option === opt);
            var optStyle = isCorrect 
                ? 'background: #F0FDF4; border: 1.5px solid #22C55E; color: #166534; font-weight: 600;' 
                : 'background: var(--bg-subtle); border: 1px solid var(--border); color: var(--dark);';
            var checkIcon = isCorrect ? '<i class="bi bi-check-circle-fill" style="color: #16A34A; margin-left: auto;"></i>' : '';

            return '<div style="display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; font-size: 13px; margin-bottom: 6px; ' + optStyle + '">' +
                '<strong style="width: 22px; height: 22px; border-radius: 50%; background: ' + (isCorrect ? '#22C55E' : 'var(--border)') + '; color: ' + (isCorrect ? '#fff' : 'var(--dark)') + '; display: inline-flex; align-items: center; justify-content: center; font-size: 11px;">' + opt + '</strong>' +
                '<span>' + (q[key] || '') + '</span>' +
                checkIcon +
            '</div>';
        }).join('');

        card.innerHTML = 
            '<div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">' +
                '<span style="font-size: 12px; font-weight: 700; color: var(--primary);">Question ' + (index + 1) + ' &bull; ' + (q.topic || 'General') + '</span>' +
                '<span class="badge badge-info" style="font-size: 11px; text-transform: uppercase;">' + (q.difficulty || 'medium') + ' &bull; ' + (q.marks || 10) + ' pts</span>' +
            '</div>' +
            '<div style="font-size: 14.5px; font-weight: 700; color: var(--dark); margin-bottom: 12px; line-height: 1.4;">' + q.question_text + '</div>' +
            '<div style="margin-bottom: 12px;">' + optionsHtml + '</div>' +
            (q.explanation ? '<div style="font-size: 12px; color: var(--muted); background: var(--bg-subtle); padding: 8px 12px; border-radius: 6px;"><strong>Explanation:</strong> ' + q.explanation + '</div>' : '');

        container.appendChild(card);
    });

    document.getElementById('saveCountBadge').textContent = questions.length;
    document.getElementById('aiPreviewSection').style.display = 'block';
    document.getElementById('btnSaveAiQuestions').style.display = 'inline-flex';
}

function saveAllGeneratedQuestions() {
    if (!generatedQuestionsData || generatedQuestionsData.length === 0) {
        alert('No questions to save.');
        return;
    }

    var btn = document.getElementById('btnSaveAiQuestions');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner" style="width: 14px; height: 14px; border-width: 2px; margin-right: 6px;"></span> Saving to Database...';

    fetch(window.getApiUrl('api/admin/save-generated-questions.php'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': window.getCsrfToken()
        },
        body: JSON.stringify({
            assessment_id: currentAssessmentId,
            course_id: currentCourseId,
            questions: generatedQuestionsData
        })
    })
    .then(function(res) { return res.json(); })
    .then(function(json) {
        if (!json.success) {
            alert(json.message || 'Failed to save questions');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check2-circle"></i> Save All to Question Bank (' + generatedQuestionsData.length + ')';
            return;
        }

        alert('Success! ' + json.data.saved_count + ' questions were saved directly to this course assessment.');
        window.location.reload();
    })
    .catch(function(err) {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check2-circle"></i> Save All to Question Bank (' + generatedQuestionsData.length + ')';
        alert('Error saving questions: ' + err.message);
    });
}
</script>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
