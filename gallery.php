<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Gallery: SCOTSA Events & Community';
require_once __DIR__ . '/includes/header.php';

$galleryItems = [
    // [category, filename, caption]
    ['events',     'IMG_4617.JPEG',   'SCOTSA Annual General Assembly'],
    ['events',     'IMG_4620.JPEG',   'Department Welcome Ceremony'],
    ['events',     'IMG_4637.JPEG',   'SCOTSA Community Gathering'],
    ['events',     'IMG_4742.JPEG',   'End-of-Semester Celebration'],
    ['src-week',   'IMG_4625.JPEG',   'SRC Week Opening Ceremony'],
    ['src-week',   'IMG_4849.JPEG',   'SRC Week Cultural Showcase'],
    ['seminars',   'IMG_4833.JPEG',   'Academic Awareness Seminar'],
    ['seminars',   'IMG_4845.JPEG',   'Department Workshop'],
    ['tech',       'IMG_4632.JPEG',   'Technology Exhibition 2024'],
    ['executives', 'IMG_4632 2.JPEG', 'SCOTSA Executive Team 2024/2025'],
];

$categories = [
    ['all',        'All Photos'],
    ['events',     'Events'],
    ['src-week',   'SRC Week'],
    ['seminars',   'Seminars'],
    ['tech',       'Tech Exhibitions'],
    ['executives', 'Executives'],
];
$categoryMap = array_column($categories, 1, 0);
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 relative z-10">
        <nav class="mb-6 flex items-center gap-2 text-xs" style="color:rgba(191,219,254,.55);">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">Gallery</span>
        </nav>
        <span class="eyebrow">Visual Archive</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-2xl leading-tight">
            Events, milestones &amp; community moments.
        </h1>
        <p class="max-w-xl text-sm leading-7" style="color:rgba(191,219,254,.72);">
            A visual record of SCOTSA's activities, from academic seminars and tech exhibitions
            to SRC Week celebrations and executive engagements.
        </p>
        <div class="mt-5 inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold"
             style="background:rgba(212,175,55,.15); border:1px solid rgba(212,175,55,.30); color:#D4AF37;">
            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <?= count($galleryItems) ?> photos across <?= count($categories) - 1 ?> categories
        </div>
    </div>
</section>

<!-- ── Filter Tabs ────────────────────────────────────── -->
<section class="border-b border-slate-200 bg-white sticky top-[65px] z-30 shadow-sm">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex gap-2 overflow-x-auto py-4 no-scrollbar">
            <?php foreach ($categories as $i => [$slug, $label]): ?>
                <button class="filter-tab flex-shrink-0 <?= $i === 0 ? 'filter-active' : '' ?>"
                        data-filter="<?= $slug ?>">
                    <?= $label ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── Masonry Gallery ────────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
    <div class="gallery-grid">
        <?php foreach ($galleryItems as $i => [$category, $photo, $caption]):
            // rawurlencode handles filenames with spaces (e.g. "IMG_4632 2.JPEG" → "IMG_4632%202.JPEG")
            $encoded  = rawurlencode($photo);
            $thumb    = IMAGES_URL . '/gallery/' . $encoded;
            $fullsize = IMAGES_URL . '/gallery/' . $encoded;
        ?>
        <div class="gallery-item fade-in"
             data-category="<?= $category ?>"
             data-lightbox="<?= htmlspecialchars($fullsize, ENT_QUOTES) ?>"
             data-caption="<?= htmlspecialchars($caption, ENT_QUOTES) ?>">

            <img src="<?= htmlspecialchars($thumb, ENT_QUOTES) ?>"
                 alt="<?= htmlspecialchars($caption, ENT_QUOTES) ?>"
                 loading="lazy"
                 onerror="this.src='<?= IMAGES_URL ?>/placeholders/default.svg'; this.onerror=null;">

            <!-- Caption overlay -->
            <div class="gallery-overlay">
                <div>
                    <p class="font-heading font-bold text-white text-sm leading-snug"><?= $caption ?></p>
                    <p class="text-xs mt-1" style="color:rgba(191,219,254,.65);">
                        <?= $categoryMap[$category] ?? ucwords(str_replace('-', ' ', $category)) ?>
                    </p>
                </div>
            </div>

            <!-- Expand icon -->
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

<!-- ── Lightbox ───────────────────────────────────────── -->
<div id="lightbox" class="lightbox" role="dialog" aria-modal="true" aria-label="Image preview">
    <!-- Close -->
    <button id="lightbox-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Prev arrow -->
    <button id="lightbox-prev"
            class="absolute left-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Previous image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>

    <!-- Next arrow -->
    <button id="lightbox-next"
            class="absolute right-4 top-1/2 -translate-y-1/2 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Next image">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    <img id="lightbox-img" src="" alt="" class="select-none">
    <p id="lightbox-caption" class="mt-4 text-sm font-medium text-center max-w-lg" style="color:rgba(191,219,254,.65);"></p>
    <p class="mt-2 text-xs" style="color:rgba(191,219,254,.30);">← → Arrow keys to navigate · Esc to close</p>
</div>

<!-- ── Contribute CTA ─────────────────────────────────── -->
<section class="py-16" style="background:#f8fafc;">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center fade-in">
        <div class="grid h-14 w-14 place-items-center rounded-2xl mx-auto mb-5 text-scotsaBlue" style="background:rgba(10,31,68,.06);"><?= icon('camera', 'h-7 w-7') ?></div>
        <span class="eyebrow">Be Part of the Story</span>
        <h2 class="font-heading font-black text-2xl text-ink mt-4 mb-3">Have photos from a SCOTSA event?</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto leading-7 mb-6">
            Contact the SCOTSA executive team to contribute your photos and help build our visual archive
            of departmental life, events, and achievements.
        </p>
        <a class="inline-block btn-primary" href="<?= BASE_URL ?>/contact.php">Contact the Team</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
