<?php
$pageTitle = 'Resource Library';
require_once __DIR__ . '/../includes/admin_header.php';

function safe_segment(string $value): string
{
    return preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($value)) ?: 'misc';
}

function sanitize_filename(string $name): string
{
    $name = pathinfo($name, PATHINFO_FILENAME);
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name);
    return substr(trim($name, '._'), 0, 80) ?: 'file';
}

function fmt_bytes(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024)    return round($bytes / 1024)      . ' KB';
    return $bytes . ' B';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    /* ── Upload ─────────────────────────────────────────── */
    if ($action === 'upload') {
        $courseId     = (int) ($_POST['course_id'] ?? 0);
        $title        = clean_text($_POST['title'] ?? '');
        $resourceType = $_POST['resource_type'] ?? '';
        $academicYear = clean_text($_POST['academic_year'] ?? '');
        $allowed      = ['past_question', 'midsem', 'end_sem', 'lecture_note'];
        $file         = $_FILES['pdf'] ?? null;

        if (!$courseId || !$title) {
            flash('error', 'Please fill in all required fields (course and title).');
            header('Location: uploads.php'); exit;
        }
        if (!in_array($resourceType, $allowed, true)) {
            flash('error', 'Invalid resource type selected.');
            header('Location: uploads.php'); exit;
        }
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File exceeds the server upload limit.',
                UPLOAD_ERR_FORM_SIZE  => 'File exceeds the form upload limit.',
                UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded. Please try again.',
                UPLOAD_ERR_NO_FILE    => 'No file was selected for upload.',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporary upload directory is missing on the server.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION  => 'A PHP extension blocked the upload.',
            ];
            $errCode = $file['error'] ?? UPLOAD_ERR_NO_FILE;
            flash('error', $uploadErrors[$errCode] ?? 'Upload failed (error code ' . $errCode . ').');
            header('Location: uploads.php'); exit;
        }
        if ($file['size'] > MAX_UPLOAD_BYTES) {
            flash('error', 'PDF must not exceed 10 MB. Your file is ' . round($file['size'] / 1048576, 1) . ' MB.');
            header('Location: uploads.php'); exit;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        if ($mime !== 'application/pdf') {
            flash('error', 'Only PDF files are accepted. Detected type: ' . $mime);
            header('Location: uploads.php'); exit;
        }

        try {
            $stmt = db()->prepare(
                'SELECT c.id, c.code, p.name program_name, l.name level_name, s.name semester_name
                 FROM courses c
                 JOIN programs p ON p.id = c.program_id
                 JOIN levels   l ON l.id = c.level_id
                 JOIN semesters s ON s.id = c.semester_id
                 WHERE c.id = ?'
            );
            $stmt->execute([$courseId]);
            $course = $stmt->fetch();
        } catch (Throwable $e) {
            error_log('[SCOTSA Upload] DB course fetch failed: ' . $e->getMessage());
            flash('error', 'Database error. Please try again.');
            header('Location: uploads.php'); exit;
        }
        if (!$course) {
            flash('error', 'The selected course does not exist.');
            header('Location: uploads.php'); exit;
        }

        $relativeDir = 'resources/'
            . safe_segment($course['program_name']) . '/'
            . safe_segment($course['level_name'])   . '/'
            . safe_segment($course['semester_name']);
        $targetDir = UPLOAD_ROOT . '/' . $relativeDir;

        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                error_log('[SCOTSA Upload] mkdir failed for: ' . $targetDir);
                flash('error', 'Could not create upload folder. Contact your system administrator.');
                header('Location: uploads.php'); exit;
            }
        }
        if (!is_writable($targetDir)) {
            error_log('[SCOTSA Upload] Directory not writable: ' . $targetDir);
            flash('error', 'Upload folder exists but is not writable. Contact your system administrator.');
            header('Location: uploads.php'); exit;
        }

        $safeName    = sanitize_filename($course['code']) . '-' . $resourceType . '-' . bin2hex(random_bytes(6));
        $uniqueName  = $safeName . '.pdf';
        $relativePath = $relativeDir . '/' . $uniqueName;
        $absolutePath = UPLOAD_ROOT . '/' . $relativePath;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            error_log('[SCOTSA Upload] move_uploaded_file failed. src=' . $file['tmp_name'] . ' dst=' . $absolutePath);
            flash('error', 'File could not be saved to disk. Ensure the upload folder is writable.');
            header('Location: uploads.php'); exit;
        }

        try {
            db()->prepare(
                'INSERT INTO past_questions
                    (course_id, uploaded_by, title, resource_type, academic_year, original_filename, file_path, file_size)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $courseId,
                $admin['id'],
                $title,
                $resourceType,
                $academicYear,
                $file['name'],
                $relativePath,
                $file['size'],
            ]);
        } catch (Throwable $e) {
            error_log('[SCOTSA Upload] DB insert failed: ' . $e->getMessage());
            @unlink($absolutePath);
            flash('error', 'File saved but database record failed. The file has been removed. Please try again.');
            header('Location: uploads.php'); exit;
        }

        audit_log('create', 'past_question', (int) db()->lastInsertId(), $title);
        flash('success', 'Resource uploaded successfully.');

    /* ── Delete ─────────────────────────────────────────── */
    } elseif ($action === 'delete') {
        try {
            $stmt = db()->prepare('SELECT file_path FROM past_questions WHERE id = ?');
            $stmt->execute([(int) $_POST['id']]);
            $upload = $stmt->fetch();
        } catch (Throwable $e) {
            flash('error', 'Database error during delete.');
            header('Location: uploads.php'); exit;
        }

        if ($upload) {
            $deleteId = (int) $_POST['id'];
            $path = realpath(UPLOAD_ROOT . '/' . $upload['file_path']);
            $root = realpath(UPLOAD_ROOT);
            if ($path && $root && strncmp($path, $root, strlen($root)) === 0 && is_file($path)) {
                unlink($path);
            }
            db()->prepare('DELETE FROM past_questions WHERE id = ?')->execute([$deleteId]);
            audit_log('delete', 'past_question', $deleteId);
            flash('success', 'Resource deleted.');
        } else {
            flash('error', 'Resource not found.');
        }
    }

    header('Location: uploads.php'); exit;
}

/* ── Fetch data ─────────────────────────────────────────── */
$courses = db()->query(
    'SELECT c.id, c.code, c.title,
            p.name program_name, l.name level_name, s.name semester_name
     FROM courses c
     JOIN programs p ON p.id = c.program_id
     JOIN levels   l ON l.id = c.level_id
     JOIN semesters s ON s.id = c.semester_id
     ORDER BY c.code'
)->fetchAll();

$totalUploads = (int) db()->query('SELECT COUNT(*) FROM past_questions')->fetchColumn();
$pg = paginate($totalUploads, 25);
$uploads = db()->query(
    "SELECT pq.*, c.code course_code, c.title course_title,
            p.name program_name, l.name level_name, s.name semester_name
     FROM past_questions pq
     JOIN courses  c ON c.id = pq.course_id
     JOIN programs p ON p.id = c.program_id
     JOIN levels   l ON l.id = c.level_id
     JOIN semesters s ON s.id = c.semester_id
     ORDER BY pq.created_at DESC
     LIMIT {$pg['perPage']} OFFSET {$pg['offset']}"
)->fetchAll();

$typeLabels = [
    'past_question' => 'Past Q',
    'midsem'        => 'Midsem',
    'end_sem'       => 'End-Sem',
    'lecture_note'  => 'Note',
];
$typeColors = [
    'past_question' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    'midsem'        => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
    'end_sem'       => 'bg-purple-50 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    'lecture_note'  => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
];
$now = time();
?>

<div class="space-y-6">

<!-- ── Upload Card ──────────────────────────────────────────────── -->
<div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 overflow-hidden shadow-sm">

    <!-- Card header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 dark:border-white/10
                bg-gradient-to-r from-scotsaBlue to-[#0d2a5c] dark:from-slate-900 dark:to-slate-800">
        <div>
            <h2 class="font-heading font-black text-white text-base leading-none">Upload PDF Resource</h2>
            <p class="mt-1 text-xs text-blue-200/70">PDF only · Max 10 MB · Stored securely</p>
        </div>
        <div class="h-9 w-9 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">
            <svg class="h-5 w-5 text-scotsaGold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
        </div>
    </div>

    <!-- Form body -->
    <form method="post" enctype="multipart/form-data" id="upload-form" class="p-6">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">

        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Course -->
            <div class="sm:col-span-2 xl:col-span-2">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Course</label>
                <select class="form-input" name="course_id" required>
                    <option value="">Select a course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int) $c['id'] ?>">
                            <?= e($c['code'] . ': ' . $c['title'] . ' (' . $c['level_name'] . ', ' . $c['semester_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Type -->
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Type</label>
                <select class="form-input" name="resource_type">
                    <option value="past_question">Past Question</option>
                    <option value="midsem">Midsem Paper</option>
                    <option value="end_sem">End-of-Sem Paper</option>
                    <option value="lecture_note">Lecture Note</option>
                </select>
            </div>

            <!-- Academic Year -->
            <div>
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Academic Year</label>
                <input class="form-input" name="academic_year" placeholder="2024/2025">
            </div>

            <!-- Title -->
            <div class="sm:col-span-2 xl:col-span-3">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Material Title</label>
                <input class="form-input" name="title" placeholder="e.g. CS101 End-of-Semester Exam 2024/2025" required>
            </div>

            <!-- Submit -->
            <div class="flex items-end">
                <button class="btn-primary w-full py-2.5 text-sm font-bold gap-2" type="submit">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Upload
                </button>
            </div>
        </div>

        <!-- Drop zone -->
        <div id="drop-zone"
             class="mt-5 flex flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed
                    border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-700/40
                    px-6 py-8 text-center cursor-pointer transition-all duration-200
                    hover:border-scotsaBlue dark:hover:border-scotsaGold hover:bg-blue-50/50 dark:hover:bg-scotsaBlue/10"
             onclick="document.getElementById('pdf-input').click()">
            <svg class="h-9 w-9 text-slate-300 dark:text-slate-500 transition-colors" id="drop-icon"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            <p id="drop-label" class="text-sm font-medium text-slate-400 dark:text-slate-400">
                Drop PDF here or <span class="text-scotsaBlue dark:text-scotsaGold font-bold">browse</span>
            </p>
            <p class="text-xs text-slate-300 dark:text-slate-500">PDF · maximum 10 MB</p>
            <input type="file" name="pdf" id="pdf-input" accept="application/pdf,.pdf" required class="sr-only">
        </div>
    </form>
</div>

<!-- ── Resource Library ─────────────────────────────────────────── -->
<div class="rounded-2xl border border-slate-200 dark:border-white/10 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">

    <!-- Library header -->
    <div class="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-b border-slate-100 dark:border-white/10">
        <div class="flex items-center gap-3 min-w-0">
            <h2 class="font-heading font-black text-ink dark:text-white text-base leading-none">Resource Library</h2>
            <span class="rounded-full bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-bold text-slate-500 dark:text-slate-300">
                <?= $totalUploads ?>
            </span>
        </div>

        <!-- Search + filter -->
        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input id="resource-search"
                       type="search"
                       placeholder="Search title or code…"
                       class="form-input py-1.5 pl-8 pr-3 text-sm w-52">
            </div>
            <select id="resource-type-filter" class="form-input py-1.5 text-sm">
                <option value="">All types</option>
                <option value="past_question">Past Question</option>
                <option value="midsem">Midsem</option>
                <option value="end_sem">End-of-Sem</option>
                <option value="lecture_note">Lecture Note</option>
            </select>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <?php if ($uploads): ?>
        <table class="w-full min-w-[820px] text-sm" id="resource-table">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-700/50 text-left text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                    <th class="px-5 py-3 rounded-tl-none">Course</th>
                    <th class="px-5 py-3">Title / Year</th>
                    <th class="px-5 py-3">Type</th>
                    <th class="px-5 py-3">Size</th>
                    <th class="px-5 py-3">Downloads</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody id="resource-tbody">
                <?php foreach ($uploads as $u):
                    $isNew      = ($now - strtotime($u['created_at'])) < 86400;
                    $dlUrl      = BASE_URL . '/download.php?id=' . (int) $u['id'];
                    $typeKey    = $u['resource_type'];
                    $typeLabel  = $typeLabels[$typeKey]  ?? $typeKey;
                    $typeColor  = $typeColors[$typeKey]  ?? 'bg-slate-100 text-slate-600';
                ?>
                <tr class="border-t border-slate-100 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/[.03] transition-colors duration-150 resource-row"
                    data-title="<?= strtolower(e($u['title'])) ?>"
                    data-code="<?= strtolower(e($u['course_code'])) ?>"
                    data-type="<?= e($typeKey) ?>">

                    <!-- Course code -->
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="font-black text-scotsaBlue dark:text-blue-300 text-sm"><?= e($u['course_code']) ?></span>
                        <?php if ($isNew): ?>
                        <span class="ml-1.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-[9px] font-black px-1.5 py-0.5 uppercase tracking-wide">New</span>
                        <?php endif; ?>
                    </td>

                    <!-- Title + year -->
                    <td class="px-5 py-3.5 max-w-[220px]">
                        <p class="truncate font-medium text-slate-800 dark:text-slate-100 leading-snug" title="<?= e($u['title']) ?>"><?= e($u['title']) ?></p>
                        <?php if ($u['academic_year']): ?>
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5"><?= e($u['academic_year']) ?></p>
                        <?php endif; ?>
                    </td>

                    <!-- Type badge -->
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold <?= $typeColor ?>">
                            <?= e($typeLabel) ?>
                        </span>
                    </td>

                    <!-- File size -->
                    <td class="px-5 py-3.5 text-slate-400 dark:text-slate-500 whitespace-nowrap tabular-nums">
                        <?= fmt_bytes((int) $u['file_size']) ?>
                    </td>

                    <!-- Downloads -->
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="flex items-center gap-1 text-slate-500 dark:text-slate-400 tabular-nums">
                            <svg class="h-3.5 w-3.5 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <?= (int) $u['download_count'] ?>
                        </span>
                    </td>

                    <!-- Actions -->
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1.5">
                            <!-- Preview -->
                            <button type="button"
                                    data-pdf-id="<?= (int) $u['id'] ?>"
                                    data-pdf-title="<?= e($u['title']) ?>"
                                    data-pdf-download="<?= e($dlUrl) ?>"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600
                                           bg-white dark:bg-slate-700 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300
                                           hover:border-scotsaBlue dark:hover:border-blue-400 hover:text-scotsaBlue dark:hover:text-blue-300
                                           hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all duration-150"
                                    title="Preview PDF">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Preview
                            </button>

                            <!-- Download -->
                            <a href="<?= e($dlUrl) ?>"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 dark:border-slate-600
                                      bg-white dark:bg-slate-700 px-2.5 py-1.5 text-[11px] font-bold text-slate-600 dark:text-slate-300
                                      hover:border-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300
                                      hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all duration-150"
                               title="Download PDF">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                DL
                            </a>

                            <!-- Delete -->
                            <form method="post" class="inline">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button type="button"
                                        data-confirm="Permanently delete &quot;<?= e($u['title']) ?>&quot;? This cannot be undone."
                                        class="inline-flex items-center justify-center rounded-lg border border-red-100 dark:border-red-900/50
                                               bg-red-50 dark:bg-red-900/20 p-1.5
                                               text-red-400 dark:text-red-400
                                               hover:border-red-300 dark:hover:border-red-600 hover:text-red-600 dark:hover:text-red-300
                                               hover:bg-red-100 dark:hover:bg-red-900/40 transition-all duration-150"
                                        title="Delete resource">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= pagination_links($pg) ?>

        <!-- No-results row (hidden by default) -->
        <div id="no-results" class="hidden px-6 py-12 text-center">
            <svg class="mx-auto mb-3 h-8 w-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <p class="font-bold text-slate-400 dark:text-slate-500">No resources match your search.</p>
        </div>

        <?php else: ?>
        <!-- Premium empty state -->
        <div class="flex flex-col items-center justify-center gap-4 px-6 py-16 text-center">
            <div class="relative">
                <div class="h-16 w-16 rounded-2xl flex items-center justify-center
                            bg-gradient-to-br from-scotsaBlue to-[#1a4a8a]
                            shadow-[0_8px_24px_rgba(10,31,68,.25)]">
                    <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="absolute -right-1 -top-1 h-5 w-5 rounded-full bg-scotsaGold flex items-center justify-center">
                    <svg class="h-2.5 w-2.5 text-scotsaBlue" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/>
                    </svg>
                </span>
            </div>
            <div>
                <p class="font-heading font-black text-slate-700 dark:text-slate-200 text-base">No resources yet</p>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-1 max-w-xs">
                    Upload your first PDF using the form above to start building the resource library.
                </p>
            </div>
            <button onclick="document.getElementById('pdf-input').click()"
                    class="btn-primary px-5 py-2 text-sm gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Upload first resource
            </button>
        </div>
        <?php endif; ?>
    </div>
</div>

</div><!-- /space-y-6 -->

<script>
/* ── Drop zone ──────────────────────────────────────────────────── */
(function () {
    const zone  = document.getElementById('drop-zone');
    const input = document.getElementById('pdf-input');
    const label = document.getElementById('drop-label');
    if (!zone || !input) return;

    function highlight()   { zone.classList.add('border-scotsaBlue', '!bg-blue-50/60', 'dark:!bg-scotsaBlue/10'); }
    function unhighlight() { zone.classList.remove('border-scotsaBlue', '!bg-blue-50/60', 'dark:!bg-scotsaBlue/10'); }

    zone.addEventListener('dragover',  e => { e.preventDefault(); highlight(); });
    zone.addEventListener('dragleave', unhighlight);
    zone.addEventListener('drop', e => {
        e.preventDefault();
        unhighlight();
        const file = e.dataTransfer?.files?.[0];
        if (!file) return;
        if (file.type !== 'application/pdf') {
            window.ScotSA?.toast('error', 'Only PDF files are accepted.');
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        label.innerHTML = '<span class="text-scotsaBlue dark:text-scotsaGold font-bold">' + file.name + '</span>';
    });

    input.addEventListener('change', () => {
        const f = input.files?.[0];
        if (f) label.innerHTML = '<span class="text-scotsaBlue dark:text-scotsaGold font-bold">' + f.name + '</span>';
    });
})();

/* ── Live search + type filter ──────────────────────────────────── */
(function () {
    const searchEl = document.getElementById('resource-search');
    const typeEl   = document.getElementById('resource-type-filter');
    const rows     = document.querySelectorAll('.resource-row');
    const noRes    = document.getElementById('no-results');
    if (!searchEl || !rows.length) return;

    function filter() {
        const q    = searchEl.value.toLowerCase().trim();
        const type = typeEl?.value || '';
        let visible = 0;

        rows.forEach(row => {
            const titleMatch = !q || row.dataset.title?.includes(q) || row.dataset.code?.includes(q);
            const typeMatch  = !type || row.dataset.type === type;
            const show       = titleMatch && typeMatch;
            row.classList.toggle('hidden', !show);
            if (show) visible++;
        });

        if (noRes) noRes.classList.toggle('hidden', visible > 0);
    }

    searchEl.addEventListener('input', filter);
    typeEl?.addEventListener('change', filter);
})();
</script>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
