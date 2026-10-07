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
$pageDesc = 'Review and download your officially verified AliStack course certificates and performance badges.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 48px 0;">
    <div class="container">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;">Achievements</span>
        </div>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 800; color: var(--text-dark); margin: 0 0 8px;">
                    Certificates & Performance Badges
                </h1>
                <p style="color: var(--text-muted); font-size: 15px; margin: 0;">
                    Your verified proof of completed courses and technical proficiency.
                </p>
            </div>
            <div style="display: flex; gap: 12px;">
                <a href="<?= baseUrl('verify-certificate.php') ?>" class="btn btn-outline btn-sm">
                    <i class="bi bi-shield-check"></i> Verify Any Certificate
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding: 48px 20px 88px;">
    <!-- Certificates Section -->
    <div style="margin-bottom: 64px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: var(--radius-sm); background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--text-dark); margin: 0;">Verified Course Certificates</h2>
            </div>
            <span class="badge badge-success" style="font-size: 12px; font-weight: 700;"><?= count($certificates) ?> Earned</span>
        </div>

        <?php if (empty($certificates)): ?>
            <div class="empty-state" data-animate="fade-up">
                <div class="empty-state-icon"><i class="bi bi-award"></i></div>
                <div class="empty-state-title">No Certificates Earned Yet</div>
                <div class="empty-state-desc">
                    Complete all lessons of a course and score 70% or higher on its final assessment to earn an official verified AliStack Certificate.
                </div>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary" style="margin-top: 16px;">
                    <i class="bi bi-compass"></i> Explore Courses
                </a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 24px;">
                <?php foreach ($certificates as $cert): ?>
                    <div class="card" data-animate="fade-up" style="padding: 28px; border: 1px solid var(--border); border-radius: var(--radius-xl); border-top: 4px solid var(--success); box-shadow: var(--shadow-sm); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px;">
                                <span class="badge badge-success"><i class="bi bi-check2-circle"></i> Passed with <?= number_format((float)$cert['score_percentage'], 1) ?>%</span>
                                <span style="font-size: 12px; color: var(--text-muted);"><?= date('M j, Y', strtotime($cert['issued_at'])) ?></span>
                            </div>

                            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--text-dark); margin-bottom: 8px; line-height: 1.4;">
                                <?= Sanitizer::e($cert['course_title']) ?>
                            </h3>

                            <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px; font-size: 12px; color: var(--text-muted); margin-bottom: 24px;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>Certificate Serial:</span>
                                    <strong style="color: var(--text-dark); font-family: monospace; font-size: 12.5px;"><?= Sanitizer::e($cert['certificate_number']) ?></strong>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <a href="<?= baseUrl('certificates.php?view=' . urlencode($cert['verification_code'])) ?>" target="_blank" class="btn btn-primary btn-sm" style="flex: 1;">
                                <i class="bi bi-printer"></i> View & Print PDF
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
    <div id="badges" style="scroll-margin-top: 80px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: var(--radius-sm); background: #F5F3FF; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <h2 style="font-size: 1.45rem; font-weight: 800; color: var(--text-dark); margin: 0;">Performance Badges Wall</h2>
            </div>
            <span class="badge badge-secondary" style="font-size: 12px; font-weight: 700;"><?= count($achievements) ?> Awarded</span>
        </div>

        <?php if (empty($achievements)): ?>
            <div class="empty-state" data-animate="fade-up">
                <div class="empty-state-icon"><i class="bi bi-shield-slash"></i></div>
                <div class="empty-state-title">No Performance Badges Yet</div>
                <div class="empty-state-desc">
                    Score between 40% and 69.99% on any course assessment to earn Silver Proficiency, Bronze Competency, or Foundation Starter badges.
                </div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
                <?php foreach ($achievements as $ach): ?>
                    <div class="card card-hover" data-animate="fade-up" style="padding: 28px 24px; text-align: center; border: 1px solid var(--border); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);">
                        <div style="font-size: 48px; margin-bottom: 12px; line-height: 1;">
                            <?php if ($ach['badge_tier'] === 'silver'): ?>
                                🥈
                            <?php elseif ($ach['badge_tier'] === 'bronze'): ?>
                                🥉
                            <?php else: ?>
                                🎖️
                            <?php endif; ?>
                        </div>

                        <h4 style="font-size: 1.1rem; font-weight: 800; color: var(--text-dark); margin: 0 0 4px;">
                            <?= Sanitizer::e($ach['badge_title']) ?>
                        </h4>
                        <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 14px;">
                            <?= Sanitizer::e($ach['course_title']) ?>
                        </div>

                        <span class="badge <?= ($ach['badge_tier'] === 'silver') ? 'badge-tier-silver' : (($ach['badge_tier'] === 'bronze') ? 'badge-tier-bronze' : 'badge-tier-starter') ?>">
                            Score: <?= number_format((float)$ach['score_percentage'], 1) ?>%
                        </span>

                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 16px;">
                            Awarded on <?= date('M j, Y', strtotime($ach['awarded_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
