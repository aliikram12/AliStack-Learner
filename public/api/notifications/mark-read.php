<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

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

$userId = (int)Auth::id();
$repo = new NotificationRepository();

if (!empty($data['all'])) {
    $repo->markAllAsRead($userId);
    Response::success(null, 'All notifications marked as read');
}

$notifId = !empty($data['id']) ? (int)$data['id'] : 0;
if ($notifId) {
    $repo->markAsRead($userId, $notifId);
    Response::success(null, 'Notification marked as read');
}

Response::error('Notification ID or all flag required', 400);
