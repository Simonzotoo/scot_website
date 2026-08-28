CREATE DATABASE IF NOT EXISTS scotsa_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE scotsa_platform;

CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(140) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    program_id INT UNSIGNED NULL,
    level_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE programs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE levels (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL UNIQUE,
    sort_order SMALLINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE semesters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(40) NOT NULL UNIQUE,
    sort_order TINYINT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE courses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    level_id INT UNSIGNED NOT NULL,
    semester_id INT UNSIGNED NOT NULL,
    code VARCHAR(30) NOT NULL,
    title VARCHAR(180) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course_context (program_id, level_id, semester_id, code),
    INDEX idx_courses_lookup (program_id, level_id, semester_id),
    CONSTRAINT fk_courses_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
    CONSTRAINT fk_courses_level FOREIGN KEY (level_id) REFERENCES levels(id),
    CONSTRAINT fk_courses_semester FOREIGN KEY (semester_id) REFERENCES semesters(id)
) ENGINE=InnoDB;

CREATE TABLE past_questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id INT UNSIGNED NOT NULL,
    uploaded_by INT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    resource_type ENUM('past_question', 'midsem', 'end_sem', 'lecture_note') NOT NULL,
    academic_year VARCHAR(20) NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(120) NOT NULL DEFAULT 'application/pdf',
    file_size INT UNSIGNED NOT NULL,
    download_count INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_past_questions_type (resource_type),
    INDEX idx_past_questions_status (status),
    CONSTRAINT fk_pq_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    CONSTRAINT fk_pq_admin FOREIGN KEY (uploaded_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE announcements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NULL,
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_announcements_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE downloads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    past_question_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_downloads_resource (past_question_id),
    CONSTRAINT fk_downloads_pq FOREIGN KEY (past_question_id) REFERENCES past_questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_downloads_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE users
    ADD CONSTRAINT fk_users_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_users_level FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE SET NULL;

INSERT INTO admins (name, email, password_hash, role)
VALUES ('SCOTSA Super Admin', 'admin@scotsa.edu', '$2y$10$qc99.Ww5fTiu01uEZYybQeI51cOw0lG6v0bwI4FNjGzheTMUst.uu', 'super_admin');
-- Default password: Admin@12345. Change it after first login.

INSERT INTO programs (name, slug, description) VALUES
('BSc Information Technology', 'bsc-information-technology', 'Systems, networking, software tools, and applied computing for real-world IT roles.'),
('BSc Computing and Actuarial Science', 'bsc-computing-and-actuarial-science', 'Combines computing fundamentals with actuarial mathematics and statistics for careers in insurance, finance, and risk analysis.'),
('BSc Cybersecurity', 'bsc-cybersecurity', 'Security operations, digital forensics, network defence, and risk management.'),
('BSc Artificial Intelligence and Robotics', 'bsc-artificial-intelligence-and-robotics', 'Machine learning, intelligent systems, and robotics engineering at the frontier of computing.'),
('BSc Management with Information Technology', 'bsc-management-with-information-technology', 'Blends business management with IT skills for technology-driven organisational leadership.'),
('Diploma in Information Technology', 'diploma-in-information-technology', 'A foundational two-year diploma in practical information technology skills.');

INSERT INTO levels (name, sort_order) VALUES
('Level 100', 100),
('Level 200', 200),
('Level 300', 300),
('Level 400', 400);

INSERT INTO semesters (name, sort_order) VALUES
('Lower', 1),
('Upper', 2);

INSERT INTO courses (program_id, level_id, semester_id, code, title) VALUES
(1, 1, 1, 'ITC101', 'Introduction to Information Technology'),
(1, 2, 1, 'ITC201', 'Database Systems'),
(2, 1, 1, 'CSC101', 'Introduction to Programming'),
(2, 2, 2, 'CSC202', 'Data Structures and Algorithms'),
(3, 2, 1, 'CYB201', 'Network Security Fundamentals'),
(4, 2, 2, 'DSC202', 'Applied Statistics for Data Science');

INSERT INTO announcements (admin_id, title, body, status, published_at) VALUES
(1, 'Welcome to the SCOTSA platform', 'Students can now browse announcements and academic resources from one central portal.', 'published', NOW()),
(1, 'Resource uploads are open', 'Course representatives can submit verified PDFs to the academic office for upload.', 'published', NOW());
