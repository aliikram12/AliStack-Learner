<?php
declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/autoload.php';

use App\Repositories\AssessmentRepository;
use App\Repositories\SettingRepository;
use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Helpers\Response;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 405);
}

if (!Auth::check() || !in_array(Auth::user()['role'] ?? '', ['admin', 'super_admin'], true)) {
    Response::error('Administrative authorization required', 403);
}

Csrf::checkOrAbort();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: $_POST;

$assessmentId = !empty($data['assessment_id']) ? (int)$data['assessment_id'] : 0;
$courseId = !empty($data['course_id']) ? (int)$data['course_id'] : 0;
$questions = !empty($data['questions']) && is_array($data['questions']) ? $data['questions'] : [];

if (!$assessmentId || empty($questions)) {
    Response::error('Assessment ID and questions array are required', 400);
}

$assessmentRepo = new AssessmentRepository();
$assessment = $assessmentRepo->findById($assessmentId);
if (!$assessment) {
    Response::error('Target assessment not found', 404);
}

$pdo = getDbConnection();
$pdo->beginTransaction();

try {
    $insertedCount = 0;
    $stmt = $pdo->prepare("
        INSERT INTO assessment_questions (assessment_id, course_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, topic, difficulty, marks, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    foreach ($questions as $q) {
        if (empty($q['question_text']) || empty($q['correct_option'])) {
            continue;
        }

        $stmt->execute([
            $assessmentId,
            $courseId ?: (int)$assessment['course_id'],
            trim((string)$q['question_text']),
            trim((string)($q['option_a'] ?? 'Option A')),
            trim((string)($q['option_b'] ?? 'Option B')),
            trim((string)($q['option_c'] ?? 'Option C')),
            trim((string)($q['option_d'] ?? 'Option D')),
            strtoupper(trim((string)$q['correct_option'])),
            trim((string)($q['explanation'] ?? '')),
            trim((string)($q['topic'] ?? 'General')),
            in_array($q['difficulty'] ?? '', ['easy', 'medium', 'hard']) ? $q['difficulty'] : 'medium',
            max(1, (int)($q['marks'] ?? 10))
        ]);
        $insertedCount++;
    }

    // Update assessment total questions to ask if needed
    $currentTotal = (int)$pdo->query("SELECT COUNT(*) FROM assessment_questions WHERE assessment_id = {$assessmentId}")->fetchColumn();
    $pdo->prepare("UPDATE assessments SET total_questions_to_ask = ?, total_marks = ? WHERE id = ?")
        ->execute([min(10, $currentTotal), min(10, $currentTotal) * 10, $assessmentId]);

    $pdo->commit();

    $settingRepo = new SettingRepository();
    $settingRepo->logAudit(Auth::id(), 'AI_QUESTIONS_SAVED', 'assessment_questions', $assessmentId, "Added {$insertedCount} AI-generated questions to assessment ID {$assessmentId}");

    Response::success([
        'assessment_id' => $assessmentId,
        'saved_count' => $insertedCount,
        'total_questions' => $currentTotal
    ], "Successfully saved {$insertedCount} questions to assessment!");

} catch (Throwable $t) {
    $pdo->rollBack();
    error_log("Failed to save generated questions: " . $t->getMessage());
    Response::error("Failed to save questions to database: " . $t->getMessage(), 500);
}
