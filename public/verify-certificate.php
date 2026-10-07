<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Services\CertificateService;
use App\Helpers\Sanitizer;

$query = trim((string)($_GET['code'] ?? $_GET['cert_number'] ?? $_GET['q'] ?? ''));
$cert = null;
$searched = false;

if (!empty($query)) {
    $searched = true;
    $service = new CertificateService();
    $cert = $service->verifyCertificate($query);
}

$pageTitle = 'Verify Certificate';
$pageDesc = 'Authenticate and verify official AliStack Learner course certificates and achievement credentials in real-time.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.06) 0%, rgba(248,250,252,1) 80%); border-bottom: 1px solid var(--border-color); padding: 64px 0;">
    <div class="container" style="max-width: 760px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #DCFCE7; color: #16A34A; font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; margin-bottom: 14px; text-transform: uppercase;">
            <i class="bi bi-shield-check"></i> Academic Registry
        </div>
        <h1 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; letter-spacing: -0.02em; margin-bottom: 14px; color: var(--dark);">
            Certificate Verification
        </h1>
        <p style="color: var(--muted); font-size: 16px; line-height: 1.6; margin: 0 auto; max-width: 600px;">
            Enter an official AliStack certificate serial number or verification code to validate authentic academic achievement.
        </p>

        <!-- Search Box -->
        <form method="GET" action="<?= baseUrl('verify-certificate.php') ?>" style="margin-top: 36px; display: flex; gap: 12px; max-width: 620px; margin-left: auto; margin-right: auto; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 260px; position: relative;">
                <i class="bi bi-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                <input type="text" name="code" value="<?= Sanitizer::e($query) ?>" placeholder="e.g. ALISTACK-CERT-2026-ABCD1234 or verification hash" class="form-control" style="font-size: 15px; padding: 13px 16px 13px 44px; border-radius: var(--radius-md); box-shadow: 0 2px 8px rgba(0,0,0,0.04);" required>
            </div>
            <button type="submit" class="btn btn-primary" style="padding: 13px 28px; font-weight: 700; border-radius: var(--radius-md); box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                <i class="bi bi-patch-check"></i> Verify
            </button>
        </form>
    </div>
</div>

<div class="container" style="max-width: 820px; padding: 56px 20px 88px;">
    <?php if ($searched): ?>
        <?php if ($cert): ?>
            <div class="card" data-animate="fade-up" style="padding: 40px; border: 2px solid <?= ($cert['status'] === 'valid') ? '#86EFAC' : '#FCA5A5' ?>; border-radius: var(--radius-xl); box-shadow: var(--shadow-xl); background: #FFFFFF;">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 24px; margin-bottom: 28px; flex-wrap: wrap; gap: 16px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 52px; height: 52px; border-radius: 50%; background: <?= ($cert['status'] === 'valid') ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= ($cert['status'] === 'valid') ? '#16A34A' : '#DC2626' ?>; display: flex; align-items: center; justify-content: center; font-size: 26px;">
                            <i class="bi <?= ($cert['status'] === 'valid') ? 'bi-patch-check-fill' : 'bi-x-circle-fill' ?>"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 1.3rem; font-weight: 800; color: var(--dark);">
                                <?= ($cert['status'] === 'valid') ? 'Officially Verified Credential' : 'Certificate Revoked' ?>
                            </h3>
                            <div style="font-size: 13px; color: var(--muted); margin-top: 2px;">
                                Certificate Serial: <strong style="font-family: monospace; color: var(--dark);"><?= Sanitizer::e($cert['certificate_number']) ?></strong>
                            </div>
                        </div>
                    </div>
                    <span class="badge <?= ($cert['status'] === 'valid') ? 'badge-success' : 'badge-danger' ?>" style="font-size: 13px; padding: 6px 14px; font-weight: 700;">
                        Status: <?= strtoupper(Sanitizer::e($cert['status'])) ?>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; margin-bottom: 36px;">
                    <div style="background: var(--bg-main); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Student Name</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--dark); margin-top: 4px;"><?= Sanitizer::e($cert['student_name']) ?></div>
                    </div>

                    <div style="background: var(--bg-main); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Course Title</div>
                        <div style="font-size: 18px; font-weight: 800; color: var(--dark); margin-top: 4px;"><?= Sanitizer::e($cert['course_title']) ?></div>
                    </div>

                    <div style="background: var(--bg-main); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Verified Final Score</div>
                        <div style="font-size: 20px; font-weight: 800; color: var(--primary); margin-top: 4px;">
                            <?= number_format((float)$cert['score_percentage'], 1) ?>%
                        </div>
                    </div>

                    <div style="background: var(--bg-main); padding: 18px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Issue Date</div>
                        <div style="font-size: 17px; font-weight: 700; color: var(--dark); margin-top: 4px;">
                            <?= date('F j, Y', strtotime($cert['issued_at'])) ?>
                        </div>
                    </div>
                </div>

                <div style="background: #F8FAFC; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
                    <div style="color: var(--muted); font-size: 13.5px; display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-shield-lock-fill" style="color: var(--primary); font-size: 16px;"></i>
                        <span>Authenticated by <strong>AliStack Academic Registry</strong></span>
                    </div>
                    <?php if ($cert['status'] === 'valid'): ?>
                        <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-outline btn-sm">
                            <i class="bi bi-eye"></i> View Full Certificate
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-state" data-animate="fade-up" style="background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-xl); padding: 48px 24px;">
                <div class="empty-icon" style="color: var(--danger);"><i class="bi bi-exclamation-octagon"></i></div>
                <div class="empty-title">Certificate Not Found</div>
                <div class="empty-desc" style="max-width: 500px; margin: 0 auto;">
                    No certificate was found matching the identifier "<strong><?= Sanitizer::e($query) ?></strong>". Please ensure the complete certificate serial number was entered without typos.
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; color: var(--muted); font-size: 14.5px; line-height: 1.6; max-width: 600px; margin: 0 auto;">
            <p>AliStack course certificates are awarded exclusively to learners who achieve 70.0% or higher on supervised, server-graded final assessments. Each certificate contains a cryptographically generated serial code registered in the AliStack database.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
