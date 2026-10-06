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

<div class="player-layout">
    <!-- Main Left Area: Video Player & Details -->
    <div class="player-main-area">
        <!-- Video Container -->
        <div class="video-container">
            <div id="youtubePlayer" 
                 data-video-id="<?= Sanitizer::e($currentLesson['youtube_video_id']) ?>" 
                 data-start-pos="<?= $startPos ?>"></div>
        </div>

        <!-- Controls Bar -->
        <div class="player-controls-bar">
            <div class="lesson-nav-buttons">
                <?php if ($prevLesson): ?>
                    <a href="<?= baseUrl("learning.php?course_id={$courseId}&lesson_id={$prevLesson['id']}") ?>" class="btn-player">
                        <i class="bi bi-chevron-left"></i> Previous
                    </a>
                <?php else: ?>
                    <button class="btn-player" disabled><i class="bi bi-chevron-left"></i> Previous</button>
                <?php endif; ?>

                <?php if ($nextLesson): ?>
                    <a href="<?= baseUrl("learning.php?course_id={$courseId}&lesson_id={$nextLesson['id']}") ?>" class="btn-player">
                        Next <i class="bi bi-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="button" id="bookmarkBtn" class="btn-player <?= $isBookmarked ? 'btn-bookmarked' : '' ?>">
                    <i class="bi <?= $isBookmarked ? 'bi-bookmark-fill' : 'bi-bookmark' ?>"></i> 
                    <?= $isBookmarked ? 'Bookmarked' : 'Bookmark' ?>
                </button>

                <button type="button" id="markCompleteBtn" class="btn-player <?= $isCompleted ? 'btn-complete' : '' ?>">
                    <i class="bi <?= $isCompleted ? 'bi-check-circle-fill' : 'bi-check2' ?>"></i> 
                    <?= $isCompleted ? 'Completed' : 'Mark Complete' ?>
                </button>
            </div>
        </div>

        <!-- Lesson Details & Tabs -->
        <div class="player-details-area">
            <div class="player-lesson-header">
                <div>
                    <div style="font-size: 13px; color: var(--muted); margin-bottom: 4px;">
                        Lesson <?= $currentIndex + 1 ?> of <?= count($lessons) ?> &bull; <?= Sanitizer::e($course['title']) ?>
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
                <button type="button" class="player-tab-btn active" data-tab="notes">
                    <i class="bi bi-pencil-square"></i> My Lesson Notes
                </button>
                <button type="button" class="player-tab-btn" data-tab="overview">
                    <i class="bi bi-card-text"></i> Lesson Overview
                </button>
                <button type="button" class="player-tab-btn" data-tab="resources">
                    <i class="bi bi-folder2-open"></i> Resources (<?= count($resources) ?>)
                </button>
            </div>

            <!-- Tab: Notes -->
            <div class="player-tab-pane active" id="tab-notes">
                <div class="notes-editor-wrap">
                    <div class="notes-header">
                        <strong style="font-size: 14px; color: var(--dark);"><i class="bi bi-journal-text"></i> Personal Notes for This Lesson</strong>
                        <span class="notes-status" id="notesStatus">Autosave enabled</span>
                    </div>
                    <textarea id="lessonNotesText" class="notes-textarea" placeholder="Take personal notes, code snippets, or key takeaways here. Changes will autosave automatically..."><?= Sanitizer::e($note['content'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Tab: Overview -->
            <div class="player-tab-pane" id="tab-overview">
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 12px;">Lesson Description</h4>
                    <p style="line-height: 1.7; font-size: 14px;">
                        <?= nl2br(Sanitizer::e($currentLesson['description'] ?? 'No additional description provided for this lesson.')) ?>
                    </p>

                    <?php if (!empty($currentLesson['learning_objectives'])): ?>
                        <h4 style="margin-top: 24px; margin-bottom: 12px;">Key Objectives</h4>
                        <div style="font-size: 14px; color: var(--muted); line-height: 1.6;">
                            <?= nl2br(Sanitizer::e($currentLesson['learning_objectives'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab: Resources -->
            <div class="player-tab-pane" id="tab-resources">
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 16px;">Downloadable Resources & Links</h4>
                    <?php if (empty($resources)): ?>
                        <p style="font-size: 13px; color: var(--muted); margin: 0;">No downloadable supplementary files attached to this lesson.</p>
                    <?php else: ?>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <?php foreach ($resources as $res): ?>
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #F8FAFC; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                                    <div style="display: flex; align-items: center; gap: 12px;">
                                        <i class="bi bi-file-earmark-arrow-down-fill" style="font-size: 20px; color: var(--primary);"></i>
                                        <div>
                                            <div style="font-weight: 600; font-size: 14px;"><?= Sanitizer::e($res['title']) ?></div>
                                            <div style="font-size: 11px; color: var(--muted);"><?= strtoupper(Sanitizer::e($res['file_type'])) ?> &bull; <?= round($res['file_size'] / 1024) ?> KB</div>
                                        </div>
                                    </div>
                                    <a href="<?= baseUrl($res['file_path']) ?>" download class="btn btn-outline btn-sm">
                                        Download
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
            <div class="course-progress-bar">
                <div class="course-progress-fill" style="width: <?= (float)$enrollment['progress_percent'] ?>%;"></div>
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
                <li style="padding: 16px 20px; background: #F8FAFC; border-top: 1px solid var(--border-color);">
                    <div style="font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; margin-bottom: 4px;">FINAL TEST</div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--dark); margin-bottom: 8px;"><?= Sanitizer::e($assessment['title']) ?></div>
                    <?php if ((float)$enrollment['progress_percent'] >= 100.0): ?>
                        <a href="<?= baseUrl("assessment.php?course_id={$courseId}") ?>" class="btn btn-primary btn-sm" style="width: 100%;">
                            Start Assessment
                        </a>
                    <?php else: ?>
                        <div style="font-size: 12px; color: var(--muted);">Complete 100% of lessons to unlock test.</div>
                    <?php endif; ?>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- Floating AI Tutor Launcher -->
<div class="ai-tutor-launcher">
    <div class="ai-tooltip">Ask AliStack AI</div>
    <button type="button" class="ai-launch-btn" id="aiLaunchBtn" title="Ask AliStack AI Tutor">
        <i class="bi bi-stars"></i>
    </button>
</div>

<!-- AliStack AI Tutor Drawer -->
<div class="ai-drawer" id="aiDrawer">
    <div class="ai-drawer-header">
        <div class="ai-header-info">
            <div class="ai-bot-avatar">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <div class="ai-header-title">AliStack AI Tutor</div>
                <div class="ai-header-subtitle">Powered by AgentRouter</div>
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
        <button type="button" class="quick-action-btn" data-prompt="Give me a code example demonstrating how to implement this correctly.">
            <i class="bi bi-code-slash"></i> Give Example
        </button>
        <button type="button" class="quick-action-btn" data-prompt="Ask me a practice question about this lesson to test my understanding.">
            <i class="bi bi-patch-question"></i> Practice Quiz
        </button>
    </div>

    <!-- Chat Messages Body -->
    <div class="ai-chat-body" id="aiChatBody">
        <div class="ai-message assistant">
            <div class="msg-bubble">
                Hello <strong><?= Sanitizer::e(explode(' ', $user['full_name'])[0]) ?></strong>! I am your <strong>AliStack AI Tutor</strong>. I am following along with <em><?= Sanitizer::e($currentLesson['title']) ?></em>. Ask me any conceptual question, request a code explanation, or click any quick action above!
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

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
