<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Csrf;
use App\Services\AuthService;
use App\Helpers\Sanitizer;

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$error = null;
$success = null;

if (empty($token)) {
    header('Location: ' . baseUrl('login.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $newPassword = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['password_confirm'] ?? '');

    $authService = new AuthService();
    $result = $authService->resetPassword($token, $newPassword, $confirmPassword);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'Choose New Password';
$pageDesc = 'Set a new secure password for your AliStack Learner account.';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 20px; background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.04) 0%, rgba(248,250,252,1) 80%);">
    <div class="card" data-animate="fade-up" style="max-width: 440px; width: 100%; padding: 40px 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: 0 20px 40px -15px rgba(15,23,42,0.08); background: #FFFFFF;">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 48px; height: 48px; font-size: 1.4rem; background: linear-gradient(135deg, #2563EB, #7C3AED); color: #FFFFFF; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--dark);">Set New Password</h1>
            <p style="font-size: 14px; color: var(--muted); margin: 0; line-height: 1.5;">Create a strong new password for your account.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success" style="margin-bottom: 24px;">
                <i class="bi bi-check-circle-fill"></i>
                <div style="font-size: 13px; font-weight: 500;"><?= Sanitizer::e($success) ?></div>
            </div>
            <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700;">
                <i class="bi bi-box-arrow-in-right"></i> Proceed to Log In
            </a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 24px;">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div style="font-size: 13px; font-weight: 500;"><?= Sanitizer::e($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= baseUrl('reset-password.php') ?>" style="display: flex; flex-direction: column; gap: 20px;">
                <?= Csrf::field() ?>
                <input type="hidden" name="token" value="<?= Sanitizer::e($token) ?>">

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="password" style="font-weight: 600; font-size: 13px; color: var(--dark);">New Password</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="password" id="password" name="password" class="form-control" style="padding-left: 42px;" required placeholder="Minimum 8 characters">
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="password_confirm" style="font-weight: 600; font-size: 13px; color: var(--dark);">Confirm New Password</label>
                    <div style="position: relative;">
                        <i class="bi bi-shield-check" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" style="padding-left: 42px;" required placeholder="Repeat new password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                    <i class="bi bi-check2-circle"></i> Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
