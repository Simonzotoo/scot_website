<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

$galleryPhotos = array_map(static fn (array $r) => [
    'photo'    => $r['photo_path'],
    'caption'  => $r['caption'],
    'category' => $r['category'],
], $pdo->query("SELECT * FROM gallery_items WHERE status='active' ORDER BY sort_order")->fetchAll());

$galleryCategories = array_merge(
    ['All'],
    $pdo->query('SELECT name FROM gallery_categories ORDER BY sort_order')->fetchAll(PDO::FETCH_COLUMN)
);

$activePage = 'gallery';
$pageTitle = 'Gallery — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

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
                 data-fallback="<?= IMAGES_URL ?>/placeholders/default.svg">
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

<?php require_once __DIR__ . '/includes/gallery_lightbox.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
