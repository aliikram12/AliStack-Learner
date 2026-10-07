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
$shouldStart = isset($_GET['start']) && $_GET['start'] === '1';

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
    $pageTitle = 'Assessment Not Found';
    require_once dirname(__DIR__) . '/templates/layouts/header.php';
    ?>
    <div class="container" style="max-width: 680px; padding: 72px 24px; text-align: center;">
        <div class="card" style="padding: 48px; border: 1px solid var(--border); border-radius: var(--radius-xl);">
            <div style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"><i class="bi bi-patch-question"></i></div>
            <h2 style="font-size: 1.6rem; margin-bottom: 12px; color: var(--text-dark);">No Assessment Configured</h2>
            <p style="color: var(--text-muted); margin-bottom: 24px;">An assessment has not yet been attached to this course. Please check back later or continue exploring lessons.</p>
            <a href="<?= baseUrl('learning.php?course_id=' . $courseId) ?>" class="btn btn-primary">
                <i class="bi bi-play-circle"></i> Return to Lessons
            </a>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/templates/layouts/footer.php';
    exit;
}

// Check eligibility
$eligibility = $service->checkEligibility($userId, $courseId);
if (!$eligibility['eligible']) {
    $pageTitle = 'Assessment Ineligible';
    require_once dirname(__DIR__) . '/templates/layouts/header.php';
    ?>
    <div class="container" style="max-width: 680px; padding: 72px 24px; text-align: center;">
        <div class="card" data-animate="fade-up" style="padding: 48px 36px; border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
            <div style="width: 64px; height: 64px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h2 style="font-size: 1.6rem; margin-bottom: 12px; color: var(--text-dark);">Assessment Locked</h2>
            <p style="color: var(--text-muted); font-size: 15px; line-height: 1.6; margin-bottom: 28px; max-width: 500px; margin-left: auto; margin-right: auto;">
                <?= Sanitizer::e($eligibility['reason']) ?>
            </p>
            <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= baseUrl('learning.php?course_id=' . $courseId) ?>" class="btn btn-primary">
                    <i class="bi bi-play-circle"></i> Continue Lessons
                </a>
                <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-outline">
                    Return to Dashboard
                </a>
            </div>
        </div>
    </div>
    <?php
    require_once dirname(__DIR__) . '/templates/layouts/footer.php';
    exit;
}

// Check if there is an existing in-progress attempt
$userAttempts = $assessmentRepo->getUserAttempts($userId, (int)$assessment['id']);
$inProgressAttempt = null;
foreach ($userAttempts as $att) {
    if ($att['status'] === 'in_progress') {
        $inProgressAttempt = $att;
        break;
    }
}

// If user hasn't explicitly started and has no active in-progress attempt, show Pre-Test Briefing Card
if (!$shouldStart && !$inProgressAttempt) {
    $pageTitle = 'Assessment Briefing - ' . $assessment['title'];
    $extraCss = ['player.css'];
    require_once dirname(__DIR__) . '/templates/layouts/header.php';
    ?>
    <div class="container" style="max-width: 820px; padding: 56px 20px 80px;">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 24px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
            <span>/</span>
            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($course['slug'])) ?>" style="color: var(--text-muted); text-decoration: none;"><?= Sanitizer::e($course['title']) ?></a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;">Assessment</span>
        </div>

        <div class="card" data-animate="fade-up" style="padding: 44px; border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 24px; border-bottom: 1px solid var(--border); padding-bottom: 24px;">
                <div>
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-light); color: var(--primary); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; margin-bottom: 10px; text-transform: uppercase;">
                        <i class="bi bi-patch-question-fill"></i> Final Graded Assessment
                    </div>
                    <h1 style="font-size: 1.85rem; font-weight: 800; color: var(--text-dark); margin: 0 0 6px;"><?= Sanitizer::e($assessment['title']) ?></h1>
                    <div style="font-size: 14px; color: var(--text-muted);">Course: <strong><?= Sanitizer::e($course['title']) ?></strong></div>
                </div>

                <div style="text-align: right;">
                    <span class="badge badge-success" style="font-size: 13px; padding: 6px 14px;">
                        <i class="bi bi-unlock-fill"></i> Assessment Unlocked
                    </span>
                </div>
            </div>

            <!-- Parameters Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 32px;">
                <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; text-align: center;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Time Limit</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--text-dark);"><?= (int)$assessment['time_limit_minutes'] ?> Mins</div>
                </div>

                <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; text-align: center;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Passing Score</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--success);"><?= number_format((float)$assessment['pass_percentage'], 0) ?>%</div>
                </div>

                <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; text-align: center;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Attempts Used</div>
                    <div style="font-size: 1.5rem; font-weight: 800; color: var(--primary);">
                        <?= $eligibility['attempts_used'] ?> / <?= $eligibility['max_attempts'] ?>
                    </div>
                </div>

                <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 18px; text-align: center;">
                    <div style="font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Format</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin-top: 4px;">Multiple Choice</div>
                </div>
            </div>

            <!-- Rules & Guidance -->
            <div style="background: #F8FAFC; border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; margin-bottom: 36px;">
                <h3 style="font-size: 15px; font-weight: 700; color: var(--text-dark); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="color: var(--primary);"></i> Assessment Instructions & Guidelines
                </h3>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: var(--text-muted); line-height: 1.8;">
                    <li>Once you click <strong>Start Assessment</strong>, your test timer will begin immediately.</li>
                    <li>You can move back and forth between questions using the Question Navigator buttons or dots.</li>
                    <li>Selected answers can be revised at any point before submitting.</li>
                    <li>If the timer reaches 00:00, your current answers will be submitted automatically by the server.</li>
                    <li>Scoring <strong>70% or above</strong> earns an official verified AliStack Course Certificate.</li>
                    <li>Scoring <strong>40% to 69.99%</strong> awards a Silver, Bronze, or Foundation Starter Performance Badge.</li>
                </ul>
            </div>

            <div style="display: flex; gap: 16px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                <a href="<?= baseUrl('learning.php?course_id=' . $courseId) ?>" class="btn btn-outline">
                    <i class="bi bi-arrow-left"></i> Review Lessons
                </a>
                <a href="<?= baseUrl('assessment.php?course_id=' . $courseId . '&start=1') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                    <i class="bi bi-play-circle-fill"></i> Start Assessment Now
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
$pageTitle = 'Testing: ' . $assessment['title'];

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Hidden Configuration for Assessment JS -->
<div id="testConfig"
     data-attempt-id="<?= $attempt['id'] ?>"
     data-time-limit="<?= $timeLimitMinutes ?>"
     data-total-questions="<?= count($questions) ?>"></div>

<div class="test-layout">
    <!-- Header Card -->
    <div class="test-header-card" style="box-shadow: var(--shadow-sm);">
        <div>
            <div style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.5px;">
                Final Graded Assessment &bull; <?= Sanitizer::e($course['title']) ?>
            </div>
            <h1 style="font-size: 1.45rem; font-weight: 800; color: var(--text-dark); margin: 4px 0 2px;"><?= Sanitizer::e($assessment['title']) ?></h1>
            <div style="font-size: 13px; color: var(--text-muted);">
                Passing Score: <strong style="color: var(--success);"><?= number_format((float)$assessment['pass_percentage'], 0) ?>%</strong> &bull; Total Questions: <strong><?= count($questions) ?></strong>
            </div>
        </div>

        <div class="test-timer">
            <i class="bi bi-stopwatch"></i>
            <span id="timerDisplay"><?= $timeLimitMinutes ?>:00</span>
        </div>
    </div>

    <!-- Question Navigator Dots -->
    <div class="test-navigator" style="background: #FFFFFF; border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px 18px; margin-bottom: 24px; display: flex; gap: 8px; flex-wrap: wrap;">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="nav-dot <?= ($idx === 0) ? 'active' : '' ?>" data-index="<?= $idx ?>" data-question-id="<?= $q['id'] ?>" style="cursor: pointer; width: 34px; height: 34px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; border: 1px solid var(--border); background: #F8FAFC; transition: var(--transition-base);">
                <?= $idx + 1 ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Questions Container -->
    <div id="questionsContainer">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="test-question-item" data-question-id="<?= $q['id'] ?>" style="<?= ($idx === 0) ? 'display: block;' : 'display: none;' ?>">
                <div class="test-question-card" style="padding: 36px 32px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <div class="question-number-badge" style="margin: 0;">QUESTION <?= $idx + 1 ?> OF <?= count($questions) ?></div>
                        <span class="badge badge-secondary" style="font-size: 11px;"><?= (int)$q['marks'] ?> MARKS</span>
                    </div>

                    <div class="question-title" style="font-size: 1.25rem; font-weight: 700; color: var(--text-dark); line-height: 1.5; margin-bottom: 24px;">
                        <?= Sanitizer::e($q['question_text']) ?>
                    </div>

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
                            <label class="option-label" style="display: flex; align-items: center; gap: 14px; padding: 16px 20px; border-radius: var(--radius-md); border: 1px solid var(--border); background: #F8FAFC; cursor: pointer; transition: all 0.2s ease;">
                                <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $optKey ?>" class="option-radio" style="width: 18px; height: 18px; accent-color: var(--primary);">
                                <span class="option-text" style="font-size: 14.5px; color: var(--text-dark); line-height: 1.5;">
                                    <strong style="color: var(--primary); margin-right: 4px;"><?= $optKey ?>.</strong> <?= Sanitizer::e($optVal) ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Test Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding: 16px 0;">
        <button type="button" id="prevQuestionBtn" class="btn btn-outline" disabled>
            <i class="bi bi-arrow-left"></i> Previous Question
        </button>

        <div style="display: flex; gap: 12px;">
            <button type="button" id="nextQuestionBtn" class="btn btn-primary">
                Next Question <i class="bi bi-arrow-right"></i>
            </button>
            <button type="button" id="submitTestBtn" class="btn btn-success" style="display: none; background: #16A34A; color: #FFFFFF; font-weight: 700;">
                <i class="bi bi-check2-circle"></i> Submit Assessment
            </button>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
