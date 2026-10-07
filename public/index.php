<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Auth;

$courseRepo = new CourseRepository();
$settingRepo = new SettingRepository();

// Get real data from database
$featuredCourses = $courseRepo->getAllPublished();
$stats = $settingRepo->getPlatformStats();

$pageTitle = 'Learn with Focus, Practice with Purpose';
$pageDesc = 'AliStack Learner is a distraction-reduced learning platform featuring structured video playlists, contextual AI tutoring, validated assessments, and verified credentials.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Modern Hero Section -->
<section class="hero-section" style="position: relative; overflow: hidden; background: radial-gradient(circle at 50% 0%, rgba(37,99,235,0.06) 0%, rgba(248,250,252,1) 75%); padding: 88px 0 96px; border-bottom: 1px solid var(--border-color);">
    <!-- Subtle Background Glow Elements -->
    <div style="position: absolute; top: -120px; left: 50%; transform: translateX(-50%); width: 720px; height: 380px; background: radial-gradient(circle, rgba(124, 58, 237, 0.08) 0%, rgba(37, 99, 235, 0.04) 50%, transparent 75%); pointer-events: none; filter: blur(50px);"></div>

    <div class="container" style="position: relative; z-index: 2; text-align: center; max-width: 980px;">
        <!-- Engine Pill -->
        <div data-animate="hero-badge" style="display: inline-flex; align-items: center; gap: 8px; background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.05); color: var(--primary); font-size: 13px; font-weight: 700; padding: 6px 16px; border-radius: 9999px; margin-bottom: 28px;">
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: #16A34A; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.2);"></span>
            <i class="bi bi-stars"></i> Powered by AliStack & AI Tutor Engine
        </div>

        <h1 data-animate="hero-title" style="font-size: clamp(2.4rem, 5vw, 3.75rem); font-weight: 800; letter-spacing: -0.03em; line-height: 1.15; margin-bottom: 24px; color: var(--dark);">
            Learn with Focus. <br>
            <span style="background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Practice with Purpose.</span> <br>
            Prove Your Skills.
        </h1>

        <p data-animate="hero-subtitle" style="font-size: clamp(1.05rem, 2vw, 1.25rem); color: var(--muted); line-height: 1.65; max-width: 760px; margin: 0 auto 40px; font-weight: 400;">
            Master high-impact technical skills through structured video playlists inside a focused, distraction-free environment with an on-demand AI Tutor, validated assessments, and verified credentials.
        </p>

        <!-- CTA Action Buttons -->
        <div data-animate="hero-ctas" style="display: flex; gap: 16px; justify-content: center; align-items: center; flex-wrap: wrap; margin-bottom: 56px;">
            <?php if (Auth::check()): ?>
                <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25);">
                    <i class="bi bi-speedometer2"></i> Open Student Dashboard
                </a>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-secondary btn-lg">
                    <i class="bi bi-compass"></i> Explore Courses
                </a>
            <?php else: ?>
                <a href="<?= baseUrl('register.php') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 8px 24px rgba(37, 99, 235, 0.25);">
                    <i class="bi bi-rocket-takeoff-fill"></i> Start Learning Now
                </a>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-secondary btn-lg">
                    <i class="bi bi-compass"></i> Explore All Courses
                </a>
            <?php endif; ?>
            <a href="<?= baseUrl('verify-certificate.php') ?>" class="btn btn-outline btn-lg" style="background: #FFFFFF;">
                <i class="bi bi-patch-check"></i> Verify Certificate
            </a>
        </div>

        <!-- Hero Feature Pill Indicators -->
        <div data-animate="hero-pills" style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; font-size: 13px; color: var(--muted); font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill" style="color: var(--success);"></i> No clickbait or algorithmic feed traps
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill" style="color: var(--success);"></i> Interactive AI Tutor in English & Roman Urdu
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="bi bi-check-circle-fill" style="color: var(--success);"></i> Tamper-proof verified certificates
            </div>
        </div>
    </div>
</section>

<!-- Real Platform Stats Section -->
<section style="padding: 48px 0; background: #FFFFFF; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
            <div class="stat-card" data-animate="fade-up" style="border: 1px solid var(--border-color); padding: 24px;">
                <div class="stat-icon blue" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-journal-code"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="font-size: 2rem;"><?= max(1, (int)$stats['published_courses']) ?></div>
                    <div class="stat-label" style="font-size: 13px; font-weight: 600;">Structured Courses</div>
                </div>
            </div>

            <div class="stat-card" data-animate="fade-up" style="border: 1px solid var(--border-color); padding: 24px;">
                <div class="stat-icon purple" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-collection-play"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="font-size: 2rem;"><?= max(12, (int)$stats['total_lessons']) ?></div>
                    <div class="stat-label" style="font-size: 13px; font-weight: 600;">Curated Video Lessons</div>
                </div>
            </div>

            <div class="stat-card" data-animate="fade-up" style="border: 1px solid var(--border-color); padding: 24px;">
                <div class="stat-icon green" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-award"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="font-size: 2rem;"><?= (int)$stats['certificates_issued'] ?></div>
                    <div class="stat-label" style="font-size: 13px; font-weight: 600;">Certificates Issued</div>
                </div>
            </div>

            <div class="stat-card" data-animate="fade-up" style="border: 1px solid var(--border-color); padding: 24px;">
                <div class="stat-icon amber" style="width: 52px; height: 52px; font-size: 1.5rem;">
                    <i class="bi bi-shield-check"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-value" style="font-size: 2rem;"><?= (int)$stats['badges_awarded'] ?></div>
                    <div class="stat-label" style="font-size: 13px; font-weight: 600;">Performance Badges</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Distraction-Reduced Philosophy Section -->
<section style="padding: 96px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 64px; align-items: center;">
            <div data-animate="fade-up">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-light); color: var(--primary); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bi bi-shield-lock-fill"></i> Distraction-Reduced Philosophy
                </div>
                <h2 style="font-size: clamp(1.8rem, 3vw, 2.5rem); font-weight: 800; letter-spacing: -0.02em; line-height: 1.25; margin-bottom: 20px; color: var(--dark);">
                    Why General Video Platforms Fail Serious Learners
                </h2>
                <p style="font-size: 15px; color: var(--muted); line-height: 1.7; margin-bottom: 16px;">
                    When watching tutorials on generic video sharing platforms, students are relentlessly pulled away by unrelated recommended videos, infinite comment sections, and notification algorithms engineered for distraction.
                </p>
                <p style="font-size: 15px; color: var(--muted); line-height: 1.7; margin-bottom: 28px;">
                    <strong>AliStack Learner</strong> transforms public YouTube playlists into a laser-focused workspace. You get structured progression, autosaved markdown notes, an AI tutor for real-time question answering, and formal assessments that validate your skills.
                </p>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 14px; font-weight: 600; color: var(--dark);">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px; margin-top: 1px;"></i>
                        <span>Zero algorithmic rabbit holes or trending feed traps</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 14px; font-weight: 600; color: var(--dark);">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px; margin-top: 1px;"></i>
                        <span>Integrated AI Mentor to unblock syntax errors and clarify concepts</span>
                    </div>
                    <div style="display: flex; align-items: flex-start; gap: 12px; font-size: 14px; font-weight: 600; color: var(--dark);">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px; margin-top: 1px;"></i>
                        <span>Server-graded MCQ assessments with unique verification links</span>
                    </div>
                </div>
            </div>

            <!-- Focus Player Live Mockup -->
            <div data-animate="fade-up" style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-xl); padding: 32px; box-shadow: var(--shadow-xl);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #EF4444;"></span>
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #F59E0B;"></span>
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: #10B981;"></span>
                        <span style="font-weight: 700; font-size: 14px; margin-left: 8px; color: var(--dark);">AliStack Learning Workspace</span>
                    </div>
                    <span class="badge badge-success"><i class="bi bi-shield-check"></i> 100% Focused</span>
                </div>

                <div style="background: #0F172A; border-radius: var(--radius-lg); padding: 24px; color: #FFFFFF; margin-bottom: 20px; box-shadow: inset 0 2px 8px rgba(0,0,0,0.4);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; font-weight: 600; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.5px;">ACTIVE LESSON 4 OF 12</span>
                        <span style="font-size: 12px; background: rgba(37,99,235,0.3); color: #93C5FD; padding: 2px 10px; border-radius: 9999px; font-weight: 600;">Technical Playlist</span>
                    </div>
                    <div style="font-size: 17px; font-weight: 700; margin-bottom: 16px; color: #F8FAFC;">Modern Backend Architecture & Secure API Design</div>
                    
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="flex: 1; height: 8px; background: rgba(255,255,255,0.12); border-radius: 4px; overflow: hidden;">
                            <div style="width: 75%; height: 100%; background: linear-gradient(90deg, #2563EB, #7C3AED); border-radius: 4px;"></div>
                        </div>
                        <span style="font-size: 12px; font-weight: 700; color: #93C5FD;">75% Complete</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; font-size: 13px;">
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 14px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-pencil-square" style="color: var(--primary); font-size: 18px;"></i>
                        <div>
                            <div style="font-weight: 700; color: var(--dark);">Timestamped Notes</div>
                            <div style="color: var(--muted); font-size: 11px;">Autosaved locally & in DB</div>
                        </div>
                    </div>
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 14px; border-radius: var(--radius-md); display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-robot" style="color: var(--secondary); font-size: 18px;"></i>
                        <div>
                            <div style="font-weight: 700; color: var(--dark);">AliStack AI Tutor</div>
                            <div style="color: var(--muted); font-size: 11px;">English & Roman Urdu</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses Section -->
<section style="padding: 96px 0; background: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 48px; flex-wrap: wrap; gap: 16px;">
            <div>
                <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--secondary-light); color: var(--secondary); font-size: 12px; font-weight: 700; padding: 4px 12px; border-radius: 9999px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="bi bi-stars"></i> Curated Learning
                </div>
                <h2 style="font-size: clamp(1.8rem, 3vw, 2.25rem); font-weight: 800; letter-spacing: -0.02em; color: var(--dark); margin: 0;">Featured Technical Courses</h2>
            </div>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-secondary">
                View All Courses <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($featuredCourses)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="bi bi-collection-play"></i></div>
                <div class="empty-title">Courses Coming Soon</div>
                <div class="empty-desc">Our instructors are curating structured courses. Check back shortly!</div>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 28px;">
                <?php foreach (array_slice($featuredCourses, 0, 3) as $c): ?>
                    <div class="course-card" data-animate="fade-up">
                        <div class="course-thumb-wrap">
                            <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%); display: flex; align-items: center; justify-content: center;">
                                <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(37,99,235,0.25); display: flex; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                                    <i class="bi bi-play-fill" style="font-size: 28px; color: #FFFFFF; margin-left: 3px;"></i>
                                </div>
                            </div>
                            <div class="course-thumb-overlay">
                                <span class="badge badge-primary"><?= ucfirst(Sanitizer::e($c['difficulty'])) ?></span>
                            </div>
                        </div>

                        <div class="course-body">
                            <div style="font-size: 12px; color: var(--primary); font-weight: 700; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px;">
                                <?= Sanitizer::e($c['category_name'] ?? 'Software Development') ?>
                            </div>
                            <h3 class="course-title">
                                <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>">
                                    <?= Sanitizer::e($c['title']) ?>
                                </a>
                            </h3>
                            <p class="course-desc"><?= Sanitizer::e($c['short_desc']) ?></p>

                            <div class="course-meta">
                                <span><i class="bi bi-clock"></i> <?= Sanitizer::e($c['estimated_duration']) ?></span>
                                <span><i class="bi bi-collection-play"></i> <?= (int)$c['lesson_count'] ?> Lessons</span>
                            </div>

                            <div style="margin-top: 20px;">
                                <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" class="btn btn-primary" style="width: 100%;">
                                    <span>Explore Syllabus</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- 5-Step Learning Pathway -->
<section style="padding: 96px 0;">
    <div class="container" style="text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(37,99,235,0.08); color: var(--primary); font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 9999px; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(37,99,235,0.2);">
            <i class="bi bi-signpost-split-fill"></i> Clear Pathway
        </div>
        <h2 style="font-size: clamp(2rem, 3.5vw, 2.75rem); font-weight: 800; letter-spacing: -0.025em; margin-bottom: 16px; color: var(--dark);">
            How AliStack Learner Works
        </h2>
        <p style="color: var(--muted); font-size: 16px; max-width: 640px; margin: 0 auto 56px; line-height: 1.6;">
            A structured five-stage learning system engineered to turn passive video watching into tangible, provable competence.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 20px; text-align: left;">
            <!-- Step 1 -->
            <div class="pathway-card" style="--card-gradient: linear-gradient(90deg, #2563EB, #3B82F6);">
                <div class="pathway-badge" style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); color: #1D4ED8;">
                    <i class="bi bi-play-circle-fill"></i>
                    <span class="pathway-step-num">1</span>
                </div>
                <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; color: var(--dark);">Enroll & Focus</h4>
                <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin: 0;">Enroll in curated courses with one click. Experience zero algorithmic distractions in our tailored classroom player.</p>
            </div>

            <!-- Step 2 -->
            <div class="pathway-card" style="--card-gradient: linear-gradient(90deg, #7C3AED, #9333EA);">
                <div class="pathway-badge" style="background: linear-gradient(135deg, #F5F3FF 0%, #EDE9FE 100%); color: #6D28D9;">
                    <i class="bi bi-journal-code"></i>
                    <span class="pathway-step-num">2</span>
                </div>
                <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; color: var(--dark);">Notes & Progress</h4>
                <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin: 0;">Take timestamped notes autosaved to MySQL. Bookmark tricky lessons and track your exact progress percentage.</p>
            </div>

            <!-- Step 3 -->
            <div class="pathway-card" style="--card-gradient: linear-gradient(90deg, #0284C7, #06B6D4);">
                <div class="pathway-badge" style="background: linear-gradient(135deg, #F0F9FF 0%, #E0F2FE 100%); color: #0284C7;">
                    <i class="bi bi-robot"></i>
                    <span class="pathway-step-num">3</span>
                </div>
                <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; color: var(--dark);">Ask AI Tutor</h4>
                <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin: 0;">Stuck on code or syntax? Open the AliStack AI Tutor anytime. Get contextual explanations in English or Roman Urdu.</p>
            </div>

            <!-- Step 4 -->
            <div class="pathway-card" style="--card-gradient: linear-gradient(90deg, #D97706, #F59E0B);">
                <div class="pathway-badge" style="background: linear-gradient(135deg, #FFFBEB 0%, #FEF3C7 100%); color: #B45309;">
                    <i class="bi bi-clipboard2-check-fill"></i>
                    <span class="pathway-step-num">4</span>
                </div>
                <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; color: var(--dark);">Take Assessment</h4>
                <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin: 0;">Complete required lessons to unlock timed course MCQ tests. Server-graded with tamper-proof validation.</p>
            </div>

            <!-- Step 5 -->
            <div class="pathway-card" style="--card-gradient: linear-gradient(90deg, #059669, #10B981);">
                <div class="pathway-badge" style="background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%); color: #047857;">
                    <i class="bi bi-award-fill"></i>
                    <span class="pathway-step-num">5</span>
                </div>
                <h4 style="font-size: 17px; font-weight: 800; margin-bottom: 8px; color: var(--dark);">Earn Credentials</h4>
                <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin: 0;">Score 70%+ for verified Certificates with unique URLs, or 40%-69.99% for Silver, Bronze, or Starter Performance Badges.</p>
            </div>
        </div>
    </div>
</section>

<!-- Credentialing Matrix -->
<section style="padding: 96px 0; background: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container" style="max-width: 960px;">
        <div style="text-align: center; margin-bottom: 48px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(22,163,74,0.08); color: #16A34A; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 9999px; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(22,163,74,0.2);">
                <i class="bi bi-shield-check"></i> Standardized Validation
            </div>
            <h2 style="font-size: clamp(2rem, 3.5vw, 2.5rem); font-weight: 800; letter-spacing: -0.025em; margin-bottom: 16px; color: var(--dark);">
                AliStack Credentialing Matrix
            </h2>
            <p style="color: var(--muted); font-size: 15.5px; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Every assessment is scored strictly on our servers. Your verified performance level dictates your earned credentials.
            </p>
        </div>

        <div class="matrix-container" data-animate="fade-up">
            <div class="matrix-header-gradient">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin: 0; color: #FFFFFF;">Performance Tiers & Criteria</h3>
                    <p style="font-size: 12.5px; color: rgba(255,255,255,0.7); margin: 2px 0 0;">Cryptographically sealed & verifiable credentials</p>
                </div>
                <span class="badge" style="background: rgba(255,255,255,0.15); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.25); font-size: 12px;">
                    <i class="bi bi-shield-lock-fill"></i> Server Graded
                </span>
            </div>

            <div class="table-responsive">
                <table class="data-table" style="margin: 0;">
                    <thead>
                        <tr style="background: #F8FAFC;">
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--dark);">Final Score</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--dark);">Earned Achievement</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--dark);">Credential Type</th>
                            <th style="padding: 16px 24px; font-size: 12px; font-weight: 800; text-transform: uppercase; color: var(--dark);">Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: linear-gradient(90deg, rgba(220,252,231,0.5) 0%, rgba(240,253,244,0.2) 100%);">
                            <td style="padding: 20px 24px; font-weight: 800; color: #15803D; font-size: 16px;">70% – 100%</td>
                            <td style="padding: 20px 24px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #22C55E; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                        <i class="bi bi-award-fill"></i>
                                    </div>
                                    <strong style="color: #15803D; font-size: 15px;">AliStack Course Certificate</strong>
                                </div>
                            </td>
                            <td style="padding: 20px 24px;"><span class="badge badge-success" style="font-weight: 700; padding: 6px 12px;">Official Certificate</span></td>
                            <td style="padding: 20px 24px; color: #15803D; font-size: 13.5px; font-weight: 600;">
                                <i class="bi bi-check2-circle"></i> Unique Verification URL & ID
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 18px 24px; font-weight: 700; color: var(--dark); font-size: 15px;">60% – 69.99%</td>
                            <td style="padding: 18px 24px;"><span class="badge badge-tier-silver" style="padding: 6px 14px; font-size: 12.5px;"><i class="bi bi-patch-check-fill"></i> Silver Proficiency Badge</span></td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);">Performance Badge</td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);"><i class="bi bi-person-badge"></i> Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td style="padding: 18px 24px; font-weight: 700; color: var(--dark); font-size: 15px;">50% – 59.99%</td>
                            <td style="padding: 18px 24px;"><span class="badge badge-tier-bronze" style="padding: 6px 14px; font-size: 12.5px;"><i class="bi bi-patch-check-fill"></i> Bronze Competency Badge</span></td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);">Performance Badge</td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);"><i class="bi bi-person-badge"></i> Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td style="padding: 18px 24px; font-weight: 700; color: var(--dark); font-size: 15px;">40% – 49.99%</td>
                            <td style="padding: 18px 24px;"><span class="badge badge-tier-starter" style="padding: 6px 14px; font-size: 12.5px;"><i class="bi bi-patch-check-fill"></i> Foundation Starter Badge</span></td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);">Performance Badge</td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);"><i class="bi bi-person-badge"></i> Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td style="padding: 18px 24px; color: var(--muted); font-weight: 600;">Below 40%</td>
                            <td style="padding: 18px 24px;"><span style="color: var(--muted); font-style: italic;">No Credential Awarded</span></td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);">Revision Guidance</td>
                            <td style="padding: 18px 24px; font-size: 13.5px; color: var(--muted);"><i class="bi bi-arrow-repeat"></i> Eligible for Retake (up to limit)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section with Interactive Accordion -->
<section style="padding: 96px 0;">
    <div class="container" style="max-width: 860px;">
        <div style="text-align: center; margin-bottom: 48px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(37,99,235,0.08); color: var(--primary); font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 9999px; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid rgba(37,99,235,0.2);">
                <i class="bi bi-question-circle-fill"></i> Clear Answers
            </div>
            <h2 style="font-size: clamp(2rem, 3.5vw, 2.5rem); font-weight: 800; letter-spacing: -0.025em; margin-bottom: 12px; color: var(--dark);">
                Frequently Asked Questions
            </h2>
            <p style="color: var(--muted); font-size: 15.5px; line-height: 1.6;">Everything you need to know about learning, AI assistance, and certificates on AliStack Learner.</p>
        </div>

        <div class="faq-accordion-group">
            <div class="faq-accordion-item active">
                <div class="faq-accordion-header" onclick="toggleFaq(this)">
                    <span>Are the video courses downloaded or re-hosted?</span>
                    <div class="faq-accordion-icon"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-accordion-body">
                    No. All courses use the official YouTube IFrame Player API in strict compliance with YouTube Terms of Service. We do not download, bypass ads, or rehost content. Instead, AliStack Learner provides a dedicated, focused classroom environment with synchronized notes, progress tracking, and AI tutoring.
                </div>
            </div>

            <div class="faq-accordion-item">
                <div class="faq-accordion-header" onclick="toggleFaq(this)">
                    <span>How does the AliStack AI Tutor work?</span>
                    <div class="faq-accordion-icon"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-accordion-body">
                    The AI Tutor uses the configured AgentRouter API with secure server-side proxying. It analyzes your active lesson title, outline, and questions to provide relevant guidance in English or Roman Urdu, without exposing private server credentials to the browser.
                </div>
            </div>

            <div class="faq-accordion-item">
                <div class="faq-accordion-header" onclick="toggleFaq(this)">
                    <span>Can anyone verify my certificate?</span>
                    <div class="faq-accordion-icon"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-accordion-body">
                    Yes. Every certificate issued includes a unique verification code and public URL. Employers and peers can verify certificate authenticity anytime at our Certificate Verification page.
                </div>
            </div>

            <div class="faq-accordion-item">
                <div class="faq-accordion-header" onclick="toggleFaq(this)">
                    <span>What if I don't achieve 70% on the assessment?</span>
                    <div class="faq-accordion-icon"><i class="bi bi-chevron-down"></i></div>
                </div>
                <div class="faq-accordion-body">
                    If you score between 40% and 69.99%, you automatically receive a verified performance badge (Silver, Bronze, or Starter) recognizing your foundation. You are also eligible to revise lessons and retake the assessment.
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function toggleFaq(headerEl) {
    var item = headerEl.parentElement;
    var wasActive = item.classList.contains('active');
    
    // Close other accordion items
    document.querySelectorAll('.faq-accordion-item').forEach(function(el) {
        el.classList.remove('active');
    });

    if (!wasActive) {
        item.classList.add('active');
    }
}
</script>

<!-- Call-to-Action Banner -->
<section style="padding: 80px 0; background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #7C3AED 100%); color: #FFFFFF; text-align: center; position: relative; overflow: hidden;">
    <div class="container" style="max-width: 760px; position: relative; z-index: 2;">
        <h2 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 800; letter-spacing: -0.02em; margin-bottom: 16px;">
            Ready to Prove Your Skills?
        </h2>
        <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.15rem; line-height: 1.6; margin-bottom: 36px;">
            Join AliStack Learner today. Learn without distractions, ask your AI Tutor anytime, and earn verifiable proof of your technical competence.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <?php if (Auth::check()): ?>
                <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-lg" style="background: #FFFFFF; color: var(--primary); font-weight: 700; padding: 14px 32px; border-radius: var(--radius-md); box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                    <i class="bi bi-speedometer2"></i> Return to Dashboard
                </a>
            <?php else: ?>
                <a href="<?= baseUrl('register.php') ?>" class="btn btn-lg" style="background: #FFFFFF; color: var(--primary); font-weight: 700; padding: 14px 32px; border-radius: var(--radius-md); box-shadow: 0 8px 24px rgba(0,0,0,0.15);">
                    <i class="bi bi-rocket-takeoff-fill"></i> Create Free Account
                </a>
                <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-lg" style="border-color: rgba(255,255,255,0.4); color: #FFFFFF;">
                    Browse Courses
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
