<?php
require_once __DIR__ . '/includes/db.php';
$pdo = db();

$studentProjects = array_map(static function (array $r) use ($pdo): array {
    $stackStmt = $pdo->prepare('SELECT tech_text FROM student_project_stack WHERE project_id = ? ORDER BY sort_order');
    $stackStmt->execute([$r['id']]);

    return [
        'title'        => $r['title'],
        'student'      => $r['student_name'],
        'programme'    => $r['programme_label'],
        'supervisor'   => $r['supervisor'],
        'coSupervisor' => $r['co_supervisor'],
        'video'        => $r['video_path'],
        'poster'       => $r['poster_path'],
        'summary'      => $r['summary'],
        'stack'        => $stackStmt->fetchAll(PDO::FETCH_COLUMN),
    ];
}, $pdo->query("SELECT * FROM student_projects WHERE status='active' ORDER BY sort_order")->fetchAll());

$activePage = 'projects';
$pageTitle = 'Student Projects — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     STUDENT PROJECTS
════════════════════════════════════════════════════════════ -->
<section id="projects" class="section-plain py-20 scroll-mt-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">From the Department</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Student Projects</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Real final-year work from SCOT students, built and demoed as part of their degree.
        </p>
    </div>

    <?php foreach ($studentProjects as $project): ?>
    <div class="grid gap-10 lg:grid-cols-[0.85fr_1.15fr] items-start">
        <div class="fade-in">
            <div class="rounded-2xl overflow-hidden shadow-2xl border border-slate-200" style="aspect-ratio:9/16;">
                <video controls preload="none" playsinline muted
                       poster="<?= IMAGES_URL ?>/<?= e($project['poster']) ?>"
                       class="w-full h-full object-cover bg-black">
                    <source src="<?= BASE_URL ?>/assets/<?= e($project['video']) ?>" type="video/mp4">
                </video>
            </div>
        </div>
        <div class="fade-in fade-in-delay-2">
            <h3 class="font-heading font-black text-2xl sm:text-3xl text-ink leading-snug"><?= e($project['title']) ?></h3>
            <p class="mt-3 text-sm font-bold text-scotsaBlue"><?= e($project['student']) ?> &middot; <?= e($project['programme']) ?></p>
            <p class="mt-1 text-sm text-slate-500">
                Supervised by <?= e($project['supervisor']) ?>
                <?php if (!empty($project['coSupervisor'])): ?> &amp; <?= e($project['coSupervisor']) ?><?php endif; ?>
            </p>
            <p class="mt-5 text-sm leading-7 text-slate-600 whitespace-pre-line text-justify"><?= e($project['summary']) ?></p>
            <div class="mt-7 flex flex-wrap gap-2.5">
                <?php foreach ($project['stack'] as $tech): ?>
                <span class="rounded-full border border-slate-200 px-3.5 py-1.5 text-sm font-semibold text-slate-600"><?= e($tech) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
