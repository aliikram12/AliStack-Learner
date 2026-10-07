<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Csrf;
use App\Services\AuthService;
use App\Helpers\Sanitizer;

$message = null;
$resetLink = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $email = trim((string)($_POST['email'] ?? ''));

    $authService = new AuthService();
    $result = $authService->forgotPassword($email);
    $message = $result['message'];

    if (!empty($result['reset_token'])) {
        $resetLink = baseUrl('reset-password.php?token=' . urlencode($result['reset_token']));
    }
}

$pageTitle = 'Forgot Password';
$pageDesc = 'Reset your AliStack Learner account password.';
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 20px; background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.04) 0%, rgba(248,250,252,1) 80%);">
    <div class="card" data-animate="fade-up" style="max-width: 440px; width: 100%; padding: 40px 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: 0 20px 40px -15px rgba(15,23,42,0.08); background: #FFFFFF;">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 48px; height: 48px; font-size: 1.4rem; background: #FEF3C7; color: #B45309; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-key-fill"></i>
            </div>
            <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--dark);">Reset Password</h1>
            <p style="font-size: 14px; color: var(--muted); margin: 0; line-height: 1.5;">Enter your account email to receive your password reset link.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info" style="margin-bottom: 24px;">
                <i class="bi bi-info-circle-fill"></i>
                <div style="font-size: 13px;"><?= Sanitizer::e($message) ?></div>
            </div>

            <?php if ($resetLink): ?>
                <div style="background: #F8FAFC; border: 1px dashed var(--border-color); padding: 16px; border-radius: var(--radius-md); margin-bottom: 24px; font-size: 13px;">
                    <strong style="color: var(--dark); display: block; margin-bottom: 6px;">Development / Local Reset Link:</strong>
                    <a href="<?= $resetLink ?>" style="word-break: break-all; color: var(--primary); font-weight: 600; text-decoration: none;">
                        Click here to reset your password directly &rarr;
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" action="<?= baseUrl('forgot-password.php') ?>" style="display: flex; flex-direction: column; gap: 20px;">
                <?= Csrf::field() ?>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="email" style="font-weight: 600; font-size: 13px; color: var(--dark);">Account Email Address</label>
                    <div style="position: relative;">
                        <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="email" id="email" name="email" class="form-control" style="padding-left: 42px;" required autofocus placeholder="name@example.com">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                    <i class="bi bi-send-fill"></i> Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border-color); font-size: 13px;">
            <a href="<?= baseUrl('login.php') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                <i class="bi bi-arrow-left"></i> Return to log in
            </a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
