<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\CertificateRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$certRepo = new CertificateRepository();
$settingRepo = new SettingRepository();
$pdo = getDbConnection();

// Handle threshold update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::isSuperAdmin()) {
    Csrf::checkOrAbort();
    
    $silver = (float)$_POST['silver_min'];
    $bronze = (float)$_POST['bronze_min'];
    $starter = (float)$_POST['starter_min'];

    $pdo->prepare("UPDATE achievement_badges SET min_score = ? WHERE badge_key = 'silver_badge'")->execute([$silver]);
    $pdo->prepare("UPDATE achievement_badges SET min_score = ? WHERE badge_key = 'bronze_badge'")->execute([$bronze]);
    $pdo->prepare("UPDATE achievement_badges SET min_score = ? WHERE badge_key = 'starter_badge'")->execute([$starter]);

    $settingRepo->set('silver_badge_threshold', (string)$silver);
    $settingRepo->set('bronze_badge_threshold', (string)$bronze);
    $settingRepo->set('starter_badge_threshold', (string)$starter);

    $settingRepo->logAudit(Auth::id(), 'BADGE_THRESHOLDS_UPDATE', 'achievement_badges', null, "Updated thresholds: Silver={$silver}, Bronze={$bronze}, Starter={$starter}");
    $_SESSION['flash_success'] = 'Badge score thresholds updated successfully.';
    header('Location: ' . baseUrl('admin/badges.php'));
    exit;
}

$badges = $certRepo->getAllBadges();
$awarded = $pdo->query("
    SELECT sa.*, b.title as badge_title, b.badge_tier, u.full_name as student_name, u.email as student_email, c.title as course_title
    FROM student_achievements sa
    JOIN achievement_badges b ON b.badge_key = sa.badge_key
    JOIN users u ON u.id = sa.user_id
    JOIN courses c ON c.id = sa.course_id
    ORDER BY sa.id DESC
    LIMIT 30
")->fetchAll();

$pageTitle = 'Manage Performance Badges';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Performance Badges</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Configure tiered recognition badges awarded for 40%–69.99% assessment achievements.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 28px;">
    <!-- Thresholds Configuration Form -->
    <div class="card" style="padding: 24px;">
        <h3 style="font-size: 1.15rem; margin-bottom: 16px;">Score Thresholds</h3>

        <form method="POST" action="<?= baseUrl('admin/badges.php') ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label" for="silver_min">🥈 Silver Badge Min %</label>
                <input type="number" id="silver_min" name="silver_min" class="form-control" value="60.00" step="0.1" min="1" max="100" <?= !Auth::isSuperAdmin() ? 'disabled' : '' ?>>
                <span style="font-size: 11px; color: var(--muted);">Threshold for Silver Proficiency Badge (default: 60.0%)</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="bronze_min">🥉 Bronze Badge Min %</label>
                <input type="number" id="bronze_min" name="bronze_min" class="form-control" value="50.00" step="0.1" min="1" max="100" <?= !Auth::isSuperAdmin() ? 'disabled' : '' ?>>
                <span style="font-size: 11px; color: var(--muted);">Threshold for Bronze Competency Badge (default: 50.0%)</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="starter_min">🎖️ Starter Badge Min %</label>
                <input type="number" id="starter_min" name="starter_min" class="form-control" value="40.00" step="0.1" min="1" max="100" <?= !Auth::isSuperAdmin() ? 'disabled' : '' ?>>
                <span style="font-size: 11px; color: var(--muted);">Threshold for Foundation Starter Badge (default: 40.0%)</span>
            </div>

            <?php if (Auth::isSuperAdmin()): ?>
                <button type="submit" class="btn btn-primary btn-sm">
                    Save Badge Thresholds
                </button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Recent Badges Awarded Log -->
    <div class="table-card">
        <div class="card-header">
            <h3 style="font-size: 1.1rem; margin: 0;">Recent Badges Awarded</h3>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Badge Tier</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Score</th>
                        <th>Awarded</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($awarded)): ?>
                        <tr><td colspan="5" style="text-align: center; color: var(--muted);">No badges awarded yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($awarded as $aw): ?>
                            <tr>
                                <td>
                                    <span class="badge <?= ($aw['badge_tier'] === 'silver') ? 'badge-tier-silver' : (($aw['badge_tier'] === 'bronze') ? 'badge-tier-bronze' : 'badge-tier-starter') ?>">
                                        <?= Sanitizer::e($aw['badge_title']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight: 600;"><?= Sanitizer::e($aw['student_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($aw['student_email']) ?></div>
                                </td>
                                <td style="font-size: 12px;"><?= Sanitizer::e($aw['course_title']) ?></td>
                                <td><strong><?= number_format((float)$aw['score_percentage'], 1) ?>%</strong></td>
                                <td style="font-size: 12px; color: var(--muted);"><?= date('M j, Y', strtotime($aw['awarded_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
