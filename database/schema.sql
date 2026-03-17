-- ============================================
-- Training Exam System — Database Schema
-- ============================================
-- Run this SQL in HeidiSQL or phpMyAdmin to create the database and tables.

CREATE DATABASE IF NOT EXISTS `training_exam_system`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `training_exam_system`;

-- ============================================
-- Organizations
-- ============================================
CREATE TABLE IF NOT EXISTS `organizations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Admins
-- ============================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL COMMENT 'Hashed with password_hash()',
    `full_name` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Question Banks
-- ============================================
CREATE TABLE IF NOT EXISTS `question_banks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `organization_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `duration_minutes` INT NULL DEFAULT NULL COMMENT 'If NULL, calculated as 1 min per question',
    `is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only one bank per org should be active',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Questions
-- ============================================
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `question_bank_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `option_a` VARCHAR(500) NOT NULL,
    `option_b` VARCHAR(500) NOT NULL,
    `option_c` VARCHAR(500) NOT NULL,
    `option_d` VARCHAR(500) NOT NULL,
    `correct_option` ENUM('A','B','C','D') NOT NULL,
    `explanation` TEXT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`question_bank_id`) REFERENCES `question_banks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Participants
-- ============================================
CREATE TABLE IF NOT EXISTS `participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(255) NOT NULL,
    `ic_number` VARCHAR(20) NOT NULL UNIQUE,
    `organization_id` INT NOT NULL,

    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Exam Attempts
-- ============================================
CREATE TABLE IF NOT EXISTS `exam_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `participant_id` INT NOT NULL,
    `question_bank_id` INT NOT NULL,
    `total_questions` INT NOT NULL DEFAULT 0,
    `correct_count` INT NOT NULL DEFAULT 0,
    `wrong_count` INT NOT NULL DEFAULT 0,
    `unanswered_count` INT NOT NULL DEFAULT 0,
    `score_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `result` ENUM('fail','pass','excellent') NULL DEFAULT NULL,
    `status` ENUM('in_progress','submitted','time_up') NOT NULL DEFAULT 'in_progress',
    `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `submitted_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`participant_id`) REFERENCES `participants`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_bank_id`) REFERENCES `question_banks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================
-- Attempt Answers
-- ============================================
CREATE TABLE IF NOT EXISTS `attempt_answers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `attempt_id` INT NOT NULL,
    `question_id` INT NOT NULL,
    `selected_option` ENUM('A','B','C','D') NULL DEFAULT NULL,
    `is_correct` TINYINT(1) NOT NULL DEFAULT 0,
    `question_order` INT NOT NULL COMMENT 'Randomized order for this participant',
    FOREIGN KEY (`attempt_id`) REFERENCES `exam_attempts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`question_id`) REFERENCES `questions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
