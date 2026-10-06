<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\SettingRepository;
use PDO;

class AiService {
    protected PDO $db;
    protected SettingRepository $settings;

    public function __construct() {
        $this->db = getDbConnection();
        $this->settings = new SettingRepository();
    }

    /**
     * Send student message with lesson context to AgentRouter API
     */
    public function askTutor(
        int $userId,
        string $userMessage,
        ?int $courseId = null,
        ?int $lessonId = null,
        ?int $conversationId = null,
        ?string $additionalContext = null
    ): array {
        // 1. Rate limit check (daily prompts per user)
        $dailyLimit = (int)($this->settings->get('ai_daily_limit_per_user', 50));
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM ai_usage_logs 
            WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)
        ");
        $stmt->execute([$userId]);
        $dailyCount = (int)$stmt->fetchColumn();

        if ($dailyCount >= $dailyLimit) {
            return [
                'success' => false,
                'error' => "You have reached your daily limit of {$dailyLimit} AI Tutor queries. Please check back tomorrow or review your saved notes."
            ];
        }

        // 2. Resolve or create conversation
        if (!$conversationId) {
            $conversationId = $this->getOrCreateConversation($userId, $courseId, $lessonId, $userMessage);
        }

        // 3. Gather Context from Course & Lesson
        $contextInfo = $this->gatherContext($courseId, $lessonId, $additionalContext);

        // 4. Retrieve recent message history (up to last 6 messages for context continuity)
        $historyMessages = $this->getConversationHistory($conversationId, 6);

        // 5. Build system prompt & messages array
        $aiConfig = require dirname(__DIR__, 2) . '/config/ai.php';
        $apiKey = getenv('AGENTROUTER_API_KEY') ?: $this->settings->get('ai_api_key', '');
        $endpoint = $this->settings->get('ai_provider_endpoint', $aiConfig['endpoint']);
        $model = $this->settings->get('ai_model_name', $aiConfig['model']);

        $systemPrompt = $aiConfig['system_prompt'];
        if (!empty($contextInfo)) {
            $systemPrompt .= "\n\n--- CURRENT LEARNING CONTEXT ---\n" . $contextInfo;
        }

        $messagesPayload = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($historyMessages as $msg) {
            $messagesPayload[] = [
                'role' => ($msg['sender'] === 'assistant') ? 'assistant' : 'user',
                'content' => $msg['message']
            ];
        }

        // Append current message
        $messagesPayload[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Store user message in DB
        $this->storeMessage($conversationId, 'user', $userMessage, 0);

        // 6. Call AgentRouter API
        $startTime = microtime(true);
        $apiResult = $this->callAgentRouter($endpoint, $apiKey, $model, $messagesPayload);
        $durationMs = (int)((microtime(true) - $startTime) * 1000);

        if (!$apiResult['success']) {
            return [
                'success' => false,
                'conversation_id' => $conversationId,
                'error' => $apiResult['error']
            ];
        }

        $aiReply = $apiResult['reply'];
        $promptTokens = $apiResult['prompt_tokens'] ?? 0;
        $completionTokens = $apiResult['completion_tokens'] ?? 0;

        // Store assistant message
        $this->storeMessage($conversationId, 'assistant', $aiReply, $completionTokens);

        // Log AI Usage
        $logStmt = $this->db->prepare("
            INSERT INTO ai_usage_logs (user_id, tokens_prompt, tokens_completion, endpoint_used, response_time_ms, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $logStmt->execute([$userId, $promptTokens, $completionTokens, $endpoint, $durationMs]);

        return [
            'success' => true,
            'conversation_id' => $conversationId,
            'reply' => $aiReply,
            'remaining_queries' => max(0, $dailyLimit - ($dailyCount + 1))
        ];
    }

    /**
     * Gather course and lesson context
     */
    protected function gatherContext(?int $courseId, ?int $lessonId, ?string $additionalContext): string {
        $context = [];
        if ($courseId) {
            $stmt = $this->db->prepare("SELECT title, short_desc, difficulty, language FROM courses WHERE id = ?");
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
            if ($course) {
                $context[] = "Course: {$course['title']} (Level: {$course['difficulty']}, Language: {$course['language']})";
                $context[] = "Course Overview: {$course['short_desc']}";
            }
        }

        if ($lessonId) {
            $stmt = $this->db->prepare("SELECT title, description, learning_objectives FROM course_lessons WHERE id = ?");
            $stmt->execute([$lessonId]);
            $lesson = $stmt->fetch();
            if ($lesson) {
                $context[] = "Current Lesson: {$lesson['title']}";
                if (!empty($lesson['description'])) {
                    $context[] = "Lesson Outline: {$lesson['description']}";
                }
                if (!empty($lesson['learning_objectives'])) {
                    $context[] = "Lesson Objectives: {$lesson['learning_objectives']}";
                }
            }
        }

        if ($additionalContext) {
            $context[] = "Student Note / Code Excerpt: " . substr(trim($additionalContext), 0, 500);
        }

        return implode("\n", $context);
    }

    /**
     * Execute HTTP POST to AgentRouter API using cURL
     */
    protected function callAgentRouter(string $endpoint, string $apiKey, string $model, array $messages): array {
        if (empty($apiKey)) {
            // Graceful diagnostic when API key hasn't been set yet
            return [
                'success' => false,
                'error' => "AgentRouter API Key is not configured yet. The administrator must add the AGENTROUTER_API_KEY in the server environment (.env) or the Admin Settings panel."
            ];
        }

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1000
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
                'User-Agent: AliStack-Learner/1.0'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log("AgentRouter cURL error: " . $curlError);
            return [
                'success' => false,
                'error' => "Unable to connect to the AI tutor service at this moment. Please check your network or try again."
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400 || empty($decoded)) {
            $msg = $decoded['error']['message'] ?? "AI provider returned status code {$httpCode}.";
            error_log("AgentRouter error response: {$response}");
            return [
                'success' => false,
                'error' => "The AI Tutor could not process the request ({$msg}). Please try again or notify support."
            ];
        }

        $reply = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$reply) {
            return [
                'success' => false,
                'error' => "The AI Tutor returned an empty response. Please ask your question again."
            ];
        }

        return [
            'success' => true,
            'reply' => trim($reply),
            'prompt_tokens' => $decoded['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $decoded['usage']['completion_tokens'] ?? 0
        ];
    }

    public function getOrCreateConversation(int $userId, ?int $courseId, ?int $lessonId, string $firstMessage): int {
        // Find existing conversation for this lesson or course
        if ($lessonId) {
            $stmt = $this->db->prepare("SELECT id FROM ai_conversations WHERE user_id = ? AND lesson_id = ? ORDER BY id DESC LIMIT 1");
            $stmt->execute([$userId, $lessonId]);
            $id = $stmt->fetchColumn();
            if ($id) return (int)$id;
        }

        $title = substr(trim($firstMessage), 0, 50);
        $stmt = $this->db->prepare("
            INSERT INTO ai_conversations (user_id, course_id, lesson_id, title, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$userId, $courseId ?: null, $lessonId ?: null, $title]);
        return (int)$this->db->lastInsertId();
    }

    public function getConversationHistory(int $conversationId, int $limit = 10): array {
        $stmt = $this->db->prepare("
            SELECT * FROM (
                SELECT * FROM ai_messages 
                WHERE conversation_id = ? 
                ORDER BY id DESC 
                LIMIT ?
            ) sub ORDER BY id ASC
        ");
        $stmt->bindValue(1, $conversationId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserConversations(int $userId, ?int $courseId = null): array {
        $sql = "SELECT ac.*, c.title as course_title, cl.title as lesson_title,
                (SELECT message FROM ai_messages m WHERE m.conversation_id = ac.id ORDER BY m.id DESC LIMIT 1) as last_message
                FROM ai_conversations ac
                LEFT JOIN courses c ON c.id = ac.course_id
                LEFT JOIN course_lessons cl ON cl.id = ac.lesson_id
                WHERE ac.user_id = ?";
        $params = [$userId];
        if ($courseId) {
            $sql .= " AND ac.course_id = ?";
            $params[] = $courseId;
        }
        $sql .= " ORDER BY ac.updated_at DESC LIMIT 20";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function storeMessage(int $conversationId, string $sender, string $message, int $tokens): int {
        $stmt = $this->db->prepare("
            INSERT INTO ai_messages (conversation_id, sender, message, tokens_used, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$conversationId, $sender, $message, $tokens]);
        
        $this->db->prepare("UPDATE ai_conversations SET updated_at = NOW() WHERE id = ?")->execute([$conversationId]);
        return (int)$this->db->lastInsertId();
    }

    public function clearConversation(int $userId, int $conversationId): bool {
        // Ensure ownership
        $stmt = $this->db->prepare("SELECT id FROM ai_conversations WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->execute([$conversationId, $userId]);
        if (!$stmt->fetchColumn()) {
            return false;
        }
        $del = $this->db->prepare("DELETE FROM ai_conversations WHERE id = ?");
        return $del->execute([$conversationId]);
    }
}
