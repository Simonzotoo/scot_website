<?php
require_once __DIR__ . '/config.php';

$siteSettings = [];
try {
    $siteSettings = db()->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (Throwable $e) {
    $siteSettings = [];
}
$contactEmail = $siteSettings['contact_email'] ?? 'scotsawiuc@gmail.com';
$whatsappUrl  = $siteSettings['whatsapp_url']  ?? 'https://chat.whatsapp.com/Cn2b43LoXOH1WGCg0uBaNR?s=cl&p=i&mlu=4';
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
                <!-- Footer brand: logo mark + SCOTSA text -->
                <a href="<?= BASE_URL ?>/index.php"
                   class="group inline-flex items-center gap-3.5 focus-visible:outline-none focus-visible:ring-2
                          focus-visible:ring-scotsaGold focus-visible:ring-offset-2
                          focus-visible:ring-offset-scotsaBlue rounded-xl"
                   aria-label="SCOTSA Home">
                    <?php
                    $footerLogoSrc = is_file(IMAGES_ROOT . '/logo/logo-light.png')
                        ? IMAGES_URL . '/logo/logo-light.png'
                        : IMAGES_URL . '/logo/logo-dark.png';
                    ?>
                    <!-- White backdrop chip so the mark's navy ring doesn't
                         blend into the navy footer background. -->
                    <div class="grid h-12 w-12 flex-shrink-0 place-items-center rounded-full bg-white p-1 shadow-sm
                                transition-transform duration-300 ease-out will-change-transform
                                group-hover:scale-[1.07]">
                        <img src="<?= $footerLogoSrc ?>"
                             alt="" aria-hidden="true"
                             class="h-full w-full object-contain"
                             onerror="this.src='<?= IMAGES_URL ?>/placeholders/logo-mark-light.svg'; this.onerror=null;"
                             loading="lazy">
                    </div>
                    <!-- Vertical separator -->
                    <div class="w-px h-9 flex-shrink-0 rounded-full" style="background:rgba(255,255,255,0.15);" aria-hidden="true"></div>
                    <div class="leading-none">
                        <span class="block font-heading font-black text-white tracking-[-0.01em]
                                     transition-colors duration-200 group-hover:text-scotsaGold"
                              style="font-size:1.22rem; line-height:1;">SCOTSA</span>
                        <span class="block font-semibold uppercase transition-colors duration-200 mt-[5px]"
                              style="font-size:.6rem; letter-spacing:.075em; color:rgba(147,197,253,.70);">
                            School of Computing &amp; Technology
                        </span>
                    </div>
                </a>

                <p class="mt-5 max-w-sm text-sm leading-7" style="color:rgba(191,219,254,.70);">
                    The official digital platform of SCOTSA, the recognised student association of Wisconsin International University College (WIUC), Accra. Built for academic access, community updates, and departmental excellence.
                </p>

                <!-- Social icons -->
                <div class="mt-6 flex gap-2.5">
                    <?php
                    $socials = [
                        ['Facebook',  $siteSettings['social_facebook']  ?? '#', 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],
                        ['Instagram', $siteSettings['social_instagram'] ?? '#', 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zM17.5 6.5h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['X',         $siteSettings['social_twitter']   ?? '#', 'M4 4l16 16M20 4L4 20'],
                        ['TikTok',    $siteSettings['social_tiktok']    ?? '#', 'M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z'],
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
                <h3 class="font-heading font-bold text-xs tracking-widest uppercase mb-5" style="color:#D4AF37;">Platform</h3>
                <div class="grid gap-2.5 text-sm" style="color:rgba(191,219,254,.65);">
                    <?php
                    $links = [
                        ['Home',          BASE_URL . '/index.php'],
                        ['About SCOTSA',  BASE_URL . '/about.php'],
                        ['Resources',     BASE_URL . '/resources.php'],
                        ['Gallery',       BASE_URL . '/gallery.php'],
                        ['Announcements', BASE_URL . '/announcements.php'],
                        ['Executives',    BASE_URL . '/executives.php'],
                        ['Contact',       BASE_URL . '/contact.php'],
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
                    <p class="leading-6">School of Computing<br>and Technology Students Assoc.</p>
                    <p>
                        <a href="mailto:<?= e($contactEmail) ?>" class="hover:text-white transition-colors duration-150 underline underline-offset-2 decoration-transparent hover:decoration-current">
                            <?= e($contactEmail) ?>
                        </a>
                    </p>
                    <p class="flex items-start gap-1.5">
                        <svg class="h-3.5 w-3.5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="leading-6">Wisconsin International University College, Ghana<br>
                        <span style="color:rgba(191,219,254,.45);">School of Computing and Technology<br>Departmental Student Office</span></span>
                    </p>
                    <a href="<?= BASE_URL ?>/contact.php"
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
        &copy; <?= date('Y') ?> SCOTSA. All rights reserved.
        <span class="mx-2 opacity-40">&middot;</span>
        Built for students, by students.
        <span class="mx-2 opacity-40">&middot;</span>
        <a href="<?= BASE_URL ?>/admin/login.php" class="hover:text-white transition-colors duration-150">Admin</a>
    </div>
</footer>

<!-- ── Floating WhatsApp button ─────────────────────────────── -->
<a href="<?= e($whatsappUrl) ?>" target="_blank" rel="noopener"
   class="fixed bottom-5 right-5 sm:bottom-6 sm:right-6 z-40 grid h-14 w-14 place-items-center rounded-full shadow-lg transition-transform duration-200 hover:scale-110 active:scale-95"
   style="background:#25D366; box-shadow:0 8px 24px rgba(0,0,0,.25);"
   aria-label="Join our WhatsApp community" title="Join our WhatsApp community">
    <span class="absolute inset-0 rounded-full animate-ping" style="background:#25D366; opacity:.35;" aria-hidden="true"></span>
    <svg class="relative h-7 w-7 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
</a>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
