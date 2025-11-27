-- Security Migration for Stock Management System
-- Run this SQL to add security features to existing database

-- Add super admin flag to admin table
ALTER TABLE `admin` 
ADD COLUMN `is_super_admin` TINYINT(1) DEFAULT 0 AFTER `email`;

-- Update password field to support hashed passwords (VARCHAR 255)
ALTER TABLE `admin` 
MODIFY COLUMN `mdp` VARCHAR(255) NOT NULL;

-- Set Abdelmalek as super admin
UPDATE `admin` 
SET `is_super_admin` = 1 
WHERE `email` = 'abdelmalek.abed321@gmail.com';

-- Add password field to client table if it doesn't exist
ALTER TABLE `client` 
ADD COLUMN `mdp` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`;

-- Create table for tracking login attempts (optional - can use sessions instead)
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `identifier` VARCHAR(255) NOT NULL,
  `attempts` INT(11) DEFAULT 0,
  `last_attempt` DATETIME DEFAULT NULL,
  `lockout_until` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
ALTER TABLE `admin` ADD INDEX `idx_email` (`email`(255));
ALTER TABLE `admin` ADD INDEX `idx_super_admin` (`is_super_admin`);
ALTER TABLE `client` ADD INDEX `idx_email` (`email`(255));

-- Note: Existing passwords need to be hashed
-- Run the password migration script (migrate_passwords.php) after this
