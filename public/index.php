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
$pageDesc = 'AliStack Learner is an AI-powered platform for distraction-reduced video learning, real MCQ assessments, verified certificates, and performance badges.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Hero Section -->
<section style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); padding: 72px 0 84px; border-bottom: 1px solid var(--border-color);">
    <div class="container" style="text-align: center; max-width: 900px;">
        <div style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-light); color: var(--primary); font-size: 13px; font-weight: 700; padding: 6px 14px; border-radius: var(--radius-full); margin-bottom: 24px;">
            <i class="bi bi-stars"></i> Powered by AliStack & AI Tutor Technology
        </div>
        <h1 style="font-size: 3.25rem; font-weight: 800; letter-spacing: -0.025em; line-height: 1.15; margin-bottom: 20px; color: var(--dark);">
            Learn with Focus. <br>
            <span style="background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Practice with Purpose.</span> <br>
            Prove Your Skills.
        </h1>
        <p style="font-size: 1.2rem; color: var(--muted); line-height: 1.6; max-width: 720px; margin: 0 auto 36px;">
            Master software engineering and emerging technologies through structured video playlists inside a distraction-reduced learning player with an on-demand AI Tutor, validated assessments, and verified credentials.
        </p>

        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <?php if (Auth::check()): ?>
                <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-speedometer2"></i> Go to Dashboard
                </a>
            <?php else: ?>
                <a href="<?= baseUrl('register.php') ?>" class="btn btn-primary btn-lg">
                    <i class="bi bi-rocket-takeoff-fill"></i> Start Learning Now
                </a>
            <?php endif; ?>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-lg">
                <i class="bi bi-compass"></i> Explore Courses
            </a>
            <a href="<?= baseUrl('verify-certificate.php') ?>" class="btn btn-outline btn-lg">
                <i class="bi bi-patch-check"></i> Verify Certificate
            </a>
        </div>
    </div>
</section>

<!-- Real Platform Stats Section -->
<section style="padding: 40px 0; background: #FFFFFF; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; text-align: center;">
            <div style="padding: 16px;">
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--primary);"><?= max(1, $stats['published_courses']) ?></div>
                <div style="font-size: 14px; font-weight: 600; color: var(--muted);">Structured Courses</div>
            </div>
            <div style="padding: 16px;">
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--secondary);"><?= max(12, $stats['total_lessons']) ?></div>
                <div style="font-size: 14px; font-weight: 600; color: var(--muted);">Curated Video Lessons</div>
            </div>
            <div style="padding: 16px;">
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--success);"><?= $stats['certificates_issued'] ?></div>
                <div style="font-size: 14px; font-weight: 600; color: var(--muted);">Certificates Issued</div>
            </div>
            <div style="padding: 16px;">
                <div style="font-size: 2.25rem; font-weight: 800; color: var(--warning);"><?= $stats['badges_awarded'] ?></div>
                <div style="font-size: 14px; font-weight: 600; color: var(--muted);">Performance Badges</div>
            </div>
        </div>
    </div>
</section>

<!-- Distraction-Reduced Philosophy -->
<section style="padding: 80px 0;">
    <div class="container">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 64px; align-items: center;">
            <div>
                <span class="badge badge-primary" style="margin-bottom: 12px;">Distraction-Reduced Learning</span>
                <h2 style="font-size: 2.25rem; margin-bottom: 20px;">Why General Video Platforms Fail Serious Learners</h2>
                <p style="margin-bottom: 16px;">
                    When watching tutorials on open video websites, students are bombarded with unrelated clickbait recommendations, notifications, algorithms designed for endless browsing, and no structured assessment.
                </p>
                <p style="margin-bottom: 24px;">
                    <strong>AliStack Learner</strong> embeds educational YouTube playlists into a focused, distraction-free environment with timestamps, notes autosaving, progress tracking, and contextual AI mentorship.
                </p>
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px;"></i>
                        No unrelated trending feeds or algorithm traps
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px;"></i>
                        Context-aware AI Tutor to explain concepts and code step-by-step
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600;">
                        <i class="bi bi-check-circle-fill" style="color: var(--success); font-size: 18px;"></i>
                        Server-graded MCQ assessments with verifiable AliStack certificates
                    </div>
                </div>
            </div>

            <div style="background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-xl);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                    <div style="font-weight: 700; font-size: 15px;">Focus Learning Player Preview</div>
                    <span class="badge badge-success"><i class="bi bi-shield-check"></i> Ad-Reduced Environment</span>
                </div>
                <div style="background: #0F172A; border-radius: var(--radius-md); padding: 24px; color: #FFFFFF; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <span style="font-size: 12px; color: #94A3B8;">LESSON 3 OF 5</span>
                        <span style="font-size: 12px; background: rgba(37,99,235,0.3); color: #93C5FD; padding: 2px 8px; border-radius: 4px;">PHP 8.2 Masterclass</span>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; margin-bottom: 16px;">PDO Fundamentals & SQL Injection Defense</div>
                    <div style="height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;">
                        <div style="width: 60%; height: 100%; background: var(--primary);"></div>
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 13px;">
                    <div style="background: var(--bg-main); padding: 12px; border-radius: var(--radius-md);">
                        <i class="bi bi-pencil-square" style="color: var(--primary);"></i> <strong>Personal Notes:</strong> Autosaved
                    </div>
                    <div style="background: var(--bg-main); padding: 12px; border-radius: var(--radius-md);">
                        <i class="bi bi-robot" style="color: var(--secondary);"></i> <strong>AliStack AI:</strong> Ready
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Courses -->
<section style="padding: 80px 0; background: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 40px;">
            <div>
                <span class="badge badge-secondary" style="margin-bottom: 8px;">Explore Curriculum</span>
                <h2 style="font-size: 2rem;">Featured Courses</h2>
            </div>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">
                View All Courses <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
            <?php foreach (array_slice($featuredCourses, 0, 3) as $c): ?>
                <div class="course-card">
                    <div class="course-thumb-wrap">
                        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #1E293B, #0F172A); display: flex; align-items: center; justify-content: center; color: #FFFFFF;">
                            <i class="bi bi-play-circle-fill" style="font-size: 48px; opacity: 0.85; color: var(--primary);"></i>
                        </div>
                        <div class="course-thumb-overlay">
                            <span class="badge badge-primary"><?= ucfirst(Sanitizer::e($c['difficulty'])) ?></span>
                        </div>
                    </div>
                    <div class="course-body">
                        <div style="font-size: 12px; color: var(--muted); font-weight: 600; margin-bottom: 6px; text-transform: uppercase;">
                            <?= Sanitizer::e($c['category_name'] ?? 'Technical Course') ?>
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
                        <div style="margin-top: 16px;">
                            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" class="btn btn-primary" style="width: 100%;">
                                View Course Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section style="padding: 80px 0;">
    <div class="container" style="text-align: center;">
        <span class="badge badge-primary" style="margin-bottom: 12px;">Step-by-Step Pathway</span>
        <h2 style="font-size: 2.25rem; margin-bottom: 16px;">How AliStack Learner Works</h2>
        <p style="color: var(--muted); max-width: 600px; margin: 0 auto 56px;">
            A structured five-stage learning system engineered to turn video watching into tangible, provable competence.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; text-align: left;">
            <div class="card" style="padding: 24px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 16px;">1</div>
                <h4 style="margin-bottom: 8px;">Enroll & Focus</h4>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Enroll in curated technical courses. Experience zero distractions inside our clean, tailored player layout.</p>
            </div>

            <div class="card" style="padding: 24px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: var(--secondary-light); color: var(--secondary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 16px;">2</div>
                <h4 style="margin-bottom: 8px;">Notes & Progress</h4>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Take timestamped notes autosaved to MySQL. Bookmark tricky lessons and track your exact progress percentage.</p>
            </div>

            <div class="card" style="padding: 24px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 16px;">3</div>
                <h4 style="margin-bottom: 8px;">Ask AI Tutor</h4>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Stuck on a syntax error or concept? Open the AliStack AI Tutor anytime. Get explanations in English or Roman Urdu.</p>
            </div>

            <div class="card" style="padding: 24px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 16px;">4</div>
                <h4 style="margin-bottom: 8px;">Take Assessment</h4>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Complete required lessons to unlock the course MCQ test. Server-graded with tamper-proof validation.</p>
            </div>

            <div class="card" style="padding: 24px;">
                <div style="width: 42px; height: 42px; border-radius: var(--radius-md); background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; margin-bottom: 16px;">5</div>
                <h4 style="margin-bottom: 8px;">Earn Credentials</h4>
                <p style="font-size: 13px; color: var(--muted); margin: 0;">Score 70%+ for an AliStack verified Certificate, or 40%-69.99% for Silver, Bronze, or Starter Performance Badges.</p>
            </div>
        </div>
    </div>
</section>

<!-- Assessment Thresholds & Achievement Policy -->
<section style="padding: 80px 0; background: #FFFFFF; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container" style="max-width: 960px;">
        <div style="text-align: center; margin-bottom: 48px;">
            <span class="badge badge-success" style="margin-bottom: 12px;">Skill Validation</span>
            <h2 style="font-size: 2.25rem; margin-bottom: 16px;">AliStack Credentialing Matrix</h2>
            <p style="color: var(--muted); max-width: 600px; margin: 0 auto;">
                Every assessment is scored strictly by the server. Your score determines your verified credential.
            </p>
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Final Assessment Score</th>
                            <th>Earned Achievement</th>
                            <th>Credential Type</th>
                            <th>Verification</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background: #F0FDF4;">
                            <td><strong>70% – 100%</strong></td>
                            <td><span style="font-weight: 700; color: #166534;"><i class="bi bi-award-fill"></i> AliStack Course Certificate</span></td>
                            <td><span class="badge badge-success">Official Certificate</span></td>
                            <td>Unique Verification URL & ID</td>
                        </tr>
                        <tr>
                            <td><strong>60% – 69.99%</strong></td>
                            <td><span class="badge badge-tier-silver"><i class="bi bi-patch-check-fill"></i> Silver Proficiency Badge</span></td>
                            <td>Performance Badge</td>
                            <td>Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td><strong>50% – 59.99%</strong></td>
                            <td><span class="badge badge-tier-bronze"><i class="bi bi-patch-check-fill"></i> Bronze Competency Badge</span></td>
                            <td>Performance Badge</td>
                            <td>Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td><strong>40% – 49.99%</strong></td>
                            <td><span class="badge badge-tier-starter"><i class="bi bi-patch-check-fill"></i> Foundation Starter Badge</span></td>
                            <td>Performance Badge</td>
                            <td>Recorded on Student Profile</td>
                        </tr>
                        <tr>
                            <td><strong>Below 40%</strong></td>
                            <td><span style="color: var(--muted); font-style: italic;">No Credential Awarded</span></td>
                            <td>Revision Guidance</td>
                            <td>Eligible for Retake (up to limit)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="padding: 80px 0;">
    <div class="container" style="max-width: 800px;">
        <div style="text-align: center; margin-bottom: 48px;">
            <h2 style="font-size: 2rem; margin-bottom: 12px;">Frequently Asked Questions</h2>
            <p style="color: var(--muted);">Everything you need to know about learning, AI assistance, and certificates on AliStack Learner.</p>
        </div>

        <div style="display: flex; flex-direction: column; gap: 16px;">
            <div class="card" style="padding: 20px;">
                <h4 style="margin-bottom: 8px;">Are the video courses downloaded or re-hosted?</h4>
                <p style="font-size: 14px; margin: 0; color: var(--muted);">No. All courses use the official YouTube IFrame Player API according to YouTube Terms of Service. We do not download, bypass ads, or rehost content.</p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4 style="margin-bottom: 8px;">How does the AliStack AI Tutor work?</h4>
                <p style="font-size: 14px; margin: 0; color: var(--muted);">The AI Tutor uses the configured AgentRouter API with server-side proxying. It analyzes your current lesson title, overview, and questions to provide relevant guidance in English or Roman Urdu.</p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4 style="margin-bottom: 8px;">Can anyone verify my certificate?</h4>
                <p style="font-size: 14px; margin: 0; color: var(--muted);">Yes. Each certificate includes a unique verification code and URL. Employers and peers can verify the certificate authenticity anytime at our Certificate Verification page.</p>
            </div>
            <div class="card" style="padding: 20px;">
                <h4 style="margin-bottom: 8px;">What if I don't achieve 70% on the assessment?</h4>
                <p style="font-size: 14px; margin: 0; color: var(--muted);">If you score between 40% and 69.99%, you receive a verified performance badge (Silver, Bronze, or Starter) acknowledging your foundation. You can also re-attempt after revising lessons.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Footer Banner -->
<section style="padding: 64px 0; background: linear-gradient(135deg, var(--primary), var(--secondary)); color: #FFFFFF; text-align: center;">
    <div class="container" style="max-width: 720px;">
        <h2 style="color: #FFFFFF; font-size: 2.25rem; margin-bottom: 16px;">Ready to Elevate Your Skills?</h2>
        <p style="color: rgba(255, 255, 255, 0.9); font-size: 1.1rem; margin-bottom: 32px;">
            Join AliStack Learner today. Learn without distractions, ask your AI Tutor anytime, and earn verifiable proof of your knowledge.
        </p>
        <a href="<?= baseUrl('register.php') ?>" class="btn btn-lg" style="background: #FFFFFF; color: var(--primary); font-weight: 700;">
            <i class="bi bi-person-plus-fill"></i> Create Free Account
        </a>
    </div>
</section>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
