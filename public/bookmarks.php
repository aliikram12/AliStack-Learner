<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Repositories\CourseRepository;

Auth::requireAuth();

$user = Auth::user();
$userId = (int)$user['id'];
$courseRepo = new CourseRepository();

$bookmarks = $courseRepo->getUserBookmarks($userId);

$pageTitle = 'Saved Lessons';
$pageDesc = 'Quickly access and revise bookmarked video lessons across your enrolled courses.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 48px 0;">
    <div class="container">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;">Bookmarks</span>
        </div>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 800; color: var(--text-dark); margin: 0 0 8px;">
                    Saved Lessons
                </h1>
                <p style="color: var(--text-muted); font-size: 15px; margin: 0;">
                    Your personal library of pinned lectures, walkthroughs, and key reference materials.
                </p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <span class="badge badge-primary" style="font-size: 13px; padding: 6px 14px;">
                    <i class="bi bi-bookmark-fill"></i> <?= count($bookmarks) ?> Bookmarked Lessons
                </span>
            </div>
        </div>
    </div>
</div>

<div class="container" style="padding: 40px 24px 80px;">
    <!-- Search & Filter Controls -->
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; margin-bottom: 32px;">
        <div style="display: flex; align-items: center; gap: 12px; flex: 1; max-width: 480px;">
            <div style="position: relative; width: 100%;">
                <i class="bi bi-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-light); font-size: 14px;"></i>
                <input type="text" id="bmSearchInput" class="form-control" placeholder="Search saved lessons by title or course..." style="padding-left: 38px;">
            </div>
        </div>
    </div>

    <?php if (empty($bookmarks)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-bookmark-star"></i>
            </div>
            <div class="empty-state-title">No bookmarked lessons yet</div>
            <div class="empty-state-desc">
                Click the bookmark icon while watching any lesson to pin it here for rapid revision before taking assessments.
            </div>
            <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-primary" style="margin-top: 16px;">
                <i class="bi bi-play-circle-fill"></i> Resume Learning
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-3" id="bookmarksGrid" style="gap: 24px;">
            <?php foreach ($bookmarks as $bm): ?>
                <div class="card bm-card" data-title="<?= strtolower(Sanitizer::e($bm['lesson_title'] . ' ' . $bm['course_title'])) ?>" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-xl); border: 1px solid var(--border);">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <span class="badge badge-secondary" style="font-size: 11px;">
                                <?= Sanitizer::e($bm['course_title']) ?>
                            </span>
                            <span style="font-size: 12px; color: var(--text-muted);"><i class="bi bi-calendar3"></i> <?= Sanitizer::timeAgo($bm['created_at']) ?></span>
                        </div>

                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-dark); margin-bottom: 14px; line-height: 1.4;">
                            <?= Sanitizer::e($bm['lesson_title']) ?>
                        </h3>

                        <!-- Thumbnail Preview -->
                        <?php if (!empty($bm['youtube_video_id'])): ?>
                            <div style="width: 100%; aspect-ratio: 16/9; border-radius: var(--radius-md); overflow: hidden; background: #0F172A; margin-bottom: 16px; position: relative;">
                                <img src="https://img.youtube.com/vi/<?= Sanitizer::e($bm['youtube_video_id']) ?>/mqdefault.jpg" alt="Video thumbnail" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&q=80';">
                                <div style="position: absolute; inset: 0; background: rgba(15,23,42,0.3); display: flex; align-items: center; justify-content: center;">
                                    <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(37,99,235,0.9); color: #FFF; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                                        <i class="bi bi-play-fill" style="margin-left: 2px;"></i>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Action Controls -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid var(--border);">
                        <a href="<?= baseUrl('learning.php?course_id=' . $bm['course_id'] . '&lesson_id=' . $bm['lesson_id']) ?>" class="btn btn-primary btn-sm" style="flex: 1; margin-right: 8px;">
                            <i class="bi bi-play-fill"></i> Continue Lesson
                        </a>
                        <button type="button" class="btn btn-outline btn-sm remove-bm-btn" data-course-id="<?= (int)$bm['course_id'] ?>" data-lesson-id="<?= (int)$bm['lesson_id'] ?>" style="color: var(--danger); border-color: var(--border);" title="Remove Bookmark">
                            <i class="bi bi-bookmark-x"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('bmSearchInput');
    const cards = document.querySelectorAll('.bm-card');

    searchInput?.addEventListener('input', function() {
        const query = this.value.toLowerCase().trim();
        cards.forEach(card => {
            const title = card.getAttribute('data-title') || '';
            if (!query || title.includes(query)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });

    // Remove Bookmark handler
    document.querySelectorAll('.remove-bm-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const courseId = this.getAttribute('data-course-id');
            const lessonId = this.getAttribute('data-lesson-id');

            try {
                const res = await fetch(window.getApiUrl('api/progress/toggle-bookmark.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.getCsrfToken()
                    },
                    body: JSON.stringify({ course_id: courseId, lesson_id: lessonId })
                });
                const data = await res.json();
                if (data.success) {
                    if (window.AliStackToast) {
                        window.AliStackToast.info('Bookmark removed');
                    }
                    this.closest('.bm-card')?.remove();
                } else {
                    alert(data.message || 'Failed to update bookmark');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while updating bookmark');
            }
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
