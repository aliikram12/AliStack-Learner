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
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="max-width: 440px; width: 100%; padding: 36px 32px; box-shadow: var(--shadow-xl);">
        <div style="text-align: center; margin-bottom: 24px;">
            <h2 style="font-size: 1.5rem; margin-bottom: 6px;">Set New Password</h2>
            <p style="font-size: 13px; color: var(--muted); margin: 0;">Create a strong new password for your account.</p>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                <div><?= Sanitizer::e($success) ?></div>
            </div>
            <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary" style="width: 100%; margin-top: 12px;">
                Proceed to Log In
            </a>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= Sanitizer::e($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= baseUrl('reset-password.php') ?>">
                <?= Csrf::field() ?>
                <input type="hidden" name="token" value="<?= Sanitizer::e($token) ?>">

                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirm New Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Repeat new password">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                    Update Password
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
