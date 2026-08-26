<?php
/* Admin Dashboard: Analytics Workspace */
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/admin_header.php';

$pdo = db();

/* ── Core stats ──────────────────────────────────────────────── */
$totalPrograms          = (int) $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn();
$totalCourses           = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$activeResources        = (int) $pdo->query("SELECT COUNT(*) FROM past_questions WHERE status='active'")->fetchColumn();
$totalDownloads         = (int) $pdo->query("SELECT COUNT(*) FROM downloads")->fetchColumn();
$publishedAnnouncements = (int) $pdo->query("SELECT COUNT(*) FROM announcements WHERE status='published'")->fetchColumn();
$totalFileSize          = (int) $pdo->query("SELECT COALESCE(SUM(file_size),0) FROM past_questions WHERE status='active'")->fetchColumn();

/* ── Today's pulse ───────────────────────────────────────────── */
$todayUploads   = (int) $pdo->query("SELECT COUNT(*) FROM past_questions WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$todayDownloads = (int) $pdo->query("SELECT COUNT(*) FROM downloads WHERE DATE(downloaded_at) = CURDATE()")->fetchColumn();

/* ── Week-over-week change ───────────────────────────────────── */
$resourcesThisWeek = (int) $pdo->query("SELECT COUNT(*) FROM past_questions WHERE status='active' AND created_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
$resourcesLastWeek = (int) $pdo->query("SELECT COUNT(*) FROM past_questions WHERE status='active' AND created_at BETWEEN DATE_SUB(NOW(),INTERVAL 14 DAY) AND DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
$downloadsThisWeek = (int) $pdo->query("SELECT COUNT(*) FROM downloads WHERE downloaded_at >= DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();
$downloadsLastWeek = (int) $pdo->query("SELECT COUNT(*) FROM downloads WHERE downloaded_at BETWEEN DATE_SUB(NOW(),INTERVAL 14 DAY) AND DATE_SUB(NOW(),INTERVAL 7 DAY)")->fetchColumn();

/* ── 14-day time-series for charts ──────────────────────────── */
$uploadsRaw   = $pdo->query("SELECT DATE(created_at) d, COUNT(*) n FROM past_questions WHERE created_at >= DATE_SUB(NOW(),INTERVAL 13 DAY) GROUP BY d")->fetchAll(PDO::FETCH_KEY_PAIR);
$downloadsRaw = $pdo->query("SELECT DATE(downloaded_at) d, COUNT(*) n FROM downloads WHERE downloaded_at >= DATE_SUB(NOW(),INTERVAL 13 DAY) GROUP BY d")->fetchAll(PDO::FETCH_KEY_PAIR);

$chartLabels = $chartUploads = $chartDownloads = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $chartLabels[]    = date('j M', strtotime($d));
    $chartUploads[]   = (int) ($uploadsRaw[$d]   ?? 0);
    $chartDownloads[] = (int) ($downloadsRaw[$d] ?? 0);
}

/* ── Resource type distribution ──────────────────────────────── */
$typeRows = $pdo->query("SELECT resource_type, COUNT(*) cnt FROM past_questions WHERE status='active' GROUP BY resource_type")->fetchAll(PDO::FETCH_KEY_PAIR);

/* ── Top resources ───────────────────────────────────────────── */
$topResources = $pdo->query("
    SELECT pq.id, pq.title, pq.resource_type, pq.download_count,
           pq.file_size, pq.created_at, c.code
    FROM past_questions pq JOIN courses c ON c.id = pq.course_id
    WHERE pq.status = 'active'
    ORDER BY pq.download_count DESC, pq.created_at DESC LIMIT 8
")->fetchAll();

/* ── Activity feed (uploads + announcements merged) ──────────── */
$recentUploads = $pdo->query("
    SELECT pq.title, pq.resource_type, pq.created_at, c.code, 'upload' AS kind
    FROM past_questions pq JOIN courses c ON c.id = pq.course_id
    ORDER BY pq.created_at DESC LIMIT 5
")->fetchAll();
$recentAnnouncements = $pdo->query("
    SELECT title, COALESCE(published_at, created_at) AS created_at,
           'announcement' AS kind, '' AS code, '' AS resource_type
    FROM announcements WHERE status='published'
    ORDER BY created_at DESC LIMIT 3
")->fetchAll();
$activity = $recentUploads;
foreach ($recentAnnouncements as $a) $activity[] = $a;
usort($activity, fn($a, $b) => strtotime((string)$b['created_at']) - strtotime((string)$a['created_at']));
$activity = array_slice($activity, 0, 7);

/* ── Lookup maps ─────────────────────────────────────────────── */
$typeLabel = [
    'past_question' => 'Past Question',
    'midsem'        => 'Mid-Semester',
    'end_sem'       => 'End-Semester',
    'lecture_note'  => 'Lecture Note',
];
$typePill = [
    'past_question' => 'bg-blue-50 text-blue-700 ring-blue-200   dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800',
    'midsem'        => 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800',
    'end_sem'       => 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:ring-emerald-800',
    'lecture_note'  => 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-900/30 dark:text-violet-300 dark:ring-violet-800',
];

/* ── PHP helpers ─────────────────────────────────────────────── */
function fmt_size(int $b): string {
    if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
    if ($b >= 1024)    return round($b / 1024) . ' KB';
    return $b . ' B';
}
function week_delta(int $now, int $prev): array {
    if ($now === 0 && $prev === 0) return ['text' => 'No activity yet', 'up' => null];
    if ($prev === 0)               return ['text' => '+' . $now . ' this week', 'up' => true];
    $pct = (int) round((($now - $prev) / max($prev, 1)) * 100);
    return ['text' => ($pct >= 0 ? '+' : '') . $pct . '% vs last week', 'up' => $pct >= 0];
}
function time_ago(string $ts): string {
    $diff = time() - strtotime($ts);
    if ($diff < 60)     return 'just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('j M', strtotime($ts));
}

$hour     = (int) date('H');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$resDelta = week_delta($resourcesThisWeek, $resourcesLastWeek);
$dlDelta  = week_delta($downloadsThisWeek, $downloadsLastWeek);

/* JSON for charts */
$j_labels    = json_encode($chartLabels);
$j_uploads   = json_encode($chartUploads);
$j_downloads = json_encode($chartDownloads);
$j_typeLabels = json_encode(array_map(fn($k) => $typeLabel[$k] ?? ucwords(str_replace('_', ' ', $k)), array_keys($typeRows)));
$j_typeCounts = json_encode(array_values($typeRows));
?>

<!-- ══════════════════════════════════════════════════════════
     WELCOME HEADER
══════════════════════════════════════════════════════════ -->
<div class="mb-8">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-sm font-medium text-slate-400 dark:text-slate-500 mb-1">
                <?= date('l, j F Y') ?>
            </p>
            <h2 class="font-heading font-black text-2xl sm:text-3xl text-ink dark:text-white leading-tight">
                <?= $greeting ?>, <?= e(explode(' ', $admin['name'])[0]) ?>.
            </h2>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Here's what's happening with SCOTSA today.
            </p>
        </div>

        <!-- Today pulse pills -->
        <div class="flex flex-wrap gap-2 mt-1">
            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-900/30 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                <?= $todayUploads ?> upload<?= $todayUploads !== 1 ? 's' : '' ?> today
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/30 px-3 py-1.5 text-xs font-semibold text-blue-700 dark:text-blue-400">
                <span class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse inline-block"></span>
                <?= $todayDownloads ?> download<?= $todayDownloads !== 1 ? 's' : '' ?> today
            </span>
            <?php if ($publishedAnnouncements): ?>
            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/30 px-3 py-1.5 text-xs font-semibold text-amber-700 dark:text-amber-400">
                <?= $publishedAnnouncements ?> active notice<?= $publishedAnnouncements !== 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     STAT CARDS
══════════════════════════════════════════════════════════ -->
<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">

    <!-- Programs -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
        <div class="absolute top-0 left-0 h-0.5 w-full bg-gradient-to-r from-scotsaBlue to-scotsaLight"></div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Programs</p>
                <div class="mt-2 font-heading font-black text-4xl text-ink dark:text-white" data-count="<?= $totalPrograms ?>" data-suffix="">0</div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Academic programmes offered</p>
            </div>
            <div class="flex-shrink-0 grid h-11 w-11 place-items-center rounded-xl" style="background:rgba(10,31,68,.08);">
                <svg class="h-5 w-5" style="color:#0A1F44" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-1.5">
            <a href="programs.php" class="text-xs font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">Manage programs →</a>
        </div>
    </div>

    <!-- Courses -->
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
        <div class="absolute top-0 left-0 h-0.5 w-full bg-gradient-to-r from-violet-500 to-blue-500"></div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Courses</p>
                <div class="mt-2 font-heading font-black text-4xl text-ink dark:text-white" data-count="<?= $totalCourses ?>" data-suffix="">0</div>
                <p class="mt-1.5 text-xs text-slate-400 dark:text-slate-500">Across all programmes</p>
            </div>
            <div class="flex-shrink-0 grid h-11 w-11 place-items-center rounded-xl" style="background:rgba(139,92,246,.10);">
                <svg class="h-5 w-5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-1.5">
            <a href="courses.php" class="text-xs font-semibold text-violet-600 dark:text-violet-400 hover:underline">Manage courses →</a>
        </div>
    </div>

    <!-- Active Resources -->
    <?php $resClass = $resDelta['up'] === true ? 'text-emerald-600 dark:text-emerald-400' : ($resDelta['up'] === false ? 'text-red-500 dark:text-red-400' : 'text-slate-400'); ?>
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
        <div class="absolute top-0 left-0 h-0.5 w-full bg-gradient-to-r from-scotsaGold to-amber-400"></div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Resources</p>
                <div class="mt-2 font-heading font-black text-4xl text-ink dark:text-white" data-count="<?= $activeResources ?>" data-suffix="">0</div>
                <p class="mt-1.5 text-xs <?= $resClass ?> font-medium flex items-center gap-0.5">
                    <?php if ($resDelta['up'] === true): ?>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <?php elseif ($resDelta['up'] === false): ?>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <?php endif; ?>
                    <?= e($resDelta['text']) ?>
                </p>
            </div>
            <div class="flex-shrink-0 grid h-11 w-11 place-items-center rounded-xl" style="background:rgba(212,175,55,.12);">
                <svg class="h-5 w-5" style="color:#D4AF37" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-between">
            <a href="uploads.php" class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline">Upload resource →</a>
            <span class="text-xs text-slate-400 dark:text-slate-500"><?= fmt_size($totalFileSize) ?> used</span>
        </div>
    </div>

    <!-- Downloads -->
    <?php $dlClass = $dlDelta['up'] === true ? 'text-emerald-600 dark:text-emerald-400' : ($dlDelta['up'] === false ? 'text-red-500 dark:text-red-400' : 'text-slate-400'); ?>
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5">
        <div class="absolute top-0 left-0 h-0.5 w-full bg-gradient-to-r from-emerald-500 to-teal-400"></div>
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">Downloads</p>
                <div class="mt-2 font-heading font-black text-4xl text-ink dark:text-white" data-count="<?= $totalDownloads ?>" data-suffix="">0</div>
                <p class="mt-1.5 text-xs <?= $dlClass ?> font-medium flex items-center gap-0.5">
                    <?php if ($dlDelta['up'] === true): ?>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <?php elseif ($dlDelta['up'] === false): ?>
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <?php endif; ?>
                    <?= e($dlDelta['text']) ?>
                </p>
            </div>
            <div class="flex-shrink-0 grid h-11 w-11 place-items-center rounded-xl" style="background:rgba(16,185,129,.10);">
                <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-xs text-slate-400 dark:text-slate-500">All-time student downloads</span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     CHARTS ROW
══════════════════════════════════════════════════════════ -->
<div class="grid gap-4 lg:grid-cols-5 mb-6">

    <!-- Activity Line Chart (3/5) -->
    <div class="lg:col-span-3 rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div>
                <h3 class="font-heading font-black text-base text-ink dark:text-white">Platform Activity</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Uploads &amp; downloads · last 14 days</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <span class="flex items-center gap-1.5 text-amber-600 dark:text-amber-400">
                    <span class="h-2.5 w-5 rounded-full inline-block" style="background:#D4AF37;"></span> Uploads
                </span>
                <span class="flex items-center gap-1.5 text-blue-600 dark:text-blue-400">
                    <span class="h-2.5 w-5 rounded-full inline-block bg-blue-500"></span> Downloads
                </span>
            </div>
        </div>
        <div class="relative" style="height:220px;">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <!-- Resource Type Doughnut (2/5) -->
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm">
        <div class="mb-5">
            <h3 class="font-heading font-black text-base text-ink dark:text-white">Resource Types</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Distribution of uploaded materials</p>
        </div>
        <?php if (array_sum($typeRows) > 0): ?>
        <div class="relative" style="height:160px;">
            <canvas id="typeChart"></canvas>
        </div>
        <div class="mt-4 grid gap-1.5">
            <?php foreach ($typeRows as $type => $cnt): ?>
            <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <span class="type-dot-<?= $type ?> h-2 w-2 rounded-full inline-block flex-shrink-0"></span>
                    <?= e($typeLabel[$type] ?? $type) ?>
                </span>
                <span class="font-bold text-ink dark:text-white"><?= $cnt ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="grid h-12 w-12 place-items-center rounded-xl mb-3" style="background:rgba(10,31,68,.06);">
                <svg class="h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">No resources yet</p>
            <a href="uploads.php" class="mt-3 text-xs font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">Upload the first one →</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     ACTIVITY FEED + QUICK ACTIONS
══════════════════════════════════════════════════════════ -->
<div class="grid gap-4 lg:grid-cols-3 mb-6">

    <!-- Activity Feed (2/3) -->
    <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-heading font-black text-base text-ink dark:text-white">Recent Activity</h3>
                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Latest platform events</p>
            </div>
            <a href="uploads.php" class="text-xs font-semibold text-scotsaBlue dark:text-blue-400 hover:underline">View all</a>
        </div>

        <?php if ($activity): ?>
        <div class="relative">
            <!-- Timeline line -->
            <div class="absolute left-[15px] top-2 bottom-2 w-px bg-slate-100 dark:bg-white/[.06]"></div>

            <div class="space-y-0">
                <?php foreach ($activity as $i => $item): ?>
                <?php
                    $isUpload = $item['kind'] === 'upload';
                    $dotColor = $isUpload ? 'bg-scotsaGold' : 'bg-amber-400';
                    $ringColor = $isUpload ? 'ring-amber-100 dark:ring-amber-900/40' : 'ring-amber-50 dark:ring-amber-900/20';
                    $label    = $isUpload ? ($typeLabel[$item['resource_type']] ?? $item['resource_type']) : 'Announcement';
                    $pillCls  = $isUpload ? ($typePill[$item['resource_type']] ?? '') : 'bg-amber-50 text-amber-700 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:ring-amber-800';
                ?>
                <div class="flex gap-4 pb-5 last:pb-0">
                    <!-- Dot -->
                    <div class="relative flex-shrink-0 mt-1">
                        <span class="flex h-[30px] w-[30px] items-center justify-center rounded-full ring-4 <?= $ringColor ?> <?= $dotColor ?>">
                            <?php if ($isUpload): ?>
                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <?php else: ?>
                            <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                            </svg>
                            <?php endif; ?>
                        </span>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0 pt-0.5">
                        <div class="flex flex-wrap items-center gap-1.5 mb-0.5">
                            <span class="inline-flex items-center rounded-full ring-1 px-2 py-0.5 text-[10px] font-bold <?= $pillCls ?>">
                                <?= e($label) ?>
                            </span>
                            <?php if ($isUpload && $item['code']): ?>
                            <span class="text-[10px] font-mono font-semibold text-slate-400 dark:text-slate-500"><?= e($item['code']) ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-sm font-semibold text-ink dark:text-white leading-snug truncate"><?= e($item['title']) ?></p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5"><?= time_ago($item['created_at']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="text-center py-10">
            <div class="grid h-12 w-12 place-items-center rounded-xl mx-auto mb-3" style="background:rgba(10,31,68,.06);">
                <svg class="h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-slate-400 dark:text-slate-500">No recent activity</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Actions (1/3) -->
    <div class="rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] p-5 shadow-sm">
        <div class="mb-5">
            <h3 class="font-heading font-black text-base text-ink dark:text-white">Quick Actions</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Common admin tasks</p>
        </div>

        <div class="grid gap-2">
            <?php
            $actions = [
                ['Upload Resource',    'uploads.php',       'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12',                                                                   '#D4AF37', 'rgba(212,175,55,.12)'],
                ['New Announcement',   'announcements.php', 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z', '#F59E0B', 'rgba(245,158,11,.10)'],
                ['Add Course',         'courses.php',       'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', '#8B5CF6', 'rgba(139,92,246,.10)'],
                ['Add Program',        'programs.php',      'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', '#0A1F44', 'rgba(10,31,68,.08)'],
                ['View Students',      'users.php',         'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',               '#10B981', 'rgba(16,185,129,.10)'],
                ['View Live Site',     '../index.php',      'M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14',                                                   '#64748B', 'rgba(100,116,139,.08)'],
            ];
            foreach ($actions as [$label, $href, $path, $iconColor, $iconBg]):
            ?>
            <a href="<?= $href ?>"
               class="group flex items-center gap-3 rounded-xl border border-slate-100 dark:border-white/[.05] px-3.5 py-2.5
                      hover:border-slate-200 dark:hover:border-white/10 hover:shadow-sm
                      transition-all duration-200 hover:-translate-x-0">
                <div class="grid h-8 w-8 flex-shrink-0 place-items-center rounded-lg transition-transform duration-200 group-hover:scale-110"
                     style="background:<?= $iconBg ?>;">
                    <svg class="h-4 w-4" style="color:<?= $iconColor ?>;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200 group-hover:text-ink dark:group-hover:text-white transition-colors"><?= $label ?></span>
                <svg class="ml-auto h-3.5 w-3.5 text-slate-300 dark:text-slate-600 group-hover:text-slate-400 dark:group-hover:text-slate-400 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     TOP RESOURCES TABLE
══════════════════════════════════════════════════════════ -->
<div class="rounded-2xl border border-slate-200 bg-white dark:border-white/[.07] dark:bg-[#111b2e] shadow-sm overflow-hidden">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-slate-100 dark:border-white/[.05]">
        <div>
            <h3 class="font-heading font-black text-base text-ink dark:text-white">Resource Library</h3>
            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">All active resources · sorted by downloads</p>
        </div>
        <a href="uploads.php" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-white/10 px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 transition hover:bg-slate-50 dark:hover:bg-white/5">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Upload Resource
        </a>
    </div>

    <?php if ($topResources): ?>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[600px] text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-white/[.03] border-b border-slate-100 dark:border-white/[.05]">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Resource</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Course</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Type</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Size</th>
                    <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Downloads</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Uploaded</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-white/[.04]">
                <?php foreach ($topResources as $res): ?>
                <?php $pill = $typePill[$res['resource_type']] ?? 'bg-slate-50 text-slate-600 ring-slate-200'; ?>
                <tr class="group hover:bg-slate-50/60 dark:hover:bg-white/[.025] transition-colors duration-150">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="grid h-8 w-8 flex-shrink-0 place-items-center rounded-lg" style="background:rgba(212,175,55,.10);">
                                <svg class="h-4 w-4" style="color:#D4AF37" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <span class="font-semibold text-ink dark:text-white truncate max-w-[220px]" title="<?= e($res['title']) ?>">
                                <?= e($res['title']) ?>
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="font-mono text-xs font-bold text-slate-500 dark:text-slate-400 bg-slate-100 dark:bg-white/[.06] px-2 py-0.5 rounded">
                            <?= e($res['code']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3.5">
                        <span class="inline-flex items-center rounded-full ring-1 px-2.5 py-1 text-[11px] font-semibold <?= $pill ?>">
                            <?= e($typeLabel[$res['resource_type']] ?? $res['resource_type']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3.5 text-xs text-slate-400 dark:text-slate-500 font-mono">
                        <?= fmt_size((int) $res['file_size']) ?>
                    </td>
                    <td class="px-4 py-3.5 text-right">
                        <div class="flex items-center justify-end gap-1 text-sm font-black text-ink dark:text-white">
                            <?= (int) $res['download_count'] ?>
                            <?php if ($res['download_count'] > 0): ?>
                            <svg class="h-3.5 w-3.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-right text-xs text-slate-400 dark:text-slate-500">
                        <?= date('j M Y', strtotime($res['created_at'])) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div class="grid h-16 w-16 place-items-center rounded-2xl mx-auto mb-5" style="background:rgba(10,31,68,.05);">
            <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
        </div>
        <h4 class="font-heading font-bold text-lg text-ink dark:text-white mb-1">No resources uploaded yet</h4>
        <p class="text-sm text-slate-400 dark:text-slate-500 max-w-xs mb-5">Upload PDFs to see them appear here with download analytics.</p>
        <a href="uploads.php" class="btn-primary">Upload First Resource</a>
    </div>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════
     CHARTS INITIALIZATION
══════════════════════════════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
(function () {
    'use strict';

    const dark = () => document.documentElement.classList.contains('dark');

    const PALETTE = {
        gold:    '#D4AF37',
        blue:    '#3B82F6',
        navy:    '#0A1F44',
        emerald: '#10B981',
        amber:   '#F59E0B',
        violet:  '#8B5CF6',
        red:     '#EF4444',
        slate:   '#CBD5E1',
    };

    function themeVars() {
        const d = dark();
        return {
            grid:    d ? 'rgba(255,255,255,.05)' : 'rgba(0,0,0,.05)',
            tick:    d ? 'rgba(255,255,255,.35)'  : 'rgba(15,23,42,.40)',
            tooltip: d ? '#1a2744'                : '#ffffff',
            tooltipText: d ? '#e2e8f0' : '#0f172a',
            tooltipBorder: d ? 'rgba(255,255,255,.10)' : 'rgba(0,0,0,.08)',
        };
    }

    /* ── Activity Line Chart ──────────────────────────────── */
    const actCtx = document.getElementById('activityChart');
    if (actCtx) {
        const tv = themeVars();
        new Chart(actCtx, {
            type: 'line',
            data: {
                labels: <?= $j_labels ?>,
                datasets: [
                    {
                        label: 'Uploads',
                        data: <?= $j_uploads ?>,
                        borderColor: PALETTE.gold,
                        backgroundColor: 'rgba(212,175,55,.10)',
                        borderWidth: 2,
                        pointBackgroundColor: PALETTE.gold,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                    },
                    {
                        label: 'Downloads',
                        data: <?= $j_downloads ?>,
                        borderColor: PALETTE.blue,
                        backgroundColor: 'rgba(59,130,246,.08)',
                        borderWidth: 2,
                        pointBackgroundColor: PALETTE.blue,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tv.tooltip,
                        borderColor: tv.tooltipBorder,
                        borderWidth: 1,
                        titleColor: tv.tooltipText,
                        bodyColor: tv.tooltipText,
                        padding: 10,
                        cornerRadius: 10,
                    },
                },
                scales: {
                    x: {
                        grid: { color: tv.grid, drawBorder: false },
                        ticks: {
                            color: tv.tick,
                            font: { size: 10 },
                            maxRotation: 0,
                            maxTicksLimit: 7,
                        },
                        border: { display: false },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: tv.grid, drawBorder: false },
                        ticks: {
                            color: tv.tick,
                            font: { size: 10 },
                            precision: 0,
                            stepSize: 1,
                        },
                        border: { display: false },
                    },
                },
            },
        });
    }

    /* ── Resource Type Doughnut ───────────────────────────── */
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx) {
        const donutColors = [PALETTE.navy, PALETTE.amber, PALETTE.emerald, PALETTE.violet];
        /* Also set CSS dot colours for the legend */
        document.querySelectorAll('[class*="type-dot-"]').forEach((el, i) => {
            el.style.background = donutColors[i] ?? PALETTE.slate;
        });

        const tv = themeVars();
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: <?= $j_typeLabels ?>,
                datasets: [{
                    data: <?= $j_typeCounts ?>,
                    backgroundColor: donutColors,
                    borderWidth: 2,
                    borderColor: dark() ? '#111b2e' : '#ffffff',
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: tv.tooltip,
                        borderColor: tv.tooltipBorder,
                        borderWidth: 1,
                        titleColor: tv.tooltipText,
                        bodyColor: tv.tooltipText,
                        padding: 10,
                        cornerRadius: 10,
                    },
                },
            },
        });
    }
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
