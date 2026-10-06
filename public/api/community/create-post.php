<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CommunityRepository;
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

$groupId = !empty($data['group_id']) ? (int)$data['group_id'] : 0;
$title = trim((string)($data['title'] ?? ''));
$content = trim((string)($data['content'] ?? ''));

if (!$groupId || empty($title) || empty($content)) {
    Response::error('Group ID, title, and content are required', 400);
}

$userId = (int)Auth::id();
$repo = new CommunityRepository();
$group = $repo->getGroupById($groupId);

if (!$group || !$group['is_active']) {
    Response::error('Discussion group not found', 404);
}

// If private group, check membership
if ($group['is_private'] && !Auth::canAccessAdmin()) {
    $membership = $repo->getUserMembership($userId, $groupId);
    if (!$membership || $membership['status'] !== 'active') {
        Response::error('You must be an approved member to post in this group', 403);
    }
}

$postId = $repo->createPost($groupId, $userId, $title, $content);

Response::success([
    'post_id' => $postId,
    'redirect_url' => baseUrl("community.php?group={$group['slug']}&post={$postId}")
], 'Post created successfully');
