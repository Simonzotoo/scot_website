<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

/**
 * Programmes + course structures. 'undergraduate' holds BSc and Diploma
 * rows together (the template itself filters by tag when it needs one or
 * the other) to match how the two groups render from one array; 'courses'
 * is keyed by level ('100'..'400') with 'lower'/'upper' semester lists of
 * [code,title] pairs for BSc/Diploma, or by 'core'/'elective' plain-title
 * lists for MSc. Short Courses carry no 'courses' key at all.
 */
function scot_load_programmes(PDO $pdo): array
{
    $out = ['undergraduate' => [], 'postgraduate' => [], 'certificate' => []];

    $progRows = $pdo->query("SELECT * FROM programs ORDER BY FIELD(tag,'BSc','Diploma','MSc','Short Courses'), sort_order")->fetchAll();
    $courseStmt = $pdo->prepare('SELECT * FROM courses WHERE program_id = ? ORDER BY sort_order');
    $levelNames = [1 => '100', 2 => '200', 3 => '300', 4 => '400'];
    $semNames = [1 => 'lower', 2 => 'upper'];

    foreach ($progRows as $p) {
        if ($p['tag'] === 'Short Courses') {
            $out['certificate'][] = ['tag' => 'Short Courses', 'name' => $p['name']];
            continue;
        }

        $entry = [
            'tag'  => $p['tag'],
            'name' => $p['name'],
            'desc' => $p['description'],
            'code' => strtoupper($p['slug']),
        ];
        if (!empty($p['note'])) {
            $entry['note'] = $p['note'];
        }

        $courseStmt->execute([$p['id']]);
        $rows = $courseStmt->fetchAll();

        if ($p['tag'] === 'MSc') {
            $courses = ['core' => [], 'elective' => []];
            foreach ($rows as $c) {
                if (isset($courses[$c['group_label']])) {
                    $courses[$c['group_label']][] = $c['title'];
                }
            }
            $entry['courses'] = $courses;
            $out['postgraduate'][] = $entry;
        } else {
            $courses = [];
            foreach ($rows as $c) {
                $levelKey = $levelNames[(int) $c['level_id']] ?? null;
                $semKey = $semNames[(int) $c['semester_id']] ?? null;
                if ($levelKey === null || $semKey === null) {
                    continue;
                }
                $courses[$levelKey][$semKey][] = [$c['code'], $c['title']];
            }
            // Ensure every level present in the data has both semester
            // keys defined (even if empty), matching the original
            // hand-written arrays the template was built against.
            foreach ($courses as $levelKey => &$sems) {
                $sems += ['lower' => [], 'upper' => []];
            }
            unset($sems);
            $entry['courses'] = $courses;
            $out['undergraduate'][] = $entry;
        }
    }

    return $out;
}
$programmes = scot_load_programmes($pdo);

$activePage = 'programmes';
$pageTitle = 'Programmes — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     PROGRAMMES
════════════════════════════════════════════════════════════ -->
<section id="programmes" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">What We Offer</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Programmes</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Undergraduate, diploma, and postgraduate programmes offered by the School of Computing and Technology.
            Tap a card to view its course structure by level and semester.
        </p>
    </div>

    <div class="mb-8 flex flex-wrap gap-2 fade-in">
        <?php foreach (['All', 'BSc', 'MSc', 'Diploma', 'Short Courses'] as $i => $filterTag): ?>
        <button type="button" class="filter-tab flex-shrink-0 <?= $i === 0 ? 'filter-active' : '' ?>" data-programme-filter="<?= e($filterTag) ?>"><?= e($filterTag) ?></button>
        <?php endforeach; ?>
    </div>

    <!-- Undergraduate (BSc) — a bento grid: BSc IT (the only programme
         with a complete Level 100-400 curriculum published) gets a wider
         featured tile; grid-auto-flow:dense packs the rest around it with
         no gaps. -->
    <div data-programme-group>
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Undergraduate</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 mb-14" style="grid-auto-flow:dense;">
        <?php
        $bscProgrammes = array_values(array_filter($programmes['undergraduate'], fn($p) => $p['tag'] === 'BSc'));
        foreach ($bscProgrammes as $i => $prog):
            $isFeatured = $i === 0;
        ?>
        <button type="button"
                class="prog-card rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?> <?= $isFeatured ? 'sm:col-span-2 lg:row-span-2' : '' ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="<?= e($prog['note'] ?? '') ?>"
                data-programme-category="<?= e($prog['tag']) ?>">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2 <?= $isFeatured ? 'text-xl' : '' ?>"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <?php if ($isFeatured): ?>
            <div class="mt-5 pt-5 border-t border-slate-100 flex items-center gap-2 text-xs font-semibold text-slate-500">
                <?= icon('academic-cap', 'h-4 w-4 text-scotsaBlue') ?>
                Complete curriculum published — Level 100 through Level 400
            </div>
            <?php endif; ?>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue">
                View Course Structure
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </button>

        <!-- Hidden course-structure content, pulled into #course-modal-body on click -->
        <div class="course-structure-content hidden" data-course-content="<?= e($prog['code']) ?>">
            <?php foreach ($prog['courses'] as $level => $sems): ?>
            <div class="mb-7">
                <h5 class="font-heading font-black text-ink text-sm uppercase tracking-wide mb-3">Level <?= e($level) ?></h5>
                <div class="grid gap-5 sm:grid-cols-2">
                    <?php foreach (['lower' => 'First Semester', 'upper' => 'Second Semester'] as $sem => $semLabel): ?>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2"><?= $semLabel ?></p>
                        <?php if ($sems[$sem]): ?>
                        <ul class="space-y-1.5">
                            <?php foreach ($sems[$sem] as [$code, $title]): ?>
                            <li class="text-sm text-slate-600"><span class="font-bold text-scotsaBlue"><?= e($code) ?></span> &mdash; <?= e($title) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-sm text-slate-400 italic">Not yet published.</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    </div>

    <div data-programme-group>
    <!-- Postgraduate -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Postgraduate</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($programmes['postgraduate'] as $i => $prog): ?>
        <button type="button"
                class="prog-card prog-card--postgrad rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note=""
                data-programme-category="<?= e($prog['tag']) ?>">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue">
                View Course Structure
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </button>

        <!-- Hidden course-structure content (Core / Elective), pulled into #course-modal-body on click -->
        <div class="course-structure-content hidden" data-course-content="<?= e($prog['code']) ?>">
            <?php foreach (['core' => 'Core Courses', 'elective' => 'Elective Courses'] as $group => $groupLabel): ?>
            <div class="mb-7">
                <h5 class="font-heading font-black text-ink text-sm uppercase tracking-wide mb-3"><?= $groupLabel ?></h5>
                <ul class="space-y-1.5">
                    <?php foreach ($prog['courses'][$group] as $courseName): ?>
                    <li class="text-sm text-slate-600"><?= e($courseName) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    </div>

    <div data-programme-group>
    <!-- Diploma -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Diploma</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php
        $diplomaProgrammes = array_values(array_filter($programmes['undergraduate'], fn($p) => $p['tag'] === 'Diploma'));
        foreach ($diplomaProgrammes as $i => $prog):
        ?>
        <button type="button"
                class="prog-card rounded-xl border border-slate-200 bg-white p-6 text-left w-full fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
                data-course-trigger="<?= e($prog['code']) ?>"
                data-course-title="<?= e($prog['tag'] . ' ' . $prog['name']) ?>"
                data-course-tag="<?= e($prog['tag']) ?>"
                data-course-note="<?= e($prog['note'] ?? '') ?>"
                data-programme-category="<?= e($prog['tag']) ?>">
            <span class="prog-kicker"><?= e($prog['tag']) ?></span>
            <h4 class="font-heading font-bold text-ink leading-snug mt-2"><?= e($prog['name']) ?></h4>
            <p class="mt-2 text-sm leading-6 text-slate-500"><?= e($prog['desc']) ?></p>
            <span class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-scotsaBlue">
                View Course Structure
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        </button>

        <!-- Hidden course-structure content, pulled into #course-modal-body on click -->
        <div class="course-structure-content hidden" data-course-content="<?= e($prog['code']) ?>">
            <?php foreach ($prog['courses'] as $level => $sems): ?>
            <div class="mb-7">
                <h5 class="font-heading font-black text-ink text-sm uppercase tracking-wide mb-3">Level <?= e($level) ?></h5>
                <div class="grid gap-5 sm:grid-cols-2">
                    <?php foreach (['lower' => 'First Semester', 'upper' => 'Second Semester'] as $sem => $semLabel): ?>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-2"><?= $semLabel ?></p>
                        <?php if ($sems[$sem]): ?>
                        <ul class="space-y-1.5">
                            <?php foreach ($sems[$sem] as [$code, $title]): ?>
                            <li class="text-sm text-slate-600"><span class="font-bold text-scotsaBlue"><?= e($code) ?></span> &mdash; <?= e($title) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="text-sm text-slate-400 italic">Not yet published.</p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    </div>

    <div data-programme-group>
    <!-- Short Courses — short standalone courses, so these are plain
         (non-clickable) cards with no course-structure modal, unlike the
         degree programme cards above. -->
    <h3 class="font-heading font-black text-sm uppercase tracking-wide text-slate-400 mb-5 fade-in">Short Courses</h3>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($programmes['certificate'] as $i => $prog): ?>
        <div class="prog-card rounded-xl border border-slate-200 bg-white p-6 fade-in fade-in-delay-<?= ($i % 3) + 1 ?>"
             data-programme-category="<?= e($prog['tag']) ?>">
            <h4 class="font-heading font-bold text-ink leading-snug text-sm"><?= e($prog['name']) ?></h4>
        </div>
        <?php endforeach; ?>
    </div>
    </div>
    </div>
</section>

<!-- Course structure modal — content is swapped in per-programme on click -->
<div id="course-modal" class="lightbox" role="dialog" aria-modal="true" aria-label="Course structure">
    <button id="course-modal-close"
            class="absolute top-5 right-5 grid h-10 w-10 place-items-center rounded-xl text-white/60 hover:text-white transition z-10"
            style="background:rgba(255,255,255,.10); border:1px solid rgba(255,255,255,.20);"
            aria-label="Close">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <div class="course-modal-card">
        <div class="course-modal-header">
            <span id="course-modal-tag" class="eyebrow"></span>
            <h3 id="course-modal-title" class="font-heading font-black text-2xl text-ink mt-2"></h3>
            <p id="course-modal-note" class="mt-1 text-sm text-slate-400 hidden"></p>
        </div>
        <div id="course-modal-body" class="course-modal-body"></div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
