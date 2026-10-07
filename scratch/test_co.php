<?php
$apiKey = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';

$urls = [
    'https://co.agentrouter.org/v1/chat/completions',
    'https://co.agentrouter.org/v1/models',
    'https://api.agentrouter.com/v1/chat/completions',
    'https://agentrouter.org/v1/chat/completions'
];

foreach ($urls as $url) {
    echo "=== Testing $url ===\n";
    $ch = curl_init($url);
    if (str_contains($url, 'models')) {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    } else {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'model' => 'gpt-4o-mini',
            'messages' => [['role' => 'user', 'content' => 'Say hi']]
        ]));
    }
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "Code: $code\nResult: " . substr($res ?: 'Empty', 0, 300) . "\n\n";
}
