<?php
declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Sanitizer;
use App\Repositories\NotificationRepository;

$currentUser = Auth::user();
$unreadNotifCount = 0;
$recentNotifs = [];

if ($currentUser) {
    $notifRepo = new NotificationRepository();
    $unreadNotifCount = $notifRepo->getUnreadCount((int)$currentUser['id']);
    $recentNotifs = $notifRepo->getUserNotifications((int)$currentUser['id'], 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? Sanitizer::e($pageTitle) . ' - AliStack Learner' : 'AliStack Learner - Focused Learning & Skills Platform' ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? Sanitizer::e($pageDesc) : 'Learn with focus. Practice with purpose. Prove your skills with AliStack Learner.' ?>">
    <?= Csrf::meta() ?>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Core CSS -->
    <link rel="stylesheet" href="<?= assetUrl('css/app.css') ?>">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= assetUrl('css/' . $css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <a href="<?= baseUrl('/') ?>" class="brand-logo">
                    <div class="brand-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <span>AliStack Learner</span>
                    <span class="brand-badge">By AliStack</span>
                </a>

                <ul class="nav-links">
                    <li><a href="<?= baseUrl('courses.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'courses.php') ? 'active' : '' ?>">Explore Courses</a></li>
                    <li><a href="<?= baseUrl('how-it-works.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'how-it-works.php') ? 'active' : '' ?>">How It Works</a></li>
                    <li><a href="<?= baseUrl('community.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'community.php') ? 'active' : '' ?>">Community</a></li>
                    <li><a href="<?= baseUrl('verify-certificate.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'verify-certificate.php') ? 'active' : '' ?>">Verify Certificate</a></li>
                    <li><a href="<?= baseUrl('about.php') ?>" class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'about.php') ? 'active' : '' ?>">About</a></li>
                </ul>

                <div class="nav-actions">
                    <?php if ($currentUser): ?>
                        <!-- Notification Bell -->
                        <div class="notif-bell-wrap" id="notifDropdownWrap">
                            <button type="button" class="notif-bell-btn" id="notifBellBtn" title="Notifications">
                                <i class="bi bi-bell"></i>
                                <?php if ($unreadNotifCount > 0): ?>
                                    <span class="notif-badge" id="notifBadge"><?= $unreadNotifCount ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu notif-dropdown" id="notifMenu">
                                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 13px;">Notifications</strong>
                                    <?php if ($unreadNotifCount > 0): ?>
                                        <button id="markAllReadBtn" style="border:none; background:none; color:var(--primary); font-size:11px; cursor:pointer; font-weight:600;">Mark all read</button>
                                    <?php endif; ?>
                                </div>
                                <div id="notifList">
                                    <?php if (empty($recentNotifs)): ?>
                                        <div style="padding: 20px; text-align: center; color: var(--muted); font-size: 13px;">
                                            No notifications yet.
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recentNotifs as $n): ?>
                                            <a href="<?= !empty($n['link_url']) ? baseUrl($n['link_url']) : '#' ?>" class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                                                <div class="notif-item-title"><?= Sanitizer::e($n['title']) ?></div>
                                                <div class="notif-item-desc"><?= Sanitizer::e($n['message']) ?></div>
                                                <div class="notif-item-time"><?= Sanitizer::timeAgo($n['created_at']) ?></div>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="user-menu" id="userMenuWrap">
                            <?php if (!empty($currentUser['avatar_url'])): ?>
                                <img src="<?= baseUrl($currentUser['avatar_url']) ?>" alt="Avatar" class="avatar" id="userMenuBtn">
                            <?php else: ?>
                                <div class="avatar" id="userMenuBtn">
                                    <?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?>
                                </div>
                            <?php endif; ?>

                            <div class="dropdown-menu" id="userMenu">
                                <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                                    <div style="font-weight: 700; font-size: 14px; color: var(--dark);"><?= Sanitizer::e($currentUser['full_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::e($currentUser['email']) ?></div>
                                </div>
                                <a href="<?= baseUrl('dashboard.php') ?>" class="dropdown-item">
                                    <i class="bi bi-grid-fill"></i> My Dashboard
                                </a>
                                <a href="<?= baseUrl('certificates.php') ?>" class="dropdown-item">
                                    <i class="bi bi-award-fill"></i> My Certificates
                                </a>
                                <a href="<?= baseUrl('profile.php') ?>" class="dropdown-item">
                                    <i class="bi bi-person-fill"></i> Profile & Settings
                                </a>
                                <?php if (Auth::canAccessAdmin()): ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?= baseUrl('admin/index.php') ?>" class="dropdown-item" style="color: var(--secondary); font-weight: 600;">
                                        <i class="bi bi-speedometer2"></i> Admin Panel
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="<?= baseUrl('api/auth/logout.php') ?>" style="margin: 0;">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer; color: var(--error);">
                                        <i class="bi bi-box-arrow-right"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= baseUrl('login.php') ?>" class="btn btn-outline btn-sm">Log In</a>
                        <a href="<?= baseUrl('register.php') ?>" class="btn btn-primary btn-sm">Start Learning</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main style="flex: 1;">
        <!-- Flash Messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div><?= Sanitizer::e($_SESSION['flash_success']) ?></div>
                </div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div><?= Sanitizer::e($_SESSION['flash_error']) ?></div>
                </div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
