<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'SCOTSA: School of Computing and Technology Students Association';

$announcements = [];
try {
    $announcements = db()->query(
        'SELECT title, body, published_at FROM announcements
         WHERE status = "published" ORDER BY published_at DESC LIMIT 3'
    )->fetchAll();
} catch (Throwable $e) {
    $announcements = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     HERO
════════════════════════════════════════════════════════════ -->
<section class="hero-grid text-white relative overflow-hidden">
    <!-- Animated gold orb -->
    <div class="hero-orb"></div>

    <div class="mx-auto max-w-3xl min-h-[58vh] flex flex-col justify-center gap-12 px-4 py-20 sm:px-6 lg:px-8 relative z-10">

        <!-- Copy -->
        <div>
            <h1 class="max-w-2xl font-heading font-black leading-[1.10] text-white" style="font-size:clamp(2.2rem,5vw,3.7rem);">
                Your academic hub for
                <span class="relative inline-block" style="color:#D4AF37;">
                    resources,
                    <span class="absolute -bottom-1 left-0 right-0 h-px" style="background:linear-gradient(to right, rgba(212,175,55,.8), transparent);"></span>
                </span>
                <br>updates &amp; community.
            </h1>

            <p class="mt-6 max-w-xl text-base leading-8" style="color:rgba(191,219,254,.78);">
                SCOTSA's official platform for academic resources, announcements, and events
                at Wisconsin International University College (WIUC), Accra.
            </p>

            <div class="mt-9 flex flex-wrap gap-3">
                <a class="btn-gold" href="<?= BASE_URL ?>/resources.php">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Browse Resources
                </a>
                <a class="btn-outline-white" href="<?= BASE_URL ?>/about.php">About SCOTSA</a>
            </div>
        </div>
    </div>

</section>

<!-- ═══════════════════════════════════════════════════════════
     SCHOOL LEADERSHIP
     Edit the array below with real names/titles once appointed —
     "Awaiting Appointment" follows the same placeholder convention
     already used for vacant roles on executives.php.
════════════════════════════════════════════════════════════ -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">School Leadership</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink">Guided by academic leadership.</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            The Dean and Heads of Department overseeing the School of Computing and Technology.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ([
            ['Dean, School of Computing and Technology', 'Dr. Patrick Kudjo', 'DR.PATRICK KUDJO DEAN.PNG'],
            ['Head of Department, Business Computing', 'Mr. Charles A. Babbage Jnr', 'MR.CHARLES BABBAGE JNR ASIEDU  HOD BUSINESS COMPUTING .jpg'],
            ['Head of Department, Information Technology', 'Dr. Amankwa', null],
            ['Head of Department, Mathematics Application', 'Dr. Leonard Kyei', null],
            ['Patron', 'Mr. Edwin Agbah', 'mr edwin agbah patron.jpg'],
        ] as $i => [$title, $name, $photo]): ?>
        <div class="card-hover group rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in fade-in-delay-<?= ($i % 3) + 1 ?>">
            <div class="relative overflow-hidden bg-slate-100" style="padding-top:115%;">
                <?php if ($photo): ?>
                <img src="<?= IMAGES_URL ?>/executives/<?= rawurlencode($photo) ?>" alt="<?= e($name) ?>"
                     class="absolute inset-0 h-full w-full object-cover"
                     style="object-position:50% 15%;"
                     loading="lazy"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/avatar.svg'; this.onerror=null;">
                <?php else: ?>
                <img src="<?= avatar_url(null, $name) ?>" alt="" class="absolute inset-0 h-full w-full object-cover">
                <?php endif; ?>
            </div>
            <div class="p-5 text-center">
                <p class="font-heading font-bold text-ink text-sm"><?= e($name) ?></p>
                <p class="mt-1 text-xs text-slate-400 leading-5"><?= e($title) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     WHY SCOTSA
════════════════════════════════════════════════════════════ -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-[0.9fr_1.1fr] items-center">
        <div class="fade-in">
            <span class="eyebrow">Why SCOTSA?</span>
            <div class="gold-line mt-3 mb-5"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl leading-tight">
                One platform.<br>Every resource your<br>academic journey needs.
            </h2>
            <p class="mt-5 text-slate-500 leading-8 text-[0.95rem]">
                SCOTSA bridges the gap between students and quality academic materials.
                We organise resources by program, level, semester, and course, so you
                always find exactly what you need, fast.
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn-primary" href="<?= BASE_URL ?>/resources.php">Explore Resources</a>
                <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50 hover:!border-slate-400" href="<?= BASE_URL ?>/about.php">Learn More</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-1 lg:gap-3 fade-in fade-in-delay-2">
            <?php foreach ([
                ['academic-cap',  'Academic Access',    'scotsaBlue', 'Past questions and course notes organized by program, level, semester, and course, always at your fingertips.'],
                ['megaphone',     'Department Updates', 'gold',       'Official announcements, event notices, and news published from one trusted, verified student source.'],
                ['rocket-launch', 'Built to Scale',     'scotsaBlue', 'A growing platform designed to support new programs, analytics, and advanced student services over time.'],
            ] as [$iconName, $title, $accent, $body]): ?>
            <div class="card-hover flex gap-4 rounded-xl border border-slate-200 bg-white p-5 group">
                <div class="flex-shrink-0 mt-0.5 grid h-10 w-10 place-items-center rounded-xl <?= $accent === 'gold' ? 'text-scotsaGold' : 'text-scotsaBlue' ?>"
                     style="background:rgba(10,31,68,.06);">
                    <?= icon($iconName, 'h-5 w-5') ?>
                </div>
                <div>
                    <h3 class="font-heading font-bold text-scotsaBlue group-hover:text-scotsaLight transition-colors"><?= $title ?></h3>
                    <p class="mt-1.5 text-sm leading-6 text-slate-500"><?= $body ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     FEATURED ANNOUNCEMENTS
════════════════════════════════════════════════════════════ -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
        <div class="fade-in">
            <span class="eyebrow">Latest News</span>
            <div class="gold-line mt-3 mb-3"></div>
            <h2 class="font-heading font-black text-3xl text-ink">Recent Announcements</h2>
        </div>
        <a class="text-sm font-bold text-scotsaBlue hover:underline underline-offset-2 transition fade-in"
           href="<?= BASE_URL ?>/announcements.php">View all announcements →</a>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        <?php if ($announcements): ?>
            <?php foreach ($announcements as $i => $ann): ?>
            <article class="ann-card card-hover rounded-xl border border-slate-200 bg-white p-6 fade-in fade-in-delay-<?= $i + 1 ?> overflow-hidden">
                <div class="flex items-center gap-2.5 mb-4">
                    <span class="ann-dot"></span>
                    <time class="text-xs font-semibold text-slate-400">
                        <?= e(date('M d, Y', strtotime($ann['published_at']))) ?>
                    </time>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                          style="background:rgba(10,31,68,.07); color:#0A1F44;">Notice</span>
                </div>
                <h3 class="font-heading font-bold text-lg text-ink leading-snug"><?= e($ann['title']) ?></h3>
                <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500"><?= e($ann['body']) ?></p>
                <a class="mt-5 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue hover:underline underline-offset-2"
                   href="<?= BASE_URL ?>/announcements.php">
                    Read more
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-400 md:col-span-3">
                <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl" style="background:rgba(10,31,68,.05);">
                    <svg class="h-7 w-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                </div>
                <p class="font-semibold text-slate-500">No announcements yet.</p>
                <p class="text-sm mt-1">Check back soon for updates from SCOTSA.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ═══════════════════════════════════════════════════════════
     RESOURCE TYPE GRID
════════════════════════════════════════════════════════════ -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Resource Portal</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink">Find what you need, instantly.</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            All academic materials filtered by type, program, level, and semester.
        </p>
    </div>

    <div class="grid gap-5 max-w-2xl mx-auto sm:grid-cols-2">
        <?php foreach ([
            ['document',   'Past Questions', 'Examinations from previous academic years, organized by course and level.', BASE_URL . '/resources.php?type=past_question', '#0A1F44', 'rgba(10,31,68,.06)'],
            ['book-open',  'Lecture Notes',  'Curated notes shared by peers and course coordinators.',                    BASE_URL . '/resources.php?type=lecture_note',  '#b8961e', 'rgba(212,175,55,.10)'],
        ] as $i => [$iconName, $title, $desc, $href, $accent, $iconBg]): ?>
        <a href="<?= $href ?>"
           class="card-hover group rounded-xl border border-slate-200 bg-white p-6 text-left fade-in fade-in-delay-<?= $i + 1 ?>">
            <div class="grid h-11 w-11 place-items-center rounded-xl mb-5 transition-transform duration-200 group-hover:scale-110"
                 style="background:<?= $iconBg ?>; color:<?= $accent ?>;">
                <?= icon($iconName, 'h-5 w-5') ?>
            </div>
            <h3 class="font-heading font-bold text-ink group-hover:text-scotsaBlue transition-colors"><?= $title ?></h3>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= $desc ?></p>
            <span class="mt-5 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue group-hover:gap-2 transition-all">
                Browse
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FULL-WIDTH CTA BANNER
════════════════════════════════════════════════════════════ -->
<section class="page-hero text-white relative overflow-hidden">
    <!-- Decorative dots pattern -->
    <div class="absolute inset-0 pointer-events-none" style="background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px); background-size:24px 24px;"></div>

    <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="eyebrow">Get started today</span>
        <h2 class="font-heading font-black text-3xl sm:text-5xl mt-5 mb-5 text-white max-w-2xl mx-auto leading-tight">
            Everything your academic<br>year demands, right here.
        </h2>
        <p class="max-w-lg mx-auto text-sm leading-8 mb-10" style="color:rgba(191,219,254,.72);">
            Join thousands of SCOTSA students who access quality resources, stay updated with
            departmental news, and connect with their community, all in one place.
        </p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a class="btn-gold" href="<?= BASE_URL ?>/resources.php">Browse Resources</a>
            <a class="btn-outline-white" href="<?= BASE_URL ?>/announcements.php">View Announcements</a>
        </div>

        <!-- Stats row -->
        <div class="mt-14 grid grid-cols-2 gap-6 sm:grid-cols-4 max-w-2xl mx-auto">
            <?php foreach ([
                ['6', 'Programs'],
                ['4', 'Year Levels'],
                ['2', 'Semesters'],
                ['24/7', 'Access'],
            ] as [$n, $l]): ?>
            <div>
                <div class="font-heading font-black text-2xl sm:text-3xl" style="color:#D4AF37;"><?= $n ?></div>
                <div class="text-xs font-semibold mt-1" style="color:rgba(191,219,254,.55);"><?= $l ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
