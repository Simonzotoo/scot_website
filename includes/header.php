<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/nav.php';

/**
 * site_settings is needed by nearly every page (Apply Now URL here in the
 * header alone, plus most pages' own body content), so it's loaded once
 * here rather than duplicated at the top of every page file.
 */
$pdo = db();
$siteSettings = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
$setting = static fn (string $key, string $default = '') => $siteSettings[$key] ?? $default;

$pageTitle = $pageTitle ?? APP_NAME;
$pageDescription = $pageDescription
    ?? 'SCOT: The School of Computing and Technology at Wisconsin International University College (WIUC), Accra — undergraduate, diploma, and postgraduate programmes, faculty, and student life.';
$activePage = $activePage ?? '';
$canonicalUrl = SITE_URL . $_SERVER['REQUEST_URI'];
$ogImageUrl = SITE_URL . '/assets/images/social/og-image.jpg';
$applyNowUrl = $setting('apply_now_url', '#');
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

<!-- ── Parent-university bar — a clear way back out to the main
     WIUC Ghana site, shown above the nav on every page ─────────── -->
<a href="https://wiuc-ghana.edu.gh/" target="_blank" rel="noopener" class="parent-site-bar">
    <span class="parent-site-bar__label">Part of</span>
    <span class="parent-site-bar__name">Wisconsin International University College, Ghana<svg class="parent-site-bar__icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg></span>
</a>

<!-- ── Header — persistent nav across every page ──────────────── -->
<header class="site-header sticky top-0 z-40 border-b border-slate-200/70 dark:border-white/[.08] bg-white/95 dark:bg-[#0b1120]/95 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

        <a href="<?= BASE_URL ?>/index.php"
           class="group flex items-center flex-shrink-0 transition-opacity duration-200 hover:opacity-80
                  rounded-xl focus-visible:outline-none focus-visible:ring-2
                  focus-visible:ring-scotsaGold focus-visible:ring-offset-2"
           aria-label="SCOT Home">
            <img src="<?= IMAGES_URL ?>/logo/scot-logo.png" alt="SCOT — School of Computing and Technology"
                 class="block dark:hidden h-8 sm:h-10 w-auto">
            <img src="<?= IMAGES_URL ?>/logo/scot-logo-dark.png" alt="SCOT — School of Computing and Technology"
                 class="hidden dark:block h-8 sm:h-10 w-auto">
        </a>

        <nav class="hidden xl:flex items-center gap-5" aria-label="Primary">
            <?php foreach (NAV_ITEMS as $key => [$href, $label]): ?>
            <a href="<?= BASE_URL ?>/<?= $href ?>"
               class="nav-link font-heading text-sm px-1 py-1 text-slate-600 <?= $activePage === $key ? 'active' : '' ?>">
                <?= e($label) ?>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <a href="<?= e($applyNowUrl) ?>" class="btn-gold hidden sm:inline-flex" style="padding:.55rem 1.25rem; font-size:.8rem;">Apply Now</a>

            <button class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                <svg data-theme-moon class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                <svg data-theme-sun class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="5"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
                </svg>
            </button>

            <button type="button" id="sidebar-open-btn" class="nav-toggle grid place-items-center xl:hidden" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar">
                <?= icon('bars-3', 'h-5 w-5') ?>
            </button>
        </div>
    </div>
</header>

<!-- Mobile off-canvas nav — the open/close JS already exists in
     assets/js/main.js ("OFF-CANVAS SIDEBAR"), keyed to these exact ids. -->
<div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-black/50 opacity-0 pointer-events-none transition-opacity duration-300 xl:hidden"></div>
<aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[80vw] -translate-x-full transform bg-white dark:bg-[#0b1120] shadow-2xl transition-transform duration-300 xl:hidden" aria-label="Mobile navigation">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-white/10">
        <span class="font-heading font-black text-scotsaBlue dark:text-white">Menu</span>
        <button type="button" id="sidebar-close" aria-label="Close menu" class="p-1.5 text-slate-500 hover:text-scotsaBlue dark:text-slate-400 dark:hover:text-white">
            <?= icon('x-mark', 'h-5 w-5') ?>
        </button>
    </div>
    <nav class="flex flex-col gap-1 p-4" aria-label="Primary mobile">
        <?php foreach (NAV_ITEMS as $key => [$href, $label]): ?>
        <a href="<?= BASE_URL ?>/<?= $href ?>"
           class="rounded-lg px-3 py-2.5 text-sm font-bold <?= $activePage === $key ? 'text-scotsaBlue bg-slate-100 dark:bg-white/10 dark:text-white' : 'text-slate-600 dark:text-slate-300' ?>">
            <?= e($label) ?>
        </a>
        <?php endforeach; ?>
        <a href="<?= e($applyNowUrl) ?>" class="btn-gold justify-center mt-3">Apply Now</a>
    </nav>
</aside>

<main id="main-content">
<?php foreach (consume_flash() as $message): ?>
    <div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6 lg:px-8" data-flash>
        <div class="rounded-xl border px-4 py-3 text-sm <?= $message['type'] === 'success' ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400' : 'border-red-200 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-400' ?>">
            <?= e($message['message']) ?>
        </div>
    </div>
<?php endforeach; ?>
