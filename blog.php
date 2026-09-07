<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

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

$activePage = 'blog';
$pageTitle = 'Blog — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

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
                <p class="text-sm leading-7 text-slate-600 whitespace-pre-line text-justify"><?= e($post['excerpt']) ?></p>
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
                     data-fallback="<?= IMAGES_URL ?>/placeholders/default.svg">
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

<?php require_once __DIR__ . '/includes/gallery_lightbox.php'; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
