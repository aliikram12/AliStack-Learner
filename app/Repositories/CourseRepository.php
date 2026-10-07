<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

class CourseRepository {
    protected PDO $db;

    public function __construct() {
        $this->db = getDbConnection();
    }

    public function getCategories(): array {
        $stmt = $this->db->query("SELECT * FROM course_categories WHERE is_active = 1 ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getAllCategoriesAdmin(): array {
        $sql = "SELECT cat.*, (SELECT COUNT(*) FROM courses c WHERE c.category_id = cat.id) as course_count 
                FROM course_categories cat 
                ORDER BY cat.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getCategoryById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM course_categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createCategory(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO course_categories (name, slug, description, icon, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['name'],
            $data['slug'],
            $data['description'] ?? null,
            $data['icon'] ?? 'bi-folder2',
            $data['is_active'] ?? 1
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateCategory(int $id, array $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $key => $val) {
            $fields[] = "`{$key}` = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE course_categories SET " . implode(', ', $fields) . " WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function deleteCategory(int $id): bool {
        // Disassociate courses first
        $this->db->prepare("UPDATE courses SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        $stmt = $this->db->prepare("DELETE FROM course_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAllPublished(?int $categoryId = null, ?string $difficulty = null, ?string $search = null): array {
        $where = ["c.status = 'published'"];
        $params = [];

        if ($categoryId) {
            $where[] = "c.category_id = ?";
            $params[] = $categoryId;
        }
        if ($difficulty && in_array($difficulty, ['beginner', 'intermediate', 'advanced'], true)) {
            $where[] = "c.difficulty = ?";
            $params[] = $difficulty;
        }
        if ($search) {
            $where[] = "(c.title LIKE ? OR c.short_desc LIKE ? OR c.instructor_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = "WHERE " . implode(' AND ', $where);
        $sql = "SELECT c.*, cat.name as category_name,
                (SELECT COUNT(*) FROM course_lessons cl WHERE cl.course_id = c.id AND cl.is_published = 1) as lesson_count,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
                FROM courses c
                LEFT JOIN course_categories cat ON cat.id = c.category_id
                {$whereClause}
                ORDER BY c.is_featured DESC, c.course_order ASC, c.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getAllAdmin(int $page = 1, int $perPage = 20, ?string $status = null, ?string $search = null): array {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if ($status) {
            $where[] = "c.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where[] = "(c.title LIKE ? OR c.instructor_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $sql = "SELECT c.*, cat.name as category_name,
                (SELECT COUNT(*) FROM course_lessons cl WHERE cl.course_id = c.id) as lesson_count,
                (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
                FROM courses c
                LEFT JOIN course_categories cat ON cat.id = c.category_id
                {$whereClause}
                ORDER BY c.id DESC
                LIMIT {$perPage} OFFSET {$offset}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countAllAdmin(?string $status = null, ?string $search = null): int {
        $where = [];
        $params = [];
        if ($status) {
            $where[] = "c.status = ?";
            $params[] = $status;
        }
        if ($search) {
            $where[] = "(c.title LIKE ? OR c.instructor_name LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        $whereClause = !empty($where) ? "WHERE " . implode(' AND ', $where) : "";
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM courses c {$whereClause}");
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function findBySlug(string $slug): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name,
            (SELECT COUNT(*) FROM course_lessons cl WHERE cl.course_id = c.id AND cl.is_published = 1) as lesson_count,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
            FROM courses c
            LEFT JOIN course_categories cat ON cat.id = c.category_id
            WHERE c.slug = ?
            LIMIT 1
        ");
        $stmt->execute([$slug]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT c.*, cat.name as category_name,
            (SELECT COUNT(*) FROM course_lessons cl WHERE cl.course_id = c.id) as lesson_count,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) as student_count
            FROM courses c
            LEFT JOIN course_categories cat ON cat.id = c.category_id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO courses (category_id, title, slug, thumbnail, short_desc, full_desc, difficulty, language, estimated_duration, learning_objectives, prerequisites, instructor_name, youtube_playlist_url, youtube_playlist_id, status, is_featured, course_order, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['category_id'] ?: null,
            $data['title'],
            $data['slug'],
            $data['thumbnail'] ?? null,
            $data['short_desc'],
            $data['full_desc'],
            $data['difficulty'] ?? 'beginner',
            $data['language'] ?? 'English',
            $data['estimated_duration'] ?? '4 Hours',
            $data['learning_objectives'] ?? null,
            $data['prerequisites'] ?? null,
            $data['instructor_name'] ?? 'AliStack Academy',
            $data['youtube_playlist_url'] ?? null,
            $data['youtube_playlist_id'] ?? null,
            $data['status'] ?? 'published',
            $data['is_featured'] ?? 0,
            $data['course_order'] ?? 0
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
        $sql = "UPDATE courses SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================================
    // Lessons
    // =========================================================================
    public function getLessons(int $courseId, bool $publishedOnly = true): array {
        $sql = "SELECT * FROM course_lessons WHERE course_id = ?";
        if ($publishedOnly) {
            $sql .= " AND is_published = 1";
        }
        $sql .= " ORDER BY lesson_order ASC, id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }

    public function getLessonById(int $lessonId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM course_lessons WHERE id = ? LIMIT 1");
        $stmt->execute([$lessonId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function createLesson(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO course_lessons (course_id, title, description, youtube_video_id, duration_minutes, lesson_order, is_published, learning_objectives, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['course_id'],
            $data['title'],
            $data['description'] ?? null,
            $data['youtube_video_id'],
            $data['duration_minutes'] ?? 10,
            $data['lesson_order'] ?? 1,
            $data['is_published'] ?? 1,
            $data['learning_objectives'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updateLesson(int $lessonId, array $data): bool {
        $fields = [];
        $params = [];
        foreach ($data as $key => $val) {
            $fields[] = "`{$key}` = ?";
            $params[] = $val;
        }
        $params[] = $lessonId;
        $sql = "UPDATE course_lessons SET " . implode(', ', $fields) . ", updated_at = NOW() WHERE id = ?";
        return $this->db->prepare($sql)->execute($params);
    }

    public function deleteLesson(int $lessonId): bool {
        $stmt = $this->db->prepare("DELETE FROM course_lessons WHERE id = ?");
        return $stmt->execute([$lessonId]);
    }

    // =========================================================================
    // Enrollments & Progress
    // =========================================================================
    public function getEnrollment(int $userId, int $courseId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? LIMIT 1");
        $stmt->execute([$userId, $courseId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function enroll(int $userId, int $courseId): bool {
        $existing = $this->getEnrollment($userId, $courseId);
        if ($existing) {
            return true;
        }
        // First lesson ID
        $firstLesson = $this->db->prepare("SELECT id FROM course_lessons WHERE course_id = ? AND is_published = 1 ORDER BY lesson_order ASC LIMIT 1");
        $firstLesson->execute([$courseId]);
        $firstId = $firstLesson->fetchColumn() ?: null;

        $stmt = $this->db->prepare("
            INSERT INTO enrollments (user_id, course_id, status, progress_percent, last_lesson_id, last_accessed_at, enrolled_at)
            VALUES (?, ?, 'active', 0.00, ?, NOW(), NOW())
        ");
        return $stmt->execute([$userId, $courseId, $firstId]);
    }

    public function getStudentEnrollments(int $userId): array {
        $sql = "SELECT e.*, c.title, c.slug, c.thumbnail, c.short_desc, c.difficulty, c.estimated_duration,
                cl.title as last_lesson_title, cl.id as current_lesson_id,
                (SELECT COUNT(*) FROM course_lessons WHERE course_id = c.id AND is_published = 1) as total_lessons,
                (SELECT COUNT(*) FROM lesson_progress lp WHERE lp.user_id = e.user_id AND lp.course_id = c.id AND lp.is_completed = 1) as completed_lessons
                FROM enrollments e
                JOIN courses c ON c.id = e.course_id
                LEFT JOIN course_lessons cl ON cl.id = e.last_lesson_id
                WHERE e.user_id = ?
                ORDER BY e.last_accessed_at DESC, e.enrolled_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getLessonProgress(int $userId, int $courseId): array {
        $stmt = $this->db->prepare("SELECT * FROM lesson_progress WHERE user_id = ? AND course_id = ?");
        $stmt->execute([$userId, $courseId]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['lesson_id']] = $r;
        }
        return $map;
    }

    public function saveLessonProgress(int $userId, int $courseId, int $lessonId, bool $completed, int $lastPos): bool {
        $sql = "INSERT INTO lesson_progress (user_id, course_id, lesson_id, is_completed, last_position_seconds, completed_at, updated_at)
                VALUES (?, ?, ?, ?, ?, IF(? = 1, NOW(), NULL), NOW())
                ON DUPLICATE KEY UPDATE 
                    is_completed = IF(is_completed = 1, 1, VALUES(is_completed)),
                    last_position_seconds = VALUES(last_position_seconds),
                    completed_at = IF(is_completed = 1, completed_at, IF(VALUES(is_completed) = 1, NOW(), NULL)),
                    updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        $res = $stmt->execute([
            $userId, $courseId, $lessonId, 
            $completed ? 1 : 0, $lastPos, 
            $completed ? 1 : 0
        ]);

        // Recalculate enrollment progress
        $this->updateCourseProgress($userId, $courseId, $lessonId);
        return $res;
    }

    public function updateCourseProgress(int $userId, int $courseId, ?int $lastLessonId = null): void {
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM course_lessons WHERE course_id = ? AND is_published = 1");
        $totalStmt->execute([$courseId]);
        $total = (int)$totalStmt->fetchColumn();

        if ($total === 0) {
            return;
        }

        $doneStmt = $this->db->prepare("SELECT COUNT(*) FROM lesson_progress WHERE user_id = ? AND course_id = ? AND is_completed = 1");
        $doneStmt->execute([$userId, $courseId]);
        $done = (int)$doneStmt->fetchColumn();

        $percent = round(($done / $total) * 100, 2);
        $status = ($percent >= 100.00) ? 'completed' : 'active';
        $completedAt = ($status === 'completed') ? date('Y-m-d H:i:s') : null;

        $updateSql = "UPDATE enrollments 
                      SET progress_percent = ?, 
                          status = ?, 
                          last_accessed_at = NOW()" . 
                          ($lastLessonId ? ", last_lesson_id = ?" : "") . 
                          ($completedAt ? ", completed_at = ?" : "") . "
                      WHERE user_id = ? AND course_id = ?";

        $params = [$percent, $status];
        if ($lastLessonId) {
            $params[] = $lastLessonId;
        }
        if ($completedAt) {
            $params[] = $completedAt;
        }
        $params[] = $userId;
        $params[] = $courseId;

        $this->db->prepare($updateSql)->execute($params);
    }

    // =========================================================================
    // Notes & Bookmarks
    // =========================================================================
    public function getLessonNote(int $userId, int $lessonId): ?array {
        $stmt = $this->db->prepare("SELECT * FROM lesson_notes WHERE user_id = ? AND lesson_id = ? LIMIT 1");
        $stmt->execute([$userId, $lessonId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getUserNotes(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare("
            SELECT ln.*, cl.title as lesson_title, c.title as course_title, c.slug as course_slug
            FROM lesson_notes ln
            JOIN course_lessons cl ON cl.id = ln.lesson_id
            JOIN courses c ON c.id = ln.course_id
            WHERE ln.user_id = ?
            ORDER BY ln.updated_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function saveLessonNote(int $userId, int $courseId, int $lessonId, string $content, bool $isImportant = false): bool {
        $sql = "INSERT INTO lesson_notes (user_id, course_id, lesson_id, content, is_important, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    content = VALUES(content),
                    is_important = VALUES(is_important),
                    updated_at = NOW()";
        return $this->db->prepare($sql)->execute([
            $userId, $courseId, $lessonId, $content, $isImportant ? 1 : 0
        ]);
    }

    public function deleteLessonNote(int $userId, int $lessonId): bool {
        $stmt = $this->db->prepare("DELETE FROM lesson_notes WHERE user_id = ? AND lesson_id = ?");
        return $stmt->execute([$userId, $lessonId]);
    }

    public function getUserBookmarks(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT lb.*, cl.title as lesson_title, cl.youtube_video_id, c.title as course_title, c.slug as course_slug
            FROM lesson_bookmarks lb
            JOIN course_lessons cl ON cl.id = lb.lesson_id
            JOIN courses c ON c.id = lb.course_id
            WHERE lb.user_id = ?
            ORDER BY lb.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function isLessonBookmarked(int $userId, int $lessonId): bool {
        $stmt = $this->db->prepare("SELECT id FROM lesson_bookmarks WHERE user_id = ? AND lesson_id = ? LIMIT 1");
        $stmt->execute([$userId, $lessonId]);
        return (bool)$stmt->fetchColumn();
    }

    public function toggleBookmark(int $userId, int $courseId, int $lessonId): bool {
        if ($this->isLessonBookmarked($userId, $lessonId)) {
            $stmt = $this->db->prepare("DELETE FROM lesson_bookmarks WHERE user_id = ? AND lesson_id = ?");
            $stmt->execute([$userId, $lessonId]);
            return false; // unbookmarked
        } else {
            $stmt = $this->db->prepare("INSERT INTO lesson_bookmarks (user_id, course_id, lesson_id, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$userId, $courseId, $lessonId]);
            return true; // bookmarked
        }
    }

    // =========================================================================
    // Resources
    // =========================================================================
    public function getResources(int $courseId, ?int $lessonId = null): array {
        $sql = "SELECT * FROM course_resources WHERE course_id = ? AND is_active = 1";
        $params = [$courseId];
        if ($lessonId !== null) {
            $sql .= " AND (lesson_id = ? OR lesson_id IS NULL)";
            $params[] = $lessonId;
        }
        $sql .= " ORDER BY id DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function addResource(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO course_resources (course_id, lesson_id, title, file_path, file_size, file_type, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW())
        ");
        $stmt->execute([
            $data['course_id'],
            $data['lesson_id'] ?? null,
            $data['title'],
            $data['file_path'],
            $data['file_size'] ?? 0,
            $data['file_type'] ?? 'pdf'
        ]);
        return (int)$this->db->lastInsertId();
    }
}
