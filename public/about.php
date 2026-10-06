<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'About AliStack Learner';
$pageDesc = 'Discover the mission, philosophy, and architectural vision behind AliStack Learner and AliStack.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container" style="max-width: 860px; text-align: center;">
        <span class="badge badge-primary" style="margin-bottom: 12px;">About the Platform</span>
        <h1 style="font-size: 2.75rem; margin-bottom: 16px;">Empowering Self-Learners Through Focus & AI</h1>
        <p style="font-size: 1.15rem; color: var(--muted); line-height: 1.6;">
            AliStack Learner was built to solve the most prevalent problem in online self-education: endless distractions, fragmented learning paths, and a lack of verifiable skill assessment.
        </p>
    </div>
</div>

<div class="container" style="max-width: 860px; padding: 64px 24px;">
    <div style="margin-bottom: 48px;">
        <h2 style="font-size: 1.75rem; margin-bottom: 16px;">Our Core Vision</h2>
        <p style="line-height: 1.7; font-size: 15px;">
            The internet contains world-class educational content created by generous developers and educators on YouTube. However, general-purpose video platforms are engineered for entertainment and view duration—not retention, practice, or structured assessment.
        </p>
        <p style="line-height: 1.7; font-size: 15px;">
            <strong>AliStack Learner</strong> transforms public educational playlists into professional courses. By surrounding videos with note-taking tools, lesson completion tracking, contextual AI tutoring, and rigorous MCQ testing, we help students progress from passive viewers into confident software engineers.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 48px;">
        <div class="card" style="padding: 24px;">
            <div style="font-size: 24px; color: var(--primary); margin-bottom: 12px;"><i class="bi bi-bullseye"></i></div>
            <h3 style="font-size: 1.2rem; margin-bottom: 8px;">Learn with Focus</h3>
            <p style="font-size: 14px; margin: 0; color: var(--muted);">
                No recommendation algorithms or autoplay feeds to divert attention away from what you came to learn.
            </p>
        </div>
        <div class="card" style="padding: 24px;">
            <div style="font-size: 24px; color: var(--secondary); margin-bottom: 12px;"><i class="bi bi-robot"></i></div>
            <h3 style="font-size: 1.2rem; margin-bottom: 8px;">Practice with Purpose</h3>
            <p style="font-size: 14px; margin: 0; color: var(--muted);">
                Contextual AI assistance in English and Roman Urdu, step-by-step code guidance, and lesson note autosaving.
            </p>
        </div>
    </div>

    <div style="margin-bottom: 48px;">
        <h2 style="font-size: 1.75rem; margin-bottom: 16px;">About AliStack</h2>
        <p style="line-height: 1.7; font-size: 15px;">
            AliStack is a technology enterprise focused on engineering resilient software architectures, open learning tools, and intelligent agentic workflows. AliStack Learner is designed and maintained by AliStack as part of our commitment to accessible, high-standard technical education.
        </p>
    </div>

    <div style="text-align: center; padding: 40px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
        <h3 style="margin-bottom: 12px;">Start Your Learning Journey Today</h3>
        <p style="margin-bottom: 24px; color: var(--muted);">Join hundreds of focused learners mastering software development.</p>
        <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary">
            Explore Courses <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
