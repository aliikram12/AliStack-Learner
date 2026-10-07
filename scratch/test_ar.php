<?php
$apiKey = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';

$userAgents = [
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'OpenAI/PHP',
    'curl/7.88.1'
];

foreach ($userAgents as $ua) {
    $ch = curl_init('https://agentrouter.org/v1/chat/completions');
    $payload = [
        'model' => 'gpt-4o-mini',
        'messages' => [['role' => 'user', 'content' => 'Hi']],
        'max_tokens' => 10
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'User-Agent: ' . $ua
        ],
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "UA: $ua\nCode: $code\nBody: $res\n\n";
}
