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

$loginIdentifier = $data['login_identifier'] ?? $data['email'] ?? '';
$password = $data['password'] ?? '';

$authService = new AuthService();
$result = $authService->login($loginIdentifier, $password);

if (!$result['success']) {
    Response::error($result['error'], 401);
}

$redirectUrl = $_SESSION['intended_url'] ?? 'dashboard.php';
unset($_SESSION['intended_url']);

Response::success([
    'user' => [
        'id' => $result['user']['id'],
        'full_name' => $result['user']['full_name'],
        'email' => $result['user']['email'],
        'role' => $result['user']['role']
    ],
    'redirect_url' => baseUrl($redirectUrl)
], 'Login successful');
