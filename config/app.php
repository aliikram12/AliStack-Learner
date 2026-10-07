<?php
declare(strict_types=1);

/**
 * AliStack Learner - Application Configuration & Bootstrap
 * Powered by AliStack
 */

require_once __DIR__ . '/database.php';

// Set application environment
$appEnv = getenv('APP_ENV') ?: 'local';
if ($appEnv === 'local') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/error.log');

// Timezone
date_default_timezone_set(getenv('APP_TIMEZONE') ?: 'UTC');

// Secure Session Initialization
if (session_status() === PHP_SESSION_NONE) {
    // Cookie security parameters
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    
    session_set_cookie_params([
        'lifetime' => 86400 * 7, // 7 days
        'path' => '/',
        'domain' => '',
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}

// Global application URL helper
if (!function_exists('baseUrl')) {
    function baseUrl(string $path = ''): string {
        // When accessed via browser/HTTP, dynamically detect host and path
        if (!empty($_SERVER['HTTP_HOST'])) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            
            $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
            
            if (str_contains($scriptName, '/public/')) {
                $baseDir = substr($scriptName, 0, strpos($scriptName, '/public/') + 7);
            } elseif (str_ends_with($scriptName, '/public')) {
                $baseDir = $scriptName;
            } else {
                $dir = dirname($scriptName);
                if ($dir === '/' || $dir === '\\' || $dir === '.' || str_starts_with($scriptName, '/admin/') || str_starts_with($scriptName, '/api/')) {
                    $baseDir = '';
                } else {
                    $baseDir = $dir;
                }
            }

            $base = rtrim($protocol . $host . $baseDir, '/');
            return $base . '/' . ltrim($path, '/');
        }

        // CLI fallback
        $configured = getenv('APP_URL') ?: 'http://localhost:8000';
        return rtrim($configured, '/') . '/' . ltrim($path, '/');
    }
}

// Global asset URL helper
if (!function_exists('assetUrl')) {
    function assetUrl(string $path): string {
        return baseUrl('assets/' . ltrim($path, '/'));
    }
}
