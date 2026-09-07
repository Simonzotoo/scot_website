<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

$facilityTiles = array_map(static fn (array $r) => [
    'photo'    => $r['photo_path'],
    'caption'  => $r['caption'],
    'featured' => (bool) $r['featured'],
], $pdo->query("SELECT * FROM facilities WHERE status='active' ORDER BY sort_order")->fetchAll());

$activePage = 'facilities';
$pageTitle = 'Facilities — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

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
        <?php foreach ($facilityTiles as $i => $tile):
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
                 data-fallback="<?= IMAGES_URL ?>/placeholders/default.svg">
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

<?php require_once __DIR__ . '/includes/gallery_lightbox.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
