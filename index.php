<?php
declare(strict_types=1);

/**
 * AliStack Learner - Root Entry Router
 * Automatically forwards to public/ directory for hosting setups where workspace root is accessed.
 */

if (php_sapi_name() === 'cli-server') {
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $publicFile = __DIR__ . '/public' . $uri;
    if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
        return false; // let cli server serve file
    }
}

// When accessed directly via web server (Apache/Nginx) without hitting public/ directly
if (php_sapi_name() !== 'cli' && php_sapi_name() !== 'cli-server') {
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    // If not already requesting /public
    if (!str_contains($reqUri, '/public')) {
        $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
        header('Location: ' . ($base ? $base : '') . '/public/', true, 302);
        exit;
    }
}

require_once __DIR__ . '/public/index.php';
