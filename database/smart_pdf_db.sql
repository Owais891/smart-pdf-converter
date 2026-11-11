-- =====================================================================
-- Smart PDF - Database Schema
-- Database: smart_pdf_db
-- Engine:   InnoDB (UTF-8 mb4)
-- ---------------------------------------------------------------------
-- Import this file via phpMyAdmin:
--   1. Open http://localhost/phpmyadmin
--   2. Click "Import" in the top menu.
--   3. Choose this file: smart_pdf_db.sql
--   4. Press "Go".
-- =====================================================================

DROP DATABASE IF EXISTS `smart_pdf_db`;
CREATE DATABASE `smart_pdf_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `smart_pdf_db`;

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- ---------------------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `full_name`   VARCHAR(100) NOT NULL,
    `email`       VARCHAR(150) NOT NULL,
    `password`    VARCHAR(255) NOT NULL,
    `role`        ENUM('user','admin') NOT NULL DEFAULT 'user',
    `avatar`      VARCHAR(255) DEFAULT NULL,
    `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
    `remember_token`     VARCHAR(255) DEFAULT NULL,
    `last_login`  DATETIME DEFAULT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_users_email` (`email`),
    KEY `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: social_accounts
-- ---------------------------------------------------------------------
CREATE TABLE `social_accounts` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED NOT NULL,
    `provider`          ENUM('google','facebook') NOT NULL,
    `provider_user_id`  VARCHAR(191) NOT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_social_provider_user` (`provider`, `provider_user_id`),
    UNIQUE KEY `uniq_social_user_provider` (`user_id`, `provider`),
    CONSTRAINT `fk_social_accounts_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: conversion_history
-- ---------------------------------------------------------------------
CREATE TABLE `conversion_history` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED DEFAULT NULL,
    `tool_name`         VARCHAR(80) NOT NULL,
    `tool_action`       VARCHAR(80) DEFAULT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `converted_filename` VARCHAR(255) DEFAULT NULL,
    `file_size`         BIGINT UNSIGNED DEFAULT 0,
    `status`            ENUM('pending','processing','success','failed','expired') NOT NULL DEFAULT 'pending',
    `error_message`     TEXT DEFAULT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_history_user` (`user_id`),
    KEY `idx_history_tool` (`tool_name`),
    KEY `idx_history_status` (`status`),
    CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: uploaded_files
-- ---------------------------------------------------------------------
CREATE TABLE `uploaded_files` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`           INT UNSIGNED DEFAULT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename`   VARCHAR(255) NOT NULL,
    `file_path`         VARCHAR(500) NOT NULL,
    `file_type`         VARCHAR(100) DEFAULT NULL,
    `file_size`         BIGINT UNSIGNED DEFAULT 0,
    `expires_at`        DATETIME DEFAULT NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_files_user` (`user_id`),
    KEY `idx_files_expires` (`expires_at`),
    CONSTRAINT `fk_files_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: user_settings
-- ---------------------------------------------------------------------
CREATE TABLE `user_settings` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `theme`      ENUM('light','dark') NOT NULL DEFAULT 'light',
    `language`   VARCHAR(10) NOT NULL DEFAULT 'en',
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_settings_user` (`user_id`),
    CONSTRAINT `fk_settings_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: password_resets
-- ---------------------------------------------------------------------
CREATE TABLE `password_resets` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT UNSIGNED NOT NULL,
    `token_hash`  CHAR(64) NOT NULL,
    `expires_at`  DATETIME NOT NULL,
    `used`        TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_resets_token` (`token_hash`),
    KEY `idx_resets_user` (`user_id`),
    CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Table: contact_messages
-- ---------------------------------------------------------------------
CREATE TABLE `contact_messages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `subject`    VARCHAR(200) NOT NULL,
    `message`    TEXT NOT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_contact_read` (`is_read`),
    KEY `idx_contact_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Seed data: default admin user
-- ---------------------------------------------------------------------
-- Email:    admin@smartpdf.local
-- Password: Admin@12345   (hash below was created with password_hash())
INSERT INTO `users` (`full_name`, `email`, `password`, `role`, `is_active`)
VALUES (
    'Site Administrator',
    'admin@smartpdf.local',
    '$2y$10$j09vOj4CSZCqlCkqXKlCreu81k2oWnj71dgHRrimv087bB4Enfxyq',
    'admin',
    1
);

-- ---------------------------------------------------------------------
-- Seed data: settings row for admin user
-- ---------------------------------------------------------------------
INSERT INTO `user_settings` (`user_id`, `theme`, `language`)
SELECT `id`, 'light', 'en' FROM `users` WHERE `email` = 'admin@smartpdf.local';

-- ---------------------------------------------------------------------
-- Demo: a few sample conversion history rows (optional)
-- ---------------------------------------------------------------------
-- These help the admin dashboard look populated on first run.
-- Comment out if you want a clean database.
INSERT INTO `conversion_history`
    (`user_id`, `tool_name`, `tool_action`, `original_filename`,
     `converted_filename`, `file_size`, `status`, `created_at`)
VALUES
    (NULL, 'pdf_to_jpg', 'convert', 'sample-report.pdf', 'sample-report-page1.jpg',
     248320, 'success', NOW() - INTERVAL 2 HOUR),
    (NULL, 'merge_pdf', 'organize', 'contract-part1.pdf',
     'merged-contract.pdf', 562431, 'success', NOW() - INTERVAL 1 DAY),
    (NULL, 'compress_pdf', 'optimize', 'large-scan.pdf',
     'large-scan-compressed.pdf', 1892300, 'success', NOW() - INTERVAL 3 DAY);

-- =====================================================================
-- End of schema
-- =====================================================================
