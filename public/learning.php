<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Repositories\CourseRepository;
use App\Repositories\AssessmentRepository;

Auth::requireAuth();

$userId = (int)Auth::id();
$courseId = !empty($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
$lessonId = !empty($_GET['lesson_id']) ? (int)$_GET['lesson_id'] : 0;

$courseRepo = new CourseRepository();
$assessmentRepo = new AssessmentRepository();

// Validate course & enrollment
$course = $courseRepo->findById($courseId);
if (!$course) {
    header('Location: ' . baseUrl('courses.php'));
    exit;
}

$enrollment = $courseRepo->getEnrollment($userId, $courseId);
if (!$enrollment) {
    // Automatically enroll if accessing
    $courseRepo->enroll($userId, $courseId);
    $enrollment = $courseRepo->getEnrollment($userId, $courseId);
}

// Get all lessons for this course
$lessons = $courseRepo->getLessons($courseId, true);
if (empty($lessons)) {
    die("This course has no published lessons available yet.");
}

// Determine active lesson
$currentLesson = null;
if ($lessonId) {
    foreach ($lessons as $l) {
        if ((int)$l['id'] === $lessonId) {
            $currentLesson = $l;
            break;
        }
    }
}

if (!$currentLesson) {
    // If user has a last_lesson_id saved in enrollment, resume that
    if (!empty($enrollment['last_lesson_id'])) {
        foreach ($lessons as $l) {
            if ((int)$l['id'] === (int)$enrollment['last_lesson_id']) {
                $currentLesson = $l;
                break;
            }
        }
    }
}

// Fallback to first lesson
if (!$currentLesson) {
    $currentLesson = $lessons[0];
}

$currentLessonId = (int)$currentLesson['id'];

// Get user progress map for all lessons in this course
$progressMap = $courseRepo->getLessonProgress($userId, $courseId);
$currentProgress = $progressMap[$currentLessonId] ?? null;
$startPos = $currentProgress ? (int)$currentProgress['last_position_seconds'] : 0;
$isCompleted = $currentProgress && !empty($currentProgress['is_completed']);

// Get notes and bookmark status for this lesson
$note = $courseRepo->getLessonNote($userId, $currentLessonId);
$isBookmarked = $courseRepo->isLessonBookmarked($userId, $currentLessonId);

// Get course resources
$resources = $courseRepo->getResources($courseId, $currentLessonId);

// Assessment for this course
$assessment = $assessmentRepo->findByCourseId($courseId);

// Find index for next and previous lesson navigation
$currentIndex = 0;
foreach ($lessons as $i => $l) {
    if ((int)$l['id'] === $currentLessonId) {
        $currentIndex = $i;
        break;
    }
}
$prevLesson = ($currentIndex > 0) ? $lessons[$currentIndex - 1] : null;
$nextLesson = ($currentIndex < count($lessons) - 1) ? $lessons[$currentIndex + 1] : null;

$extraCss = ['player.css'];
$extraJs = ['player.js', 'ai-tutor.js'];
$pageTitle = $currentLesson['title'] . ' - ' . $course['title'];

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<!-- Hidden Configuration for JS -->
<div id="playerConfig" 
     data-course-id="<?= $courseId ?>" 
     data-lesson-id="<?= $currentLessonId ?>"></div>

<!-- Learning Top Bar -->
<div style="background: #111827; border-bottom: 1px solid #1F2937; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; color: #FFFFFF;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <a href="<?= baseUrl('course-details.php?slug=' . $course['slug']) ?>" class="btn btn-ghost btn-sm" style="color: #94A3B8; padding: 6px 10px;">
            <i class="bi bi-arrow-left"></i> Course Overview
        </a>
        <div style="height: 16px; width: 1px; background: #374151;"></div>
        <div>
            <div style="font-size: 11px; text-transform: uppercase; color: #94A3B8; font-weight: 700; letter-spacing: 0.05em;">FOCUSED LEARNING</div>
            <div style="font-family: var(--font-heading); font-size: 14px; font-weight: 700; color: #FFFFFF; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 480px;">
                <?= Sanitizer::e($course['title']) ?>
            </div>
        </div>
    </div>

    <div style="display: flex; align-items: center; gap: 18px;">
        <div style="display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: #94A3B8;">
            <span>Progress: <strong style="color: #60A5FA;"><?= number_format((float)$enrollment['progress_percent'], 0) ?>%</strong></span>
            <div class="progress-bar" style="width: 100px; height: 6px; background: #374151;">
                <div class="progress-fill" style="width: <?= (float)$enrollment['progress_percent'] ?>%;"></div>
            </div>
        </div>

        <button type="button" id="topAiLaunchBtn" class="btn btn-secondary btn-sm" style="background: linear-gradient(135deg, #2563EB, #7C3AED); border: none; padding: 6px 14px;">
            <i class="bi bi-stars"></i> Ask AliStack AI
        </button>
    </div>
</div>

<div class="player-layout">
    <!-- Main Left Area: Video Player & Details -->
    <div class="player-main-area">
        <!-- 16:9 Video Container with Rounded Borders -->
        <div class="video-container" style="background: #000000; overflow: hidden;">
            <div id="youtubePlayer" 
                 data-video-id="<?= Sanitizer::e($currentLesson['youtube_video_id']) ?>" 
                 data-start-pos="<?= $startPos ?>"></div>
        </div>

        <!-- Controls Bar -->
        <div class="player-controls-bar">
            <div class="lesson-nav-buttons">
                <?php if ($prevLesson): ?>
                    <a href="<?= baseUrl("learning.php?course_id={$courseId}&lesson_id={$prevLesson['id']}") ?>" class="btn-player">
                        <i class="bi bi-arrow-left"></i> Previous
                    </a>
                <?php else: ?>
                    <button class="btn-player" disabled><i class="bi bi-arrow-left"></i> Previous</button>
                <?php endif; ?>

                <button type="button" id="markCompleteBtn" class="btn-player <?= $isCompleted ? 'btn-complete' : '' ?>">
                    <i class="bi <?= $isCompleted ? 'bi-check-circle-fill' : 'bi-check2' ?>"></i> 
                    <?= $isCompleted ? 'Completed' : 'Mark Complete' ?>
                </button>

                <?php if ($nextLesson): ?>
                    <a href="<?= baseUrl("learning.php?course_id={$courseId}&lesson_id={$nextLesson['id']}") ?>" class="btn btn-primary btn-sm" style="padding: 8px 16px;">
                        Next Lesson <i class="bi bi-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <?php if ($assessment): ?>
                        <a href="<?= baseUrl("assessment.php?course_id={$courseId}") ?>" class="btn btn-secondary btn-sm" style="padding: 8px 16px;">
                            Final Assessment <i class="bi bi-award-fill"></i>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="button" id="bookmarkBtn" class="btn-player <?= $isBookmarked ? 'btn-bookmarked' : '' ?>">
                    <i class="bi <?= $isBookmarked ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i> 
                    <?= $isBookmarked ? 'Saved' : 'Bookmark' ?>
                </button>
            </div>
        </div>

        <!-- Lesson Details & Tabs -->
        <div class="player-details-area">
            <div class="player-lesson-header">
                <div>
                    <div style="font-size: 13px; color: var(--text-muted); margin-bottom: 4px; font-weight: 500;">
                        Lesson <?= $currentIndex + 1 ?> of <?= count($lessons) ?> &bull; Estimated Duration: <?= (int)$currentLesson['duration_minutes'] ?> mins
                    </div>
                    <h1 class="player-lesson-title"><?= Sanitizer::e($currentLesson['title']) ?></h1>
                </div>

                <?php if ($assessment && (float)$enrollment['progress_percent'] >= 100.0): ?>
                    <a href="<?= baseUrl("assessment.php?course_id={$courseId}") ?>" class="btn btn-primary btn-sm">
                        <i class="bi bi-award-fill"></i> Take Final Assessment
                    </a>
                <?php endif; ?>
            </div>

            <div class="player-tabs">
                <button type="button" class="player-tab-btn active" data-tab="overview">
                    <i class="bi bi-card-text"></i> Overview
                </button>
                <button type="button" class="player-tab-btn" data-tab="notes">
                    <i class="bi bi-pencil-square"></i> My Notes
                </button>
                <button type="button" class="player-tab-btn" data-tab="resources">
                    <i class="bi bi-folder2-open"></i> Resources (<?= count($resources) ?>)
                </button>
            </div>

            <!-- Tab: Overview -->
            <div class="player-tab-pane active" id="tab-overview">
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 12px; font-size: 1.05rem;">Lesson Description</h4>
                    <p style="line-height: 1.7; font-size: 14px; color: var(--text-main);">
                        <?= nl2br(Sanitizer::e($currentLesson['description'] ?? 'No additional description provided for this lesson.')) ?>
                    </p>

                    <?php if (!empty($currentLesson['learning_objectives'])): ?>
                        <h4 style="margin-top: 24px; margin-bottom: 12px; font-size: 1.05rem;">Learning Objectives</h4>
                        <div style="font-size: 14px; color: var(--text-main); line-height: 1.6; background: var(--bg-main); padding: 16px; border-radius: var(--radius-md); border: 1px solid var(--border);">
                            <?= nl2br(Sanitizer::e($currentLesson['learning_objectives'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Notes -->
            <div class="player-tab-pane" id="tab-notes">
                <div class="notes-editor-wrap">
                    <div class="notes-header">
                        <strong style="font-size: 14px; color: var(--text-dark);"><i class="bi bi-journal-text"></i> Personal Notes for This Lesson</strong>
                        <span class="notes-status" id="notesStatus"><i class="bi bi-cloud-check"></i> Autosave active</span>
                    </div>
                    <textarea id="lessonNotesText" class="notes-textarea" placeholder="Take personal notes, code snippets, or key takeaways here. Changes autosave automatically as you type..."><?= Sanitizer::e($note['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Tab: Resources -->
            <div class="player-tab-pane" id="tab-resources">
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 16px; font-size: 1.05rem;">Downloadable Resources & Reference Links</h4>
                    <?php if (empty($resources)): ?>
                        <div class="empty-state" style="padding: 32px 20px;">
                            <i class="bi bi-folder" style="font-size: 2rem; color: var(--text-light); margin-bottom: 8px; display: block;"></i>
                            <div style="font-size: 13.5px; color: var(--text-muted);">No downloadable supplementary files attached to this lesson.</div>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($resources as $res): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: var(--bg-main); border-radius: var(--radius-md); border: 1px solid var(--border);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <i class="bi bi-file-earmark-arrow-down-fill" style="font-size: 22px; color: var(--primary);"></i>
                                        <div>
                                            <div style="font-weight: 600; font-size: 14px; color: var(--text-dark);"><?= Sanitizer::e($res['title']) ?></div>
                                            <div style="font-size: 11.5px; color: var(--text-muted);"><?= strtoupper(Sanitizer::e($res['file_type'])) ?> &bull; <?= round($res['file_size'] / 1024) ?> KB</div>
                                        </div>
                                    </div>
                                    <a href="<?= baseUrl($res['file_path']) ?>" download class="btn btn-outline btn-sm">
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar: Collapsible Lesson Playlist -->
    <div class="player-sidebar">
        <div class="playlist-header">
            <div class="playlist-title">Course Content</div>
            <div class="playlist-progress-wrap">
                <span>Course Progress: <?= number_format((float)$enrollment['progress_percent'], 0) ?>%</span>
                <span><?= count($lessons) ?> Lessons</span>
            </div>
            <div class="progress-bar" style="height: 6px; margin-top: 8px;">
                <div class="progress-fill" style="width: <?= (float)$enrollment['progress_percent'] ?>%;"></div>
            </div>
        </div>

        <ul class="playlist-items">
            <?php foreach ($lessons as $idx => $l): 
                $lId = (int)$l['id'];
                $isLActive = ($lId === $currentLessonId);
                $isLDone = !empty($progressMap[$lId]['is_completed']);
            ?>
                <li style="margin: 0;">
                    <a href="<?= baseUrl("learning.php?course_id={$courseId}&lesson_id={$lId}") ?>" 
                       class="lesson-item <?= $isLActive ? 'active' : '' ?> <?= $isLDone ? 'completed' : '' ?>">
                        <div class="lesson-check">
                            <i class="bi bi-check-lg"></i>
                        </div>
                        <div class="lesson-info">
                            <div class="lesson-title-text">
                                <?= ($idx + 1) ?>. <?= Sanitizer::e($l['title']) ?>
                            </div>
                            <div class="lesson-duration">
                                <i class="bi bi-clock"></i> <?= (int)$l['duration_minutes'] ?>m
                            </div>
                        </div>
                    </a>
                </li>
            <?php endforeach; ?>

            <?php if ($assessment): ?>
                <li style="padding: 18px 20px; background: #F8FAFC; border-top: 1px solid var(--border);">
                    <div style="font-size: 11px; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.05em;">FINAL EXAMINATION</div>
                    <div style="font-size: 14px; font-weight: 700; color: var(--text-dark); margin-bottom: 8px;"><?= Sanitizer::e($assessment['title']) ?></div>
                    <?php if ((float)$enrollment['progress_percent'] >= 100.0): ?>
                        <a href="<?= baseUrl("assessment.php?course_id={$courseId}") ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                            <i class="bi bi-award-fill"></i> Launch Assessment
                        </a>
                    <?php else: ?>
                        <div style="font-size: 12px; color: var(--text-muted);"><i class="bi bi-lock-fill"></i> Complete 100% of lessons to unlock assessment.</div>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Floating AI Tutor Launcher -->
<div class="ai-tutor-launcher">
    <div class="ai-tooltip">Ask AliStack AI</div>
    <button type="button" class="ai-launch-btn" id="aiLaunchBtn" title="Ask AliStack AI Tutor" aria-label="Ask AliStack AI Tutor">
        <i class="bi bi-stars"></i>
    </button>
</div>

<!-- AliStack AI Tutor Drawer -->
<div class="ai-drawer" id="aiDrawer">
    <div class="ai-drawer-header">
        <div class="ai-header-info">
            <div class="ai-bot-avatar">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <div class="ai-header-title">AliStack AI Tutor</div>
                <div class="ai-header-subtitle">Context-Aware Learning Mentor</div>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="button" id="aiNewConvBtn" style="background: none; border: none; color: #94A3B8; cursor: pointer; font-size: 16px;" title="New Chat">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
            <button type="button" id="aiCloseBtn" style="background: none; border: none; color: #94A3B8; cursor: pointer; font-size: 18px;" title="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <!-- Quick Actions Pills -->
    <div class="ai-quick-actions">
        <button type="button" class="quick-action-btn" data-prompt="Explain this concept in simpler words with a practical real-world analogy.">
            <i class="bi bi-lightbulb"></i> Explain Concept
        </button>
        <button type="button" class="quick-action-btn" data-prompt="Summarize the core takeaways of this lesson in 3 key bullet points.">
            <i class="bi bi-card-checklist"></i> Summarize Lesson
        </button>
        <button type="button" class="quick-action-btn" data-prompt="Is lesson ka concept aasan Roman Urdu mein samjhayein.">
            <i class="bi bi-chat-quote"></i> Roman Urdu
        </button>
        <button type="button" class="quick-action-btn" data-prompt="Give me a clean code example demonstrating how to implement this.">
            <i class="bi bi-code-slash"></i> Code Example
        </button>
        <button type="button" class="quick-action-btn" data-prompt="Ask me a practice question about this lesson to test my understanding.">
            <i class="bi bi-patch-question"></i> Practice Quiz
        </button>
    </div>

    <!-- Chat Messages Body -->
    <div class="ai-chat-body" id="aiChatBody">
        <div class="ai-message assistant">
            <div class="msg-bubble">
                Hello <strong><?= Sanitizer::e(explode(' ', $user['full_name'])[0]) ?></strong>! I am your <strong>AliStack AI Tutor</strong>. I am following along with <em><?= Sanitizer::e($currentLesson['title']) ?></em>. Ask me any question, request code debugging, or pick a quick action above!
            </div>
        </div>
    </div>

    <!-- Typing Indicator -->
    <div class="ai-typing-indicator" id="aiTypingIndicator">
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
        <div class="typing-dot"></div>
    </div>

    <!-- Chat Input Form -->
    <div class="ai-chat-footer">
        <form id="aiChatForm" class="ai-input-form">
            <textarea id="aiInputField" class="ai-input-field" placeholder="Ask a question about this lesson... (Enter to send)" rows="1"></textarea>
            <button type="submit" class="ai-send-btn" title="Send message">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
    </div>
</div>

<script>
    // Link top bar AI button to AI launcher
    document.getElementById('topAiLaunchBtn')?.addEventListener('click', () => {
        document.getElementById('aiLaunchBtn')?.click();
    });
</script>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
