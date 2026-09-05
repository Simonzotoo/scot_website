<?php
require_once __DIR__ . '/includes/db.php';

/**
 * Every piece of department content below is now pulled from MySQL via
 * the admin dashboard (see admin/), not hardcoded. Each block reshapes
 * DB rows into the exact same associative-array shape the template
 * further down the file already expects — nothing below this point
 * changes as a result of this cutover.
 */
$pdo = db();

$siteSettings = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$setting = static fn (string $key, string $default = '') => $siteSettings[$key] ?? $default;

$pageTitle = $setting('site_meta_title', 'School of Computing and Technology (SCOT), WIUC Ghana');

$heroSlides = array_map(static function (array $r): array {
    $slide = [
        'photo'    => $r['photo_path'],
        'headline' => $r['headline'],
        'subtext'  => $r['subtext'],
    ];
    if (!empty($r['photo_position'])) {
        $slide['photoPosition'] = $r['photo_position'];
    }
    return $slide;
}, $pdo->query("SELECT * FROM hero_slides WHERE status='active' ORDER BY sort_order")->fetchAll());

/**
 * Programmes + course structures. 'undergraduate' holds BSc and Diploma
 * rows together (the template itself filters by tag when it needs one or
 * the other) to match how the two groups render from one array; 'courses'
 * is keyed by level ('100'..'400') with 'lower'/'upper' semester lists of
 * [code,title] pairs for BSc/Diploma, or by 'core'/'elective' plain-title
 * lists for MSc. Short Courses carry no 'courses' key at all.
 */
function scot_load_programmes(PDO $pdo): array
{
    $out = ['undergraduate' => [], 'postgraduate' => [], 'certificate' => []];

    $progRows = $pdo->query("SELECT * FROM programs ORDER BY FIELD(tag,'BSc','Diploma','MSc','Short Courses'), sort_order")->fetchAll();
    $courseStmt = $pdo->prepare('SELECT * FROM courses WHERE program_id = ? ORDER BY sort_order');
    $levelNames = [1 => '100', 2 => '200', 3 => '300', 4 => '400'];
    $semNames = [1 => 'lower', 2 => 'upper'];

    foreach ($progRows as $p) {
        if ($p['tag'] === 'Short Courses') {
            $out['certificate'][] = ['tag' => 'Short Courses', 'name' => $p['name']];
            continue;
        }

        $entry = [
            'tag'  => $p['tag'],
            'name' => $p['name'],
            'desc' => $p['description'],
            'code' => strtoupper($p['slug']),
        ];
        if (!empty($p['note'])) {
            $entry['note'] = $p['note'];
        }

        $courseStmt->execute([$p['id']]);
        $rows = $courseStmt->fetchAll();

        if ($p['tag'] === 'MSc') {
            $courses = ['core' => [], 'elective' => []];
            foreach ($rows as $c) {
                if (isset($courses[$c['group_label']])) {
                    $courses[$c['group_label']][] = $c['title'];
                }
            }
            $entry['courses'] = $courses;
            $out['postgraduate'][] = $entry;
        } else {
            $courses = [];
            foreach ($rows as $c) {
                $levelKey = $levelNames[(int) $c['level_id']] ?? null;
                $semKey = $semNames[(int) $c['semester_id']] ?? null;
                if ($levelKey === null || $semKey === null) {
                    continue;
                }
                $courses[$levelKey][$semKey][] = [$c['code'], $c['title']];
            }
            // Ensure every level present in the data has both semester
            // keys defined (even if empty), matching the original
            // hand-written arrays the template was built against.
            foreach ($courses as $levelKey => &$sems) {
                $sems += ['lower' => [], 'upper' => []];
            }
            unset($sems);
            $entry['courses'] = $courses;
            $out['undergraduate'][] = $entry;
        }
    }

    return $out;
}
$programmes = scot_load_programmes($pdo);

/**
 * Faculty. 'leadership' roster = Dean + Heads (rendered together on the
 * public page); 'faculty' roster = Lecturers. Each member's three list
 * tabs (courses taught / publications / education) come from their own
 * child tables; research_interest is a single column.
 */
function scot_load_faculty_member(PDO $pdo, array $r): array
{
    $fetch = static function (string $table) use ($pdo, $r): array {
        $stmt = $pdo->prepare("SELECT text_value FROM `$table` WHERE member_id = ? ORDER BY sort_order");
        $stmt->execute([$r['id']]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    };

    return [
        'role'              => $r['role'],
        'name'              => $r['name'],
        'portfolio'         => $r['portfolio'],
        'bio'               => $r['bio'],
        'photo'             => $r['photo_path'],
        'email'             => $r['email'],
        'courses_taught'    => $fetch('faculty_courses_taught'),
        'research_interest' => $r['research_interest'],
        'publications'      => $fetch('faculty_publications'),
        'education'         => $fetch('faculty_education'),
    ];
}

$leadershipRows = $pdo->query("SELECT * FROM team_members WHERE roster='leadership' ORDER BY sort_order")->fetchAll();
$dean = null;
$heads = [];
foreach ($leadershipRows as $i => $r) {
    $member = scot_load_faculty_member($pdo, $r);
    if ($i === 0) {
        $dean = $member;
    } else {
        $heads[] = $member;
    }
}
$lecturers = array_map(
    static fn (array $r) => scot_load_faculty_member($pdo, $r),
    $pdo->query("SELECT * FROM team_members WHERE roster='faculty' ORDER BY sort_order")->fetchAll()
);

$galleryPhotos = array_map(static fn (array $r) => [
    'photo'    => $r['photo_path'],
    'caption'  => $r['caption'],
    'category' => $r['category'],
], $pdo->query("SELECT * FROM gallery_items WHERE status='active' ORDER BY sort_order")->fetchAll());

$galleryCategories = array_merge(
    ['All'],
    $pdo->query('SELECT name FROM gallery_categories ORDER BY sort_order')->fetchAll(PDO::FETCH_COLUMN)
);

$videos = array_map(static fn (array $r) => [
    'file'    => $r['file_path'],
    'poster'  => $r['poster_path'],
    'title'   => $r['title'],
    'caption' => $r['caption'],
], $pdo->query("SELECT * FROM videos WHERE status='active' ORDER BY sort_order")->fetchAll());

$blogPosts = array_map(static function (array $r) use ($pdo): array {
    $photoStmt = $pdo->prepare('SELECT photo_path AS photo, caption, featured FROM blog_post_photos WHERE post_id = ? ORDER BY sort_order');
    $photoStmt->execute([$r['id']]);
    $photos = array_map(static function (array $p): array {
        $photo = ['photo' => $p['photo'], 'caption' => $p['caption']];
        if ($p['featured']) {
            $photo['featured'] = true;
        }
        return $photo;
    }, $photoStmt->fetchAll());

    return [
        'title'   => $r['title'],
        'date'    => $r['post_date'],
        'excerpt' => $r['excerpt'],
        'photos'  => $photos,
        'video'   => $r['video_path'],
        'poster'  => $r['poster_path'],
    ];
}, $pdo->query("SELECT * FROM blog_posts WHERE status='published' ORDER BY sort_order")->fetchAll());

$studentProjects = array_map(static function (array $r) use ($pdo): array {
    $stackStmt = $pdo->prepare('SELECT tech_text FROM student_project_stack WHERE project_id = ? ORDER BY sort_order');
    $stackStmt->execute([$r['id']]);

    return [
        'title'        => $r['title'],
        'student'      => $r['student_name'],
        'programme'    => $r['programme_label'],
        'supervisor'   => $r['supervisor'],
        'coSupervisor' => $r['co_supervisor'],
        'video'        => $r['video_path'],
        'poster'       => $r['poster_path'],
        'summary'      => $r['summary'],
        'stack'        => $stackStmt->fetchAll(PDO::FETCH_COLUMN),
    ];
}, $pdo->query("SELECT * FROM student_projects WHERE status='active' ORDER BY sort_order")->fetchAll());

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
             <?= empty($slide['bgVideo']) ? 'style="background-image:url(\'' . e(IMAGES_URL . '/' . $slide['photo']) . '\');' . (!empty($slide['photoPosition']) ? 'background-position:' . e($slide['photoPosition']) . ';' : '') . '"' : '' ?>
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

<!-- Video modal for a hero slide's optional "Watch" trigger -->
<div id="video-modal" class="lightbox" role="dialog" aria-modal="true" aria-label="Video">
    <button id="video-modal-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <div id="video-modal-frame" class="video-frame"></div>
</div>

<!-- One-time section jump strip — replaces a persistent nav bar, since this
     page has no sticky header of its own (see includes/header.php). -->
<nav aria-label="Jump to section" class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 pt-8">
    <div class="mb-8 flex flex-wrap gap-2 fade-in rounded-full bg-white/90 dark:bg-[#0b1120]/90 backdrop-blur-md shadow-lg border border-white/60 dark:border-white/10 px-3 py-2.5">
        <?php foreach ([
            ['About',      '#about'],
            ['Programmes', '#programmes'],
            ['Facilities', '#facilities'],
            ['Watch',      '#watch'],
            ['Faculty',    '#faculty'],
            ['Projects',   '#projects'],
            ['Blog',       '#blog'],
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
<section class="section-plain py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
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
            <span class="eyebrow" style="font-size:1.125rem; letter-spacing:.08em;">A Message From the Dean</span>
            <div class="gold-line mt-3 mb-6"></div>
            <blockquote class="text-lg sm:text-xl leading-9 text-ink font-medium">
                &ldquo;<?= e($setting('dean_message')) ?>&rdquo;
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
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     ABOUT THE DEPARTMENT
════════════════════════════════════════════════════════════ -->
<section id="about" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] items-center">
        <div class="fade-in">
            <span class="eyebrow">About the Department</span>
            <div class="gold-line mt-3 mb-5"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl leading-tight">
                The School of<br>Computing and Technology.
            </h2>
            <p class="mt-5 text-slate-500 leading-8 text-sm">
                <?= e($setting('about_paragraph_1')) ?>
            </p>
            <p class="mt-4 text-slate-500 leading-8 text-sm">
                <?= e($setting('about_paragraph_2')) ?>
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn-primary" href="#programmes">View Programmes</a>
                <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50 hover:!border-slate-400" href="#faculty">Faculty &amp; Leadership</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-1 lg:gap-3 fade-in fade-in-delay-2">
            <?php foreach ([
                [$setting('about_feature_1_title'), $setting('about_feature_1_body')],
                [$setting('about_feature_2_title'), $setting('about_feature_2_body')],
                [$setting('about_feature_3_title'), $setting('about_feature_3_body')],
            ] as [$title, $body]): ?>
            <div class="card-hover rounded-xl border border-slate-200 bg-white p-5 border-l-4 border-l-scotsaBlue">
                <h3 class="font-heading font-bold text-scotsaBlue"><?= e($title) ?></h3>
                <p class="mt-1.5 text-sm leading-6 text-slate-500"><?= e($body) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     PROGRAMMES
════════════════════════════════════════════════════════════ -->
<section id="programmes" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">What We Offer</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Programmes</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Undergraduate, diploma, and postgraduate programmes offered by the School of Computing and Technology.
            Tap a card to view its course structure by level and semester.
        </p>
    </div>

    <div class="mb-8 flex flex-wrap gap-2 fade-in">
        <?php foreach (['All', 'BSc', 'MSc', 'Diploma', 'Short Courses'] as $i => $filterTag): ?>
        <button type="button" class="filter-tab flex-shrink-0 <?= $i === 0 ? 'filter-active' : '' ?>" data-programme-filter="<?= e($filterTag) ?>"><?= e($filterTag) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Undergraduate (BSc) — a bento grid: BSc IT (the only programme
         with a complete Level 100-400 curriculum published) gets a wider
         featured tile; grid-auto-flow:dense packs the rest around it with
         no gaps. -->
    <div data-programme-group>
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Undergraduate</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-14" style="grid-auto-flow:dense;">
        <?php
        $bscProgrammes = array_values(array_filter($programmes['undergraduate'], fn($p) => $p['tag'] === 'BSc'));
        foreach ($bscProgrammes as $i => $prog):
            $isFeatured = $i === 0;
        ?>
        <button type="button"
                class="prog-card rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?> <?= $isFeatured ? 'sm:col-span-2 lg:row-span-2' : '' ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="<?= e($prog['note'] ?? '') ?>"
                data-programme-category="<?= e($prog['tag']) ?>">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2 <?= $isFeatured ? 'text-xl' : '' ?>"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <?php if ($isFeatured): ?>
            <div class="mt-5 pt-5 border-t border-slate-100 flex items-center gap-2 text-xs font-semibold text-slate-500">
                <?= icon('academic-cap', 'h-4 w-4 text-scotsaBlue') ?>
                Complete curriculum published — Level 100 through Level 400
            </div>
            <?php endif; ?>
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
                    <?php foreach (['lower' => 'First Semester', 'upper' => 'Second Semester'] as $sem => $semLabel): ?>
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
    </div>

    <div data-programme-group>
    <!-- Postgraduate -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Postgraduate</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($programmes['postgraduate'] as $i => $prog): ?>
        <button type="button"
                class="prog-card prog-card--postgrad rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note=""
                data-programme-category="<?= e($prog['tag']) ?>">
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
    </div>

    <div data-programme-group>
    <!-- Diploma -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Diploma</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php
        $diplomaProgrammes = array_values(array_filter($programmes['undergraduate'], fn($p) => $p['tag'] === 'Diploma'));
        foreach ($diplomaProgrammes as $i => $prog):
        ?>
        <button type="button"
                class="prog-card rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="<?= e($prog['note'] ?? '') ?>"
                data-programme-category="<?= e($prog['tag']) ?>">
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
                    <?php foreach (['lower' => 'First Semester', 'upper' => 'Second Semester'] as $sem => $semLabel): ?>
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
    </div>

    <div data-programme-group>
    <!-- Short Courses — short standalone courses, so these are plain
         (non-clickable) cards with no course-structure modal, unlike the
         degree programme cards above. -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Short Courses</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($programmes['certificate'] as $i => $prog): ?>
        <div class="prog-card rounded-xl border border-slate-200 bg-white p-6 fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
             data-programme-category="<?= e($prog['tag']) ?>">
            <h4 class="font-heading font-bold text-ink leading-snug text-sm"><?= e($prog['name']) ?></h4>
        </div>
        <?php endforeach; ?>
    </div>
    </div>
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

<!-- ═══════════════════════════════════════════════════════════
     FACILITIES — the department's labs, in a bento grid; tiles
     reuse the gallery lightbox (data-gallery-lightbox) so clicking
     one opens the same full-size viewer as the Gallery section.
════════════════════════════════════════════════════════════ -->
<section id="facilities" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Where You'll Learn</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Our Facilities</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Purpose-built labs for computing, cybersecurity, digital forensics, and robotics &amp; AI.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4" style="grid-auto-flow:dense;">
        <?php
        $facilityTiles = array_map(static fn (array $r) => [
            'photo'    => $r['photo_path'],
            'caption'  => $r['caption'],
            'featured' => (bool) $r['featured'],
        ], $pdo->query("SELECT * FROM facilities WHERE status='active' ORDER BY sort_order")->fetchAll());
        foreach ($facilityTiles as $i => $tile):
            $fullsize = IMAGES_URL . '/' . $tile['photo'];
            $featured = !empty($tile['featured']);
        ?>
        <div class="facility-tile fade-in fade-in-delay-<?= ($i % 3) + 1 ?> <?= $featured ? 'sm:col-span-2 lg:col-span-2 lg:row-span-2' : '' ?>"
             data-gallery-lightbox="<?= e($fullsize) ?>"
             data-gallery-caption="<?= e($tile['caption']) ?>"
             <?= !$featured ? 'style="aspect-ratio:4/3;"' : '' ?>>
            <img src="<?= e($fullsize) ?>"
                 alt="<?= e($tile['caption']) ?>"
                 loading="lazy"
                 onerror="this.src='<?= IMAGES_URL ?>/placeholders/default.svg'; this.onerror=null;">
            <div class="gallery-overlay">
                <div>
                    <p class="font-heading font-bold text-white text-sm leading-snug"><?= e($tile['caption']) ?></p>
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
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     WATCH — video showcase
════════════════════════════════════════════════════════════ -->
<section id="watch" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">See It For Yourself</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Watch</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            A closer look at campus and department life at the School of Computing and Technology.
        </p>
    </div>

    <div class="flex flex-wrap justify-center gap-8">
        <?php foreach ($videos as $i => $vid): ?>
        <div class="fade-in fade-in-delay-<?= $i + 1 ?> w-full sm:w-[calc(50%-1rem)]">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:16/9;">
                <video controls preload="none" playsinline
                       poster="<?= IMAGES_URL ?>/<?= e($vid['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($vid['file']) ?>" type="video/mp4">
                </video>
            </div>
            <p class="mt-4 font-heading font-bold text-lg text-ink"><?= e($vid['title']) ?></p>
            <p class="mt-1 text-sm leading-6 text-slate-500"><?= e($vid['caption']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FACULTY — Heads of Department, Lecturers
════════════════════════════════════════════════════════════ -->
<section id="faculty" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Faculty</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Heads of Department and Lecturers</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Tap a photo to view a full profile — courses taught, research interests, publications, and education.
        </p>
    </div>

    <?php
    // Shared card renderer for heads of department / lecturers. Clicking a
    // card opens the full-page profile (#faculty-page), not a small modal —
    // this function renders the trigger button plus a hidden content block
    // holding that person's five profile panels (Overview / Courses Taught /
    // Research Interest / Publications / Education), which JS copies into
    // the shared panel containers when the page opens.
    function scot_faculty_card(array $f): void {
        static $facultyIdCounter = 0;
        $id = 'faculty-' . (++$facultyIdCounter);

        $displayName = $f['name'] ?: 'Awaiting Appointment';
        $photoUrl    = $f['photo'] ? IMAGES_URL . '/' . $f['photo'] : avatar_url(null, $displayName);
    ?>
    <button type="button"
            class="card-hover group rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in text-left w-full"
            data-faculty-trigger="<?= $id ?>"
            data-name="<?= e($displayName) ?>"
            data-role="<?= e($f['role']) ?>"
            data-portfolio="<?= e($f['portfolio'] ?? '') ?>"
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

    <div class="hidden" data-faculty-content="<?= $id ?>">
        <div data-panel="overview">
            <p class="text-sm leading-7 text-slate-600 whitespace-pre-line"><?= e($f['bio'] ?: 'Biography coming soon.') ?></p>
        </div>
        <div data-panel="courses">
            <?php if (!empty($f['courses_taught'])): ?>
            <ul class="space-y-2.5">
                <?php foreach ($f['courses_taught'] as $course): ?>
                <li class="text-sm text-slate-600 flex items-start gap-2">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($course) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Course list to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="research">
            <?php if (!empty($f['research_interest'])): ?>
            <p class="text-sm leading-7 text-slate-600"><?= e($f['research_interest']) ?></p>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Research interests to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="publications">
            <?php if (!empty($f['publications'])): ?>
            <ul class="space-y-3">
                <?php foreach ($f['publications'] as $pub): ?>
                <li class="text-sm leading-6 text-slate-600 flex items-start gap-2">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($pub) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Publications to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="education">
            <?php if (!empty($f['education'])): ?>
            <ul class="space-y-3">
                <?php foreach ($f['education'] as $edu): ?>
                <li class="text-sm leading-6 text-slate-600 flex items-start gap-2">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($edu) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Educational background to be added.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php } ?>

    <!-- Dean & Heads of Department — the Dean also lectures, so he's listed
         here too (first) with the same clickable profile, in addition to
         his own spotlight section near the top of the page. -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Dean &amp; Heads of Department</span>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 max-w-3xl mx-auto mb-14">
        <?php foreach (array_merge([$dean], $heads) as $head): scot_faculty_card($head); ?>
        <?php endforeach; ?>
    </div>

    <!-- Lecturers -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Lecturers</span>
    </div>
    <?php if ($lecturers): ?>
    <div class="flex flex-wrap justify-center gap-6">
        <?php foreach ($lecturers as $lecturer): ?>
        <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.125rem)]">
            <?php scot_faculty_card($lecturer); ?>
        </div>
        <?php endforeach; ?>
        <?php
        // Open seats — a plain "vacant" tile, not a clickable fake profile.
        // Auto-sized to round the grid up to a clean multiple of 4 (so it
        // shrinks on its own as real lecturers are added via the admin
        // dashboard, down to zero once the roster fills a full row).
        $lecturerPlaceholders = (4 - (count($lecturers) % 4)) % 4;
        for ($i = 0; $i < $lecturerPlaceholders; $i++):
        ?>
        <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.125rem)]">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 overflow-hidden fade-in h-full">
                <div class="relative overflow-hidden" style="padding-top:115%;">
                    <div class="absolute inset-0 grid place-items-center">
                        <?= icon('user-circle', 'h-12 w-12 text-slate-300') ?>
                    </div>
                </div>
                <div class="p-5 text-center">
                    <p class="font-heading font-bold text-slate-400 text-sm">New Lecturer <?= $i + 1 ?></p>
                </div>
            </div>
        </div>
        <?php endfor; ?>
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
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FACULTY PROFILE PAGE — full-viewport takeover
════════════════════════════════════════════════════════════ -->
<div id="faculty-page">
    <div class="faculty-page-inner">
        <div class="faculty-page-topbar">
            <button type="button" id="faculty-page-close" class="faculty-page-back" aria-label="Back">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </button>
            <div class="flex items-center gap-3">
                <button type="button" id="faculty-page-prev" class="faculty-page-nav-btn" aria-label="Previous">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" id="faculty-page-next" class="faculty-page-nav-btn" aria-label="Next">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="faculty-page-header">
            <img id="faculty-page-photo" src="" alt=""
                 onerror="this.src='<?= IMAGES_URL ?>/placeholders/avatar.svg'; this.onerror=null;">
            <div>
                <span class="eyebrow">School of Computing and Technology</span>
                <h2 id="faculty-page-name" class="font-heading font-black text-3xl text-ink mt-2"></h2>
                <p id="faculty-page-role" class="mt-1 text-sm font-bold text-scotsaBlue"></p>
                <p id="faculty-page-portfolio" class="mt-1 text-sm text-slate-500"></p>
                <a id="faculty-page-email" href="" class="mt-4 hidden items-center gap-1.5 text-xs font-bold text-scotsaBlue">
                    <?= icon('mail', 'h-3.5 w-3.5') ?>
                    <span id="faculty-page-email-text"></span>
                </a>
            </div>
        </div>

        <nav class="faculty-page-tabs" aria-label="Profile sections">
            <button type="button" class="faculty-tab-btn active" data-faculty-tab="overview">Overview</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="courses">Courses Taught</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="research">Research Interest</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="publications">Publications</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="education">Education</button>
        </nav>

        <div id="faculty-page-panel-overview" class="faculty-tab-panel active"></div>
        <div id="faculty-page-panel-courses" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-research" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-publications" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-education" class="faculty-tab-panel"></div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════
     STUDENT PROJECTS
════════════════════════════════════════════════════════════ -->
<section id="projects" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">From the Department</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Student Projects</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Real final-year work from SCOT students, built and demoed as part of their degree.
        </p>
    </div>

    <?php foreach ($studentProjects as $project): ?>
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] items-start">
        <div class="fade-in">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:9/16;">
                <video controls preload="none" playsinline muted
                       poster="<?= IMAGES_URL ?>/<?= e($project['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($project['video']) ?>" type="video/mp4">
                </video>
            </div>
        </div>
        <div class="fade-in fade-in-delay-2">
            <h3 class="font-heading font-black text-2xl sm:text-3xl text-ink leading-snug"><?= e($project['title']) ?></h3>
            <p class="mt-3 text-sm font-bold text-scotsaBlue"><?= e($project['student']) ?> &middot; <?= e($project['programme']) ?></p>
            <p class="mt-1 text-sm text-slate-500">
                Supervised by <?= e($project['supervisor']) ?>
                <?php if (!empty($project['coSupervisor'])): ?> &amp; <?= e($project['coSupervisor']) ?><?php endif; ?>
            </p>
            <p class="mt-5 text-sm leading-7 text-slate-600 whitespace-pre-line"><?= e($project['summary']) ?></p>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <?php foreach ($project['stack'] as $tech): ?>
                <span class="rounded-full border border-slate-200 px-3.5 py-1.5 text-sm font-semibold text-slate-600"><?= e($tech) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     BLOG — department news & updates. Photo tiles reuse the gallery
     lightbox (data-gallery-lightbox), same as the Facilities section.
════════════════════════════════════════════════════════════ -->
<section id="blog" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">News &amp; Updates</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Blog</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Department news, partnerships, and milestones from the School of Computing and Technology.
        </p>
    </div>

    <?php foreach ($blogPosts as $post): ?>
    <article class="fade-in">
        <div class="grid gap-10 lg:grid-cols-[1.1fr_0.9fr] items-start mb-10">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2">
                    <?= e(date('F j, Y', strtotime($post['date']))) ?>
                </p>
                <h3 class="font-heading font-black text-2xl text-ink leading-snug mb-4"><?= e($post['title']) ?></h3>
                <p class="text-sm leading-7 text-slate-600 whitespace-pre-line"><?= e($post['excerpt']) ?></p>
            </div>
            <?php if (!empty($post['video'])): ?>
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:16/9;">
                <video controls preload="none" playsinline
                       poster="<?= IMAGES_URL ?>/<?= e($post['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($post['video']) ?>" type="video/mp4">
                </video>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4" style="grid-auto-flow:dense;">
            <?php foreach ($post['photos'] as $i => $photo):
                $fullsize = IMAGES_URL . '/' . $photo['photo'];
                $featured = !empty($photo['featured']);
            ?>
            <div class="facility-tile fade-in fade-in-delay-<?= ($i % 3) + 1 ?> <?= $featured ? 'sm:col-span-2 lg:col-span-2 lg:row-span-2' : '' ?>"
                 data-gallery-lightbox="<?= e($fullsize) ?>"
                 data-gallery-caption="<?= e($photo['caption']) ?>"
                 <?= !$featured ? 'style="aspect-ratio:4/3;"' : '' ?>>
                <img src="<?= e($fullsize) ?>"
                     alt="<?= e($photo['caption']) ?>"
                     loading="lazy"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/default.svg'; this.onerror=null;">
                <div class="gallery-overlay">
                    <div>
                        <p class="font-heading font-bold text-white text-sm leading-snug"><?= e($photo['caption']) ?></p>
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
    </article>
    <?php endforeach; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     GALLERY — campus moments
════════════════════════════════════════════════════════════ -->
<section id="gallery" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Campus Life</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Gallery</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Moments from department events, orientation, seminars, and the hackathon.
        </p>
    </div>

    <div class="mb-8 flex flex-wrap gap-2 fade-in">
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
    </div>
</section>

<!-- Gallery photo lightbox — also used by Facilities tiles and Blog photos
     (any element with data-gallery-lightbox on the page). -->
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
    <img id="gallery-lightbox-img" src="" alt="" class="lightbox-img">
    <p id="gallery-lightbox-caption" class="lightbox-caption"></p>
</div>

<!-- ═══════════════════════════════════════════════════════════
     CONTACT
════════════════════════════════════════════════════════════ -->
<section id="contact" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Get in Touch</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Contact the department</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Reach out through your preferred channel, or visit the department office on campus.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <a href="mailto:<?= e($setting('contact_email')) ?>" class="contact-card group fade-in">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('mail', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Email</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_email')) ?></p>
            </div>
        </a>

        <a href="tel:<?= e(preg_replace('/\s+/', '', $setting('contact_phone'))) ?>" class="contact-card group fade-in fade-in-delay-1">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('phone', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Phone</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_phone')) ?></p>
            </div>
        </a>

        <a href="https://www.google.com/maps?q=5.670433,-0.1893348" target="_blank" rel="noopener" class="contact-card group fade-in fade-in-delay-2">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('map-pin', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Campus</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_address')) ?></p>
            </div>
        </a>
    </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
