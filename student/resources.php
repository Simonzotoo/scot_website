<?php
$pageTitle = 'Browse Resources';
require_once __DIR__ . '/../includes/student_header.php';

$pdo = db();

$filters = [
    'q'          => trim($_GET['q'] ?? ''),
    'program_id' => $_GET['program_id'] ?? '',
    'level_id'   => $_GET['level_id'] ?? '',
    'semester_id'=> $_GET['semester_id'] ?? '',
    'type'       => $_GET['type'] ?? '',
    'exam_month' => $_GET['exam_month'] ?? '',
    'exam_year'  => $_GET['exam_year'] ?? '',
];

$programs  = $pdo->query('SELECT id, name FROM programs ORDER BY name')->fetchAll();
$levels    = $pdo->query('SELECT id, name FROM levels ORDER BY id')->fetchAll();
$semesters = $pdo->query('SELECT id, name FROM semesters ORDER BY id')->fetchAll();
$examYears = $pdo->query(
    'SELECT DISTINCT exam_year FROM past_questions WHERE exam_year IS NOT NULL ORDER BY exam_year DESC'
)->fetchAll(PDO::FETCH_COLUMN);

$resources = [];
try {
    $activeTypePlaceholders = implode(',', array_fill(0, count(ACTIVE_RESOURCE_TYPES), '?'));
    $where  = "WHERE pq.status = \"active\" AND pq.resource_type IN ($activeTypePlaceholders)";
    $params = array_keys(ACTIVE_RESOURCE_TYPES);

    if ($filters['q'] !== '') {
        $where .= ' AND (c.code LIKE ? OR c.title LIKE ? OR pq.title LIKE ?)';
        $t = '%' . $filters['q'] . '%';
        array_push($params, $t, $t, $t);
    }
    foreach (['program_id' => 'c.program_id', 'level_id' => 'c.level_id', 'semester_id' => 'c.semester_id'] as $key => $col) {
        if ($filters[$key] !== '') {
            $where .= " AND {$col} = ?";
            $params[] = (int) $filters[$key];
        }
    }
    if ($filters['type'] !== '') {
        $where .= ' AND pq.resource_type = ?';
        $params[] = $filters['type'];
    }
    if ($filters['exam_month'] !== '') {
        $where .= ' AND pq.exam_month = ?';
        $params[] = (int) $filters['exam_month'];
    }
    if ($filters['exam_year'] !== '') {
        $where .= ' AND pq.exam_year = ?';
        $params[] = (int) $filters['exam_year'];
    }

    $joins = 'FROM past_questions pq
              JOIN courses c ON c.id = pq.course_id
              JOIN programs p ON p.id = c.program_id
              JOIN levels l ON l.id = c.level_id
              JOIN semesters s ON s.id = c.semester_id';

    $countStmt = $pdo->prepare("SELECT COUNT(*) $joins $where");
    $countStmt->execute($params);
    $pg = paginate((int) $countStmt->fetchColumn(), 12);

    $sql = "SELECT pq.*, c.code, c.title AS course_title, p.name AS program_name, l.name AS level_name, s.name AS semester_name
            $joins
            $where
            ORDER BY pq.created_at DESC
            LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $resources = $stmt->fetchAll();
} catch (Throwable $e) {
    $resources = [];
    $pg = paginate(0, 12);
}

$pageQuery = $_GET;
unset($pageQuery['page']);
$paginationBaseQuery = http_build_query($pageQuery);

$typeMeta = [
    'past_question' => ['Past Question', 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/20 dark:text-blue-300 dark:border-blue-900/40', 'document'],
    'midsem'        => ['Midsem Paper',  'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/20 dark:text-amber-300 dark:border-amber-900/40', 'pencil-square'],
    'end_sem'       => ['End-of-Sem',    'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/20 dark:text-purple-300 dark:border-purple-900/40', 'clipboard-list'],
    'lecture_note'  => ['Lecture Note',  'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300 dark:border-emerald-900/40', 'book-open'],
];
?>

<div class="mb-6">
    <h1 class="font-heading font-black text-2xl sm:text-3xl text-ink dark:text-white">Browse Resources</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Search and filter every resource by course, programme, type, month, and year.</p>
</div>

<!-- Filter panel -->
<div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5 mb-6">
    <form class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 items-end" method="get">
        <div class="sm:col-span-2 xl:col-span-2">
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Search</label>
            <input class="form-input" name="q" value="<?= e($filters['q']) ?>" placeholder="Course code, title, or keyword…">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Programme</label>
            <select class="form-input" name="program_id">
                <option value="">All programmes</option>
                <?php foreach ($programs as $p): ?>
                    <option value="<?= (int) $p['id'] ?>" <?= (string) $p['id'] === (string) $filters['program_id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Level</label>
            <select class="form-input" name="level_id">
                <option value="">All levels</option>
                <?php foreach ($levels as $l): ?>
                    <option value="<?= (int) $l['id'] ?>" <?= (string) $l['id'] === (string) $filters['level_id'] ? 'selected' : '' ?>><?= e($l['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Semester</label>
            <select class="form-input" name="semester_id">
                <option value="">All semesters</option>
                <?php foreach ($semesters as $s): ?>
                    <option value="<?= (int) $s['id'] ?>" <?= (string) $s['id'] === (string) $filters['semester_id'] ? 'selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Type</label>
            <select class="form-input" name="type">
                <option value="">All types</option>
                <?php foreach (ACTIVE_RESOURCE_TYPES as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= $filters['type'] === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Month</label>
            <select class="form-input" name="exam_month">
                <option value="">Any month</option>
                <?php foreach (EXAM_MONTHS as $val => $label): ?>
                    <option value="<?= $val ?>" <?= (string) $val === (string) $filters['exam_month'] ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-1.5 tracking-wide uppercase">Year</label>
            <select class="form-input" name="exam_year">
                <option value="">Any year</option>
                <?php foreach ($examYears as $y): ?>
                    <option value="<?= (int) $y ?>" <?= (string) $y === (string) $filters['exam_year'] ? 'selected' : '' ?>><?= (int) $y ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="sm:col-span-2 lg:col-span-4 xl:col-span-7 flex items-center gap-3">
            <button class="btn-primary" type="submit">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Filter
            </button>
            <?php if (array_filter($filters)): ?>
            <a href="resources.php" class="text-xs font-bold text-red-500 hover:text-red-400 transition">Clear all ×</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if ($resources): ?>
<p class="text-sm text-slate-500 dark:text-slate-400 mb-4 font-medium">
    Showing <strong class="text-ink dark:text-white font-bold"><?= count($resources) ?></strong>
    of <strong class="text-ink dark:text-white font-bold"><?= $pg['totalRows'] ?></strong>
    resource<?= $pg['totalRows'] !== 1 ? 's' : '' ?>
</p>
<?php endif; ?>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($resources as $r):
        [$typeLabel, $typeClass, $typeIcon] = $typeMeta[$r['resource_type']] ?? [ucwords(str_replace('_', ' ', $r['resource_type'])), 'bg-slate-50 text-slate-600 border-slate-200 dark:bg-slate-700/40 dark:text-slate-300 dark:border-white/10', 'document'];
        $fileExt = strtoupper(pathinfo($r['original_filename'], PATHINFO_EXTENSION) ?: 'FILE');
        $periodText = $r['exam_month'] && $r['exam_year']
            ? (EXAM_MONTHS[(int) $r['exam_month']] ?? '') . ' ' . $r['exam_year']
            : '';
    ?>
    <article class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#111b2e] p-5 flex flex-col">
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="min-w-0">
                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-bold <?= $typeClass ?>">
                    <?= icon($typeIcon, 'h-3 w-3') ?> <?= e($typeLabel) ?>
                </span>
                <h2 class="font-heading font-black text-lg text-ink dark:text-white mt-2"><?= e($r['code']) ?></h2>
            </div>
            <span class="flex-shrink-0 rounded-lg px-2.5 py-1 text-xs font-bold bg-blue-50 dark:bg-blue-900/20 text-scotsaBlue dark:text-blue-300 border border-blue-100 dark:border-blue-900/40"><?= e($fileExt) ?></span>
        </div>

        <p class="font-semibold text-slate-700 dark:text-slate-200 text-sm leading-snug"><?= e($r['title']) ?></p>
        <?php if ($periodText): ?>
        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500 font-medium"><?= e($periodText) ?></p>
        <?php endif; ?>

        <div class="mt-3 flex flex-wrap gap-1.5">
            <?php foreach ([e($r['program_name']), e($r['level_name']), e($r['semester_name'])] as $chip): ?>
            <span class="rounded-full bg-slate-100 dark:bg-slate-700/50 border border-slate-200 dark:border-white/10 px-2.5 py-0.5 text-[10px] font-semibold text-slate-500 dark:text-slate-400"><?= $chip ?></span>
            <?php endforeach; ?>
        </div>

        <div class="mt-auto pt-4 flex items-center justify-between border-t border-slate-100 dark:border-white/5">
            <span class="flex items-center gap-1.5 text-xs text-slate-400 dark:text-slate-500">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <?= (int) $r['download_count'] ?>
            </span>
            <a class="btn-primary text-xs" href="<?= BASE_URL ?>/download.php?id=<?= (int) $r['id'] ?>">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download
            </a>
        </div>
    </article>
    <?php endforeach; ?>

    <?php if (!$resources): ?>
    <div class="sm:col-span-2 xl:col-span-3 rounded-xl border border-dashed border-slate-300 dark:border-white/10 bg-slate-50 dark:bg-white/[.02] p-14 text-center">
        <p class="font-heading font-bold text-slate-500 dark:text-slate-300 text-lg">No resources match your filters.</p>
        <p class="text-sm text-slate-400 dark:text-slate-500 mt-1.5">Try different keywords or clear your active filters.</p>
        <a href="resources.php" class="mt-6 inline-block btn-primary">Clear filters</a>
    </div>
    <?php endif; ?>
</div>

<div class="mt-6">
<?= pagination_links($pg, $paginationBaseQuery) ?>
</div>

<?php require_once __DIR__ . '/../includes/student_footer.php'; ?>
