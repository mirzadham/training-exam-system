-- ============================================
-- Migration: Add Admin Authentication Fields
-- ============================================
-- Adds email-based registration, email verification,
-- and password reset support to the admins table.
--
-- Run this migration on an existing database.
-- ============================================

USE `training_exam_system`;

-- Add new columns to admins table
ALTER TABLE `admins`
    ADD COLUMN `email` VARCHAR(255) NULL AFTER `username`,
    ADD COLUMN `email_verified_at` TIMESTAMP NULL DEFAULT NULL AFTER `email`,
    ADD COLUMN `verification_token` VARCHAR(64) NULL DEFAULT NULL AFTER `email_verified_at`,
    ADD COLUMN `reset_token` VARCHAR(64) NULL DEFAULT NULL AFTER `verification_token`,
    ADD COLUMN `reset_token_expires_at` TIMESTAMP NULL DEFAULT NULL AFTER `reset_token`;

-- Add unique index on email (allow NULL for legacy accounts)
ALTER TABLE `admins`
    ADD UNIQUE INDEX `admins_email_unique` (`email`);

-- Update existing admin accounts: set email_verified_at so they can still log in
-- (They won't have an email, but they can still use username-based login as fallback)
UPDATE `admins`
    SET `email_verified_at` = NOW()
    WHERE `email_verified_at` IS NULL;
