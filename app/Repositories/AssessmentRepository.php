<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class AssessmentRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function findByCourseId(int $courseId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM assessments WHERE course_id = ? AND is_published = 1 LIMIT 1");
        $stmt->execute([$courseId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $assessmentId): ?array {
        $stmt = $this->db->prepare("
            SELECT a.*, c.title as course_title, c.slug as course_slug 
            FROM assessments a 
            JOIN courses c ON c.id = a.course_id 
            WHERE a.id = ? LIMIT 1
        ");
        $stmt->execute([$assessmentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getQuestions(int $assessmentId, bool $activeOnly = true, bool $shuffle = true): array {
        $sql = "SELECT id, assessment_id, course_id, question_text, option_a, option_b, option_c, option_d, topic, difficulty, marks 
                FROM assessment_questions 
                WHERE assessment_id = ?";
        if ($activeOnly) {
            $sql .= " AND is_active = 1";
        }
        $sql .= $shuffle ? " ORDER BY RAND()" : " ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$assessmentId]);
        return $stmt->fetchAll();
    }

    public function getQuestionsAdmin(int $assessmentId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM assessment_questions 
            WHERE assessment_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$assessmentId]);
        return $stmt->fetchAll();
    }

    public function getQuestionById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM assessment_questions WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createQuestion(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO assessment_questions (assessment_id, course_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, topic, difficulty, marks, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['assessment_id'],
            $data['course_id'],
            $data['question_text'],
            $data['option_a'],
            $data['option_b'],
            $data['option_c'],
            $data['option_d'],
            strtoupper($data['correct_option']),
            $data['explanation'] ?? null,
            $data['topic'] ?? null,
            $data['difficulty'] ?? 'medium',
            $data['marks'] ?? 10,
            $data['is_active'] ?? 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateQuestion(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $k => $v) {
            $fields[] = "`{$k}` = ?";
            $params[] = $v;
        }
        $params[] = $id;
        $sql = "UPDATE assessment_questions SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function deleteQuestion(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM assessment_questions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getUserAttempts(int $userId, int $assessmentId): array {
        $stmt = $this->db->prepare("
            SELECT * FROM assessment_attempts 
            WHERE user_id = ? AND assessment_id = ? 
            ORDER BY attempt_number DESC
        ");
        $stmt->execute([$userId, $assessmentId]);
        return $stmt->fetchAll();
    }

    public function getActiveAttempt(int $userId, int $assessmentId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM assessment_attempts 
            WHERE user_id = ? AND assessment_id = ? AND status = 'in_progress' 
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$userId, $assessmentId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getAttemptById(int $attemptId): ?array {
        $stmt = $this->db->prepare("
            SELECT aa.*, a.title as assessment_title, a.pass_percentage, a.time_limit_minutes, a.reveal_answers,
                   c.title as course_title, c.slug as course_slug, u.full_name as student_name, u.email as student_email
            FROM assessment_attempts aa
            JOIN assessments a ON a.id = aa.assessment_id
            JOIN courses c ON c.id = aa.course_id
            JOIN users u ON u.id = aa.user_id
            WHERE aa.id = ? LIMIT 1
        ");
        $stmt->execute([$attemptId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createAttempt(int $userId, int $assessmentId, int $courseId, int $attemptNumber, float $totalMarks): int {
        $stmt = $this->db->prepare("
            INSERT INTO assessment_attempts (assessment_id, user_id, course_id, attempt_number, score, total_marks, percentage, status, qualifying_achievement, started_at)
            VALUES (?, ?, ?, ?, 0.00, ?, 0.00, 'in_progress', 'none', NOW())
        ");
        $stmt->execute([$assessmentId, $userId, $courseId, $attemptNumber, $totalMarks]);
        return (int)$this->db->lastInsertId();
    }

    public function saveAnswer(int $attemptId, int $questionId, string $selectedOption): bool {
        $selectedOption = strtoupper($selectedOption);
        $sql = "INSERT INTO assessment_answers (attempt_id, question_id, selected_option)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE selected_option = VALUES(selected_option)";
        return $this->db->prepare($sql)->execute([$attemptId, $questionId, $selectedOption]);
    }

    public function getAttemptAnswers(int $attemptId): array {
        $stmt = $this->db->prepare("
            SELECT ans.*, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d, q.correct_option, q.explanation, q.marks
            FROM assessment_answers ans
            JOIN assessment_questions q ON q.id = ans.question_id
            WHERE ans.attempt_id = ?
        ");
        $stmt->execute([$attemptId]);
        return $stmt->fetchAll();
    }

    public function gradeAndFinalizeAttempt(int $attemptId, array $submittedAnswers = []): array {
        $attempt = $this->getAttemptById($attemptId);
        if (!$attempt || $attempt['status'] !== 'in_progress') {
            return $attempt ?: [];
        }

        $assessment = $this->findById((int)$attempt['assessment_id']);
        $questions = $this->getQuestionsAdmin((int)$attempt['assessment_id']);

        $totalMarksPossible = 0.00;
        $earnedScore = 0.00;

        $this->db->beginTransaction();

        try {
            foreach ($questions as $q) {
                $qId = (int)$q['id'];
                $qMarks = (float)$q['marks'];
                $totalMarksPossible += $qMarks;

                $userChoice = $submittedAnswers[$qId] ?? null;
                if ($userChoice !== null) {
                    $userChoice = strtoupper(trim((string)$userChoice));
                }

                $isCorrect = ($userChoice !== null && $userChoice === strtoupper((string)$q['correct_option'])) ? 1 : 0;
                $awarded = $isCorrect ? $qMarks : 0.00;
                $earnedScore += $awarded;

                // Upsert answer
                $stmt = $this->db->prepare("
                    INSERT INTO assessment_answers (attempt_id, question_id, selected_option, is_correct, marks_awarded)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        selected_option = VALUES(selected_option),
                        is_correct = VALUES(is_correct),
                        marks_awarded = VALUES(marks_awarded)
                ");
                $stmt->execute([$attemptId, $qId, $userChoice, $isCorrect, $awarded]);
            }

            $percentage = ($totalMarksPossible > 0) ? round(($earnedScore / $totalMarksPossible) * 100, 2) : 0.00;

            // Determine qualifying achievement according to AliStack policy:
            // 70% - 100%: certificate
            // 60% - 69.99%: silver_badge
            // 50% - 59.99%: bronze_badge
            // 40% - 49.99%: starter_badge
            // < 40%: none
            $achievement = 'none';
            if ($percentage >= 70.00) {
                $achievement = 'certificate';
            } elseif ($percentage >= 60.00) {
                $achievement = 'silver_badge';
            } elseif ($percentage >= 50.00) {
                $achievement = 'bronze_badge';
            } elseif ($percentage >= 40.00) {
                $achievement = 'starter_badge';
            }

            $update = $this->db->prepare("
                UPDATE assessment_attempts 
                SET score = ?, total_marks = ?, percentage = ?, status = 'completed', qualifying_achievement = ?, submitted_at = NOW()
                WHERE id = ?
            ");
            $update->execute([$earnedScore, $totalMarksPossible, $percentage, $achievement, $attemptId]);

            $this->db->commit();

            return [
                'attempt_id' => $attemptId,
                'score' => $earnedScore,
                'total_marks' => $totalMarksPossible,
                'percentage' => $percentage,
                'qualifying_achievement' => $achievement,
                'status' => 'completed'
            ];
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getAllAttemptsAdmin(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT aa.*, u.full_name as student_name, u.email as student_email, c.title as course_title, a.title as assessment_title
                FROM assessment_attempts aa
                JOIN users u ON u.id = aa.user_id
                JOIN courses c ON c.id = aa.course_id
                JOIN assessments a ON a.id = aa.assessment_id
                ORDER BY aa.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}
