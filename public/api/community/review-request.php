<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\CommunityRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::canAccessAdmin()) {
    Response::error('Administrative or moderator access required', 403);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$requestId = !empty($data['request_id']) ? (int)$data['request_id'] : 0;
$action = trim((string)($data['action'] ?? '')); // 'approved' or 'rejected'

if (!$requestId || !in_array($action, ['approved', 'rejected'], true)) {
    Response::error('request_id and valid action (approved/rejected) are required', 400);
}

$reviewerId = (int)Auth::id();
$repo = new CommunityRepository();
$pdo = getDbConnection();

$stmt = $pdo->prepare("SELECT gjr.*, g.name as group_name, g.slug as group_slug FROM group_join_requests gjr JOIN discussion_groups g ON g.id = gjr.group_id WHERE gjr.id = ?");
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request) {
    Response::error('Join request not found', 404);
}

$success = $repo->reviewJoinRequest($requestId, $action, $reviewerId);

// Send student notification
$notifRepo = new NotificationRepository();
if ($action === 'approved') {
    $notifRepo->create(
        (int)$request['user_id'],
        "Group Request Approved",
        "Your request to join '{$request['group_name']}' has been approved. You may now post and participate.",
        "community.php?group={$request['group_slug']}",
        "community"
    );
} else {
    $notifRepo->create(
        (int)$request['user_id'],
        "Group Request Update",
        "Your request to join '{$request['group_name']}' was not approved at this time.",
        "community.php",
        "community"
    );
}

// Audit log
$settingRepo = new SettingRepository();
$settingRepo->logAudit($reviewerId, 'REVIEW_GROUP_REQUEST', 'group_join_requests', $requestId, "Request {$action} for user ID {$request['user_id']}");

Response::success([
    'request_id' => $requestId,
    'status' => $action
], "Request {$action} successfully");
