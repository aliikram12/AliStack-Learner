<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CommunityRepository;
use App\Repositories\NotificationRepository;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::check()) {
    Response::error('Authentication required', 401);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$postId = !empty($data['post_id']) ? (int)$data['post_id'] : 0;
$content = trim((string)($data['content'] ?? ''));
$parentReplyId = !empty($data['parent_reply_id']) ? (int)$data['parent_reply_id'] : null;

if (!$postId || empty($content)) {
    Response::error('Post ID and reply content are required', 400);
}

$userId = (int)Auth::id();
$repo = new CommunityRepository();
$post = $repo->getPostById($postId);

if (!$post) {
    Response::error('Post not found', 404);
}

if ($post['is_locked']) {
    Response::error('This discussion thread has been locked by a moderator', 403);
}

// Verify group membership if private
$group = $repo->getGroupById((int)$post['group_id']);
if ($group && $group['is_private'] && !Auth::canAccessAdmin()) {
    $membership = $repo->getUserMembership($userId, (int)$group['id']);
    if (!$membership || $membership['status'] !== 'active') {
        Response::error('You must be an approved member to reply in this group', 403);
    }
}

$replyId = $repo->createReply($postId, $userId, $content, $parentReplyId);

// Send notification to post author if reply is from another user
if ((int)$post['user_id'] !== $userId) {
    $notifRepo = new NotificationRepository();
    $currentUser = Auth::user();
    $notifRepo->create(
        (int)$post['user_id'],
        "New Reply to Your Post",
        "{$currentUser['full_name']} replied to: {$post['title']}",
        "community.php?group={$post['group_slug']}&post={$postId}",
        "community"
    );
}

Response::success([
    'reply_id' => $replyId,
    'post_id' => $postId
], 'Reply posted successfully');
