<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'About AliStack Learner';
$pageDesc = 'Discover the mission, philosophy, and architectural vision behind AliStack Learner and AliStack.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.06) 0%, rgba(248,250,252,1) 80%); border-bottom: 1px solid var(--border-color); padding: 72px 0;">
    <div class="container" style="max-width: 860px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-light); color: var(--primary); font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; margin-bottom: 16px; text-transform: uppercase;">
            <i class="bi bi-info-circle-fill"></i> About the Platform
        </div>
        <h1 style="font-size: clamp(2.2rem, 4vw, 3rem); font-weight: 800; letter-spacing: -0.02em; margin-bottom: 16px; color: var(--dark);">
            Empowering Self-Learners Through Focus & AI
        </h1>
        <p style="font-size: 16px; color: var(--muted); line-height: 1.6; max-width: 680px; margin: 0 auto;">
            AliStack Learner was built to solve the most prevalent problem in online education: endless distractions, fragmented playlists, and an absence of verifiable skill proof.
        </p>
    </div>
</div>

<div class="container" style="max-width: 880px; padding: 64px 20px 96px;">
    <div class="card" data-animate="fade-up" style="padding: 40px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); margin-bottom: 40px; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 16px; color: var(--dark);">Our Core Vision</h2>
        <p style="line-height: 1.7; font-size: 15px; color: var(--muted); margin-bottom: 16px;">
            The internet contains remarkable technical educational content created by generous developers and educators on YouTube. However, general-purpose video streaming platforms are engineered for entertainment and watch time—not retention, deliberate practice, or structured assessment.
        </p>
        <p style="line-height: 1.7; font-size: 15px; color: var(--muted); margin: 0;">
            <strong>AliStack Learner</strong> transforms public educational playlists into professional courses. By surrounding videos with note-taking tools, progress persistence, contextual AI tutoring, and rigorous MCQ testing, we help students progress from passive viewers into capable engineers.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
        <div class="card" data-animate="fade-up" style="padding: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px;">
                <i class="bi bi-bullseye"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 10px; color: var(--dark);">Learn with Focus</h3>
            <p style="font-size: 14px; margin: 0; color: var(--muted); line-height: 1.6;">
                No recommendation algorithms or autoplay feeds to divert attention away from your educational goals.
            </p>
        </div>

        <div class="card" data-animate="fade-up" style="padding: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm);">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #F5F3FF; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px;">
                <i class="bi bi-robot"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 10px; color: var(--dark);">Practice with Purpose</h3>
            <p style="font-size: 14px; margin: 0; color: var(--muted); line-height: 1.6;">
                Contextual AI assistance in English and Roman Urdu, step-by-step code guidance, and lesson note autosaving.
            </p>
        </div>
    </div>

    <div class="card" data-animate="fade-up" style="padding: 40px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); margin-bottom: 48px; box-shadow: var(--shadow-sm);">
        <h2 style="font-size: 1.6rem; font-weight: 800; margin-bottom: 16px; color: var(--dark);">About AliStack</h2>
        <p style="line-height: 1.7; font-size: 15px; color: var(--muted); margin: 0;">
            AliStack is a technology enterprise focused on engineering resilient software architectures, open learning tools, and intelligent agentic workflows. AliStack Learner is designed and maintained by AliStack as part of our commitment to accessible, high-standard technical education.
        </p>
    </div>

    <div style="text-align: center; padding: 48px 32px; background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-md);">
        <h3 style="font-size: 1.5rem; font-weight: 800; margin-bottom: 10px; color: var(--dark);">Start Your Learning Journey Today</h3>
        <p style="margin-bottom: 24px; color: var(--muted); font-size: 15px;">Join focused learners building real skills on AliStack Learner.</p>
        <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
            Explore All Courses <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
