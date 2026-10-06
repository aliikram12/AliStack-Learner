<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CommunityRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getAllGroups(bool $activeOnly = true): array {
        $where = $activeOnly ? "WHERE g.is_active = 1" : "";
        $sql = "SELECT g.*, c.title as course_title, c.slug as course_slug,
                (SELECT COUNT(*) FROM group_memberships gm WHERE gm.group_id = g.id AND gm.status = 'active') as member_count,
                (SELECT COUNT(*) FROM discussion_posts dp WHERE dp.group_id = g.id) as post_count
                FROM discussion_groups g
                LEFT JOIN courses c ON c.id = g.course_id
                {$where}
                ORDER BY g.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getGroupById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT g.*, c.title as course_title, c.slug as course_slug,
            (SELECT COUNT(*) FROM group_memberships gm WHERE gm.group_id = g.id AND gm.status = 'active') as member_count,
            (SELECT COUNT(*) FROM discussion_posts dp WHERE dp.group_id = g.id) as post_count
            FROM discussion_groups g
            LEFT JOIN courses c ON c.id = g.course_id
            WHERE g.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getGroupBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT g.*, c.title as course_title, c.slug as course_slug,
            (SELECT COUNT(*) FROM group_memberships gm WHERE gm.group_id = g.id AND gm.status = 'active') as member_count,
            (SELECT COUNT(*) FROM discussion_posts dp WHERE dp.group_id = g.id) as post_count
            FROM discussion_groups g
            LEFT JOIN courses c ON c.id = g.course_id
            WHERE g.slug = ? LIMIT 1
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserMembership(int $userId, int $groupId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM group_memberships WHERE user_id = ? AND group_id = ? LIMIT 1");
        $stmt->execute([$userId, $groupId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserJoinRequest(int $userId, int $groupId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM group_join_requests WHERE user_id = ? AND group_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$userId, $groupId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createJoinRequest(int $userId, int $groupId): bool {
        // If public group, join directly
        $group = $this->getGroupById($groupId);
        if (!$group) return false;

        if (!$group['is_private']) {
            $stmt = $this->db->prepare("INSERT IGNORE INTO group_memberships (group_id, user_id, role, status, joined_at) VALUES (?, ?, 'member', 'active', NOW())");
            return $stmt->execute([$groupId, $userId]);
        }

        // Private group: create join request
        $sql = "INSERT INTO group_join_requests (group_id, user_id, status, created_at)
                VALUES (?, ?, 'pending', NOW())
                ON DUPLICATE KEY UPDATE status = 'pending', reviewed_at = NULL, reviewed_by = NULL";
        return $this->db->prepare($sql)->execute([$groupId, $userId]);
    }

    public function reviewJoinRequest(int $requestId, string $status, int $reviewerId): bool {
        $stmt = $this->db->prepare("SELECT * FROM group_join_requests WHERE id = ? LIMIT 1");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch();
        if (!$req) return false;

        $update = $this->db->prepare("
            UPDATE group_join_requests 
            SET status = ?, reviewed_by = ?, reviewed_at = NOW() 
            WHERE id = ?
        ");
        $update->execute([$status, $reviewerId, $requestId]);

        if ($status === 'approved') {
            // Add to memberships
            $join = $this->db->prepare("
                INSERT INTO group_memberships (group_id, user_id, role, status, joined_at)
                VALUES (?, ?, 'member', 'active', NOW())
                ON DUPLICATE KEY UPDATE status = 'active'
            ");
            $join->execute([(int)$req['group_id'], (int)$req['user_id']]);
        }

        return true;
    }

    public function getPendingRequestsAdmin(?int $groupId = null): array {
        $where = "WHERE gjr.status = 'pending'";
        $params = [];
        if ($groupId) {
            $where .= " AND gjr.group_id = ?";
            $params[] = $groupId;
        }
        $sql = "SELECT gjr.*, u.full_name as student_name, u.email as student_email, u.username as student_username, g.name as group_name
                FROM group_join_requests gjr
                JOIN users u ON u.id = gjr.user_id
                JOIN discussion_groups g ON g.id = gjr.group_id
                {$where}
                ORDER BY gjr.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getGroupPosts(int $groupId, int $page = 1, int $perPage = 20): array {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT p.*, u.full_name as author_name, u.avatar_url as author_avatar, u.role as author_role
                FROM discussion_posts p
                JOIN users u ON u.id = p.user_id
                WHERE p.group_id = ?
                ORDER BY p.is_pinned DESC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$groupId]);
        return $stmt->fetchAll();
    }

    public function getPostById(int $postId): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as author_name, u.avatar_url as author_avatar, u.role as author_role, g.name as group_name, g.slug as group_slug
            FROM discussion_posts p
            JOIN users u ON u.id = p.user_id
            JOIN discussion_groups g ON g.id = p.group_id
            WHERE p.id = ? LIMIT 1
        ");
        $stmt->execute([$postId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createPost(int $groupId, int $userId, string $title, string $content): int {
        $stmt = $this->db->prepare("
            INSERT INTO discussion_posts (group_id, user_id, title, content, is_pinned, is_locked, replies_count, created_at, updated_at)
            VALUES (?, ?, ?, ?, 0, 0, 0, NOW(), NOW())
        ");
        $stmt->execute([$groupId, $userId, $title, $content]);
        return (int)$this->db->lastInsertId();
    }

    public function getPostReplies(int $postId): array {
        $stmt = $this->db->prepare("
            SELECT r.*, u.full_name as author_name, u.avatar_url as author_avatar, u.role as author_role
            FROM discussion_replies r
            JOIN users u ON u.id = r.user_id
            WHERE r.post_id = ?
            ORDER BY r.created_at ASC
        ");
        $stmt->execute([$postId]);
        return $stmt->fetchAll();
    }

    public function createReply(int $postId, int $userId, string $content, ?int $parentReplyId = null): int {
        $stmt = $this->db->prepare("
            INSERT INTO discussion_replies (post_id, user_id, parent_reply_id, content, created_at, updated_at)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$postId, $userId, $parentReplyId ?: null, $content]);
        $replyId = (int)$this->db->lastInsertId();

        // Increment post reply count
        $this->db->prepare("UPDATE discussion_posts SET replies_count = replies_count + 1, updated_at = NOW() WHERE id = ?")->execute([$postId]);
        return $replyId;
    }

    public function reportContent(int $reporterId, string $type, int $contentId, string $reason): bool {
        $stmt = $this->db->prepare("
            INSERT INTO content_reports (reporter_id, content_type, content_id, reason, status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        return $stmt->execute([$reporterId, $type, $contentId, $reason]);
    }

    public function getReportsAdmin(): array {
        $stmt = $this->db->query("
            SELECT cr.*, u.full_name as reporter_name, u.email as reporter_email
            FROM content_reports cr
            JOIN users u ON u.id = cr.reporter_id
            ORDER BY cr.id DESC
        ");
        return $stmt->fetchAll();
    }
}
