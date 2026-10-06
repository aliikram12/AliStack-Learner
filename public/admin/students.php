<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\UserRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$userRepo = new UserRepository();
$settingRepo = new SettingRepository();

// Handle status or role update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';
    $targetUserId = (int)($_POST['target_user_id'] ?? 0);

    if ($action === 'toggle_status' && $targetUserId) {
        $u = $userRepo->findById($targetUserId);
        if ($u) {
            $newStatus = ($u['status'] === 'active') ? 'suspended' : 'active';
            $userRepo->update($targetUserId, ['status' => $newStatus]);
            $settingRepo->logAudit(Auth::id(), 'USER_STATUS_CHANGE', 'users', $targetUserId, "Changed status of {$u['email']} to {$newStatus}");
            $_SESSION['flash_success'] = "User status updated to {$newStatus}.";
        }
    } elseif ($action === 'change_role' && $targetUserId && Auth::isSuperAdmin()) {
        $newRole = $_POST['new_role'] ?? 'student';
        if (in_array($newRole, ['super_admin', 'admin', 'moderator', 'student'], true)) {
            $userRepo->update($targetUserId, ['role' => $newRole]);
            $settingRepo->logAudit(Auth::id(), 'USER_ROLE_CHANGE', 'users', $targetUserId, "Changed role to {$newRole}");
            $_SESSION['flash_success'] = "User role updated to {$newRole}.";
        }
    }
    header('Location: ' . baseUrl('admin/students.php'));
    exit;
}

$page = max(1, (int)($_GET['page'] ?? 1));
$role = !empty($_GET['role']) ? (string)$_GET['role'] : null;
$search = !empty($_GET['q']) ? (string)$_GET['q'] : null;

$users = $userRepo->getAll($page, 20, $role, $search);
$total = $userRepo->countAll($role, $search);

$pageTitle = 'Manage Users & Roles';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Users & Role Management</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Manage student accounts, moderators, administrators, and account statuses.</p>
    </div>
</div>

<div class="table-card">
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); display: flex; gap: 12px; align-items: center; justify-content: space-between;">
        <form method="GET" action="<?= baseUrl('admin/students.php') ?>" style="display: flex; gap: 10px; align-items: center;">
            <input type="text" name="q" value="<?= Sanitizer::e($search ?? '') ?>" placeholder="Search by name, email, or username..." class="form-control" style="width: 260px; font-size: 13px; padding: 6px 12px;">
            <select name="role" class="form-select" style="width: 140px; font-size: 13px; padding: 6px 12px;" onchange="this.form.submit()">
                <option value="">All Roles</option>
                <option value="student" <?= ($role === 'student') ? 'selected' : '' ?>>Students</option>
                <option value="moderator" <?= ($role === 'moderator') ? 'selected' : '' ?>>Moderators</option>
                <option value="admin" <?= ($role === 'admin') ? 'selected' : '' ?>>Admins</option>
                <option value="super_admin" <?= ($role === 'super_admin') ? 'selected' : '' ?>>Super Admins</option>
            </select>
            <button type="submit" class="btn btn-outline btn-sm">Filter</button>
        </form>

        <span style="font-size: 12px; color: var(--muted);"><?= $total ?> total users</span>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User Profile</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Active</th>
                    <th>Registered</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--muted);">No users found.</td></tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 700; color: var(--dark);"><?= Sanitizer::e($u['full_name']) ?></div>
                                <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::e($u['email']) ?> &bull; @<?= Sanitizer::e($u['username']) ?></div>
                            </td>
                            <td>
                                <?php if (Auth::isSuperAdmin() && (int)$u['id'] !== Auth::id()): ?>
                                    <form method="POST" action="<?= baseUrl('admin/students.php') ?>" style="margin: 0;">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="action" value="change_role">
                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                        <select name="new_role" class="form-select" style="font-size: 12px; padding: 4px 8px; width: 120px;" onchange="this.form.submit()">
                                            <option value="student" <?= ($u['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                                            <option value="moderator" <?= ($u['role'] === 'moderator') ? 'selected' : '' ?>>Moderator</option>
                                            <option value="admin" <?= ($u['role'] === 'admin') ? 'selected' : '' ?>>Admin</option>
                                            <option value="super_admin" <?= ($u['role'] === 'super_admin') ? 'selected' : '' ?>>Super Admin</option>
                                        </select>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-gray"><?= strtoupper(Sanitizer::e($u['role'])) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= ($u['status'] === 'active') ? 'badge-success' : 'badge-danger' ?>">
                                    <?= strtoupper(Sanitizer::e($u['status'])) ?>
                                </span>
                            </td>
                            <td style="font-size: 12px; color: var(--muted);"><?= Sanitizer::timeAgo($u['last_login_at']) ?></td>
                            <td style="font-size: 12px; color: var(--muted);"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td style="text-align: right;">
                                <?php if ((int)$u['id'] !== Auth::id()): ?>
                                    <form method="POST" action="<?= baseUrl('admin/students.php') ?>" style="display: inline;">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-outline btn-sm" style="font-size: 11px;">
                                            <?= ($u['status'] === 'active') ? 'Suspend' : 'Activate' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
