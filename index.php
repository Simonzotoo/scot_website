<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

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

// Dean's Message only needs the Dean's own name/role/photo — the full
// profile (bio, courses taught, publications, education) lives on the
// Faculty page's clickable card instead.
$dean = $pdo->query("SELECT name, role, photo_path AS photo FROM team_members WHERE roster='leadership' ORDER BY sort_order LIMIT 1")->fetch()
    ?: ['name' => '', 'role' => '', 'photo' => ''];

// site_settings isn't loaded yet at this point (header.php owns that
// fetch), so the homepage's admin-configurable title is queried directly
// here rather than via $setting(), which doesn't exist until after the
// header.php require below — but header.php needs $pageTitle already set
// to render <title>/OG tags, so this can't wait until after that require.
$pageTitle = $pdo->query("SELECT setting_value FROM site_settings WHERE setting_key = 'site_meta_title'")->fetchColumn()
    ?: 'School of Computing and Technology (SCOT), WIUC Ghana';
$activePage = 'home';
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

            <p id="hero-subtext" class="mt-6 max-w-xl text-base leading-8 text-justify" style="color:rgba(191,219,254,.78);">
                <?= e($heroSlides[0]['subtext']) ?>
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-3">
                <a class="btn-gold" href="<?= BASE_URL ?>/programmes.php">
                    <?= icon('academic-cap', 'h-4 w-4') ?>
                    Explore Programmes
                </a>
                <a class="btn-outline-white" href="<?= BASE_URL ?>/faculty.php">Faculty &amp; Leadership</a>
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

<!-- ═══════════════════════════════════════════════════════════
     A MESSAGE FROM THE DEAN
════════════════════════════════════════════════════════════ -->
<section class="section-navy py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] items-center">
        <div class="fade-in">
            <div class="relative rounded-2xl overflow-hidden shadow-2xl" style="aspect-ratio:4/5;">
                <img src="<?= IMAGES_URL ?>/<?= e($dean['photo']) ?>" alt="<?= e($dean['name']) ?>"
                     class="absolute inset-0 h-full w-full object-cover"
                     style="object-position:50% 10%;"
                     data-fallback="<?= IMAGES_URL ?>/placeholders/avatar.svg">
            </div>
        </div>

        <div class="fade-in fade-in-delay-2">
            <span class="eyebrow" style="font-size:1.125rem; letter-spacing:.08em;">A Message From the Dean</span>
            <div class="gold-line mt-3 mb-6"></div>
            <blockquote class="text-lg sm:text-xl leading-9 text-ink font-medium text-justify">
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
            <p class="mt-5 text-slate-500 leading-8 text-sm text-justify">
                <?= e($setting('about_paragraph_1')) ?>
            </p>
            <p class="mt-4 text-slate-500 leading-8 text-sm text-justify">
                <?= e($setting('about_paragraph_2')) ?>
            </p>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="btn-primary" href="<?= BASE_URL ?>/programmes.php">View Programmes</a>
                <a class="btn-outline-white !border-slate-300 !text-slate-700 hover:!bg-slate-50 hover:!border-slate-400" href="<?= BASE_URL ?>/faculty.php">Faculty &amp; Leadership</a>
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
                <p class="mt-1.5 text-sm leading-6 text-slate-500 text-justify"><?= e($body) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
