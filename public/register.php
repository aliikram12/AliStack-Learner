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
        $_SESSION['flash_success'] = 'Welcome to AliStack Learner! Your student account has been created successfully.';
        header('Location: ' . baseUrl('dashboard.php'));
        exit;
    } else {
        $errors = $result['errors'] ?? ['general' => 'Registration could not be completed. Please correct the fields below.'];
    }
}

$pageTitle = 'Student Registration';
$pageDesc = 'Register for a free AliStack Learner account to access structured technical courses and on-demand AI tutoring.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="min-height: calc(100vh - 180px); display: flex; align-items: center; justify-content: center; padding: 48px 20px; background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.04) 0%, rgba(248,250,252,1) 80%);">
    <div class="card" data-animate="fade-up" style="max-width: 520px; width: 100%; padding: 40px 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: 0 20px 40px -15px rgba(15,23,42,0.08); background: #FFFFFF;">
        <div style="text-align: center; margin-bottom: 28px;">
            <div class="brand-icon" style="margin: 0 auto 16px; width: 48px; height: 48px; font-size: 1.4rem; background: linear-gradient(135deg, #2563EB, #7C3AED); color: #FFFFFF; border-radius: 12px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <h1 style="font-size: 1.65rem; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 8px; color: var(--dark);">Create Student Account</h1>
            <p style="font-size: 14px; color: var(--muted); margin: 0; line-height: 1.5;">Start learning with focus, practice with AI, and earn verified credentials.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error" style="margin-bottom: 24px;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div style="font-size: 13px; font-weight: 500;"><?= Sanitizer::e($errors['general']) ?></div>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= baseUrl('register.php') ?>" style="display: flex; flex-direction: column; gap: 18px;">
            <?= Csrf::field() ?>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" for="full_name" style="font-weight: 600; font-size: 13px; color: var(--dark);">Full Name</label>
                <div style="position: relative;">
                    <i class="bi bi-person" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                    <input type="text" id="full_name" name="full_name" class="form-control" style="padding-left: 42px;" required placeholder="e.g. Alex Morgan" value="<?= Sanitizer::e($_POST['full_name'] ?? '') ?>">
                </div>
                <?php if (!empty($errors['full_name'])): ?>
                    <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['full_name']) ?></span>
                <?php endif; ?>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="email" style="font-weight: 600; font-size: 13px; color: var(--dark);">Email Address</label>
                    <div style="position: relative;">
                        <i class="bi bi-envelope" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="email" id="email" name="email" class="form-control" style="padding-left: 42px;" required placeholder="alex@example.com" value="<?= Sanitizer::e($_POST['email'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['email'])): ?>
                        <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['email']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="username" style="font-weight: 600; font-size: 13px; color: var(--dark);">Username</label>
                    <div style="position: relative;">
                        <i class="bi bi-at" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="text" id="username" name="username" class="form-control" style="padding-left: 42px;" required placeholder="alexm" value="<?= Sanitizer::e($_POST['username'] ?? '') ?>">
                    </div>
                    <?php if (!empty($errors['username'])): ?>
                        <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['username']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="password" style="font-weight: 600; font-size: 13px; color: var(--dark);">Password</label>
                    <div style="position: relative;">
                        <i class="bi bi-lock" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="password" id="password" name="password" class="form-control" style="padding-left: 42px;" required placeholder="Min 8 characters">
                    </div>
                    <?php if (!empty($errors['password'])): ?>
                        <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="password_confirm" style="font-weight: 600; font-size: 13px; color: var(--dark);">Confirm Password</label>
                    <div style="position: relative;">
                        <i class="bi bi-shield-check" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 16px;"></i>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control" style="padding-left: 42px;" required placeholder="Repeat password">
                    </div>
                    <?php if (!empty($errors['password_confirm'])): ?>
                        <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['password_confirm']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 4px;">
                <label class="form-check" style="margin: 0; font-size: 13px; cursor: pointer; user-select: none;">
                    <input type="checkbox" name="terms" value="1" <?= !empty($_POST['terms']) ? 'checked' : '' ?> required style="cursor: pointer;">
                    <span style="color: var(--muted);">
                        I agree to the <a href="<?= baseUrl('terms.php') ?>" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: none;">Terms of Service</a> & <a href="<?= baseUrl('privacy.php') ?>" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: none;">Privacy Policy</a>
                    </span>
                </label>
                <?php if (!empty($errors['terms'])): ?>
                    <span class="form-error" style="color: var(--danger); font-size: 12px; margin-top: 4px; display: block;"><?= Sanitizer::e($errors['terms']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.25); margin-top: 6px;">
                <i class="bi bi-person-check-fill"></i> Complete Registration
            </button>
        </form>

        <div style="text-align: center; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border-color); font-size: 13px; color: var(--muted);">
            Already have an account? 
            <a href="<?= baseUrl('login.php') ?>" style="font-weight: 700; color: var(--primary); text-decoration: none;">Log In</a>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
