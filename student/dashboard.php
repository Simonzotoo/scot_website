<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/student_header.php';

$pdo = db();

$availableCount = 0;
if ($student['program_id'] && $student['level_id']) {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM past_questions pq JOIN courses c ON c.id = pq.course_id
         WHERE pq.status = 'active' AND c.program_id = ? AND c.level_id = ?"
    );
    $stmt->execute([$student['program_id'], $student['level_id']]);
    $availableCount = (int) $stmt->fetchColumn();
}

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM downloads WHERE user_id = ?');
$countStmt->execute([$student['id']]);
$myDownloadTotal = (int) $countStmt->fetchColumn();

$recommended = [];
if ($student['program_id'] && $student['level_id']) {
    $stmt = $pdo->prepare(
        "SELECT pq.id, pq.title, pq.resource_type, pq.academic_year, pq.file_size, pq.created_at, c.code
         FROM past_questions pq JOIN courses c ON c.id = pq.course_id
         WHERE pq.status = 'active' AND c.program_id = ? AND c.level_id = ?
         ORDER BY pq.created_at DESC LIMIT 6"
    );
    $stmt->execute([$student['program_id'], $student['level_id']]);
    $recommended = $stmt->fetchAll();
}

$myDownloads = $pdo->prepare(
    "SELECT d.downloaded_at, pq.id, pq.title, c.code
     FROM downloads d
     JOIN past_questions pq ON pq.id = d.past_question_id
     JOIN courses c ON c.id = pq.course_id
     WHERE d.user_id = ?
     ORDER BY d.downloaded_at DESC LIMIT 5"
);
$myDownloads->execute([$student['id']]);
$myDownloads = $myDownloads->fetchAll();

$announcements = $pdo->query(
    "SELECT title, body, published_at FROM announcements WHERE status = 'published' ORDER BY published_at DESC LIMIT 3"
)->fetchAll();

$typeLabel = [
    'past_question' => 'Past Question',
    'midsem'        => 'Midsem Paper',
    'end_sem'       => 'End-of-Sem',
    'lecture_note'  => 'Lecture Note',
];
$typeIcon = [
    'past_question' => 'document',
    'midsem'        => 'pencil-square',
    'end_sem'       => 'clipboard-list',
    'lecture_note'  => 'book-open',
];

function fmt_size(int $b): string
{
    if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
    if ($b >= 1024)    return round($b / 1024) . ' KB';
    return $b . ' B';
}
?>

<div class="mb-8">
    <h1 class="font-heading font-black text-2xl sm:text-3xl text-ink dark:text-white">
        Welcome, <?= e(explode(' ', $student['full_name'])[0]) ?>
    </h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
        <?= e($student['program_name'] ?? 'No program set') ?><?= $student['level_name'] ? ' · ' . e($student['level_name']) : '' ?>
    </p>
</div>

<div class="grid gap-4 sm:grid-cols-3 mb-8">
    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Available to you</p>
        <p class="mt-2 font-heading font-black text-3xl text-ink dark:text-white"><?= $availableCount ?></p>
        <p class="mt-1 text-xs text-slate-400">Resources matching your program &amp; level</p>
    </div>
    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Your downloads</p>
        <p class="mt-2 font-heading font-black text-3xl text-ink dark:text-white"><?= $myDownloadTotal ?></p>
        <p class="mt-1 text-xs text-slate-400">Most recent shown below</p>
    </div>
    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Announcements</p>
        <p class="mt-2 font-heading font-black text-3xl text-ink dark:text-white"><?= count($announcements) ?></p>
        <p class="mt-1 text-xs text-slate-400">Latest from SCOTSA</p>
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-3">

    <div class="lg:col-span-2 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-heading font-black text-base text-ink dark:text-white">Recommended for you</h2>
            <a href="<?= BASE_URL ?>/resources.php<?= $student['program_id'] ? '?program_id=' . (int) $student['program_id'] . '&level_id=' . (int) $student['level_id'] : '' ?>"
               class="text-xs font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">Browse all</a>
        </div>

        <?php if ($recommended): ?>
        <div class="grid gap-3 sm:grid-cols-2">
            <?php foreach ($recommended as $r): ?>
            <div class="rounded-lg border border-slate-100 dark:border-white/5 p-4">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 grid h-9 w-9 place-items-center rounded-lg bg-blue-50 dark:bg-blue-900/20 text-scotsaBlue dark:text-blue-400">
                        <?= icon($typeIcon[$r['resource_type']] ?? 'document', 'h-4 w-4') ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-ink dark:text-white truncate"><?= e($r['code']) ?></p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate" title="<?= e($r['title']) ?>"><?= e($r['title']) ?></p>
                    </div>
                </div>
                <div class="mt-3 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400"><?= e($typeLabel[$r['resource_type']] ?? $r['resource_type']) ?> · <?= fmt_size((int) $r['file_size']) ?></span>
                    <a href="<?= BASE_URL ?>/download.php?id=<?= (int) $r['id'] ?>" class="text-xs font-bold text-scotsaBlue dark:text-blue-400 hover:underline">Download</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php elseif (!$student['program_id'] || !$student['level_id']): ?>
        <div class="rounded-lg border border-dashed border-slate-200 dark:border-white/10 p-8 text-center">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Set your program and level to see recommendations.</p>
            <a href="profile.php" class="mt-3 inline-block text-xs font-bold text-scotsaBlue dark:text-blue-400 hover:underline">Update profile</a>
        </div>
        <?php else: ?>
        <div class="rounded-lg border border-dashed border-slate-200 dark:border-white/10 p-8 text-center">
            <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">No resources for your program &amp; level yet.</p>
            <a href="<?= BASE_URL ?>/resources.php" class="mt-3 inline-block text-xs font-bold text-scotsaBlue dark:text-blue-400 hover:underline">Browse everything</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
        <h2 class="font-heading font-black text-base text-ink dark:text-white mb-4">Latest Announcements</h2>
        <?php if ($announcements): ?>
        <div class="grid gap-4">
            <?php foreach ($announcements as $a): ?>
            <div class="border-b border-slate-100 dark:border-white/5 pb-4 last:border-0 last:pb-0">
                <p class="text-sm font-bold text-ink dark:text-white"><?= e($a['title']) ?></p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 line-clamp-2"><?= e($a['body']) ?></p>
                <p class="mt-1.5 text-[11px] text-slate-400"><?= e(date('M j, Y', strtotime($a['published_at']))) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <a href="<?= BASE_URL ?>/announcements.php" class="mt-4 inline-block text-xs font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">View all announcements</a>
        <?php else: ?>
        <p class="text-sm text-slate-400">No announcements yet.</p>
        <?php endif; ?>
    </div>
</div>

<div class="mt-6 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5">
    <h2 class="font-heading font-black text-base text-ink dark:text-white mb-4">Recent Downloads</h2>
    <?php if ($myDownloads): ?>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[480px] text-sm">
            <thead>
                <tr class="text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 border-b border-slate-100 dark:border-white/5">
                    <th class="py-2 pr-4">Course</th>
                    <th class="py-2 pr-4">Title</th>
                    <th class="py-2 text-right">Downloaded</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($myDownloads as $d): ?>
                <tr class="border-b border-slate-50 dark:border-white/[.03] last:border-0">
                    <td class="py-2.5 pr-4 font-mono text-xs font-bold text-slate-500"><?= e($d['code']) ?></td>
                    <td class="py-2.5 pr-4 text-slate-700 dark:text-slate-200 truncate max-w-[260px]"><?= e($d['title']) ?></td>
                    <td class="py-2.5 text-right text-xs text-slate-400"><?= e(date('M j, Y g:ia', strtotime($d['downloaded_at']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <p class="text-sm text-slate-400">You haven't downloaded anything yet. <a href="<?= BASE_URL ?>/resources.php" class="font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">browse resources</a> to get started.</p>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
