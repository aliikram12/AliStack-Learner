<?php
declare(strict_types=1);

/**
 * AliStack Learner - PSR-4 Compatible Autoloader
 * Powered by AliStack
 * 
 * Automatically loads App\ classes without requiring composer install.
 */

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// Require config & helpers
require_once dirname(__DIR__) . '/config/app.php';
