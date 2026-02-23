-- Migration: Add firebase_uid field to users table
-- Run this SQL in your database to add support for Firebase authentication

ALTER TABLE `users` 
ADD COLUMN `firebase_uid` VARCHAR(128) NULL AFTER `is_verified`,
ADD UNIQUE KEY `uk_users_firebase_uid` (`firebase_uid`);

-- Add index for faster lookups
ALTER TABLE `users` 
ADD INDEX `idx_users_firebase_uid` (`firebase_uid`);

