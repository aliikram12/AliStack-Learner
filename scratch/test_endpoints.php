<?php
$token = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';

$endpoints = [
    '/v1/dashboard/billing/subscription',
    '/v1/dashboard/billing/usage',
    '/api/token',
    '/api/user/self',
    '/api/user/models'
];

foreach ($endpoints as $ep) {
    $ch = curl_init("https://agentrouter.org" . $ep);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'User-Agent: claude-code/1.0.0'
        ],
        CURLOPT_TIMEOUT => 6,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$ep -> Code $code: " . substr($res ?: '', 0, 150) . "\n";
}
