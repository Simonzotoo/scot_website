<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

$documents = array_map(static fn (array $r) => [
    'title'    => $r['title'],
    'audience' => $r['audience'],
    'file'     => $r['file_path'],
    'preview'  => $r['preview_path'],
    'size'     => (int) $r['file_size'],
], $pdo->query("SELECT * FROM documents WHERE status='active' ORDER BY sort_order")->fetchAll());

function scot_format_bytes(int $bytes): string
{
    if ($bytes >= 1024 * 1024) {
        return round($bytes / (1024 * 1024), 1) . ' MB';
    }
    return round($bytes / 1024) . ' KB';
}

$activePage = 'downloads';
$pageTitle = 'Downloads — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     DOWNLOADS — academic calendar, timetables, and other
     department documents students need direct access to.
════════════════════════════════════════════════════════════ -->
<section id="downloads" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Student Resources</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Downloads</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            The academic calendar, class timetables, and other documents you need this semester.
        </p>
    </div>

    <?php if ($documents): ?>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($documents as $i => $doc):
            $fileUrl = BASE_URL . '/assets/' . $doc['file'];
            $previewUrl = $doc['preview'] ? BASE_URL . '/assets/' . $doc['preview'] : null;
        ?>
        <div class="document-card fade-in fade-in-delay-<?= ($i % 3) + 1 ?>">
            <a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="document-card-preview" aria-label="Preview <?= e($doc['title']) ?>">
                <?php if ($previewUrl): ?>
                <img src="<?= e($previewUrl) ?>" alt="Preview of <?= e($doc['title']) ?>" loading="lazy">
                <?php else: ?>
                <div class="document-card-fallback"><?= icon('document', 'h-10 w-10') ?></div>
                <?php endif; ?>
                <span class="document-card-preview-label">
                    <?= icon('eye', 'h-3.5 w-3.5') ?>
                    Preview
                </span>
            </a>
            <div class="document-card-body">
                <span class="audience-badge"><?= e($doc['audience']) ?></span>
                <p class="font-heading font-bold text-ink text-sm mt-2.5 leading-snug"><?= e($doc['title']) ?></p>
                <p class="text-xs font-semibold mt-1 text-slate-400">PDF &middot; <?= e(scot_format_bytes($doc['size'])) ?></p>
                <a href="<?= e($fileUrl) ?>" download class="document-card-download">
                    <?= icon('arrow-down-tray', 'h-4 w-4') ?>
                    Download
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center fade-in max-w-lg mx-auto">
        <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl" style="background:rgba(10,31,68,.06);">
            <?= icon('document', 'h-7 w-7 text-slate-400') ?>
        </div>
        <h3 class="font-heading font-bold text-lg text-ink mb-2">No documents yet</h3>
        <p class="text-sm text-slate-400 leading-6">
            Academic calendars, timetables, and other resources will appear here once published.
        </p>
    </div>
    <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
