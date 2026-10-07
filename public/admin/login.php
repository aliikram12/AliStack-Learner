<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Sanitizer;
use App\Services\AuthService;

// If already authenticated as administrator, forward straight to admin dashboard
if (Auth::check() && Auth::canAccessAdmin()) {
    header('Location: ' . baseUrl('admin/index.php'));
    exit;
}

$error = null;
$authService = new AuthService();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    
    $login = trim((string)($_POST['login'] ?? $_POST['identifier'] ?? $_POST['login_identifier'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    
    $result = $authService->login($login, $password);
    
    if ($result['success']) {
        if (!Auth::canAccessAdmin()) {
            Auth::logout();
            $error = 'Access restricted. This administrative console requires an authorized Administrator or Moderator account.';
        } else {
            $_SESSION['flash_success'] = 'Welcome back, ' . Sanitizer::e(Auth::user()['full_name']) . '!';
            header('Location: ' . baseUrl('admin/index.php'));
            exit;
        }
    } else {
        $error = $result['error'] ?? 'Invalid administrator credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Console Login - AliStack Learner</title>
    <?= Csrf::meta() ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Core App CSS -->
    <link rel="stylesheet" href="<?= assetUrl('css/app.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin.css') ?>">
    
    <style>
        body {
            background-color: #0B0F19;
            color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .admin-login-layout {
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            max-width: 960px;
            width: 100%;
            background: #111827;
            border: 1px solid #1F2937;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }
        .admin-brand-panel {
            background: linear-gradient(145deg, #1E1B4B, #0F172A);
            padding: 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-right: 1px solid #1F2937;
            position: relative;
            overflow: hidden;
        }
        .admin-brand-panel::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(124, 58, 237, 0.25) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        .admin-form-panel {
            padding: 48px;
            background: #111827;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .admin-input {
            background: #1F2937;
            border: 1px solid #374151;
            color: #FFFFFF;
            padding: 12px 16px;
            border-radius: var(--radius-md);
            width: 100%;
            font-size: 14px;
            transition: all var(--transition-fast);
        }
        .admin-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.25);
            background: #111827;
        }
        @media (max-width: 820px) {
            .admin-login-layout {
                grid-template-columns: 1fr;
            }
            .admin-brand-panel {
                padding: 32px;
            }
            .admin-form-panel {
                padding: 32px;
            }
        }
    </style>
</head>
<body>

    <div class="admin-login-layout">
        <!-- Left Side: AliStack Branding & Control Overview -->
        <div class="admin-brand-panel">
            <div>
                <a href="<?= baseUrl('/') ?>" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 36px;">
                    <div class="brand-icon" style="width: 38px; height: 38px; font-size: 1.2rem;">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <span style="font-family: var(--font-heading); font-size: 1.25rem; font-weight: 800; color: #FFFFFF;">AliStack Learner</span>
                </a>
                
                <div style="display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; background: rgba(124, 58, 237, 0.2); color: #C4B5FD; padding: 4px 10px; border-radius: var(--radius-full); margin-bottom: 14px; border: 1px solid rgba(124, 58, 237, 0.3);">
                    <i class="bi bi-shield-lock-fill"></i> CONTROL CENTER
                </div>
                
                <h1 style="color: #FFFFFF; font-size: 2rem; font-weight: 800; line-height: 1.25; margin-bottom: 14px;">
                    Manage AliStack Learner
                </h1>
                
                <p style="color: #94A3B8; font-size: 14px; line-height: 1.6; max-width: 360px;">
                    Access the central management console to configure YouTube course playlists, author assessments, review certificates, supervise community discussions, and inspect security audit logs.
                </p>
            </div>

            <div style="margin-top: 40px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 12px; color: #64748B;">
                A proud product of <strong style="color: #E2E8F0;">AliStack</strong> &bull; Secured with RBAC & Audit Trails
            </div>
        </div>

        <!-- Right Side: Login Card -->
        <div class="admin-form-panel">
            <div style="margin-bottom: 28px;">
                <h2 style="color: #FFFFFF; font-size: 1.45rem; font-weight: 700; margin-bottom: 6px;">
                    Administrative Sign In
                </h2>
                <p style="color: #94A3B8; font-size: 13.5px;">
                    Please enter your verified administrator credentials.
                </p>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.3); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 13px; color: #FCA5A5;">
                    <i class="bi bi-exclamation-triangle-fill" style="flex-shrink: 0; font-size: 16px;"></i>
                    <div><?= Sanitizer::e($error) ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= Csrf::field() ?>

                <div class="form-group" style="margin-bottom: 18px;">
                    <label class="form-label" style="color: #E2E8F0; font-size: 13px;">Email or Username</label>
                    <div class="input-icon-wrap">
                        <i class="bi bi-person input-icon" style="color: #94A3B8;"></i>
                        <input type="text" name="login" class="admin-input" style="padding-left: 38px;" placeholder="admin@alistack.com" required autofocus value="<?= Sanitizer::e($_POST['login'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label class="form-label" style="color: #E2E8F0; font-size: 13px; margin: 0;">Password</label>
                        <a href="<?= baseUrl('forgot-password.php') ?>" style="font-size: 12px; color: #60A5FA;">Forgot password?</a>
                    </div>
                    <div class="input-icon-wrap">
                        <i class="bi bi-lock input-icon" style="color: #94A3B8;"></i>
                        <input type="password" name="password" class="admin-input" style="padding-left: 38px;" placeholder="••••••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 700; background: linear-gradient(135deg, #2563EB, #7C3AED); border: none;">
                    <i class="bi bi-box-arrow-in-right"></i> Authenticate & Enter Console
                </button>
            </form>

            <div style="margin-top: 28px; padding-top: 20px; border-top: 1px solid #1F2937; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                <a href="<?= baseUrl('/') ?>" style="color: #94A3B8; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-arrow-left"></i> Return to Student Site
                </a>
                <span style="color: #64748B;">
                    <i class="bi bi-shield-check" style="color: #10B981;"></i> SSL 256-Bit
                </span>
            </div>
        </div>
    </div>

</body>
</html>
