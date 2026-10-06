<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Services\AuthService;
use App\Helpers\Sanitizer;

if (Auth::check()) {
    header('Location: ' . baseUrl('dashboard.php'));
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();

    $authService = new AuthService();
    $result = $authService->register($_POST);

    if ($result['success']) {
        $_SESSION['flash_success'] = 'Welcome to AliStack Learner! Your account has been created successfully.';
        header('Location: ' . baseUrl('dashboard.php'));
        exit;
    } else {
        $errors = $result['errors'] ?? ['general' => 'Registration failed. Please check the fields below.'];
    }
}

$pageTitle = 'Student Registration';
$pageDesc = 'Register for a free AliStack Learner account and start mastering technical skills.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 48px 24px;">
    <div class="card" style="max-width: 480px; width: 100%; padding: 36px 32px; box-shadow: var(--shadow-xl);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 44px; height: 44px; font-size: 1.35rem;">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h2 style="font-size: 1.6rem; margin-bottom: 6px;">Create Student Account</h2>
            <p style="font-size: 13px; color: var(--muted); margin: 0;">Start learning with focus, practice with AI, and earn verified credentials.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div><?= Sanitizer::e($errors['general']) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= baseUrl('register.php') ?>">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label class="form-label" for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-control" required placeholder="John Doe" value="<?= Sanitizer::e($_POST['full_name'] ?? '') ?>">
                <?php if (!empty($errors['full_name'])): ?>
                    <span class="form-error"><?= Sanitizer::e($errors['full_name']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" required placeholder="john@example.com" value="<?= Sanitizer::e($_POST['email'] ?? '') ?>">
                <?php if (!empty($errors['email'])): ?>
                    <span class="form-error"><?= Sanitizer::e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required placeholder="johndoe" value="<?= Sanitizer::e($_POST['username'] ?? '') ?>">
                <?php if (!empty($errors['username'])): ?>
                    <span class="form-error"><?= Sanitizer::e($errors['username']) ?></span>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Min 8 characters">
                    <?php if (!empty($errors['password'])): ?>
                        <span class="form-error"><?= Sanitizer::e($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirm">Confirm Password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form-control" required placeholder="Repeat password">
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <span class="form-error"><?= Sanitizer::e($errors['password_confirm']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-bottom: 24px;">
                <label class="form-check">
                    <input type="checkbox" name="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?> required>
                    <span>I agree to the <a href="<?= baseUrl('terms.php') ?>" target="_blank">Terms of Service</a> & <a href="<?= baseUrl('privacy.php') ?>" target="_blank">Privacy Policy</a></span>
                </label>
                <?php if (!empty($errors['terms'])): ?>
                    <span class="form-error"><?= Sanitizer::e($errors['terms']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 11px;">
                <i class="bi bi-person-check-fill"></i> Complete Registration
            </button>
        </form>

        <div style="text-align: center; margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); font-size: 13px; color: var(--muted);">
            Already have an account? 
            <a href="<?= baseUrl('login.php') ?>" style="font-weight: 600;">Log In</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
