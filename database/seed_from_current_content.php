<?php
declare(strict_types=1);

/**
 * One-time reseed: wipes the stale SCOTSA-era rows in the tables below and
 * inserts the REAL content that's currently hardcoded in index.php, so the
 * dashboard cutover doesn't lose anything. Run once, by hand, from the CLI:
 *
 *   php database/seed_from_current_content.php
 *
 * Safe to re-run — it always wipes-then-inserts these specific tables, so
 * running it twice just re-seeds the same state (it does NOT touch
 * announcements/past_questions/users/downloads/audit_log/etc., which are
 * unrelated leftover tables from the old build).
 */

require_once __DIR__ . '/../includes/env.php';
require_once __DIR__ . '/../includes/db.php';

$pdo = db();
$pdo->beginTransaction();

try {
    // ── Wipe stale rows (children first for FK safety) ──────────────
    foreach ([
        'faculty_courses_taught', 'faculty_publications', 'faculty_education',
        'team_members',
        'courses', 'programs',
        'student_project_stack', 'student_projects',
        'blog_post_photos', 'blog_posts',
        'facilities', 'videos',
        'gallery_items', 'gallery_categories',
        'hero_slides',
        'site_settings',
        'admins',
    ] as $table) {
        $pdo->exec("DELETE FROM `$table`");
    }

    // ── hero_slides ──────────────────────────────────────────────────
    $heroSlides = [
        ['labs/lab-computing-room-1.jpg', null, 'The School of Computing and Technology.',
            'Educating the next generation of computing, IT, and cybersecurity professionals at Wisconsin International University College (WIUC), Accra.'],
        ['labs/lab-robotics-drone-1.jpg', 'center 70%', 'Robotics & Artificial Intelligence.',
            'Hands-on labs in robotics, machine learning, and applied AI — preparing students for the technology of tomorrow.'],
        ['labs/lab-forensics-1.jpg', null, 'Cybersecurity & Digital Forensics.',
            'Purpose-built forensics labs equipped with industry-standard tools for investigation and incident response.'],
        ['labs/lab-workstation-1.jpg', null, 'High-Performance Computing Labs.',
            'Modern workstations built for demanding coursework in programming, data science, and systems design.'],
        ['labs/lab-server-rack.jpg', null, 'Real Infrastructure, Real Skills.',
            'Students train on genuine server and networking equipment, not just simulations.'],
        ['labs/lab-computing-room-4.jpg', null, 'Undergraduate, Diploma & Postgraduate programmes.',
            'From foundational diplomas to postgraduate degrees in cybersecurity, business computing, and IT.'],
    ];
    $stmt = $pdo->prepare('INSERT INTO hero_slides (photo_path, photo_position, headline, subtext, sort_order, status) VALUES (?, ?, ?, ?, ?, "active")');
    foreach ($heroSlides as $i => $s) {
        $stmt->execute([$s[0], $s[1], $s[2], $s[3], $i]);
    }
    echo "hero_slides: " . count($heroSlides) . " rows\n";

    // ── gallery_categories + gallery_items ───────────────────────────
    $galleryCategories = ['Events', 'Seminars', 'Hackathon', 'Orientation', 'Facilities'];
    $catStmt = $pdo->prepare('INSERT INTO gallery_categories (name, sort_order) VALUES (?, ?)');
    foreach ($galleryCategories as $i => $cat) {
        $catStmt->execute([$cat, $i]);
    }

    $galleryPhotos = [
        ['labs/lab-computing-room-1.jpg', 'Cybersecurity Lab', 'Facilities'],
        ['labs/lab-computing-room-2.jpg', 'Computing Lab', 'Facilities'],
        ['labs/lab-computing-room-3.jpg', 'Computing Lab', 'Facilities'],
        ['labs/lab-computing-room-4.jpg', 'Computing Lab', 'Facilities'],
        ['labs/lab-computing-room-5.jpg', 'Computing Lab', 'Facilities'],
        ['labs/lab-computing-room-6.jpg', 'Computing Lab', 'Facilities'],
        ['labs/lab-forensics-3.jpg', 'Digital Forensic Tools', 'Facilities'],
        ['labs/lab-forensics-4.jpg', 'Digital Forensic Tools', 'Facilities'],
        ['labs/lab-robotics-drone-1.jpg', 'Robotics Equipment', 'Facilities'],
        ['labs/lab-3d-printer.jpg', '3D Printing Lab', 'Facilities'],
        ['labs/lab-vr-headsets-2.jpg', 'VR Headsets', 'Facilities'],
        ['labs/lab-vr-headsets-1.jpg', 'VR Headsets', 'Facilities'],
        ['labs/lab-vr-headsets-3.jpg', 'VR Headsets', 'Facilities'],
        ['labs/lab-workstation-1.jpg', 'High-Performance Workstations', 'Facilities'],
        ['labs/lab-workstation-2.jpg', 'High-Performance Workstations', 'Facilities'],
        ['labs/lab-forensics-1.jpg', 'Digital Forensics Lab', 'Facilities'],
        ['labs/lab-forensics-2.jpg', 'Digital Forensics Lab', 'Facilities'],
        ['labs/lab-server-rack.jpg', 'Server & Network Infrastructure', 'Facilities'],
        ['labs/lab-print-scan-station.jpg', 'Print & Scan Station', 'Facilities'],
        ['gallery/IMG_4617.JPEG', 'SCOTSA Annual General Assembly', 'Events'],
        ['gallery/IMG_4620.JPEG', 'Department Welcome Ceremony', 'Events'],
        ['gallery/IMG_4625.JPEG', 'SRC Week Opening Ceremony', 'Events'],
        ['gallery/IMG_4637.JPEG', 'SCOTSA Community Gathering', 'Events'],
        ['gallery/IMG_4742.JPEG', 'End-of-Semester Celebration', 'Events'],
        ['gallery/IMG_4849.JPEG', 'SRC Week Cultural Showcase', 'Events'],
        ['gallery/IMG_4833.JPEG', 'Academic Awareness Seminar', 'Seminars'],
        ['gallery/IMG_4845.JPEG', 'Department Workshop', 'Seminars'],
        ['gallery/IMG_4632.JPEG', 'SCOT Hackathon 2024', 'Hackathon'],
        ['gallery/Orientation_12.JPEG', 'Student Engaged During Orientation', 'Orientation'],
        ['gallery/Orientation_46.JPEG', 'Connect, Create, Collaborate Campaign', 'Orientation'],
        ['gallery/Orientation_47.JPEG', 'Faculty Member Addressing Students', 'Orientation'],
        ['gallery/Orientation_48.JPEG', 'Open Floor Discussion at Orientation', 'Orientation'],
        ['gallery/Orientation_50.JPEG', 'Student Sharing Remarks at Orientation', 'Orientation'],
        ['gallery/Orientation_51.JPEG', 'Executive Member Addressing New Students', 'Orientation'],
        ['gallery/Orientation_52.JPEG', 'SCOTSA Executives Engaging Students', 'Orientation'],
        ['gallery/Orientation_63.JPEG', 'Student Addressing the Orientation Audience', 'Orientation'],
        ['gallery/Orientation_64.JPEG', 'SCOTSA Executive Speaking at Orientation', 'Orientation'],
        ['gallery/Orientation_65.JPEG', 'Facilitator Leading a Training Session', 'Orientation'],
        ['gallery/Orientation_68.JPEG', 'Connect, Create, Collaborate Campaign', 'Orientation'],
        ['gallery/Orientation_71.JPEG', 'SCOTSA Orientation Group Photo', 'Orientation'],
        ['gallery/IMG_7216.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/IMG_7218.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/IMG_7219.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/IMG_7220.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/IMG_7221.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/IMG_7222.jpg', 'SCOTSA Team at the Orientation Session', 'Orientation'],
        ['gallery/DR.IAN ASARE.JPEG', 'Dr. Ian Asare at Orientation', 'Orientation'],
        ['gallery/DR.MATEKO OKANTEY.JPEG', 'Dr. Mateko Okantey at Orientation', 'Orientation'],
        ['hero/hero-orientation-team.jpg', 'Orientation Team Group Photo', 'Orientation'],
    ];
    $giStmt = $pdo->prepare('INSERT INTO gallery_items (photo_path, caption, category, sort_order, status) VALUES (?, ?, ?, ?, "active")');
    foreach ($galleryPhotos as $i => $g) {
        $giStmt->execute([$g[0], $g[1], $g[2], $i]);
    }
    echo "gallery_categories: " . count($galleryCategories) . " rows\n";
    echo "gallery_items: " . count($galleryPhotos) . " rows\n";

    // ── programs + courses ────────────────────────────────────────────
    $progStmt = $pdo->prepare(
        'INSERT INTO programs (name, tag, slug, description, note, featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $courseStmt = $pdo->prepare(
        'INSERT INTO courses (program_id, level_id, semester_id, group_label, sort_order, code, title) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    // levels: 1=100 2=200 3=300 4=400 | semesters: 1=lower(First) 2=upper(Second)
    $levelMap = ['100' => 1, '200' => 2, '300' => 3, '400' => 4];
    $semMap   = ['lower' => 1, 'upper' => 2];

    $undergraduate = [
        ['tag' => 'BSc', 'name' => 'Information Technology', 'code' => 'BSCIT', 'featured' => 1,
            'desc' => 'Systems, networking, software tools, and applied computing for real-world IT roles.', 'note' => null,
            'courses' => [
                '100' => ['lower' => [['WGS105','Introduction to Sociology'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices']],
                          'upper' => [['ITW104','Discrete Mathematics'],['WGS108','Principles of Psychology'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT205','Programming in C++'],['WMT107','Logic and Critical Thinking']]],
                '200' => ['lower' => [['ITW201','Data Structures and Algorithms'],['ITW218','Calculus'],['ITW307','Computer Architecture & Organization'],['ITW310','Multimedia Applications'],['WIT301','Management Information Systems'],['WIT310','Systems Analysis and Design']],
                          'upper' => [['ITW202','Probability and Statistics'],['ITW204','Intro to Software Engineering'],['ITW207','Modern Operating Systems'],['ITW210','Database Management Systems I'],['ITW301','Web Technologies'],['ITW303','Data Communications and Networks'],['ITW406','Professional Ethics and Legal Issues']]],
                '300' => ['lower' => [['ITW205','Website Programming'],['ITW208','IT Service Management'],['ITW305','Advance Database Management System'],['ITW311','Numerical Methods'],['ITW315','Cloud Computing'],['ITW316','Data Communication & Networks II'],['ITW408','Human Computer Interaction'],['WIT311','Object-Oriented Programming (Visual Basic)']],
                          'upper' => [['ITW304','Wireless and Mobile Computing'],['ITW322','Advanced Website Programming'],['ITW325','Information Systems Research Methods'],['ITW405','E-Business and E-Commerce'],['WIT214','Programming in Java'],['WMT318','Data Analysis']]],
                '400' => ['lower' => [['ITW308','Distributed Systems'],['ITW312','Systems Administration'],['ITW322','Advanced Website Programming'],['ITW407','Information Systems Security Management'],['ITW409','Embedded Systems'],['WBC301','Mobile Application Development']],
                          'upper' => [['ITW403','Artificial Intelligence'],['ITW413','Project Management'],['ITW416','Data Mining'],['ITW422','Advanced Mobile Application Development'],['WBS304','Operations Management'],['WBS332','Fundamentals of Entrepreneurship']]],
            ]],
        ['tag' => 'BSc', 'name' => 'Computing and Actuarial Science', 'code' => 'BSCCAS', 'featured' => 0,
            'desc' => 'Combines computing fundamentals with actuarial mathematics and statistics for careers in insurance, finance, and risk analysis.', 'note' => null,
            'courses' => [
                '100' => ['lower' => [['WCA101','Introductory Mathematical Methods'],['WCA103','Statistics'],['WCA105','Computer Systems'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT']],
                          'upper' => [['WCA102','Mathematics of Finance and Investment I'],['WCA104','Probability Theory and Distribution'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT150','Intro to Network & the Internet'],['WMT107','Logic and Critical Thinking']]],
                '200' => ['lower' => [['ITW207','Modern Operating Systems'],['WCA205','Mathematics of Finance and Investment II'],['WIT211','Principles of Programming']],
                          'upper' => [['ITW210','Database Management Systems I'],['WBS202','Financial Accounting II'],['WCA202','Sampling Techniques and Survey Methods'],['WCA206','Modelling with Spreadsheet'],['WES210','Principles of Macroeconomics'],['WIT205','Programming in C++']]],
                '300' => ['lower' => [['ITW304','Wireless and Mobile Computing'],['ITW305','Advance Database Management System'],['ITW315','Cloud Computing'],['WBF407','Corporate Finance'],['WCA309','Life Contingencies'],['WIT214','Programming in Java']],
                          'upper' => [['ITW204','Intro to Software Engineering'],['ITW205','Website Programming'],['WBS308','Research Methods'],['WCA308','Social Security and Pensions Administration'],['WCA314','Life Insurance'],['WIT301','Management Information Systems']]],
                '400' => ['lower' => [['ITW312','Systems Administration'],['ITW416','Data Mining'],['WBC301','Mobile Application Development'],['WCA411','Non-Life Insurance'],['WCA413','Health Insurance']],
                          'upper' => [['ITW404','Operation Research and Optimization'],['ITW406','Professional Ethics and Legal Issues'],['WBS332','Fundamentals of Entrepreneurship'],['WCA410','Risk Management'],['WCA412','Statistical Inference']]],
            ]],
        ['tag' => 'BSc', 'name' => 'Cybersecurity', 'code' => 'BSCCS', 'featured' => 0,
            'desc' => 'Security operations, digital forensics, network defence, and risk management.',
            'note' => 'Course structure currently published through Level 300 First Semester.',
            'courses' => [
                '100' => ['lower' => [['WCB101','Introduction to Cybersecurity'],['WCB103','Computer Hardware and Software'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices'],['WMT107','Logic and Critical Thinking']],
                          'upper' => [['ITW205','Website Programming'],['ITW218','Calculus'],['WBS201','Financial Accounting I'],['WCB104','Networking Fundamentals'],['WCB106','Digital Systems and Logic Design'],['WGS128','Communication Skills II'],['WIT102','Programming with Python']]],
                '200' => ['lower' => [['ITW104','Discrete Mathematics'],['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks'],['ITW307','Computer Architecture & Organization'],['WCB201','Intro to Secure Systems Design'],['WCB207','Intro to Penetration Testing'],['WIT214','Programming in Java']],
                          'upper' => [['ITW201','Data Structures and Algorithms'],['ITW204','Intro to Software Engineering'],['ITW207','Modern Operating Systems'],['ITW305','Advance Database Management System'],['ITW407','Information Systems Security Management'],['WCB212','Number Theory']]],
                '300' => ['lower' => [['ITW325','Information Systems Research Methods'],['WCB212','Number Theory'],['WCB301','Web Application Security'],['WCB303','Operating System Security'],['WCB305','Cryptography'],['WCB307','Cyber Law & Ethics'],['WCB309','Differential Equation'],['WCB311','Intro to IoT Security and Challenges']],
                          'upper' => []],
            ]],
        ['tag' => 'BSc', 'name' => 'Artificial Intelligence and Robotics', 'code' => 'BSCAIR', 'featured' => 0,
            'desc' => 'Machine learning, intelligent systems, and robotics engineering at the frontier of computing.',
            'note' => 'The newest programme in the department — currently running Level 100 only.',
            'courses' => [
                '100' => ['lower' => [['WCB103','Computer Hardware and Software'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIAR111','Introduction to Structured Programming'],['WIAR123','Introduction to Artificial Intelligence'],['WIAR127','Introduction to Robotics'],['WIAR167','Introduction to Electronics and Circuits'],['WMT103','Algebra & Matrices']],
                          'upper' => []],
            ]],
        ['tag' => 'BSc', 'name' => 'Management with Information Technology', 'code' => 'BMIT', 'featured' => 0,
            'desc' => 'Blends business management with IT skills for technology-driven organisational leadership.',
            'note' => 'Course structure currently published through Level 200 First Semester.',
            'courses' => [
                '100' => ['lower' => [['WBC101','Fundamentals of Management Science'],['WBS217','Introduction to Business Management'],['WGS105','Introduction to Sociology'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT']],
                          'upper' => [['WBC101','Fundamentals of Management Science'],['WGS108','Principles of Psychology'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT211','Principles of Programming'],['WIT301','Management Information Systems'],['WMT107','Logic and Critical Thinking']]],
                '200' => ['lower' => [['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks'],['WBS201','Financial Accounting I'],['WIT214','Programming in Java'],['WIT310','Systems Analysis and Design'],['WMT215','Quantitative Methods I']],
                          'upper' => []],
            ]],
        ['tag' => 'Diploma', 'name' => 'Information Technology', 'code' => 'DIPIT', 'featured' => 0,
            'desc' => 'A foundational two-year diploma in practical information technology skills.', 'note' => null,
            'courses' => [
                '100' => ['lower' => [['ITW307','Computer Architecture & Organization'],['WBS201','Financial Accounting I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices']],
                          'upper' => [['ITW104','Discrete Mathematics'],['ITW310','Multimedia Applications'],['WBS217','Introduction to Business Management'],['WGS128','Communication Skills II'],['WIT150','Intro to Network & the Internet'],['WIT205','Programming in C++']]],
                '200' => ['lower' => [['DIT205','Website Design & Development'],['ITW201','Data Structures and Algorithms'],['ITW207','Modern Operating Systems'],['WIT301','Management Information Systems'],['WIT310','Systems Analysis and Design']],
                          'upper' => [['DIT208','Computer Graphics'],['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks']]],
            ]],
    ];

    $sortOrder = 0;
    foreach ($undergraduate as $p) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $p['code']));
        $progStmt->execute([$p['name'], $p['tag'], $slug, $p['desc'], $p['note'], $p['featured'], $sortOrder++]);
        $programId = (int) $pdo->lastInsertId();
        $courseSort = 0;
        foreach ($p['courses'] as $level => $sems) {
            foreach (['lower', 'upper'] as $sem) {
                foreach ($sems[$sem] as [$code, $title]) {
                    $courseStmt->execute([$programId, $levelMap[$level], $semMap[$sem], null, $courseSort++, $code, $title]);
                }
            }
        }
    }
    echo "programs (undergraduate/diploma): " . count($undergraduate) . " rows\n";

    // MSc course structures — transcribed from the School's official
    // "Mounted Courses" listings (WIUC MIS, faculty 4). Each course is a
    // [code, title] pair; within Core/Elective they run first-semester
    // (600L) courses before second-semester (600U), then by code.
    $postgraduate = [
        ['name' => 'Cybersecurity and Digital Forensics', 'code' => 'MSCCSDF',
            'desc' => 'Develops critical skills to analyse and solve cyber security problems, covering the legal, ethical, and technical dimensions of designing and securing modern IT systems.',
            'core' => [
                ['WMCS603', 'Interactive Programming with Python'],
                ['WMIT601', 'Research Methods and Professional Practice'],
                ['WMIT605', 'Computer Networking Theory, Technologies & Protocols'],
                ['WMIT607', 'Artificial Intelligence and Machine Learning'],
                ['WMCS602', 'Operating Systems Theory and Applications'],
                ['WMCS604', 'Cyber Security and Forensics'],
                ['WMCS608', 'Computer Networks and Systems Security'],
                ['WMIT600', 'Project Work'],
                ['WMIT608', 'Data Structures and Complexities of Algorithms'],
                ['WMIT610', 'Seminar'],
            ],
            'elective' => [
                ['WMCS609', 'Data Recovery and Digital Forensics Analysis'],
                ['WMCS611', 'Ethical Hacking and Penetration Testing'],
                ['WMCS612', 'Cryptography Theory & Applications'],
                ['WMCS616', 'Mobile Systems Forensics'],
            ]],
        ['name' => 'Business Computing', 'code' => 'MSCBC',
            'desc' => 'Combines computing expertise with business strategy, equipping graduates to design, evaluate, and implement IT-driven solutions across modern organisations.',
            'core' => [
                ['WMBC605', 'Business Process Analysis and Design'],
                ['WMIT601', 'Research Methods and Professional Practice'],
                ['WMIT605', 'Computer Networking Theory, Technologies & Protocols'],
                ['WMIT607', 'Artificial Intelligence and Machine Learning'],
                ['WMBC602', 'IT Project Management'],
                ['WMBC606', 'Supply Chain Integration Technologies'],
                ['WMBC608', 'Technology Entrepreneurship and Innovations'],
                ['WMBC612', 'Information Security Management'],
                ['WMIT600', 'Project Work'],
                ['WMIT610', 'Seminar'],
            ],
            'elective' => [
                ['WMBC613', 'Big Data Analytics'],
                ['WMIT609', 'Advanced Database Management Systems'],
                ['WMBC614', 'Business Intelligence'],
                ['WMIT606', 'Management Information Systems'],
            ]],
        ['name' => 'Information Technology', 'code' => 'MSCIT',
            'desc' => 'Advanced knowledge across web technologies, mobile computing, machine learning, data management, cybersecurity, and cloud computing, paired with real-world problem-solving practice.',
            'core' => [
                ['WMIT601', 'Research Methods and Professional Practice'],
                ['WMIT603', 'Advanced Programming Concepts with Java'],
                ['WMIT605', 'Computer Networking Theory, Technologies & Protocols'],
                ['WMIT607', 'Artificial Intelligence and Machine Learning'],
                ['WMBC602', 'IT Project Management'],
                ['WMIT600', 'Project Work'],
                ['WMIT602', 'Operating Systems Theory and Administration'],
                ['WMIT604', 'Advanced Computer Networks'],
                ['WMIT606', 'Management Information Systems'],
                ['WMIT608', 'Data Structures and Complexities of Algorithms'],
                ['WMIT610', 'Seminar'],
            ],
            'elective' => [
                ['WMIT609', 'Advanced Database Management Systems'],
                ['WMIT613', 'Ethical Hacking, Data Recovery and Penetration Testing'],
                ['WMCS604', 'Cyber Security and Forensics'],
                ['WMIT616', 'Advanced Software Engineering'],
            ]],
    ];
    foreach ($postgraduate as $p) {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $p['code']));
        $progStmt->execute([$p['name'], 'MSc', $slug, $p['desc'], null, 0, $sortOrder++]);
        $programId = (int) $pdo->lastInsertId();
        $courseSort = 0;
        foreach (['core', 'elective'] as $group) {
            foreach ($p[$group] as [$code, $title]) {
                $courseStmt->execute([$programId, null, null, $group, $courseSort++, $code, $title]);
            }
        }
    }
    echo "programs (postgraduate): " . count($postgraduate) . " rows\n";

    $shortCourses = [
        'Networking & Systems Administration', 'Forensic Report Writing & Intelligence Analysis',
        'Administrative Computing; Office Suite Fundamentals', 'Cloud Data Extraction',
        'Advanced Mobile Operating System Forensics', 'Artificial Intelligence for Security Operation & Governance',
        'Mobile Financial Transaction Forensics', 'Cryptocurrency Wallet Analysis',
        'Encrypted Messaging Application Analysis', 'Data Visualization Techniques',
        'Mobile Malware Detection & Spyware Analysis', 'Cybersecurity in Judicial Practice',
        'Information Security Risk Assessment & Management', 'Cybersecurity & Critical Infrastructure Protection',
        'Cyber Defense Analysis',
    ];
    foreach ($shortCourses as $name) {
        $slug = 'sc-' . strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
        $slug = substr($slug, 0, 140);
        $progStmt->execute([$name, 'Short Courses', $slug, null, null, 0, $sortOrder++]);
    }
    echo "programs (short courses): " . count($shortCourses) . " rows\n";

    // ── team_members + faculty sub-lists ─────────────────────────────
    $memberStmt = $pdo->prepare(
        'INSERT INTO team_members (roster, role, name, email, portfolio, bio, research_interest, photo_path, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $ctStmt = $pdo->prepare('INSERT INTO faculty_courses_taught (member_id, text_value, sort_order) VALUES (?, ?, ?)');
    $pubStmt = $pdo->prepare('INSERT INTO faculty_publications (member_id, text_value, sort_order) VALUES (?, ?, ?)');
    $eduStmt = $pdo->prepare('INSERT INTO faculty_education (member_id, text_value, sort_order) VALUES (?, ?, ?)');

    function seed_member(PDO $pdo, $memberStmt, $ctStmt, $pubStmt, $eduStmt, string $roster, array $m, int $sort): void
    {
        $memberStmt->execute([
            $roster, $m['role'], $m['name'], $m['email'] ?? null, $m['portfolio'] ?? null,
            $m['bio'] ?? null, $m['research_interest'] ?? null, $m['photo'] ?? null, $sort,
        ]);
        $memberId = (int) $pdo->lastInsertId();
        foreach ($m['courses_taught'] ?? [] as $i => $c) {
            $ctStmt->execute([$memberId, $c, $i]);
        }
        foreach ($m['publications'] ?? [] as $i => $p) {
            $pubStmt->execute([$memberId, $p, $i]);
        }
        foreach ($m['education'] ?? [] as $i => $e) {
            $eduStmt->execute([$memberId, $e, $i]);
        }
    }

    $dean = [
        'role' => 'Dean', 'name' => 'Dr. Patrick Kudjo', 'portfolio' => 'School of Computing and Technology',
        'bio' => 'Dr. Patrick Kudjo leads the School of Computing and Technology at Wisconsin International University College, overseeing academic direction, faculty, and departmental strategy across all computing programmes.',
        'photo' => 'faculty/dean-patrick-kudjo.png', 'email' => 'pat.kudjo@wiuc-ghana.edu.gh',
        'courses_taught' => ['WCB303 — Operating System Security'],
    ];
    seed_member($pdo, $memberStmt, $ctStmt, $pubStmt, $eduStmt, 'leadership', $dean, 0);

    $heads = [
        [
            'role' => 'Head of Department, Business Computing', 'name' => 'Charles A. Babbage Jnr.',
            'portfolio' => 'Educator, Researcher & AI Specialist',
            'bio' => "Charles Jnr. Asiedu, widely known as Babbage, is a seasoned educator and researcher with a distinguished background in Information Technology and Artificial Intelligence.\n\nHe earned his Bachelor of Science in Information Technology from Wisconsin International University College, graduating as the Valedictorian of his class. He subsequently completed his national service at the University of Ghana Business School, contributing to the Operations and Management Information Systems Department.\n\nCharles holds a research-based Master's degree in Engineering from Huzhou University, China, awarded through a competitive merit-based Chinese scholarship for academic excellence. His specialization in Intelligent Information Processing Technology advanced his work in artificial intelligence, culminating in his research thesis titled \"Sentiment Analysis on Twitter Data: A Performance Gap Between Deep Learning and Traditional Machine Learning Algorithms.\"\n\nThroughout his career, he has contributed to curriculum development for CTVET institutions, consulted for various organizations, and published research in respected journals.\n\nHis research interests focus on Artificial Intelligence, Machine Learning applications, and their intersection with Healthcare and Education.",
            'photo' => 'faculty/hod-charles-babbage-asiedu.jpg', 'email' => 'charles.asiedu@wiuc-ghana.edu.gh',
            'research_interest' => 'Artificial Intelligence, Machine Learning applications, and their intersection with Healthcare and Education.',
            'courses_taught' => ['WIT105 — Fundamentals of ICT','WIT102 — Programming with Python','WIT214 — Programming in Java','ITW304 — Wireless and Mobile Computing','ITW416 — Data Mining','ITW422 — Advanced Mobile Application Development'],
            'publications' => ['Research thesis: "Sentiment Analysis on Twitter Data: A Performance Gap Between Deep Learning and Traditional Machine Learning Algorithms" — Huzhou University','Additional research published in peer-reviewed journals (full list to be added)'],
            'education' => ['Master\'s in Engineering (research-based), Huzhou University, China — merit-based Chinese scholarship; specialisation in Intelligent Information Processing Technology','National Service, University of Ghana Business School — Operations and Management Information Systems Department','Bachelor of Science in Information Technology, Wisconsin International University College — graduated Valedictorian'],
        ],
        [
            'role' => 'Head of Department, Information Technology', 'name' => 'Dr. Richard Amankwah',
            'portfolio' => 'Information Technology',
            'bio' => 'Dr. Richard Amankwah heads the Information Technology unit at the School of Computing and Technology, overseeing the BSc Information Technology and Diploma in Information Technology programmes.',
            'photo' => 'faculty/hod-richard-amankwah.jpg', 'email' => null,
            'courses_taught' => ['ITW205 — Website Programming','ITW322 — Advanced Website Programming','ITW407 — Information Systems Security Management'],
        ],
        [
            'role' => 'Head of Department, Mathematics Applications', 'name' => 'Dr. Leonard Kyei',
            'portfolio' => 'Mathematics Applications',
            'bio' => 'Dr. Leonard Kyei heads the Mathematics Applications unit at the School of Computing and Technology, teaching statistics and quantitative methods across several programmes.',
            'photo' => 'faculty/hod-leonard-kyei.jpg', 'email' => 'leonard.kyei@wiuc-ghana.edu.gh',
            'courses_taught' => ['ITW202 — Probability and Statistics','WBS308 — Research Methods','WCA104 — Probability Theory and Distribution','WCA412 — Statistical Inference','WMT106 — Basic Statistics'],
        ],
    ];
    foreach ($heads as $i => $h) {
        seed_member($pdo, $memberStmt, $ctStmt, $pubStmt, $eduStmt, 'leadership', $h, $i + 1);
    }
    echo "team_members (leadership): " . (1 + count($heads)) . " rows\n";

    $lecturers = [
        [
            'role' => 'Head, IT Support Department', 'name' => 'Mr. Edwin Agbah',
            'portfolio' => 'Network Engineer & IT Infrastructure Lead',
            'bio' => "Mr. Edwin Agbah serves as Head of the IT Support Department and oversees institutional networking and server infrastructure management.\n\nWith extensive Cisco-based networking training, he manages routing, switching, infrastructure security, and enterprise system reliability across campus environments.\n\nHe bridges academic instruction with enterprise-grade systems administration, ensuring high availability and operational continuity.",
            'photo' => 'faculty/edwin-agbah.jpg', 'email' => 'edwin.agbah@wiuc-ghana.edu.gh',
            'courses_taught' => ['ITW312 — Systems Administration','ITW316 — Data Communication & Networks II','WCB207 — Intro to Penetration Testing','WCB301 — Web Application Security'],
        ],
        [
            'role' => 'Lecturer, Cloud Computing', 'name' => 'Dr. Ian Asare',
            'portfolio' => 'Cloud Architecture Specialist',
            'bio' => "Dr. Ian Asare is a lecturer at the School of Computing and Technology, specialising in cloud architecture and distributed systems.\n\nHe works closely with students on hands on projects that translate cloud computing theory into practical architecture, helping them build the skills needed for cloud engineering roles after graduation.",
            'photo' => 'faculty/dr-ian-asare.jpg', 'email' => null,
            'research_interest' => 'Cloud infrastructure design, scalable systems, and the deployment models used by modern enterprise platforms.',
            'courses_taught' => ['ITW204 — Intro to Software Engineering'],
        ],
        [
            'role' => 'Lecturer, Information Systems', 'name' => 'Dr. Mateko Okantey',
            'portfolio' => 'Systems Analysis, E-Commerce & E-Business',
            'bio' => "Dr. Mateko Okantey is a lecturer at the School of Computing and Technology, specialising in systems analysis, e-commerce, and e-business.\n\nShe guides students through the full systems development lifecycle, from requirements gathering and analysis to designing e-business solutions that meet real organisational needs.",
            'photo' => 'faculty/dr-mateko-okantey.jpg', 'email' => 'mateko.okantey@wiuc-ghana.edu.gh',
            'research_interest' => 'How organisations design, analyse, and implement information systems that support online commerce and digital business operations.',
            'courses_taught' => ['WIT301 — Management Information Systems'],
        ],
        [
            'role' => 'Lecturer, Artificial Intelligence', 'name' => 'Emmanuel Junior Tapany',
            'portfolio' => 'AI & Machine Learning Researcher',
            'bio' => "Emmanuel Junior Tapany is a Lecturer, Computer Scientist, researcher, academic mentor, and emerging scholar in Artificial Intelligence and advanced computing. He currently serves as the Postgraduate Coordinator for the School of Computing and Technology (SCOT), where he represents the Graduate School within SCOT; with dexterity He leads the coordination of postgraduate academic activities, communication, student progression, and related graduate-school functions under the auspices of the Dean of SCOT.\n\nHe is currently pursuing a PhD in Computer Science and holds both an MPhil in Computer Science and a BSc in Computer Science.\n\nHis research interests span Artificial Intelligence, Machine Learning, Deep Learning, Graph Neural Networks (GNNs), Capsule Neural Networks (CapsNets), Computer Vision, Edge AI and Edge Deployment, Data Science and Analytics, and intelligent applications in biometrics, health and agriculture. His doctoral research trajectory is increasingly incorporating Graph Neural Networks and graph-based representation learning, while building on his previous research experience in Capsule Neural Networks, artificial neural networks, biometric signature verification and intelligent computer vision systems. His previous research includes a Capsule Neural Network approach for bilingual writer-independent offline signature identification and verification, while his doctoral work explores scalable neural architectures, robustness benchmarking and edge deployment.\n\nAs an academic, Emmanuel combines teaching, research and practical computing education. He currently teaches at Wisconsin International University College, Ghana with teaching experience covering Artificial Intelligence, Principles of Programming, Networking Fundamentals, IT Service Management, Multimedia Applications, Computer Hardware and Software, Introduction to AI (tailored for Robotics) and Distributed Systems. He has also supervised undergraduate research projects in areas including AI-powered crop disease detection, digital agriculture, blockchain-based identity management and mobile application development.\n\nBeyond teaching and research, Emmanuel brings over two decades of active leadership orientation and service, having developed his leadership journey from student and community leadership into academic administration, coaching, counselling and mentorship. He has served in roles including Head Boy, House Captain, University Association President, Planning Committee Chairman and Secretary, Academic Coach. His leadership philosophy is centered on service, personal development, responsibility, growth and creating opportunities for others to succeed.\n\nA defining part of his professional identity is his passion for mentoring, developing and inspiring young minds towards excellence and greatness. He views education not merely as the transfer of technical knowledge, but as an opportunity to identify potential, cultivate confidence, encourage innovation and help students develop the character, competence and resilience required to become successful professionals, researchers and leaders.\n\nHis broader professional interests extend into responsible AI, secure data analytics, quantum computing, blockchain technologies and emerging computing paradigms. He participated in the QWorld QIntern 2023 programme, where his team worked on securing electoral processes using classical and quantum blockchain technology and received a 3rd Best Team Award. He has also contributed to peer-reviewed research publications in areas including MANET security and hybrid metaheuristic optimization.\n\nUltimately, Emmanuel's academic and professional vision is to contribute to the development of efficient, trustworthy and intelligent computing systems, while using research, teaching, leadership and mentorship to inspire the next generation of African scientists, technologists, innovators and leaders to realise their full potential and make meaningful contributions to society.",
            'photo' => 'faculty/emmanuel-tapany.jpg', 'email' => 'emmanuel.tapany@wiuc-ghana.edu.gh',
            'research_interest' => 'Artificial Intelligence and Machine Learning, with particular interests in Deep Learning, Graph Neural Networks, Computer Vision, Capsule Neural Networks, Explainable AI, Trustworthy AI, Data Science, and efficient learning architectures for real-world and resource-constrained environments.',
            'courses_taught' => ['ITW208 — IT Service Management','ITW308 — Distributed Systems','ITW403 — Artificial Intelligence','WCB104 — Networking Fundamentals','WIAR123 — Introduction to Artificial Intelligence','WIT211 — Principles of Programming'],
            'publications' => [
                'Agor, A. D., Kwesi Oberko, P. S., Dotse, S. K., Partey, B. T., Aboagye-Darko, D., & Tapany, E. J. (2026). A descriptive systematic review of contemporary MANET security research: themes, design structures, and reporting rigor. Future Technology, 5(2), 69–80.',
                'Agor, A. D., Banaseka, F. K., Oberko, P. S. K., Banning, L. A., Dotse, S. K., & Tapany, E. J. (2026). A systematic review of metaheuristic–metaheuristic (MH–MH) hybridizations for optimization. Journal of Computer Science, 22(2), 660–678. DOI: 10.3844/jcssp.2026.660.678.',
            ],
            'education' => ['PhD Computer Science, Ghana Communication Technology University (Ongoing)','MPhil Computer Science, University of Energy & Natural Resources','BSc Computer Science, University of Energy & Natural Resources'],
        ],
        [
            'role' => 'Lecturer', 'name' => 'Ruth Oteng', 'portfolio' => null, 'bio' => null,
            'photo' => 'faculty/ruth-oteng.jpg', 'email' => null,
            'courses_taught' => ['DIT208 — Computer Graphics','ITW405 — E-Business and E-Commerce','ITW413 — Project Management','WCA105 — Computer Systems','WCB101 — Introduction to Cybersecurity','WIT105 — Information Technology Fundamentals'],
        ],
    ];
    foreach ($lecturers as $i => $l) {
        seed_member($pdo, $memberStmt, $ctStmt, $pubStmt, $eduStmt, 'faculty', $l, $i);
    }
    echo "team_members (lecturers): " . count($lecturers) . " rows\n";

    // ── facilities ────────────────────────────────────────────────────
    $facilities = [
        ['labs/lab-computing-room-1.jpg', 'Cybersecurity Lab', 1],
        ['labs/lab-vr-headsets-2.jpg', 'Robotics, AI & VR Lab', 0],
        ['labs/lab-forensics-1.jpg', 'Digital Forensics Lab', 0],
        ['labs/lab-3d-printer.jpg', '3D Printing Lab', 0],
        ['labs/lab-server-rack.jpg', 'Server & Network Infrastructure', 0],
    ];
    $facStmt = $pdo->prepare('INSERT INTO facilities (photo_path, caption, featured, sort_order, status) VALUES (?, ?, ?, ?, "active")');
    foreach ($facilities as $i => $f) {
        $facStmt->execute([$f[0], $f[1], $f[2], $i]);
    }
    echo "facilities: " . count($facilities) . " rows\n";

    // ── videos (Watch section) ───────────────────────────────────────
    $videos = [
        ['videos/cybersecurity-lab-spotlight.mp4', 'video-posters/cybersecurity-lab-spotlight.jpg', 'Spotlight on the Cybersecurity & Digital Forensics Lab', 'A closer look at the department\'s Cybersecurity and Digital Forensics Laboratory and its equipment.'],
        ['videos/lab-forensics-tour.mp4', 'video-posters/lab-forensics-tour.jpg', 'Inside the Digital Forensics Lab', 'A walkthrough of the department\'s digital forensics lab and its investigation equipment.'],
        ['videos/campus-tour.mp4', 'video-posters/campus-tour.jpg', 'Campus Tour', 'An aerial look at the WIUC campus, home to the School of Computing and Technology.'],
        ['videos/computer-lab-tour.mp4', 'video-posters/computer-lab-tour.jpg', 'Inside the Computer Lab', 'A look inside one of the department\'s computer labs during a session.'],
    ];
    $vidStmt = $pdo->prepare('INSERT INTO videos (file_path, poster_path, title, caption, sort_order, status) VALUES (?, ?, ?, ?, ?, "active")');
    foreach ($videos as $i => $v) {
        $vidStmt->execute([$v[0], $v[1], $v[2], $v[3], $i]);
    }
    echo "videos: " . count($videos) . " rows\n";

    // ── student_projects + stack ──────────────────────────────────────
    $projStmt = $pdo->prepare(
        'INSERT INTO student_projects (title, student_name, programme_label, supervisor, co_supervisor, video_path, poster_path, summary, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "active")'
    );
    $projStmt->execute([
        'Smart IoT-Based LPG Leakage Detection System', 'Simon Zotoo', 'Final Year Project, BSc Information Technology',
        'Mr Charles A. Babbage Jnr', 'Mr. Nathaniel Mills',
        'videos/gas-leak-detection-system.mp4', 'video-posters/gas-leak-detection-system.jpg',
        "LPG replaced firewood in most Ghanaian kitchens in the 1990s, but most households still rely on smell alone to catch a leak. This project builds a low-cost early-warning system around that gap: an ESP32 microcontroller reads MQ-6 and MQ-2 gas sensors every two seconds, firing a local buzzer, red LED, and LCD warning the instant a leak crosses threshold. At the same time, readings are pushed to a Firebase Realtime Database and surfaced live in a Flutter mobile app; if Wi-Fi is down, a SIM800L GSM module sends an SMS straight to a pre-registered number instead.\n\nIn testing, the local alarm responded within the two-second polling interval, cloud updates arrived within seconds, and SMS delivery took roughly ten to twelve seconds. The system was built to be replicated cheaply in an ordinary Ghanaian household.",
        0,
    ]);
    $projectId = (int) $pdo->lastInsertId();
    $stack = ['ESP32', 'MQ-6 & MQ-2 Sensors', 'SIM800L GSM', 'Firebase Realtime DB', 'Flutter'];
    $stackStmt = $pdo->prepare('INSERT INTO student_project_stack (project_id, tech_text, sort_order) VALUES (?, ?, ?)');
    foreach ($stack as $i => $tech) {
        $stackStmt->execute([$projectId, $tech, $i]);
    }
    echo "student_projects: 1 row (+" . count($stack) . " stack tags)\n";

    // ── blog_posts + photos ───────────────────────────────────────────
    $blogStmt = $pdo->prepare(
        'INSERT INTO blog_posts (title, post_date, excerpt, video_path, poster_path, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, "published")'
    );
    $blogStmt->execute([
        'SCOT Delegation Visits the Ghana Armed Forces IT Directorate', '2026-09-03',
        "A delegation from the School of Computing and Technology, led by the Dean, Dr. Patrick Kwaku Kudjo, and including the Heads of Information Technology and Business Computing, paid a partnership visit to the Directorate of Information Technology, Ghana Armed Forces.\n\nThe visit explored future collaboration in professional training and capacity building for Service personnel, and included a tour of the Directorate's facilities and an engagement with officers and students at the Ghana Armed Forces School of Information Technology (GAFSIT). Discussions are ongoing, and we'll share further updates as the partnership develops.",
        'videos/gaf-visit-podium.mp4', 'video-posters/gaf-visit-podium.jpg', 0,
    ]);
    $postId = (int) $pdo->lastInsertId();
    $blogPhotos = [
        ['blog/gaf-visit-group-photo.jpg', 'The delegation with GAF officers at GAFSIT', 1],
        ['blog/gaf-visit-directors-office.jpg', 'With the Director of IT and Communications', 0],
        ['blog/gaf-visit-lecture-hall.jpg', 'Engaging officers and students at GAFSIT', 0],
        ['blog/gaf-visit-podium-address.jpg', 'Addressing officers and students', 0],
        ['blog/gaf-visit-tour-briefing.jpg', 'Touring the IT Directorate', 0],
    ];
    $bpStmt = $pdo->prepare('INSERT INTO blog_post_photos (post_id, photo_path, caption, featured, sort_order) VALUES (?, ?, ?, ?, ?)');
    foreach ($blogPhotos as $i => $bp) {
        $bpStmt->execute([$postId, $bp[0], $bp[1], $bp[2], $i]);
    }
    echo "blog_posts: 1 row (+" . count($blogPhotos) . " photos)\n";

    // ── site_settings ─────────────────────────────────────────────────
    $settings = [
        'site_meta_title' => 'School of Computing and Technology (SCOT), WIUC Ghana',
        'about_paragraph_1' => 'SCOT is the academic department at Wisconsin International University College (WIUC), Accra, responsible for undergraduate, diploma, and postgraduate programmes in computing, information technology, cybersecurity, and artificial intelligence. Our faculty combine industry experience with academic rigour to prepare graduates for real careers in technology.',
        'about_paragraph_2' => "SCOTSA, the department's student association, runs academic resources, events, and community programmes that support every student's journey through SCOT.",
        'about_feature_1_title' => 'Academic Excellence',
        'about_feature_1_body' => 'Undergraduate, diploma, and postgraduate programmes across computing, IT, cybersecurity, and AI.',
        'about_feature_2_title' => 'Experienced Faculty',
        'about_feature_2_body' => 'Lecturers and department leadership combining academic depth with industry experience.',
        'about_feature_3_title' => 'Built to Grow',
        'about_feature_3_body' => 'A department expanding its programmes, research, and student services year over year.',
        'dean_message' => 'Welcome to the School of Computing and Technology. Whether you are just beginning your studies or advancing toward a postgraduate degree, our goal is the same: to equip you with the knowledge, skills, and confidence to lead in an increasingly digital world. Our faculty are committed to your success, both in the classroom and beyond it.',
        'contact_email' => 'info@wiuc-ghana.edu.gh',
        'contact_phone' => '+233 54 485 3383',
        'contact_address' => 'No. 23 Akoto Bamfo Street, North Legon, Accra',
        'social_facebook' => 'https://web.facebook.com/wiucghana',
        'social_instagram' => 'https://www.instagram.com/wiuc_ghana/',
        'social_x' => 'https://twitter.com/WIUCGHANA',
        'social_linkedin' => 'https://www.linkedin.com/school/wiucghana/',
        'footer_tagline' => 'The School of Computing and Technology at Wisconsin International University College (WIUC), Accra — home to our undergraduate, diploma, and postgraduate programmes, faculty, and student community.',
        'apply_now_url' => '#',
    ];
    $setStmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($settings as $k => $v) {
        $setStmt->execute([$k, $v]);
    }
    echo "site_settings: " . count($settings) . " rows\n";

    // ── admins: one fresh account ─────────────────────────────────────
    $adminEmail = 'admin@wiuc-ghana.edu.gh';
    $adminPassword = bin2hex(random_bytes(6)); // shown once below, must be changed on first login
    $adminStmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, "super_admin")');
    $adminStmt->execute(['SCOT Admin', $adminEmail, password_hash($adminPassword, PASSWORD_DEFAULT)]);
    echo "admins: 1 row\n";

    $pdo->commit();

    echo "\n=== SEED COMPLETE ===\n";
    echo "Admin login: {$adminEmail}\n";
    echo "Admin password (shown once, CHANGE ON FIRST LOGIN): {$adminPassword}\n";
} catch (Throwable $e) {
    $pdo->rollBack();
    fwrite(STDERR, 'SEED FAILED: ' . $e->getMessage() . "\n");
    exit(1);
}
