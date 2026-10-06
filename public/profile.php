<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Sanitizer;
use App\Services\AuthService;
use App\Repositories\UserRepository;

Auth::requireAuth();

$user = Auth::user();
$userId = (int)$user['id'];
$userRepo = new UserRepository();
$fullUser = $userRepo->findById($userId);

$successMsg = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    $authService = new AuthService();

    if ($action === 'update_profile') {
        $file = $_FILES['avatar'] ?? null;
        $res = $authService->updateProfile($userId, $_POST, $file);
        if ($res['success']) {
            $successMsg = $res['message'];
            $fullUser = $userRepo->findById($userId);
        } else {
            $errorMsg = $res['error'];
        }
    } elseif ($action === 'change_password') {
        $currentPass = (string)($_POST['current_password'] ?? '');
        $newPass = (string)($_POST['new_password'] ?? '');
        $confirmPass = (string)($_POST['confirm_password'] ?? '');

        if (!password_verify($currentPass, $fullUser['password_hash'])) {
            $errorMsg = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 8) {
            $errorMsg = 'New password must be at least 8 characters long.';
        } elseif ($newPass !== $confirmPass) {
            $errorMsg = 'New passwords do not match.';
        } else {
            $newHash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $userRepo->updatePassword($userId, $newHash);
            $successMsg = 'Password updated successfully.';
        }
    }
}

$pageTitle = 'Profile & Settings';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 48px 0;">
    <div class="container" style="max-width: 860px;">
        <h1 style="font-size: 2.25rem; margin-bottom: 8px;">Account Settings</h1>
        <p style="color: var(--muted); font-size: 15px; margin: 0;">
            Manage your personal profile, profile image, and security credentials.
        </p>
    </div>
</div>

<div class="container" style="max-width: 860px; padding: 48px 24px 80px;">
    <?php if ($successMsg): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle-fill"></i>
            <div><?= Sanitizer::e($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div><?= Sanitizer::e($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 36px;">
        <!-- Left Forms Column -->
        <div>
            <!-- Profile Details Card -->
            <div class="card" style="padding: 28px; margin-bottom: 32px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 20px;">Profile Information</h3>

                <form method="POST" action="<?= baseUrl('profile.php') ?>" enctype="multipart/form-data">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 24px;">
                        <?php if (!empty($fullUser['avatar_url'])): ?>
                            <img src="<?= baseUrl($fullUser['avatar_url']) ?>" alt="Avatar" class="avatar" style="width: 64px; height: 64px; font-size: 24px;">
                        <?php else: ?>
                            <div class="avatar" style="width: 64px; height: 64px; font-size: 24px;">
                                <?= strtoupper(substr($fullUser['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <label class="form-label" for="avatar" style="margin-bottom: 4px;">Update Avatar</label>
                            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" style="font-size: 13px;">
                            <div style="font-size: 11px; color: var(--muted); margin-top: 4px;">JPG, PNG, WebP up to 2MB</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= Sanitizer::e($fullUser['full_name']) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" value="<?= Sanitizer::e($fullUser['email']) ?>" disabled>
                        <span style="font-size: 11px; color: var(--muted);">Email address cannot be changed directly.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" class="form-control" value="<?= Sanitizer::e($fullUser['username']) ?>" disabled>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bio">Bio / Study Goals</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Tell other learners about your goals or background..."><?= Sanitizer::e($fullUser['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">
                        Save Profile Changes
                    </button>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="card" style="padding: 28px;">
                <h3 style="font-size: 1.25rem; margin-bottom: 20px;">Change Password</h3>

                <form method="POST" action="<?= baseUrl('profile.php') ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group">
                        <label class="form-label" for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Minimum 8 characters">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Account Details Sidebar -->
        <div>
            <div class="card" style="padding: 24px;">
                <h4 style="margin-bottom: 16px; font-size: 15px;">Account Metadata</h4>
                <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13px;">
                    <div>
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Role</div>
                        <div style="font-weight: 700; color: var(--dark);"><?= strtoupper(Sanitizer::e($fullUser['role'])) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Member Since</div>
                        <div><?= date('M j, Y', strtotime($fullUser['created_at'])) ?></div>
                    </div>
                    <div>
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Last Logged In</div>
                        <div><?= Sanitizer::formatDate($fullUser['last_login_at'], 'M j, Y, g:i a') ?></div>
                    </div>
                    <div>
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase;">Status</div>
                        <span class="badge badge-success"><?= strtoupper(Sanitizer::e($fullUser['status'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
