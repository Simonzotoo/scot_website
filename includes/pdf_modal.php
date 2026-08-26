<!-- ════════════════════════════════════════════════════════════
     PDF PREVIEW MODAL, powered by PDF.js
     Trigger: data-pdf-id="N" data-pdf-title="Title" data-pdf-download="url"
════════════════════════════════════════════════════════════ -->
<div id="pdf-modal"
     role="dialog" aria-modal="true" aria-label="PDF Preview"
     style="display:none; background:#0b1120;"
     class="fixed inset-0 z-[300] flex flex-col">

    <!-- ── Toolbar ─────────────────────────────────────────── -->
    <div class="flex items-center gap-3 px-3 sm:px-5 py-3 flex-shrink-0"
         style="background:#070d1a; border-bottom:1px solid rgba(255,255,255,.08);">

        <!-- Doc title -->
        <div class="flex items-center gap-2.5 min-w-0 flex-1">
            <div class="grid h-8 w-8 flex-shrink-0 place-items-center rounded-lg" style="background:rgba(212,175,55,.15);">
                <svg class="h-4 w-4" style="color:#D4AF37" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </div>
            <span id="pdf-modal-title" class="text-sm font-semibold text-white truncate max-w-[140px] sm:max-w-none"></span>
        </div>

        <!-- Page nav -->
        <div class="flex items-center gap-1.5 flex-shrink-0">
            <button id="pdf-prev-page" title="Previous page (←)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white transition disabled:opacity-30 disabled:cursor-not-allowed" disabled>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <span class="text-xs font-medium px-1.5 tabular-nums" style="color:rgba(255,255,255,.55);">
                <span id="pdf-current-page">–</span>
                <span style="color:rgba(255,255,255,.25);"> / </span>
                <span id="pdf-total-pages">–</span>
            </span>
            <button id="pdf-next-page" title="Next page (→)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white transition disabled:opacity-30 disabled:cursor-not-allowed" disabled>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>

        <!-- Zoom + actions -->
        <div class="flex items-center gap-1 flex-shrink-0">
            <button id="pdf-zoom-out" title="Zoom out (-)"
                    class="hidden sm:grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                </svg>
            </button>
            <span id="pdf-zoom-label" class="hidden sm:block text-xs tabular-nums px-1.5 min-w-[3rem] text-center" style="color:rgba(255,255,255,.45);">100%</span>
            <button id="pdf-zoom-in" title="Zoom in (+)"
                    class="hidden sm:grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
            </button>

            <div class="w-px h-5 mx-1 hidden sm:block" style="background:rgba(255,255,255,.12);"></div>

            <a id="pdf-download-btn" href="#" download
               title="Download PDF"
               class="grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-white/10 hover:text-white transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            <button id="pdf-modal-close" title="Close (Esc)"
                    class="grid h-8 w-8 place-items-center rounded-lg text-white/60 hover:bg-red-500/20 hover:text-red-300 transition ml-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Viewer ──────────────────────────────────────────── -->
    <div id="pdf-viewer-scroll" class="flex-1 overflow-auto" style="background:#0f1829;">
        <div class="flex flex-col items-center py-8 px-4 min-h-full">

            <!-- Skeleton loader -->
            <div id="pdf-skeleton" class="w-full max-w-3xl rounded-xl overflow-hidden shadow-2xl" style="display:none; background:#1a2540;">
                <div class="pdf-shimmer h-8 w-full mb-0.5"></div>
                <?php for ($i = 0; $i < 18; $i++): ?>
                <div class="pdf-shimmer h-4 w-full mb-1" style="opacity:<?= 0.9 - ($i * 0.03) ?>"></div>
                <?php endfor; ?>
                <div class="pdf-shimmer h-8 w-3/4 mt-4"></div>
            </div>

            <!-- Error state -->
            <div id="pdf-error" class="hidden flex-col items-center justify-center py-20 text-center">
                <div class="grid h-16 w-16 place-items-center rounded-2xl mx-auto mb-4" style="background:rgba(239,68,68,.15);">
                    <svg class="h-8 w-8 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <p class="font-heading font-bold text-white text-lg mb-1">Could not load document</p>
                <p class="text-sm text-white/40">The PDF may be missing or inaccessible.</p>
            </div>

            <!-- Canvas -->
            <canvas id="pdf-canvas"
                    class="max-w-full rounded-xl shadow-[0_24px_80px_rgba(0,0,0,.70)]"
                    style="display:none;"></canvas>
        </div>
    </div>

    <!-- ── Mobile bottom bar ───────────────────────────────── -->
    <div class="sm:hidden flex items-center justify-center gap-4 py-2.5 flex-shrink-0"
         style="background:#070d1a; border-top:1px solid rgba(255,255,255,.06);">
        <button id="pdf-zoom-out-mob"
                class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-white/60 hover:bg-white/10 hover:text-white transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
            Zoom out
        </button>
        <span id="pdf-zoom-label-mob" class="text-xs tabular-nums" style="color:rgba(255,255,255,.40);">100%</span>
        <button id="pdf-zoom-in-mob"
                class="flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold text-white/60 hover:bg-white/10 hover:text-white transition">
            Zoom in
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </button>
    </div>
</div>

<style>
/* ── PDF Modal ─────────────────────────────────────────────── */
#pdf-modal { transition: opacity 200ms ease; }
#pdf-modal.pdf-entering { animation: pdfFadeIn 200ms ease forwards; }
#pdf-modal.pdf-leaving  { animation: pdfFadeOut 180ms ease forwards; }
@keyframes pdfFadeIn  { from { opacity:0; transform:scale(.98); } to { opacity:1; transform:scale(1); } }
@keyframes pdfFadeOut { from { opacity:1; transform:scale(1); } to { opacity:0; transform:scale(.98); } }

#pdf-canvas { transition: opacity 200ms ease; }
#pdf-canvas.pdf-page-transition { opacity: 0; }

/* Shimmer skeleton */
.pdf-shimmer {
    background: linear-gradient(90deg,
        rgba(255,255,255,.04) 25%,
        rgba(255,255,255,.08) 50%,
        rgba(255,255,255,.04) 75%);
    background-size: 400% 100%;
    animation: shimmer 1.4s ease infinite;
    border-radius: 4px;
}
@keyframes shimmer {
    0%   { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}
</style>

<script>
/* ════════════════════════════════════════════════════════════
   SCOTSA PDF VIEWER, PDF.js powered
════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    const PDFJS_CDN    = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
    const PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    const BASE_URL     = '<?= BASE_URL ?>';

    /* State */
    let pdfDoc      = null;
    let currentPage = 1;
    let totalPages  = 0;
    let scale       = 1.2;
    let rendering   = false;
    let pdfJsReady  = false;

    /* Elements */
    const modal       = document.getElementById('pdf-modal');
    const canvas      = document.getElementById('pdf-canvas');
    const skeleton    = document.getElementById('pdf-skeleton');
    const errEl       = document.getElementById('pdf-error');
    const titleEl     = document.getElementById('pdf-modal-title');
    const curPageEl   = document.getElementById('pdf-current-page');
    const totPageEl   = document.getElementById('pdf-total-pages');
    const zoomLabel   = document.getElementById('pdf-zoom-label');
    const zoomLabelM  = document.getElementById('pdf-zoom-label-mob');
    const prevBtn     = document.getElementById('pdf-prev-page');
    const nextBtn     = document.getElementById('pdf-next-page');
    const dlBtn       = document.getElementById('pdf-download-btn');
    const closeBtn    = document.getElementById('pdf-modal-close');

    if (!modal || !canvas) return;
    const ctx = canvas.getContext('2d');

    /* ── Load PDF.js lazily ─────────────────────────────── */
    function ensurePDFJS() {
        return new Promise((resolve, reject) => {
            if (pdfJsReady && window.pdfjsLib) { resolve(); return; }
            const s = document.createElement('script');
            s.src = PDFJS_CDN;
            s.onload = () => {
                window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
                pdfJsReady = true;
                resolve();
            };
            s.onerror = () => reject(new Error('PDF.js failed to load'));
            document.head.appendChild(s);
        });
    }

    /* ── Show / hide states ─────────────────────────────── */
    function showSkeleton() {
        skeleton.style.display = 'block';
        canvas.style.display   = 'none';
        errEl.classList.add('hidden'); errEl.classList.remove('flex');
    }
    function showCanvas() {
        skeleton.style.display = 'none';
        canvas.style.display   = 'block';
        errEl.classList.add('hidden'); errEl.classList.remove('flex');
    }
    function showError() {
        skeleton.style.display = 'none';
        canvas.style.display   = 'none';
        errEl.classList.remove('hidden'); errEl.classList.add('flex');
    }

    /* ── Render page ────────────────────────────────────── */
    async function renderPage(num) {
        if (!pdfDoc || rendering) return;
        rendering = true;

        canvas.classList.add('pdf-page-transition');

        try {
            const page   = await pdfDoc.getPage(num);
            const vp     = page.getViewport({ scale });

            canvas.width  = vp.width;
            canvas.height = vp.height;

            await page.render({ canvasContext: ctx, viewport: vp }).promise;

            currentPage   = num;
            curPageEl.textContent = num;

            prevBtn.disabled = num <= 1;
            nextBtn.disabled = num >= totalPages;

            showCanvas();
        } catch (e) {
            console.error('[SCOTSA PDF] Render error:', e);
            showError();
        } finally {
            rendering = false;
            requestAnimationFrame(() => canvas.classList.remove('pdf-page-transition'));
        }
    }

    /* ── Zoom helpers ───────────────────────────────────── */
    const ZOOM_STEPS = [0.6, 0.75, 0.9, 1.0, 1.2, 1.4, 1.6, 2.0];
    function updateZoomLabels() {
        const pct = Math.round(scale * 100) + '%';
        if (zoomLabel)  zoomLabel.textContent  = pct;
        if (zoomLabelM) zoomLabelM.textContent = pct;
    }
    function zoomOut() {
        const i = ZOOM_STEPS.findLastIndex(s => s < scale);
        if (i >= 0) { scale = ZOOM_STEPS[i]; updateZoomLabels(); renderPage(currentPage); }
    }
    function zoomIn() {
        const i = ZOOM_STEPS.findIndex(s => s > scale);
        if (i >= 0) { scale = ZOOM_STEPS[i]; updateZoomLabels(); renderPage(currentPage); }
    }

    /* ── Open ───────────────────────────────────────────── */
    async function openModal(id, title, downloadUrl) {
        /* Reset state */
        pdfDoc = null; currentPage = 1; totalPages = 0; scale = 1.2;
        rendering = false;
        updateZoomLabels();
        prevBtn.disabled = true;
        nextBtn.disabled = true;
        curPageEl.textContent = '–';
        totPageEl.textContent = '–';

        /* Set title + download link */
        titleEl.textContent = title || 'Document Preview';
        dlBtn.href = downloadUrl || '#';

        /* Show modal */
        modal.style.display = 'flex';
        modal.classList.add('pdf-entering');
        document.body.style.overflow = 'hidden';
        setTimeout(() => modal.classList.remove('pdf-entering'), 220);

        showSkeleton();

        try {
            await ensurePDFJS();
            const pdfUrl = BASE_URL + '/download.php?id=' + encodeURIComponent(id) + '&preview=1';
            pdfDoc = await window.pdfjsLib.getDocument(pdfUrl).promise;
            totalPages = pdfDoc.numPages;
            totPageEl.textContent = totalPages;
            await renderPage(1);
        } catch (e) {
            console.error('[SCOTSA PDF] Load error:', e);
            showError();
        }
    }

    /* ── Close ──────────────────────────────────────────── */
    function closeModal() {
        modal.classList.add('pdf-leaving');
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('pdf-leaving');
            document.body.style.overflow = '';
            /* cleanup canvas */
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            pdfDoc = null;
        }, 200);
    }

    /* ── Event wiring ───────────────────────────────────── */
    closeBtn.addEventListener('click', closeModal);

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1 && !rendering) renderPage(currentPage - 1);
    });
    nextBtn.addEventListener('click', () => {
        if (currentPage < totalPages && !rendering) renderPage(currentPage + 1);
    });

    document.getElementById('pdf-zoom-out')?.addEventListener('click', zoomOut);
    document.getElementById('pdf-zoom-in')?.addEventListener('click', zoomIn);
    document.getElementById('pdf-zoom-out-mob')?.addEventListener('click', zoomOut);
    document.getElementById('pdf-zoom-in-mob')?.addEventListener('click', zoomIn);

    /* Keyboard shortcuts */
    document.addEventListener('keydown', e => {
        if (modal.style.display === 'none') return;
        if (e.key === 'Escape')      closeModal();
        if (e.key === 'ArrowLeft')   { if (currentPage > 1)           renderPage(currentPage - 1); }
        if (e.key === 'ArrowRight')  { if (currentPage < totalPages)   renderPage(currentPage + 1); }
        if (e.key === '+' || e.key === '=') zoomIn();
        if (e.key === '-')           zoomOut();
    });

    /* Delegate: any element with data-pdf-id triggers preview */
    document.addEventListener('click', e => {
        const trigger = e.target.closest('[data-pdf-id]');
        if (!trigger) return;
        e.preventDefault();
        const id          = trigger.dataset.pdfId;
        const title       = trigger.dataset.pdfTitle || 'Document';
        const downloadUrl = trigger.dataset.pdfDownload || (BASE_URL + '/download.php?id=' + id);
        openModal(id, title, downloadUrl);
    });

    /* Expose globally for programmatic use */
    window.ScotSA = window.ScotSA || {};
    window.ScotSA.openPDF = openModal;
    window.ScotSA.closePDF = closeModal;
})();
</script>
