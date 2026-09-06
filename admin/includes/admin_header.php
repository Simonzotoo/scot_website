<?php
declare(strict_types=1);

/**
 * Shared chrome for every admin screen. Include after require_admin_login()
 * and after any POST handling in the calling file. Expects $pageTitle and
 * $activeNav (one of the NAV_ITEMS keys below) to be set by the caller.
 */

const ADMIN_NAV_ITEMS = [
    'dashboard'  => ['index.php', 'Dashboard'],
    'hero'       => ['hero.php', 'Hero Carousel'],
    'programmes' => ['programmes.php', 'Programmes'],
    'facilities' => ['facilities.php', 'Facilities'],
    'videos'     => ['videos.php', 'Watch Videos'],
    'faculty'    => ['faculty.php', 'Faculty'],
    'projects'   => ['projects.php', 'Student Projects'],
    'blog'       => ['blog.php', 'Blog'],
    'gallery'    => ['gallery.php', 'Gallery'],
    'settings'   => ['settings.php', 'Site Settings'],
];

$admin = require_admin_login();
$activeNav = $activeNav ?? '';
$pageTitle = $pageTitle ?? 'Admin';

/** First letter of up to the first two words of a name, e.g. "SCOT Admin" -> "SA". */
function admin_initials(string $name): string
{
    $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY);
    $initials = array_map(static fn (string $w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice($words, 0, 2));
    return implode('', $initials) ?: '?';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> — SCOT Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@500;700;800;900&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar">
        <a href="<?= BASE_URL ?>/admin/index.php" class="admin-brand">
            <img src="<?= IMAGES_URL ?>/logo/scot-logo-dark.png" alt="SCOT — School of Computing and Technology">
            <span class="gold-line"></span>
        </a>
        <nav class="admin-nav">
            <?php foreach (ADMIN_NAV_ITEMS as $key => [$href, $label]): ?>
            <a href="<?= BASE_URL ?>/admin/<?= $href ?>" class="admin-nav-link <?= $activeNav === $key ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-footer">
            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="admin-nav-link">View Site &#8599;</a>
            <a href="<?= BASE_URL ?>/admin/logout.php" class="admin-nav-link">Log Out</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1><?= e($pageTitle) ?></h1>
            <div class="admin-topbar-right">
                <span class="admin-topbar-user"><?= e($admin['name']) ?></span>
                <div class="admin-avatar"><?= e(admin_initials($admin['name'])) ?></div>
            </div>
        </header>

        <main class="admin-content">
            <?php foreach (consume_flash() as $message): ?>
            <div class="admin-flash admin-flash-<?= e($message['type']) ?>"><?= e($message['message']) ?></div>
            <?php endforeach; ?>
