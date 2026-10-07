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
    $recentNotifs = $notifRepo->getUserNotifications((int)$currentUser['id'], 6);
}

$currentScript = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? Sanitizer::e($pageTitle) . ' - AliStack Learner' : 'AliStack Learner - Focused Learning & Skills Platform' ?></title>
    <meta name="description" content="<?= isset($pageDesc) ? Sanitizer::e($pageDesc) : 'Learn with focus. Practice with purpose. Prove your skills with AliStack Learner.' ?>">
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

    <!-- Modern Typography: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Core Application Design System -->
    <link rel="stylesheet" href="<?= assetUrl('css/app.css') ?>">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= assetUrl('css/' . $css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- GSAP & ScrollTrigger Animation Engine -->
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <nav class="navbar">
                <a href="<?= baseUrl('/') ?>" class="brand-logo">
                    <div class="brand-icon">
                        <i class="bi bi-mortarboard-fill"></i>
                    </div>
                    <span class="brand-name">AliStack Learner</span>
                    <span class="brand-badge">By AliStack</span>
                </a>

                <ul class="nav-links">
                    <li><a href="<?= baseUrl('courses.php') ?>" class="nav-link <?= ($currentScript === 'courses.php') ? 'active' : '' ?>">Explore Courses</a></li>
                    <li><a href="<?= baseUrl('how-it-works.php') ?>" class="nav-link <?= ($currentScript === 'how-it-works.php') ? 'active' : '' ?>">How It Works</a></li>
                    <li><a href="<?= baseUrl('community.php') ?>" class="nav-link <?= ($currentScript === 'community.php') ? 'active' : '' ?>">Community</a></li>
                    <li><a href="<?= baseUrl('verify-certificate.php') ?>" class="nav-link <?= ($currentScript === 'verify-certificate.php') ? 'active' : '' ?>">Verify Certificate</a></li>
                    <li><a href="<?= baseUrl('about.php') ?>" class="nav-link <?= ($currentScript === 'about.php') ? 'active' : '' ?>">About</a></li>
                </ul>

                <div class="nav-actions">
                    <?php if ($currentUser): ?>
                        <!-- Notification Bell -->
                        <div class="notif-bell-wrap" id="notifDropdownWrap">
                            <button type="button" class="notif-bell-btn" id="notifBellBtn" title="Notifications" aria-label="Notifications">
                                <i class="bi bi-bell"></i>
                                <?php if ($unreadNotifCount > 0): ?>
                                    <span class="notif-badge" id="notifBadge"><?= $unreadNotifCount ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu notif-dropdown" id="notifMenu">
                                <div style="padding: 14px 18px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
                                    <strong style="font-size: 13.5px; font-family: var(--font-heading); color: var(--text-dark);">Notifications</strong>
                                    <?php if ($unreadNotifCount > 0): ?>
                                        <button id="markAllReadBtn" style="border:none; background:none; color:var(--primary); font-size:11.5px; cursor:pointer; font-weight:600;">Mark all read</button>
                                    <?php endif; ?>
                                </div>
                                <div id="notifList">
                                    <?php if (empty($recentNotifs)): ?>
                                        <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px;">
                                            <i class="bi bi-bell-slash" style="font-size: 1.5rem; display: block; margin-bottom: 6px; opacity: 0.5;"></i>
                                            No notifications right now.
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
                                <div style="padding: 14px 18px; border-bottom: 1px solid var(--border);">
                                    <div style="font-weight: 700; font-size: 14px; color: var(--text-dark);"><?= Sanitizer::e($currentUser['full_name']) ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted); text-overflow: ellipsis; overflow: hidden;"><?= Sanitizer::e($currentUser['email']) ?></div>
                                </div>
                                <a href="<?= baseUrl('dashboard.php') ?>" class="dropdown-item">
                                    <i class="bi bi-grid-fill"></i> Student Dashboard
                                </a>
                                <a href="<?= baseUrl('bookmarks.php') ?>" class="dropdown-item">
                                    <i class="bi bi-bookmark-fill"></i> Saved Lessons
                                </a>
                                <a href="<?= baseUrl('notes.php') ?>" class="dropdown-item">
                                    <i class="bi bi-journal-text"></i> My Notes
                                </a>
                                <a href="<?= baseUrl('certificates.php') ?>" class="dropdown-item">
                                    <i class="bi bi-award-fill"></i> My Certificates & Badges
                                </a>
                                <a href="<?= baseUrl('profile.php') ?>" class="dropdown-item">
                                    <i class="bi bi-person-fill"></i> Profile & Settings
                                </a>
                                <?php if (Auth::canAccessAdmin()): ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="<?= baseUrl('admin/index.php') ?>" class="dropdown-item" style="color: var(--secondary); font-weight: 600;">
                                        <i class="bi bi-speedometer2"></i> Admin Console
                                    </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="<?= baseUrl('api/auth/logout.php') ?>" style="margin: 0;">
                                    <?= Csrf::field() ?>
                                    <button type="submit" class="dropdown-item" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer; color: var(--danger);">
                                        <i class="bi bi-box-arrow-right"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= baseUrl('login.php') ?>" class="btn btn-outline btn-sm">Log In</a>
                        <a href="<?= baseUrl('register.php') ?>" class="btn btn-primary btn-sm">Start Learning</a>
                    <?php endif; ?>

                    <!-- Mobile Hamburger Menu Button -->
                    <button type="button" class="mobile-nav-toggle" id="mobileMenuBtn" aria-label="Open Navigation">
                        <i class="bi bi-list"></i>
                    </button>
                </div>
            </nav>
        </div>

        <!-- Mobile Drawer -->
        <div id="mobileDrawer" style="display: none; background: #FFFFFF; border-top: 1px solid var(--border); padding: 16px 24px;">
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px;">
                <li><a href="<?= baseUrl('courses.php') ?>" class="sidebar-link"><i class="bi bi-collection-play sidebar-icon"></i> Explore Courses</a></li>
                <li><a href="<?= baseUrl('how-it-works.php') ?>" class="sidebar-link"><i class="bi bi-lightbulb sidebar-icon"></i> How It Works</a></li>
                <li><a href="<?= baseUrl('community.php') ?>" class="sidebar-link"><i class="bi bi-chat-square-dots sidebar-icon"></i> Community</a></li>
                <li><a href="<?= baseUrl('verify-certificate.php') ?>" class="sidebar-link"><i class="bi bi-shield-check sidebar-icon"></i> Verify Certificate</a></li>
                <li><a href="<?= baseUrl('about.php') ?>" class="sidebar-link"><i class="bi bi-info-circle sidebar-icon"></i> About</a></li>
                <?php if (!$currentUser): ?>
                    <li style="margin-top: 8px; display: flex; gap: 10px;">
                        <a href="<?= baseUrl('login.php') ?>" class="btn btn-outline" style="flex: 1;">Log In</a>
                        <a href="<?= baseUrl('register.php') ?>" class="btn btn-primary" style="flex: 1;">Start Learning</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </header>

    <script>
        // Mobile Drawer Toggle
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const drawer = document.getElementById('mobileDrawer');
            if (drawer) {
                drawer.style.display = (drawer.style.display === 'none') ? 'block' : 'none';
            }
        });
    </script>

    <main style="flex: 1;">
        <!-- Global Flash Messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div class="card" style="padding: 14px 20px; border-left: 4px solid var(--success); display: flex; align-items: center; gap: 12px; background: #F0FDF4;">
                    <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 1.25rem;"></i>
                    <div style="font-size: 14px; color: #166534; font-weight: 500;"><?= Sanitizer::e($_SESSION['flash_success']) ?></div>
                </div>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="container" style="margin-top: 20px;">
                <div class="card" style="padding: 14px 20px; border-left: 4px solid var(--danger); display: flex; align-items: center; gap: 12px; background: #FEF2F2;">
                    <i class="bi bi-exclamation-triangle-fill" style="color: var(--danger); font-size: 1.25rem;"></i>
                    <div style="font-size: 14px; color: #991B1B; font-weight: 500;"><?= Sanitizer::e($_SESSION['flash_error']) ?></div>
                </div>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
