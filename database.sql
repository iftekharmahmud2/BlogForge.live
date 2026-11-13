-- ========================================================
-- BlogForge (blogforge.live) Database Schema & Initial Seed
-- Full-Stack Blog & Content Management System (CMS)
-- Engine: MySQL 8.0+ / MariaDB 10.5+
-- ========================================================

CREATE DATABASE IF NOT EXISTS `blogforge`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `blogforge`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) DEFAULT NULL,
  `last_name` VARCHAR(50) DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `handle` VARCHAR(50) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'author', 'reader') NOT NULL DEFAULT 'reader',
  `status` ENUM('active', 'banned') NOT NULL DEFAULT 'active',
  `avatar` VARCHAR(500) DEFAULT NULL,
  `bio` VARCHAR(280) DEFAULT NULL,
  `dob` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_users_email` (`email`),
  UNIQUE KEY `idx_users_handle` (`handle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_categories_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: posts
-- --------------------------------------------------------
CREATE TABLE `posts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `author_id` INT UNSIGNED NOT NULL,
  `category_id` INT UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `excerpt` VARCHAR(500) DEFAULT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `featured_image` VARCHAR(500) DEFAULT NULL,
  `status` ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views` INT UNSIGNED NOT NULL DEFAULT 0,
  `read_time` INT UNSIGNED NOT NULL DEFAULT 4,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_posts_slug` (`slug`),
  KEY `idx_posts_author` (`author_id`),
  KEY `idx_posts_category` (`category_id`),
  KEY `idx_posts_status_featured` (`status`, `is_featured`),
  CONSTRAINT `fk_posts_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_posts_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: comments
-- --------------------------------------------------------
CREATE TABLE `comments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `status` ENUM('approved', 'pending', 'spam') NOT NULL DEFAULT 'approved',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_comments_post` (`post_id`),
  KEY `idx_comments_user` (`user_id`),
  KEY `idx_comments_status` (`status`),
  CONSTRAINT `fk_comments_post` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: settings
-- --------------------------------------------------------
CREATE TABLE `settings` (
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================================
-- Seed Data
-- ========================================================

-- 1. Default Administrators (Password: Admin@123)
-- Hash generated using PASSWORD_BCRYPT for 'Admin@123'
INSERT INTO `users` (`id`, `first_name`, `last_name`, `name`, `email`, `handle`, `password_hash`, `role`, `status`, `bio`, `created_at`)
VALUES
  (1, 'Admin', 'BlogForge', 'Admin BlogForge', 'admin@blogforge.live', 'admin', '$2y$10$Uo04u51v/Hl5aK0jO3eZc.QO0pWwLqBvZ1o2h26.4c5aXl0BqGvWq', 'admin', 'active', 'Official platform administrator.', NOW()),
  (2, 'Iftekhar', 'Mahmud', 'Iftekhar Mahmud', 'iftekhar69c@gmail.com', 'iftekhar', '$2y$10$Uo04u51v/Hl5aK0jO3eZc.QO0pWwLqBvZ1o2h26.4c5aXl0BqGvWq', 'admin', 'active', 'BlogForge Administrator & Lead Engineer.', NOW());

-- 2. Five Sample Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`)
VALUES
  (1, 'Technology', 'technology', 'Deep dives into software architecture, edge systems, and computing foundations.', NOW()),
  (2, 'Tutorials', 'tutorials', 'Step-by-step technical guides and engineering playbooks.', NOW()),
  (3, 'Case Studies', 'case-studies', 'In-depth real-world retrospectives and performance breakdowns.', NOW()),
  (4, 'Opinion', 'opinion', 'Thought pieces, perspectives, and editorial commentary on modern tech.', NOW()),
  (5, 'News', 'news', 'Announcements, releases, and platform updates.', NOW());

-- 3. Global Site Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `created_at`)
VALUES
  ('site_title', 'BlogForge', NOW()),
  ('site_tagline', 'Where ideas meet ink. A distraction-free publishing space for deep work, essays, and stories.', NOW()),
  ('site_description', 'BlogForge is an independent editorial publishing platform designed for writers, essayists, and curious readers.', NOW()),
  ('maintenance_mode', '0', NOW()),
  ('default_role', 'reader', NOW()),
  ('comments_policy', 'approved', NOW());

-- 4. Three Sample Published Posts
INSERT INTO `posts` (`id`, `author_id`, `category_id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `status`, `is_featured`, `views`, `read_time`, `created_at`)
VALUES
  (1, 1, 1, 'The Architecture of Distraction-Free Publishing Systems', 'the-architecture-of-distraction-free-publishing-systems', 'How minimalistic typography and decoupled frontends create the optimal reading experience.', '<p>In an era dominated by algorithmic feeds and intrusive popups, modern readers crave clean typographic spaces. When building a publication platform, our first constraint was latency: every page must render within 100 milliseconds.</p><h2>Designing for Focus</h2><p>Distraction-free publishing is more than just removing ads. It is an intentional constraint on layout, line height, contrast ratios, and content presentation.</p>', 'https://images.unsplash.com/photo-1499750310107-5fef28a66643?auto=format&fit=crop&w=1200&q=80', 'published', 1, 342, 5, NOW()),
  (2, 2, 2, 'Building Production-Ready RBAC with PHP and NoSQL', 'building-production-ready-rbac-with-php-and-nosql', 'A comprehensive pattern for managing administrator, author, and reader permissions across scalable applications.', '<p>Role-Based Access Control (RBAC) is the cornerstone of multi-tenant content management systems. In this guide, we explore strict server-side authorization guards, session synchronization, and defense-in-depth security principles.</p><h2>The Three Role Tiers</h2><p>By enforcing strict boundary checks at the router and controller level, we guarantee that administrative endpoints are inaccessible to unauthorized visitors.</p>', 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=1200&q=80', 'published', 0, 189, 7, NOW()),
  (3, 1, 3, 'Refactoring Monolithic Web Apps for Extreme Cold-Start Speed', 'refactoring-monolithic-web-apps-for-extreme-cold-start-speed', 'Case study detailing our journey from heavy bundles down to lean server-rendered assets with sub-50ms TTFB.', '<p>Every millisecond saved in First Contentful Paint directly correlates to engagement. Learn how BlogForge stripped unnecessary JS overhead and optimized server-side caching.</p>', 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=1200&q=80', 'published', 0, 95, 4, NOW());

-- 5. Sample Comments
INSERT INTO `comments` (`id`, `post_id`, `user_id`, `body`, `status`, `created_at`)
VALUES
  (1, 1, 2, 'Incredible read! The perspective on latency and typography hits home.', 'approved', NOW()),
  (2, 2, 1, 'Clear and actionable breakdown of session guards.', 'approved', NOW());
