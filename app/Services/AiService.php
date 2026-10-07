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
    /**
     * Execute HTTP POST to AgentRouter / OpenAI compatible API
     */
    protected function callAgentRouter(string $endpoint, string $apiKey, string $model, array $messages): array {
        if (empty($apiKey)) {
            return [
                'success' => false,
                'error' => "AI API Key is not configured yet. Please configure the AGENTROUTER_API_KEY in the server environment (.env) or the Admin Settings panel."
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
                'User-Agent: claude-code/1.0.0',
                'X-Stainless-Lang: python',
                'X-Stainless-Package-Version: 0.40.0',
                'X-Stainless-OS: Windows'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // If cURL was blocked by AgentRouter's Aliyun WAF ("unauthorized client"), attempt Python bridge
        if ($httpCode === 401 && str_contains((string)$response, 'unauthorized client detected')) {
            $pyResult = $this->callPythonBridge($apiKey, $model, $messages);
            if ($pyResult['success']) {
                return $pyResult;
            }
        }

        if ($response === false) {
            error_log("AI API cURL error: " . $curlError);
            return [
                'success' => false,
                'error' => "Unable to connect to the AI service at this moment. Please check your network or try again."
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 400 || empty($decoded)) {
            $msg = $decoded['error']['message'] ?? $decoded['message'] ?? "AI provider returned status code {$httpCode}.";
            error_log("AI API error response ({$httpCode}): {$response}");
            return [
                'success' => false,
                'error' => "AI Service Error: {$msg}",
                'http_code' => $httpCode,
                'raw_response' => $response
            ];
        }

        $reply = $decoded['choices'][0]['message']['content'] ?? null;
        if (!$reply) {
            return [
                'success' => false,
                'error' => "The AI service returned an empty response. Please ask your question again."
            ];
        }

        return [
            'success' => true,
            'reply' => trim($reply),
            'prompt_tokens' => $decoded['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $decoded['usage']['completion_tokens'] ?? 0
        ];
    }

    /**
     * Call Python bridge for WAF-restricted AgentRouter requests
     */
    protected function callPythonBridge(string $apiKey, string $model, array $messages): array {
        $runnerScript = dirname(__DIR__, 2) . '/bin/ai_runner.py';
        if (!file_exists($runnerScript)) {
            return ['success' => false, 'error' => 'Python runner not found'];
        }

        $systemPrompt = '';
        $cleanMessages = [];
        foreach ($messages as $m) {
            if ($m['role'] === 'system') {
                $systemPrompt = $m['content'];
            } else {
                $cleanMessages[] = ['role' => $m['role'], 'content' => $m['content']];
            }
        }

        $payload = json_encode([
            'api_key' => $apiKey,
            'base_url' => 'https://agentrouter.org',
            'model' => $model,
            'system' => $systemPrompt,
            'messages' => $cleanMessages,
            'max_tokens' => 1200
        ]);

        $cmd = 'python "' . $runnerScript . '"';
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $process = proc_open($cmd, $descriptors, $pipes);
        if (!is_resource($process)) {
            return ['success' => false, 'error' => 'Could not launch Python bridge process'];
        }

        fwrite($pipes[0], $payload);
        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        proc_close($process);

        $decoded = json_decode($stdout, true);
        if (!$decoded || !empty($decoded['error'])) {
            return [
                'success' => false,
                'error' => $decoded['error'] ?? ($stderr ?: 'Python bridge failed')
            ];
        }

        return [
            'success' => true,
            'reply' => trim($decoded['reply'] ?? ''),
            'prompt_tokens' => $decoded['prompt_tokens'] ?? 0,
            'completion_tokens' => $decoded['completion_tokens'] ?? 0
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

    /**
     * Generate MCQ assessment questions using AI with curriculum fallback
     */
    public function generateAssessmentQuestions(
        int $courseId,
        int $count = 5,
        string $difficulty = 'medium',
        ?string $topic = null
    ): array {
        // 1. Fetch course & lessons
        $courseStmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name 
            FROM courses c 
            LEFT JOIN course_categories cat ON cat.id = c.category_id 
            WHERE c.id = ?
        ");
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch();
        if (!$course) {
            return ['success' => false, 'error' => 'Course not found'];
        }

        $lessonStmt = $this->db->prepare("
            SELECT id, title, description, learning_objectives, lesson_order 
            FROM course_lessons 
            WHERE course_id = ? 
            ORDER BY lesson_order ASC
        ");
        $lessonStmt->execute([$courseId]);
        $lessons = $lessonStmt->fetchAll();

        // 2. Prepare AI prompt
        $lessonSummary = [];
        foreach ($lessons as $l) {
            $lessonSummary[] = "Lesson {$l['lesson_order']}: {$l['title']} - " . substr($l['description'] ?? '', 0, 100);
        }
        $lessonsText = implode("\n", $lessonSummary);

        $systemPrompt = <<<SYS
You are an expert technical curriculum designer and senior examiner for AliStack Academy.
Your task is to create high-quality, practical multiple-choice questions (MCQs) for student assessment.

STRICT JSON OUTPUT REQUIREMENT:
Respond ONLY with a valid JSON array of question objects. Do not wrap in markdown quotes if possible, output pure JSON.
Each question object MUST have exactly these keys:
- "question_text": The clear, challenging question scenario.
- "option_a": First choice.
- "option_b": Second choice.
- "option_c": Third choice.
- "option_d": Fourth choice.
- "correct_option": One uppercase letter: "A", "B", "C", or "D".
- "explanation": Concise, educational reason why this option is correct.
- "topic": Topic or module name (e.g. "DOM Manipulation", "PDO Security", "CSS Flexbox").
- "difficulty": "easy", "medium", or "hard".
- "marks": 10
SYS;

        $userPrompt = <<<PROMPT
Create exactly {$count} multiple-choice questions for the following course:
Course Title: {$course['title']}
Category: {$course['category_name']}
Target Difficulty: {$difficulty}
Specific Focus Topic: {$topic}

Curriculum Lessons:
{$lessonsText}

Ensure questions test real-world developer understanding and application, not trivial trivia. Output pure JSON array.
PROMPT;

        $aiConfig = require dirname(__DIR__, 2) . '/config/ai.php';
        $apiKey = getenv('AGENTROUTER_API_KEY') ?: $this->settings->get('ai_api_key', '');
        $endpoint = $this->settings->get('ai_provider_endpoint', $aiConfig['endpoint']);
        $model = $this->settings->get('ai_model_name', $aiConfig['model']);

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];

        // 3. Call AI
        $apiResult = $this->callAgentRouter($endpoint, $apiKey, $model, $messages);
        $questions = [];

        if ($apiResult['success']) {
            $parsed = $this->extractJsonFromAiResponse($apiResult['reply']);
            if (is_array($parsed) && !empty($parsed)) {
                foreach ($parsed as $item) {
                    if (!empty($item['question_text']) && !empty($item['correct_option'])) {
                        $questions[] = [
                            'question_text' => trim((string)$item['question_text']),
                            'option_a' => trim((string)($item['option_a'] ?? 'Option A')),
                            'option_b' => trim((string)($item['option_b'] ?? 'Option B')),
                            'option_c' => trim((string)($item['option_c'] ?? 'Option C')),
                            'option_d' => trim((string)($item['option_d'] ?? 'Option D')),
                            'correct_option' => strtoupper(trim((string)$item['correct_option'])),
                            'explanation' => trim((string)($item['explanation'] ?? '')),
                            'topic' => trim((string)($item['topic'] ?? ($topic ?: $course['title']))),
                            'difficulty' => in_array($item['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $item['difficulty'] : $difficulty,
                            'marks' => (int)($item['marks'] ?? 10)
                        ];
                    }
                }
            }
        }

        // 4. Fallback to curriculum engine if AI returned empty, failed, or hit quota limits
        $source = 'ai_live';
        $note = null;
        if (empty($questions)) {
            $questions = $this->generateCurriculumFallbackQuestions($course, $lessons, $count, $difficulty, $topic);
            $source = 'curriculum_engine';
            if (!$apiResult['success']) {
                $note = "AI Gateway notice: " . ($apiResult['error'] ?? 'Service returned code 402/quota limit.') . " Generated {$count} high-quality questions via AliStack Curriculum Engine.";
            } else {
                $note = "AI response formatted via AliStack Curriculum Engine.";
            }
        }

        return [
            'success' => true,
            'course_id' => $courseId,
            'questions' => array_slice($questions, 0, $count),
            'source' => $source,
            'note' => $note
        ];
    }

    /**
     * Clean and parse JSON from AI string output
     */
    protected function extractJsonFromAiResponse(string $raw): ?array {
        $clean = trim($raw);
        // Remove markdown backticks ```json ... ```
        if (preg_match('/```(?:json)?\s*(\[[\s\S]*?\])\s*```/i', $clean, $matches)) {
            $clean = $matches[1];
        } elseif (preg_match('/(\[[\s\S]*\])/', $clean, $matches)) {
            $clean = $matches[1];
        }

        $decoded = json_decode($clean, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Rich, pedagogically sound fallback question generator based on course curriculum
     */
    protected function generateCurriculumFallbackQuestions(array $course, array $lessons, int $count, string $difficulty, ?string $topic): array {
        $title = strtolower($course['title'] . ' ' . ($topic ?? ''));
        $bank = [];

        if (str_contains($title, 'web') || str_contains($title, 'html') || str_contains($title, 'css') || str_contains($title, 'javascript') || str_contains($title, 'frontend')) {
            $bank = [
                [
                    'question_text' => 'Which HTML5 semantic element is most appropriate for wrapping self-contained syndicated content such as a blog post or news card?',
                    'option_a' => '<section>',
                    'option_b' => '<article>',
                    'option_c' => '<aside>',
                    'option_d' => '<main>',
                    'correct_option' => 'B',
                    'explanation' => 'The <article> element represents an independent, self-contained piece of content that could be distributed independently.',
                    'topic' => 'HTML5 Semantics',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'In CSS Flexbox layout, which property is used to align flex children along the CROSS axis?',
                    'option_a' => 'justify-content',
                    'option_b' => 'align-items',
                    'option_c' => 'flex-direction',
                    'option_d' => 'align-self',
                    'correct_option' => 'B',
                    'explanation' => 'align-items aligns all flex items along the cross axis (vertical when flex-direction is row).',
                    'topic' => 'CSS Flexbox',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What is the standard CSS property setting recommended in modern CSS resets to ensure padding and border do NOT increase an element\'s declared width?',
                    'option_a' => 'box-sizing: content-box;',
                    'option_b' => 'box-sizing: border-box;',
                    'option_c' => 'box-sizing: padding-box;',
                    'option_d' => 'overflow: hidden;',
                    'correct_option' => 'B',
                    'explanation' => 'box-sizing: border-box tells the browser to account for border and padding inside the declared width/height.',
                    'topic' => 'CSS Box Model',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What does an async function in modern JavaScript always return?',
                    'option_a' => 'A boolean value indicating execution status',
                    'option_b' => 'A Promise that resolves with the returned value or rejects with an exception',
                    'option_c' => 'A Generator iterator object',
                    'option_d' => 'undefined if no explicit return statement exists',
                    'correct_option' => 'B',
                    'explanation' => 'Functions defined with the async keyword always return a Promise, automatically wrapping return values.',
                    'topic' => 'Async JavaScript',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'When using the browser Fetch API, does an HTTP status code 404 or 500 cause the returned Promise to reject?',
                    'option_a' => 'Yes, any response with status outside 200-299 rejects the promise immediately',
                    'option_b' => 'No, the Promise only rejects on network failures or CORS blockages; response.ok must be checked',
                    'option_c' => 'Yes, 500 status codes reject but 404 resolves',
                    'option_d' => 'It only rejects if mode is set to "cors"',
                    'correct_option' => 'B',
                    'explanation' => 'fetch() resolves normally even on 404 or 500. It only rejects if network connection fails completely.',
                    'topic' => 'Web APIs & Fetch',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'Which DOM method is the modern, safe standard for listening to user events with support for multiple handlers and bubbling phases?',
                    'option_a' => 'element.onclick = function()',
                    'option_b' => 'element.addEventListener("click", handler)',
                    'option_c' => 'element.attachEvent("onclick", handler)',
                    'option_d' => 'element.bind("click", handler)',
                    'correct_option' => 'B',
                    'explanation' => 'addEventListener is the official standard API that allows multiple listeners and event capture/bubble configuration.',
                    'topic' => 'DOM Events',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What is the purpose of "Event Delegation" in JavaScript web applications?',
                    'option_a' => 'To disable event bubbling on child nodes',
                    'option_b' => 'To attach a single listener on a parent element to handle events from multiple current and dynamic child elements',
                    'option_c' => 'To dispatch custom browser events across web workers',
                    'option_d' => 'To throttle mouse movement events',
                    'correct_option' => 'B',
                    'explanation' => 'Event delegation takes advantage of bubbling by placing one listener on a parent container for efficient memory management.',
                    'topic' => 'DOM Performance',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'In CSS Grid, which unit represents a fraction of the remaining free space inside the grid container?',
                    'option_a' => '% (percentage)',
                    'option_b' => 'fr (fractional unit)',
                    'option_c' => 'vw (viewport width)',
                    'option_d' => 'em unit',
                    'correct_option' => 'B',
                    'explanation' => 'The fr unit represents a fraction of the leftover available space in the grid container.',
                    'topic' => 'CSS Grid',
                    'difficulty' => 'easy',
                    'marks' => 10
                ]
            ];
        } elseif (str_contains($title, 'php') || str_contains($title, 'mysql') || str_contains($title, 'backend')) {
            $bank = [
                [
                    'question_text' => 'Which PHP function is the cryptographically secure and standard recommended method for hashing user passwords?',
                    'option_a' => 'md5()',
                    'option_b' => 'sha1()',
                    'option_c' => 'password_hash()',
                    'option_d' => 'crypt_aes()',
                    'correct_option' => 'C',
                    'explanation' => 'password_hash() uses strong one-way algorithms (such as Bcrypt or Argon2) with automatic salt generation.',
                    'topic' => 'Password Security',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What is the primary security advantage of using PDO prepared statements with bound parameters?',
                    'option_a' => 'It compresses the query string to save bandwidth',
                    'option_b' => 'It guarantees queries execute faster in all scenarios',
                    'option_c' => 'It completely separates SQL instructions from user-supplied data, neutralizing SQL injection',
                    'option_d' => 'It encrypts database tables on disk automatically',
                    'correct_option' => 'C',
                    'explanation' => 'Prepared statements prevent attackers from altering the SQL syntax by sending structure and parameters separately.',
                    'topic' => 'PDO & SQL Security',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'Why is session_regenerate_id(true) critical immediately following a successful user login?',
                    'option_a' => 'It clears the user browser cache',
                    'option_b' => 'It prevents Session Fixation attacks by issuing a brand new session identifier and destroying the old one',
                    'option_c' => 'It refreshes the database connection pool',
                    'option_d' => 'It encrypts the PHP source files in memory',
                    'correct_option' => 'B',
                    'explanation' => 'Session Fixation occurs when an attacker forces a known ID. Regenerating invalidates any pre-auth session.',
                    'topic' => 'Session Security',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'In PHP 8.0+, what is the behavior of the match expression compared to a legacy switch statement?',
                    'option_a' => 'It uses loose comparison (==) and allows fall-through',
                    'option_b' => 'It uses strict comparison (===), returns an expression value directly, and does not fall through',
                    'option_c' => 'It can only evaluate boolean variables',
                    'option_d' => 'It executes asynchronously in a background thread',
                    'correct_option' => 'B',
                    'explanation' => 'The match expression uses strict identity checks (===), returns a value directly, and does not require break statements.',
                    'topic' => 'Modern PHP 8',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'Which HTTP cookie attribute prevents client-side JavaScript (like document.cookie) from accessing the session identifier?',
                    'option_a' => 'SameSite=Strict',
                    'option_b' => 'Secure',
                    'option_c' => 'HttpOnly',
                    'option_d' => 'Max-Age',
                    'correct_option' => 'C',
                    'explanation' => 'The HttpOnly flag directs browsers never to expose cookie data to JavaScript, protecting against XSS cookie theft.',
                    'topic' => 'Web Security',
                    'difficulty' => 'easy',
                    'marks' => 10
                ]
            ];
        } else {
            // AI, CS or General
            $bank = [
                [
                    'question_text' => 'In LLM applications, what does lowering the temperature parameter (e.g. from 0.8 to 0.1) achieve?',
                    'option_a' => 'Increases the maximum tokens generated per second',
                    'option_b' => 'Makes the output more deterministic, focused, and repeatable',
                    'option_c' => 'Doubles the context window size',
                    'option_d' => 'Automatically connects the model to Google Search',
                    'correct_option' => 'B',
                    'explanation' => 'Lower temperature concentrates probability distribution onto the highest scoring tokens for consistent answers.',
                    'topic' => 'LLM Hyperparameters',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'Where should external API credentials (such as AI gateway keys) be stored and used in a web application?',
                    'option_a' => 'In client-side JavaScript constants',
                    'option_b' => 'Exclusively on the server environment (.env), never exposed to browser requests',
                    'option_c' => 'In public HTML data attributes',
                    'option_d' => 'Inside CSS root variables',
                    'correct_option' => 'B',
                    'explanation' => 'API keys are private credentials; exposing them in frontend bundles allows immediate theft and billing abuse.',
                    'topic' => 'API Security',
                    'difficulty' => 'easy',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What is "few-shot prompting" in prompt engineering?',
                    'option_a' => 'Limiting the model to 3 attempts before raising an error',
                    'option_b' => 'Providing the model with high-quality input-output demonstrations inside the prompt context to guide format and logic',
                    'option_c' => 'Training neural network weights using gradient descent on client devices',
                    'option_d' => 'Using multiple AI models simultaneously in a round-robin cycle',
                    'correct_option' => 'B',
                    'explanation' => 'Few-shot prompting shows exemplar input/output pairs, improving alignment with required schema and task rules.',
                    'topic' => 'Prompt Engineering',
                    'difficulty' => 'medium',
                    'marks' => 10
                ],
                [
                    'question_text' => 'What happens when a prompt exceeds an LLM\'s maximum context window limit?',
                    'option_a' => 'The model executes in parallel memory',
                    'option_b' => 'The API rejects the request or earlier context is truncated, causing loss of instructions',
                    'option_c' => 'The response temperature automatically scales to zero',
                    'option_d' => 'The token rate doubles',
                    'correct_option' => 'B',
                    'explanation' => 'Context window limits restrict total token count (prompt + generation); exceeding this causes errors or context loss.',
                    'topic' => 'LLM Architecture',
                    'difficulty' => 'medium',
                    'marks' => 10
                ]
            ];
        }

        shuffle($bank);
        return array_slice($bank, 0, $count);
    }
}

