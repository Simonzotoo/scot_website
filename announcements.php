<?php
require_once __DIR__ . '/includes/db.php';
$pageTitle = 'Announcements: SCOTSA News & Updates';

$announcements = [];
try {
    $announcements = db()->query(
        'SELECT title, body, published_at FROM announcements
         WHERE status = "published" ORDER BY published_at DESC'
    )->fetchAll();
} catch (Throwable $e) {
    $announcements = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 relative z-10">
        <nav class="mb-6 flex items-center gap-2 text-xs" style="color:rgba(191,219,254,.55);">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">Announcements</span>
        </nav>
        <span class="eyebrow">Latest from SCOTSA</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-2xl leading-tight">
            News, updates &amp; departmental notices.
        </h1>
        <p class="max-w-xl text-sm leading-7" style="color:rgba(191,219,254,.72);">
            Stay informed with the latest announcements from the School of Computing and Technology
            Students Association. All notices are published by SCOTSA administrators.
        </p>

        <?php if ($announcements): ?>
        <div class="mt-6 inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-xs font-semibold"
             style="background:rgba(212,175,55,.15); border:1px solid rgba(212,175,55,.30); color:#D4AF37;">
            <span class="h-1.5 w-1.5 rounded-full bg-current inline-block"></span>
            <?= count($announcements) ?> active notice<?= count($announcements) !== 1 ? 's' : '' ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Announcements Feed ─────────────────────────────── -->
<section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">

    <?php if ($announcements): ?>

        <div class="grid gap-5">
            <?php foreach ($announcements as $i => $ann): ?>
            <article class="ann-card card-hover group rounded-xl border border-slate-200 bg-white p-7 fade-in overflow-hidden">

                <!-- Header row -->
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <!-- Date badge -->
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-500">
                        <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?= e(date('F j, Y', strtotime($ann['published_at']))) ?>
                    </span>

                    <!-- Official badge -->
                    <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold"
                          style="background:rgba(10,31,68,.07); color:#0A1F44;">
                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Official Notice
                    </span>

                    <!-- Time (pushed right) -->
                    <span class="ml-auto flex items-center gap-1 text-xs text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?= e(date('g:i A', strtotime($ann['published_at']))) ?>
                    </span>
                </div>

                <!-- Title -->
                <h2 class="font-heading font-black text-xl text-ink leading-snug mb-4 group-hover:text-scotsaBlue transition-colors">
                    <?= e($ann['title']) ?>
                </h2>

                <!-- Body -->
                <p class="text-sm leading-8 text-slate-500 whitespace-pre-line">
                    <?= e($ann['body']) ?>
                </p>

                <!-- Footer -->
                <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="grid h-7 w-7 place-items-center rounded-lg text-xs font-heading font-black"
                             style="background:rgba(10,31,68,.08); color:#0A1F44;">SC</div>
                        <span class="text-xs font-semibold text-slate-400">SCOTSA Administration</span>
                    </div>
                    <span class="ann-dot"></span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

    <?php else: ?>

        <!-- Empty state -->
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center fade-in">
            <div class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-2xl" style="background:rgba(10,31,68,.06);">
                <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
            </div>
            <h3 class="font-heading font-bold text-xl text-ink mb-2">No announcements yet</h3>
            <p class="text-sm text-slate-400 max-w-sm mx-auto leading-6">
                SCOTSA announcements will appear here once published by the administration.
                Check back soon for departmental updates.
            </p>
        </div>

    <?php endif; ?>
</section>

<!-- ── Stay Connected CTA ─────────────────────────────── -->
<section class="pb-16">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="relative rounded-2xl p-8 text-white text-center overflow-hidden fade-in"
             style="background:linear-gradient(135deg, #0A1F44 0%, #0d2a5c 100%);">
            <div class="absolute inset-0 pointer-events-none"
                 style="background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px); background-size:22px 22px;"></div>
            <div class="relative z-10">
                <span class="eyebrow">Stay Connected</span>
                <h2 class="font-heading font-black text-2xl mt-4 mb-3 text-white">
                    Never miss an announcement
                </h2>
                <p class="text-sm max-w-md mx-auto leading-7 mb-7" style="color:rgba(191,219,254,.65);">
                    Follow SCOTSA on social media or reach out directly to stay up to date with
                    all departmental news, events, and academic updates.
                </p>
                <div class="flex flex-wrap gap-3 justify-center">
                    <a class="btn-gold" href="<?= BASE_URL ?>/contact.php">Get In Touch</a>
                    <a class="btn-outline-white" href="<?= BASE_URL ?>/gallery.php">View Gallery</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
