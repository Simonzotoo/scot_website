-- Adds real authentication to the existing student roster table so
-- students can self-register and log in, instead of it being an
-- admin-entered-only contact list.
-- mysql -u root -p scotsa_platform < database/migrations/004_student_accounts.sql
USE scotsa_platform;

ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL AFTER email;
