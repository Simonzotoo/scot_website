-- Adds admin-dashboard support for every section of the current SCOT
-- one-page site. Existing tables (admins, hero_slides, gallery_items,
-- team_members, programs, courses, site_settings) are extended in place;
-- brand-new content types (facilities, videos, faculty sub-lists, student
-- projects, blog) get their own tables. Row data is NOT touched here —
-- see database/seed_from_current_content.php for the one-time reseed.

-- hero_slides: support the per-slide custom crop position used by the
-- "Robotics & AI" slide (background-position: center 70%).
ALTER TABLE hero_slides
    ADD COLUMN photo_position VARCHAR(40) NULL AFTER photo_path;

-- team_members: roster already has a 'faculty' value from the original
-- build (unused since); repurposed here as the "Lecturers" grid, while
-- 'leadership' covers the Dean + Heads grid — matches the two grids the
-- public page already renders. New columns for the profile page's other
-- tabs; courses_taught/publications/education are one-row-per-line lists
-- (see child tables below), research_interest is a single paragraph.
ALTER TABLE team_members
    ADD COLUMN email VARCHAR(180) NULL AFTER name,
    ADD COLUMN research_interest TEXT NULL AFTER bio;

CREATE TABLE faculty_courses_taught (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    text_value VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_fct_member FOREIGN KEY (member_id) REFERENCES team_members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE faculty_publications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    text_value TEXT NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_fp_member FOREIGN KEY (member_id) REFERENCES team_members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE faculty_education (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    member_id INT UNSIGNED NOT NULL,
    text_value VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_fe_member FOREIGN KEY (member_id) REFERENCES team_members(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- programs: 'tag' drives the Programmes filter tabs (BSc/MSc/Diploma/Short
-- Courses); 'featured' marks the one bento-featured card (BSc IT); 'note'
-- is the small "course structure currently published through..." caption.
-- 'name' was unique platform-wide under the old 6-programme catalogue;
-- now that BSc/Diploma/MSc share names like "Information Technology",
-- only 'slug' (already distinct per programme) stays unique.
ALTER TABLE programs
    DROP INDEX name,
    ADD COLUMN tag VARCHAR(20) NOT NULL DEFAULT 'BSc' AFTER name,
    ADD COLUMN note VARCHAR(255) NULL AFTER description,
    ADD COLUMN featured TINYINT(1) NOT NULL DEFAULT 0 AFTER note,
    ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER featured;

-- courses: MSc programmes group into Core/Elective rather than
-- Level/Semester, so level_id/semester_id become optional and a
-- group_label carries the MSc grouping instead. Short Courses have no
-- rows here at all — they're just programs.tag = 'Short Courses'.
ALTER TABLE courses
    MODIFY COLUMN level_id INT UNSIGNED NULL,
    MODIFY COLUMN semester_id INT UNSIGNED NULL,
    ADD COLUMN group_label ENUM('core','elective') NULL AFTER semester_id,
    ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0 AFTER group_label;

CREATE TABLE facilities (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    photo_path VARCHAR(255) NOT NULL,
    caption VARCHAR(180) NOT NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE videos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    file_path VARCHAR(255) NOT NULL,
    poster_path VARCHAR(255) NULL,
    title VARCHAR(180) NOT NULL,
    caption VARCHAR(400) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE student_projects (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    student_name VARCHAR(120) NOT NULL,
    programme_label VARCHAR(180) NULL,
    supervisor VARCHAR(120) NULL,
    co_supervisor VARCHAR(120) NULL,
    video_path VARCHAR(255) NULL,
    poster_path VARCHAR(255) NULL,
    summary TEXT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE student_project_stack (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id INT UNSIGNED NOT NULL,
    tech_text VARCHAR(80) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_pstack_project FOREIGN KEY (project_id) REFERENCES student_projects(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE blog_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    post_date DATE NOT NULL,
    excerpt TEXT NULL,
    video_path VARCHAR(255) NULL,
    poster_path VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('draft','published') NOT NULL DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE blog_post_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    caption VARCHAR(180) NULL,
    featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_bpp_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE gallery_categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
