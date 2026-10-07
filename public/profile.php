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
$pageDesc = 'Manage your student profile information, study goals, avatar, and account security.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 48px 0;">
    <div class="container" style="max-width: 920px;">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); margin-bottom: 16px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--muted); text-decoration: none;">Dashboard</a>
            <span>/</span>
            <span style="color: var(--dark); font-weight: 600;">Account Profile</span>
        </div>

        <div style="display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
            <div style="position: relative;">
                <?php if (!empty($fullUser['avatar_url'])): ?>
                    <img src="<?= baseUrl($fullUser['avatar_url']) ?>" alt="Avatar" class="avatar" style="width: 76px; height: 76px; font-size: 28px; border: 3px solid #FFFFFF; box-shadow: 0 4px 14px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div class="avatar" style="width: 76px; height: 76px; font-size: 28px; font-weight: 800; background: linear-gradient(135deg, #2563EB, #7C3AED); color: #FFFFFF; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                        <?= strtoupper(substr($fullUser['full_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <h1 style="font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 800; color: var(--dark); margin: 0 0 6px;">
                    <?= Sanitizer::e($fullUser['full_name']) ?>
                </h1>
                <div style="display: flex; align-items: center; gap: 12px; font-size: 13.5px; color: var(--muted); flex-wrap: wrap;">
                    <span><i class="bi bi-at"></i><?= Sanitizer::e($fullUser['username']) ?></span>
                    <span>&bull;</span>
                    <span class="badge badge-primary"><?= strtoupper(Sanitizer::e($fullUser['role'])) ?></span>
                    <span>&bull;</span>
                    <span>Member since <?= date('M Y', strtotime($fullUser['created_at'])) ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container" style="max-width: 920px; padding: 48px 20px 88px;">
    <?php if ($successMsg): ?>
        <div class="alert alert-success" style="margin-bottom: 24px;">
            <i class="bi bi-check-circle-fill"></i>
            <div style="font-size: 13.5px; font-weight: 500;"><?= Sanitizer::e($successMsg) ?></div>
        </div>
    <?php endif; ?>

    <?php if ($errorMsg): ?>
        <div class="alert alert-error" style="margin-bottom: 24px;">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div style="font-size: 13.5px; font-weight: 500;"><?= Sanitizer::e($errorMsg) ?></div>
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) 280px; gap: 32px; align-items: flex-start;">
        <!-- Left Forms Column -->
        <div>
            <!-- Profile Details Card -->
            <div class="card" data-animate="fade-up" style="padding: 36px 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); margin-bottom: 32px; background: #FFFFFF;">
                <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0 0 24px;">Profile Information</h3>

                <form method="POST" action="<?= baseUrl('profile.php') ?>" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 20px;">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div style="background: var(--bg-main); border: 1px dashed var(--border-color); border-radius: var(--radius-lg); padding: 18px; display: flex; align-items: center; gap: 16px;">
                        <div>
                            <label class="form-label" for="avatar" style="font-weight: 700; font-size: 13px; color: var(--dark); margin-bottom: 4px;">Update Avatar Photo</label>
                            <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp" style="font-size: 13px;">
                            <div style="font-size: 11.5px; color: var(--muted); margin-top: 4px;">Supports JPG, PNG, WebP up to 2MB</div>
                        </div>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="full_name" style="font-weight: 600; font-size: 13px; color: var(--dark);">Full Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" required value="<?= Sanitizer::e($fullUser['full_name']) ?>">
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="email" style="font-weight: 600; font-size: 13px; color: var(--dark);">Email Address</label>
                            <input type="email" id="email" class="form-control" value="<?= Sanitizer::e($fullUser['email']) ?>" disabled style="background: var(--bg-main); cursor: not-allowed;">
                            <span style="font-size: 11px; color: var(--muted); margin-top: 2px; display: block;">Primary address (cannot be modified)</span>
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="username" style="font-weight: 600; font-size: 13px; color: var(--dark);">Username</label>
                            <input type="text" id="username" class="form-control" value="<?= Sanitizer::e($fullUser['username']) ?>" disabled style="background: var(--bg-main); cursor: not-allowed;">
                        </div>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="bio" style="font-weight: 600; font-size: 13px; color: var(--dark);">Bio & Learning Ambitions</label>
                        <textarea id="bio" name="bio" class="form-control" rows="3" placeholder="Share your technical interests, career goals, or learning focus..."><?= Sanitizer::e($fullUser['bio'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: fit-content; padding: 11px 24px; font-weight: 700; box-shadow: 0 4px 12px rgba(37,99,235,0.2);">
                        <i class="bi bi-check2"></i> Save Profile Changes
                    </button>
                </form>
            </div>

            <!-- Password Change Card -->
            <div class="card" data-animate="fade-up" style="padding: 36px 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0 0 24px;">Security & Password</h3>

                <form method="POST" action="<?= baseUrl('profile.php') ?>" style="display: flex; flex-direction: column; gap: 20px;">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="current_password" style="font-weight: 600; font-size: 13px; color: var(--dark);">Current Password</label>
                        <input type="password" id="current_password" name="current_password" class="form-control" required placeholder="••••••••">
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="new_password" style="font-weight: 600; font-size: 13px; color: var(--dark);">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required placeholder="Minimum 8 characters">
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="confirm_password" style="font-weight: 600; font-size: 13px; color: var(--dark);">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary" style="width: fit-content; padding: 11px 24px; font-weight: 700;">
                        <i class="bi bi-shield-lock"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Account Details Sidebar -->
        <div>
            <div class="card" data-animate="fade-up" style="padding: 28px 24px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                <h4 style="margin: 0 0 18px; font-size: 15px; font-weight: 800; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-shield-check" style="color: var(--primary);"></i> Account Metadata
                </h4>
                
                <div style="display: flex; flex-direction: column; gap: 16px; font-size: 13px;">
                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Account Status</div>
                        <span class="badge badge-success"><i class="bi bi-check-circle-fill"></i> <?= strtoupper(Sanitizer::e($fullUser['status'])) ?></span>
                    </div>

                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Role Level</div>
                        <div style="font-weight: 700; color: var(--dark);"><?= strtoupper(Sanitizer::e($fullUser['role'])) ?></div>
                    </div>

                    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Registration Date</div>
                        <div style="color: var(--dark); font-weight: 600;"><?= date('F j, Y', strtotime($fullUser['created_at'])) ?></div>
                    </div>

                    <div>
                        <div style="color: var(--muted); font-size: 11px; text-transform: uppercase; font-weight: 700; margin-bottom: 4px;">Last Login Session</div>
                        <div style="color: var(--dark); font-weight: 600;"><?= Sanitizer::formatDate($fullUser['last_login_at'], 'M j, Y, g:i a') ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
