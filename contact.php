<?php
$activePage = 'contact';
$pageTitle = 'Contact — School of Computing and Technology, WIUC Ghana';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════
     CONTACT
════════════════════════════════════════════════════════════ -->
<section id="contact" class="section-tint py-20 scroll-mt-20">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
    <div class="text-center mb-12 fade-in">
        <span class="eyebrow">Get in Touch</span>
        <div class="gold-line mt-3 mx-auto mb-4"></div>
        <h2 class="font-heading font-black text-3xl text-ink sm:text-4xl">Contact the department</h2>
        <p class="text-slate-500 text-sm max-w-md mx-auto mt-3 leading-7">
            Reach out through your preferred channel, or visit the department office on campus.
        </p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <a href="mailto:<?= e($setting('contact_email')) ?>" class="contact-card group fade-in">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('mail', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Email</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_email')) ?></p>
            </div>
        </a>

        <a href="tel:<?= e(preg_replace('/\s+/', '', $setting('contact_phone'))) ?>" class="contact-card group fade-in fade-in-delay-1">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('phone', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Phone</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_phone')) ?></p>
            </div>
        </a>

        <a href="https://www.google.com/maps?q=5.670433,-0.1893348" target="_blank" rel="noopener" class="contact-card group fade-in fade-in-delay-2">
            <div class="social-icon flex-shrink-0" style="background:rgba(10,31,68,.08); color:#0A1F44;">
                <?= icon('map-pin', 'h-6 w-6') ?>
            </div>
            <div class="min-w-0">
                <p class="font-heading font-bold text-ink text-sm">Campus</p>
                <p class="text-xs font-semibold mt-1 text-scotsaBlue"><?= e($setting('contact_address')) ?></p>
            </div>
        </a>
    </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
