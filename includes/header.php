<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
$pageTitle = $pageTitle ?? APP_NAME;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="SCOT: The School of Computing and Technology at Wisconsin International University College (WIUC), Accra — home of SCOTSA, the department's student association.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&family=Lato:wght@400;700;900&display=swap" rel="stylesheet">

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

<!-- ── Header ──────────────────────────────────────────────── -->
<!--
    Not sticky, no nav links: this page is reached via a link from the main
    WIUC website (the "mother" site owns global navigation), so it shouldn't
    carry its own persistent app-style nav bar. Section jump links live in a
    one-time pill row right after the hero instead (see index.php).
-->
<header class="border-b border-slate-200/70 dark:border-white/[.08] bg-white dark:bg-[#0b1120]">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3.5 sm:px-6 lg:px-8">

        <a href="<?= BASE_URL ?>/index.php"
           class="group flex items-center flex-shrink-0
                  rounded-xl focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-scotsaGold focus-visible:ring-offset-2"
           aria-label="SCOT Home">
            <div class="leading-none min-w-0">
                <span class="block font-heading font-black text-scotsaBlue dark:text-white
                             tracking-[-0.015em] transition-colors duration-200
                             group-hover:text-scotsaLight dark:group-hover:text-scotsaGold"
                      style="font-size:1.85rem; line-height:1;">SCOT</span>
                <span class="hidden sm:block font-heading font-black uppercase text-scotsaBlue dark:text-slate-200
                             transition-colors duration-200 mt-[6px] whitespace-nowrap"
                      style="font-size:.78rem; letter-spacing:.05em; line-height:1.2;">
                    School of Computing &amp; Technology
                </span>
            </div>
        </a>

        <button class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
            <svg data-theme-moon class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
            <svg data-theme-sun class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="5"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>
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
