<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Repositories\AssessmentRepository;
use App\Repositories\CertificateRepository;

Auth::requireAuth();

$userId = (int)Auth::id();
$attemptId = !empty($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;

$assessmentRepo = new AssessmentRepository();
$certRepo = new CertificateRepository();

$attempt = $assessmentRepo->getAttemptById($attemptId);
if (!$attempt || (int)$attempt['user_id'] !== $userId) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit;
}

$score = (float)$attempt['score'];
$totalMarks = (float)$attempt['total_marks'];
$percentage = (float)$attempt['percentage'];
$achievement = $attempt['qualifying_achievement'];

// Check if certificate was issued for this attempt
$cert = null;
if ($achievement === 'certificate') {
    $userCerts = $certRepo->getUserCertificates($userId);
    foreach ($userCerts as $c) {
        if ((int)$c['attempt_id'] === $attemptId) {
            $cert = $c;
            break;
        }
    }
}

// Answers breakdown (if allowed by assessment configuration)
$answers = [];
if (!empty($attempt['reveal_answers'])) {
    $answers = $assessmentRepo->getAttemptAnswers($attemptId);
}

$pageTitle = 'Assessment Results - ' . $attempt['assessment_title'];
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div class="container" style="max-width: 840px; padding: 48px 24px 80px;">
    <!-- Result Summary Card -->
    <div class="card" style="padding: 40px; text-align: center; margin-bottom: 32px; box-shadow: var(--shadow-lg);">
        <div style="font-size: 13px; font-weight: 700; color: var(--muted); text-transform: uppercase; margin-bottom: 8px;">
            Assessment Results &bull; <?= Sanitizer::e($attempt['course_title']) ?>
        </div>
        <h1 style="font-size: 2rem; margin-bottom: 20px;"><?= Sanitizer::e($attempt['assessment_title']) ?></h1>

        <!-- Score Meter -->
        <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 140px; height: 140px; border-radius: 50%; border: 8px solid <?= ($percentage >= 70.0) ? 'var(--success)' : (($percentage >= 40.0) ? 'var(--warning)' : 'var(--error)') ?>; margin-bottom: 24px;">
            <div style="font-size: 32px; font-weight: 800; color: var(--dark);"><?= number_format($percentage, 1) ?>%</div>
            <div style="font-size: 12px; color: var(--muted);"><?= $score ?> / <?= $totalMarks ?> pts</div>
        </div>

        <!-- Achievement Banner -->
        <?php if ($achievement === 'certificate'): ?>
            <div style="background: #F0FDF4; border: 1px solid #86EFAC; border-radius: var(--radius-md); padding: 24px; max-width: 580px; margin: 0 auto 28px;">
                <div style="font-size: 36px; color: var(--success); margin-bottom: 8px;"><i class="bi bi-patch-check-fill"></i></div>
                <h3 style="color: #14532D; margin-bottom: 8px;">Congratulations! Certificate Earned</h3>
                <p style="font-size: 14px; color: #166534; margin-bottom: 16px;">
                    You have demonstrated technical mastery by scoring <strong><?= number_format($percentage, 1) ?>%</strong>. Your official AliStack Course Certificate has been issued and registered in the AliStack registry.
                </p>
                <?php if ($cert): ?>
                    <div style="font-size: 13px; color: #166534; margin-bottom: 16px;">
                        Certificate ID: <strong><?= Sanitizer::e($cert['certificate_number']) ?></strong>
                    </div>
                    <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-primary">
                        <i class="bi bi-printer-fill"></i> View & Print Certificate
                    </a>
                <?php endif; ?>
            </div>
        <?php elseif ($achievement === 'silver_badge'): ?>
            <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: var(--radius-md); padding: 24px; max-width: 580px; margin: 0 auto 28px;">
                <div style="font-size: 40px; margin-bottom: 8px;">🥈</div>
                <h3 style="color: var(--dark); margin-bottom: 8px;">Silver Proficiency Badge Awarded</h3>
                <p style="font-size: 14px; color: var(--muted); margin: 0;">
                    Great effort! You achieved between 60% and 69.99%. Your Silver Badge has been added to your profile achievements. Review a few lessons to aim for the 70% Certificate!
                </p>
            </div>
        <?php elseif ($achievement === 'bronze_badge'): ?>
            <div style="background: #FFF7ED; border: 1px solid #FED7AA; border-radius: var(--radius-md); padding: 24px; max-width: 580px; margin: 0 auto 28px;">
                <div style="font-size: 40px; margin-bottom: 8px;">🥉</div>
                <h3 style="color: #9A3412; margin-bottom: 8px;">Bronze Competency Badge Awarded</h3>
                <p style="font-size: 14px; color: #C2410C; margin: 0;">
                    Good foundation! You achieved between 50% and 59.99%. Your Bronze Badge has been added to your profile. Review your lesson notes and retake to earn higher tiers.
                </p>
            </div>
        <?php elseif ($achievement === 'starter_badge'): ?>
            <div style="background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: var(--radius-md); padding: 24px; max-width: 580px; margin: 0 auto 28px;">
                <div style="font-size: 40px; margin-bottom: 8px;">🎖️</div>
                <h3 style="color: #3730A3; margin-bottom: 8px;">Foundation Starter Badge Awarded</h3>
                <p style="font-size: 14px; color: #4338CA; margin: 0;">
                    You scored between 40% and 49.99%. You have earned the Foundation Starter Badge. Continue practicing to strengthen your mastery!
                </p>
            </div>
        <?php else: ?>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-md); padding: 24px; max-width: 580px; margin: 0 auto 28px;">
                <div style="font-size: 36px; color: var(--error); margin-bottom: 8px;"><i class="bi bi-info-circle-fill"></i></div>
                <h3 style="color: #991B1B; margin-bottom: 8px;">Keep Practicing</h3>
                <p style="font-size: 14px; color: #B91C1C; margin: 0;">
                    You scored <?= number_format($percentage, 1) ?>%. An achievement badge requires 40% or higher. We encourage you to review the video lessons, ask the AI Tutor for clarification, and attempt the assessment again.
                </p>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-outline">
                <i class="bi bi-grid-fill"></i> Dashboard
            </a>
            <a href="<?= baseUrl('learning.php?course_id=' . $attempt['course_id']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-arrow-repeat"></i> Review Lessons
            </a>
            <a href="<?= baseUrl('certificates.php') ?>" class="btn btn-outline">
                <i class="bi bi-award"></i> My Achievements
            </a>
        </div>
    </div>

    <!-- Answers Breakdown (if enabled) -->
    <?php if (!empty($answers)): ?>
        <div>
            <h3 style="font-size: 1.4rem; margin-bottom: 20px;">Question Breakdown</h3>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <?php foreach ($answers as $idx => $ans): 
                    $isCorrect = !empty($ans['is_correct']);
                ?>
                    <div class="card" style="padding: 24px; border-left: 6px solid <?= $isCorrect ? 'var(--success)' : 'var(--error)' ?>;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge <?= $isCorrect ? 'badge-success' : 'badge-danger' ?>">
                                <?= $isCorrect ? 'Correct (+10 pts)' : 'Incorrect (0 pts)' ?>
                            </span>
                            <span style="font-size: 12px; color: var(--muted);">Question <?= $idx + 1 ?></span>
                        </div>

                        <div style="font-weight: 600; font-size: 15px; margin-bottom: 16px;">
                            <?= Sanitizer::e($ans['question_text']) ?>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px; margin-bottom: 16px;">
                            <div style="padding: 10px; background: #F8FAFC; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                                <strong>Your Answer:</strong> Option <?= Sanitizer::e($ans['selected_option'] ?? 'None') ?>
                            </div>
                            <div style="padding: 10px; background: #F0FDF4; border-radius: var(--radius-sm); border: 1px solid #BBF7D0; color: #166534;">
                                <strong>Correct Answer:</strong> Option <?= Sanitizer::e($ans['correct_option']) ?>
                            </div>
                        </div>

                        <?php if (!empty($ans['explanation'])): ?>
                            <div style="background: #F8FAFC; padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13px; color: var(--muted); border-left: 3px solid var(--primary);">
                                <strong>Explanation:</strong> <?= Sanitizer::e($ans['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
