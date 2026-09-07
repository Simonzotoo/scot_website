<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

$videos = array_map(static fn (array $r) => [
    'file'    => $r['file_path'],
    'poster'  => $r['poster_path'],
    'title'   => $r['title'],
    'caption' => $r['caption'],
], $pdo->query("SELECT * FROM videos WHERE status='active' ORDER BY sort_order")->fetchAll());

$activePage = 'watch';
$pageTitle = 'Watch — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
