<?php
$token = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';
$ch = curl_init('https://agentrouter.org/v1/dashboard/billing/subscription');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $token,
        'User-Agent: claude-code/1.0.0'
    ],
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false
]);
$res = curl_exec($ch);
curl_close($ch);
echo $res . "\n";
