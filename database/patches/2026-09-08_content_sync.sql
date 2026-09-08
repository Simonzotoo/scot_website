-- ============================================================================
--  SCOT — production DB content sync (2026-09-08)
--
--  Brings the LIVE (Hostinger) database in line with content changes
--  that are already deployed in code and files but not in the database:
--
--    1. Emmanuel Junior Tapany's education entries (institutions, no years)
--    2. The three MSc programmes' course structures (official titles + codes)
--    3. The three MSc course-structure PDFs on the Downloads page
--    4. Dr. Richard Amankwah's faculty profile (bio, portfolio, research
--       interest, publications, education)
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


-- ── 4. Faculty profile — Dr. Richard Amankwah ──────────────────────────────
--     Matched by name. CHAR(10) is a newline (bio paragraph break).

UPDATE team_members SET
    portfolio = 'Computer Scientist & Software Security Researcher',
    research_interest = 'Software vulnerability detection and severity prediction, web application security, software defect prediction using Bellwether analysis, and machine-learning approaches for secure and reliable software systems.',
    bio = CONCAT(
        'Dr. Richard Amankwah is the Head of the Information Technology Department at the School of Computing and Technology, where he oversees the BSc Information Technology and Diploma in Information Technology programmes, curriculum development, and student research supervision.', CHAR(10), CHAR(10),
        'He holds a PhD in Computer Application Technology from Jiangsu University, China, where his doctoral research focused on software vulnerability detection and severity prediction. He earned an MSc in Information Technology from Sikkim Manipal University, India, and a BEd in Information Technology from the University of Education, Winneba.', CHAR(10), CHAR(10),
        'Dr. Amankwah brings more than two decades of teaching experience across Ghanaian universities and colleges of education, spanning undergraduate and postgraduate computer science and information technology, and has supervised numerous undergraduate and graduate research projects. He has served for many years as a Chief Examiner with the West African Examinations Council (WAEC) and has contributed to test-item development for the National Teaching Council''s Ghana Teacher Licensure Examination.', CHAR(10), CHAR(10),
        'His research centres on software security — vulnerability detection methods and tools, the severity assessment of vulnerabilities reported by open-source and commercial web scanners, and the use of Bellwether analysis to predict vulnerable software components. His work has appeared in peer-reviewed journals and been presented at international venues including the IEEE International Conference on Trust, Security and Privacy in Computing and Communications and the ECOOP/ISSTA Doctoral Symposium. His broader professional background spans IT infrastructure, consultancy, data science, and cyber-security management, and he is a recipient of the T-TEL Challenge Fund award.'
    )
 WHERE name = 'Dr. Richard Amankwah';

DELETE fp
  FROM faculty_publications fp
  JOIN team_members tm ON tm.id = fp.member_id
 WHERE tm.name = 'Dr. Richard Amankwah';

INSERT INTO faculty_publications (member_id, text_value, sort_order)
SELECT tm.id, v.text_value, v.sort_order
  FROM team_members tm
  JOIN (
              SELECT 'Amankwah, R., Chen, J., Kudjo, P. K., & Towey, D. (2020). An empirical comparison of commercial and open-source web vulnerability scanners.' AS text_value, 0 AS sort_order
    UNION ALL SELECT 'Amankwah, R., Chen, J., Kudjo, P. K., Agyemang, B. K., & Amponsah, A. A. (2020). An automated framework for evaluating open-source web scanner vulnerability severity.', 1
    UNION ALL SELECT 'Amankwah, R., Chen, J., Mensah, S., & Kudjo, P. K. (2020). The effect of Bellwether analysis on software vulnerability severity prediction models.', 2
    UNION ALL SELECT 'Amankwah, R., Chen, J., Amponsah, A. A., Ocran, V., & Anang, C. O. (2020). Fast bug detection algorithm for identifying potential vulnerabilities in Juliet test cases.', 3
    UNION ALL SELECT 'Amankwah, R., Agyeman, B. K., Mensah, K., Brew, B., & Antwi, S. Y. (2018). An integrated approach for detecting security vulnerabilities in web applications: a theoretical perspective.', 4
    UNION ALL SELECT 'Amankwah, R., Chen, J., & Mensah, S. (2018). Predicting vulnerable software components via Bellwethers.', 5
    UNION ALL SELECT 'Amankwah, R., & Antwi, S. Y. (2017). Evaluation of software vulnerability detection methods and tools: a review.', 6
    UNION ALL SELECT 'Ben-Bright, B., Zhan, Y., Ghansah, B., Amankwah, R., et al. (2017). Taxonomy and a theoretical model for feedforward neural networks.', 7
    UNION ALL SELECT 'Adjardjah, W., Amankwah, R., Okine, A. A., et al. (2017). A fuzzy logic QoE enhancement VHO scheme for VLC-RF HetNet in an indoor environment.', 8
       ) v
 WHERE tm.name = 'Dr. Richard Amankwah';

DELETE fe
  FROM faculty_education fe
  JOIN team_members tm ON tm.id = fe.member_id
 WHERE tm.name = 'Dr. Richard Amankwah';

INSERT INTO faculty_education (member_id, text_value, sort_order)
SELECT tm.id, v.text_value, v.sort_order
  FROM team_members tm
  JOIN (
              SELECT 'PhD Computer Application Technology, Jiangsu University, China' AS text_value, 0 AS sort_order
    UNION ALL SELECT 'MSc Information Technology, Sikkim Manipal University, India', 1
    UNION ALL SELECT 'BEd Information Technology, University of Education, Winneba', 2
       ) v
 WHERE tm.name = 'Dr. Richard Amankwah';


COMMIT;

-- ── Quick check (optional) ─────────────────────────────────────────────────
-- SELECT text_value FROM faculty_education fe JOIN team_members tm ON tm.id = fe.member_id
--  WHERE tm.name = 'Emmanuel Junior Tapany' ORDER BY fe.sort_order;
-- SELECT p.slug, c.group_label, c.code, c.title FROM courses c JOIN programs p ON p.id = c.program_id
--  WHERE p.tag = 'MSc' ORDER BY p.slug, c.sort_order;
-- SELECT title, file_path, sort_order FROM documents ORDER BY sort_order;
-- SELECT portfolio, research_interest, bio FROM team_members WHERE name = 'Dr. Richard Amankwah';
-- SELECT text_value FROM faculty_publications fp JOIN team_members tm ON tm.id = fp.member_id
--  WHERE tm.name = 'Dr. Richard Amankwah' ORDER BY fp.sort_order;
