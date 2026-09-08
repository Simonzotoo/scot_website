-- ============================================================================
--  SCOT — production DB content sync (2026-09-08)
--
--  Brings the LIVE (Hostinger) database in line with three content changes
--  that are already deployed in code and files but not in the database:
--
--    1. Emmanuel Junior Tapany's education entries (institutions, no years)
--    2. The three MSc programmes' course structures (official titles + codes)
--    3. The three MSc course-structure PDFs on the Downloads page
--
--  HOW TO RUN
--    Hostinger  ->  hPanel  ->  Databases  ->  phpMyAdmin
--    Pick the SCOT database in the left sidebar, open the "SQL" tab,
--    paste this whole file, and click "Go".
--
--  This script is keyed by names / slugs / file paths, not by numeric IDs,
--  so it does not matter that the live IDs differ from local. It is safe to
--  run more than once — each section clears the same rows before re-inserting.
--
--  NOTE (section 2): deleting the MSc `courses` rows will cascade to any
--  `past_questions` attached to those courses. There are normally none for
--  the MSc programmes. If you have uploaded MSc past questions, stop and ask
--  before running section 2.
-- ============================================================================

START TRANSACTION;

-- ── 1. Faculty education — Emmanuel Junior Tapany ────────────────────────────

DELETE fe
  FROM faculty_education fe
  JOIN team_members tm ON tm.id = fe.member_id
 WHERE tm.name = 'Emmanuel Junior Tapany';

INSERT INTO faculty_education (member_id, text_value, sort_order)
SELECT tm.id, v.text_value, v.sort_order
  FROM team_members tm
  JOIN (
              SELECT 'PhD Computer Science, Ghana Communication Technology University (Ongoing)' AS text_value, 0 AS sort_order
    UNION ALL SELECT 'MPhil Computer Science, University of Energy & Natural Resources', 1
    UNION ALL SELECT 'BSc Computer Science, University of Energy & Natural Resources', 2
       ) v
 WHERE tm.name = 'Emmanuel Junior Tapany';


-- ── 2. MSc course structures ────────────────────────────────────────────────
--     Programmes matched by slug: msccsdf, mscbc, mscit

DELETE c
  FROM courses c
  JOIN programs p ON p.id = c.program_id
 WHERE p.tag = 'MSc'
   AND p.slug IN ('msccsdf', 'mscbc', 'mscit');

INSERT INTO courses (program_id, level_id, semester_id, group_label, sort_order, code, title)
SELECT p.id, NULL, NULL, v.group_label, v.sort_order, v.code, v.title
  FROM programs p
  JOIN (
    -- MSc Information Technology --------------------------------------------
              SELECT 'mscit' AS slug, 'core'     AS group_label,  0 AS sort_order, 'WMIT601' AS code, 'Research Methods and Professional Practice' AS title
    UNION ALL SELECT 'mscit', 'core',      1, 'WMIT603', 'Advanced Programming Concepts with Java'
    UNION ALL SELECT 'mscit', 'core',      2, 'WMIT605', 'Computer Networking Theory, Technologies & Protocols'
    UNION ALL SELECT 'mscit', 'core',      3, 'WMIT607', 'Artificial Intelligence and Machine Learning'
    UNION ALL SELECT 'mscit', 'core',      4, 'WMBC602', 'IT Project Management'
    UNION ALL SELECT 'mscit', 'core',      5, 'WMIT600', 'Project Work'
    UNION ALL SELECT 'mscit', 'core',      6, 'WMIT602', 'Operating Systems Theory and Administration'
    UNION ALL SELECT 'mscit', 'core',      7, 'WMIT604', 'Advanced Computer Networks'
    UNION ALL SELECT 'mscit', 'core',      8, 'WMIT606', 'Management Information Systems'
    UNION ALL SELECT 'mscit', 'core',      9, 'WMIT608', 'Data Structures and Complexities of Algorithms'
    UNION ALL SELECT 'mscit', 'core',     10, 'WMIT610', 'Seminar'
    UNION ALL SELECT 'mscit', 'elective', 11, 'WMIT609', 'Advanced Database Management Systems'
    UNION ALL SELECT 'mscit', 'elective', 12, 'WMIT613', 'Ethical Hacking, Data Recovery and Penetration Testing'
    UNION ALL SELECT 'mscit', 'elective', 13, 'WMCS604', 'Cyber Security and Forensics'
    UNION ALL SELECT 'mscit', 'elective', 14, 'WMIT616', 'Advanced Software Engineering'

    -- MSc Business Computing ----------------------------------------------
    UNION ALL SELECT 'mscbc', 'core',      0, 'WMBC605', 'Business Process Analysis and Design'
    UNION ALL SELECT 'mscbc', 'core',      1, 'WMIT601', 'Research Methods and Professional Practice'
    UNION ALL SELECT 'mscbc', 'core',      2, 'WMIT605', 'Computer Networking Theory, Technologies & Protocols'
    UNION ALL SELECT 'mscbc', 'core',      3, 'WMIT607', 'Artificial Intelligence and Machine Learning'
    UNION ALL SELECT 'mscbc', 'core',      4, 'WMBC602', 'IT Project Management'
    UNION ALL SELECT 'mscbc', 'core',      5, 'WMBC606', 'Supply Chain Integration Technologies'
    UNION ALL SELECT 'mscbc', 'core',      6, 'WMBC608', 'Technology Entrepreneurship and Innovations'
    UNION ALL SELECT 'mscbc', 'core',      7, 'WMBC612', 'Information Security Management'
    UNION ALL SELECT 'mscbc', 'core',      8, 'WMIT600', 'Project Work'
    UNION ALL SELECT 'mscbc', 'core',      9, 'WMIT610', 'Seminar'
    UNION ALL SELECT 'mscbc', 'elective', 10, 'WMBC613', 'Big Data Analytics'
    UNION ALL SELECT 'mscbc', 'elective', 11, 'WMIT609', 'Advanced Database Management Systems'
    UNION ALL SELECT 'mscbc', 'elective', 12, 'WMBC614', 'Business Intelligence'
    UNION ALL SELECT 'mscbc', 'elective', 13, 'WMIT606', 'Management Information Systems'

    -- MSc Cybersecurity and Digital Forensics -----------------------------
    UNION ALL SELECT 'msccsdf', 'core',      0, 'WMCS603', 'Interactive Programming with Python'
    UNION ALL SELECT 'msccsdf', 'core',      1, 'WMIT601', 'Research Methods and Professional Practice'
    UNION ALL SELECT 'msccsdf', 'core',      2, 'WMIT605', 'Computer Networking Theory, Technologies & Protocols'
    UNION ALL SELECT 'msccsdf', 'core',      3, 'WMIT607', 'Artificial Intelligence and Machine Learning'
    UNION ALL SELECT 'msccsdf', 'core',      4, 'WMCS602', 'Operating Systems Theory and Applications'
    UNION ALL SELECT 'msccsdf', 'core',      5, 'WMCS604', 'Cyber Security and Forensics'
    UNION ALL SELECT 'msccsdf', 'core',      6, 'WMCS608', 'Computer Networks and Systems Security'
    UNION ALL SELECT 'msccsdf', 'core',      7, 'WMIT600', 'Project Work'
    UNION ALL SELECT 'msccsdf', 'core',      8, 'WMIT608', 'Data Structures and Complexities of Algorithms'
    UNION ALL SELECT 'msccsdf', 'core',      9, 'WMIT610', 'Seminar'
    UNION ALL SELECT 'msccsdf', 'elective', 10, 'WMCS609', 'Data Recovery and Digital Forensics Analysis'
    UNION ALL SELECT 'msccsdf', 'elective', 11, 'WMCS611', 'Ethical Hacking and Penetration Testing'
    UNION ALL SELECT 'msccsdf', 'elective', 12, 'WMCS612', 'Cryptography Theory & Applications'
    UNION ALL SELECT 'msccsdf', 'elective', 13, 'WMCS616', 'Mobile Systems Forensics'
       ) v ON v.slug = p.slug
 WHERE p.tag = 'MSc';


-- ── 3. MSc course-structure PDFs on the Downloads page ──────────────────────
--     Files ship with the deploy at assets/documents/ and .../previews/

DELETE FROM documents
 WHERE file_path IN (
    'documents/msc-it-course-structure.pdf',
    'documents/msc-business-computing-course-structure.pdf',
    'documents/msc-cybersecurity-course-structure.pdf'
 );

SELECT COALESCE(MAX(sort_order), -1) INTO @doc_sort FROM documents;

INSERT INTO documents (title, audience, file_path, preview_path, file_size, sort_order, status) VALUES
('MSc Information Technology — Course Structure',            'MSc Students', 'documents/msc-it-course-structure.pdf',                  'documents/previews/msc-it-course-structure.jpg',                  80002, @doc_sort + 1, 'active'),
('MSc Business Computing — Course Structure',                'MSc Students', 'documents/msc-business-computing-course-structure.pdf',  'documents/previews/msc-business-computing-course-structure.jpg',  78483, @doc_sort + 2, 'active'),
('MSc Cybersecurity & Digital Forensics — Course Structure', 'MSc Students', 'documents/msc-cybersecurity-course-structure.pdf',       'documents/previews/msc-cybersecurity-course-structure.jpg',       78809, @doc_sort + 3, 'active');


COMMIT;

-- ── Quick check (optional) ─────────────────────────────────────────────────
-- SELECT text_value FROM faculty_education fe JOIN team_members tm ON tm.id = fe.member_id
--  WHERE tm.name = 'Emmanuel Junior Tapany' ORDER BY fe.sort_order;
-- SELECT p.slug, c.group_label, c.code, c.title FROM courses c JOIN programs p ON p.id = c.program_id
--  WHERE p.tag = 'MSc' ORDER BY p.slug, c.sort_order;
-- SELECT title, file_path, sort_order FROM documents ORDER BY sort_order;
