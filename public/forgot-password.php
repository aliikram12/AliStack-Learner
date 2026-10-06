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
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="max-width: 440px; width: 100%; padding: 36px 32px; box-shadow: var(--shadow-xl);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 44px; height: 44px; font-size: 1.35rem; background: var(--warning);">
                <i class="bi bi-key-fill"></i>
            </div>
            <h2 style="font-size: 1.5rem; margin-bottom: 6px;">Reset Password</h2>
            <p style="font-size: 13px; color: var(--muted); margin: 0;">Enter your account email to receive reset instructions.</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill"></i>
                <div><?= Sanitizer::e($message) ?></div>
            </div>

            <?php if ($resetLink): ?>
                <div style="background: #F1F5F9; border: 1px dashed #CBD5E1; padding: 14px; border-radius: var(--radius-md); margin-bottom: 20px; font-size: 13px;">
                    <strong>Local Environment Notice:</strong><br>
                    <a href="<?= $resetLink ?>" style="word-break: break-all; color: var(--primary); font-weight: 600;">
                        Click here to reset your password directly
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <form method="POST" action="<?= baseUrl('forgot-password.php') ?>">
                <?= Csrf::field() ?>
                <div class="form-group">
                    <label class="form-label" for="email">Account Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" required placeholder="name@example.com">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                    Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 13px;">
            <a href="<?= baseUrl('login.php') ?>"><i class="bi bi-arrow-left"></i> Return to login</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
