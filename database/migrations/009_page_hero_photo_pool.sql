-- Replaces the single page_hero_image setting with a small managed pool of
-- photos, so each inner page (Executives, Resources, Gallery, Contact,
-- About, Announcements) can show a different banner instead of repeating
-- the same one everywhere.
-- mysql -u root -p scotsa_platform < database/migrations/009_page_hero_photo_pool.sql
USE scotsa_platform;

CREATE TABLE IF NOT EXISTS page_hero_photos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    photo_path VARCHAR(255) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Orientation_65 and Orientation_46 were tried and dropped: single-person
-- shots don't read well as a generic page banner (one showed nothing but
-- blank whiteboard above the subject's head even with the 25%-biased crop
-- in styles.css .page-hero; the other looked odd repurposed as a banner for
-- an unrelated page). Every photo below is a group/event shot, checked
-- against a simulated crop at the real banner aspect ratio before adding.
INSERT INTO page_hero_photos (photo_path, sort_order)
SELECT * FROM (SELECT 'hero/hero-orientation-team.jpg' AS photo_path, 0 AS sort_order
    UNION ALL SELECT 'gallery/Orientation_71.JPEG', 1
    UNION ALL SELECT 'gallery/IMG_4632 2.JPEG', 3
    UNION ALL SELECT 'gallery/IMG_4625.JPEG', 4
    UNION ALL SELECT 'gallery/IMG_4617.JPEG', 5
    UNION ALL SELECT 'gallery/IMG_4742.JPEG', 6
    UNION ALL SELECT 'gallery/Orientation_51.JPEG', 7
    UNION ALL SELECT 'gallery/IMG_4845.JPEG', 8
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM page_hero_photos);

DELETE FROM site_settings WHERE setting_key = 'page_hero_image';
