-- Makes the leadership/executive team, gallery, and a handful of
-- site-wide settings (hero image, contact links) admin-manageable
-- instead of hardcoded in index.php / executives.php / gallery.php.
-- Seeds these tables with exactly what's currently hardcoded so the
-- site looks identical right after migrating.
-- mysql -u root -p scotsa_platform < database/migrations/007_manageable_content.sql
USE scotsa_platform;

CREATE TABLE IF NOT EXISTS team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    roster ENUM('leadership', 'executive') NOT NULL,
    role VARCHAR(120) NOT NULL,
    name VARCHAR(120) NULL,
    portfolio VARCHAR(180) NULL,
    bio TEXT NULL,
    initials VARCHAR(4) NULL,
    gradient VARCHAR(120) NULL,
    photo_path VARCHAR(255) NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_team_roster (roster)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gallery_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(60) NOT NULL,
    caption VARCHAR(180) NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('active', 'archived') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_gallery_category (category),
    INDEX idx_gallery_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(80) PRIMARY KEY,
    setting_value TEXT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── Leadership roster (index.php "School Leadership" section) ──────────
INSERT INTO team_members (roster, role, name, photo_path, sort_order) VALUES
('leadership', 'Dean, School of Computing and Technology', 'Dr. Patrick Kudjo', 'executives/DR.PATRICK KUDJO DEAN.PNG', 1),
('leadership', 'Head of Department, Business Computing', 'Mr. Charles A. Babbage Jnr', 'executives/MR.CHARLES BABBAGE JNR ASIEDU  HOD BUSINESS COMPUTING .jpg', 2),
('leadership', 'Head of Department, Information Technology', 'Dr. Amankwa', NULL, 3),
('leadership', 'Head of Department, Mathematics Application', 'Dr. Leonard Kyei', NULL, 4),
('leadership', 'Patron', 'Mr. Edwin Agbah', 'executives/mr edwin agbah patron.jpg', 5);

-- ── Executive roster (executives.php "Executive Team" page) ────────────
INSERT INTO team_members (roster, role, name, portfolio, bio, initials, gradient, photo_path, sort_order) VALUES
('executive', 'President', 'Justice Simon Zotoo', 'Executive Lead & Chief Representative',
 'Chairs all SCOTSA executive meetings, represents the association at Wisconsin International University College (WIUC), ITAG, and allied institutional engagements, and leads the strategic vision and direction of the association for the academic year.',
 'JS', '135deg, #0A1F44 0%, #0d2a5c 100%', 'executives/justice-simon-zotoo-president-web.jpg', 1),

('executive', 'Vice President', 'Cyril Osei Akyeampong', 'Deputy Lead & Student Affairs',
 'Supports the President in all capacities, coordinates departmental initiatives, oversees student welfare programmes, and acts in the President''s stead when required by the association''s constitution.',
 'VP', '135deg, #0d2a5c 0%, #1a3a7a 100%', 'executives/CYRIL OSEI AKYEAMPONG VICE PRESIDENT.jpg', 2),

('executive', 'Secretary', NULL, 'Administration & Official Records',
 'Manages all official SCOTSA correspondence, keeps accurate minutes of every executive meeting, maintains the association''s records, and ensures the smooth administrative operations of SCOTSA throughout the academic year.',
 'SE', '135deg, #0A1F44 0%, #07172f 100%', NULL, 3),

('executive', 'Financial Secretary', NULL, 'Finance, Records & Accountability',
 'Assists the Treasurer in managing SCOTSA''s financial records, prepares financial documentation, and ensures accurate accounting and transparent reporting of all association funds to the student body.',
 'FS', '135deg, #07172f 0%, #0A1F44 100%', NULL, 4),

('executive', 'Organising Secretary', 'Dennis Nana Yaw Kesse Boateng', 'Events, Programmes & Logistics',
 'Plans and coordinates all SCOTSA events, academic programmes, and departmental activities, from SRC Week and orientation to end-of-semester celebrations, seminars, and inter-association engagements.',
 'OS', '135deg, #0d2a5c 0%, #0A1F44 100%', 'executives/Dennis Nana Yaw Kesse Boateng Organizing Secretary.jpg', 5),

('executive', 'Communications Director', 'John Kpakpo Boabeng', 'Communications, Media & Branding',
 'Manages SCOTSA''s public image, social media channels, and press communications, ensuring consistent, professional representation of the association across all platforms and to the wider WIUC student community.',
 'JK', '135deg, #0A1F44 0%, #0d2a5c 100%', 'executives/Communications Director.png', 6),

('executive', 'Treasurer', NULL, 'Finance, Budgets & Accountability',
 'Oversees all of SCOTSA''s financial affairs, preparing and managing the association''s budget, maintaining financial accounts, accounting for all funds received and disbursed, and ensuring transparent financial reporting to the student body.',
 'TR', '135deg, #07172f 0%, #0d2a5c 100%', NULL, 7),

('executive', 'Women''s Commissioner', NULL, 'Gender Equity & Inclusive Programmes',
 'Leads SCOTSA''s gender equity agenda at WIUC, drives inclusive programming, and advocates for the welfare, rights, and empowerment of women in computing and technology, ensuring every female student has an equal voice.',
 'WC', '135deg, #0d2a5c 0%, #07172f 100%', NULL, 8);

-- ── Gallery items ────────────────────────────────────────────────────
INSERT INTO gallery_items (category, caption, photo_path, sort_order) VALUES
('events',      'SCOTSA Annual General Assembly',            'gallery/IMG_4617.JPEG', 1),
('events',      'Department Welcome Ceremony',                'gallery/IMG_4620.JPEG', 2),
('events',      'SCOTSA Community Gathering',                 'gallery/IMG_4637.JPEG', 3),
('events',      'End-of-Semester Celebration',                'gallery/IMG_4742.JPEG', 4),
('events',      'SRC Week Opening Ceremony',                  'gallery/IMG_4625.JPEG', 5),
('events',      'SRC Week Cultural Showcase',                 'gallery/IMG_4849.JPEG', 6),
('seminars',    'Academic Awareness Seminar',                 'gallery/IMG_4833.JPEG', 7),
('seminars',    'Department Workshop',                        'gallery/IMG_4845.JPEG', 8),
('tech',        'Technology Exhibition 2024',                 'gallery/IMG_4632.JPEG', 9),
('events',      'SCOTSA Executive Team 2024/2025',            'gallery/IMG_4632 2.JPEG', 10),
('orientation', 'SCOTSA Orientation Group Photo',              'gallery/Orientation_71.JPEG', 11),
('orientation', 'Connect, Create, Collaborate Campaign',       'gallery/Orientation_46.JPEG', 12),
('orientation', 'Student Engaged During Orientation',          'gallery/Orientation_12.JPEG', 13),
('orientation', 'Faculty Member Addressing Students',          'gallery/Orientation_47.JPEG', 14),
('orientation', 'Open Floor Discussion at Orientation',        'gallery/Orientation_48.JPEG', 15),
('orientation', 'Faculty Member Responding to Questions',      'gallery/Orientation_49.JPEG', 16),
('orientation', 'Student Sharing Remarks at Orientation',      'gallery/Orientation_50.JPEG', 17),
('orientation', 'Executive Member Addressing New Students',    'gallery/Orientation_51.JPEG', 18),
('orientation', 'SCOTSA Executives Engaging Students',         'gallery/Orientation_52.JPEG', 19),
('orientation', 'Student Addressing the Orientation Audience', 'gallery/Orientation_63.JPEG', 20),
('orientation', 'SCOTSA Executive Speaking at Orientation',    'gallery/Orientation_64.JPEG', 21),
('orientation', 'Facilitator Leading a Training Session',      'gallery/Orientation_65.JPEG', 22),
('orientation', 'Student Attentive During the Programme',      'gallery/Orientation_66.JPEG', 23),
('orientation', 'Connect, Create, Collaborate Campaign',       'gallery/Orientation_68.JPEG', 24),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7216.jpg', 25),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7218.jpg', 26),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7219.jpg', 27),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7220.jpg', 28),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7221.jpg', 29),
('orientation', 'SCOTSA Team at the Orientation Session',      'gallery/IMG_7222.jpg', 30);

-- ── Site settings ────────────────────────────────────────────────────
INSERT INTO site_settings (setting_key, setting_value) VALUES
('hero_image',        'hero/hero-orientation-team.jpg'),
('contact_email',     'scotsawiuc@gmail.com'),
('whatsapp_url',      'https://chat.whatsapp.com/Cn2b43LoXOH1WGCg0uBaNR?s=cl&p=i&mlu=4'),
('social_facebook',   'https://www.facebook.com/share/1Hrd1Z7gii/?mibextid=wwXIfr'),
('social_instagram',  'https://www.instagram.com/scotsa_wiuc?igsi=MWxqZ2RtdXQwaGt1Yw=='),
('social_tiktok',     'https://www.tiktok.com/@scotsa_wiuc?_r=1&_t=ZS-99Dd4S31JyO'),
('social_twitter',    'https://x.com/scotsawiuc?s=21');
