<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CertificateRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function generateUniqueCertificateNumber(): string {
        $year = date('Y');
        do {
            $randomHex = strtoupper(bin2hex(random_bytes(4)));
            $number = "ALISTACK-CERT-{$year}-{$randomHex}";
            $stmt = $this->db->prepare("SELECT id FROM certificates WHERE certificate_number = ? LIMIT 1");
            $stmt->execute([$number]);
        } while ($stmt->fetchColumn());

        return $number;
    }

    public function generateVerificationCode(): string {
        return bin2hex(random_bytes(24));
    }

    public function issueCertificate(int $userId, int $courseId, int $attemptId, string $studentName, string $courseTitle, float $scorePercentage): array {
        // Prevent duplicate certificate for this attempt
        $stmt = $this->db->prepare("SELECT * FROM certificates WHERE attempt_id = ? LIMIT 1");
        $stmt->execute([$attemptId]);
        $existing = $stmt->fetch();
        if ($existing) {
            return $existing;
        }

        // Also check if valid certificate already exists for this user and course
        $stmtUserCourse = $this->db->prepare("SELECT * FROM certificates WHERE user_id = ? AND course_id = ? AND status = 'valid' LIMIT 1");
        $stmtUserCourse->execute([$userId, $courseId]);
        $prior = $stmtUserCourse->fetch();
        if ($prior) {
            return $prior;
        }

        $certNumber = $this->generateUniqueCertificateNumber();
        $verificationCode = $this->generateVerificationCode();

        $stmt = $this->db->prepare("
            INSERT INTO certificates (certificate_number, user_id, course_id, attempt_id, student_name, course_title, score_percentage, verification_code, status, issued_at, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'valid', NOW(), NOW())
        ");
        $stmt->execute([
            $certNumber,
            $userId,
            $courseId,
            $attemptId,
            $studentName,
            $courseTitle,
            $scorePercentage,
            $verificationCode
        ]);

        return $this->findByVerificationCode($verificationCode);
    }

    public function findByVerificationCode(string $code): ?array {
        $stmt = $this->db->prepare("
            SELECT cert.*, u.email as student_email, c.slug as course_slug
            FROM certificates cert
            JOIN users u ON u.id = cert.user_id
            JOIN courses c ON c.id = cert.course_id
            WHERE cert.verification_code = ? LIMIT 1
        ");
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByCertificateNumber(string $number): ?array {
        $stmt = $this->db->prepare("
            SELECT cert.*, u.email as student_email, c.slug as course_slug
            FROM certificates cert
            JOIN users u ON u.id = cert.user_id
            JOIN courses c ON c.id = cert.course_id
            WHERE cert.certificate_number = ? LIMIT 1
        ");
        $stmt->execute([strtoupper(trim($number))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserCertificates(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT cert.*, c.slug as course_slug, c.thumbnail as course_thumbnail
            FROM certificates cert
            JOIN courses c ON c.id = cert.course_id
            WHERE cert.user_id = ?
            ORDER BY cert.issued_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllAdmin(int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT cert.*, u.email as student_email, c.slug as course_slug
                FROM certificates cert
                JOIN users u ON u.id = cert.user_id
                JOIN courses c ON c.id = cert.course_id
                ORDER BY cert.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function countAll(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM certificates")->fetchColumn();
    }

    // =========================================================================
    // Badges & Achievements
    // =========================================================================
    public function awardBadge(int $userId, int $courseId, int $attemptId, string $badgeKey, float $scorePercentage): bool {
        // Prevent duplicate badge for same attempt
        $stmt = $this->db->prepare("SELECT id FROM student_achievements WHERE attempt_id = ? AND badge_key = ? LIMIT 1");
        $stmt->execute([$attemptId, $badgeKey]);
        if ($stmt->fetchColumn()) {
            return true;
        }

        $insert = $this->db->prepare("
            INSERT INTO student_achievements (user_id, course_id, attempt_id, badge_key, score_percentage, awarded_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $insert->execute([$userId, $courseId, $attemptId, $badgeKey, $scorePercentage]);
    }

    public function getUserAchievements(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT sa.*, b.title as badge_title, b.badge_tier, b.description as badge_desc,
                   c.title as course_title, c.slug as course_slug
            FROM student_achievements sa
            JOIN achievement_badges b ON b.badge_key = sa.badge_key
            JOIN courses c ON c.id = sa.course_id
            WHERE sa.user_id = ?
            ORDER BY sa.awarded_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getAllBadges(): array {
        return $this->db->query("SELECT * FROM achievement_badges ORDER BY min_score DESC")->fetchAll();
    }
}
