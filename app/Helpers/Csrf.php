<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * CSRF Protection Helper
 * Generates and validates cryptographically secure tokens.
 */
class Csrf {
    public static function getToken(): string {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function token(): string {
        return self::getToken();
    }

    public static function field(): string {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function meta(): string {
        $token = self::getToken();
        return '<meta name="csrf-token" content="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function validate(?string $token = null): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    public static function checkOrAbort(): void {
        if (!self::validate()) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || (isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))) {
                header('Content-Type: application/json', true, 403);
                echo json_encode(['success' => false, 'error' => 'Invalid or expired CSRF token. Please refresh the page and try again.']);
                exit;
            }
            http_response_code(403);
            die('Forbidden: CSRF validation failed. Please return to the previous page and refresh.');
        }
    }
}
