-- Adds a profile photo to both admin and student accounts.
-- mysql -u root -p scotsa_platform < database/migrations/005_profile_avatars.sql
USE scotsa_platform;

ALTER TABLE admins ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER password_hash;
ALTER TABLE users  ADD COLUMN IF NOT EXISTS avatar_path VARCHAR(255) NULL AFTER password_hash;
