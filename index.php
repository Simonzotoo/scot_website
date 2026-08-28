<?php
$pageTitle = 'School of Computing and Technology (SCOT), WIUC Ghana';

/**
 * All department content is hardcoded here rather than admin-managed — this
 * is a single static showcase page, not a CMS. Update these arrays directly
 * when programmes, faculty, or hero photos change.
 */
$heroSlides = [
    [
        'bgVideo'  => 'videos/campus-tour.mp4',
        'photo'    => 'hero/hero-orientation-team.jpg', // used as the <video> poster and as a no-JS/no-video fallback
        'headline' => 'The School of Computing and Technology.',
        'subtext'  => 'Educating the next generation of computing, IT, and cybersecurity professionals at Wisconsin International University College (WIUC), Accra.',
    ],
    [
        'photo'    => 'hero/IMG_4625.JPEG',
        'headline' => 'Undergraduate, Diploma & Postgraduate programmes.',
        'subtext'  => 'From foundational diplomas to postgraduate degrees in cybersecurity, business computing, and IT.',
    ],
];

/**
 * Course structures below are transcribed from WIUC's published regular
 * semester timetable (2025/2026, 1st semester) — real course codes/titles
 * per level and semester, parsed directly from the timetable's own group
 * tables (one table per "[level][L/U]-[code]" group, matched by its own
 * <caption>), not invented and not limited to whatever a quick skim caught.
 * Levels/semesters with no group table in the timetable are simply omitted
 * rather than guessed — e.g. BSc AI & Robotics is genuinely Level 100 only
 * (newest programme), and diplomas stop at Level 200 by design. Postgraduate
 * programmes run on a separate schedule not covered by that timetable, so
 * they carry no 'courses' key and their cards are not clickable.
 */
$programmes = [
    'undergraduate' => [
        [
            'tag' => 'BSc', 'name' => 'Information Technology',
            'desc' => 'Systems, networking, software tools, and applied computing for real-world IT roles.',
            'code' => 'BSCIT',
            'courses' => [
                '100' => [
                    'lower' => [['WGS105','Introduction to Sociology'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices']],
                    'upper' => [['ITW104','Discrete Mathematics'],['WGS108','Principles of Psychology'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT205','Programming in C++'],['WMT107','Logic and Critical Thinking']],
                ],
                '200' => [
                    'lower' => [['ITW201','Data Structures and Algorithms'],['ITW218','Calculus'],['ITW307','Computer Architecture & Organization'],['ITW310','Multimedia Applications'],['WIT301','Management Information Systems'],['WIT310','Systems Analysis and Design']],
                    'upper' => [['ITW202','Probability and Statistics'],['ITW204','Intro to Software Engineering'],['ITW207','Modern Operating Systems'],['ITW210','Database Management Systems I'],['ITW301','Web Technologies'],['ITW303','Data Communications and Networks'],['ITW406','Professional Ethics and Legal Issues']],
                ],
                '300' => [
                    'lower' => [['ITW205','Website Programming'],['ITW208','IT Service Management'],['ITW305','Advance Database Management System'],['ITW311','Numerical Methods'],['ITW315','Cloud Computing'],['ITW316','Data Communication & Networks II'],['ITW408','Human Computer Interaction'],['WIT311','Object-Oriented Programming (Visual Basic)']],
                    'upper' => [['ITW304','Wireless and Mobile Computing'],['ITW322','Advanced Website Programming'],['ITW325','Information Systems Research Methods'],['ITW405','E-Business and E-Commerce'],['WIT214','Programming in Java'],['WMT318','Data Analysis']],
                ],
                '400' => [
                    'lower' => [['ITW308','Distributed Systems'],['ITW312','Systems Administration'],['ITW322','Advanced Website Programming'],['ITW407','Information Systems Security Management'],['ITW409','Embedded Systems'],['WBC301','Mobile Application Development']],
                    'upper' => [['ITW403','Artificial Intelligence'],['ITW413','Project Management'],['ITW416','Data Mining'],['ITW422','Advanced Mobile Application Development'],['WBS304','Operations Management'],['WBS332','Fundamentals of Entrepreneurship']],
                ],
            ],
        ],
        [
            'tag' => 'BSc', 'name' => 'Computing and Actuarial Science',
            'desc' => 'Combines computing fundamentals with actuarial mathematics and statistics for careers in insurance, finance, and risk analysis.',
            'code' => 'BSCCAS',
            'courses' => [
                '100' => [
                    'lower' => [['WCA101','Introductory Mathematical Methods'],['WCA103','Statistics'],['WCA105','Computer Systems'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT']],
                    'upper' => [['WCA102','Mathematics of Finance and Investment I'],['WCA104','Probability Theory and Distribution'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT150','Intro to Network & the Internet'],['WMT107','Logic and Critical Thinking']],
                ],
                '200' => [
                    'lower' => [['ITW207','Modern Operating Systems'],['WCA205','Mathematics of Finance and Investment II'],['WIT211','Principles of Programming']],
                    'upper' => [['ITW210','Database Management Systems I'],['WBS202','Financial Accounting II'],['WCA202','Sampling Techniques and Survey Methods'],['WCA206','Modelling with Spreadsheet'],['WES210','Principles of Macroeconomics'],['WIT205','Programming in C++']],
                ],
                '300' => [
                    'lower' => [['ITW304','Wireless and Mobile Computing'],['ITW305','Advance Database Management System'],['ITW315','Cloud Computing'],['WBF407','Corporate Finance'],['WCA309','Life Contingencies'],['WIT214','Programming in Java']],
                    'upper' => [['ITW204','Intro to Software Engineering'],['ITW205','Website Programming'],['WBS308','Research Methods'],['WCA308','Social Security and Pensions Administration'],['WCA314','Life Insurance'],['WIT301','Management Information Systems']],
                ],
                '400' => [
                    'lower' => [['ITW312','Systems Administration'],['ITW416','Data Mining'],['WBC301','Mobile Application Development'],['WCA411','Non-Life Insurance'],['WCA413','Health Insurance']],
                    'upper' => [['ITW404','Operation Research and Optimization'],['ITW406','Professional Ethics and Legal Issues'],['WBS332','Fundamentals of Entrepreneurship'],['WCA410','Risk Management'],['WCA412','Statistical Inference']],
                ],
            ],
        ],
        [
            'tag' => 'BSc', 'name' => 'Cybersecurity',
            'desc' => 'Security operations, digital forensics, network defence, and risk management.',
            'code' => 'BSCCS',
            'note' => 'Course structure currently published through Level 300 Lower Semester.',
            'courses' => [
                '100' => [
                    'lower' => [['WCB101','Introduction to Cybersecurity'],['WCB103','Computer Hardware and Software'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices'],['WMT107','Logic and Critical Thinking']],
                    'upper' => [['ITW205','Website Programming'],['ITW218','Calculus'],['WBS201','Financial Accounting I'],['WCB104','Networking Fundamentals'],['WCB106','Digital Systems and Logic Design'],['WGS128','Communication Skills II'],['WIT102','Programming with Python']],
                ],
                '200' => [
                    'lower' => [['ITW104','Discrete Mathematics'],['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks'],['ITW307','Computer Architecture & Organization'],['WCB201','Intro to Secure Systems Design'],['WCB207','Intro to Penetration Testing'],['WIT214','Programming in Java']],
                    'upper' => [['ITW201','Data Structures and Algorithms'],['ITW204','Intro to Software Engineering'],['ITW207','Modern Operating Systems'],['ITW305','Advance Database Management System'],['ITW407','Information Systems Security Management'],['WCB212','Number Theory']],
                ],
                '300' => [
                    'lower' => [['ITW325','Information Systems Research Methods'],['WCB212','Number Theory'],['WCB301','Web Application Security'],['WCB303','Operating System Security'],['WCB305','Cryptography'],['WCB307','Cyber Law & Ethics'],['WCB309','Differential Equation'],['WCB311','Intro to IoT Security and Challenges']],
                    'upper' => [],
                ],
            ],
        ],
        [
            'tag' => 'BSc', 'name' => 'Artificial Intelligence and Robotics',
            'desc' => 'Machine learning, intelligent systems, and robotics engineering at the frontier of computing.',
            'code' => 'BSCAIR',
            'note' => 'The newest programme in the department — currently running Level 100 only.',
            'courses' => [
                '100' => [
                    'lower' => [['WCB103','Computer Hardware and Software'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIAR111','Introduction to Structured Programming'],['WIAR123','Introduction to Artificial Intelligence'],['WIAR127','Introduction to Robotics'],['WIAR167','Introduction to Electronics and Circuits'],['WMT103','Algebra & Matrices']],
                    'upper' => [],
                ],
            ],
        ],
        [
            'tag' => 'BSc', 'name' => 'Management with Information Technology',
            'desc' => 'Blends business management with IT skills for technology-driven organisational leadership.',
            'code' => 'BMIT',
            'note' => 'Course structure currently published through Level 200 Lower Semester.',
            'courses' => [
                '100' => [
                    'lower' => [['WBC101','Fundamentals of Management Science'],['WBS217','Introduction to Business Management'],['WGS105','Introduction to Sociology'],['WGS113','French I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT']],
                    'upper' => [['WBC101','Fundamentals of Management Science'],['WGS108','Principles of Psychology'],['WGS114','French II'],['WGS128','Communication Skills II'],['WIT211','Principles of Programming'],['WIT301','Management Information Systems'],['WMT107','Logic and Critical Thinking']],
                ],
                '200' => [
                    'lower' => [['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks'],['WBS201','Financial Accounting I'],['WIT214','Programming in Java'],['WIT310','Systems Analysis and Design'],['WMT215','Quantitative Methods I']],
                    'upper' => [],
                ],
            ],
        ],
        [
            'tag' => 'Diploma', 'name' => 'Information Technology',
            'desc' => 'A foundational two-year diploma in practical information technology skills.',
            'code' => 'DIPIT',
            'courses' => [
                '100' => [
                    'lower' => [['ITW307','Computer Architecture & Organization'],['WBS201','Financial Accounting I'],['WGS127','Communication Skills I'],['WIT105','Fundamentals of ICT'],['WIT211','Principles of Programming'],['WMT103','Algebra & Matrices']],
                    'upper' => [['ITW104','Discrete Mathematics'],['ITW310','Multimedia Applications'],['WBS217','Introduction to Business Management'],['WGS128','Communication Skills II'],['WIT150','Intro to Network & the Internet'],['WIT205','Programming in C++']],
                ],
                '200' => [
                    'lower' => [['DIT205','Website Design & Development'],['ITW201','Data Structures and Algorithms'],['ITW207','Modern Operating Systems'],['WIT301','Management Information Systems'],['WIT310','Systems Analysis and Design']],
                    'upper' => [['DIT208','Computer Graphics'],['ITW210','Database Management Systems I'],['ITW303','Data Communications and Networks']],
                ],
            ],
        ],
    ],
    'postgraduate' => [
        [
            'tag' => 'MSc', 'name' => 'Cybersecurity and Digital Forensics',
            'desc' => 'Develops critical skills to analyse and solve cyber security problems, covering the legal, ethical, and technical dimensions of designing and securing modern IT systems.',
            'code' => 'MSCCSDF',
            'courses' => [
                'core' => ['Research Methods and Professional Practice','Operating Systems Theory and Applications','Interactive Programming with Python','Cyber Security and Forensics','Computer Networking Theory, Technologies & Protocols','Data Structures and Complexities of Algorithms','Artificial Intelligence and Machine Learning','Computer Networks and Systems Security','Seminar'],
                'elective' => ['Data Recovery and Digital Forensics Analysis','Ethical Hacking and Penetration Testing','Cryptography Theory and Applications','Information Security','Cyber Intelligence Analysis and Modelling','Mobile Systems Forensics'],
            ],
        ],
        [
            'tag' => 'MSc', 'name' => 'Business Computing',
            'desc' => 'Combines computing expertise with business strategy, equipping graduates to design, evaluate, and implement IT-driven solutions across modern organisations.',
            'code' => 'MSCBC',
            'courses' => [
                'core' => ['Research Methods and Professional Practice','Business Information Systems','Programming for Business Applications','Database Management Systems','Management Information Systems','Data Analytics for Business','Enterprise Systems and Digital Transformation','Project Management for Computing','Seminar'],
                'elective' => ['Business Intelligence and Decision Support Systems','E-Commerce and Digital Business','Information Systems Strategy and Governance','Software Engineering for Business Applications','Human-Computer Interaction','Cloud Computing for Business'],
            ],
        ],
        [
            'tag' => 'MSc', 'name' => 'Information Technology',
            'desc' => 'Advanced knowledge across web technologies, mobile computing, machine learning, data management, cybersecurity, and cloud computing, paired with real-world problem-solving practice.',
            'code' => 'MSCIT',
            'courses' => [
                'core' => ['Research Methods and Professional Practice','Advanced Programming Concepts with Java','Computer Networking Theory, Technologies & Protocols','Artificial Intelligence and Machine Learning','Operating Systems Theory and Applications','Advanced Computer Networks','Management Information Systems','Data Structures and Complexities of Algorithms','Seminar'],
                'elective' => ['Advanced Database Management Systems','Computer Systems & Architecture','Ethical Hacking, Data Recovery and Penetration Testing','Cyber Security & Forensics','Multimedia Systems and Image Processing','Advanced Software and Engineering'],
            ],
        ],
    ],
];

/**
 * Faculty tiers. Real photos are used where available (dean, HOD Business
 * Computing); everywhere else a vacant/"Awaiting Appointment" placeholder is
 * shown, following the same convention as the rest of the site.
 */
$dean = [
    'role'      => 'Dean',
    'name'      => 'Dr. Patrick Kudjo',
    'portfolio' => 'School of Computing and Technology',
    'bio'       => 'Dr. Patrick Kudjo leads the School of Computing and Technology at Wisconsin International University College, overseeing academic direction, faculty, and departmental strategy across all computing programmes.',
    'photo'     => 'faculty/dean-patrick-kudjo.png',
    'email'     => null,
];

$heads = [
    [
        'role'      => 'Head of Department, Business Computing',
        'name'      => 'Charles A. Babbage Jnr.',
        'portfolio' => 'Educator, Researcher & AI Specialist',
        'bio'       => "Charles Jnr. Asiedu, widely known as Babbage, is a seasoned educator and researcher with a distinguished background in Information Technology and Artificial Intelligence.\n\nHe earned his Bachelor of Science in Information Technology from Wisconsin International University College, graduating as the Valedictorian of his class. He subsequently completed his national service at the University of Ghana Business School, contributing to the Operations and Management Information Systems Department.\n\nCharles holds a research-based Master's degree in Engineering from Huzhou University, China, awarded through a competitive merit-based Chinese scholarship for academic excellence. His specialization in Intelligent Information Processing Technology advanced his work in artificial intelligence, culminating in his research thesis titled \"Sentiment Analysis on Twitter Data: A Performance Gap Between Deep Learning and Traditional Machine Learning Algorithms.\"\n\nThroughout his career, he has contributed to curriculum development for CTVET institutions, consulted for various organizations, and published research in respected journals.\n\nHis research interests focus on Artificial Intelligence, Machine Learning applications, and their intersection with Healthcare and Education.",
        'photo'     => 'faculty/hod-charles-babbage-asiedu.jpg',
        'email'     => 'charles.asiedu@wiuc-ghana.edu.gh',
    ],
    [
        'role'      => 'Head of Department, Information Technology',
        'name'      => 'Dr. Richard Amankwah',
        'portfolio' => 'Information Technology',
        'bio'       => 'Dr. Richard Amankwah heads the Information Technology unit at the School of Computing and Technology, overseeing the BSc Information Technology and Diploma in Information Technology programmes.',
        'photo'     => 'faculty/hod-richard-amankwah.jpg',
        'email'     => null,
    ],
];

$lecturers = [
    [
        'role'      => 'Head, IT Support Department',
        'name'      => 'Mr. Edwin Agbah',
        'portfolio' => 'Network Engineer & IT Infrastructure Lead',
        'bio'       => "Mr. Edwin Agbah serves as Head of the IT Support Department and oversees institutional networking and server infrastructure management.\n\nWith extensive Cisco-based networking training, he manages routing, switching, infrastructure security, and enterprise system reliability across campus environments.\n\nHe bridges academic instruction with enterprise-grade systems administration, ensuring high availability and operational continuity.",
        'photo'     => 'faculty/edwin-agbah.jpg',
        'email'     => 'edwin.agbah@wiuc-ghana.edu.gh',
    ],
    [
        'role'      => 'Lecturer, Cloud Computing',
        'name'      => 'Dr. Ian Asare',
        'portfolio' => 'Cloud Architecture Specialist',
        'bio'       => "Dr. Ian Asare is a lecturer at the School of Computing and Technology, specialising in cloud architecture and distributed systems.\n\nHis teaching and research interests centre on cloud infrastructure design, scalable systems, and the deployment models used by modern enterprise platforms.\n\nHe works closely with students on hands on projects that translate cloud computing theory into practical architecture, helping them build the skills needed for cloud engineering roles after graduation.",
        'photo'     => 'faculty/dr-ian-asare.jpg',
        'email'     => null,
    ],
    [
        'role'      => 'Lecturer, Information Systems',
        'name'      => 'Dr. Mateko Okantey',
        'portfolio' => 'Systems Analysis, E-Commerce & E-Business',
        'bio'       => "Dr. Mateko Okantey is a lecturer at the School of Computing and Technology, specialising in systems analysis, e-commerce, and e-business.\n\nHer work focuses on how organisations design, analyse, and implement information systems that support online commerce and digital business operations.\n\nShe guides students through the full systems development lifecycle, from requirements gathering and analysis to designing e-business solutions that meet real organisational needs.",
        'photo'     => 'faculty/dr-mateko-okantey.jpg',
        'email'     => null,
    ],
]; // Add more lecturers directly in this array as names/photos become available.

$galleryPhotos = [
    // Events
    ['photo' => 'gallery/IMG_4617.JPEG', 'caption' => 'SCOTSA Annual General Assembly',   'category' => 'Events'],
    ['photo' => 'gallery/IMG_4620.JPEG', 'caption' => 'Department Welcome Ceremony',      'category' => 'Events'],
    ['photo' => 'gallery/IMG_4625.JPEG', 'caption' => 'SRC Week Opening Ceremony',        'category' => 'Events'],
    ['photo' => 'gallery/IMG_4637.JPEG', 'caption' => 'SCOTSA Community Gathering',       'category' => 'Events'],
    ['photo' => 'gallery/IMG_4742.JPEG', 'caption' => 'End-of-Semester Celebration',      'category' => 'Events'],
    ['photo' => 'gallery/IMG_4849.JPEG', 'caption' => 'SRC Week Cultural Showcase',       'category' => 'Events'],

    // Seminars
    ['photo' => 'gallery/IMG_4833.JPEG', 'caption' => 'Academic Awareness Seminar', 'category' => 'Seminars'],
    ['photo' => 'gallery/IMG_4845.JPEG', 'caption' => 'Department Workshop',        'category' => 'Seminars'],

    // Tech Exhibitions
    ['photo' => 'gallery/IMG_4632.JPEG', 'caption' => 'Technology Exhibition 2024', 'category' => 'Tech Exhibitions'],

    // Orientation
    ['photo' => 'gallery/Orientation_12.JPEG', 'caption' => 'Student Engaged During Orientation',        'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_46.JPEG', 'caption' => 'Connect, Create, Collaborate Campaign',     'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_47.JPEG', 'caption' => 'Faculty Member Addressing Students',        'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_48.JPEG', 'caption' => 'Open Floor Discussion at Orientation',      'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_50.JPEG', 'caption' => 'Student Sharing Remarks at Orientation',    'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_51.JPEG', 'caption' => 'Executive Member Addressing New Students',  'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_52.JPEG', 'caption' => 'SCOTSA Executives Engaging Students',       'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_63.JPEG', 'caption' => 'Student Addressing the Orientation Audience','category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_64.JPEG', 'caption' => 'SCOTSA Executive Speaking at Orientation',  'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_65.JPEG', 'caption' => 'Facilitator Leading a Training Session',    'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_68.JPEG', 'caption' => 'Connect, Create, Collaborate Campaign',     'category' => 'Orientation'],
    ['photo' => 'gallery/Orientation_71.JPEG', 'caption' => 'SCOTSA Orientation Group Photo',            'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7216.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7218.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7219.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7220.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7221.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/IMG_7222.jpg', 'caption' => 'SCOTSA Team at the Orientation Session', 'category' => 'Orientation'],
    ['photo' => 'gallery/DR.IAN ASARE.JPEG',      'caption' => 'Dr. Ian Asare at Orientation',      'category' => 'Orientation'],
    ['photo' => 'gallery/DR.MATEKO OKANTEY.JPEG', 'caption' => 'Dr. Mateko Okantey at Orientation', 'category' => 'Orientation'],
];
$galleryCategories = ['All', 'Events', 'Seminars', 'Tech Exhibitions', 'Orientation'];

$videos = [
    [
        'file'    => 'videos/campus-tour.mp4',
        'poster'  => 'video-posters/campus-tour.jpg',
        'title'   => 'Campus Tour',
        'caption' => 'An aerial look at the WIUC campus, home to the School of Computing and Technology.',
    ],
    [
        'file'    => 'videos/computer-lab-tour.mp4',
        'poster'  => 'video-posters/computer-lab-tour.jpg',
        'title'   => 'Inside the Computer Lab',
        'caption' => 'A look inside one of the department\'s computer labs during a session.',
    ],
];

$studentProjects = [
    [
        'title'      => 'Smart IoT-Based LPG Leakage Detection System',
        'student'    => 'Simon Zotoo',
        'programme'  => 'Final Year Project, BSc Information Technology',
        'supervisor'   => 'Mr Charles A. Babbage Jnr',
        'coSupervisor' => 'Mr. Nathaniel Mills',
        'video'      => 'videos/gas-leak-detection-system.mp4',
        'poster'     => 'video-posters/gas-leak-detection-system.jpg',
        'summary'    => "LPG replaced firewood in most Ghanaian kitchens in the 1990s, but most households still rely on smell alone to catch a leak. This project builds a low-cost early-warning system around that gap: an ESP32 microcontroller reads MQ-6 and MQ-2 gas sensors every two seconds, firing a local buzzer, red LED, and LCD warning the instant a leak crosses threshold. At the same time, readings are pushed to a Firebase Realtime Database and surfaced live in a Flutter mobile app; if Wi-Fi is down, a SIM800L GSM module sends an SMS straight to a pre-registered number instead.\n\nIn testing, the local alarm responded within the two-second polling interval, cloud updates arrived within seconds, and SMS delivery took roughly ten to twelve seconds. The system was built to be replicated cheaply in an ordinary Ghanaian household.",
        'stack'      => ['ESP32', 'MQ-6 & MQ-2 Sensors', 'SIM800L GSM', 'Firebase Realtime DB', 'Flutter'],
    ],
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     HERO CAROUSEL
════════════════════════════════════════════════════════════ -->
<section class="hero-grid text-white relative overflow-hidden" id="hero-carousel">

    <div class="hero-slides" aria-hidden="true">
        <?php foreach ($heroSlides as $i => $slide): ?>
        <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
             data-slide
             <?= empty($slide['bgVideo']) ? 'style="background-image:url(\'' . e(IMAGES_URL . '/' . $slide['photo']) . '\')"' : '' ?>
             data-headline="<?= e($slide['headline']) ?>"
             data-subtext="<?= e($slide['subtext']) ?>"
             data-video="">
            <?php if (!empty($slide['bgVideo'])): ?>
            <video class="hero-bg-video" autoplay muted loop playsinline
                   poster="<?= IMAGES_URL ?>/<?= e($slide['photo']) ?>">
                <source src="<?= BASE_URL ?>/assets/<?= e($slide['bgVideo']) ?>" type="video/mp4">
            </video>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <div class="hero-overlay" aria-hidden="true"></div>

    <div class="mx-auto max-w-3xl min-h-[58vh] flex flex-col justify-center gap-10 px-4 py-20 sm:px-6 lg:px-8 relative z-10">

        <div>
            <h1 id="hero-headline" class="max-w-2xl font-heading font-black leading-[1.10] text-white" style="font-size:clamp(2.2rem,5vw,3.7rem);">
                <?= e($heroSlides[0]['headline']) ?>
            </h1>

            <p id="hero-subtext" class="mt-6 max-w-xl text-base leading-8" style="color:rgba(191,219,254,.78);">
                <?= e($heroSlides[0]['subtext']) ?>
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-3">
                <a class="btn-gold" href="#programmes">
                    <?= icon('academic-cap', 'h-4 w-4') ?>
                    Explore Programmes
                </a>
                <a class="btn-outline-white" href="#faculty">Faculty &amp; Leadership</a>
                <button type="button" id="hero-watch-btn" class="btn-outline-white hidden" data-video-embed=""></button>
            </div>
        </div>

        <?php if (count($heroSlides) > 1): ?>
        <div class="flex items-center gap-4">
            <button type="button" class="hero-arrow" id="hero-prev" aria-label="Previous slide">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="hero-dots" id="hero-dots">
                <?php foreach ($heroSlides as $i => $slide): ?>
                <button type="button" class="hero-dot <?= $i === 0 ? 'active' : '' ?>" data-dot="<?= $i ?>" aria-label="Go to slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <button type="button" class="hero-arrow" id="hero-next" aria-label="Next slide">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Video modal: iframe is only inserted once a viewer taps "Watch" -->
<div id="video-modal" class="lightbox" role="dialog" aria-modal="true" aria-label="Video">
    <button id="video-modal-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close video">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <div id="video-modal-frame" class="video-frame"></div>
</div>

<!-- One-time section jump strip — replaces a persistent nav bar, since this
     page has no sticky header of its own (see includes/header.php). -->
<nav aria-label="Jump to section" class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-8">
    <div class="flex flex-wrap items-center justify-center gap-2 fade-in">
        <?php foreach ([
            ['About',      '#about'],
            ['Programmes', '#programmes'],
            ['Watch',      '#watch'],
            ['Faculty',    '#faculty'],
            ['Projects',   '#projects'],
            ['Gallery',    '#gallery'],
            ['Contact',    '#contact'],
        ] as [$label, $href]): ?>
        <a class="filter-tab" href="<?= $href ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>
</nav>

<!-- ═══════════════════════════════════════════════════════════
     A MESSAGE FROM THE DEAN
════════════════════════════════════════════════════════════ -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] items-center">
        <div class="fade-in">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl" style="aspect-ratio:4/5;">
                <img src="<?= IMAGES_URL ?>/<?= e($dean['photo']) ?>" alt="<?= e($dean['name']) ?>"
                     class="absolute inset-0 h-full w-full object-cover"
                     style="object-position:50% 10%;"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/avatar.svg'; this.onerror=null;">
            </div>
        </div>

        <div class="fade-in fade-in-delay-2">
            <span class="eyebrow">A Message From the Dean</span>
            <div class="gold-line mt-3 mb-6"></div>
            <blockquote class="text-lg sm:text-xl leading-9 text-ink font-medium">
                &ldquo;Welcome to the School of Computing and Technology. Whether you are just beginning
                your studies or advancing toward a postgraduate degree, our goal is the same: to equip
                you with the knowledge, skills, and confidence to lead in an increasingly digital world.
                Our faculty are committed to your success, both in the classroom and beyond it.&rdquo;
            </blockquote>
            <div class="mt-7 flex items-center gap-4">
                <div class="w-10 h-px" style="background:#D4AF37;"></div>
                <div>
                    <p class="font-heading font-black text-ink"><?= e($dean['name']) ?></p>
                    <p class="text-sm text-slate-500"><?= e($dean['role']) ?>, School of Computing and Technology</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     ABOUT THE DEPARTMENT
════════════════════════════════════════════════════════════ -->
<section id="about" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] items-center">
        <div class="fade-in">
            <span class="eyebrow">About the Department</span>
            <div class="gold-line mt-3 mb-5"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl leading-tight">
                The School of<br>Computing and Technology.
            </h2>
            <p class="mt-5 text-slate-500 leading-8 text-sm">
                SCOT is the academic department at Wisconsin International University College (WIUC),
                Accra, responsible for undergraduate, diploma, and postgraduate programmes in computing,
                information technology, cybersecurity, and artificial intelligence. Our faculty combine
                industry experience with academic rigour to prepare graduates for real careers in technology.
            </p>
            <p class="mt-4 text-slate-500 leading-8 text-sm">
                SCOTSA, the department's student association, runs academic resources, events, and
                community programmes that support every student's journey through SCOT.
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn-primary" href="#programmes">View Programmes</a>
                <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50 hover:!border-slate-400" href="#faculty">Faculty &amp; Leadership</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-1 lg:gap-3 fade-in fade-in-delay-2">
            <?php foreach ([
                ['Academic Excellence', 'Undergraduate, diploma, and postgraduate programmes across computing, IT, cybersecurity, and AI.'],
                ['Experienced Faculty', 'Lecturers and department leadership combining academic depth with industry experience.'],
                ['Built to Grow',       'A department expanding its programmes, research, and student services year over year.'],
            ] as [$title, $body]): ?>
            <div class="card-hover rounded-xl border border-slate-200 bg-white p-5 border-l-4 border-l-scotsaBlue">
                <h3 class="font-heading font-bold text-scotsaBlue"><?= $title ?></h3>
                <p class="mt-1.5 text-sm leading-6 text-slate-500"><?= $body ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     PROGRAMMES
════════════════════════════════════════════════════════════ -->
<section id="programmes" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">What We Offer</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Programmes</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Undergraduate, diploma, and postgraduate programmes offered by the School of Computing and Technology.
            Tap a card to view its course structure by level and semester.
        </p>
    </div>

    <!-- Undergraduate & Diploma -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Undergraduate &amp; Diploma</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-14">
        <?php foreach ($programmes['undergraduate'] as $i => $prog): ?>
        <button type="button"
                class="prog-card rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="<?= e($prog['note'] ?? '') ?>">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue">
                View Course Structure
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </button>

        <!-- Hidden course-structure content, pulled into #course-modal-body on click -->
        <div class="course-structure-content hidden" data-course-content="<?= e($prog['code']) ?>">
            <?php foreach ($prog['courses'] as $level => $sems): ?>
            <div class="mb-7">
                <h5 class="font-heading font-black text-ink text-sm uppercase tracking-wide mb-3">Level <?= e($level) ?></h5>
                <div class="grid gap-5 sm:grid-cols-2">
                    <?php foreach (['lower' => 'Lower Semester', 'upper' => 'Upper Semester'] as $sem => $semLabel): ?>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2"><?= $semLabel ?></p>
                        <?php if ($sems[$sem]): ?>
                        <ul class="space-y-1.5">
                            <?php foreach ($sems[$sem] as [$code, $title]): ?>
                            <li class="text-sm text-slate-600"><span class="font-bold text-scotsaBlue"><?= e($code) ?></span> &mdash; <?= e($title) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-sm text-slate-400 italic">Not yet published.</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Postgraduate -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Postgraduate</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($programmes['postgraduate'] as $i => $prog): ?>
        <button type="button"
                class="prog-card prog-card--postgrad rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue">
                View Course Structure
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </button>

        <!-- Hidden course-structure content (Core / Elective), pulled into #course-modal-body on click -->
        <div class="course-structure-content hidden" data-course-content="<?= e($prog['code']) ?>">
            <?php foreach (['core' => 'Core Courses', 'elective' => 'Elective Courses'] as $group => $groupLabel): ?>
            <div class="mb-7">
                <h5 class="font-heading font-black text-ink text-sm uppercase tracking-wide mb-3"><?= $groupLabel ?></h5>
                <ul class="space-y-1.5">
                    <?php foreach ($prog['courses'][$group] as $courseName): ?>
                    <li class="text-sm text-slate-600"><?= e($courseName) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Course structure modal — content is swapped in per-programme on click -->
<div id="course-modal" class="lightbox" role="dialog" aria-modal="true" aria-label="Course structure">
    <button id="course-modal-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <div class="course-modal-card">
        <div class="course-modal-header">
            <span id="course-modal-tag" class="eyebrow"></span>
            <h3 id="course-modal-title" class="font-heading font-black text-2xl text-ink mt-2"></h3>
            <p id="course-modal-note" class="mt-1 text-sm text-slate-400 hidden"></p>
        </div>
        <div id="course-modal-body" class="course-modal-body"></div>
    </div>
</div>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     WATCH — video showcase
════════════════════════════════════════════════════════════ -->
<section id="watch" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">See It For Yourself</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Watch</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            A closer look at campus and department life at the School of Computing and Technology.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <?php foreach ($videos as $i => $vid): ?>
        <div class="fade-in fade-in-delay-<?= $i + 1 ?>">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:16/9;">
                <video controls preload="none" playsinline
                       poster="<?= IMAGES_URL ?>/<?= e($vid['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($vid['file']) ?>" type="video/mp4">
                </video>
            </div>
            <p class="mt-4 font-heading font-bold text-ink"><?= e($vid['title']) ?></p>
            <p class="mt-1 text-sm leading-6 text-slate-500"><?= e($vid['caption']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     FACULTY — Heads of Department, Lecturers
     (the Dean has his own spotlight section right after the hero)
════════════════════════════════════════════════════════════ -->
<section id="faculty" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-14 fade-in">
        <span class="eyebrow">Faculty</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink">Heads of Department and Lecturers.</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            The faculty of the School of Computing and Technology, responsible for teaching,
            research, and academic leadership across all programmes. Select a profile to view
            a full biography.
        </p>
    </div>

    <?php
    // Shared card renderer for heads of department / lecturers.
    function scot_faculty_card(array $f): void {
        $displayName = $f['name'] ?: 'Awaiting Appointment';
        $photoUrl    = $f['photo'] ? IMAGES_URL . '/' . $f['photo'] : avatar_url(null, $displayName);
    ?>
    <button type="button"
            class="card-hover group rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in text-left w-full"
            data-faculty-trigger
            data-name="<?= e($displayName) ?>"
            data-role="<?= e($f['role']) ?><?= $f['portfolio'] ? ' — ' . e($f['portfolio']) : '' ?>"
            data-portfolio="<?= e($f['portfolio'] ?? '') ?>"
            data-bio="<?= e($f['bio'] ?? 'Biography coming soon.') ?>"
            data-photo="<?= e($photoUrl) ?>"
            data-email="<?= e($f['email'] ?? '') ?>">
        <div class="relative overflow-hidden bg-slate-100" style="padding-top:115%;">
            <img src="<?= e($photoUrl) ?>" alt="<?= e($displayName) ?>"
                 class="absolute inset-0 h-full w-full object-cover"
                 style="object-position:50% 15%;"
                 loading="lazy"
                 onerror="this.src='<?= IMAGES_URL ?>/placeholders/avatar.svg'; this.onerror=null;">
        </div>
        <div class="p-5 text-center">
            <p class="font-heading font-bold text-ink text-sm"><?= e($displayName) ?></p>
            <p class="mt-1 text-xs font-semibold text-scotsaBlue"><?= e($f['role']) ?></p>
            <?php if ($f['portfolio']): ?>
            <p class="mt-1 text-xs text-slate-400 leading-5"><?= e($f['portfolio']) ?></p>
            <?php endif; ?>
        </div>
    </button>
    <?php } ?>

    <!-- Heads of Department -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Heads of Department</span>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 max-w-3xl mx-auto mb-14">
        <?php foreach ($heads as $head): scot_faculty_card($head); ?>
        <?php endforeach; ?>
    </div>

    <!-- Lecturers -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Lecturers</span>
    </div>
    <?php if ($lecturers): ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($lecturers as $lecturer): scot_faculty_card($lecturer); ?>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center fade-in max-w-lg mx-auto">
        <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl" style="background:rgba(10,31,68,.06);">
            <?= icon('users', 'h-7 w-7 text-slate-400') ?>
        </div>
        <h3 class="font-heading font-bold text-lg text-ink mb-2">Lecturer profiles coming soon</h3>
        <p class="text-sm text-slate-400 leading-6">
            Full lecturer profiles for the School of Computing and Technology will appear here once published.
        </p>
    </div>
    <?php endif; ?>
</section>

<!-- Faculty bio modal -->
<div id="faculty-modal" class="lightbox" role="dialog" aria-modal="true" aria-label="Faculty biography">
    <button id="faculty-modal-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <button id="faculty-modal-prev"
            class="absolute left-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Previous faculty member">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button id="faculty-modal-next"
            class="absolute right-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Next faculty member">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <div class="bio-modal-card">
        <img id="faculty-modal-photo" src="" alt="">
        <div class="bio-modal-text">
            <p id="faculty-modal-portfolio" class="eyebrow"></p>
            <h3 id="faculty-modal-name" class="font-heading font-black text-2xl text-ink mt-3"></h3>
            <p id="faculty-modal-role" class="mt-1 text-sm font-semibold text-scotsaBlue"></p>
            <a id="faculty-modal-email" href="" class="mt-2 hidden items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-scotsaBlue transition-colors">
                <?= icon('mail', 'h-3.5 w-3.5') ?>
                <span id="faculty-modal-email-text"></span>
            </a>
            <p id="faculty-modal-bio" class="mt-4 text-sm leading-7 text-slate-500 whitespace-pre-line"></p>
        </div>
    </div>
</div>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     STUDENT PROJECTS — real work by SCOT students
════════════════════════════════════════════════════════════ -->
<section id="projects" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-14 fade-in">
        <span class="eyebrow">From the Department</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Student Projects</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Final year and capstone work built by SCOT students.
        </p>
    </div>

    <?php foreach ($studentProjects as $project): ?>
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] items-start">
        <div class="mx-auto w-full max-w-sm fade-in">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:9/16;">
                <video controls preload="none" playsinline
                       poster="<?= IMAGES_URL ?>/<?= e($project['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($project['video']) ?>" type="video/mp4">
                </video>
            </div>
        </div>

        <div class="fade-in fade-in-delay-1">
            <h3 class="font-heading font-black text-3xl text-ink leading-snug"><?= e($project['title']) ?></h3>
            <p class="mt-3 text-base font-semibold text-scotsaBlue">
                <?= e($project['student']) ?> &middot; <?= e($project['programme']) ?>
            </p>
            <p class="mt-2 text-lg font-bold text-ink">
                Supervised by <?= e($project['supervisor']) ?>
                <?php if (!empty($project['coSupervisor'])): ?> &amp; <?= e($project['coSupervisor']) ?><?php endif; ?>
            </p>

            <p class="mt-6 text-base leading-8 text-slate-500 whitespace-pre-line"><?= e($project['summary']) ?></p>

            <div class="mt-7 flex flex-wrap gap-2.5">
                <?php foreach ($project['stack'] as $tech): ?>
                <span class="rounded-full border border-slate-200 px-3.5 py-1.5 text-sm font-semibold text-slate-600"><?= e($tech) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     GALLERY — campus moments
════════════════════════════════════════════════════════════ -->
<section id="gallery" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Campus Life</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Gallery</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Moments from department events, orientation, seminars, and tech exhibitions.
        </p>
    </div>

    <div class="mb-8 flex gap-2 overflow-x-auto no-scrollbar fade-in">
        <?php foreach ($galleryCategories as $i => $cat): ?>
        <button type="button" class="filter-tab flex-shrink-0 <?= $i === 0 ? 'filter-active' : '' ?>" data-gallery-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="gallery-grid">
        <?php foreach ($galleryPhotos as $photo):
            $fullsize = IMAGES_URL . '/' . $photo['photo'];
        ?>
        <div class="gallery-item fade-in"
             data-gallery-category="<?= e($photo['category']) ?>"
             data-gallery-lightbox="<?= e($fullsize) ?>"
             data-gallery-caption="<?= e($photo['caption']) ?>">
            <img src="<?= e($fullsize) ?>"
                 alt="<?= e($photo['caption']) ?>"
                 loading="lazy"
                 onerror="this.src='<?= IMAGES_URL ?>/placeholders/default.svg'; this.onerror=null;">
            <div class="gallery-overlay">
                <div>
                    <p class="font-heading font-bold text-white text-sm leading-snug"><?= e($photo['caption']) ?></p>
                    <p class="text-xs mt-1" style="color:rgba(191,219,254,.65);"><?= e($photo['category']) ?></p>
                </div>
            </div>
            <div class="gallery-expand">
                <div class="grid h-8 w-8 place-items-center rounded-lg" style="background:rgba(0,0,0,.45); backdrop-filter:blur(4px);">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                    </svg>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Gallery photo lightbox -->
<div id="gallery-lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Photo preview">
    <button id="gallery-lightbox-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <button id="gallery-lightbox-prev"
            class="absolute left-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Previous image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button id="gallery-lightbox-next"
            class="absolute right-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Next image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>
    <img id="gallery-lightbox-img" src="" alt="" class="select-none">
    <p id="gallery-lightbox-caption" class="mt-4 text-sm font-medium text-center max-w-lg" style="color:rgba(191,219,254,.65);"></p>
</div>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     CONTACT
════════════════════════════════════════════════════════════ -->
<section id="contact" class="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8 scroll-mt-20">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Get in Touch</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Contact the department</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Reach out through your preferred channel, or visit the department office on campus.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 mb-6">
        <a href="mailto:scotsawiuc@gmail.com" class="contact-card group fade-in">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Email</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">scotsawiuc@gmail.com</p>
            </div>
        </a>

        <a href="https://chat.whatsapp.com/Cn2b43LoXOH1WGCg0uBaNR?s=cl&p=i&mlu=4" target="_blank" rel="noopener" class="contact-card group fade-in fade-in-delay-1">
            <div class="social-icon flex-shrink-0" style="background:rgba(37,211,102,.12); color:#1DA851;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">WhatsApp</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">Join the community</p>
            </div>
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 overflow-hidden fade-in fade-in-delay-2" style="min-height:280px;">
        <iframe
            src="https://www.google.com/maps?q=5.670433,-0.1893348&z=16&output=embed"
            class="w-full border-0"
            style="min-height:320px;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Map showing Wisconsin International University College, Ghana">
        </iframe>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
