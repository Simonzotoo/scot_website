<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

/**
 * Faculty. 'leadership' roster = Dean + Heads (rendered together on the
 * public page); 'faculty' roster = Lecturers. Each member's three list
 * tabs (courses taught / publications / education) come from their own
 * child tables; research_interest is a single column.
 */
function scot_load_faculty_member(PDO $pdo, array $r): array
{
    $fetch = static function (string $table) use ($pdo, $r): array {
        $stmt = $pdo->prepare("SELECT text_value FROM `$table` WHERE member_id = ? ORDER BY sort_order");
        $stmt->execute([$r['id']]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    };

    return [
        'role'              => $r['role'],
        'name'              => $r['name'],
        'portfolio'         => $r['portfolio'],
        'bio'               => $r['bio'],
        'photo'             => $r['photo_path'],
        'email'             => $r['email'],
        'courses_taught'    => $fetch('faculty_courses_taught'),
        'research_interest' => $r['research_interest'],
        'publications'      => $fetch('faculty_publications'),
        'education'         => $fetch('faculty_education'),
    ];
}

$leadershipRows = $pdo->query("SELECT * FROM team_members WHERE roster='leadership' ORDER BY sort_order")->fetchAll();
$dean = null;
$heads = [];
foreach ($leadershipRows as $i => $r) {
    $member = scot_load_faculty_member($pdo, $r);
    if ($i === 0) {
        $dean = $member;
    } else {
        $heads[] = $member;
    }
}
$lecturers = array_map(
    static fn (array $r) => scot_load_faculty_member($pdo, $r),
    $pdo->query("SELECT * FROM team_members WHERE roster='faculty' ORDER BY sort_order")->fetchAll()
);
// Faculty Office staff (Faculty Officer + Deputy) — administrative, not
// academic, so these render as plain name/role/photo cards rather than
// the clickable five-tab profile the heads and lecturers get.
$officers = array_map(
    static fn (array $r) => scot_load_faculty_member($pdo, $r),
    $pdo->query("SELECT * FROM team_members WHERE roster='officer' ORDER BY sort_order")->fetchAll()
);

$activePage = 'faculty';
$pageTitle = 'Faculty — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     FACULTY — Heads of Department, Lecturers
════════════════════════════════════════════════════════════ -->
<section id="faculty" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Faculty</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Heads of Department and Lecturers</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Tap a photo to view a full profile — courses taught, research interests, publications, and education.
        </p>
    </div>

    <?php
    // Shared card renderer for heads of department / lecturers. Clicking a
    // card opens the full-page profile (#faculty-page), not a small modal —
    // this function renders the trigger button plus a hidden content block
    // holding that person's five profile panels (Overview / Courses Taught /
    // Research Interest / Publications / Education), which JS copies into
    // the shared panel containers when the page opens.
    function scot_faculty_card(array $f): void {
        static $facultyIdCounter = 0;
        $id = 'faculty-' . (++$facultyIdCounter);

        $displayName = $f['name'] ?: 'Awaiting Appointment';
        $photoUrl    = $f['photo'] ? IMAGES_URL . '/' . $f['photo'] : avatar_url(null, $displayName);
    ?>
    <button type="button"
            class="card-hover group rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in text-left w-full"
            data-faculty-trigger="<?= $id ?>"
            data-name="<?= e($displayName) ?>"
            data-role="<?= e($f['role']) ?>"
            data-portfolio="<?= e($f['portfolio'] ?? '') ?>"
            data-photo="<?= e($photoUrl) ?>"
            data-email="<?= e($f['email'] ?? '') ?>">
        <div class="relative overflow-hidden bg-slate-100" style="padding-top:115%;">
            <img src="<?= e($photoUrl) ?>" alt="<?= e($displayName) ?>"
                 class="absolute inset-0 h-full w-full object-cover"
                 style="object-position:50% 15%;"
                 loading="lazy"
                 data-fallback="<?= IMAGES_URL ?>/placeholders/avatar.svg">
        </div>
        <div class="p-5 text-center">
            <p class="font-heading font-bold text-ink text-sm"><?= e($displayName) ?></p>
            <p class="mt-1 text-xs font-semibold text-scotsaBlue"><?= e($f['role']) ?></p>
            <?php if ($f['portfolio']): ?>
            <p class="mt-1 text-xs text-slate-400 leading-5"><?= e($f['portfolio']) ?></p>
            <?php endif; ?>
        </div>
    </button>

    <div class="hidden" data-faculty-content="<?= $id ?>">
        <div data-panel="overview">
            <p class="text-sm leading-7 text-slate-600 whitespace-pre-line text-justify"><?= e($f['bio'] ?: 'Biography coming soon.') ?></p>
        </div>
        <div data-panel="courses">
            <?php if (!empty($f['courses_taught'])): ?>
            <ul class="space-y-2.5">
                <?php foreach ($f['courses_taught'] as $course): ?>
                <li class="text-sm text-slate-600 flex items-start gap-2 text-justify">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($course) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Course list to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="research">
            <?php if (!empty($f['research_interest'])): ?>
            <p class="text-sm leading-7 text-slate-600 text-justify"><?= e($f['research_interest']) ?></p>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Research interests to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="publications">
            <?php if (!empty($f['publications'])): ?>
            <ul class="space-y-3">
                <?php foreach ($f['publications'] as $pub): ?>
                <li class="text-sm leading-6 text-slate-600 flex items-start gap-2 text-justify">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($pub) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Publications to be added.</p>
            <?php endif; ?>
        </div>
        <div data-panel="education">
            <?php if (!empty($f['education'])): ?>
            <ul class="space-y-3">
                <?php foreach ($f['education'] as $edu): ?>
                <li class="text-sm leading-6 text-slate-600 flex items-start gap-2 text-justify">
                    <span class="mt-2 h-1.5 w-1.5 rounded-full flex-shrink-0" style="background:#D4AF37;"></span>
                    <?= e($edu) ?>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php else: ?>
            <p class="text-sm text-slate-400 italic">Educational background to be added.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php } ?>

    <!-- Dean & Heads of Department — the Dean also lectures, so he's listed
         here too (first) with the same clickable profile, in addition to
         his own spotlight section on the Home page. -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Dean &amp; Heads of Department</span>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 max-w-3xl mx-auto mb-14">
        <?php foreach (array_merge([$dean], $heads) as $head): scot_faculty_card($head); ?>
        <?php endforeach; ?>
    </div>

    <!-- Lecturers -->
    <div class="mb-6 text-center fade-in">
        <span class="eyebrow">Lecturers</span>
    </div>
    <?php if ($lecturers): ?>
    <div class="flex flex-wrap justify-center gap-6">
        <?php foreach ($lecturers as $lecturer): ?>
        <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.125rem)]">
            <?php scot_faculty_card($lecturer); ?>
        </div>
        <?php endforeach; ?>
        <?php
        // Open seats — a plain "vacant" tile, not a clickable fake profile.
        // Auto-sized to round the grid up to a clean multiple of 4 (so it
        // shrinks on its own as real lecturers are added via the admin
        // dashboard, down to zero once the roster fills a full row).
        $lecturerPlaceholders = (4 - (count($lecturers) % 4)) % 4;
        for ($i = 0; $i < $lecturerPlaceholders; $i++):
        ?>
        <div class="w-full sm:w-[calc(50%-0.75rem)] lg:w-[calc(25%-1.125rem)]">
            <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 overflow-hidden fade-in h-full">
                <div class="relative overflow-hidden" style="padding-top:115%;">
                    <div class="absolute inset-0 grid place-items-center">
                        <?= icon('user-circle', 'h-12 w-12 text-slate-300') ?>
                    </div>
                </div>
                <div class="p-5 text-center">
                    <p class="font-heading font-bold text-slate-400 text-sm">New Lecturer <?= $i + 1 ?></p>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <?php else: ?>
    <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center fade-in max-w-lg mx-auto">
        <div class="mx-auto mb-5 grid h-14 w-14 place-items-center rounded-2xl" style="background:rgba(10,31,68,.06);">
            <?= icon('users', 'h-7 w-7 text-slate-400') ?>
        </div>
        <h3 class="font-heading font-bold text-lg text-ink mb-2">Lecturer profiles coming soon</h3>
        <p class="text-sm text-slate-400 leading-6">
            Full lecturer profiles for the School of Computing and Technology will appear here once published.
        </p>
    </div>
    <?php endif; ?>

    <?php if ($officers): ?>
    <!-- Faculty Office — administrative staff, shown as plain cards -->
    <div class="mt-16 mb-6 text-center fade-in">
        <span class="eyebrow">Faculty Office</span>
    </div>
    <div class="grid gap-6 sm:grid-cols-2 max-w-2xl mx-auto">
        <?php foreach ($officers as $officer):
            $officerName  = $officer['name'] ?: 'Awaiting Appointment';
            $officerPhoto = $officer['photo'] ? IMAGES_URL . '/' . $officer['photo'] : avatar_url(null, $officerName);
        ?>
        <div class="card-hover rounded-2xl border border-slate-200 bg-white overflow-hidden fade-in">
            <div class="relative overflow-hidden bg-slate-100" style="padding-top:115%;">
                <img src="<?= e($officerPhoto) ?>" alt="<?= e($officerName) ?>"
                     class="absolute inset-0 h-full w-full object-cover"
                     style="object-position:50% 15%;"
                     loading="lazy"
                     data-fallback="<?= IMAGES_URL ?>/placeholders/avatar.svg">
            </div>
            <div class="p-5 text-center">
                <p class="font-heading font-bold text-ink text-sm"><?= e($officerName) ?></p>
                <p class="mt-1 text-xs font-semibold text-scotsaBlue"><?= e($officer['role']) ?></p>
                <?php if ($officer['email']): ?>
                <a href="mailto:<?= e($officer['email']) ?>" class="mt-1.5 inline-block text-xs text-slate-400 hover:text-scotsaBlue transition-colors break-all"><?= e($officer['email']) ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════════
     FACULTY PROFILE PAGE — full-viewport takeover
════════════════════════════════════════════════════════════ -->
<div id="faculty-page">
    <div class="faculty-page-inner">
        <div class="faculty-page-topbar">
            <button type="button" id="faculty-page-close" class="faculty-page-back" aria-label="Back">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </button>
            <div class="flex items-center gap-3">
                <button type="button" id="faculty-page-prev" class="faculty-page-nav-btn" aria-label="Previous">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" id="faculty-page-next" class="faculty-page-nav-btn" aria-label="Next">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="faculty-page-header">
            <img id="faculty-page-photo" src="" alt=""
                 data-fallback="<?= IMAGES_URL ?>/placeholders/avatar.svg">
            <div>
                <span class="eyebrow">School of Computing and Technology</span>
                <h2 id="faculty-page-name" class="font-heading font-black text-3xl text-ink mt-2"></h2>
                <p id="faculty-page-role" class="mt-1 text-sm font-bold text-scotsaBlue"></p>
                <p id="faculty-page-portfolio" class="mt-1 text-sm text-slate-500"></p>
                <a id="faculty-page-email" href="" class="mt-4 hidden items-center gap-1.5 text-xs font-bold text-scotsaBlue">
                    <?= icon('mail', 'h-3.5 w-3.5') ?>
                    <span id="faculty-page-email-text"></span>
                </a>
            </div>
        </div>

        <nav class="faculty-page-tabs" aria-label="Profile sections">
            <button type="button" class="faculty-tab-btn active" data-faculty-tab="overview">Overview</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="courses">Courses Taught</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="research">Research Interest</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="publications">Publications</button>
            <button type="button" class="faculty-tab-btn" data-faculty-tab="education">Education</button>
        </nav>

        <div id="faculty-page-panel-overview" class="faculty-tab-panel active"></div>
        <div id="faculty-page-panel-courses" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-research" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-publications" class="faculty-tab-panel"></div>
        <div id="faculty-page-panel-education" class="faculty-tab-panel"></div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
