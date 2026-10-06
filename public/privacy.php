<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'Privacy Policy';
$pageDesc = 'Learn how AliStack Learner protects your personal data, progress history, and privacy.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container" style="max-width: 860px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 12px;">Privacy Policy</h1>
        <p style="color: var(--muted); font-size: 14px;">Effective Date: October 2026</p>
    </div>
</div>

<div class="container" style="max-width: 860px; padding: 56px 24px; line-height: 1.8; font-size: 15px;">
    <h3 style="margin-bottom: 12px;">1. Information We Collect</h3>
    <p style="color: var(--dark-light); margin-bottom: 24px;">
        When you create an account on AliStack Learner, we collect your full name, email address, username, and encrypted password hash. As you use the platform, we store your course enrollments, playback progress timestamps, personal lesson notes, bookmarks, assessment attempts, and issued certificates.
    </p>

    <h3 style="margin-bottom: 12px;">2. AI Tutor Data Processing</h3>
    <p style="color: var(--dark-light); margin-bottom: 24px;">
        Queries sent to the AliStack AI Tutor are processed server-side through our configured AgentRouter gateway. Contextual lesson titles and student notes may be forwarded to answer queries accurately. We do not sell your learning transcripts to external advertisers.
    </p>

    <h3 style="margin-bottom: 12px;">3. YouTube Integration</h3>
    <p style="color: var(--dark-light); margin-bottom: 24px;">
        AliStack Learner utilizes the official YouTube IFrame Player API. Interacting with the embedded player complies with the YouTube Terms of Service and Google Privacy Policy. We do not download or rehost YouTube content.
    </p>

    <h3 style="margin-bottom: 12px;">4. Public Certificate Verification</h3>
    <p style="color: var(--dark-light); margin-bottom: 24px;">
        Certificates issued through AliStack Learner contain a unique public verification URL. This page confirms only the recipient name, course title, final score percentage, and issue date to verify credentials to employers or academic institutions.
    </p>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
