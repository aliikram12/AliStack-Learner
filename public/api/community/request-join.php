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
if (!$groupId) {
    Response::error('group_id is required', 400);
}

$userId = (int)Auth::id();
$repo = new CommunityRepository();
$group = $repo->getGroupById($groupId);

if (!$group || !$group['is_active']) {
    Response::error('Discussion group not found or inactive', 404);
}

$ok = $repo->createJoinRequest($userId, $groupId);
if (!$ok) {
    Response::error('Unable to submit request', 500);
}

Response::success([
    'group_id' => $groupId,
    'status' => $group['is_private'] ? 'pending' : 'active'
], $group['is_private'] ? 'Membership request submitted for review' : 'You have joined the group');
