<?php
declare(strict_types=1);

namespace App\Helpers;

/**
 * HTTP & JSON Response Helper
 */
class Response {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void {
        self::json([
            'success' => true,
            'message' => $message,
            'data'    => $data
        ], $statusCode);
    }

    public static function error(string $message = 'An error occurred', int $statusCode = 400, mixed $errors = null): void {
        self::json([
            'success' => false,
            'error'   => $message,
            'errors'  => $errors
        ], $statusCode);
    }

    public static function redirect(string $path): void {
        header('Location: ' . baseUrl($path));
        exit;
    }
}
