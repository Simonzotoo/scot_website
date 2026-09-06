<?php
require_once __DIR__ . '/config.php';
?>
</main>

<!-- ── Footer ──────────────────────────────────────────────── -->
<footer class="mt-20" style="background:#0A1F44;">
    <!-- Gold gradient divider -->
    <div class="h-px w-full" style="background:linear-gradient(to right, transparent 0%, #D4AF37 30%, #D4AF37 70%, transparent 100%); opacity:.85;"></div>

    <div class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">

            <!-- Brand -->
            <div class="lg:col-span-2">
                <!-- Footer brand: text-only wordmark (no logo — see header.php) -->
                <a href="<?= BASE_URL ?>/index.php"
                   class="group inline-flex items-center focus-visible:outline-none focus-visible:ring-2
                          focus-visible:ring-scotsaGold focus-visible:ring-offset-2
                          focus-visible:ring-offset-scotsaBlue rounded-xl"
                   aria-label="SCOT Home">
                    <div class="leading-none">
                        <span class="block font-heading font-black text-white tracking-[-0.01em]
                                     transition-colors duration-200 group-hover:text-scotsaGold"
                              style="font-size:1.4rem; line-height:1;">SCOT</span>
                        <span class="block font-semibold uppercase transition-colors duration-200 mt-[5px]"
                              style="font-size:.6rem; letter-spacing:.075em; color:rgba(147,197,253,.70);">
                            School of Computing &amp; Technology
                        </span>
                    </div>
                </a>

                <p class="mt-5 max-w-sm text-sm leading-7" style="color:rgba(191,219,254,.70);">
                    The School of Computing and Technology at Wisconsin International University College (WIUC), Accra &mdash; home to our undergraduate, diploma, and postgraduate programmes, faculty, and student community.
                </p>

                <!-- Social icons -->
                <div class="mt-6 flex gap-2.5">
                    <?php
                    $socials = [
                        ['Facebook',  'https://web.facebook.com/wiucghana',  'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z', false],
                        ['Instagram', 'https://www.instagram.com/wiuc_ghana/', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', false],
                        ['X',         'https://twitter.com/WIUCGHANA', 'M4 4l16 16M20 4L4 20', false],
                        ['LinkedIn',  'https://www.linkedin.com/school/wiucghana/', 'M19 3a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h14zM8.34 18.34V10.13H5.67v8.21h2.67zM7.01 9.01a1.54 1.54 0 100-3.08 1.54 1.54 0 000 3.08zM18.34 18.34v-4.45c0-2.38-1.27-3.49-2.96-3.49-1.36 0-1.97.75-2.31 1.27v-1.09h-2.67c.04.75 0 8.21 0 8.21h2.67v-4.6c0-.25.02-.49.09-.67.2-.49.65-1 1.4-1 .99 0 1.39.75 1.39 1.85v4.42h2.67z', true],
                    ];
                    foreach ($socials as [$name, $url, $path, $solid]):
                    ?>
                    <a href="<?= $url ?>" target="_blank" rel="noopener"
                       class="social-icon-link grid h-9 w-9 place-items-center rounded-lg border transition-all duration-200 hover:scale-110"
                       aria-label="<?= $name ?>">
                        <?php if ($solid): ?>
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="<?= $path ?>"/>
                        </svg>
                        <?php else: ?>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/>
                        </svg>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick links -->
            <div>
                <h3 class="font-heading font-bold text-xs tracking-widest uppercase mb-5" style="color:#D4AF37;">Explore</h3>
                <div class="grid gap-2.5 text-sm" style="color:rgba(191,219,254,.65);">
                    <?php
                    $links = [
                        ['Home',       BASE_URL . '/index.php'],
                        ['About',      BASE_URL . '/index.php#about'],
                        ['Programmes', BASE_URL . '/index.php#programmes'],
                        ['Facilities', BASE_URL . '/index.php#facilities'],
                        ['Watch',      BASE_URL . '/index.php#watch'],
                        ['Faculty',    BASE_URL . '/index.php#faculty'],
                        ['Projects',   BASE_URL . '/index.php#projects'],
                        ['Blog',       BASE_URL . '/index.php#blog'],
                        ['Gallery',    BASE_URL . '/index.php#gallery'],
                        ['Contact',    BASE_URL . '/index.php#contact'],
                    ];
                    foreach ($links as [$label, $href]):
                    ?>
                    <a href="<?= $href ?>" class="footer-link hover:text-white transition-colors duration-150"><?= $label ?></a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contact info -->
            <div>
                <h3 class="font-heading font-bold text-xs tracking-widest uppercase mb-5" style="color:#D4AF37;">Contact</h3>
                <div class="grid gap-3 text-sm" style="color:rgba(191,219,254,.65);">
                    <p class="leading-6">School of Computing<br>and Technology</p>
                    <p>
                        <a href="mailto:info@wiuc-ghana.edu.gh" class="hover:text-white transition-colors duration-150 underline underline-offset-2 decoration-transparent hover:decoration-current">
                            info@wiuc-ghana.edu.gh
                        </a>
                    </p>
                    <p>
                        <a href="tel:+233544853383" class="hover:text-white transition-colors duration-150 underline underline-offset-2 decoration-transparent hover:decoration-current">
                            +233 54 485 3383
                        </a>
                    </p>
                    <p class="flex items-start gap-1.5">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="leading-6">No. 23 Akoto Bamfo Street, North Legon, Accra<br>
                        <span style="color:rgba(191,219,254,.45);">Wisconsin International University College, Ghana</span></span>
                    </p>
                    <a href="#contact"
                       class="mt-1 inline-flex items-center gap-1.5 rounded-lg px-3.5 py-2 text-xs font-semibold text-white transition-all duration-200 hover:bg-white/10 w-fit"
                       style="border:1px solid rgba(255,255,255,.22);">
                        Get in touch
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t py-5 text-center text-xs" style="border-color:rgba(255,255,255,.08); color:rgba(191,219,254,.40);">
        &copy; <?= date('Y') ?> School of Computing and Technology (SCOT), WIUC. All rights reserved.
    </div>
</footer>

<div id="cookie-consent" role="dialog" aria-live="polite" aria-label="Cookie notice">
    <p>We use cookies to run this site and, only with your consent, to understand how it's used.</p>
    <div class="cookie-actions">
        <button type="button" class="cookie-decline">Decline</button>
        <button type="button" class="cookie-accept">Accept</button>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/loading-bar.js"></script>
<script src="<?= BASE_URL ?>/assets/js/consent.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
