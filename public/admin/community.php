<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\CommunityRepository;
use App\Repositories\CourseRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

$communityRepo = new CommunityRepository();
$courseRepo = new CourseRepository();
$settingRepo = new SettingRepository();

// Handle new group creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_group') {
        $name = trim((string)$_POST['name']);
        $slug = Sanitizer::slug($name);
        $desc = trim((string)$_POST['description']);
        $rules = trim((string)($_POST['rules'] ?? ''));
        $courseId = !empty($_POST['course_id']) ? (int)$_POST['course_id'] : null;
        $isPrivate = !empty($_POST['is_private']) ? 1 : 0;

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO discussion_groups (course_id, name, slug, description, rules, is_private, is_active, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, ?, NOW())
        ");
        $stmt->execute([$courseId, $name, $slug, $desc, $rules, $isPrivate, Auth::id()]);
        $settingRepo->logAudit(Auth::id(), 'GROUP_CREATE', 'discussion_groups', (int)$pdo->lastInsertId(), "Created group {$name}");
        $_SESSION['flash_success'] = 'Discussion group created successfully.';
        header('Location: ' . baseUrl('admin/community.php'));
        exit;
    }
}

$pendingRequests = $communityRepo->getPendingRequestsAdmin();
$groups = $communityRepo->getAllGroups(false);
$reports = $communityRepo->getReportsAdmin();
$courses = $courseRepo->getAllAdmin(1, 100);

$pageTitle = 'Community & Moderation';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Community Moderation</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Review private group join requests, manage discussion channels, and audit reports.</p>
    </div>

    <button type="button" class="btn btn-primary btn-sm" onclick="openModal('addGroupModal')">
        <i class="bi bi-plus-circle"></i> Create Discussion Group
    </button>
</div>

<!-- Pending Join Requests Section -->
<div class="table-card" style="margin-bottom: 32px;">
    <div class="card-header">
        <h3 style="font-size: 1.1rem; margin: 0;">
            Pending Group Join Requests (<?= count($pendingRequests) ?>)
        </h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Requested Group</th>
                    <th>Requested At</th>
                    <th style="text-align: right;">Review Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pendingRequests)): ?>
                    <tr><td colspan="4" style="text-align: center; color: var(--muted); padding: 24px;">No pending join requests waiting for review.</td></tr>
                <?php else: ?>
                    <?php foreach ($pendingRequests as $req): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 600;"><?= Sanitizer::e($req['student_name']) ?></div>
                                <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($req['student_email']) ?></div>
                            </td>
                            <td><strong><?= Sanitizer::e($req['group_name']) ?></strong></td>
                            <td style="font-size: 12px; color: var(--muted);"><?= Sanitizer::timeAgo($req['created_at']) ?></td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="reviewRequest(<?= $req['id'] ?>, 'approved')">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="reviewRequest(<?= $req['id'] ?>, 'rejected')">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Existing Groups List -->
<div class="table-card">
    <div class="card-header">
        <h3 style="font-size: 1.1rem; margin: 0;">Discussion Groups</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Group Name & Slug</th>
                    <th>Associated Course</th>
                    <th>Access</th>
                    <th>Members</th>
                    <th>Posts</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($groups as $g): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 700;"><?= Sanitizer::e($g['name']) ?></div>
                            <div style="font-size: 11px; color: var(--muted);"><?= Sanitizer::e($g['slug']) ?></div>
                        </td>
                        <td><?= Sanitizer::e($g['course_title'] ?? 'General Discussion') ?></td>
                        <td>
                            <span class="badge <?= $g['is_private'] ? 'badge-warning' : 'badge-success' ?>">
                                <?= $g['is_private'] ? 'Private' : 'Public' ?>
                            </span>
                        </td>
                        <td><?= (int)$g['member_count'] ?></td>
                        <td><?= (int)$g['post_count'] ?></td>
                        <td style="text-align: right;">
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" target="_blank" class="btn btn-outline btn-sm">
                                View Feed &rarr;
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Add Group -->
<div class="modal-backdrop" id="addGroupModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Create Discussion Group</h3>
            <button type="button" class="modal-close" onclick="closeModal('addGroupModal')">&times;</button>
        </div>
        <form method="POST" action="<?= baseUrl('admin/community.php') ?>">
            <?= Csrf::field() ?>
            <input type="hidden" name="action" value="create_group">

            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="name">Group Name</label>
                    <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Full-Stack PHP Discussion Circle">
                </div>

                <div class="form-group">
                    <label class="form-label" for="course_id">Associated Course (Optional)</label>
                    <select id="course_id" name="course_id" class="form-select">
                        <option value="">General (Not tied to a course)</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= Sanitizer::e($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" required rows="2" placeholder="Brief explanation of the group's purpose..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="rules">Community Guidelines / Rules</label>
                    <textarea id="rules" name="rules" class="form-control" rows="3" placeholder="1. Be respectful&#10;2. No sharing assessment answer keys"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="is_private" value="1" checked>
                        <span>Private Group (Requires Moderator / Admin Approval)</span>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('addGroupModal')">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Create Group</button>
            </div>
        </form>
    </div>
</div>

<script>
async function reviewRequest(requestId, action) {
    if (!confirm(`Are you sure you want to ${action} this request?`)) return;

    try {
        const res = await fetch('<?= baseUrl('api/community/review-request.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.getCsrfToken()
            },
            body: JSON.stringify({ request_id: requestId, action: action })
        });
        const data = await res.json();
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Review action failed.');
        }
    } catch (e) {
        console.error(e);
        alert('Network error.');
    }
}
</script>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
