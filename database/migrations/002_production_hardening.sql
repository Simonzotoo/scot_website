-- Production-hardening migration: run after schema.sql.
-- mysql -u root -p scotsa_platform < database/migrations/002_production_hardening.sql
USE scotsa_platform;

-- ── Login throttling ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS login_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(64) NOT NULL,
    success TINYINT(1) NOT NULL DEFAULT 0,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_attempts_identifier_time (identifier, attempted_at)
) ENGINE=InnoDB;

-- ── Admin audit log ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    entity_type VARCHAR(40) NOT NULL,
    entity_id INT UNSIGNED NULL,
    detail VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_log_created (created_at),
    CONSTRAINT fk_audit_log_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── Search performance ────────────────────────────────────────
ALTER TABLE past_questions ADD INDEX IF NOT EXISTS idx_past_questions_title (title);
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_courses_code (code);
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_courses_title (title);

-- ── Download throttling lookups ───────────────────────────────
ALTER TABLE downloads ADD INDEX IF NOT EXISTS idx_downloads_ip_time (ip_address, downloaded_at);
