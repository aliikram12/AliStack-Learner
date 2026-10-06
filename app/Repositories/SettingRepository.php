<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class SettingRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("SELECT * FROM platform_settings ORDER BY setting_group ASC, setting_key ASC");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    public function get(string $key, mixed $default = null): mixed {
        $stmt = $this->db->prepare("SELECT setting_value FROM platform_settings WHERE setting_key = ? LIMIT 1");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? $val : $default;
    }

    public function set(string $key, ?string $value): bool {
        $stmt = $this->db->prepare("
            INSERT INTO platform_settings (setting_key, setting_value, updated_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()
        ");
        return $stmt->execute([$key, $value]);
    }

    public function logAudit(?int $userId, string $action, string $entityType, ?int $entityId = null, ?string $details = null): void {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/System', 0, 255);
            $stmt = $this->db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$userId, $action, $entityType, $entityId, $details, $ip, $ua]);
        } catch (\Throwable $e) {
            error_log("Failed to write audit log: " . $e->getMessage());
        }
    }

    public function getAuditLogs(int $page = 1, int $perPage = 25): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT al.*, u.full_name as user_name, u.email as user_email, u.role as user_role
                FROM audit_logs al
                LEFT JOIN users u ON u.id = al.user_id
                ORDER BY al.id DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function countAuditLogs(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
    }

    /**
     * Retrieve platform statistics computed directly from actual database queries
     */
    public function getPlatformStats(): array {
        $stats = [];
        $stats['total_students'] = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
        $stats['total_users'] = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $stats['published_courses'] = (int)$this->db->query("SELECT COUNT(*) FROM courses WHERE status = 'published'")->fetchColumn();
        $stats['total_lessons'] = (int)$this->db->query("SELECT COUNT(*) FROM course_lessons WHERE is_published = 1")->fetchColumn();
        $stats['active_enrollments'] = (int)$this->db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'active'")->fetchColumn();
        $stats['completed_courses'] = (int)$this->db->query("SELECT COUNT(*) FROM enrollments WHERE status = 'completed'")->fetchColumn();
        $stats['assessment_attempts'] = (int)$this->db->query("SELECT COUNT(*) FROM assessment_attempts WHERE status = 'completed'")->fetchColumn();
        $stats['certificates_issued'] = (int)$this->db->query("SELECT COUNT(*) FROM certificates WHERE status = 'valid'")->fetchColumn();
        $stats['badges_awarded'] = (int)$this->db->query("SELECT COUNT(*) FROM student_achievements")->fetchColumn();
        $stats['pending_group_requests'] = (int)$this->db->query("SELECT COUNT(*) FROM group_join_requests WHERE status = 'pending'")->fetchColumn();
        $stats['total_discussion_posts'] = (int)$this->db->query("SELECT COUNT(*) FROM discussion_posts")->fetchColumn();
        return $stats;
    }
}
