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
$pageDesc = 'Collaborate with peers, ask questions, share insights, and discuss lessons in moderated spaces.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%); border-bottom: 1px solid var(--border-color); padding: 52px 0; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -60px; right: -60px; width: 320px; height: 320px; background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%); border-radius: 50%; pointer-events: none;"></div>
    <div class="container" style="position: relative; z-index: 1;">
        <!-- Breadcrumb -->
        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); margin-bottom: 16px;">
            <a href="<?= baseUrl('dashboard.php') ?>" style="color: var(--muted); text-decoration: none;"><i class="bi bi-house"></i> Dashboard</a>
            <span>/</span>
            <?php if ($selectedGroup): ?>
                <a href="<?= baseUrl('community.php') ?>" style="color: var(--muted); text-decoration: none;">Community</a>
                <span>/</span>
                <?php if ($selectedPost): ?>
                    <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug'])) ?>" style="color: var(--muted); text-decoration: none;"><?= Sanitizer::e($selectedGroup['name']) ?></a>
                    <span>/</span>
                    <span style="color: var(--dark); font-weight: 600;">Discussion</span>
                <?php else: ?>
                    <span style="color: var(--dark); font-weight: 600;"><?= Sanitizer::e($selectedGroup['name']) ?></span>
                <?php endif; ?>
            <?php else: ?>
                <span style="color: var(--dark); font-weight: 600;">Community</span>
            <?php endif; ?>
        </div>

        <div style="display: flex; align-items: flex-end; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div>
                <?php if ($selectedGroup): ?>
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(37,99,235,0.08); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                        <i class="bi bi-people-fill"></i> Community Hub
                    </div>
                    <h1 style="font-size: clamp(1.8rem, 3vw, 2.35rem); font-weight: 800; color: var(--dark); margin: 0 0 10px; letter-spacing: -0.02em;">
                        <?= Sanitizer::e($selectedGroup['name']) ?>
                    </h1>
                    <div style="display: flex; align-items: center; gap: 14px; font-size: 13.5px; color: var(--muted); flex-wrap: wrap;">
                        <?= $selectedGroup['is_private'] ? '<span class="badge badge-warning" style="padding: 4px 10px; font-weight: 700;"><i class="bi bi-lock-fill"></i> Private Group</span>' : '<span class="badge badge-success" style="padding: 4px 10px; font-weight: 700;"><i class="bi bi-globe"></i> Public Group</span>' ?>
                        <span>&bull;</span>
                        <span><i class="bi bi-people-fill" style="color: var(--primary);"></i> <?= (int)$selectedGroup['member_count'] ?> Active Members</span>
                        <span>&bull;</span>
                        <span><i class="bi bi-chat-square-text-fill" style="color: #7C3AED;"></i> <?= (int)$selectedGroup['post_count'] ?> Discussions</span>
                    </div>
                <?php else: ?>
                    <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(37,99,235,0.08); border-radius: 999px; font-size: 12px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
                        <i class="bi bi-chat-dots-fill"></i> Peer Collaboration
                    </div>
                    <h1 style="font-size: clamp(2rem, 3.2vw, 2.5rem); font-weight: 800; color: var(--dark); margin: 0 0 10px; letter-spacing: -0.02em;">
                        Learning Community
                    </h1>
                    <p style="color: var(--muted); font-size: 15.5px; margin: 0; max-width: 680px; line-height: 1.6;">
                        Learn alongside peers, ask questions, share project breakthroughs, and discuss code in supportive, moderated spaces.
                    </p>
                <?php endif; ?>
            </div>

            <?php if ($selectedGroup && $membership && $membership['status'] === 'active' && !$selectedPost): ?>
                <button type="button" class="btn btn-primary" onclick="window.AliModal?.open('newPostModal')" style="box-shadow: 0 4px 14px rgba(37,99,235,0.3); font-weight: 700;">
                    <i class="bi bi-plus-lg"></i> Start New Discussion
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container" style="padding: 48px 20px 88px;">
    <?php if (!$selectedGroup): ?>
        <!-- Groups Directory Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
            <?php foreach ($groups as $g): 
                $userMemb = $currentUserId ? $communityRepo->getUserMembership($currentUserId, (int)$g['id']) : null;
                $userReq = $currentUserId ? $communityRepo->getUserJoinRequest($currentUserId, (int)$g['id']) : null;

                // Dynamic icon and accent theme
                $icon = 'bi-collection-play';
                $iconColor = '#2563EB';
                $iconBg = 'rgba(37,99,235,0.1)';
                if (str_contains($g['slug'], 'php')) {
                    $icon = 'bi-database-fill-gear';
                    $iconColor = '#2563EB';
                    $iconBg = '#EFF6FF';
                } elseif (str_contains($g['slug'], 'javascript')) {
                    $icon = 'bi-braces-asterisk';
                    $iconColor = '#D97706';
                    $iconBg = '#FEF3C7';
                } elseif (str_contains($g['slug'], 'ai')) {
                    $icon = 'bi-cpu-fill';
                    $iconColor = '#7C3AED';
                    $iconBg = '#F5F3FF';
                } elseif (str_contains($g['slug'], 'web')) {
                    $icon = 'bi-laptop-fill';
                    $iconColor = '#059669';
                    $iconBg = '#ECFDF5';
                }
            ?>
                <div class="community-card-pro" data-animate="fade-up">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                <i class="bi <?= $icon ?>"></i>
                            </div>

                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span class="badge <?= $g['is_private'] ? 'badge-warning' : 'badge-success' ?>" style="font-weight: 700; padding: 4px 10px;">
                                    <i class="bi <?= $g['is_private'] ? 'bi-lock-fill' : 'bi-globe' ?>"></i> 
                                    <?= $g['is_private'] ? 'Private' : 'Public' ?>
                                </span>
                            </div>
                        </div>

                        <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 10px; line-height: 1.4;">
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" style="color: var(--dark); text-decoration: none; transition: color 0.2s ease;">
                                <?= Sanitizer::e($g['name']) ?>
                            </a>
                        </h3>

                        <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin-bottom: 24px;">
                            <?= Sanitizer::e($g['description']) ?>
                        </p>
                    </div>

                    <div style="border-top: 1px solid var(--border-color); padding-top: 20px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 14px; font-size: 13px; color: var(--muted); font-weight: 600;">
                            <span><i class="bi bi-people-fill" style="color: var(--primary);"></i> <?= (int)$g['member_count'] ?></span>
                            <span><i class="bi bi-chat-text-fill" style="color: #7C3AED;"></i> <?= (int)$g['post_count'] ?></span>
                        </div>
                        
                        <?php if ($userMemb && $userMemb['status'] === 'active'): ?>
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                                Enter Group &rarr;
                            </a>
                        <?php elseif ($userReq && $userReq['status'] === 'pending'): ?>
                            <span class="badge badge-warning" style="font-weight: 600;"><i class="bi bi-hourglass-split"></i> Request Pending</span>
                        <?php else: ?>
                            <a href="<?= baseUrl('community.php?group=' . urlencode($g['slug'])) ?>" class="btn btn-primary btn-sm" style="box-shadow: 0 2px 8px rgba(37,99,235,0.2); font-weight: 600;">
                                <?= $g['is_private'] ? 'Request Access' : 'View Group' ?> &rarr;
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php elseif ($selectedPost): ?>
        <!-- Single Post & Threaded Replies View -->
        <div style="max-width: 860px; margin: 0 auto;">
            <!-- Post Card -->
            <div class="card" data-animate="fade-up" style="padding: 36px 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); margin-bottom: 32px; background: #FFFFFF;">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 20px;">
                    <div class="avatar" style="width: 42px; height: 42px; font-size: 14px; background: linear-gradient(135deg, #2563EB, #7C3AED); color: #FFFFFF; font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <?= strtoupper(substr($selectedPost['author_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 15px; color: var(--dark);"><?= Sanitizer::e($selectedPost['author_name']) ?></div>
                        <div style="font-size: 12px; color: var(--muted);"><?= Sanitizer::timeAgo($selectedPost['created_at']) ?></div>
                    </div>
                </div>

                <h2 style="font-size: 1.65rem; font-weight: 800; color: var(--dark); margin: 0 0 16px; line-height: 1.4;">
                    <?= Sanitizer::e($selectedPost['title']) ?>
                </h2>

                <div style="font-size: 15px; line-height: 1.7; color: #334155; margin-bottom: 24px;">
                    <?= nl2br(Sanitizer::e($selectedPost['content'])) ?>
                </div>
            </div>

            <!-- Replies Section -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0;">
                    Community Replies (<?= count($replies) ?>)
                </h3>
            </div>

            <?php if (empty($replies)): ?>
                <div class="empty-state" style="background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg); padding: 36px; margin-bottom: 32px;">
                    <div class="empty-icon"><i class="bi bi-chat-square-dots"></i></div>
                    <div class="empty-title">No Replies Yet</div>
                    <div class="empty-desc">Be the first learner to share insight or constructive feedback!</div>
                </div>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 32px;">
                    <?php foreach ($replies as $r): ?>
                        <div class="card" style="padding: 20px 24px; border: 1px solid var(--border-color); border-radius: var(--radius-lg); background: #FFFFFF;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <div class="avatar" style="width: 32px; height: 32px; font-size: 12px; background: #EFF6FF; color: var(--primary); font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <?= strtoupper(substr($r['author_name'], 0, 1)) ?>
                                </div>
                                <div>
                                    <span style="font-weight: 700; font-size: 13.5px; color: var(--dark);"><?= Sanitizer::e($r['author_name']) ?></span>
                                    <span style="font-size: 11.5px; color: var(--muted); margin-left: 6px;">&bull; <?= Sanitizer::timeAgo($r['created_at']) ?></span>
                                </div>
                            </div>
                            <div style="font-size: 14.5px; line-height: 1.6; color: #334155; padding-left: 42px;">
                                <?= nl2br(Sanitizer::e($r['content'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Post Reply Form -->
            <?php if (Auth::check()): ?>
                <div class="card" style="padding: 28px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                    <h4 style="margin: 0 0 14px; font-size: 15px; font-weight: 700; color: var(--dark);">Join the Conversation</h4>
                    <form method="POST" action="<?= baseUrl('api/community/create-reply.php') ?>">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="post_id" value="<?= $selectedPost['id'] ?>">
                        <div class="form-group" style="margin-bottom: 16px;">
                            <textarea name="content" class="form-control" placeholder="Share your perspective or answer constructively..." required rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-chat-dots-fill"></i> Post Reply
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 24px; background: #FFFFFF; border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
                    <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary btn-sm">Log in to participate in discussions</a>
                </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <!-- Group Feed & Rules Sidebar Layout -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)) 300px; gap: 36px; align-items: flex-start;">
            <!-- Discussions Feed -->
            <div>
                <?php if ($selectedGroup['is_private'] && (!$membership || $membership['status'] !== 'active') && !Auth::canAccessAdmin()): ?>
                    <!-- Private Group Locked Screen -->
                    <div class="card" data-animate="fade-up" style="padding: 56px 36px; text-align: center; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 20px;">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <h2 style="font-size: 1.6rem; font-weight: 800; color: var(--dark); margin: 0 0 12px;">Private Discussion Group</h2>
                        <p style="color: var(--muted); max-width: 480px; margin: 0 auto 28px; font-size: 14.5px; line-height: 1.6;">
                            This group is moderated for approved course students. Submit a join request to participate in discussions.
                        </p>

                        <?php if ($joinRequest && $joinRequest['status'] === 'pending'): ?>
                            <span class="badge badge-warning" style="font-size: 14px; padding: 10px 20px;">
                                <i class="bi bi-hourglass-split"></i> Join Request Pending Moderator Review
                            </span>
                        <?php elseif (Auth::check()): ?>
                            <form method="POST" action="<?= baseUrl('api/community/request-join.php') ?>">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="group_id" value="<?= $selectedGroup['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                                    <i class="bi bi-person-plus-fill"></i> Request to Join Group
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="<?= baseUrl('login.php') ?>" class="btn btn-primary btn-lg">
                                Log In to Request Access
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Authorized Members Feed -->
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <?php if (empty($posts)): ?>
                            <div class="empty-state" data-animate="fade-up" style="background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-xl); padding: 48px 24px;">
                                <div class="empty-icon"><i class="bi bi-chat-square-text"></i></div>
                                <div class="empty-title">No Discussions Started Yet</div>
                                <div class="empty-desc" style="margin-bottom: 20px;">Be the first student to start a topic or share notes in this group!</div>
                                <button type="button" class="btn btn-primary btn-sm" onclick="window.AliModal?.open('newPostModal')">
                                    <i class="bi bi-plus-lg"></i> Start First Discussion
                                </button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($posts as $p): ?>
                                <div class="card" data-animate="fade-up" style="padding: 24px 28px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <div class="avatar" style="width: 32px; height: 32px; font-size: 12px; background: #EFF6FF; color: var(--primary); font-weight: 700; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                <?= strtoupper(substr($p['author_name'], 0, 1)) ?>
                                            </div>
                                            <span style="font-size: 13.5px; font-weight: 700; color: var(--dark);"><?= Sanitizer::e($p['author_name']) ?></span>
                                            <span style="font-size: 12px; color: var(--muted);">&bull; <?= Sanitizer::timeAgo($p['created_at']) ?></span>
                                        </div>
                                        <span class="badge badge-secondary" style="font-size: 11px;">
                                            <i class="bi bi-chat"></i> <?= (int)$p['replies_count'] ?> replies
                                        </span>
                                    </div>

                                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 10px;">
                                        <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug']) . '&post=' . $p['id']) ?>" style="color: var(--dark); text-decoration: none;">
                                            <?= Sanitizer::e($p['title']) ?>
                                        </a>
                                    </h3>

                                    <p style="font-size: 14px; color: var(--muted); margin-bottom: 16px; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?= Sanitizer::e($p['content']) ?>
                                    </p>

                                    <div>
                                        <a href="<?= baseUrl('community.php?group=' . urlencode($selectedGroup['slug']) . '&post=' . $p['id']) ?>" class="btn btn-outline btn-sm">
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
                <div class="card" style="padding: 24px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
                    <h4 style="margin: 0 0 12px; font-size: 15px; font-weight: 700; color: var(--dark); display: flex; align-items: center; gap: 8px;">
                        <i class="bi bi-info-circle-fill" style="color: var(--primary);"></i> About This Group
                    </h4>
                    <p style="font-size: 13.5px; color: var(--muted); line-height: 1.6; margin-bottom: 20px;">
                        <?= Sanitizer::e($selectedGroup['description']) ?>
                    </p>

                    <?php if (!empty($selectedGroup['rules'])): ?>
                        <h5 style="margin: 0 0 8px; font-size: 13px; font-weight: 700; color: var(--dark);">Community Guidelines</h5>
                        <div style="font-size: 12.5px; color: var(--muted); line-height: 1.6; background: #F8FAFC; border: 1px solid var(--border-color); padding: 12px; border-radius: var(--radius-md);">
                            <?= nl2br(Sanitizer::e($selectedGroup['rules'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Modal: New Post -->
        <div class="modal-backdrop" id="newPostModal">
            <div class="modal-dialog" style="max-width: 560px;">
                <div class="modal-header">
                    <h3 class="modal-title" style="font-weight: 800; font-size: 1.25rem;">Start New Discussion</h3>
                    <button type="button" class="modal-close" onclick="window.AliModal?.close('newPostModal')">&times;</button>
                </div>
                <form method="POST" action="<?= baseUrl('api/community/create-post.php') ?>">
                    <?= Csrf::field() ?>
                    <input type="hidden" name="group_id" value="<?= $selectedGroup['id'] ?>">

                    <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="post_title" style="font-weight: 600; font-size: 13px;">Discussion Topic Title</label>
                            <input type="text" id="post_title" name="title" class="form-control" required placeholder="e.g. Question regarding Lesson 3 PDO query binding">
                        </div>

                        <div class="form-group" style="margin: 0;">
                            <label class="form-label" for="post_content" style="font-weight: 600; font-size: 13px;">Message Content</label>
                            <textarea id="post_content" name="content" class="form-control" required placeholder="Describe what you are trying to solve or share with your peers..." rows="5"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer" style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline btn-sm" onclick="window.AliModal?.close('newPostModal')">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-send-fill"></i> Post Discussion</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
