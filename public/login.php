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
$pageDesc = 'Log into your AliStack Learner account to continue courses, test assessments, and track progress.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 20px; background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.04) 0%, rgba(248,250,252,1) 80%);">
    <div class="card" data-animate="fade-up" style="max-width: 440px; width: 100%; padding: 40px 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: 0 20px 40px -15px rgba(15,23,42,0.08); background: #FFFFFF;">
        <div style="text-align: center; margin-bottom: 32px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 48px; height: 48px; font-size: 1.4rem; background: linear-gradient(135deg, #2563EB, #7C3AED); color: #FFFFFF; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--dark);">Welcome Back</h1>
            <p style="font-size: 14px; color: var(--muted); margin: 0; line-height: 1.5;">Log in to resume your learning workspace & AI mentorship.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error" style="margin-bottom: 24px;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div style="font-size: 13px; font-weight: 500;"><?= Sanitizer::e($error) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= baseUrl('login.php') ?>" style="display: flex; flex-direction: column; gap: 20px;">
            <?= Csrf::field() ?>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="login_identifier" style="font-weight: 600; font-size: 13px; color: var(--dark);">Email or Username</label>
                <div style="position: relative;">
                    <i class="bi bi-person" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                    <input type="text" id="login_identifier" name="login_identifier" class="form-control" style="padding-left: 42px;" required autofocus placeholder="name@example.com or username" value="<?= Sanitizer::e($_POST['login_identifier'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin: 0;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" for="password" style="font-weight: 600; font-size: 13px; color: var(--dark); margin: 0;">Password</label>
                    <a href="<?= baseUrl('forgot-password.php') ?>" style="font-size: 12px; color: var(--primary); font-weight: 600; text-decoration: none;">Forgot password?</a>
                </div>
                <div style="position: relative;">
                    <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                    <input type="password" id="password" name="password" class="form-control" style="padding-left: 42px; padding-right: 42px;" required placeholder="••••••••">
                    <button type="button" id="togglePasswordBtn" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--muted); cursor: pointer; padding: 4px;" aria-label="Toggle password visibility">
                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between;">
                <label class="form-check" style="margin: 0; font-size: 13px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="remember" value="1" style="cursor: pointer;">
                    <span style="color: var(--muted);">Keep me logged in</span>
                </label>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                <i class="bi bi-box-arrow-in-right"></i> Log In to AliStack Learner
            </button>
        </form>

        <div style="text-align: center; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border-color); font-size: 13px; color: var(--muted);">
            Don't have an account yet? 
            <a href="<?= baseUrl('register.php') ?>" style="font-weight: 700; color: var(--primary); text-decoration: none;">Create free account</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('togglePasswordBtn')?.addEventListener('click', function() {
        const pass = document.getElementById('password');
        const icon = document.getElementById('togglePasswordIcon');
        if (pass && icon) {
            if (pass.type === 'password') {
                pass.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                pass.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
    });
</script>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
