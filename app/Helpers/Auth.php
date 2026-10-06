<?php
declare(strict_types=1);

namespace App\Helpers;

use PDO;

/**
 * Authentication and Authorization Helper
 * Handles session state, role validation, and login/logout workflows.
 */
class Auth {
    public static function check(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($_SESSION['user']) && !empty($_SESSION['user']['id']);
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return $_SESSION['user'];
    }

    public static function id(): ?int {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function role(): ?string {
        $u = self::user();
        return $u['role'] ?? null;
    }

    public static function isSuperAdmin(): bool {
        return self::role() === 'super_admin';
    }

    public static function isAdmin(): bool {
        return in_array(self::role(), ['super_admin', 'admin'], true);
    }

    public static function isModerator(): bool {
        return in_array(self::role(), ['super_admin', 'admin', 'moderator'], true);
    }

    public static function isStudent(): bool {
        return self::role() === 'student';
    }

    public static function canAccessAdmin(): bool {
        return self::isAdmin() || self::isModerator();
    }

    public static function login(array $user): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Prevent session fixation
        if (!headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'username' => $user['username'],
            'role' => $user['role'],
            'status' => $user['status'],
            'avatar_url' => $user['avatar_url'] ?? null
        ];

        // Update last login timestamp in database
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
            $stmt->execute([(int)$user['id']]);
        } catch (\Throwable $e) {
            error_log("Failed to update last_login_at: " . $e->getMessage());
        }
    }

    public static function logout(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function requireAuth(string $redirectUrl = '/login.php'): void {
        if (!self::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/dashboard.php';
            header("Location: " . baseUrl($redirectUrl));
            exit;
        }
    }

    public static function requireAdmin(): void {
        self::requireAuth();
        if (!self::canAccessAdmin()) {
            http_response_code(403);
            die("Access Denied: You do not have permission to access the administrative area.");
        }
    }

    public static function requireSuperAdmin(): void {
        self::requireAuth();
        if (!self::isSuperAdmin()) {
            http_response_code(403);
            die("Access Denied: Super Administrator privileges required.");
        }
    }
}
