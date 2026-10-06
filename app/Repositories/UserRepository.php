<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class UserRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByUsername(string $username): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([trim($username)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO users (full_name, email, username, password_hash, role, status, avatar_url, bio, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['full_name'],
            strtolower(trim($data['email'])),
            trim($data['username']),
            $data['password_hash'],
            $data['role'] ?? 'student',
            $data['status'] ?? 'active',
            $data['avatar_url'] ?? null,
            $data['bio'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $key => $val) {
            $fields[] = "`{$key}` = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function updatePassword(int $id, string $hash): bool {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public function getAll(int $page = 1, int $perPage = 20, ?string $role = null, ?string $search = null): array {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($role) {
            $where[] = "role = ?";
            $params[] = $role;
        }
        if ($search) {
            $where[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $sql = "SELECT id, full_name, email, username, role, status, avatar_url, last_login_at, created_at 
                FROM users {$whereClause} 
                ORDER BY id DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAll(?string $role = null, ?string $search = null): int {
        $where = [];
        $params = [];
        if ($role) {
            $where[] = "role = ?";
            $params[] = $role;
        }
        if ($search) {
            $where[] = "(full_name LIKE ? OR email LIKE ? OR username LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users {$whereClause}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function createPasswordResetToken(int $userId, string $tokenHash, string $expiresAt): bool {
        // Delete any existing tokens for this user first
        $this->deletePasswordResetToken($userId);
        $stmt = $this->db->prepare("
            INSERT INTO password_reset_tokens (user_id, token_hash, expires_at, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        return $stmt->execute([$userId, $tokenHash, $expiresAt]);
    }

    public function findValidPasswordResetToken(string $tokenHash): ?array {
        $stmt = $this->db->prepare("
            SELECT prt.*, u.email, u.full_name 
            FROM password_reset_tokens prt
            JOIN users u ON u.id = prt.user_id
            WHERE prt.token_hash = ? AND prt.expires_at > NOW()
            LIMIT 1
        ");
        $stmt->execute([$tokenHash]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deletePasswordResetToken(int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
}
