<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\AssessmentRepository;
use App\Repositories\CourseRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\SettingRepository;

class AssessmentService {
    protected AssessmentRepository $assessmentRepo;
    protected CourseRepository $courseRepo;
    protected CertificateRepository $certRepo;
    protected NotificationRepository $notifRepo;
    protected SettingRepository $settingRepo;

    public function __construct() {
        $this->assessmentRepo = new AssessmentRepository();
        $this->courseRepo = new CourseRepository();
        $this->certRepo = new CertificateRepository();
        $this->notifRepo = new NotificationRepository();
        $this->settingRepo = new SettingRepository();
    }

    /**
     * Check if student is eligible to take the assessment
     */
    public function checkEligibility(int $userId, int $courseId): array {
        $course = $this->courseRepo->findById($courseId);
        if (!$course) {
            return ['eligible' => false, 'reason' => 'Course does not exist.'];
        }

        $assessment = $this->assessmentRepo->findByCourseId($courseId);
        if (!$assessment) {
            return ['eligible' => false, 'reason' => 'No active assessment is currently available for this course.'];
        }

        $enrollment = $this->courseRepo->getEnrollment($userId, $courseId);
        if (!$enrollment) {
            return ['eligible' => false, 'reason' => 'You must be enrolled in this course to take the assessment.'];
        }

        // Check lesson completion requirement
        $requireCompletion = (bool)($this->settingRepo->get('require_lesson_completion_for_test', 1));
        if ($requireCompletion && (float)$enrollment['progress_percent'] < 100.00) {
            return [
                'eligible' => false,
                'reason' => 'You must complete all course lessons (100% progress) before attempting the final assessment.',
                'progress' => (float)$enrollment['progress_percent']
            ];
        }

        // Check attempts limit
        $attempts = $this->assessmentRepo->getUserAttempts($userId, (int)$assessment['id']);
        $completedAttempts = array_filter($attempts, fn($a) => $a['status'] === 'completed');
        $maxAttempts = (int)$assessment['max_attempts'];

        if (count($completedAttempts) >= $maxAttempts) {
            return [
                'eligible' => false,
                'reason' => "You have reached the maximum allowed attempts ({$maxAttempts}) for this assessment.",
                'attempts' => $attempts
            ];
        }

        return [
            'eligible' => true,
            'assessment' => $assessment,
            'attempts_used' => count($completedAttempts),
            'max_attempts' => $maxAttempts
        ];
    }

    /**
     * Start or resume an assessment attempt
     */
    public function startAttempt(int $userId, int $assessmentId): array {
        $assessment = $this->assessmentRepo->findById($assessmentId);
        if (!$assessment) {
            return ['success' => false, 'error' => 'Assessment not found.'];
        }

        $courseId = (int)$assessment['course_id'];
        $eligibility = $this->checkEligibility($userId, $courseId);
        if (!$eligibility['eligible']) {
            return ['success' => false, 'error' => $eligibility['reason']];
        }

        // Check for active in-progress attempt
        $active = $this->assessmentRepo->getActiveAttempt($userId, $assessmentId);
        if ($active) {
            $questions = $this->assessmentRepo->getQuestions($assessmentId, true, false);
            return [
                'success' => true,
                'attempt' => $active,
                'questions' => $questions,
                'is_resumed' => true
            ];
        }

        $allAttempts = $this->assessmentRepo->getUserAttempts($userId, $assessmentId);
        $nextAttemptNumber = count($allAttempts) + 1;

        $attemptId = $this->assessmentRepo->createAttempt(
            $userId, 
            $assessmentId, 
            $courseId, 
            $nextAttemptNumber, 
            (float)$assessment['total_marks']
        );

        $attempt = $this->assessmentRepo->getAttemptById($attemptId);
        $questions = $this->assessmentRepo->getQuestions($assessmentId, true, (bool)$assessment['randomize_questions']);

        return [
            'success' => true,
            'attempt' => $attempt,
            'questions' => $questions,
            'is_resumed' => false
        ];
    }

    /**
     * Submit and grade the assessment
     */
    public function submitAssessment(int $userId, int $attemptId, array $submittedAnswers): array {
        $attempt = $this->assessmentRepo->getAttemptById($attemptId);
        if (!$attempt || (int)$attempt['user_id'] !== $userId) {
            return ['success' => false, 'error' => 'Invalid attempt session.'];
        }

        if ($attempt['status'] !== 'in_progress') {
            return ['success' => false, 'error' => 'This attempt has already been graded and closed.'];
        }

        // Server-side grading
        $result = $this->assessmentRepo->gradeAndFinalizeAttempt($attemptId, $submittedAnswers);
        $scorePercent = (float)$result['percentage'];
        $courseId = (int)$attempt['course_id'];
        $studentName = $attempt['student_name'];
        $courseTitle = $attempt['course_title'];

        $achievementType = $result['qualifying_achievement'];
        $certData = null;

        // 1. Issue Certificate if score >= 70%
        if ($scorePercent >= 70.00) {
            $certData = $this->certRepo->issueCertificate(
                $userId,
                $courseId,
                $attemptId,
                $studentName,
                $courseTitle,
                $scorePercent
            );

            // In-app Notification
            $this->notifRepo->create(
                $userId,
                "Certificate Earned: {$courseTitle}",
                "Congratulations! You scored {$scorePercent}% and earned your verified AliStack Course Certificate.",
                "certificates.php",
                "achievement"
            );
        }
        // 2. Award Silver Badge (60% - 69.99%)
        elseif ($scorePercent >= 60.00) {
            $this->certRepo->awardBadge($userId, $courseId, $attemptId, 'silver_badge', $scorePercent);
            $this->notifRepo->create(
                $userId,
                "Silver Proficiency Badge Earned!",
                "Outstanding work! You scored {$scorePercent}% and earned the Silver Proficiency Badge for {$courseTitle}.",
                "dashboard.php#badges",
                "achievement"
            );
        }
        // 3. Award Bronze Badge (50% - 59.99%)
        elseif ($scorePercent >= 50.00) {
            $this->certRepo->awardBadge($userId, $courseId, $attemptId, 'bronze_badge', $scorePercent);
            $this->notifRepo->create(
                $userId,
                "Bronze Competency Badge Earned!",
                "Good job! You scored {$scorePercent}% and earned the Bronze Competency Badge for {$courseTitle}.",
                "dashboard.php#badges",
                "achievement"
            );
        }
        // 4. Award Starter Badge (40% - 49.99%)
        elseif ($scorePercent >= 40.00) {
            $this->certRepo->awardBadge($userId, $courseId, $attemptId, 'starter_badge', $scorePercent);
            $this->notifRepo->create(
                $userId,
                "Foundation Starter Badge Earned!",
                "Well done! You scored {$scorePercent}% and earned the Foundation Starter Badge for {$courseTitle}.",
                "dashboard.php#badges",
                "achievement"
            );
        }
        // 5. Score below 40%
        else {
            $this->notifRepo->create(
                $userId,
                "Assessment Completed: {$courseTitle}",
                "You scored {$scorePercent}%. Review your lessons and test again when you feel ready.",
                "results.php?attempt_id={$attemptId}",
                "assessment"
            );
        }

        return [
            'success' => true,
            'result' => $result,
            'certificate' => $certData,
            'redirect_url' => "results.php?attempt_id={$attemptId}"
        ];
    }
}
