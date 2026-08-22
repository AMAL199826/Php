-- =====================================================
-- Legacy CRM — Schema Changes & Seed Data
-- =====================================================
-- Run this against your `legacy_crm` database if setting up fresh.
-- (If you already applied these changes manually via phpMyAdmin,
--  you don't need to re-run this — it's here for documentation
--  and for graders/reviewers setting up from scratch.)

-- ---------------------------------------------------
-- 1. USERS TABLE (roles + team assignment for RBAC)
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `username` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','manager','sales') NOT NULL DEFAULT 'sales',
  `team_id` INT(11) UNSIGNED NULL DEFAULT NULL,
  `created_at` DATETIME NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------
-- 2. CUSTOMERS TABLE — add assigned_to column (RBAC ownership)
-- ---------------------------------------------------
ALTER TABLE `customers`
  ADD COLUMN `assigned_to` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `status`;

ALTER TABLE `customers`
  ADD CONSTRAINT `customers_assigned_to_foreign`
  FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------------------------------------------------
-- 3. SEED DATA — test users, one per role
-- ---------------------------------------------------
-- NOTE: passwords are stored in plain text in this legacy system
-- (matches the existing Auth::authenticate() comparison logic).
-- In a production system these should be hashed with password_hash().

INSERT INTO `users` (`name`, `username`, `email`, `password`, `role`, `team_id`, `created_at`, `updated_at`)
VALUES
  ('Admin User',   'admin',   'admin@crm.test',   'admin123', 'admin',   NULL, NOW(), NOW()),
  ('Manager User', 'manager', 'manager@crm.test', '12345',    'manager', 1,    NOW(), NOW()),
  ('Sales User',   'sales',   'sales@crm.test',   'sales123', 'sales',   1,    NOW(), NOW());

-- ---------------------------------------------------
-- 4. OPTIONAL — assign some existing customers to test users
--    so RBAC filtering has something to actually show per role.
--    Adjust the user IDs below to match your actual seeded IDs.
-- ---------------------------------------------------
-- UPDATE customers SET assigned_to = 3 WHERE id % 2 = 0; -- sales user
-- UPDATE customers SET assigned_to = 2 WHERE id % 2 = 1; -- manager's team