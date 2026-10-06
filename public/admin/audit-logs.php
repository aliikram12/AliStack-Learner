<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Auth;

Auth::requireSuperAdmin();

$settingRepo = new SettingRepository();
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$totalLogs = $settingRepo->countAuditLogs();
$totalPages = max(1, (int)ceil($totalLogs / $perPage));
$logs = $settingRepo->getAuditLogs($page, $perPage);

$pageTitle = 'Security & System Audit Logs';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">System Audit Logs</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Immutable security audit trail recording privileged administrator operations and security events.</p>
    </div>
    <div style="font-size: 13px; color: var(--muted); background: var(--bg-main); padding: 8px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
        <i class="bi bi-shield-check" style="color: var(--success); margin-right: 4px;"></i> Total Logged Events: <strong><?= number_format($totalLogs) ?></strong>
    </div>
</div>

<div class="admin-card">
    <div style="padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
        <h2 style="font-size: 15px; font-weight: 700; margin: 0;">Activity Timeline</h2>
        <span style="font-size: 12px; color: var(--muted);">Page <?= $page ?> of <?= $totalPages ?></span>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 70px;">ID</th>
                    <th>Timestamp (UTC)</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Details</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 48px; color: var(--muted);">
                            <i class="bi bi-journal-text" style="font-size: 2rem; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                            No audit log events recorded yet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td style="color: var(--muted); font-family: monospace; font-size: 12px;">#<?= $log['id'] ?></td>
                            <td style="font-size: 12px; white-space: nowrap; color: var(--text-dark);">
                                <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                            </td>
                            <td>
                                <?php if (!empty($log['user_name'])): ?>
                                    <div style="font-weight: 600; font-size: 13px;"><?= Sanitizer::e($log['user_name']) ?></div>
                                    <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($log['user_email']) ?> (<?= Sanitizer::e($log['user_role'] ?? 'user') ?>)</div>
                                <?php else: ?>
                                    <span style="color: var(--muted); font-size: 12px; font-style: italic;">System / Guest</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badgeClass = 'badge-primary';
                                $act = strtoupper($log['action'] ?? '');
                                if (str_contains($act, 'DELETE') || str_contains($act, 'REVOKE') || str_contains($act, 'SUSPEND')) {
                                    $badgeClass = 'badge-error';
                                } elseif (str_contains($act, 'CREATE') || str_contains($act, 'APPROVE') || str_contains($act, 'ISSUE')) {
                                    $badgeClass = 'badge-success';
                                } elseif (str_contains($act, 'UPDATE') || str_contains($act, 'EDIT')) {
                                    $badgeClass = 'badge-warning';
                                }
                                ?>
                                <span class="badge <?= $badgeClass ?>" style="font-family: monospace; font-size: 11px;">
                                    <?= Sanitizer::e($log['action']) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--text-dark);">
                                <?php if (!empty($log['entity_type'])): ?>
                                    <strong><?= Sanitizer::e($log['entity_type']) ?></strong>
                                    <?php if (!empty($log['entity_id'])): ?>
                                        <span style="color: var(--muted); font-size: 11px;">#<?= (int)$log['entity_id'] ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: var(--muted);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size: 12px; max-width: 320px; word-break: break-word; color: #334155;">
                                <?= Sanitizer::e($log['details'] ?? '-') ?>
                            </td>
                            <td style="font-size: 11px; font-family: monospace; color: var(--muted); white-space: nowrap;">
                                <?= Sanitizer::e($log['ip_address'] ?? '127.0.0.1') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div style="padding: 16px 24px; display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--border);">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                    <i class="bi bi-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px;">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
