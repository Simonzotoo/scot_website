-- Adds structured exam month/year to past_questions so students can filter
-- by exact period (not just a free-text "academic_year" string that was
-- never searchable), and widens mime_type's practical use now that uploads
-- accept more than just PDF.
-- mysql -u root -p scotsa_platform < database/migrations/006_exam_period_and_file_types.sql
USE scotsa_platform;

ALTER TABLE past_questions
    ADD COLUMN IF NOT EXISTS exam_month TINYINT UNSIGNED NULL AFTER academic_year,
    ADD COLUMN IF NOT EXISTS exam_year  SMALLINT UNSIGNED NULL AFTER exam_month;

ALTER TABLE past_questions
    ADD INDEX IF NOT EXISTS idx_past_questions_period (exam_year, exam_month);

-- Match how the department actually refers to semesters.
UPDATE semesters SET name = 'Lower Semester' WHERE name = 'Semester 1';
UPDATE semesters SET name = 'Upper Semester' WHERE name = 'Semester 2';
