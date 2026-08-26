-- mysql -u root -p scotsa_platform < database/migrations/003_rate_limit_table.sql
USE scotsa_platform;

CREATE TABLE IF NOT EXISTS rate_limit_hits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bucket VARCHAR(80) NOT NULL,
    hit_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_rate_limit_bucket_time (bucket, hit_at)
) ENGINE=InnoDB;
