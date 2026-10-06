<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class NotificationRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function create(int $userId, string $title, string $message, ?string $linkUrl = null, string $type = 'system'): int {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, link_url, type, is_read, created_at)
            VALUES (?, ?, ?, ?, ?, 0, NOW())
        ");
        $stmt->execute([$userId, $title, $message, $linkUrl, $type]);
        return (int)$this->db->lastInsertId();
    }

    public function getUserNotifications(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY id DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAsRead(int $userId, int $notificationId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        return $stmt->execute([$notificationId, $userId]);
    }

    public function markAllAsRead(int $userId): bool {
        $stmt = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
