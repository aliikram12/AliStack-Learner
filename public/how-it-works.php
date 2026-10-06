<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pageTitle = 'How AliStack Learner Works';
$pageDesc = 'Learn how our distraction-reduced video player, contextual AI tutor, and skill assessment workflows function.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container" style="max-width: 860px; text-align: center;">
        <span class="badge badge-secondary" style="margin-bottom: 12px;">Architecture & Workflow</span>
        <h1 style="font-size: 2.75rem; margin-bottom: 16px;">How It Works</h1>
        <p style="font-size: 1.15rem; color: var(--muted); line-height: 1.6;">
            A transparent overview of how AliStack Learner curates courses, supports your learning with AI, and validates your skills.
        </p>
    </div>
</div>

<div class="container" style="max-width: 920px; padding: 64px 24px;">
    <!-- Workflow steps -->
    <div style="display: flex; flex-direction: column; gap: 48px;">
        <div style="display: flex; gap: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--primary); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex-shrink: 0;">1</div>
            <div>
                <h3 style="margin-bottom: 8px;">Curated YouTube Playlists Embedded via Official Player API</h3>
                <p style="line-height: 1.6; color: var(--muted); margin-bottom: 12px;">
                    Administrators configure structured technical playlists. AliStack Learner embeds them using the official YouTube IFrame Player API. We comply fully with YouTube's policies: videos are never downloaded, scraped, or rehosted. The difference is our focused interface, which omits algorithm-driven recommendations, autoplay rabbit holes, and distracting comment feeds.
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--secondary); color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex-shrink: 0;">2</div>
            <div>
                <h3 style="margin-bottom: 8px;">Synchronized Playback Progress, Bookmarks & Autosaved Notes</h3>
                <p style="line-height: 1.6; color: var(--muted); margin-bottom: 12px;">
                    As you watch, your playback position is periodically reported and saved to MySQL. If you leave and return later, the player offers to resume right where you left off. Every lesson includes a private markdown-ready notebook with debounced autosaving, so your thoughts and code snippets are never lost.
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #2563EB; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex-shrink: 0;">3</div>
            <div>
                <h3 style="margin-bottom: 8px;">Contextual AI Learning Mentor (AliStack AI Tutor)</h3>
                <p style="line-height: 1.6; color: var(--muted); margin-bottom: 12px;">
                    Whenever you encounter confusion, click the floating "Ask AliStack AI" button. Our backend uses the secure AgentRouter API gateway to query the AI model. The tutor receives the exact course title, lesson overview, and your notes, enabling it to answer with pinpoint context. It supports English, Urdu, and Roman Urdu.
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #D97706; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex-shrink: 0;">4</div>
            <div>
                <h3 style="margin-bottom: 8px;">Course Completion & Server-Graded MCQ Assessments</h3>
                <p style="line-height: 1.6; color: var(--muted); margin-bottom: 12px;">
                    To protect academic rigor, students must complete 100% of required lessons before the final assessment is unlocked. Assessments are timed, with randomized questions and anti-cheating protections. Answer keys are strictly kept on the server.
                </p>
            </div>
        </div>

        <div style="display: flex; gap: 24px;">
            <div style="width: 48px; height: 48px; border-radius: 50%; background: #16A34A; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; flex-shrink: 0;">5</div>
            <div>
                <h3 style="margin-bottom: 8px;">Verifiable Certificates & Tiered Performance Badges</h3>
                <p style="line-height: 1.6; color: var(--muted); margin-bottom: 12px;">
                    Upon submitting, your score percentage is computed instantly by the server:
                </p>
                <ul style="padding-left: 20px; color: var(--muted); line-height: 1.8; margin-bottom: 12px;">
                    <li><strong>70% – 100%:</strong> Verified AliStack Course Certificate with unique ID and public verification link.</li>
                    <li><strong>60% – 69.99%:</strong> Silver Proficiency Badge.</li>
                    <li><strong>50% – 59.99%:</strong> Bronze Competency Badge.</li>
                    <li><strong>40% – 49.99%:</strong> Foundation Starter Badge.</li>
                </ul>
            </div>
        </div>
    </div>

    <div style="margin-top: 64px; text-align: center;">
        <a href="<?= baseUrl('courses.php') ?>" class="btn btn-primary btn-lg">
            Start Learning Now <i class="bi bi-arrow-right"></i>
        </a>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
