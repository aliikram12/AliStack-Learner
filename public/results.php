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

<div class="container" style="max-width: 860px; padding: 48px 20px 88px;">
    <!-- Result Summary Card -->
    <div class="card" data-animate="fade-up" style="padding: 48px 36px; text-align: center; margin-bottom: 32px; border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); background: #FFFFFF; position: relative; overflow: hidden;">
        
        <!-- Subtle Top Glow -->
        <div style="position: absolute; top: 0; left: 0; right: 0; height: 5px; background: <?= ($percentage >= 70.0) ? 'linear-gradient(90deg, #16A34A, #10B981)' : (($percentage >= 40.0) ? 'linear-gradient(90deg, #F59E0B, #7C3AED)' : 'linear-gradient(90deg, #DC2626, #F87171)') ?>;"></div>

        <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
            Assessment Completed &bull; <?= Sanitizer::e($attempt['course_title']) ?>
        </div>
        <h1 style="font-size: clamp(1.6rem, 3vw, 2.15rem); font-weight: 800; color: var(--text-dark); margin: 0 0 28px;">
            <?= Sanitizer::e($attempt['assessment_title']) ?>
        </h1>

        <!-- Circular Score Meter -->
        <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; width: 154px; height: 154px; border-radius: 50%; border: 8px solid <?= ($percentage >= 70.0) ? '#16A34A' : (($percentage >= 40.0) ? '#F59E0B' : '#DC2626') ?>; background: <?= ($percentage >= 70.0) ? '#F0FDF4' : (($percentage >= 40.0) ? '#FFFBEB' : '#FEF2F2') ?>; margin-bottom: 32px; box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
            <div style="font-size: 34px; font-weight: 800; color: var(--text-dark); line-height: 1;"><?= number_format($percentage, 1) ?>%</div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-top: 4px;"><?= $score ?> / <?= $totalMarks ?> pts</div>
        </div>

        <!-- Achievement Banner -->
        <?php if ($achievement === 'certificate'): ?>
            <div style="background: #F0FDF4; border: 1px solid #86EFAC; border-radius: var(--radius-lg); padding: 28px; max-width: 620px; margin: 0 auto 32px; text-align: center;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h3 style="color: #14532D; font-size: 1.35rem; font-weight: 800; margin-bottom: 8px;">Official Certificate Earned!</h3>
                <p style="font-size: 14.5px; color: #166534; line-height: 1.6; margin-bottom: 20px;">
                    Outstanding work! You demonstrated technical mastery with a final score of <strong><?= number_format($percentage, 1) ?>%</strong>. Your official AliStack Course Certificate is permanently registered and ready to share.
                </p>
                <?php if ($cert): ?>
                    <div style="background: #FFFFFF; border: 1px dashed #86EFAC; border-radius: var(--radius-md); padding: 12px 18px; display: inline-block; font-size: 13px; color: #14532D; margin-bottom: 20px;">
                        Certificate Serial: <strong><?= Sanitizer::e($cert['certificate_number']) ?></strong>
                    </div>
                    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                        <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-primary" style="background: #16A34A; border-color: #16A34A;">
                            <i class="bi bi-printer-fill"></i> View & Print Certificate
                        </a>
                        <a href="<?= baseUrl('verify-certificate.php?code=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-outline" style="border-color: #86EFAC; color: #166534;">
                            <i class="bi bi-patch-check"></i> Public Verification
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($achievement === 'silver_badge'): ?>
            <div style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: var(--radius-lg); padding: 28px; max-width: 620px; margin: 0 auto 32px;">
                <div style="font-size: 44px; margin-bottom: 8px;">🥈</div>
                <h3 style="color: var(--text-dark); font-size: 1.35rem; font-weight: 800; margin-bottom: 8px;">Silver Proficiency Badge Awarded</h3>
                <p style="font-size: 14.5px; color: var(--text-muted); line-height: 1.6; margin: 0;">
                    Excellent result! You scored between 60% and 69.99%. Your Silver Badge has been recorded on your student profile. Review key lessons and retake when ready to earn the 70% Certificate!
                </p>
            </div>
        <?php elseif ($achievement === 'bronze_badge'): ?>
            <div style="background: #FFF7ED; border: 1px solid #FED7AA; border-radius: var(--radius-lg); padding: 28px; max-width: 620px; margin: 0 auto 32px;">
                <div style="font-size: 44px; margin-bottom: 8px;">🥉</div>
                <h3 style="color: #9A3412; font-size: 1.35rem; font-weight: 800; margin-bottom: 8px;">Bronze Competency Badge Awarded</h3>
                <p style="font-size: 14.5px; color: #C2410C; line-height: 1.6; margin: 0;">
                    Solid foundation! You scored between 50% and 59.99%. Your Bronze Badge has been added to your profile achievements. Keep practicing to reach the certificate tier!
                </p>
            </div>
        <?php elseif ($achievement === 'starter_badge'): ?>
            <div style="background: #EEF2FF; border: 1px solid #C7D2FE; border-radius: var(--radius-lg); padding: 28px; max-width: 620px; margin: 0 auto 32px;">
                <div style="font-size: 44px; margin-bottom: 8px;">🎖️</div>
                <h3 style="color: #3730A3; font-size: 1.35rem; font-weight: 800; margin-bottom: 8px;">Foundation Starter Badge Awarded</h3>
                <p style="font-size: 14.5px; color: #4338CA; line-height: 1.6; margin: 0;">
                    You scored between 40% and 49.99%. You have earned the Foundation Starter Badge. Practice with the AliStack AI Tutor to strengthen tricky concepts!
                </p>
            </div>
        <?php else: ?>
            <div style="background: #FEF2F2; border: 1px solid #FECACA; border-radius: var(--radius-lg); padding: 28px; max-width: 620px; margin: 0 auto 32px;">
                <div style="font-size: 40px; color: #DC2626; margin-bottom: 8px;"><i class="bi bi-info-circle-fill"></i></div>
                <h3 style="color: #991B1B; font-size: 1.35rem; font-weight: 800; margin-bottom: 8px;">Keep Practicing &mdash; You're Getting Closer</h3>
                <p style="font-size: 14.5px; color: #B91C1C; line-height: 1.6; margin: 0;">
                    You achieved <?= number_format($percentage, 1) ?>%. Earning an achievement badge requires 40% or higher. We encourage you to review the lesson playlists, take notes, ask the AI Tutor, and attempt the assessment again.
                </p>
            </div>
        <?php endif; ?>

        <!-- Action Navigation -->
        <div style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-primary">
                <i class="bi bi-speedometer2"></i> Return to Dashboard
            </a>
            <a href="<?= baseUrl('learning.php?course_id=' . $attempt['course_id']) ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-repeat"></i> Review Lessons
            </a>
            <a href="<?= baseUrl('certificates.php') ?>" class="btn btn-outline">
                <i class="bi bi-award"></i> My Achievements
            </a>
        </div>
    </div>

    <!-- Answers Breakdown (if enabled by assessment config) -->
    <?php if (!empty($answers)): ?>
        <div data-animate="fade-up">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
                <h3 style="font-size: 1.45rem; font-weight: 800; color: var(--text-dark); margin: 0;">Question Breakdown</h3>
                <span class="badge badge-secondary"><?= count($answers) ?> Questions Evaluated</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <?php foreach ($answers as $idx => $ans): 
                    $isCorrect = !empty($ans['is_correct']);
                ?>
                    <div class="card" style="padding: 24px 28px; border-left: 5px solid <?= $isCorrect ? 'var(--success)' : 'var(--danger)' ?>; border-radius: var(--radius-lg);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="badge <?= $isCorrect ? 'badge-success' : 'badge-danger' ?>">
                                <?= $isCorrect ? 'Correct (+10 pts)' : 'Incorrect (0 pts)' ?>
                            </span>
                            <span style="font-size: 12px; font-weight: 600; color: var(--text-muted);">QUESTION <?= $idx + 1 ?></span>
                        </div>

                        <div style="font-weight: 700; font-size: 16px; color: var(--text-dark); margin-bottom: 16px; line-height: 1.5;">
                            <?= Sanitizer::e($ans['question_text']) ?>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13.5px; margin-bottom: 16px;">
                            <div style="padding: 12px 14px; background: #F8FAFC; border-radius: var(--radius-md); border: 1px solid var(--border);">
                                <span style="color: var(--text-muted); display: block; font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Your Selection</span>
                                <strong style="color: var(--text-dark);">Option <?= Sanitizer::e($ans['selected_option'] ?? 'None') ?></strong>
                            </div>
                            <div style="padding: 12px 14px; background: #F0FDF4; border-radius: var(--radius-md); border: 1px solid #BBF7D0;">
                                <span style="color: #15803D; display: block; font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 2px;">Correct Option</span>
                                <strong style="color: #166534;">Option <?= Sanitizer::e($ans['correct_option']) ?></strong>
                            </div>
                        </div>

                        <?php if (!empty($ans['explanation'])): ?>
                            <div style="background: #F8FAFC; padding: 14px 18px; border-radius: var(--radius-md); font-size: 13px; color: var(--text-muted); border-left: 3px solid var(--primary); line-height: 1.6;">
                                <strong style="color: var(--text-dark);">Explanation:</strong> <?= Sanitizer::e($ans['explanation']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
