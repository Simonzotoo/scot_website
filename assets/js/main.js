/* ── SCOTSA Platform — Premium JS ─────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {

    /* ════════════════════════════════════════════════════════
       DARK MODE
    ════════════════════════════════════════════════════════ */
    const html         = document.documentElement;
    const themeToggles = document.querySelectorAll('[data-theme-toggle]');
    const stored       = localStorage.getItem('scotsa-theme');
    const preferred    = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    const initial      = stored || preferred;

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        if (theme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }

    applyTheme(initial);

    themeToggles.forEach(btn => {
        btn.addEventListener('click', () => {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
            localStorage.setItem('scotsa-theme', next);
        });
    });

    /* ════════════════════════════════════════════════════════
       MOBILE NAV — smooth slide
    ════════════════════════════════════════════════════════ */
    const menuBtn    = document.querySelector('[data-menu-button]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');

    if (menuBtn && mobileMenu) {
        let isOpen = false;

        const openNav = () => {
            isOpen = true;
            mobileMenu.style.maxHeight = mobileMenu.scrollHeight + 'px';
            mobileMenu.style.opacity   = '1';
            menuBtn.setAttribute('aria-expanded', 'true');
            menuBtn.classList.add('bg-slate-100');
        };

        const closeNav = () => {
            isOpen = false;
            mobileMenu.style.maxHeight = '0';
            mobileMenu.style.opacity   = '0';
            menuBtn.setAttribute('aria-expanded', 'false');
            menuBtn.classList.remove('bg-slate-100');
        };

        menuBtn.addEventListener('click', () => isOpen ? closeNav() : openNav());

        document.addEventListener('click', e => {
            if (isOpen && !menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                closeNav();
            }
        });

        /* Close on nav link click (after route change) */
        mobileMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeNav));
    }

    /* ════════════════════════════════════════════════════════
       PASSWORD VISIBILITY TOGGLE
    ════════════════════════════════════════════════════════ */
    document.querySelectorAll('[data-password-toggle]').forEach(btn => {
        const input = document.getElementById(btn.dataset.passwordToggle);
        if (!input) return;
        const eyeOpen   = btn.querySelector('[data-eye-open]');
        const eyeClosed = btn.querySelector('[data-eye-closed]');
        btn.addEventListener('click', () => {
            const revealing = input.type === 'password';
            input.type = revealing ? 'text' : 'password';
            eyeOpen?.classList.toggle('hidden', revealing);
            eyeClosed?.classList.toggle('hidden', !revealing);
            btn.setAttribute('aria-label', revealing ? 'Hide password' : 'Show password');
        });
    });

    /* ════════════════════════════════════════════════════════
       OFF-CANVAS SIDEBAR (admin panel + student portal)
    ════════════════════════════════════════════════════════ */
    const sidebar        = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarOpenBtn  = document.getElementById('sidebar-open-btn');
    const sidebarCloseBtn = document.getElementById('sidebar-close');

    if (sidebar && sidebarBackdrop) {
        const openSidebar = () => {
            sidebar.classList.remove('-translate-x-full');
            sidebarBackdrop.classList.remove('opacity-0', 'pointer-events-none');
            sidebarOpenBtn?.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };
        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            sidebarBackdrop.classList.add('opacity-0', 'pointer-events-none');
            sidebarOpenBtn?.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };

        sidebarOpenBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarBackdrop.addEventListener('click', closeSidebar);
        sidebar.querySelectorAll('a').forEach(a => a.addEventListener('click', closeSidebar));
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) closeSidebar();
        });
    }

    /* ════════════════════════════════════════════════════════
       CONFIRM DIALOGS — handled by SweetAlert2 in admin_footer
       Fallback for any non-admin page that still uses data-confirm
    ════════════════════════════════════════════════════════ */
    if (typeof Swal === 'undefined') {
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', e => {
                if (!confirm(btn.dataset.confirm || 'Are you sure?')) e.preventDefault();
            });
        });
    }

    /* ════════════════════════════════════════════════════════
       SCROLL ANIMATIONS (IntersectionObserver)
    ════════════════════════════════════════════════════════ */
    const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.07, rootMargin: '0px 0px -28px 0px' });

    document.querySelectorAll('.fade-in, .fade-left').forEach(el => io.observe(el));

    /* ════════════════════════════════════════════════════════
       COUNT-UP ANIMATION
    ════════════════════════════════════════════════════════ */
    const easeOutCubic = t => 1 - Math.pow(1 - t, 3);

    function animateCount(el) {
        const target   = parseFloat(el.dataset.count);
        const suffix   = el.dataset.suffix || '';
        const prefix   = el.dataset.prefix || '';
        const duration = 1600;
        const start    = performance.now();

        function step(now) {
            const elapsed  = now - start;
            const progress = Math.min(elapsed / duration, 1);
            const value    = easeOutCubic(progress) * target;
            el.textContent = prefix + (Number.isInteger(target) ? Math.round(value) : value.toFixed(1)) + suffix;
            if (progress < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }

    const countIo = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCount(entry.target);
                countIo.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('[data-count]').forEach(el => countIo.observe(el));

    /* ════════════════════════════════════════════════════════
       GALLERY CATEGORY FILTERS (animated transition)
    ════════════════════════════════════════════════════════ */
    const filterTabs   = document.querySelectorAll('[data-filter]');
    const galleryItems = document.querySelectorAll('[data-category]');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            filterTabs.forEach(t => t.classList.remove('filter-active'));
            tab.classList.add('filter-active');
            const filter = tab.dataset.filter;

            galleryItems.forEach(item => {
                const show = filter === 'all' || item.dataset.category === filter;
                if (show) {
                    item.style.display = 'block';
                    /* Small delay for re-paint, then fade in */
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.96)';
                    requestAnimationFrame(() => {
                        item.style.transition = 'opacity 280ms ease, transform 280ms ease';
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    });
                } else {
                    item.style.opacity = '0';
                    setTimeout(() => { item.style.display = 'none'; }, 180);
                }
            });
        });
    });

    /* ════════════════════════════════════════════════════════
       LIGHTBOX
    ════════════════════════════════════════════════════════ */
    const lightbox      = document.getElementById('lightbox');
    const lightboxImg   = document.getElementById('lightbox-img');
    const lightboxCap   = document.getElementById('lightbox-caption');
    const lightboxClose = document.getElementById('lightbox-close');
    const allImages     = [];
    let   currentIndex  = 0;

    if (lightbox && lightboxImg) {
        const triggers = document.querySelectorAll('[data-lightbox]');

        triggers.forEach((item, i) => {
            allImages.push({ src: item.dataset.lightbox, caption: item.dataset.caption || '' });

            item.addEventListener('click', () => {
                currentIndex = i;
                openLightbox(allImages[i].src, allImages[i].caption);
            });
        });

        function openLightbox(src, caption) {
            lightboxImg.src = src;
            lightboxImg.alt = caption;
            if (lightboxCap) lightboxCap.textContent = caption;
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { lightboxImg.src = ''; }, 300);
        }

        function prevImage() {
            currentIndex = (currentIndex - 1 + allImages.length) % allImages.length;
            openLightbox(allImages[currentIndex].src, allImages[currentIndex].caption);
        }

        function nextImage() {
            currentIndex = (currentIndex + 1) % allImages.length;
            openLightbox(allImages[currentIndex].src, allImages[currentIndex].caption);
        }

        lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });
        lightboxClose?.addEventListener('click', closeLightbox);
        document.addEventListener('keydown', e => {
            if (!lightbox.classList.contains('open')) return;
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowLeft')   prevImage();
            if (e.key === 'ArrowRight')  nextImage();
        });

        /* Nav arrows */
        const prevBtn = document.getElementById('lightbox-prev');
        const nextBtn = document.getElementById('lightbox-next');
        prevBtn?.addEventListener('click', e => { e.stopPropagation(); prevImage(); });
        nextBtn?.addEventListener('click', e => { e.stopPropagation(); nextImage(); });
    }

    /* ════════════════════════════════════════════════════════
       NAVBAR SCROLL SHADOW + SHRINK
    ════════════════════════════════════════════════════════ */
    const header = document.querySelector('header');
    if (header) {
        let ticking = false;
        const onScroll = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    const y = window.scrollY;
                    if (y > 8) {
                        header.classList.add('shadow-md');
                        header.classList.add('nav-scrolled');
                    } else {
                        header.classList.remove('shadow-md');
                        header.classList.remove('nav-scrolled');
                    }
                    ticking = false;
                });
                ticking = true;
            }
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ════════════════════════════════════════════════════════
       LAZY-LOAD IMAGE FADE
    ════════════════════════════════════════════════════════ */
    document.querySelectorAll('img[loading="lazy"]').forEach(img => {
        img.classList.add('lazy-fade');
        if (img.complete && img.naturalWidth) {
            img.classList.add('loaded');
        } else {
            img.addEventListener('load', () => img.classList.add('loaded'), { once: true });
        }
    });

    /* ════════════════════════════════════════════════════════
       FLASH MESSAGE AUTO-DISMISS
    ════════════════════════════════════════════════════════ */
    document.querySelectorAll('[data-flash]').forEach(el => {
        setTimeout(() => {
            el.style.transition = 'opacity 500ms ease, transform 400ms ease';
            el.style.opacity = '0';
            el.style.transform = 'translateY(-8px)';
            setTimeout(() => el.remove(), 500);
        }, 4000);
    });

});
