<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription
    ?? 'SCOT: The School of Computing and Technology at Wisconsin International University College (WIUC), Accra — undergraduate, diploma, and postgraduate programmes, faculty, and student life.';
$canonicalUrl = SITE_URL . $_SERVER['REQUEST_URI'];
$ogImageUrl = SITE_URL . '/assets/images/social/og-image.jpg';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/assets/images/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/images/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/images/favicon/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/images/favicon/apple-touch-icon.png">
    <link rel="manifest" href="<?= BASE_URL ?>/assets/images/favicon/site.webmanifest">
    <meta name="theme-color" content="#0A1F44">

    <!-- Open Graph / social share preview -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="SCOT — WIUC Ghana">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e($ogImageUrl) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:locale" content="en_GB">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e($ogImageUrl) ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800;900&family=Lato:wght@400;700;900&display=swap" rel="stylesheet">

    <!-- Applies the saved theme before first paint (prevents a flash of
         the wrong theme) — external file so it runs under this site's CSP. -->
    <script src="<?= BASE_URL ?>/assets/js/theme-init.js"></script>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/tailwind.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/styles.css">
</head>
<body class="bg-white dark:bg-[#0b1120] text-ink dark:text-slate-100 antialiased transition-colors duration-200"
      data-ga-id="<?= e(GA_MEASUREMENT_ID) ?>">

<div id="loading-bar" aria-hidden="true"></div>

<a href="#main-content" class="skip-link">Skip to main content</a>

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
           class="group flex items-center flex-shrink-0 transition-opacity duration-200 hover:opacity-80
                  rounded-xl focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-scotsaGold focus-visible:ring-offset-2"
           aria-label="SCOT Home">
            <img src="<?= IMAGES_URL ?>/logo/scot-logo.png" alt="SCOT — School of Computing and Technology"
                 class="block dark:hidden h-10 sm:h-14 w-auto">
            <img src="<?= IMAGES_URL ?>/logo/scot-logo-dark.png" alt="SCOT — School of Computing and Technology"
                 class="hidden dark:block h-10 sm:h-14 w-auto">
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

<main id="main-content">
<?php foreach (consume_flash() as $message): ?>
    <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8" data-flash>
        <div class="rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
            <?= e($message['message']) ?>
        </div>
    </div>
<?php endforeach; ?>
