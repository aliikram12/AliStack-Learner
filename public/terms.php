<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'Terms of Service';
$pageDesc = 'Terms and conditions governing the use of the AliStack Learner platform.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 56px 0;">
    <div class="container" style="max-width: 860px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 12px;">Terms of Service</h1>
        <p style="color: var(--text-muted); font-size: 14px;">Last Updated: October 2026</p>
    </div>
</div>

<div class="container" style="max-width: 860px; padding: 56px 24px; line-height: 1.8; font-size: 15px;">
    <h3 style="margin-bottom: 12px;">1. Acceptance of Terms</h3>
    <p style="color: var(--text-main); margin-bottom: 24px;">
        By accessing AliStack Learner, registering an account, or taking assessments, you agree to these Terms of Service. If you do not agree, you must discontinue platform use.
    </p>

    <h3 style="margin-bottom: 12px;">2. Academic Integrity</h3>
    <p style="color: var(--text-main); margin-bottom: 24px;">
        All assessment attempts must reflect your personal knowledge and effort. Attempting to extract answer keys, circumvent timers, or share test questions in discussion groups is strictly prohibited and subject to account suspension and certificate revocation.
    </p>

    <h3 style="margin-bottom: 12px;">3. Third-Party Media Compliance</h3>
    <p style="color: var(--text-main); margin-bottom: 24px;">
        Videos are embedded from YouTube under standard embedding policies. AliStack Learner makes no ownership claim over third-party videos and does not bypass platform restrictions.
    </p>

    <h3 style="margin-bottom: 12px;">4. Certificates & Accreditations</h3>
    <p style="color: var(--text-main); margin-bottom: 24px;">
        AliStack certificates represent verified performance on AliStack Learner assessments. Unless explicitly stated, certificates are internal certifications from AliStack Academy and do not represent external university degrees.
    </p>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
