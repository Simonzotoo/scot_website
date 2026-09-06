<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
$admin = require_admin_login();

$counts = [];
$tiles = [
    'hero'       => ['hero.php', 'Hero Slides', 'SELECT COUNT(*) FROM hero_slides'],
    'programmes' => ['programmes.php', 'Programmes', "SELECT COUNT(*) FROM programs WHERE tag != 'Short Courses'"],
    'shortcourses' => ['programmes.php', 'Short Courses', "SELECT COUNT(*) FROM programs WHERE tag = 'Short Courses'"],
    'facilities' => ['facilities.php', 'Facilities', 'SELECT COUNT(*) FROM facilities'],
    'videos'     => ['videos.php', 'Watch Videos', 'SELECT COUNT(*) FROM videos'],
    'faculty'    => ['faculty.php', 'Faculty', 'SELECT COUNT(*) FROM team_members'],
    'projects'   => ['projects.php', 'Student Projects', 'SELECT COUNT(*) FROM student_projects'],
    'blog'       => ['blog.php', 'Blog Posts', 'SELECT COUNT(*) FROM blog_posts'],
    'gallery'    => ['gallery.php', 'Gallery Photos', 'SELECT COUNT(*) FROM gallery_items'],
];
foreach ($tiles as $key => [$href, $label, $sql]) {
    $counts[$key] = (int) db()->query($sql)->fetchColumn();
}

$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$firstName = explode(' ', trim($admin['name']))[0] ?? $admin['name'];

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-welcome-card">
    <span class="gold-line"></span>
    <h2><?= e($greeting) ?>, <?= e($firstName) ?>.</h2>
    <p>Pick a section below to manage — every change here goes live on the public site immediately.</p>
</div>

<div class="admin-dashboard-grid">
    <?php foreach ($tiles as $key => [$href, $label, $sql]): ?>
    <a href="<?= BASE_URL ?>/admin/<?= $href ?>" class="admin-dashboard-tile">
        <div class="count"><?= $counts[$key] ?></div>
        <div class="label"><?= e($label) ?></div>
    </a>
    <?php endforeach; ?>
    <a href="<?= BASE_URL ?>/admin/settings.php" class="admin-dashboard-tile">
        <div class="label">About / Contact / Social</div>
    </a>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
