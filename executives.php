<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Executive Team: SCOTSA Leadership';
require_once __DIR__ . '/includes/header.php';

$executives = [
    // [role, name, portfolio, initials, bio, gradient, photo_file]
    // photo_file: filename in assets/images/executives/, null = initials badge shown
    ['President',                  'Justice Simon Zotoo',    'Executive Lead & Chief Representative',   'JS', 'Chairs all SCOTSA executive meetings, represents the association at Wisconsin International University College (WIUC), ITAG, and allied institutional engagements, and leads the strategic vision and direction of the association for the academic year.',          '135deg, #0A1F44 0%, #0d2a5c 100%', 'JUSTICE SIMON ZOTOO - PRESIDENT.PNG'],
    ['Vice President',             'Awaiting Election',      'Deputy Lead & Student Affairs',           'VP', 'Supports the President in all capacities, coordinates departmental initiatives, oversees student welfare programmes, and acts in the President\'s stead when required by the association\'s constitution.', '135deg, #0d2a5c 0%, #1a3a7a 100%', null],
    ['Secretary',                  'Awaiting Election',      'Administration & Official Records',       'SE', 'Manages all official SCOTSA correspondence, keeps accurate minutes of every executive meeting, maintains the association\'s records, and ensures the smooth administrative operations of SCOTSA throughout the academic year.',          '135deg, #0A1F44 0%, #07172f 100%', null],
    ['Financial Secretary',        'Awaiting Election',      'Finance, Records & Accountability',       'FS', 'Assists the Treasurer in managing SCOTSA\'s financial records, prepares financial documentation, and ensures accurate accounting and transparent reporting of all association funds to the student body.',                         '135deg, #07172f 0%, #0A1F44 100%', null],
    ['Organising Secretary',       'Awaiting Election',      'Events, Programmes & Logistics',         'OS', 'Plans and coordinates all SCOTSA events, academic programmes, and departmental activities, from SRC Week and orientation to end-of-semester celebrations, seminars, and inter-association engagements.',                    '135deg, #0d2a5c 0%, #0A1F44 100%', null],
    ['Communications Director',    'John Kpakpo Boabeng',    'Communications, Media & Branding',       'JK', 'Manages SCOTSA\'s public image, social media channels, and press communications, ensuring consistent, professional representation of the association across all platforms and to the wider WIUC student community.',                  '135deg, #0A1F44 0%, #0d2a5c 100%', 'JOHN KPAKPO BOABENG - COMMUNICATIONS DIRECTOR .jpg'],
    ['Treasurer',                  'Awaiting Election',      'Finance, Budgets & Accountability',      'TR', 'Oversees all of SCOTSA\'s financial affairs, preparing and managing the association\'s budget, maintaining financial accounts, accounting for all funds received and disbursed, and ensuring transparent financial reporting to the student body.',                    '135deg, #07172f 0%, #0d2a5c 100%', null],
    ['Women\'s Commissioner',      'Awaiting Election',      'Gender Equity & Inclusive Programmes',   'WC', 'Leads SCOTSA\'s gender equity agenda at WIUC, drives inclusive programming, and advocates for the welfare, rights, and empowerment of women in computing and technology, ensuring every female student has an equal voice.',                      '135deg, #0d2a5c 0%, #07172f 100%', null],
];
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 relative z-10">
        <nav class="mb-6 flex items-center gap-2 text-xs" style="color:rgba(191,219,254,.55);">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">Executives</span>
        </nav>
        <span class="eyebrow">Student Leadership</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-2xl leading-tight">
            Meet the SCOTSA executive team.
        </h1>
        <p class="max-w-xl text-sm leading-7" style="color:rgba(191,219,254,.72);">
            Your elected representatives, dedicated to advancing academic excellence,
            student welfare, and community growth in the School of Computing and Technology.
        </p>
    </div>
</section>

<!-- ── Executive Grid ─────────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="text-center mb-14 fade-in">
        <span class="eyebrow">2024 / 2025 Academic Year</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink">Current Executive Council</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Eight portfolio holders democratically elected by the student body of Wisconsin International University College to serve computing and technology students.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php foreach ($executives as $i => [$role, $name, $portfolio, $initials, $bio, $gradient, $photo]): ?>
        <article class="card-hover group rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in fade-in-delay-<?= ($i % 4) + 1 ?>">

            <!-- ── Photo / Avatar section ──────────────────────────── -->
            <!--
                Portrait-friendly: no fixed height, uses padding-top trick
                so the container is always 4:5 aspect ratio regardless of card width.
                object-position: 50% 15% keeps the face in frame for headshots.
            -->
            <div class="relative overflow-hidden" style="background:linear-gradient(<?= $gradient ?>); padding-top:120%;">

                <!-- Dot grid texture -->
                <div class="absolute inset-0 pointer-events-none"
                     style="background-image:radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px); background-size:16px 16px;"></div>

                <?php if ($photo): ?>

                    <img src="<?= IMAGES_URL ?>/executives/<?= rawurlencode($photo) ?>"
                         alt="Photo of <?= e($name) ?>"
                         class="absolute inset-0 w-full h-full object-cover"
                         style="object-position:50% 15%;"
                         loading="lazy"
                         onerror="this.src='<?= IMAGES_URL ?>/placeholders/avatar.svg'; this.onerror=null;">

                    <!-- Bottom gradient: makes portfolio text legible over photo -->
                    <div class="absolute bottom-0 inset-x-0 h-28 pointer-events-none"
                         style="background:linear-gradient(to top, rgba(7,23,47,.88) 0%, rgba(7,23,47,.30) 60%, transparent 100%);"></div>

                <?php else: ?>

                    <!-- Initials badge: absolutely centered when no photo is uploaded -->
                    <div class="absolute inset-0 flex items-end justify-center pb-14">
                        <div class="exec-avatar-ring" style="width:80px;height:80px;font-size:1.25rem;">
                            <?= e($initials) ?>
                            <span class="absolute -bottom-1 -right-1 grid h-5 w-5 place-items-center rounded-full"
                                  style="background:#D4AF37; border:2px solid white;">
                                <svg class="h-2.5 w-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Fade for consistent text area even without photo -->
                    <div class="absolute bottom-0 inset-x-0 h-20 pointer-events-none"
                         style="background:linear-gradient(to top, rgba(7,23,47,.60) 0%, transparent 100%);"></div>

                <?php endif; ?>

                <!-- Portfolio label: always at bottom -->
                <div class="absolute bottom-0 inset-x-0 px-4 py-3 text-center z-10">
                    <p class="eyebrow text-[9px] tracking-widest leading-none" style="color:rgba(212,175,55,.90);"><?= e($portfolio) ?></p>
                </div>

            </div><!-- /photo section -->

            <!-- ── Info section ───────────────────────────────────── -->
            <div class="p-5">
                <h3 class="font-heading font-black text-ink text-base leading-snug"><?= e($role) ?></h3>
                <p class="text-xs font-semibold mt-0.5 mb-3 italic" style="color:rgba(10,31,68,.5);"><?= e($name) ?></p>
                <p class="text-xs leading-6 text-slate-500 line-clamp-3"><?= e($bio) ?></p>
            </div>

        </article>
        <?php endforeach; ?>
    </div>
</section>

<div class="section-divider mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"></div>

<!-- ── Leadership Values ──────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="grid gap-12 lg:grid-cols-2 items-center">
        <div class="fade-in">
            <span class="eyebrow">Our Commitment</span>
            <div class="gold-line mt-3 mb-5"></div>
            <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl leading-tight">
                Leadership that serves,<br>not just leads.
            </h2>
            <p class="mt-5 text-slate-500 leading-8 text-sm">
                The SCOTSA Executive Council is elected democratically by students at Wisconsin
                International University College, for students. Every initiative, resource, and
                decision is guided by one constitutional purpose: to foster the well-being of
                members, protect student rights, and meaningfully improve the academic and social
                experience of every computing and technology student at WIUC.
            </p>
            <p class="mt-4 text-slate-500 leading-8 text-sm">
                From organising annual events and managing the academic resource platform to
                promoting cooperation with ITAG and advocating for better facilities, your
                executives work year-round on your behalf.
            </p>
            <a class="mt-8 inline-block btn-primary" href="<?= BASE_URL ?>/contact.php">
                Contact the Executives
            </a>
        </div>

        <div class="grid grid-cols-2 gap-4 fade-in fade-in-delay-2">
            <?php foreach ([
                ['check-badge',    'Democratically Elected',  'Every SCOTSA executive is chosen through a free, fair, and transparent student election at WIUC.'],
                ['clipboard-list', 'Constitutionally Bound',  'All executives operate under SCOTSA\'s official constitution, with defined mandates, rights, and responsibilities.'],
                ['briefcase',      'Portfolio-Driven Roles',  'Each position carries a specific constitutional mandate and direct accountability to the student body.'],
                ['arrow-path',     'Annual Renewal',           'Positions are renewed each academic year, ensuring fresh, representative student leadership.'],
            ] as [$iconName, $title, $body]): ?>
            <div class="card-hover rounded-xl border border-slate-200 bg-white p-5">
                <div class="grid h-10 w-10 place-items-center rounded-xl mb-3 text-scotsaBlue"
                     style="background:rgba(10,31,68,.06);">
                    <?= icon($iconName, 'h-5 w-5') ?>
                </div>
                <h3 class="font-heading font-bold text-sm text-ink mb-1.5"><?= $title ?></h3>
                <p class="text-xs leading-6 text-slate-500"><?= $body ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Elections CTA ──────────────────────────────────── -->
<section class="page-hero text-white">
    <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8 relative z-10 text-center">
        <span class="eyebrow">Get Involved</span>
        <h2 class="font-heading font-black text-3xl mt-4 mb-4 text-white max-w-xl mx-auto leading-tight">
            Want to serve as an executive?
        </h2>
        <p class="text-sm max-w-md mx-auto leading-7 mb-8" style="color:rgba(191,219,254,.65);">
            SCOTSA elections are open to all registered computing and technology students at
            Wisconsin International University College (WIUC). Watch for election announcements
            each academic year.
        </p>
        <div class="flex flex-wrap gap-3 justify-center">
            <a class="btn-gold" href="<?= BASE_URL ?>/announcements.php">View Announcements</a>
            <a class="btn-outline-white" href="<?= BASE_URL ?>/contact.php">Get In Touch</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
