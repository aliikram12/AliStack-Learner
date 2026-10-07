<?php
require_once __DIR__ . '/../config/database.php';
$pdo = getDbConnection();
$key = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';
$stmt = $pdo->prepare("INSERT INTO platform_settings (setting_key, setting_value, setting_group, description) 
    VALUES ('ai_api_key', ?, 'ai', 'Server API Key') 
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
$stmt->execute([$key]);
echo "Updated platform_settings with API key.\n";
