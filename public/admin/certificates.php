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

// Revoke or reinstate certificate
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $certId = (int)($_POST['cert_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($certId && in_array($action, ['revoke', 'reinstate'], true)) {
        $newStatus = ($action === 'revoke') ? 'revoked' : 'valid';
        $stmt = $pdo->prepare("UPDATE certificates SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $certId]);

        $settingRepo->logAudit(Auth::id(), 'CERTIFICATE_STATUS_CHANGE', 'certificates', $certId, "Changed certificate {$certId} to {$newStatus}");
        $_SESSION['flash_success'] = "Certificate status updated to {$newStatus}.";
        header('Location: ' . baseUrl('admin/certificates.php'));
        exit;
    }
}

$page = max(1, (int)($_GET['page'] ?? 1));
$certificates = $certRepo->getAllAdmin($page, 20);
$total = $certRepo->countAll();

$pageTitle = 'Manage Certificates';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Verified Certificates Registry</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Audit issued certificates, check verification codes, and revoke invalid credentials.</p>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Certificate ID</th>
                    <th>Student Name</th>
                    <th>Course</th>
                    <th>Score</th>
                    <th>Status</th>
                    <th>Issued Date</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($certificates)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--muted);">No certificates issued yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($certificates as $c): ?>
                        <tr>
                            <td>
                                <strong style="font-family: monospace; font-size: 13px; color: var(--primary);">
                                    <?= Sanitizer::e($c['certificate_number']) ?>
                                </strong>
                            </td>
                            <td>
                                <div style="font-weight: 600;"><?= Sanitizer::e($c['student_name']) ?></div>
                                <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($c['student_email']) ?></div>
                            </td>
                            <td style="font-size: 13px;"><?= Sanitizer::e($c['course_title']) ?></td>
                            <td><strong style="color: var(--success);"><?= number_format((float)$c['score_percentage'], 1) ?>%</strong></td>
                            <td>
                                <span class="badge <?= ($c['status'] === 'valid') ? 'badge-success' : 'badge-danger' ?>">
                                    <?= strtoupper(Sanitizer::e($c['status'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--muted);"><?= date('M j, Y', strtotime($c['issued_at'])) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 6px;">
                                    <a href="<?= baseUrl('verify-certificate.php?code=' . urlencode($c['verification_code'])) ?>" target="_blank" class="btn btn-outline btn-sm" title="Verify Online">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form method="POST" action="<?= baseUrl('admin/certificates.php') ?>" style="display: inline;">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="cert_id" value="<?= $c['id'] ?>">
                                        <input type="hidden" name="action" value="<?= ($c['status'] === 'valid') ? 'revoke' : 'reinstate' ?>">
                                        <button type="submit" class="btn <?= ($c['status'] === 'valid') ? 'btn-danger' : 'btn-outline' ?> btn-sm" style="font-size: 11px;">
                                            <?= ($c['status'] === 'valid') ? 'Revoke' : 'Reinstate' ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
