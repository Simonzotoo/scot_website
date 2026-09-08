-- Faculty Officers — the School's administrative office staff (Faculty
-- Officer + Deputy), shown as their own group under the Lecturers on the
-- public Faculty page. They are not academics, so they carry only a
-- name / role / photo / email — no bio, courses, publications, or
-- education (those child tables stay empty for them).
--
-- Adds an 'officer' value to the team_members.roster enum and seeds the
-- two current officers. Safe to re-run: the officer rows are cleared and
-- re-inserted (nothing references them).

ALTER TABLE team_members
    MODIFY COLUMN roster ENUM('leadership', 'executive', 'faculty', 'officer') NOT NULL;

DELETE FROM team_members WHERE roster = 'officer';

INSERT INTO team_members (roster, role, name, email, photo_path, sort_order) VALUES
('officer', 'Faculty Officer',        'Madam Edith', NULL,                           NULL,                                      0),
('officer', 'Deputy Faculty Officer', 'Provo Glabu', 'provo.glabu@wiuc-ghana.edu.gh', 'faculty/faculty-officer-provo-glabu.jpg', 1);
