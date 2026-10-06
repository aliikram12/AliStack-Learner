<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Services\AuthService;
use App\Helpers\Sanitizer;

// Redirect if already logged in
if (Auth::check()) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $identifier = trim((string)($_POST['login_identifier'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $authService = new AuthService();
    $result = $authService->login($identifier, $password);

    if ($result['success']) {
        $dest = $_SESSION['intended_url'] ?? 'dashboard.php';
        unset($_SESSION['intended_url']);
        header('Location: ' . baseUrl($dest));
        exit;
    } else {
        $error = $result['error'];
    }
}

$pageTitle = 'Log In';
$pageDesc = 'Log into your AliStack Learner account to continue courses and track progress.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="max-width: 440px; width: 100%; padding: 36px 32px; box-shadow: var(--shadow-xl);">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 44px; height: 44px; font-size: 1.35rem;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h2 style="font-size: 1.6rem; margin-bottom: 6px;">Welcome Back</h2>
            <p style="font-size: 13px; color: var(--muted); margin: 0;">Log in to resume your courses and AI learning sessions.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= Sanitizer::e($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= baseUrl('login.php') ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label" for="login_identifier">Email or Username</label>
                <input type="text" id="login_identifier" name="login_identifier" class="form-control" required autofocus placeholder="name@example.com or username" value="<?= Sanitizer::e($_POST['login_identifier'] ?? '') ?>">
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="margin: 0;">Password</label>
                    <a href="<?= baseUrl('forgot-password.php') ?>" style="font-size: 12px; color: var(--primary);">Forgot password?</a>
                </div>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-check">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me logged in on this device</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                <i class="bi bi-box-arrow-in-right"></i> Log In to AliStack Learner
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 13px; color: var(--muted);">
            Don't have an account? 
            <a href="<?= baseUrl('register.php') ?>" style="font-weight: 600;">Create account</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
