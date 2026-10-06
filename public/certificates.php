<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Services\CertificateService;
use App\Repositories\CertificateRepository;

$viewCode = trim((string)($_GET['view'] ?? ''));

// If a specific certificate code is requested for viewing/printing
if (!empty($viewCode)) {
    $service = new CertificateService();
    $cert = $service->verifyCertificate($viewCode);
    if (!$cert) {
        http_response_code(404);
        die("Certificate not found.");
    }
    // Render print-ready certificate HTML directly
    echo $service->renderCertificateHtml($cert);
    exit;
}

// Student Gallery View requires login
Auth::requireAuth();

$userId = (int)Auth::id();
$certRepo = new CertificateRepository();
$certificates = $certRepo->getUserCertificates($userId);
$achievements = $certRepo->getUserAchievements($userId);

$pageTitle = 'My Certificates & Achievements';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 48px 0;">
    <div class="container">
        <h1 style="font-size: 2.25rem; margin-bottom: 8px;">My Certificates & Badges</h1>
        <p style="color: var(--muted); font-size: 15px; margin: 0;">
            Review your officially earned AliStack course certificates and performance badges.
        </p>
    </div>
</div>

<div class="container" style="padding: 48px 24px 80px;">
    <!-- Certificates Section -->
    <div style="margin-bottom: 48px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h2 style="font-size: 1.6rem;"><i class="bi bi-award-fill" style="color: var(--success);"></i> Verified Course Certificates</h2>
            <span class="badge badge-success"><?= count($certificates) ?> Earned</span>
        </div>

        <?php if (empty($certificates)): ?>
            <div style="text-align: center; padding: 48px 24px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                <i class="bi bi-patch-check" style="font-size: 48px; color: var(--muted-light); margin-bottom: 12px; display: block;"></i>
                <h3 style="margin-bottom: 8px;">No Certificates Earned Yet</h3>
                <p style="color: var(--muted); max-width: 480px; margin: 0 auto 20px;">
                    Complete all lessons of an enrolled course and pass the final assessment with a score of 70% or higher to earn an official AliStack Certificate.
                </p>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-sm">Explore Courses</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 24px;">
                <?php foreach ($certificates as $cert): ?>
                    <div class="card" style="padding: 24px; border-top: 4px solid var(--success);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <span class="badge badge-success"><i class="bi bi-check2"></i> Verified 70%+</span>
                            <span style="font-size: 11px; color: var(--muted);"><?= date('M j, Y', strtotime($cert['issued_at'])) ?></span>
                        </div>

                        <h3 style="font-size: 1.2rem; margin-bottom: 6px; color: var(--dark);">
                            <?= Sanitizer::e($cert['course_title']) ?>
                        </h3>
                        <div style="font-size: 13px; color: var(--primary); font-weight: 700; margin-bottom: 16px;">
                            Score: <?= number_format((float)$cert['score_percentage'], 1) ?>%
                        </div>

                        <div style="background: #F8FAFC; border-radius: var(--radius-sm); padding: 10px; font-size: 12px; color: var(--muted); margin-bottom: 20px;">
                            <strong>ID:</strong> <?= Sanitizer::e($cert['certificate_number']) ?>
                        </div>

                        <div style="display: flex; gap: 8px;">
                            <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-primary btn-sm" style="flex: 1;">
                                <i class="bi bi-printer"></i> View / Print PDF
                            </a>
                            <a href="<?= baseUrl('verify-certificate.php?code=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-outline btn-sm">
                                <i class="bi bi-patch-check"></i> Verify
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Performance Badges Section -->
    <div>
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h2 style="font-size: 1.6rem;"><i class="bi bi-patch-check-fill" style="color: var(--secondary);"></i> Performance Badges Gallery</h2>
            <span class="badge badge-secondary"><?= count($achievements) ?> Awarded</span>
        </div>

        <?php if (empty($achievements)): ?>
            <div style="text-align: center; padding: 48px 24px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                <i class="bi bi-shield-check" style="font-size: 48px; color: var(--muted-light); margin-bottom: 12px; display: block;"></i>
                <h3 style="margin-bottom: 8px;">No Badges Earned Yet</h3>
                <p style="color: var(--muted); max-width: 480px; margin: 0 auto;">
                    Score between 40% and 69.99% on any course assessment to earn Silver, Bronze, or Foundation Starter badges.
                </p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                <?php foreach ($achievements as $ach): ?>
                    <div class="card" style="padding: 24px; text-align: center;">
                        <div style="font-size: 48px; margin-bottom: 12px;">
                            <?php if ($ach['badge_tier'] === 'silver'): ?>
                                🥈
                            <?php elseif ($ach['badge_tier'] === 'bronze'): ?>
                                🥉
                            <?php else: ?>
                                🎖️
                            <?php endif; ?>
                        </div>

                        <h4 style="margin-bottom: 6px; font-size: 1.1rem;"><?= Sanitizer::e($ach['badge_title']) ?></h4>
                        <div style="font-size: 13px; color: var(--muted); margin-bottom: 12px;"><?= Sanitizer::e($ach['course_title']) ?></div>

                        <span class="badge <?= ($ach['badge_tier'] === 'silver') ? 'badge-tier-silver' : (($ach['badge_tier'] === 'bronze') ? 'badge-tier-bronze' : 'badge-tier-starter') ?>">
                            Score: <?= number_format((float)$ach['score_percentage'], 1) ?>%
                        </span>

                        <div style="font-size: 11px; color: var(--muted-light); margin-top: 14px;">
                            Awarded on <?= date('M j, Y', strtotime($ach['awarded_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
