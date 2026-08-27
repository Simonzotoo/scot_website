<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'About SCOTSA: School of Computing and Technology Students Association';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white"<?= $pageHeroStyle ?>>
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 relative z-10">
        <nav class="mb-6 flex items-center gap-2 text-xs" style="color:rgba(191,219,254,.55);">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">About</span>
        </nav>
        <span class="eyebrow">Who We Are</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-3xl leading-tight">
            Building the future of student computing, together.
        </h1>
        <p class="max-w-2xl text-base leading-8" style="color:rgba(191,219,254,.72);">
            SCOTSA is the official student association of the School of Computing and Technology,
            dedicated to academic excellence, community growth, and professional development.
        </p>
    </div>
</section>

<!-- ── What is SCOTSA ─────────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="grid gap-14 lg:grid-cols-2 items-center">
        <div class="fade-in">
            <span class="eyebrow">Our Identity</span>
            <div class="gold-line mt-3 mb-5"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl leading-tight">
                What is SCOTSA?
            </h2>
            <p class="mt-5 text-slate-500 leading-8 text-[0.95rem]">
                The <strong class="text-ink font-bold">School of Computing and Technology Students Association (SCOTSA)</strong>
                is the officially recognised student body for all computing and technology students at
                Wisconsin International University College (WIUC), Accra. In exercise of our natural
                and inalienable right to establish an Association, SCOTSA exists to foster the
                well-being of our members, protect student rights, and promote cooperation
                with ITAG and allied student associations.
            </p>
            <p class="mt-4 text-slate-500 leading-8 text-[0.95rem]">
                Our platform gives every WIUC computing and technology student instant access to
                academic materials, departmental announcements, and community milestones, anytime, anywhere.
            </p>

            <!-- Key facts -->
            <div class="mt-8 grid grid-cols-2 gap-4">
                <?php foreach ([
                    ['Founded',   '2021', 'Serving students since inception'],
                    ['Programs',  '6',    'BSc &amp; Diploma programs at WIUC'],
                ] as [$label, $val, $sub]): ?>
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <div class="font-heading font-black text-2xl text-scotsaBlue"><?= $val ?></div>
                    <div class="font-semibold text-ink text-sm mt-0.5"><?= $label ?></div>
                    <div class="text-xs text-slate-400 mt-0.5"><?= $sub ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Image card -->
        <div class="fade-in fade-in-delay-2">
            <div class="img-zoom relative rounded-2xl overflow-hidden shadow-2xl">
                <img src="<?= IMAGES_URL ?>/placeholders/aboutscotsa.JPEG"
                     alt="SCOTSA students collaborating"
                     loading="lazy"
                     class="w-full block"
                     onerror="this.src='<?= IMAGES_URL ?>/gallery/IMG_4617.JPEG'; this.onerror=null;">
                <div class="absolute inset-0" style="background:linear-gradient(to top, rgba(10,31,68,.75) 0%, transparent 55%);"></div>
                <div class="absolute bottom-0 left-0 p-6">
                    <p class="font-heading font-black text-white text-lg">Student-powered. Community-driven.</p>
                    <p class="text-sm mt-1" style="color:rgba(191,219,254,.75);">Wisconsin International University College, Accra</p>
                </div>
                <!-- Gold accent corner -->
                <div class="absolute top-4 right-4 rounded-lg px-3 py-1.5 text-xs font-bold"
                     style="background:rgba(212,175,55,.20); border:1px solid rgba(212,175,55,.35); color:#D4AF37; backdrop-filter:blur(8px);">
                    SCOTSA
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ── Mission & Vision ───────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Our Purpose</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Mission &amp; Vision</h2>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <!-- Mission -->
        <div class="relative rounded-2xl p-8 overflow-hidden fade-in fade-in-delay-1"
             style="background:linear-gradient(135deg, #0A1F44 0%, #0d2a5c 100%);">
            <div class="absolute inset-0 pointer-events-none"
                 style="background-image:radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px); background-size:20px 20px;"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-5">
                    <div class="grid h-12 w-12 place-items-center rounded-xl text-scotsaGold"
                         style="background:rgba(212,175,55,.15); border:1px solid rgba(212,175,55,.30);"><?= icon('flag', 'h-6 w-6') ?></div>
                    <h3 class="font-heading font-black text-xl text-white">Our Mission</h3>
                </div>
                <p class="leading-8 text-sm" style="color:rgba(191,219,254,.78);">
                    To foster the well-being and academic advancement of every computing and
                    technology student at Wisconsin International University College, protecting
                    student rights, promoting cooperation with ITAG and allied associations, and
                    building a unified, accessible, excellence-driven community that prepares
                    members for leadership and innovation.
                </p>
            </div>
        </div>

        <!-- Vision -->
        <div class="rounded-2xl border border-slate-200 bg-white p-8 fade-in fade-in-delay-2">
            <div class="flex items-center gap-3 mb-5">
                <div class="grid h-12 w-12 place-items-center rounded-xl text-scotsaBlue"
                     style="background:rgba(10,31,68,.06); border:1px solid rgba(10,31,68,.10);"><?= icon('sparkles', 'h-6 w-6') ?></div>
                <h3 class="font-heading font-black text-xl text-ink">Our Vision</h3>
            </div>
            <p class="text-slate-500 leading-8 text-sm">
                A community of computing and technology students at WIUC who are equipped and
                supported to pursue innovation in their fields, with every student's academic
                journey supported and every voice heard.
            </p>
        </div>
    </div>
</section>

<!-- ── Core Values ────────────────────────────────────── -->
<section class="py-20" style="background:#f8fafc;">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fade-in">
            <span class="eyebrow">What Drives Us</span>
            <div class="gold-line mt-3 mx-auto mb-4"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Core Values</h2>
            <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
                Six principles that govern everything we do as an association.
            </p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ([
                ['academic-cap', 'Academic Excellence',  'We champion rigorous learning, peer support, and the highest academic standards across all computing and technology programs at WIUC.'],
                ['heart',        'Student Well-being',   'Every member\'s welfare is our priority. We advocate for student rights and fair treatment for all our members.'],
                ['light-bulb',   'Innovation',            'We embrace technological thinking, creative problem-solving, and forward-looking solutions that prepare WIUC students to shape tomorrow\'s world.'],
                ['eye',          'Transparency',          'We operate with openness and accountability, from executive decisions to resource management, event planning, and financial reporting.'],
                ['users',        'Cooperation',           'We actively promote cooperation with ITAG and allied student associations, building bridges across departments and institutions.'],
                ['shield-check', 'Loyalty & Integrity',  'We uphold SCOTSA\'s constitutional mandate with honour, and every executive and member is bound by loyalty to the association and its students.'],
            ] as $i => [$iconName, $title, $body]): ?>
            <div class="card-hover group rounded-xl border border-slate-200 bg-white p-6 fade-in fade-in-delay-<?= ($i % 3) + 1 ?>">
                <div class="grid h-12 w-12 place-items-center rounded-xl mb-4 text-scotsaBlue transition-transform duration-200 group-hover:scale-110"
                     style="background:rgba(10,31,68,.06);">
                    <?= icon($iconName, 'h-6 w-6') ?>
                </div>
                <h3 class="font-heading font-bold text-ink mb-2 group-hover:text-scotsaBlue transition-colors"><?= $title ?></h3>
                <p class="text-sm leading-7 text-slate-500"><?= $body ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Stats Row ──────────────────────────────────────── -->
<section class="page-hero text-white relative overflow-hidden"<?= $pageHeroStyle ?>>
    <div class="absolute inset-0 pointer-events-none"
         style="background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px); background-size:24px 24px;"></div>
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 relative z-10">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <?php foreach ([
                ['6',   '',  'Academic Programs',     'BSc &amp; Diploma, all disciplines'],
                ['4',   '',  'Year Levels',           'Level 100 through to Level 400'],
                ['2',   '',  'Semesters',             'Lower (first) &amp; Upper (second)'],
                ['24',  '/7','Platform Availability', 'Always online, always accessible'],
            ] as $i => [$num, $suffix, $label, $sub]): ?>
            <div class="text-center fade-in fade-in-delay-<?= $i + 1 ?>">
                <div class="font-heading font-black text-5xl" style="color:#D4AF37;">
                    <span class="count-up" data-count="<?= $num ?>"><?= $num ?></span><?= $suffix ?>
                </div>
                <p class="font-bold text-white mt-3 text-sm"><?= $label ?></p>
                <p class="text-xs mt-1" style="color:rgba(191,219,254,.55);"><?= $sub ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ────────────────────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 text-center fade-in">
    <span class="eyebrow">Get Involved</span>
    <div class="gold-line mt-3 mx-auto mb-5"></div>
    <h2 class="font-heading font-black text-3xl text-ink mt-4 mb-5 max-w-xl mx-auto leading-tight">
        See what SCOTSA runs day to day.
    </h2>
    <p class="text-slate-500 text-sm max-w-md mx-auto leading-7 mb-8">
        Past questions and notes, executive contacts, event photos, and official announcements.
    </p>
    <div class="flex flex-wrap gap-3 justify-center">
        <a class="btn-primary" href="<?= BASE_URL ?>/resources.php">Browse Resources</a>
        <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50" href="<?= BASE_URL ?>/executives.php">Meet the Executives</a>
        <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50" href="<?= BASE_URL ?>/contact.php">Contact Us</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
