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

$notes = $courseRepo->getUserNotes($userId, 100);

$pageTitle = 'My Notes';
$pageDesc = 'Review and manage your personal study notes recorded across AliStack lessons.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 48px 0;">
    <div class="container">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--text-muted); margin-bottom: 16px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
            <span>/</span>
            <span style="color: var(--text-dark); font-weight: 600;">My Notes</span>
        </div>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <h1 style="font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 800; color: var(--text-dark); margin: 0 0 8px;">
                    Personal Study Notes
                </h1>
                <p style="color: var(--text-muted); font-size: 15px; margin: 0;">
                    Your synchronized thoughts, formulas, and code snippets captured during lessons.
                </p>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <span class="badge badge-primary" style="font-size: 13px; padding: 6px 14px;">
                    <i class="bi bi-journal-check"></i> <?= count($notes) ?> Saved Notes
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
                <input type="text" id="noteSearchInput" class="form-control" placeholder="Search notes by keywords or lesson..." style="padding-left: 38px;">
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-sm btn-outline filter-btn active" data-filter="all">All Notes</button>
            <button type="button" class="btn btn-sm btn-outline filter-btn" data-filter="important">
                <i class="bi bi-star-fill" style="color: var(--warning);"></i> Starred
            </button>
        </div>
    </div>

    <?php if (empty($notes)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-journal-text"></i>
            </div>
            <div class="empty-state-title">Your notes will appear here as you learn</div>
            <div class="empty-state-desc">
                Take timestamped notes while watching video lessons in the classroom workspace. They will automatically autosave to this collection.
            </div>
            <a href="<?= baseUrl('dashboard.php') ?>" class="btn btn-primary" style="margin-top: 16px;">
                <i class="bi bi-play-circle-fill"></i> Continue Learning
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-3" id="notesGrid" style="gap: 24px;">
            <?php foreach ($notes as $note): ?>
                <div class="card note-card" data-important="<?= $note['is_important'] ? '1' : '0' ?>" data-title="<?= strtolower(Sanitizer::e($note['lesson_title'] . ' ' . $note['course_title'])) ?>" data-content="<?= strtolower(Sanitizer::e($note['content'])) ?>" style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-xl); border: 1px solid var(--border);">
                    <div>
                        <!-- Header & Meta -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                            <div>
                                <span class="badge badge-secondary" style="font-size: 11px; margin-bottom: 6px; display: inline-block;">
                                    <?= Sanitizer::e($note['course_title']) ?>
                                </span>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin: 0; line-height: 1.35;">
                                    <?= Sanitizer::e($note['lesson_title']) ?>
                                </h3>
                            </div>
                            <?php if (!empty($note['is_important'])): ?>
                                <span title="Marked as Important" style="color: var(--warning); font-size: 1.15rem; flex-shrink: 0;">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Content Preview -->
                        <div style="background: var(--bg-main); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px; margin-bottom: 20px; font-size: 13.5px; color: var(--text-main); line-height: 1.6; white-space: pre-wrap; max-height: 180px; overflow-y: auto;">
                            <?= Sanitizer::e($note['content']) ?>
                        </div>
                    </div>

                    <!-- Footer Controls -->
                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid var(--border); font-size: 12px; color: var(--text-muted);">
                        <span><i class="bi bi-clock"></i> <?= Sanitizer::timeAgo($note['updated_at']) ?></span>
                        <div style="display: flex; gap: 8px;">
                            <a href="<?= baseUrl('learning.php?course_id=' . $note['course_id'] . '&lesson_id=' . $note['lesson_id']) ?>" class="btn btn-outline btn-xs" title="Open Lesson Workspace">
                                <i class="bi bi-box-arrow-up-right"></i> Open
                            </a>
                            <button type="button" class="btn btn-outline btn-xs delete-note-btn" data-lesson-id="<?= (int)$note['lesson_id'] ?>" style="color: var(--danger); border-color: var(--border);" title="Delete Note">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('noteSearchInput');
    const filterBtns = document.querySelectorAll('.filter-btn');
    const noteCards = document.querySelectorAll('.note-card');

    let currentFilter = 'all';

    function filterNotes() {
        const query = (searchInput?.value || '').toLowerCase().trim();

        noteCards.forEach(card => {
            const isImportant = card.getAttribute('data-important') === '1';
            const title = card.getAttribute('data-title') || '';
            const content = card.getAttribute('data-content') || '';

            const matchesQuery = !query || title.includes(query) || content.includes(query);
            const matchesFilter = (currentFilter === 'all') || (currentFilter === 'important' && isImportant);

            if (matchesQuery && matchesFilter) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    searchInput?.addEventListener('input', filterNotes);

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter') || 'all';
            filterNotes();
        });
    });

    // Delete Note handler
    document.querySelectorAll('.delete-note-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const lessonId = this.getAttribute('data-lesson-id');
            if (!confirm('Are you sure you want to delete this study note?')) return;

            try {
                const res = await fetch(window.getApiUrl('api/notes/delete.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': window.getCsrfToken()
                    },
                    body: JSON.stringify({ lesson_id: lessonId })
                });
                const data = await res.json();
                if (data.success) {
                    if (window.AliStackToast) {
                        window.AliStackToast.success('Note deleted successfully');
                    }
                    this.closest('.note-card')?.remove();
                } else {
                    alert(data.message || 'Failed to delete note');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error while deleting note');
            }
        });
    });
});
</script>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
