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
                        ['Facebook',  '#', 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
                        ['Instagram', '#', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['X',         '#', 'M4 4l16 16M20 4L4 20'],
                        ['TikTok',    '#', 'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z'],
                    ];
                    foreach ($socials as [$name, $url, $path]):
                    ?>
                    <a href="<?= $url ?>" target="_blank" rel="noopener"
                       class="grid h-9 w-9 place-items-center rounded-lg border transition-all duration-200 hover:scale-110"
                       style="background:rgba(255,255,255,.06); border-color:rgba(255,255,255,.15); color:rgba(191,219,254,.65);"
                       onmouseover="this.style.borderColor='rgba(212,175,55,.5)'; this.style.color='#D4AF37'; this.style.background='rgba(212,175,55,.08)';"
                       onmouseout="this.style.borderColor='rgba(255,255,255,.15)'; this.style.color='rgba(191,219,254,.65)'; this.style.background='rgba(255,255,255,.06)';"
                       aria-label="<?= $name ?>">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>"/>
                        </svg>
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
                        ['Watch',      BASE_URL . '/index.php#watch'],
                        ['Faculty',    BASE_URL . '/index.php#faculty'],
                        ['Projects',   BASE_URL . '/index.php#projects'],
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
                        <a href="mailto:scotsawiuc@gmail.com" class="hover:text-white transition-colors duration-150 underline underline-offset-2 decoration-transparent hover:decoration-current">
                            scotsawiuc@gmail.com
                        </a>
                    </p>
                    <p class="flex items-start gap-1.5">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="leading-6">Wisconsin International University College, Ghana<br>
                        <span style="color:rgba(191,219,254,.45);">School of Computing and Technology</span></span>
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
        <span class="mx-2 opacity-40">&middot;</span>
        Built for students, by the department.
    </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
