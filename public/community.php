<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Auth;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Repositories\CommunityRepository;

$communityRepo = new CommunityRepository();
$currentUserId = Auth::id();

$groupSlug = trim((string)($_GET['group'] ?? ''));
$postId = !empty($_GET['post']) ? (int)$_GET['post'] : 0;

$selectedGroup = null;
$selectedPost = null;
$membership = null;
$joinRequest = null;
$posts = [];
$replies = [];

if ($groupSlug) {
    $selectedGroup = $communityRepo->getGroupBySlug($groupSlug);
    if ($selectedGroup) {
        $groupId = (int)$selectedGroup['id'];
        if ($currentUserId) {
            $membership = $communityRepo->getUserMembership($currentUserId, $groupId);
            $joinRequest = $communityRepo->getUserJoinRequest($currentUserId, $groupId);
        }

        if ($postId) {
            $selectedPost = $communityRepo->getPostById($postId);
            if ($selectedPost) {
                $replies = $communityRepo->getPostReplies($postId);
            }
        } else {
            $posts = $communityRepo->getGroupPosts($groupId);
        }
    }
} else {
    $groups = $communityRepo->getAllGroups(true);
}

$pageTitle = $selectedPost ? $selectedPost['title'] : ($selectedGroup ? $selectedGroup['name'] : 'Learning Community');
require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 48px 0;">
    <div class="container">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <?php if ($selectedGroup): ?>
                    <a href="<?= baseUrl('community.php') ?>" style="font-size: 13px; color: var(--muted); display: inline-flex; align-items: center; gap: 4px; margin-bottom: 6px;">
                        <i class="bi bi-arrow-left"></i> All Discussion Groups
                    </a>
                    <h1 style="font-size: 2rem; margin-bottom: 6px;"><?= Sanitizer::e($selectedGroup['name']) ?></h1>
                    <div style="font-size: 13px; color: var(--muted);">
                        <?= $selectedGroup['is_private'] ? '<span class="badge badge-warning"><i class="bi bi-lock-fill"></i> Private Group</span>' : '<span class="badge badge-success"><i class="bi bi-globe"></i> Public Group</span>' ?>
                        &bull; <?= (int)$selectedGroup['member_count'] ?> members &bull; <?= (int)$selectedGroup['post_count'] ?> discussions
                    </div>
                <?php else: ?>
                    <h1 style="font-size: 2.25rem; margin-bottom: 6px;">Learning Community Groups</h1>
                    <p style="color: var(--muted); font-size: 15px; margin: 0;">
                        Collaborate with peers, ask questions, share insights, and discuss lessons in moderated spaces.
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($selectedGroup && $membership && $membership['status'] === 'active' && !$selectedPost): ?>
                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newPostModal')">
                    <i class="bi bi-plus-lg"></i> New Discussion
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container" style="padding: 40px 24px 80px;">
    <?php if (!$selectedGroup): ?>
        <!-- Groups Directory Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
            <?php foreach ($groups as $g): 
                $userMemb = $currentUserId ? $communityRepo->getUserMembership($currentUserId, (int)$g['id']) : null;
                $userReq = $currentUserId ? $communityRepo->getUserJoinRequest($currentUserId, (int)$g['id']) : null;
            ?>
                <div class="card" style="padding: 24px; display: flex; flex-direction: column;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <span class="badge <?= $g['is_private'] ? 'badge-warning' : 'badge-success' ?>">
                            <i class="bi <?= $g['is_private'] ? 'bi-lock-fill' : 'bi-globe' ?>"></i> 
                            <?= $g['is_private'] ? 'Private' : 'Public' ?>
                        </span>
                        <span style="font-size: 12px; color: var(--muted);"><i class="bi bi-people"></i> <?= (int)$g['member_count'] ?></span>
                    </div>

                    <h3 style="font-size: 1.25rem; margin-bottom: 8px;">
                        <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" style="color: var(--dark);">
                            <?= Sanitizer::e($g['name']) ?>
                        </a>
                    </h3>

                    <p style="font-size: 13px; color: var(--muted); line-height: 1.5; margin-bottom: 20px; flex: 1;">
                        <?= Sanitizer::e($g['description']) ?>
                    </p>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 16px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 12px; color: var(--muted);"><?= (int)$g['post_count'] ?> posts</span>
                        
                        <?php if ($userMemb && $userMemb['status'] === 'active'): ?>
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" class="btn btn-outline-primary btn-sm">
                                Enter Group
                            </a>
                        <?php elseif ($userReq && $userReq['status'] === 'pending'): ?>
                            <span class="badge badge-warning">Request Pending</span>
                        <?php else: ?>
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" class="btn btn-primary btn-sm">
                                <?= $g['is_private'] ? 'Request Access' : 'View Group' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($selectedPost): ?>
        <!-- Single Post & Threaded Replies View -->
        <div style="max-width: 820px; margin: 0 auto;">
            <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug'])) ?>" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: var(--muted); margin-bottom: 20px;">
                <i class="bi bi-arrow-left"></i> Back to all discussions in <?= Sanitizer::e($selectedGroup['name']) ?>
            </a>

            <!-- Post Card -->
            <div class="card" style="padding: 32px; margin-bottom: 32px;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                    <div class="avatar" style="width: 36px; height: 36px; font-size: 13px;">
                        <?= strtoupper(substr($selectedPost['author_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--dark);"><?= Sanitizer::e($selectedPost['author_name']) ?></div>
                        <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::timeAgo($selectedPost['created_at']) ?></div>
                    </div>
                </div>

                <h2 style="font-size: 1.6rem; margin-bottom: 16px;"><?= Sanitizer::e($selectedPost['title']) ?></h2>
                <div style="font-size: 15px; line-height: 1.7; color: var(--dark-light); margin-bottom: 20px;">
                    <?= nl2br(Sanitizer::e($selectedPost['content'])) ?>
                </div>
            </div>

            <!-- Replies Section -->
            <h3 style="font-size: 1.3rem; margin-bottom: 20px;">Replies (<?= count($replies) ?>)</h3>

            <?php if (empty($replies)): ?>
                <div style="text-align: center; padding: 32px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-md); margin-bottom: 28px;">
                    <p style="color: var(--muted); font-size: 14px; margin: 0;">No replies yet. Be the first to join the conversation!</p>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
                    <?php foreach ($replies as $r): ?>
                        <div class="card" style="padding: 20px;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div class="avatar" style="width: 30px; height: 30px; font-size: 11px;">
                                    <?= strtoupper(substr($r['author_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span style="font-weight: 700; font-size: 13px; color: var(--dark);"><?= Sanitizer::e($r['author_name']) ?></span>
                                    <span style="font-size: 11px; color: var(--muted); margin-left: 6px;"><?= Sanitizer::timeAgo($r['created_at']) ?></span>
                                </div>
                            </div>
                            <div style="font-size: 14px; line-height: 1.6; color: var(--dark-light); padding-left: 40px;">
                                <?= nl2br(Sanitizer::e($r['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Post Reply Form -->
            <?php if (Auth::check()): ?>
                <div class="card" style="padding: 24px;">
                    <h4 style="margin-bottom: 12px; font-size: 15px;">Add Your Reply</h4>
                    <form method="POST" action="<?= baseUrl('api/community/create-reply.php') ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="post_id" value="<?= $selectedPost['id'] ?>">
                        <div class="form-group">
                            <textarea name="content" class="form-control" placeholder="Share your perspective or answer constructively..." required rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-chat-dots-fill"></i> Post Reply
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 20px; background: #FFFFFF; border-radius: var(--radius-md);">
                    <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary btn-sm">Log in to reply</a>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Group Feed & Sidebar Layout -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 36px;">
            <!-- Discussions Feed -->
            <div>
                <?php if ($selectedGroup['is_private'] && (!$membership || $membership['status'] !== 'active') && !Auth::canAccessAdmin()): ?>
                    <!-- Private Group Lock Screen -->
                    <div class="card" style="padding: 48px 32px; text-align: center;">
                        <div style="font-size: 48px; color: var(--warning); margin-bottom: 16px;"><i class="bi bi-lock-fill"></i></div>
                        <h2 style="font-size: 1.5rem; margin-bottom: 10px;">Private Discussion Group</h2>
                        <p style="color: var(--muted); max-width: 460px; margin: 0 auto 24px; font-size: 14px;">
                            This group is moderated for approved course students. Submit a join request to participate in discussions.
                        </p>

                        <?php if ($joinRequest && $joinRequest['status'] === 'pending'): ?>
                            <span class="badge badge-warning" style="font-size: 14px; padding: 8px 16px;">
                                <i class="bi bi-hourglass-split"></i> Join Request Pending Moderator Review
                            </span>
                        <?php elseif (Auth::check()): ?>
                            <form method="POST" action="<?= baseUrl('api/community/request-join.php') ?>">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="group_id" value="<?= $selectedGroup['id'] ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus-fill"></i> Request to Join Group
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary">
                                Log in to Request Access
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Authorized Members Feed -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php if (empty($posts)): ?>
                            <div style="text-align: center; padding: 48px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
                                <i class="bi bi-chat-square-text" style="font-size: 40px; color: var(--muted-light); margin-bottom: 12px; display: block;"></i>
                                <h4 style="margin-bottom: 6px;">No discussions started yet</h4>
                                <p style="color: var(--muted); font-size: 13px; margin-bottom: 16px;">Be the first student to start a topic in this group!</p>
                                <button type="button" class="btn btn-primary btn-sm" onclick="openModal('newPostModal')">
                                    Start First Discussion
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($posts as $p): ?>
                                <div class="card" style="padding: 20px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar" style="width: 28px; height: 28px; font-size: 11px;">
                                                <?= strtoupper(substr($p['author_name'], 0, 1)) ?>
                                            </div>
                                            <span style="font-size: 13px; font-weight: 600;"><?= Sanitizer::e($p['author_name']) ?></span>
                                            <span style="font-size: 11px; color: var(--muted);">&bull; <?= Sanitizer::timeAgo($p['created_at']) ?></span>
                                        </div>
                                        <span style="font-size: 12px; color: var(--muted);"><i class="bi bi-chat"></i> <?= (int)$p['replies_count'] ?></span>
                                    </div>

                                    <h3 style="font-size: 1.15rem; margin-bottom: 8px;">
                                        <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug']) . '&post=' . $p['id']) ?>" style="color: var(--dark);">
                                            <?= Sanitizer::e($p['title']) ?>
                                        </a>
                                    </h3>

                                    <p style="font-size: 13px; color: var(--muted); margin-bottom: 14px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= Sanitizer::e($p['content']) ?>
                                    </p>

                                    <div>
                                        <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug']) . '&post=' . $p['id']) ?>" class="btn btn-outline btn-sm" style="font-size: 12px; padding: 4px 10px;">
                                            Read Discussion & Reply &rarr;
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Group Rules Sidebar -->
            <div>
                <div class="card" style="padding: 24px; margin-bottom: 24px;">
                    <h4 style="margin-bottom: 12px; font-size: 15px;"><i class="bi bi-info-circle" style="color: var(--primary);"></i> About This Group</h4>
                    <p style="font-size: 13px; color: var(--muted); line-height: 1.6; margin-bottom: 16px;">
                        <?= Sanitizer::e($selectedGroup['description']) ?>
                    </p>

                    <?php if (!empty($selectedGroup['rules'])): ?>
                        <h5 style="margin-bottom: 8px; font-size: 13px;">Community Guidelines</h5>
                        <div style="font-size: 12px; color: var(--muted); line-height: 1.6; background: #F8FAFC; padding: 12px; border-radius: var(--radius-sm);">
                            <?= nl2br(Sanitizer::e($selectedGroup['rules'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal: New Post -->
        <div class="modal-backdrop" id="newPostModal">
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title">New Discussion Topic</h3>
                    <button type="button" class="modal-close" onclick="closeModal('newPostModal')">&times;</button>
                </div>
                <form method="POST" action="<?= baseUrl('api/community/create-post.php') ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="group_id" value="<?= $selectedGroup['id'] ?>">

                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label" for="post_title">Discussion Title</label>
                            <input type="text" id="post_title" name="title" class="form-control" required placeholder="e.g. Question regarding Lesson 3 PDO query binding">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="post_content">Message Content</label>
                            <textarea id="post_content" name="content" class="form-control" required placeholder="Describe what you are trying to solve or share with peers..." rows="5"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('newPostModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Post Discussion</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
