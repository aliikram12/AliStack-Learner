<?php
declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Sanitizer;

Auth::requireAdmin();
$adminUser = Auth::user();
$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? Sanitizer::e($pageTitle) . ' - AliStack Admin' : 'Admin Panel - AliStack Learner' ?></title>
    <?= Csrf::meta() ?>
    <script>
        window.AliStack = {
            baseUrl: "<?= rtrim(baseUrl(), '/') ?>/",
            csrfToken: "<?= Csrf::token() ?>"
        };
        window.getApiUrl = function(endpoint) {
            var base = (window.AliStack && window.AliStack.baseUrl) ? window.AliStack.baseUrl : '/';
            if (!base.endsWith('/')) base += '/';
            var ep = endpoint.startsWith('/') ? endpoint.slice(1) : endpoint;
            return base + ep;
        };
        window.getCsrfToken = function() {
            return (window.AliStack && window.AliStack.csrfToken) || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
        };
    </script>

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= assetUrl('css/app.css') ?>">
    <link rel="stylesheet" href="<?= assetUrl('css/admin.css') ?>">

    <!-- GSAP Animation Engine & Lucide Icons -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <a href="<?= baseUrl('admin/index.php') ?>" class="admin-brand">
                <div class="brand-icon" style="width: 34px; height: 34px; font-size: 1.1rem;">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div>
                    <div>AliStack Learner</div>
                    <div style="font-size: 11px; color: #64748B; font-weight: 400;">Control Center</div>
                </div>
            </a>

            <ul class="admin-nav">
                <li class="admin-nav-item <?= ($currentScript === 'index.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/index.php') ?>"><i class="bi bi-speedometer2"></i> Overview</a>
                </li>

                <div class="admin-nav-header">Curriculum</div>
                <li class="admin-nav-item <?= in_array($currentScript, ['courses.php', 'course-edit.php'], true) ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/courses.php') ?>"><i class="bi bi-collection-play"></i> Courses</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'categories.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/categories.php') ?>"><i class="bi bi-folder2-open"></i> Categories</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'lessons.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/lessons.php') ?>"><i class="bi bi-play-circle"></i> Lessons</a>
                </li>

                <div class="admin-nav-header">Testing & Awards</div>
                <li class="admin-nav-item <?= ($currentScript === 'assessments.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/assessments.php') ?>"><i class="bi bi-patch-question"></i> Assessments</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'questions.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/questions.php') ?>"><i class="bi bi-list-check"></i> MCQ Questions</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'certificates.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/certificates.php') ?>"><i class="bi bi-award"></i> Certificates</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'badges.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/badges.php') ?>"><i class="bi bi-shield-check"></i> Badges</a>
                </li>

                <div class="admin-nav-header">Users & Groups</div>
                <li class="admin-nav-item <?= ($currentScript === 'students.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/students.php') ?>"><i class="bi bi-people"></i> Users & Roles</a>
                </li>
                <li class="admin-nav-item <?= ($currentScript === 'community.php') ? 'active' : '' ?>">
                    <a href="<?= baseUrl('admin/community.php') ?>"><i class="bi bi-chat-square-text"></i> Community & Moderation</a>
                </li>

                <?php if (Auth::isSuperAdmin()): ?>
                    <div class="admin-nav-header">System</div>
                    <li class="admin-nav-item <?= ($currentScript === 'settings.php') ? 'active' : '' ?>">
                        <a href="<?= baseUrl('admin/settings.php') ?>"><i class="bi bi-sliders"></i> Platform Settings</a>
                    </li>
                    <li class="admin-nav-item <?= ($currentScript === 'audit-logs.php') ? 'active' : '' ?>">
                        <a href="<?= baseUrl('admin/audit-logs.php') ?>"><i class="bi bi-shield-lock"></i> Audit Logs</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div style="padding: 16px 20px; border-top: 1px solid #1E293B; font-size: 12px;">
                <a href="<?= baseUrl('dashboard.php') ?>" style="color: #94A3B8; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-box-arrow-left"></i> Exit to Student Site
                </a>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="admin-content">
            <!-- Topbar -->
            <header class="admin-topbar">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <button type="button" id="adminSidebarToggle" class="btn btn-outline btn-sm admin-mobile-toggle" style="display: none; padding: 6px 10px;" aria-label="Toggle Navigation">
                        <i class="bi bi-list" style="font-size: 18px;"></i>
                    </button>
                    <div style="font-weight: 700; font-size: 15px; color: var(--dark);">
                        <?= Sanitizer::e($pageTitle ?? 'Admin Area') ?>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="font-size: 13px; text-align: right;">
                        <div style="font-weight: 700; color: var(--dark);"><?= Sanitizer::e($adminUser['full_name']) ?></div>
                        <div style="font-size: 11px; color: var(--muted);"><?= strtoupper(Sanitizer::e($adminUser['role'])) ?></div>
                    </div>
                    <div class="avatar" style="width: 36px; height: 36px;">
                        <?= strtoupper(substr($adminUser['full_name'], 0, 1)) ?>
                    </div>
                </div>
            </header>

            <!-- Page Main Body -->
            <main class="admin-main">
                <!-- Flash Messages -->
                <?php if (!empty($_SESSION['flash_success'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <div><?= Sanitizer::e($_SESSION['flash_success']) ?></div>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (!empty($_SESSION['flash_error'])): ?>
                    <div class="alert alert-error">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div><?= Sanitizer::e($_SESSION['flash_error']) ?></div>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>
