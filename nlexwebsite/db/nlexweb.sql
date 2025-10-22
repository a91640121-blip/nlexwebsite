
CREATE DATABASE IF NOT EXISTS `nlexweb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `nlexweb`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`full_name`, `email`, `password_hash`, `created_at`) VALUES
('Admin User', 'admin@nlex.local', '$2y$10$u/0pQqjR0z8mKQzq2gQqJuyY3j2nQf1T1q0cG7K6Y1xq2uZf9KQ9e', NOW());

-- Add role column to users (idempotent)
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `role` VARCHAR(50) NOT NULL DEFAULT 'user';

-- Create a new static admin account (if not exists)
INSERT INTO `users` (`full_name`, `email`, `password_hash`, `created_at`, `role`)
SELECT 'Admin', 'admin@gmail.com', '$2y$10$RrSYpuvOiuJlATjEOJkDlO1BrCjQBgxZztesFGnprtB5LxjMC0Vga', NOW(), 'admin'
WHERE NOT EXISTS (SELECT 1 FROM users WHERE email = 'admin@gmail.com');

CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(191) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `messages` 
  ADD COLUMN IF NOT EXISTS `user_id` INT UNSIGNED NULL AFTER `id`,
  ADD INDEX (`user_id`);

-- Add columns to store admin replies to messages
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `reply` TEXT NULL AFTER `message`,
  ADD COLUMN IF NOT EXISTS `replied_at` DATETIME NULL AFTER `reply`,
  ADD COLUMN IF NOT EXISTS `replied_by` INT UNSIGNED NULL AFTER `replied_at`,
  ADD COLUMN IF NOT EXISTS `status` ENUM('open','replied','closed') NOT NULL DEFAULT 'open' AFTER `replied_by`;

-- Add columns to store user's reply back to admin
ALTER TABLE `messages`
  ADD COLUMN IF NOT EXISTS `user_reply` TEXT NULL AFTER `reply`,
  ADD COLUMN IF NOT EXISTS `user_replied_at` DATETIME NULL AFTER `user_reply`;
