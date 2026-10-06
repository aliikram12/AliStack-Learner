<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Services\AssessmentService;
use App\Repositories\CourseRepository;
use App\Repositories\AssessmentRepository;

Auth::requireAuth();

$userId = (int)Auth::id();
$courseId = !empty($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

$courseRepo = new CourseRepository();
$assessmentRepo = new AssessmentRepository();
$service = new AssessmentService();

$course = $courseRepo->findById($courseId);
if (!$course) {
    header('Location: ' . baseUrl('courses.php'));
    exit;
}

$assessment = $assessmentRepo->findByCourseId($courseId);
if (!$assessment) {
    die("No assessment has been configured for this course yet.");
}

// Check eligibility
$eligibility = $service->checkEligibility($userId, $courseId);
if (!$eligibility['eligible']) {
    $pageTitle = 'Assessment Ineligible';
    require_once dirname(__DIR__) . '/templates/layouts/header.php';
    ?>
    <div class="container" style="max-width: 680px; padding: 64px 24px; text-align: center;">
        <div class="card" style="padding: 40px;">
            <div style="font-size: 48px; color: var(--warning); margin-bottom: 16px;"><i class="bi bi-shield-lock"></i></div>
            <h2 style="margin-bottom: 12px;">Assessment Not Available</h2>
            <p style="color: var(--muted); margin-bottom: 24px;">
                <?= Sanitizer::e($eligibility['reason']) ?>
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="<?= baseUrl('learning.php?course_id=' . $courseId) ?>" class="btn btn-primary">
                    <i class="bi bi-play-circle"></i> Return to Lessons
                </a>
                <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-outline">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/templates/layouts/footer.php';
    exit;
}

// Start or Resume attempt
$attemptResult = $service->startAttempt($userId, (int)$assessment['id']);
if (!$attemptResult['success']) {
    die(Sanitizer::e($attemptResult['error']));
}

$attempt = $attemptResult['attempt'];
$questions = $attemptResult['questions'];
$timeLimitMinutes = (int)$assessment['time_limit_minutes'];

$extraCss = ['player.css'];
$extraJs = ['assessment.js'];
$pageTitle = $assessment['title'];

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Hidden Configuration for Assessment JS -->
<div id="testConfig"
     data-attempt-id="<?= $attempt['id'] ?>"
     data-time-limit="<?= $timeLimitMinutes ?>"
     data-total-questions="<?= count($questions) ?>"></div>

<div class="test-layout">
    <!-- Header Card -->
    <div class="test-header-card">
        <div>
            <div style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase;">
                Final Graded Assessment &bull; <?= Sanitizer::e($course['title']) ?>
            </div>
            <h1 style="font-size: 1.4rem; margin: 4px 0;"><?= Sanitizer::e($assessment['title']) ?></h1>
            <div style="font-size: 12px; color: var(--muted);">
                Passing Score: <strong><?= number_format((float)$assessment['pass_percentage'], 0) ?>%</strong> &bull; Total Questions: <strong><?= count($questions) ?></strong>
            </div>
        </div>

        <div class="test-timer">
            <i class="bi bi-stopwatch"></i>
            <span id="timerDisplay"><?= $timeLimitMinutes ?>:00</span>
        </div>
    </div>

    <!-- Question Navigator Dots -->
    <div class="test-navigator">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="nav-dot" data-index="<?= $idx ?>" data-question-id="<?= $q['id'] ?>">
                <?= $idx + 1 ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Questions Container -->
    <div id="questionsContainer">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="test-question-item" data-question-id="<?= $q['id'] ?>" style="<?= ($idx === 0) ? 'display: block;' : 'display: none;' ?>">
                <div class="test-question-card">
                    <div class="question-number-badge">QUESTION <?= $idx + 1 ?> OF <?= count($questions) ?> (<?= (int)$q['marks'] ?> MARKS)</div>
                    <div class="question-title"><?= Sanitizer::e($q['question_text']) ?></div>

                    <div class="options-list">
                        <?php 
                        $options = [
                            'A' => $q['option_a'],
                            'B' => $q['option_b'],
                            'C' => $q['option_c'],
                            'D' => $q['option_d']
                        ];
                        foreach ($options as $optKey => $optVal): 
                        ?>
                            <label class="option-label">
                                <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $optKey ?>" class="option-radio">
                                <span class="option-text">
                                    <strong><?= $optKey ?>.</strong> <?= Sanitizer::e($optVal) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Test Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px;">
        <button type="button" id="prevQuestionBtn" class="btn btn-outline" disabled>
            <i class="bi bi-arrow-left"></i> Previous Question
        </button>

        <div>
            <button type="button" id="nextQuestionBtn" class="btn btn-primary">
                Next Question <i class="bi bi-arrow-right"></i>
            </button>
            <button type="button" id="submitTestBtn" class="btn btn-success" style="display: none; background: var(--success); color: #FFFFFF;">
                <i class="bi bi-check2-circle"></i> Submit Assessment
            </button>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
