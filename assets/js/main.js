/* ── SCOTSA Platform — Premium JS ─────────────────────────── */

/* Broken-image fallback: any <img data-fallback="..."> swaps to that URL
   if its real src 404s. Registered at the top level (not inside
   DOMContentLoaded) and on the capture phase, since 'error' doesn't
   bubble — this has to be listening before any image has a chance to
   fail. Kept as a delegated listener rather than inline onerror="..." so
   it runs under this site's CSP (script-src has no 'unsafe-inline'). */
document.addEventListener('error', (e) => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement) || !img.dataset.fallback) return;
    if (img.src === img.dataset.fallback) return; // already showing the fallback — don't loop
    img.src = img.dataset.fallback;
}, true);

document.addEventListener('DOMContentLoaded', () => {

    /* ════════════════════════════════════════════════════════
       DARK MODE
    ════════════════════════════════════════════════════════ */
    const html         = document.documentElement;
    const themeToggles = document.querySelectorAll('[data-theme-toggle]');
    // Defaults to light unless the visitor has explicitly toggled dark
    // before (stored in localStorage) — matches assets/js/theme-init.js,
    // which already applied this same default before first paint.
    const initial      = localStorage.getItem('scotsa-theme') || 'light';

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
       HERO CAROUSEL (homepage)
    ════════════════════════════════════════════════════════ */
    const heroSection = document.getElementById('hero-carousel');

    if (heroSection) {
        const heroSlides  = Array.from(heroSection.querySelectorAll('[data-slide]'));
        const heroDots    = Array.from(document.querySelectorAll('#hero-dots [data-dot]'));
        const heroPrevBtn = document.getElementById('hero-prev');
        const heroNextBtn = document.getElementById('hero-next');
        const headlineEl  = document.getElementById('hero-headline');
        const subtextEl   = document.getElementById('hero-subtext');
        const watchBtn    = document.getElementById('hero-watch-btn');
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let heroIndex = 0;
        let heroTimer = null;

        function toEmbedUrl(url) {
            try {
                const u = new URL(url);
                if (u.hostname.includes('youtu.be')) {
                    return 'https://www.youtube.com/embed/' + u.pathname.slice(1);
                }
                if (u.hostname.includes('youtube.com')) {
                    const id = u.searchParams.get('v');
                    if (id) return 'https://www.youtube.com/embed/' + id;
                    if (u.pathname.startsWith('/embed/')) return url;
                }
                if (u.hostname.includes('vimeo.com')) {
                    const id = u.pathname.split('/').filter(Boolean).pop();
                    return 'https://player.vimeo.com/video/' + id;
                }
            } catch (err) { /* not a valid URL yet; fall through */ }
            return url;
        }

        function showHeroSlide(i) {
            heroIndex = (i + heroSlides.length) % heroSlides.length;
            heroSlides.forEach((s, idx) => {
                s.classList.toggle('active', idx === heroIndex);
                const bgVideo = s.querySelector('.hero-bg-video');
                if (bgVideo) {
                    if (idx === heroIndex && !reduceMotion) {
                        bgVideo.play().catch(() => {}); // ignore autoplay-blocked rejections
                    } else {
                        bgVideo.pause();
                    }
                }
            });
            heroDots.forEach((d, idx) => d.classList.toggle('active', idx === heroIndex));

            const active = heroSlides[heroIndex];
            if (headlineEl && active.dataset.headline) headlineEl.textContent = active.dataset.headline;
            if (subtextEl && active.dataset.subtext) subtextEl.textContent = active.dataset.subtext;

            if (watchBtn) {
                const videoUrl = active.dataset.video || '';
                watchBtn.classList.toggle('hidden', !videoUrl);
                watchBtn.dataset.videoEmbed = videoUrl ? toEmbedUrl(videoUrl) : '';
            }
        }

        function stopHeroAutoplay() {
            if (heroTimer) clearInterval(heroTimer);
            heroTimer = null;
        }

        function startHeroAutoplay() {
            if (reduceMotion || heroSlides.length < 2) return;
            stopHeroAutoplay();
            heroTimer = setInterval(() => showHeroSlide(heroIndex + 1), 6000);
        }

        if (heroSlides.length) {
            showHeroSlide(0);
            startHeroAutoplay();

            heroPrevBtn?.addEventListener('click', () => { showHeroSlide(heroIndex - 1); startHeroAutoplay(); });
            heroNextBtn?.addEventListener('click', () => { showHeroSlide(heroIndex + 1); startHeroAutoplay(); });
            heroDots.forEach(dot => dot.addEventListener('click', () => {
                showHeroSlide(parseInt(dot.dataset.dot, 10));
                startHeroAutoplay();
            }));

            heroSection.addEventListener('mouseenter', stopHeroAutoplay);
            heroSection.addEventListener('mouseleave', startHeroAutoplay);
        }

        /* Video modal — only loads an <iframe> once a viewer taps "Watch" */
        const videoModal = document.getElementById('video-modal');
        const videoFrame  = document.getElementById('video-modal-frame');
        const videoClose  = document.getElementById('video-modal-close');

        function openVideoModal(embedUrl) {
            if (!videoModal || !videoFrame || !embedUrl) return;
            videoFrame.innerHTML = '<iframe src="' + embedUrl + '" title="Video" frameborder="0" ' +
                'allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
            videoModal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeVideoModal() {
            if (!videoModal) return;
            videoModal.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { if (videoFrame) videoFrame.innerHTML = ''; }, 300);
        }

        watchBtn?.addEventListener('click', () => openVideoModal(watchBtn.dataset.videoEmbed));
        videoClose?.addEventListener('click', closeVideoModal);
        videoModal?.addEventListener('click', e => { if (e.target === videoModal) closeVideoModal(); });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && videoModal?.classList.contains('open')) closeVideoModal();
        });
    }

    /* ════════════════════════════════════════════════════════
       GALLERY CATEGORY FILTERS (animated transition)
    ════════════════════════════════════════════════════════ */
    const galleryFilterTabs  = document.querySelectorAll('[data-gallery-filter]');
    const galleryFilterItems = document.querySelectorAll('[data-gallery-category]');

    galleryFilterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            galleryFilterTabs.forEach(t => t.classList.remove('filter-active'));
            tab.classList.add('filter-active');
            const filter = tab.dataset.galleryFilter;

            galleryFilterItems.forEach(item => {
                const show = filter === 'All' || item.dataset.galleryCategory === filter;
                if (show) {
                    item.style.display = 'block';
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
       GALLERY LIGHTBOX
    ════════════════════════════════════════════════════════ */
    const galleryLightbox = document.getElementById('gallery-lightbox');

    if (galleryLightbox) {
        const glImg     = document.getElementById('gallery-lightbox-img');
        const glCaption = document.getElementById('gallery-lightbox-caption');
        const glClose   = document.getElementById('gallery-lightbox-close');
        const glPrev    = document.getElementById('gallery-lightbox-prev');
        const glNext    = document.getElementById('gallery-lightbox-next');

        const glTriggers = Array.from(document.querySelectorAll('[data-gallery-lightbox]'));
        const glItems = glTriggers.map(el => ({
            src: el.dataset.galleryLightbox,
            caption: el.dataset.galleryCaption || '',
        }));
        let glIndex = 0;

        function openGalleryLightbox(i) {
            if (!glItems.length) return;
            glIndex = (i + glItems.length) % glItems.length;
            const item = glItems[glIndex];
            if (glImg) { glImg.src = item.src; glImg.alt = item.caption; }
            if (glCaption) glCaption.textContent = item.caption;
            galleryLightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeGalleryLightbox() {
            galleryLightbox.classList.remove('open');
            document.body.style.overflow = '';
            setTimeout(() => { if (glImg) glImg.src = ''; }, 300);
        }

        glTriggers.forEach((el, i) => el.addEventListener('click', () => openGalleryLightbox(i)));

        galleryLightbox.addEventListener('click', e => { if (e.target === galleryLightbox) closeGalleryLightbox(); });
        glClose?.addEventListener('click', closeGalleryLightbox);
        glPrev?.addEventListener('click', e => { e.stopPropagation(); openGalleryLightbox(glIndex - 1); });
        glNext?.addEventListener('click', e => { e.stopPropagation(); openGalleryLightbox(glIndex + 1); });
        document.addEventListener('keydown', e => {
            if (!galleryLightbox.classList.contains('open')) return;
            if (e.key === 'Escape')     closeGalleryLightbox();
            if (e.key === 'ArrowLeft')  openGalleryLightbox(glIndex - 1);
            if (e.key === 'ArrowRight') openGalleryLightbox(glIndex + 1);
        });
    }

    /* ════════════════════════════════════════════════════════
       FACULTY PROFILE PAGE — full-viewport takeover, not a modal.
       Header fields are simple text swaps; the five tab panels are
       populated by copying each matching hidden [data-faculty-content]
       block's panels into the shared panel containers on open.
    ════════════════════════════════════════════════════════ */
    const facultyPage = document.getElementById('faculty-page');

    if (facultyPage) {
        const pagePhoto     = document.getElementById('faculty-page-photo');
        const pageName      = document.getElementById('faculty-page-name');
        const pageRole      = document.getElementById('faculty-page-role');
        const pagePortfolio = document.getElementById('faculty-page-portfolio');
        const pageEmail     = document.getElementById('faculty-page-email');
        const pageEmailText = document.getElementById('faculty-page-email-text');
        const pageClose     = document.getElementById('faculty-page-close');
        const pagePrev      = document.getElementById('faculty-page-prev');
        const pageNext      = document.getElementById('faculty-page-next');
        const tabButtons    = Array.from(document.querySelectorAll('[data-faculty-tab]'));
        const panels        = {
            overview:     document.getElementById('faculty-page-panel-overview'),
            courses:      document.getElementById('faculty-page-panel-courses'),
            research:     document.getElementById('faculty-page-panel-research'),
            publications: document.getElementById('faculty-page-panel-publications'),
            education:    document.getElementById('faculty-page-panel-education'),
        };

        const facultyTriggers = Array.from(document.querySelectorAll('[data-faculty-trigger]'));
        const facultyList = facultyTriggers.map(el => ({
            id: el.dataset.facultyTrigger,
            name: el.dataset.name || '',
            role: el.dataset.role || '',
            portfolio: el.dataset.portfolio || '',
            photo: el.dataset.photo || '',
            email: el.dataset.email || '',
        }));
        let facultyIndex = 0;

        function setFacultyTab(tabName) {
            tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.facultyTab === tabName));
            Object.keys(panels).forEach(key => panels[key]?.classList.toggle('active', key === tabName));
        }

        function openFacultyPage(i) {
            if (!facultyList.length) return;
            facultyIndex = (i + facultyList.length) % facultyList.length;
            const f = facultyList[facultyIndex];

            if (pagePhoto) { pagePhoto.src = f.photo; pagePhoto.alt = f.name; }
            if (pageName) pageName.textContent = f.name;
            if (pageRole) pageRole.textContent = f.role;
            if (pagePortfolio) pagePortfolio.textContent = f.portfolio;
            if (pageEmail) {
                if (f.email) {
                    pageEmail.href = 'mailto:' + f.email;
                    if (pageEmailText) pageEmailText.textContent = f.email;
                    pageEmail.classList.remove('hidden');
                    pageEmail.classList.add('inline-flex');
                } else {
                    pageEmail.classList.add('hidden');
                    pageEmail.classList.remove('inline-flex');
                }
            }

            const source = document.querySelector('[data-faculty-content="' + f.id + '"]');
            if (source) {
                Object.keys(panels).forEach(key => {
                    const sourcePanel = source.querySelector('[data-panel="' + key + '"]');
                    if (panels[key] && sourcePanel) panels[key].innerHTML = sourcePanel.innerHTML;
                });
            }
            setFacultyTab('overview');

            const showNav = facultyList.length > 1 ? '' : 'none';
            if (pagePrev) pagePrev.style.display = showNav;
            if (pageNext) pageNext.style.display = showNav;

            facultyPage.classList.add('open');
            facultyPage.scrollTop = 0;
            document.body.style.overflow = 'hidden';
        }

        function closeFacultyPage() {
            facultyPage.classList.remove('open');
            document.body.style.overflow = '';
        }

        facultyTriggers.forEach((el, i) => el.addEventListener('click', () => openFacultyPage(i)));

        tabButtons.forEach(btn => btn.addEventListener('click', () => setFacultyTab(btn.dataset.facultyTab)));

        pageClose?.addEventListener('click', closeFacultyPage);
        pagePrev?.addEventListener('click', () => openFacultyPage(facultyIndex - 1));
        pageNext?.addEventListener('click', () => openFacultyPage(facultyIndex + 1));
        document.addEventListener('keydown', e => {
            if (!facultyPage.classList.contains('open')) return;
            if (e.key === 'Escape') closeFacultyPage();
        });
    }

    /* ════════════════════════════════════════════════════════
       COURSE STRUCTURE MODAL
    ════════════════════════════════════════════════════════ */
    const courseModal = document.getElementById('course-modal');

    if (courseModal) {
        const courseModalTitle = document.getElementById('course-modal-title');
        const courseModalTag   = document.getElementById('course-modal-tag');
        const courseModalNote  = document.getElementById('course-modal-note');
        const courseModalBody  = document.getElementById('course-modal-body');
        const courseModalClose = document.getElementById('course-modal-close');

        function openCourseModal(trigger) {
            const code = trigger.dataset.courseTrigger;
            const content = document.querySelector('[data-course-content="' + code + '"]');
            if (!content) return;

            if (courseModalTag) courseModalTag.textContent = trigger.dataset.courseTag || '';
            if (courseModalTitle) courseModalTitle.textContent = trigger.dataset.courseTitle || '';
            if (courseModalNote) {
                const note = trigger.dataset.courseNote || '';
                courseModalNote.textContent = note;
                courseModalNote.classList.toggle('hidden', !note);
            }
            if (courseModalBody) courseModalBody.innerHTML = content.innerHTML;

            courseModal.classList.add('open');
            courseModal.scrollTop = 0;
            if (courseModalBody) courseModalBody.scrollTop = 0;
            document.body.style.overflow = 'hidden';
        }

        function closeCourseModal() {
            courseModal.classList.remove('open');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-course-trigger]').forEach(el => {
            el.addEventListener('click', () => openCourseModal(el));
        });

        courseModal.addEventListener('click', e => { if (e.target === courseModal) closeCourseModal(); });
        courseModalClose?.addEventListener('click', closeCourseModal);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && courseModal.classList.contains('open')) closeCourseModal();
        });
    }

    /* ════════════════════════════════════════════════════════
       PROGRAMME FILTERS (by degree tag: BSc / Diploma / MSc)
    ════════════════════════════════════════════════════════ */
    const programmeFilterTabs  = document.querySelectorAll('[data-programme-filter]');
    const programmeFilterItems = document.querySelectorAll('[data-programme-category]');
    const programmeGroups      = document.querySelectorAll('[data-programme-group]');

    function updateProgrammeGroupVisibility() {
        programmeGroups.forEach(group => {
            const anyVisible = Array.from(group.querySelectorAll('[data-programme-category]'))
                .some(item => item.style.display !== 'none');
            group.style.display = anyVisible ? '' : 'none';
        });
    }

    programmeFilterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            programmeFilterTabs.forEach(t => t.classList.remove('filter-active'));
            tab.classList.add('filter-active');
            const filter = tab.dataset.programmeFilter;

            programmeFilterItems.forEach(item => {
                const show = filter === 'All' || item.dataset.programmeCategory === filter;
                if (show) {
                    item.style.display = 'block';
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.96)';
                    requestAnimationFrame(() => {
                        item.style.transition = 'opacity 280ms ease, transform 280ms ease';
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    });
                } else {
                    item.style.opacity = '0';
                    setTimeout(() => { item.style.display = 'none'; updateProgrammeGroupVisibility(); }, 180);
                }
            });

            updateProgrammeGroupVisibility();
        });
    });

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
