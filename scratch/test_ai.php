<?php
$apiKey = 'sk-KYKQ34ydr6CwXqChci18VNCjEorYXDAzbjMan8jIkgeTg9zt';

$providers = [
    'OpenRouter' => ['url' => 'https://openrouter.ai/api/v1/chat/completions', 'model' => 'meta-llama/llama-3-8b-instruct'],
    'Groq' => ['url' => 'https://api.groq.com/openai/v1/chat/completions', 'model' => 'llama-3.1-8b-instant'],
    'DeepSeek' => ['url' => 'https://api.deepseek.com/chat/completions', 'model' => 'deepseek-chat'],
    'Together' => ['url' => 'https://api.together.xyz/v1/chat/completions', 'model' => 'meta-llama/Meta-Llama-3.1-8B-Instruct-Turbo'],
    'Mistral' => ['url' => 'https://api.mistral.ai/v1/chat/completions', 'model' => 'mistral-tiny'],
    'AgentRouter_api' => ['url' => 'https://api.agentrouter.org/v1/chat/completions', 'model' => 'gpt-4o-mini'],
    'AgentRouter_v1' => ['url' => 'https://agentrouter.org/v1/chat/completions', 'model' => 'gpt-3.5-turbo'],
];

foreach ($providers as $name => $cfg) {
    $ch = curl_init($cfg['url']);
    $payload = [
        'model' => $cfg['model'],
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
            'HTTP-Referer: http://localhost',
            'X-Title: AliStack'
        ],
        CURLOPT_TIMEOUT => 8,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "Provider {$name}: Code {$code} -> " . substr($res ?: 'No response', 0, 150) . "\n";
}
