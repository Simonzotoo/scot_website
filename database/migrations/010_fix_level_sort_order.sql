-- levels.sort_order was TINYINT UNSIGNED (max 255), so Level 300 and
-- Level 400 both silently clamped to 255 on insert, making their sort order
-- ambiguous/tied. Nothing currently ORDER BYs this column (pages use
-- ORDER BY id instead), so there's no visible symptom yet, but any future
-- query that sorts by sort_order would show Level 300/400 in an unstable,
-- arbitrary order relative to each other.
-- mysql -u root -p scotsa_platform < database/migrations/010_fix_level_sort_order.sql
USE scotsa_platform;

ALTER TABLE levels MODIFY COLUMN sort_order SMALLINT UNSIGNED NOT NULL;

UPDATE levels SET sort_order = 100 WHERE name = 'Level 100';
UPDATE levels SET sort_order = 200 WHERE name = 'Level 200';
UPDATE levels SET sort_order = 300 WHERE name = 'Level 300';
UPDATE levels SET sort_order = 400 WHERE name = 'Level 400';
