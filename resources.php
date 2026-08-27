<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Academic Resources: SCOTSA';

$filters = [
    'q'          => trim($_GET['q'] ?? ''),
    'program_id' => $_GET['program_id'] ?? '',
    'level_id'   => $_GET['level_id'] ?? '',
    'semester_id'=> $_GET['semester_id'] ?? '',
    'type'       => $_GET['type'] ?? '',
    'exam_month' => $_GET['exam_month'] ?? '',
    'exam_year'  => $_GET['exam_year'] ?? '',
];

['programs' => $programs, 'levels' => $levels, 'semesters' => $semesters, 'examYears' => $examYears] = fetch_resource_filter_options();
['resources' => $resources, 'pagination' => $pg] = search_resources($filters, 12);

$pageQuery = $_GET;
unset($pageQuery['page']);
$paginationBaseQuery = http_build_query($pageQuery);

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white"<?= $pageHeroStyle ?>>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10" style="padding-top:4.5rem; padding-bottom:4.5rem;">
        <nav class="mb-6 flex items-center gap-2 text-xs" style="color:rgba(191,219,254,.55);">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">Resources</span>
        </nav>
        <span class="eyebrow">Resource Portal</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-2xl leading-tight">
            Past questions &amp; academic materials.
        </h1>
        <p class="max-w-xl text-sm leading-7" style="color:rgba(191,219,254,.72);">
            Search and filter by program, level, semester, course, type, and exam month/year.
            All files are verified and ready to download instantly.
        </p>
    </div>
</section>

<!-- ── Filter Panel ───────────────────────────────────── -->
<section class="border-b border-slate-200 bg-white shadow-sm sticky top-[65px] z-30">
    <div class="mx-auto max-w-7xl px-4 py-5 sm:px-6 lg:px-8">
        <form class="grid gap-3 md:grid-cols-4 lg:grid-cols-8 items-end" method="get">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Search</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input class="form-input pl-9" name="q" value="<?= e($filters['q']) ?>"
                           placeholder="Course code, title, or keyword…">
                </div>
            </div>

            <!-- Program -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Program</label>
                <select class="form-input" name="program_id">
                    <option value="">All programs</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= e((string)$p['id']) ?>" <?= (string)$p['id'] === (string)$filters['program_id'] ? 'selected' : '' ?>>
                            <?= e($p['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Level -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Level</label>
                <select class="form-input" name="level_id">
                    <option value="">All levels</option>
                    <?php foreach ($levels as $l): ?>
                        <option value="<?= e((string)$l['id']) ?>" <?= (string)$l['id'] === (string)$filters['level_id'] ? 'selected' : '' ?>>
                            <?= e($l['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Semester -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Semester</label>
                <select class="form-input" name="semester_id">
                    <option value="">All semesters</option>
                    <?php foreach ($semesters as $s): ?>
                        <option value="<?= e((string)$s['id']) ?>" <?= (string)$s['id'] === (string)$filters['semester_id'] ? 'selected' : '' ?>>
                            <?= e($s['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Type -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Type</label>
                <select class="form-input" name="type">
                    <option value="">All types</option>
                    <?php foreach (ACTIVE_RESOURCE_TYPES as $val => $label): ?>
                        <option value="<?= e($val) ?>" <?= $filters['type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Exam month -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Month</label>
                <select class="form-input" name="exam_month">
                    <option value="">Any month</option>
                    <?php foreach (EXAM_MONTHS as $val => $label): ?>
                        <option value="<?= $val ?>" <?= (string) $val === (string) $filters['exam_month'] ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Exam year -->
            <div>
                <label class="block text-xs font-bold text-slate-500 mb-1.5 tracking-wide uppercase">Year</label>
                <select class="form-input" name="exam_year">
                    <option value="">Any year</option>
                    <?php foreach ($examYears as $y): ?>
                        <option value="<?= (int) $y ?>" <?= (string) $y === (string) $filters['exam_year'] ? 'selected' : '' ?>><?= (int) $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Submit -->
            <button class="btn-primary w-full" type="submit">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                </svg>
                Filter
            </button>
        </form>

        <!-- Active filter chips -->
        <?php if (array_filter($filters)): ?>
        <div class="mt-3 flex items-center gap-2 flex-wrap">
            <span class="text-xs text-slate-400 font-semibold">Active filters:</span>
            <?php if ($filters['q']): ?>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-scotsaBlue">
                    "<?= e($filters['q']) ?>"
                </span>
            <?php endif; ?>
            <?php if ($filters['type']): ?>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-scotsaBlue">
                    <?= e(ACTIVE_RESOURCE_TYPES[$filters['type']] ?? $filters['type']) ?>
                </span>
            <?php endif; ?>
            <?php if ($filters['exam_month']): ?>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-scotsaBlue">
                    <?= e(EXAM_MONTHS[(int) $filters['exam_month']] ?? $filters['exam_month']) ?>
                </span>
            <?php endif; ?>
            <?php if ($filters['exam_year']): ?>
                <span class="rounded-full bg-blue-50 border border-blue-200 px-3 py-1 text-xs font-semibold text-scotsaBlue">
                    <?= e((string) $filters['exam_year']) ?>
                </span>
            <?php endif; ?>
            <a href="resources.php" class="text-xs font-bold text-red-500 hover:text-red-600 transition ml-1">
                Clear all ×
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Results Grid ───────────────────────────────────── -->
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">

    <?php if ($resources): ?>
    <p class="text-sm text-slate-400 mb-6 font-medium">
        Showing <strong class="text-ink font-bold"><?= count($resources) ?></strong>
        of <strong class="text-ink font-bold"><?= $pg['totalRows'] ?></strong>
        resource<?= $pg['totalRows'] !== 1 ? 's' : '' ?>
        <?php if (array_filter($filters)): ?><span class="text-slate-300 mx-1">·</span>
            <a href="resources.php" class="text-xs text-slate-400 hover:text-red-500 transition">clear filters</a>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($resources as $i => $r): ?>
            <?php
            $typeMeta = [
                'past_question' => ['Past Question', 'bg-blue-50 text-blue-700 border-blue-200',     'document'],
                'midsem'        => ['Midsem Paper',  'bg-amber-50 text-amber-700 border-amber-200',   'pencil-square'],
                'end_sem'       => ['End-of-Sem',    'bg-purple-50 text-purple-700 border-purple-200','clipboard-list'],
                'lecture_note'  => ['Lecture Note',  'bg-emerald-50 text-emerald-700 border-emerald-200','book-open'],
            ];
            [$typeLabel, $typeClass, $typeIcon] = $typeMeta[$r['resource_type']] ?? [ucwords(str_replace('_', ' ', $r['resource_type'])), 'bg-slate-50 text-slate-600 border-slate-200', 'document'];
            $fileExt = strtoupper(pathinfo($r['original_filename'], PATHINFO_EXTENSION) ?: 'FILE');
            $periodText = $r['exam_month'] && $r['exam_year']
                ? (EXAM_MONTHS[(int) $r['exam_month']] ?? '') . ' ' . $r['exam_year']
                : '';
            ?>
            <article class="card-hover group flex flex-col rounded-xl border border-slate-200 bg-white p-6 fade-in fade-in-delay-<?= ($i % 3) + 1 ?>">
                <!-- Header -->
                <div class="flex items-start justify-between gap-3 mb-4">
                    <div class="min-w-0">
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold <?= $typeClass ?>">
                            <?= icon($typeIcon, 'h-3 w-3') ?> <?= $typeLabel ?>
                        </span>
                        <h2 class="font-heading font-black text-xl text-ink mt-2 group-hover:text-scotsaBlue transition-colors"><?= e($r['code']) ?></h2>
                    </div>
                    <span class="flex-shrink-0 rounded-lg px-2.5 py-1 text-xs font-bold"
                          style="background:rgba(10,31,68,.07); color:#0A1F44; border:1px solid rgba(10,31,68,.12);"><?= e($fileExt) ?></span>
                </div>

                <!-- Title -->
                <p class="font-semibold text-slate-700 text-sm leading-snug"><?= e($r['title']) ?></p>
                <?php if ($periodText): ?>
                <p class="mt-1 text-xs text-slate-400 font-medium"><?= e($periodText) ?></p>
                <?php endif; ?>

                <!-- Meta chips -->
                <div class="mt-3 flex flex-wrap gap-1.5">
                    <?php foreach ([e($r['program_name']), e($r['level_name']), e($r['semester_name'])] as $chip): ?>
                    <span class="rounded-full bg-slate-100 border border-slate-200 px-2.5 py-0.5 text-[10px] font-semibold text-slate-500"><?= $chip ?></span>
                    <?php endforeach; ?>
                </div>

                <!-- Footer -->
                <div class="mt-auto pt-5 flex items-center justify-between border-t border-slate-100">
                    <span class="flex items-center gap-1.5 text-xs text-slate-400">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <?= (int)$r['download_count'] ?> download<?= (int)$r['download_count'] !== 1 ? 's' : '' ?>
                    </span>
                    <a class="btn-primary text-xs" href="download.php?id=<?= (int)$r['id'] ?>">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download
                    </a>
                </div>
            </article>
        <?php endforeach; ?>

        <?php if (!$resources): ?>
            <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-14 text-center md:col-span-2 lg:col-span-3 fade-in">
                <div class="mx-auto mb-4 grid h-14 w-14 place-items-center rounded-2xl" style="background:rgba(10,31,68,.06);">
                    <svg class="h-7 w-7 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="font-heading font-bold text-slate-500 text-lg">No resources match your filters.</p>
                <p class="text-sm text-slate-400 mt-1.5 leading-6">Try different keywords or clear your active filters.</p>
                <a href="resources.php" class="mt-6 inline-block btn-primary">Clear filters</a>
            </div>
        <?php endif; ?>
    </div>

    <?= pagination_links($pg, $paginationBaseQuery) ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
