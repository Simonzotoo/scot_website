-- Adds a second hero-photo setting for the inner-page ".page-hero" banners
-- (Executives, Resources, Gallery, Contact, About, Announcements, and the
-- homepage's bottom CTA banner), separate from the homepage's main hero_image.
-- mysql -u root -p scotsa_platform < database/migrations/008_page_hero_image.sql
USE scotsa_platform;

INSERT INTO site_settings (setting_key, setting_value)
VALUES ('page_hero_image', 'hero/hero-orientation-team.jpg')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
