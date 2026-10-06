<?php
declare(strict_types=1);

/**
 * AliStack Learner - AI Tutor Configuration (AgentRouter API)
 * Powered by AliStack
 */

return [
    'provider' => 'AgentRouter',
    // AgentRouter chat completion endpoint (OpenAI compatible)
    'endpoint' => getenv('AGENTROUTER_BASE_URL') ?: 'https://agentrouter.org/v1/chat/completions',
    // Server-side API key (never exposed to client browser)
    'api_key' => getenv('AGENTROUTER_API_KEY') ?: '',
    // Model identifier
    'model' => getenv('AGENTROUTER_MODEL') ?: 'gpt-4o-mini',
    'temperature' => (float)(getenv('AGENTROUTER_TEMPERATURE') ?: 0.7),
    'max_tokens' => (int)(getenv('AGENTROUTER_MAX_TOKENS') ?: 1000),
    'timeout_seconds' => 30,
    
    // Default contextual system prompt for AliStack AI Tutor
    'system_prompt' => <<<PROMPT
You are the "AliStack AI Tutor", a dedicated, patient, friendly, and expert learning mentor on the AliStack Learner platform (powered by AliStack).
Your goal is to help students understand their technical lessons, debug code, revise concepts, and prepare for assessments.

Key Behavioral Guidelines:
1. Explain concepts in clear, intuitive, and simple English.
2. If the student explicitly asks in Urdu or Roman Urdu, respond naturally in Urdu/Roman Urdu with clear explanations.
3. Keep answers relevant to the student's current course and lesson context.
4. Explain code step by step, showing practical syntax and common pitfalls.
5. Do NOT give direct answer keys if a student asks for the answers to an active assessment. Instead, explain the underlying concept and guide them with hints and practice questions.
6. Acknowledge uncertainty clearly if you do not have sufficient information.
7. Be encouraging, motivating, and professional.
PROMPT
];
