-- ====================================================================
-- AliStack Learner - Database Schema & Initial Data
-- Powered by AliStack
-- Character set: utf8mb4 | Engine: InnoDB
-- Compatible with MySQL 8.0+ and MariaDB 10.4+
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `alistack_learner` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `alistack_learner`;

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------------------
-- 1. Table: users
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('super_admin', 'admin', 'moderator', 'student') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active',
  `avatar_url` VARCHAR(255) NULL,
  `bio` TEXT NULL,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_users_role` (`role`),
  INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. Table: password_reset_tokens
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(128) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. Table: email_verification_tokens
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `email_verification_tokens`;
CREATE TABLE `email_verification_tokens` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `token_hash` VARCHAR(128) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. Table: course_categories
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `course_categories`;
CREATE TABLE `course_categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `description` VARCHAR(255) NULL,
  `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-collection',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. Table: courses
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `courses`;
CREATE TABLE `courses` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `thumbnail` VARCHAR(255) NULL,
  `short_desc` VARCHAR(300) NOT NULL,
  `full_desc` TEXT NOT NULL,
  `difficulty` ENUM('beginner', 'intermediate', 'advanced') NOT NULL DEFAULT 'beginner',
  `language` VARCHAR(50) NOT NULL DEFAULT 'English',
  `estimated_duration` VARCHAR(50) NOT NULL DEFAULT '6 Hours',
  `learning_objectives` TEXT NULL,
  `prerequisites` TEXT NULL,
  `instructor_name` VARCHAR(100) NOT NULL DEFAULT 'AliStack Academy',
  `youtube_playlist_url` VARCHAR(255) NULL,
  `youtube_playlist_id` VARCHAR(100) NULL,
  `status` ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'published',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `course_order` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `course_categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_courses_status` (`status`),
  INDEX `idx_courses_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. Table: course_lessons
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `course_lessons`;
CREATE TABLE `course_lessons` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NULL,
  `youtube_video_id` VARCHAR(50) NOT NULL,
  `duration_minutes` INT NOT NULL DEFAULT 10,
  `lesson_order` INT NOT NULL DEFAULT 1,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `learning_objectives` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  INDEX `idx_lessons_course_order` (`course_id`, `lesson_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. Table: course_resources
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `course_resources`;
CREATE TABLE `course_resources` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NULL,
  `title` VARCHAR(150) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `file_size` INT UNSIGNED NOT NULL DEFAULT 0,
  `file_type` VARCHAR(50) NOT NULL DEFAULT 'pdf',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. Table: enrollments
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `enrollments`;
CREATE TABLE `enrollments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `status` ENUM('active', 'completed', 'dropped') NOT NULL DEFAULT 'active',
  `progress_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `last_lesson_id` INT UNSIGNED NULL,
  `last_accessed_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `enrolled_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_enrollment` (`user_id`, `course_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`last_lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 9. Table: lesson_progress
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `lesson_progress`;
CREATE TABLE `lesson_progress` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NOT NULL,
  `is_completed` TINYINT(1) NOT NULL DEFAULT 0,
  `last_position_seconds` INT NOT NULL DEFAULT 0,
  `completed_at` DATETIME NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_lesson` (`user_id`, `lesson_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 10. Table: lesson_notes
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `lesson_notes`;
CREATE TABLE `lesson_notes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `is_important` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 11. Table: lesson_bookmarks
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `lesson_bookmarks`;
CREATE TABLE `lesson_bookmarks` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_bookmark` (`user_id`, `lesson_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 12. Table: assessments
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `assessments`;
CREATE TABLE `assessments` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `instructions` TEXT NULL,
  `total_questions_to_ask` INT UNSIGNED NOT NULL DEFAULT 10,
  `total_marks` INT UNSIGNED NOT NULL DEFAULT 100,
  `pass_percentage` DECIMAL(5,2) NOT NULL DEFAULT 70.00,
  `time_limit_minutes` INT UNSIGNED NOT NULL DEFAULT 20,
  `max_attempts` INT UNSIGNED NOT NULL DEFAULT 3,
  `randomize_questions` TINYINT(1) NOT NULL DEFAULT 1,
  `randomize_options` TINYINT(1) NOT NULL DEFAULT 0,
  `reveal_answers` TINYINT(1) NOT NULL DEFAULT 1,
  `retakes_allowed` TINYINT(1) NOT NULL DEFAULT 1,
  `is_published` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 13. Table: assessment_questions
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `assessment_questions`;
CREATE TABLE `assessment_questions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `question_text` TEXT NOT NULL,
  `option_a` TEXT NOT NULL,
  `option_b` TEXT NOT NULL,
  `option_c` TEXT NOT NULL,
  `option_d` TEXT NOT NULL,
  `correct_option` ENUM('A', 'B', 'C', 'D') NOT NULL,
  `explanation` TEXT NULL,
  `topic` VARCHAR(100) NULL,
  `difficulty` ENUM('easy', 'medium', 'hard') NOT NULL DEFAULT 'medium',
  `marks` INT UNSIGNED NOT NULL DEFAULT 10,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`assessment_id`) REFERENCES `assessments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 14. Table: assessment_attempts
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `assessment_attempts`;
CREATE TABLE `assessment_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `assessment_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `attempt_number` INT UNSIGNED NOT NULL DEFAULT 1,
  `score` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  `total_marks` DECIMAL(6,2) NOT NULL DEFAULT 100.00,
  `percentage` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('in_progress', 'completed', 'timed_out', 'abandoned') NOT NULL DEFAULT 'in_progress',
  `qualifying_achievement` ENUM('certificate', 'silver_badge', 'bronze_badge', 'starter_badge', 'none') NOT NULL DEFAULT 'none',
  `started_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `submitted_at` DATETIME NULL,
  FOREIGN KEY (`assessment_id`) REFERENCES `assessments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  INDEX `idx_attempts_user_assessment` (`user_id`, `assessment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 15. Table: assessment_answers
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `assessment_answers`;
CREATE TABLE `assessment_answers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `attempt_id` INT UNSIGNED NOT NULL,
  `question_id` INT UNSIGNED NOT NULL,
  `selected_option` ENUM('A', 'B', 'C', 'D') NULL,
  `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
  `marks_awarded` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`attempt_id`) REFERENCES `assessment_attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`question_id`) REFERENCES `assessment_questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 16. Table: certificates
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `certificates`;
CREATE TABLE `certificates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `certificate_number` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `attempt_id` INT UNSIGNED NOT NULL,
  `student_name` VARCHAR(150) NOT NULL,
  `course_title` VARCHAR(200) NOT NULL,
  `score_percentage` DECIMAL(5,2) NOT NULL,
  `verification_code` VARCHAR(64) NOT NULL UNIQUE,
  `status` ENUM('valid', 'revoked') NOT NULL DEFAULT 'valid',
  `issued_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attempt_id`) REFERENCES `assessment_attempts`(`id`) ON DELETE CASCADE,
  INDEX `idx_cert_code` (`verification_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 17. Table: achievement_badges
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `achievement_badges`;
CREATE TABLE `achievement_badges` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `badge_key` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(100) NOT NULL,
  `min_score` DECIMAL(5,2) NOT NULL,
  `max_score` DECIMAL(5,2) NOT NULL,
  `badge_tier` ENUM('silver', 'bronze', 'starter') NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 18. Table: student_achievements
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `student_achievements`;
CREATE TABLE `student_achievements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `attempt_id` INT UNSIGNED NOT NULL,
  `badge_key` VARCHAR(50) NOT NULL,
  `score_percentage` DECIMAL(5,2) NOT NULL,
  `awarded_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`attempt_id`) REFERENCES `assessment_attempts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`badge_key`) REFERENCES `achievement_badges`(`badge_key`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 19. Table: ai_conversations
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `ai_conversations`;
CREATE TABLE `ai_conversations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `lesson_id` INT UNSIGNED NULL,
  `title` VARCHAR(200) NOT NULL DEFAULT 'New Conversation',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`lesson_id`) REFERENCES `course_lessons`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 20. Table: ai_messages
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `ai_messages`;
CREATE TABLE `ai_messages` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT UNSIGNED NOT NULL,
  `sender` ENUM('user', 'assistant', 'system') NOT NULL,
  `message` MEDIUMTEXT NOT NULL,
  `tokens_used` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`conversation_id`) REFERENCES `ai_conversations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 21. Table: ai_usage_logs
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `ai_usage_logs`;
CREATE TABLE `ai_usage_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `tokens_prompt` INT NOT NULL DEFAULT 0,
  `tokens_completion` INT NOT NULL DEFAULT 0,
  `endpoint_used` VARCHAR(255) NOT NULL,
  `response_time_ms` INT NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 22. Table: discussion_groups
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `discussion_groups`;
CREATE TABLE `discussion_groups` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `course_id` INT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `slug` VARCHAR(170) NOT NULL UNIQUE,
  `description` TEXT NOT NULL,
  `rules` TEXT NULL,
  `is_private` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`course_id`) REFERENCES `courses`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 23. Table: group_memberships
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `group_memberships`;
CREATE TABLE `group_memberships` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `role` ENUM('member', 'moderator') NOT NULL DEFAULT 'member',
  `status` ENUM('active', 'banned') NOT NULL DEFAULT 'active',
  `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_group_user` (`group_id`, `user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `discussion_groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 24. Table: group_join_requests
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `group_join_requests`;
CREATE TABLE `group_join_requests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_request` (`group_id`, `user_id`),
  FOREIGN KEY (`group_id`) REFERENCES `discussion_groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 25. Table: discussion_posts
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `discussion_posts`;
CREATE TABLE `discussion_posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `group_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `is_pinned` TINYINT(1) NOT NULL DEFAULT 0,
  `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
  `replies_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`group_id`) REFERENCES `discussion_groups`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 26. Table: discussion_replies
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `discussion_replies`;
CREATE TABLE `discussion_replies` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `parent_reply_id` INT UNSIGNED NULL,
  `content` MEDIUMTEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`post_id`) REFERENCES `discussion_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_reply_id`) REFERENCES `discussion_replies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 27. Table: post_reactions
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `post_reactions`;
CREATE TABLE `post_reactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT UNSIGNED NULL,
  `reply_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `reaction_type` ENUM('like', 'helpful', 'insightful') NOT NULL DEFAULT 'like',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`post_id`) REFERENCES `discussion_posts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reply_id`) REFERENCES `discussion_replies`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 28. Table: content_reports
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `content_reports`;
CREATE TABLE `content_reports` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `reporter_id` INT UNSIGNED NOT NULL,
  `content_type` ENUM('post', 'reply') NOT NULL,
  `content_id` INT UNSIGNED NOT NULL,
  `reason` VARCHAR(255) NOT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 29. Table: notifications
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `link_url` VARCHAR(255) NULL,
  `type` ENUM('system', 'course', 'assessment', 'achievement', 'community') NOT NULL DEFAULT 'system',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  INDEX `idx_notif_user_read` (`user_id`, `is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 30. Table: platform_settings
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE `platform_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(80) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) NULL,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 31. Table: audit_logs
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ====================================================================
-- SEED DATA
-- ====================================================================

-- Initial Administrator User (Default fallback before or alongside seeder script)
-- Password is 'Admin@AliStack2026!'
INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, 'Super Administrator', 'admin@alistack.com', 'superadmin', '$2y$12$e6m7xQeG/1oN4oD7oVf9re9YkOQzT7nJ8c8P1gV0K7sQ/M2fLh4Gy', 'super_admin', 'active', NOW());

-- Badges
INSERT INTO `achievement_badges` (`badge_key`, `title`, `min_score`, `max_score`, `badge_tier`, `description`) VALUES
('silver_badge', 'Silver Proficiency Badge', 60.00, 69.99, 'silver', 'Awarded to learners achieving between 60% and 69.99% on the final course assessment.'),
('bronze_badge', 'Bronze Competency Badge', 50.00, 59.99, 'bronze', 'Awarded to learners achieving between 50% and 59.99% on the final course assessment.'),
('starter_badge', 'Foundation Starter Badge', 40.00, 49.99, 'starter', 'Awarded to learners achieving between 40% and 49.99% on the final course assessment.');

-- Categories
INSERT INTO `course_categories` (`id`, `name`, `slug`, `description`, `icon`) VALUES
(1, 'Web Development', 'web-development', 'Modern web technologies, server-side engineering, and client-side design.', 'bi-code-slash'),
(2, 'Artificial Intelligence', 'artificial-intelligence', 'Applied AI, large language models, prompt engineering, and machine learning.', 'bi-cpu'),
(3, 'Computer Science Foundations', 'cs-foundations', 'Algorithms, data structures, databases, and architectural best practices.', 'bi-diagram-3'),
(4, 'DevOps & Cloud', 'devops-cloud', 'Deployment, containers, Linux servers, and web application scalability.', 'bi-cloud-check');

-- Platform Settings
INSERT INTO `platform_settings` (`setting_key`, `setting_value`, `setting_group`, `description`) VALUES
('site_name', 'AliStack Learner', 'general', 'Platform Name'),
('site_tagline', 'Learn with focus. Practice with purpose. Prove your skills.', 'general', 'Platform Tagline'),
('parent_brand', 'AliStack', 'general', 'Parent Company/Brand'),
('certificate_threshold', '70.00', 'assessment', 'Passing percentage required for AliStack Certificate'),
('silver_badge_threshold', '60.00', 'assessment', 'Minimum percentage for Silver Badge'),
('bronze_badge_threshold', '50.00', 'assessment', 'Minimum percentage for Bronze Badge'),
('starter_badge_threshold', '40.00', 'assessment', 'Minimum percentage for Starter Badge'),
('ai_provider_endpoint', 'https://agentrouter.org/v1/chat/completions', 'ai', 'AgentRouter API Chat Endpoint'),
('ai_model_name', 'gpt-4o-mini', 'ai', 'AgentRouter AI Model identifier'),
('ai_temperature', '0.7', 'ai', 'AI generation temperature'),
('ai_max_tokens', '800', 'ai', 'Maximum tokens per response'),
('ai_daily_limit_per_user', '50', 'ai', 'Maximum AI prompts per student per day'),
('require_lesson_completion_for_test', '1', 'assessment', 'Require 100% lesson completion before assessment is unlocked'),
('max_upload_size_mb', '10', 'storage', 'Maximum file upload size in MB');

-- Courses
INSERT INTO `courses` (`id`, `category_id`, `title`, `slug`, `thumbnail`, `short_desc`, `full_desc`, `difficulty`, `language`, `estimated_duration`, `learning_objectives`, `prerequisites`, `instructor_name`, `youtube_playlist_url`, `youtube_playlist_id`, `status`, `is_featured`, `course_order`) VALUES
(1, 1, 'Full-Stack PHP 8 & MySQL Modern Architecture', 'fullstack-php-8-mysql-architecture', 'php-course.jpg', 'Master modern PHP 8.2+, OOP design patterns, PDO security, and relational MySQL database design.', 'A comprehensive, distraction-free course guiding you through production PHP 8.2+ development. You will learn modern syntax, type declarations, Object-Oriented programming principles, secure database access via PDO, authentication systems, and scalable MVC architectures without depending on heavyweight frameworks.', 'beginner', 'English', '8 Hours', 'Master PHP 8.2+ syntax and typed properties\nBuild secure database queries using PDO prepared statements\nUnderstand password hashing and session security\nArchitect clean, maintainable modular backends', 'Basic understanding of HTML and basic logic.', 'Engr. Ali & Team AliStack', 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9gksOX3BdCEVDhUXF689Vj', 'PL4cUxeGkcC9gksOX3BdCEVDhUXF689Vj', 'published', 1, 1),
(2, 1, 'Modern Vanilla JavaScript & Web APIs Deep Dive', 'modern-vanilla-javascript-deep-dive', 'js-course.jpg', 'Understand asynchronous JavaScript, DOM manipulation, Fetch API, and state management.', 'Learn vanilla JavaScript from foundational concepts to advanced asynchronous programming. Explore ES6+ modules, promises, async/await, DOM events, and API integrations with zero framework bloat.', 'intermediate', 'English', '6 Hours', 'Confidently manipulate the DOM without libraries\nMaster asynchronous JavaScript with Promises and async/await\nConnect to REST APIs cleanly using Fetch\nHandle state and browser storage securely', 'Basic HTML & CSS knowledge.', 'AliStack Academy', 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9haFPT7J25Q9GRB_Z5230Z7', 'PL4cUxeGkcC9haFPT7J25Q9GRB_Z5230Z7', 'published', 1, 2),
(3, 2, 'Applied AI & Practical Prompt Engineering', 'applied-ai-practical-prompt-engineering', 'ai-course.jpg', 'Harness Large Language Models, Agentic APIs, and structured prompts to build intelligent workflows.', 'Step into practical AI engineering. Discover how LLMs process tokens, master chain-of-thought and few-shot prompt design, connect external tools through APIs, and integrate AI tutors and copilots into modern web applications.', 'beginner', 'English', '5 Hours', 'Understand LLM architectures, temperature, and tokens\nDesign robust few-shot and system prompts\nIntegrate AI gateway APIs like AgentRouter\nBuild contextual assistants with memory and constraints', 'No prior AI experience required.', 'AliStack AI Research Group', 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9i1m1Vq4zO0F5yZ5040f2p', 'PL4cUxeGkcC9i1m1Vq4zO0F5yZ5040f2p', 'published', 1, 3);

-- Course Lessons (Course 1: PHP & MySQL)
INSERT INTO `course_lessons` (`id`, `course_id`, `title`, `description`, `youtube_video_id`, `duration_minutes`, `lesson_order`, `is_published`) VALUES
(1, 1, 'Introduction to Modern PHP 8.2 & Environment Setup', 'Welcome to the course. We overview modern PHP standards, setup XAMPP / CLI, and write our first strongly typed PHP script.', 'nZJbQkIq3t0', 12, 1, 1),
(2, 1, 'PHP 8 Types, Functions & Control Structures', 'Explore scalar types, union types, nullables, match expressions, and pure functions in modern PHP.', '243pQxSDApM', 18, 2, 1),
(3, 1, 'Object-Oriented PHP: Classes, Inheritance & Interfaces', 'Understand encapsulation, inheritance, interfaces, and dependency injection in practical PHP software design.', 'gCo6JqGMi30', 22, 3, 1),
(4, 1, 'PDO Fundamentals & SQL Injection Defense', 'Connecting to MySQL securely with PDO. Using prepared statements with bound parameters to eliminate SQL injection.', 'kEW6f7PILc4', 25, 4, 1),
(5, 1, 'Session Management, Password Hashing & Authentication', 'Implement secure user sessions, session regeneration, and cryptographic password hashing with password_hash.', 'x0bO_o8R90s', 20, 5, 1);

-- Course Lessons (Course 2: Modern JavaScript)
INSERT INTO `course_lessons` (`id`, `course_id`, `title`, `description`, `youtube_video_id`, `duration_minutes`, `lesson_order`, `is_published`) VALUES
(6, 2, 'JavaScript Fundamentals: Scope, Closures & ES6', 'Deep dive into let/const, block scoping, arrow functions, and closures in modern JavaScript.', 'W6NZfCO5SIk', 15, 1, 1),
(7, 2, 'The DOM Tree, Event Listeners & Event Delegation', 'Querying DOM nodes efficiently, manipulating attributes, and utilizing event delegation for high performance.', '0ik6X4DJKCc', 18, 2, 1),
(8, 2, 'Promises, Async/Await and Error Handling', 'Understand the JavaScript event loop, asynchronous execution, and cleanly orchestrating promises with try/catch.', 'PoRJizFvM7s', 20, 3, 1),
(9, 2, 'The Fetch API & RESTful Communication', 'Communicating with backend endpoints using Fetch, sending JSON payloads, and reading HTTP response codes.', 'cuEtnrL9-H0', 22, 4, 1);

-- Course Lessons (Course 3: Applied AI)
INSERT INTO `course_lessons` (`id`, `course_id`, `title`, `description`, `youtube_video_id`, `duration_minutes`, `lesson_order`, `is_published`) VALUES
(10, 3, 'Foundations of LLMs, Tokens & Context Windows', 'How language models predict text, understand tokens, context limits, and temperature hyperparameters.', 'aircAruvnKk', 15, 1, 1),
(11, 3, 'System Prompts & Few-Shot Engineering', 'Crafting deterministic system instructions, role assignment, and few-shot formatting techniques.', 'jC4v5AS4RIM', 18, 2, 1),
(12, 3, 'Connecting LLMs to Web Apps via AgentRouter API', 'Building server-side API clients, maintaining chat history, and securing API keys.', 'zjkBMFhNj_g', 25, 3, 1);

-- Assessments
INSERT INTO `assessments` (`id`, `course_id`, `title`, `instructions`, `total_questions_to_ask`, `total_marks`, `pass_percentage`, `time_limit_minutes`, `max_attempts`, `randomize_questions`, `reveal_answers`, `retakes_allowed`, `is_published`) VALUES
(1, 1, 'Full-Stack PHP 8 & MySQL Final Assessment', 'This assessment tests your comprehensive knowledge of modern PHP 8.2, PDO security, Object-Oriented principles, and authentication. Pass with 70%+ to earn your official AliStack Certificate. Scores between 40% and 69.99% earn performance badges.', 5, 50, 70.00, 15, 3, 1, 1, 1, 1),
(2, 2, 'Modern JavaScript & Web APIs Assessment', 'Demonstrate your mastery of ES6+, DOM manipulation, asynchronous programming, and REST integrations.', 4, 40, 70.00, 12, 3, 1, 1, 1, 1),
(3, 3, 'Applied AI & Prompt Engineering Assessment', 'Evaluate your understanding of prompt design, context management, tokens, and API integration.', 3, 30, 70.00, 10, 3, 1, 1, 1, 1);

-- Assessment Questions (Assessment 1: PHP 8)
INSERT INTO `assessment_questions` (`id`, `assessment_id`, `course_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `topic`, `difficulty`, `marks`) VALUES
(1, 1, 1, 'Which PHP function is the cryptographically secure and standard recommended method for hashing user passwords?', 'md5()', 'sha1()', 'password_hash()', 'crypt_aes()', 'C', 'password_hash() uses strong one-way hashing algorithms (such as Bcrypt or Argon2) with automatic salt generation and resistant against rainbow table attacks.', 'Security', 'easy', 10),
(2, 1, 1, 'What is the primary security advantage of using PDO prepared statements with bound parameters?', 'It compresses the query string to save bandwidth', 'It guarantees queries execute faster in all scenarios', 'It completely separates SQL code from user-supplied data, neutralizing SQL injection', 'It encrypts database tables on disk automatically', 'C', 'Prepared statements send query structure and parameters separately to the database engine, preventing user input from altering the SQL syntax.', 'PDO & Database', 'medium', 10),
(3, 1, 1, 'Why is session_regenerate_id(true) critical immediately following a successful user login?', 'It clears the user browser cache', 'It prevents Session Fixation attacks by issuing a brand new session identifier', 'It refreshes the database connection pool', 'It encrypts the PHP source files in memory', 'B', 'Session Fixation occurs when an attacker forces a known session ID on a victim. Regenerating the ID upon privilege change invalidates any pre-session identifier.', 'Authentication', 'medium', 10),
(4, 1, 1, 'In PHP 8.0+, what is the behavior of the match expression compared to a legacy switch statement?', 'It uses loose comparison (==) and allows fall-through', 'It uses strict comparison (===), returns a value directly, and does not require break statements', 'It can only evaluate boolean variables', 'It runs in a separate thread asynchronously', 'B', 'The match expression uses strict identity checks (===), returns an expression result, and executes only the matching branch without fall-through.', 'PHP Syntax', 'medium', 10),
(5, 1, 1, 'Which HTTP header attribute prevents JavaScript from accessing session cookies, defending against XSS cookie theft?', 'SameSite=Lax', 'Secure', 'HttpOnly', 'Max-Age', 'C', 'The HttpOnly flag directs browsers not to expose the cookie to client-side scripts like document.cookie, mitigating XSS-based session hijacking.', 'Session Security', 'easy', 10);

-- Assessment Questions (Assessment 2: JavaScript)
INSERT INTO `assessment_questions` (`id`, `assessment_id`, `course_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `topic`, `difficulty`, `marks`) VALUES
(6, 2, 2, 'What does an async function in modern JavaScript always return?', 'A boolean value indicating execution status', 'A Promise that resolves with the returned value or rejects with an uncaught error', 'A generator iterator object', 'Undefined if no return statement is explicitly written', 'B', 'Functions declared with async always wrap the return value inside a resolved Promise, or reject if an exception is thrown.', 'Async JS', 'medium', 10),
(7, 2, 2, 'Which technique attaches a single event listener to a parent element to handle events from multiple children?', 'Event Bubbling Negation', 'Event Delegation', 'Event Throttling', 'Event Hoisting', 'B', 'Event delegation takes advantage of event propagation (bubbling) to handle events on parent containers rather than binding to every individual child.', 'DOM', 'easy', 10),
(8, 2, 2, 'When using the Fetch API, does a 404 or 500 HTTP response status cause the returned Promise to reject?', 'Yes, any status outside 200-299 rejects the promise', 'No, the Promise only rejects on network failures or request blocking; response.ok must be checked', 'Yes, only 500 status codes reject', 'Only if the credentials option is set to include', 'B', 'A fetch() Promise only rejects if there is a network error or the request was aborted. HTTP status codes like 404 or 500 resolve normally with response.ok set to false.', 'Web APIs', 'medium', 10),
(9, 2, 2, 'What is the purpose of debouncing an input event handler in JavaScript?', 'To run the function repeatedly at exact intervals', 'To delay function execution until a specified delay has passed since the user last triggered the event', 'To cancel all network packets in flight', 'To convert synchronous code into web worker threads', 'B', 'Debouncing limits the rate at which a function is invoked, ensuring it only executes after the user stops typing for a given duration.', 'Performance', 'medium', 10);

-- Assessment Questions (Assessment 3: Applied AI)
INSERT INTO `assessment_questions` (`id`, `assessment_id`, `course_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`, `explanation`, `topic`, `difficulty`, `marks`) VALUES
(10, 3, 3, 'In LLM applications, what does lowering the temperature parameter (e.g. from 0.9 to 0.1) achieve?', 'Increases the maximum tokens generated per second', 'Makes responses more deterministic, focused, and repeatable', 'Doubles the context window capacity', 'Automatically verifies factual accuracy with Google Search', 'B', 'Lower temperature reduces sampling randomness, making the model pick the highest probability tokens for more consistent and focused responses.', 'LLM Parameters', 'easy', 10),
(11, 3, 3, 'Where should an external AI gateway API key (such as AgentRouter) be stored and used in a web application?', 'In client-side JavaScript constants', 'Exclusively on the server environment, never exposed to browser requests or public source files', 'In public HTML data attributes', 'Inside CSS root custom properties', 'B', 'API keys represent paid access and credentials. Exposing them to client browsers allows unauthorized extraction and misuse.', 'Security & API', 'easy', 10),
(12, 3, 3, 'What is "few-shot prompting"?', 'Limiting the model to 3 attempts before returning an error', 'Providing the model with a few high-quality input-output examples inside the prompt to guide its behavior', 'Training model weights using gradient descent on client devices', 'Using multiple AI models simultaneously in a round-robin cycle', 'B', 'Few-shot prompting provides sample demonstrations within the prompt context, significantly improving accuracy on formatting and tasks.', 'Prompt Engineering', 'medium', 10);

-- Discussion Groups
INSERT INTO `discussion_groups` (`id`, `course_id`, `name`, `slug`, `description`, `rules`, `is_private`, `is_active`, `created_by`) VALUES
(1, 1, 'PHP & MySQL Architecture Circle', 'php-mysql-architecture-circle', 'A dedicated space for students of the Full-Stack PHP & MySQL Masterclass to discuss patterns, ask debugging questions, and collaborate.', '1. Be respectful to fellow learners.\n2. Do not share complete assessment answers.\n3. Format code snippets cleanly.', 1, 1, 1),
(2, 2, 'JavaScript Engineers Lounge', 'javascript-engineers-lounge', 'Share code snippets, discuss modern browser features, DOM tricks, and asynchronous design.', '1. Stay on topic.\n2. No promotional spam.\n3. Encourage constructive feedback.', 1, 1, 1),
(3, 3, 'AI & Prompt Crafting Hub', 'ai-prompt-crafting-hub', 'Explore LLM workflows, test system prompts, share tips on AgentRouter integration, and optimize model responses.', '1. Respect API safety and usage guidelines.\n2. Share prompts and observations clearly.', 0, 1, 1);

SET FOREIGN_KEY_CHECKS = 1;
