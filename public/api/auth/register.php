<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Services\AuthService;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

// CSRF validation
Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$authService = new AuthService();
$result = $authService->register($data);

if (!$result['success']) {
    Response::error('Validation failed', 422, $result['errors'] ?? null);
}

Response::success([
    'user' => [
        'id' => $result['user']['id'],
        'full_name' => $result['user']['full_name'],
        'email' => $result['user']['email']
    ],
    'redirect_url' => baseUrl('dashboard.php')
], 'Registration successful', 201);
