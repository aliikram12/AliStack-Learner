<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'How AliStack Learner Works';
$pageDesc = 'Learn how our distraction-reduced video player, contextual AI tutor, and skill assessment workflows function.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.06) 0%, rgba(248,250,252,1) 80%); border-bottom: 1px solid var(--border-color); padding: 72px 0;">
    <div class="container" style="max-width: 860px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--secondary-light); color: var(--secondary); font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; margin-bottom: 16px; text-transform: uppercase;">
            <i class="bi bi-gear-wide-connected"></i> Architecture & Workflow
        </div>
        <h1 style="font-size: clamp(2.2rem, 4vw, 3rem); font-weight: 800; letter-spacing: -0.02em; margin-bottom: 16px; color: var(--dark);">
            How AliStack Learner Works
        </h1>
        <p style="font-size: 16px; color: var(--muted); line-height: 1.6; max-width: 680px; margin: 0 auto;">
            A transparent overview of how our platform curates playlists, provides real-time AI assistance, and mathematically validates skills.
        </p>
    </div>
</div>

<div class="container" style="max-width: 900px; padding: 64px 20px 96px;">
    <!-- Workflow Steps Grid -->
    <div style="display: flex; flex-direction: column; gap: 32px;">
        <!-- Step 1 -->
        <div class="card" data-animate="fade-up" style="padding: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); display: flex; gap: 24px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(37,99,235,0.15);">
                1
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 10px;">
                    Curated YouTube Playlists Embedded via Official Player API
                </h3>
                <p style="line-height: 1.7; color: var(--muted); font-size: 14.5px; margin: 0;">
                    Instructors organize structured technical curricula. AliStack Learner streams them using the official YouTube IFrame Player API. We fully honor YouTube's policies: video assets are never downloaded, scraped, or rehosted. What changes is your workspace: zero algorithmic suggestions, no autoplay rabbit holes, and zero distraction feeds.
                </p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="card" data-animate="fade-up" style="padding: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); display: flex; gap: 24px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: #F5F3FF; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(124,58,237,0.15);">
                2
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 10px;">
                    Synchronized Progress, Bookmarks & Autosaved Notes
                </h3>
                <p style="line-height: 1.7; color: var(--muted); font-size: 14.5px; margin: 0;">
                    As you study, playback position is periodically transmitted to the server. If you leave and return later, the player automatically offers to resume at your exact timestamp. Each lesson includes a dedicated notes panel with debounced autosaving, ensuring your notes and code snippets are preserved.
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="card" data-animate="fade-up" style="padding: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); display: flex; gap: 24px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: #EFF6FF; color: #1D4ED8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(29,78,216,0.15);">
                3
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 10px;">
                    Contextual AI Learning Mentor (AliStack AI Tutor)
                </h3>
                <p style="line-height: 1.7; color: var(--muted); font-size: 14.5px; margin: 0;">
                    Whenever you hit a roadblock, open the floating AliStack AI widget. Our backend routes queries through the AgentRouter API with server-side proxying. The tutor is provided with the current course title, lesson overview, and notes to deliver relevant answers in English, Urdu, or Roman Urdu.
                </p>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="card" data-animate="fade-up" style="padding: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); display: flex; gap: 24px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: #FEF3C7; color: #B45309; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(180,83,9,0.15);">
                4
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 10px;">
                    Course Completion & Server-Graded MCQ Assessments
                </h3>
                <p style="line-height: 1.7; color: var(--muted); font-size: 14.5px; margin: 0;">
                    To maintain academic rigor, students must complete required course lessons before unlocking the comprehensive assessment. Assessments are timed, anti-cheating protected, and evaluated securely on our server.
                </p>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="card" data-animate="fade-up" style="padding: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); display: flex; gap: 24px; align-items: flex-start; box-shadow: var(--shadow-sm);">
            <div style="width: 52px; height: 52px; border-radius: 12px; background: #DCFCE7; color: #15803D; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(21,128,61,0.15);">
                5
            </div>
            <div>
                <h3 style="font-size: 1.3rem; font-weight: 800; color: var(--dark); margin: 0 0 10px;">
                    Verifiable Certificates & Tiered Performance Badges
                </h3>
                <p style="line-height: 1.7; color: var(--muted); font-size: 14.5px; margin-bottom: 12px;">
                    Your final assessment score determines your recognized achievement:
                </p>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13.5px;">
                    <div style="background: #F0FDF4; border: 1px solid #BBF7D0; padding: 12px 14px; border-radius: var(--radius-md); color: #166534;">
                        <strong>70% – 100%:</strong> Verified AliStack Certificate
                    </div>
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 12px 14px; border-radius: var(--radius-md);">
                        <strong>60% – 69.99%:</strong> Silver Proficiency Badge
                    </div>
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 12px 14px; border-radius: var(--radius-md);">
                        <strong>50% – 59.99%:</strong> Bronze Competency Badge
                    </div>
                    <div style="background: var(--bg-main); border: 1px solid var(--border-color); padding: 12px 14px; border-radius: var(--radius-md);">
                        <strong>40% – 49.99%:</strong> Foundation Starter Badge
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-top: 64px; text-align: center;">
        <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-lg" style="box-shadow: 0 8px 24px rgba(37,99,235,0.25);">
            Start Learning Now <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
