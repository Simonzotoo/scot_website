<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security.php';
$pageTitle = 'Contact SCOTSA: Get In Touch';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Hero ──────────────────────────────────────── -->
<section class="page-hero text-white">
    <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 relative z-10">
        <nav class="mb-6 flex items-center gap-2 text-xs text-blue-200/60">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-white transition">Home</a>
            <span>/</span>
            <span class="text-white">Contact</span>
        </nav>
        <span class="eyebrow">Reach Us</span>
        <h1 class="font-heading font-black text-4xl sm:text-5xl mt-4 mb-5 text-white max-w-2xl leading-tight">
            Connect with SCOTSA directly.
        </h1>
        <p class="text-blue-100/75 max-w-xl text-sm leading-7">
            Reach out through your preferred channel. We're active across all major platforms.
            No forms, no waiting. Just click and connect.
        </p>
    </div>
</section>

<!-- ── Social Contact Hub ─────────────────────────────── -->
<section class="mx-auto max-w-5xl px-4 py-20 sm:px-6 lg:px-8">

    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Direct Channels</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink">How to reach us</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Click any card below to connect with SCOTSA instantly on your preferred platform.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">

        <!-- WhatsApp -->
        <a href="https://chat.whatsapp.com/Cn2b43LoXOH1WGCg0uBaNR?s=cl&p=i&mlu=4" target="_blank" rel="noopener"
           class="contact-card group fade-in">
            <div class="social-icon flex-shrink-0" style="background:rgba(37,211,102,.12); color:#1DA851;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">WhatsApp</p>
                <p class="text-slate-500 text-xs mt-0.5">Join our official community</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">Open WhatsApp</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <!-- Email -->
        <a href="mailto:scotsawiuc@gmail.com"
           class="contact-card group fade-in fade-in-delay-1">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Email</p>
                <p class="text-slate-500 text-xs mt-0.5">Send us a message anytime</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">scotsawiuc@gmail.com</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <!-- Facebook -->
        <a href="https://www.facebook.com/share/1Hrd1Z7gii/?mibextid=wwXIfr" target="_blank" rel="noopener"
           class="contact-card group fade-in fade-in-delay-1">
            <div class="social-icon flex-shrink-0" style="background:rgba(24,119,242,.10); color:#1877F2;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Facebook</p>
                <p class="text-slate-500 text-xs mt-0.5">Follow our official page</p>
                <p class="text-xs font-semibold mt-1" style="color:#1877F2;">@scotsa_wiuc</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <!-- Instagram -->
        <a href="https://www.instagram.com/scotsa_wiuc?igsi=MWxqZ2RtdXQwaGt1Yw==" target="_blank" rel="noopener"
           class="contact-card group fade-in fade-in-delay-2">
            <div class="social-icon flex-shrink-0" style="background:linear-gradient(135deg, rgba(240,148,51,.14) 0%, rgba(230,104,60,.14) 25%, rgba(220,39,67,.14) 50%, rgba(204,35,102,.14) 75%, rgba(188,24,136,.14) 100%); color:#dc2743;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Instagram</p>
                <p class="text-slate-500 text-xs mt-0.5">Photos, stories &amp; updates</p>
                <p class="text-xs font-semibold mt-1" style="color:#dc2743;">@scotsa_wiuc</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <!-- TikTok -->
        <a href="https://www.tiktok.com/@scotsa_wiuc?_r=1&_t=ZS-99Dd4S31JyO" target="_blank" rel="noopener"
           class="contact-card group fade-in fade-in-delay-2">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.07); color:#0A1F44;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1V9.01a6.33 6.33 0 00-.79-.05 6.34 6.34 0 00-6.34 6.34 6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.33-6.34V8.69a8.18 8.18 0 004.78 1.52V6.75a4.85 4.85 0 01-1.01-.06z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">TikTok</p>
                <p class="text-slate-500 text-xs mt-0.5">Short videos &amp; highlights</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">@scotsa_wiuc</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

        <!-- X (Twitter) -->
        <a href="https://x.com/scotsawiuc?s=21" target="_blank" rel="noopener"
           class="contact-card group fade-in fade-in-delay-3">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.07); color:#0A1F44;">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.713 6.231 5.45-6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">X (Twitter)</p>
                <p class="text-slate-500 text-xs mt-0.5">Updates &amp; announcements</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue">@scotsa_wiuc</p>
            </div>
            <svg class="h-4 w-4 text-slate-300 flex-shrink-0 ml-auto transition group-hover:text-slate-500 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>

    </div>
</section>

<div class="section-divider mx-auto max-w-5xl px-4 sm:px-6 lg:px-8"></div>

<!-- ── Office & Location ──────────────────────────────── -->
<section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid gap-6 md:grid-cols-2">

        <!-- Office info card -->
        <div class="rounded-2xl p-8 text-white fade-in" style="background:linear-gradient(135deg, #0A1F44 0%, #0d2a5c 100%);">
            <div class="flex items-center gap-3 mb-6">
                <div class="grid h-11 w-11 place-items-center rounded-xl" style="background:rgba(212,175,55,.15); border:1px solid rgba(212,175,55,.3);">
                    <svg class="h-5 w-5" style="color:#D4AF37;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h3 class="font-heading font-black text-lg text-white">Office Location</h3>
            </div>
            <p class="font-heading font-bold text-lg text-white leading-snug">
                Wisconsin International University College
            </p>
            <p class="mt-2 text-sm leading-7" style="color:rgba(191,219,254,.75);">
                Find the SCOTSA office within the School of Computing and Technology's departmental block, on campus in Accra, Ghana.
            </p>

            <div class="mt-6 flex items-center gap-2.5 pt-6 text-sm" style="border-top:1px solid rgba(255,255,255,.10); color:rgba(191,219,254,.85);">
                <svg class="h-4 w-4 flex-shrink-0" style="color:#D4AF37;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Open Monday to Friday, 8:00 AM to 5:00 PM
            </div>
        </div>

        <!-- Map card -->
        <div class="group rounded-2xl border border-slate-200 overflow-hidden flex flex-col fade-in fade-in-delay-1 hover:border-scotsaGold transition-colors duration-200"
             style="min-height:280px;">
            <!-- Live embedded map -->
            <iframe
                src="https://www.google.com/maps?q=5.670433,-0.1893348&z=16&output=embed"
                class="w-full flex-1 border-0"
                style="min-height:220px;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Map showing Wisconsin International University College, Ghana">
            </iframe>
            <!-- CTA bar -->
            <a href="https://maps.app.goo.gl/xYnrCU3t9MBHGarq9" target="_blank" rel="noopener"
               class="group flex items-center justify-between px-5 py-3.5 border-t border-slate-200 bg-white hover:bg-scotsaBlue transition-colors duration-200">
                <span class="text-xs font-bold text-scotsaBlue group-hover:text-white transition-colors duration-200">Open in Google Maps</span>
                <svg class="h-4 w-4 text-slate-400 group-hover:text-scotsaGold transition-all duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
