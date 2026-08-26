<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/student_auth.php';
$pageTitle   = $pageTitle ?? APP_NAME;
$currentPage = basename($_SERVER['PHP_SELF']);
$navStudent  = current_student();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="SCOTSA: The official digital platform of the School of Computing and Technology Students Association.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Apply saved theme before any paint: prevents flash of wrong theme -->
    <script>
        (function() {
            var t = localStorage.getItem('scotsa-theme');
            if (!t) t = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', t);
            if (t === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body class="bg-white dark:bg-[#0b1120] text-ink dark:text-slate-100 antialiased transition-colors duration-200">

<!-- ── Navbar ──────────────────────────────────────────────── -->
<header class="sticky top-0 z-50 border-b border-slate-200/70 dark:border-white/[.08] bg-white dark:bg-[#0b1120] transition-all duration-200">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">

        <!-- ── Brand ─────────────────────────────────────────────────────── -->
        <!--
            Logo files: assets/images/logo/logo-dark.png  (mark for light bg)
                        assets/images/logo/logo-light.png (mark for dark bg)
            Drop PNG/SVG files in that folder; the fallback SVG badge auto-shows
            if the files don't exist yet.
        -->
        <a href="<?= BASE_URL ?>/index.php"
           class="group flex items-center gap-3 sm:gap-3.5 flex-shrink-0
                  rounded-xl focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-scotsaGold focus-visible:ring-offset-2"
           aria-label="SCOTSA Home">

            <!-- Logo mark -->
            <?php
            // Use logo-light.png if it exists, otherwise fall back to logo-dark.png (no 404 flash)
            $logoLight = is_file(IMAGES_ROOT . '/logo/logo-light.png')
                ? IMAGES_URL . '/logo/logo-light.png'
                : IMAGES_URL . '/logo/logo-dark.png';
            ?>
            <div class="relative flex-shrink-0">
                <!-- Light-bg mark (shown in light mode) -->
                <img src="<?= IMAGES_URL ?>/logo/logo-dark.png"
                     alt="" aria-hidden="true"
                     class="block dark:hidden h-11 w-11 sm:h-12 sm:w-12 object-contain
                            transition-transform duration-300 ease-out will-change-transform
                            group-hover:scale-[1.08]"
                     style="filter:drop-shadow(0 1px 4px rgba(10,31,68,.14));"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/logo-mark.svg'; this.onerror=null;"
                     loading="eager">
                <!-- Dark-bg mark (shown in dark mode), resolved server-side, no 404 -->
                <img src="<?= $logoLight ?>"
                     alt="" aria-hidden="true"
                     class="hidden dark:block h-11 w-11 sm:h-12 sm:w-12 object-contain
                            transition-transform duration-300 ease-out will-change-transform
                            group-hover:scale-[1.08]"
                     style="filter:drop-shadow(0 1px 6px rgba(212,175,55,.18));"
                     onerror="this.src='<?= IMAGES_URL ?>/placeholders/logo-mark-light.svg'; this.onerror=null;"
                     loading="eager">
            </div>

            <!-- Vertical separator, desktop only -->
            <div class="hidden sm:block w-px h-8 flex-shrink-0 rounded-full
                        bg-slate-200 dark:bg-white/10" aria-hidden="true"></div>

            <!-- Text identity -->
            <div class="leading-none min-w-0">
                <span class="block font-heading font-black text-scotsaBlue dark:text-white
                             tracking-[-0.015em] transition-colors duration-200
                             group-hover:text-scotsaLight dark:group-hover:text-scotsaGold"
                      style="font-size:1.18rem; line-height:1;">SCOTSA</span>
                <span class="hidden sm:block font-semibold uppercase text-slate-500 dark:text-slate-400
                             transition-colors duration-200 mt-[5px] whitespace-nowrap"
                      style="font-size:.63rem; letter-spacing:.065em; line-height:1.2;">
                    School of Computing &amp; Technology Students Association
                </span>
            </div>
        </a>

        <!-- Mobile controls -->
        <div class="flex items-center gap-2 md:hidden">
            <!-- Dark mode toggle (mobile) -->
            <button class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                <svg data-theme-moon class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg data-theme-sun class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>

            <!-- Hamburger -->
            <button class="rounded-lg border border-slate-200 dark:border-white/10 p-2 transition hover:bg-slate-50 dark:hover:bg-white/5 active:scale-95"
                    data-menu-button aria-label="Toggle menu" aria-expanded="false">
                <svg class="h-5 w-5 text-slate-700 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <!-- Desktop nav -->
        <?php
        $navLinks = [
            ['Home',          BASE_URL . '/index.php',         'index.php'],
            ['About',         BASE_URL . '/about.php',         'about.php'],
            ['Resources',     BASE_URL . '/resources.php',     'resources.php'],
            ['Gallery',       BASE_URL . '/gallery.php',       'gallery.php'],
            ['Announcements', BASE_URL . '/announcements.php', 'announcements.php'],
            ['Executives',    BASE_URL . '/executives.php',    'executives.php'],
            ['Contact',       BASE_URL . '/contact.php',       'contact.php'],
        ];
        ?>
        <div class="hidden items-center gap-0.5 md:flex">
            <?php foreach ($navLinks as [$label, $href, $page]): ?>
                <a class="nav-link px-3 py-2 text-[0.82rem] font-semibold text-slate-600 dark:text-slate-300 transition
                   <?= $currentPage === $page
                       ? 'active !text-scotsaBlue dark:!text-scotsaGold bg-blue-50/70 dark:bg-scotsaGold/10'
                       : 'hover:bg-slate-50 dark:hover:bg-white/5' ?>"
                   href="<?= $href ?>"><?= $label ?></a>
            <?php endforeach; ?>

            <!-- Dark mode toggle (desktop) -->
            <button class="theme-toggle ml-2" data-theme-toggle aria-label="Toggle dark mode">
                <svg data-theme-moon class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg data-theme-sun class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>

            <?php if ($navStudent): ?>
            <a class="ml-2 btn-primary" href="<?= BASE_URL ?>/student/dashboard.php">Dashboard</a>
            <?php else: ?>
            <a class="ml-2 btn-primary" href="<?= BASE_URL ?>/student/login.php">Student Login</a>
            <?php endif; ?>
            <a class="ml-1 px-2 text-xs font-semibold text-slate-400 dark:text-slate-500 hover:text-scotsaBlue dark:hover:text-scotsaGold transition" href="<?= BASE_URL ?>/admin/login.php">Admin</a>
        </div>
    </nav>

    <!-- Mobile drawer -->
    <div class="border-t border-slate-100 dark:border-white/[.06] bg-white dark:bg-[#111827] px-4 pb-4 pt-3 md:hidden" data-mobile-menu>
        <div class="grid gap-0.5">
            <?php foreach ($navLinks as [$label, $href, $page]): ?>
                <a class="rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300 transition
                   hover:bg-slate-50 dark:hover:bg-white/5
                   <?= $currentPage === $page ? '!bg-blue-50 dark:!bg-scotsaGold/10 !text-scotsaBlue dark:!text-scotsaGold' : '' ?>"
                   href="<?= $href ?>"><?= $label ?></a>
            <?php endforeach; ?>
            <?php if ($navStudent): ?>
            <a class="mt-2 btn-primary text-center" href="<?= BASE_URL ?>/student/dashboard.php">Dashboard</a>
            <?php else: ?>
            <a class="mt-2 btn-primary text-center" href="<?= BASE_URL ?>/student/login.php">Student Login</a>
            <?php endif; ?>
            <a class="mt-1 text-center text-xs font-semibold text-slate-400 dark:text-slate-500 py-2" href="<?= BASE_URL ?>/admin/login.php">Admin Login</a>
        </div>
    </div>
</header>

<main>
<?php foreach (consume_flash() as $message): ?>
    <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8" data-flash>
        <div class="rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
            <?= e($message['message']) ?>
        </div>
    </div>
<?php endforeach; ?>
