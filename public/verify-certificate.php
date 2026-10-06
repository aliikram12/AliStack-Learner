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
$pageDesc = 'Authenticate and verify official AliStack Learner course certificates and achievement credentials.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container" style="max-width: 760px; text-align: center;">
        <span class="badge badge-success" style="margin-bottom: 12px;"><i class="bi bi-shield-check"></i> Credential Authentication</span>
        <h1 style="font-size: 2.5rem; margin-bottom: 12px;">Certificate Verification</h1>
        <p style="color: var(--muted); font-size: 15px;">
            Enter an official AliStack certificate number or verification hash to validate its authenticity.
        </p>

        <!-- Search Box -->
        <form method="GET" action="<?= baseUrl('verify-certificate.php') ?>" style="margin-top: 32px; display: flex; gap: 12px;">
            <input type="text" name="code" value="<?= Sanitizer::e($query) ?>" placeholder="e.g. ALISTACK-CERT-2026-ABCD1234 or hash code" class="form-control" style="font-size: 15px; padding: 12px 16px;" required>
            <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                <i class="bi bi-search"></i> Verify
            </button>
        </form>
    </div>
</div>

<div class="container" style="max-width: 800px; padding: 56px 24px;">
    <?php if ($searched): ?>
        <?php if ($cert): ?>
            <div class="card" style="padding: 36px; border: 2px solid <?= ($cert['status'] === 'valid') ? '#86EFAC' : '#FCA5A5' ?>; box-shadow: var(--shadow-xl);">
                <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= ($cert['status'] === 'valid') ? '#DCFCE7' : '#FEE2E2' ?>; color: <?= ($cert['status'] === 'valid') ? '#16A34A' : '#DC2626' ?>; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="bi <?= ($cert['status'] === 'valid') ? 'bi-patch-check-fill' : 'bi-x-circle-fill' ?>"></i>
                        </div>
                        <div>
                            <h3 style="margin: 0; font-size: 18px;">
                                <?= ($cert['status'] === 'valid') ? 'Officially Verified Credential' : 'Certificate Revoked' ?>
                            </h3>
                            <div style="font-size: 13px; color: var(--muted);">Certificate ID: <strong><?= Sanitizer::e($cert['certificate_number']) ?></strong></div>
                        </div>
                    </div>
                    <span class="badge <?= ($cert['status'] === 'valid') ? 'badge-success' : 'badge-danger' ?>" style="font-size: 13px; padding: 6px 14px;">
                        Status: <?= strtoupper(Sanitizer::e($cert['status'])) ?>
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 32px;">
                    <div>
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 600;">Student Name</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--dark); margin-top: 4px;"><?= Sanitizer::e($cert['student_name']) ?></div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 600;">Course Title</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--dark); margin-top: 4px;"><?= Sanitizer::e($cert['course_title']) ?></div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 600;">Verified Final Score</div>
                        <div style="font-size: 18px; font-weight: 700; color: var(--primary); margin-top: 4px;">
                            <?= number_format((float)$cert['score_percentage'], 1) ?>%
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 600;">Issue Date</div>
                        <div style="font-size: 16px; font-weight: 600; color: var(--dark); margin-top: 4px;">
                            <?= date('F j, Y', strtotime($cert['issued_at'])) ?>
                        </div>
                    </div>
                </div>

                <div style="background: #F8FAFC; border-radius: var(--radius-md); padding: 16px; display: flex; align-items: center; justify-content: space-between; font-size: 13px;">
                    <div style="color: var(--muted);">
                        <i class="bi bi-shield-lock-fill" style="color: var(--primary);"></i> Authenticated by <strong>AliStack Academic Registry</strong>
                    </div>
                    <?php if ($cert['status'] === 'valid'): ?>
                        <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-outline btn-sm">
                            <i class="bi bi-eye"></i> View Full Certificate
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 48px 24px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                <div style="font-size: 48px; color: var(--error); margin-bottom: 16px;"><i class="bi bi-exclamation-octagon"></i></div>
                <h3 style="margin-bottom: 8px;">Certificate Not Found</h3>
                <p style="color: var(--muted); max-width: 480px; margin: 0 auto;">
                    No certificate was found matching the identifier "<strong><?= Sanitizer::e($query) ?></strong>". Please ensure the certificate number or link was copied correctly.
                </p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div style="text-align: center; color: var(--muted); font-size: 14px;">
            <p>AliStack certificates are issued only to students achieving 70.0% or higher on supervised, server-graded course assessments.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
